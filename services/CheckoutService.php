<?php
declare(strict_types=1);

final class CheckoutService
{
    public function __construct(private readonly PDO $pdo) {}

    public function quote(array $input, array $cart, ?int $customerId = null): array
    {
        if (!$cart) throw new InvalidArgumentException('Your cart is empty.');
        $zoneId = filter_var($input['delivery_zone_id'] ?? null, FILTER_VALIDATE_INT);
        $zone = null;
        $deliveryFee = 0.0;
        if ($zoneId) {
            $zoneStmt = $this->pdo->prepare('SELECT id, name, delivery_fee, estimated_days FROM delivery_zones WHERE id = :id AND is_active = 1');
            $zoneStmt->execute(['id' => $zoneId]);
            $zone = $zoneStmt->fetch();
            if (!$zone) throw new InvalidArgumentException('Choose an active delivery area.');
            $deliveryFee = (float) $zone['delivery_fee'];
        }

        $subtotal = 0.0;
        $items = [];
        foreach ($cart as $variantId => $quantity) {
            $variantId = filter_var($variantId, FILTER_VALIDATE_INT);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
            if (!$variantId || !$quantity || $quantity < 1 || $quantity > 10) throw new InvalidArgumentException('One or more cart quantities are invalid.');
            $variantStmt = $this->pdo->prepare('SELECT v.id, v.size_label, v.price, v.stock_quantity, p.id AS product_id, p.name,
                (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS image_path
                FROM product_variants v JOIN products p ON p.id = v.product_id
                WHERE v.id = :id AND v.is_active = 1 AND p.is_active = 1 AND p.is_visible = 1');
            $variantStmt->execute(['id' => $variantId]);
            $variant = $variantStmt->fetch();
            if (!$variant) throw new InvalidArgumentException('A selected size is no longer available.');
            $unitPrice = max(0.0, (float) $variant['price']);
            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;
            $items[] = [
                'product_id' => (int) $variant['product_id'],
                'name' => $variant['name'],
                'size_label' => $variant['size_label'],
                'image_path' => $variant['image_path'],
                'quantity' => $quantity,
                'regular_price' => $unitPrice,
                'offer_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        $code = strtoupper(trim((string) ($input['discount_code'] ?? ($_SESSION['discount_code'] ?? ''))));
        $phone = normalize_phone((string) ($input['phone'] ?? ''));
        $couponError = '';
        try {
            $discount = $this->calculateDiscount($code, $subtotal, $phone, array_map(static function (array $item): array {
                return ['product_id' => $item['product_id'], 'line_total' => $item['line_total']];
            }, $items));
        } catch (InvalidArgumentException $error) {
            $discount = ['id' => null, 'amount' => 0.0];
            $couponError = $error->getMessage();
        }
        $grandTotal = max(0, $subtotal - $discount['amount'] + $deliveryFee);

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'product_discount' => round(array_sum(array_map(
                static fn(array $item): float => max(0, $item['regular_price'] - $item['offer_price']) * $item['quantity'],
                $items
            )), 2),
            'coupon_code' => $couponError === '' ? $code : '',
            'coupon_error' => $couponError,
            'coupon_discount' => round($discount['amount'], 2),
            'delivery_fee' => round($deliveryFee, 2),
            'delivery_zone' => $zone ? ['id' => (int) $zone['id'], 'name' => $zone['name'], 'estimated_days' => $zone['estimated_days']] : null,
            'grand_total' => round($grandTotal, 2),
        ];
    }

    public function placeOrder(array $input, array $cart, ?int $customerId = null): array
    {
        $name = trim((string) ($input['full_name'] ?? ''));
        $phone = normalize_phone((string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $address = trim((string) ($input['address'] ?? ''));
        $district = trim((string) ($input['district'] ?? ''));
        $paymentMethod = (string) ($input['payment_method'] ?? '');
        $transactionId = trim((string) ($input['transaction_id'] ?? ''));
        $zoneId = filter_var($input['delivery_zone_id'] ?? null, FILTER_VALIDATE_INT);
        $token = (string) ($input['idempotency_token'] ?? '');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) throw new InvalidArgumentException('Enter your full name.');
        if (!valid_phone($phone)) throw new InvalidArgumentException('Enter a valid Bangladeshi mobile number.');
        if ($email !== '' && (mb_strlen($email) > 190 || !filter_var($email, FILTER_VALIDATE_EMAIL))) throw new InvalidArgumentException('Enter a valid email address.');
        if (mb_strlen($address) < 8 || mb_strlen($address) > 500 || mb_strlen($district) < 2) throw new InvalidArgumentException('Enter your area and complete delivery address.');
        if (!in_array($paymentMethod, ['bkash', 'nagad', 'cod'], true)) throw new InvalidArgumentException('Select an available payment method.');
        if (in_array($paymentMethod, ['bkash', 'nagad'], true) && ($transactionId === '' || mb_strlen($transactionId) > 100)) throw new InvalidArgumentException('Enter the mobile payment transaction ID.');
        if (!$zoneId || !preg_match('/^[a-f0-9]{32}$/', $token)) throw new InvalidArgumentException('Your checkout session expired. Please refresh and try again.');
        if (!$cart) throw new InvalidArgumentException('Your cart is empty.');

        $this->pdo->beginTransaction();
        try {
            $existing = $this->pdo->prepare('SELECT id, order_number, grand_total FROM orders WHERE idempotency_token = :token LIMIT 1');
            $existing->execute(['token' => $token]);
            if ($order = $existing->fetch()) {
                $this->pdo->commit();
                return ['id' => (int) $order['id'], 'order_number' => $order['order_number'], 'grand_total' => (float) $order['grand_total']];
            }
            $paymentSetting = $this->pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key = "accepted_payment_methods" LIMIT 1');
            $paymentSetting->execute();
            $acceptedMethods = json_decode((string) ($paymentSetting->fetchColumn() ?: '["bkash","nagad","cod"]'), true);
            if (!is_array($acceptedMethods) || !in_array($paymentMethod, $acceptedMethods, true)) throw new InvalidArgumentException('That payment method is currently unavailable.');
            $giftSetting = $this->pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key="gift_mode_enabled" LIMIT 1');
            $giftSetting->execute();
            $giftModeValue = $giftSetting->fetchColumn();
            $giftModeEnabled = $giftModeValue === false || (string) $giftModeValue === '1';
            $giftMessage = trim((string) ($input['gift_message'] ?? ''));
            if (mb_strlen($giftMessage) > 500) throw new InvalidArgumentException('Keep your gift note under 500 characters.');

            $zoneStmt = $this->pdo->prepare('SELECT id, delivery_fee FROM delivery_zones WHERE id = :id AND is_active = 1 FOR UPDATE');
            $zoneStmt->execute(['id' => $zoneId]);
            $zone = $zoneStmt->fetch();
            if (!$zone) throw new InvalidArgumentException('Choose an active delivery area.');

            $subtotal = 0.0;
            $lockedItems = [];
            $dropQuantities = [];
            ksort($cart, SORT_NUMERIC);
            foreach ($cart as $variantId => $quantity) {
                $variantId = filter_var($variantId, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if (!$variantId || !$quantity || $quantity < 1 || $quantity > 10) throw new InvalidArgumentException('One or more cart quantities are invalid.');
                $variantStmt = $this->pdo->prepare('SELECT v.id, v.size_label, v.price, v.stock_quantity, p.id AS product_id, p.name
                    FROM product_variants v JOIN products p ON p.id = v.product_id
                    WHERE v.id = :id AND v.is_active = 1 AND p.is_active = 1 AND p.is_visible = 1 FOR UPDATE');
                $variantStmt->execute(['id' => $variantId]);
                $variant = $variantStmt->fetch();
                if (!$variant) throw new InvalidArgumentException('A selected size is no longer available.');
                $dropLock = $this->pdo->prepare('SELECT id, release_at, end_at, stock_limit, allow_preorder FROM limited_drops WHERE product_id=:product AND status="active" AND release_at<=NOW() AND (end_at IS NULL OR end_at>=NOW()) LIMIT 1 FOR UPDATE');
                $dropLock->execute(['product' => $variant['product_id']]);
                $activeDrop = $dropLock->fetch();
                $preorderAvailable = $activeDrop && (int) $activeDrop['allow_preorder'] === 1;
                if ((int) $variant['stock_quantity'] < $quantity && !$preorderAvailable) throw new InvalidArgumentException('A selected size is no longer available in the requested quantity.');
                $dropLimitReached = false;
                if ($activeDrop && $activeDrop['stock_limit'] !== null) {
                    $soldStmt = $this->pdo->prepare('SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.product_id=:product AND o.order_status<>"cancelled" AND o.created_at>=:release_at');
                    $soldStmt->execute(['product' => $variant['product_id'], 'release_at' => $activeDrop['release_at']]);
                    $alreadyInCart = $dropQuantities[(int) $variant['product_id']] ?? 0;
                    $dropLimitReached = (int) $soldStmt->fetchColumn() + $alreadyInCart + $quantity > (int) $activeDrop['stock_limit'];
                }
                $dropQuantities[(int) $variant['product_id']] = ($dropQuantities[(int) $variant['product_id']] ?? 0) + $quantity;
                if ($dropLimitReached && !$preorderAvailable) throw new InvalidArgumentException('This limited release has reached its available quantity.');
                $variant['preorder_end_at'] = $activeDrop['end_at'] ?? null;
                $variant['is_preorder'] = (int) $variant['stock_quantity'] < $quantity || $dropLimitReached;
                $variant['quantity'] = $quantity;
                $variant['line_total'] = (float) $variant['price'] * $quantity;
                $subtotal += $variant['line_total'];
                $lockedItems[] = $variant;
            }

            $discount = $this->calculateDiscount((string) ($input['discount_code'] ?? ($_SESSION['discount_code'] ?? '')), $subtotal, $phone, $lockedItems);
            $deliveryFee = (float) $zone['delivery_fee'];
            $grandTotal = max(0, $subtotal - $discount['amount'] + $deliveryFee);
            $orderNumber = 'MS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $paymentStatus = $paymentMethod === 'cod' ? 'unpaid' : 'pending_verification';
            $orderStmt = $this->pdo->prepare('INSERT INTO orders
                (order_number, idempotency_token, customer_id, delivery_zone_id, customer_name, phone, phone_normalized, email, district, delivery_address, order_note,
                payment_method, payment_status, order_status, subtotal, discount_amount, delivery_fee, grand_total, gift_message, gift_wrap, created_at)
                VALUES (:number, :token, :customer_id, :zone_id, :name, :phone_raw, :phone, :email, :district, :address, :note, :method, :payment_status, "pending", :subtotal, :discount, :delivery, :total, :gift, :gift_wrap, NOW())');
            $orderStmt->execute([
                'number' => $orderNumber, 'token' => $token, 'customer_id' => $customerId, 'zone_id' => $zoneId,
                'name' => $name, 'phone_raw' => trim((string) $input['phone']), 'phone' => $phone, 'email' => $email ?: null, 'district' => $district,
                'address' => $address, 'note' => trim((string) ($input['order_note'] ?? '')) ?: null,
                'method' => $paymentMethod, 'payment_status' => $paymentStatus,
                'subtotal' => $subtotal, 'discount' => $discount['amount'], 'delivery' => $deliveryFee,
                'total' => $grandTotal, 'gift' => $giftModeEnabled && $giftMessage !== '' ? $giftMessage : null,
                'gift_wrap' => $giftModeEnabled && !empty($input['gift_wrap']) ? 1 : 0,
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare('INSERT INTO order_items (order_id, product_id, variant_id, product_name, size_label, unit_price, quantity, line_total)
                VALUES (:order_id, :product_id, :variant_id, :product_name, :size_label, :unit_price, :quantity, :line_total)');
            $stockStmt = $this->pdo->prepare('UPDATE product_variants SET stock_quantity = stock_quantity - :quantity, updated_at = NOW() WHERE id = :id AND stock_quantity >= :guard');
            $inventoryStmt = $this->pdo->prepare('INSERT INTO inventory_transactions (variant_id, order_id, quantity_change, reason, created_at) VALUES (:variant_id, :order_id, :quantity, "order", NOW())');
            foreach ($lockedItems as $item) {
                if (!$item['is_preorder']) {
                    $stockStmt->execute(['quantity' => $item['quantity'], 'id' => $item['id'], 'guard' => $item['quantity']]);
                    if ($stockStmt->rowCount() !== 1) throw new InvalidArgumentException('Stock changed while placing your order. Please review your cart.');
                    $inventoryStmt->execute(['variant_id' => $item['id'], 'order_id' => $orderId, 'quantity' => -$item['quantity']]);
                }
                $itemStmt->execute(['order_id' => $orderId, 'product_id' => $item['product_id'], 'variant_id' => $item['id'], 'product_name' => $item['name'], 'size_label' => $item['size_label'], 'unit_price' => $item['price'], 'quantity' => $item['quantity'], 'line_total' => $item['line_total']]);
                if ($item['is_preorder']) {
                    $preorder = $this->pdo->prepare('INSERT INTO preorders (order_id, product_id, expected_ship_date, status) VALUES (:order_id, :product_id, :ship_date, "reserved") ON DUPLICATE KEY UPDATE expected_ship_date = VALUES(expected_ship_date)');
                    $preorder->execute(['order_id' => $orderId, 'product_id' => $item['product_id'], 'ship_date' => $item['preorder_end_at'] ? date('Y-m-d', strtotime($item['preorder_end_at'])) : null]);
                }
                $salesStmt = $this->pdo->prepare('UPDATE products SET sales_count = sales_count + :quantity WHERE id = :id');
                $salesStmt->execute(['quantity' => $item['quantity'], 'id' => $item['product_id']]);
            }

            $paymentStmt = $this->pdo->prepare('INSERT INTO payments (order_id, method, transaction_id, amount, status, created_at) VALUES (:order_id, :method, :transaction_id, :amount, :status, NOW())');
            $paymentStmt->execute(['order_id' => $orderId, 'method' => $paymentMethod, 'transaction_id' => $paymentMethod === 'cod' ? null : $transactionId, 'amount' => $grandTotal, 'status' => $paymentMethod === 'cod' ? 'unpaid' : 'pending_verification']);
            $history = $this->pdo->prepare('INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (:order_id, "pending", "Order placed", NOW())');
            $history->execute(['order_id' => $orderId]);
            $notification = $this->pdo->prepare('INSERT INTO notifications (title, body, notification_type) VALUES (:title, :body, "order")');
            $notification->execute(['title' => 'New order · ' . $orderNumber, 'body' => $name . ' · BDT ' . number_format($grandTotal, 2, '.', '')]);
            if ($discount['id']) {
                $usage = $this->pdo->prepare('INSERT INTO discount_usage (discount_id, order_id, phone_normalized, used_at) VALUES (:discount_id, :order_id, :phone, NOW())');
                $usage->execute(['discount_id' => $discount['id'], 'order_id' => $orderId, 'phone' => $phone]);
                $countUsage = $this->pdo->prepare('UPDATE discounts SET usage_count = usage_count + 1 WHERE id = :id');
                $countUsage->execute(['id' => $discount['id']]);
            }
            $this->pdo->commit();
            return ['id' => $orderId, 'order_number' => $orderNumber, 'grand_total' => $grandTotal];
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    private function calculateDiscount(string $code, float $subtotal, string $phone, array $items): array
    {
        if ($code === '') return ['id' => null, 'amount' => 0.0];
        $stmt = $this->pdo->prepare('SELECT id, product_id, discount_type, discount_value, minimum_purchase, max_discount, first_order_only, usage_limit, usage_count
            FROM discounts WHERE code = :code AND is_active = 1 AND starts_at <= NOW() AND ends_at >= NOW() LIMIT 1 FOR UPDATE');
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $discount = $stmt->fetch();
        $eligibleSubtotal = $subtotal;
        if ($discount && $discount['product_id'] !== null) {
            $eligibleSubtotal = 0.0;
            foreach ($items as $item) {
                if ((int) $item['product_id'] === (int) $discount['product_id']) $eligibleSubtotal += (float) $item['line_total'];
            }
        }
        if (!$discount || $eligibleSubtotal <= 0 || $eligibleSubtotal < (float) $discount['minimum_purchase']) throw new InvalidArgumentException('That discount code is unavailable or the order minimum is not met.');
        if ($discount['usage_limit'] !== null && (int) $discount['usage_count'] >= (int) $discount['usage_limit']) throw new InvalidArgumentException('That offer has reached its usage limit.');
        $used = $this->pdo->prepare('SELECT 1 FROM discount_usage WHERE discount_id = :discount AND phone_normalized = :phone LIMIT 1');
        $used->execute(['discount' => $discount['id'], 'phone' => $phone]);
        if ($used->fetchColumn()) throw new InvalidArgumentException('That offer has already been used with this phone number.');
        if ((int) $discount['first_order_only'] === 1) {
            $priorOrder = $this->pdo->prepare('SELECT 1 FROM orders WHERE phone_normalized = :phone AND order_status <> "cancelled" LIMIT 1');
            $priorOrder->execute(['phone' => $phone]);
            if ($priorOrder->fetchColumn()) throw new InvalidArgumentException('This offer is only available on your first order.');
        }
        $amount = $discount['discount_type'] === 'percentage' ? $eligibleSubtotal * ((float) $discount['discount_value'] / 100) : (float) $discount['discount_value'];
        if ($discount['max_discount'] !== null) $amount = min($amount, (float) $discount['max_discount']);
        return ['id' => (int) $discount['id'], 'amount' => min($subtotal, $eligibleSubtotal, round($amount, 2))];
    }
}
