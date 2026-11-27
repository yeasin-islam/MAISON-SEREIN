<?php
declare(strict_types=1);

final class OrderConfirmationEmailService
{
    public function __construct(private readonly PDO $pdo) {}

    public function sendForOrder(int $orderId): array
    {
        $orderStmt = $this->pdo->prepare(
            'SELECT o.*, z.name AS delivery_zone_name, d.code AS coupon_code,
                (SELECT setting_value FROM store_settings WHERE setting_key = "store_email" LIMIT 1) AS support_email,
                (SELECT setting_value FROM store_settings WHERE setting_key = "store_phone" LIMIT 1) AS support_phone
             FROM orders o
             LEFT JOIN delivery_zones z ON z.id = o.delivery_zone_id
             LEFT JOIN discount_usage du ON du.order_id = o.id
             LEFT JOIN discounts d ON d.id = du.discount_id
             WHERE o.id = :id LIMIT 1'
        );
        $orderStmt->execute(['id' => $orderId]);
        $order = $orderStmt->fetch();
        if (!$order) {
            throw new InvalidArgumentException('Order not found.');
        }

        $itemStmt = $this->pdo->prepare(
            'SELECT oi.product_name, oi.size_label, oi.unit_price, oi.quantity, oi.line_total,
                (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = oi.product_id ORDER BY pi.sort_order, pi.id LIMIT 1) AS image_path
             FROM order_items oi WHERE oi.order_id = :id ORDER BY oi.id'
        );
        $itemStmt->execute(['id' => $orderId]);
        $items = $itemStmt->fetchAll();

        $email = trim((string) ($order['email'] ?? ''));
        if ($email === '') {
            $this->markSkipped($orderId, null);
            return ['status' => 'skipped'];
        }

        $claim = $this->claimAttempt($orderId, $email);
        if ($claim !== 'claimed') {
            return ['status' => $claim];
        }

        try {
            $this->sendMessage($order, $items);
        } catch (Throwable $error) {
            $this->recordFailure($orderId, $error);
            return ['status' => 'failed'];
        }

        try {
            $update = $this->pdo->prepare(
                'UPDATE order_email_notifications SET status = "sent", last_error = NULL, sent_at = NOW(), updated_at = NOW() WHERE order_id = :id'
            );
            $update->execute(['id' => $orderId]);
            return ['status' => 'sent'];
        } catch (Throwable $error) {
            error_log('[THE LAST NOTE BD] Email sent but confirmation status could not be saved for order ' . $orderId . ' (' . get_class($error) . ').');
            return ['status' => 'sending'];
        }
    }

    private function claimAttempt(int $orderId, string $email): string
    {
        $this->pdo->beginTransaction();
        try {
            $insert = $this->pdo->prepare(
                'INSERT IGNORE INTO order_email_notifications (order_id, recipient_email, status) VALUES (:id, :email, "pending")'
            );
            $insert->execute(['id' => $orderId, 'email' => $email]);
            $select = $this->pdo->prepare(
                'SELECT status, last_attempt_at, last_attempt_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE) AS stale
                 FROM order_email_notifications WHERE order_id = :id FOR UPDATE'
            );
            $select->execute(['id' => $orderId]);
            $notification = $select->fetch();
            if (!$notification) {
                throw new RuntimeException('Could not claim the order email attempt.');
            }

            if (in_array($notification['status'], ['sent', 'skipped'], true)) {
                $this->pdo->commit();
                return (string) $notification['status'];
            }
            if ($notification['status'] === 'sending' && (int) $notification['stale'] !== 1) {
                $this->pdo->commit();
                return 'sending';
            }

            $claim = $this->pdo->prepare(
                'UPDATE order_email_notifications
                 SET recipient_email = :email, status = "sending", attempts = attempts + 1,
                     last_error = NULL, last_attempt_at = NOW(), updated_at = NOW()
                 WHERE order_id = :id'
            );
            $claim->execute(['email' => $email, 'id' => $orderId]);
            $this->pdo->commit();
            return 'claimed';
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    private function markSkipped(int $orderId, ?string $email): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_email_notifications (order_id, recipient_email, status)
             VALUES (:id, :email, "skipped")
             ON DUPLICATE KEY UPDATE recipient_email = VALUES(recipient_email), status = IF(status = "sent", status, "skipped"), updated_at = NOW()'
        );
        $stmt->execute(['id' => $orderId, 'email' => $email]);
    }

    private function sendMessage(array $order, array $items): void
    {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            throw new RuntimeException('PHPMailer is unavailable.');
        }

        $host = trim((string) getenv('MAIL_HOST'));
        $username = trim((string) getenv('MAIL_USERNAME'));
        $password = (string) getenv('MAIL_PASSWORD');
        $fromAddress = trim((string) getenv('MAIL_FROM_ADDRESS'));
        $encryption = strtolower(trim((string) (getenv('MAIL_ENCRYPTION') ?: 'tls')));
        $port = filter_var(getenv('MAIL_PORT') ?: '587', FILTER_VALIDATE_INT);
        if (
            $host === '' || $username === '' || $password === '' ||
            !filter_var($fromAddress, FILTER_VALIDATE_EMAIL) ||
            !filter_var((string) $order['email'], FILTER_VALIDATE_EMAIL) ||
            $port === false || $port < 1 || $port > 65535 ||
            !in_array($encryption, ['tls', 'ssl'], true)
        ) {
            throw new RuntimeException('SMTP configuration is incomplete or invalid.');
        }

        $order['product_discount'] = 0.0;
        $baseUrl = $this->publicBaseUrl();
        foreach ($items as &$item) {
            $item['image_url'] = $this->publicImageUrl($item['image_path'] ?? null, $baseUrl);
        }
        unset($item);

        $supportEmail = trim((string) ($order['support_email'] ?? ''));
        if (!filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            $supportEmail = '';
        }
        $supportPhone = trim((string) ($order['support_phone'] ?? ''));
        $htmlBody = $this->renderTemplate('order-confirmation.php', [
            'order' => $order,
            'items' => $items,
            'supportEmail' => $supportEmail,
            'supportPhone' => $supportPhone,
        ]);
        $textBody = $this->renderTemplate('order-confirmation.txt.php', [
            'order' => $order,
            'items' => $items,
            'supportEmail' => $supportEmail,
            'supportPhone' => $supportPhone,
        ]);

        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->SMTPAuth = true;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->SMTPSecure = $encryption === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $port;
        $mailer->Timeout = 15;
        $mailer->SMTPDebug = 0;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom(
            $fromAddress,
            trim((string) (getenv('MAIL_FROM_NAME') ?: 'THE LAST NOTE BD'))
        );
        $mailer->addAddress((string) $order['email'], (string) $order['customer_name']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Order Confirmation – THE LAST NOTE BD | Order #' . $order['id'];
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;
        $mailer->send();
    }

    private function renderTemplate(string $name, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require dirname(__DIR__) . '/views/emails/' . $name;
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    private function publicBaseUrl(): string
    {
        $url = trim((string) (getenv('APP_URL') ?: ''));
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
            return '';
        }
        return rtrim($url, '/');
    }

    private function publicImageUrl(?string $path, string $baseUrl): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        $parts = parse_url($path);
        if (is_array($parts) && isset($parts['scheme'], $parts['host'])) {
            return strtolower((string) $parts['scheme']) === 'https' ? $path : null;
        }
        if ($baseUrl === '' || str_starts_with($path, '//') || str_contains($path, '..')) {
            return null;
        }
        return $baseUrl . '/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    private function recordFailure(int $orderId, Throwable $error): void
    {
        error_log('[THE LAST NOTE BD] Order confirmation email failed for order ' . $orderId . ' (' . get_class($error) . ').');
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE order_email_notifications
                 SET status = "failed", last_error = "Delivery failed; check server mail logs.", updated_at = NOW()
                 WHERE order_id = :id'
            );
            $stmt->execute(['id' => $orderId]);
        } catch (Throwable $recordError) {
            error_log('[THE LAST NOTE BD] Could not record order email failure for order ' . $orderId . ' (' . get_class($recordError) . ').');
        }
    }
}
