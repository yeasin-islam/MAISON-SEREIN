<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/services/CheckoutService.php';
require_once dirname(__DIR__) . '/services/OrderConfirmationEmailService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok' => false, 'message' => 'This action requires POST.'], 405);
$action = (string) ($_POST['action'] ?? '');
try {
    verify_csrf((string) ($_POST['csrf'] ?? ''));
    $pdo = database();

    switch ($action) {
        case 'cart.add':
        case 'cart.update': {
            $variantId = filter_var($_POST['variant_id'] ?? $_POST['product_variant_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
            if (!$variantId || !$quantity || $quantity < 1 || $quantity > 10) throw new InvalidArgumentException('Choose a quantity between 1 and 10.');
            $stmt = $pdo->prepare('SELECT v.stock_quantity, EXISTS(SELECT 1 FROM limited_drops d WHERE d.product_id = p.id AND d.status = "active" AND d.allow_preorder = 1 AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())) AS preorder_available FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = :id AND v.is_active = 1 AND p.is_active = 1 AND p.is_visible = 1');
            $stmt->execute(['id' => $variantId]);
            $availability = $stmt->fetch();
            if (!$availability) throw new InvalidArgumentException('That fragrance is no longer available.');
            if ($action === 'cart.add') $quantity += (int) ($_SESSION['cart'][$variantId] ?? 0);
            if ($quantity > 10 || ($quantity > (int) $availability['stock_quantity'] && !(int) $availability['preorder_available'])) throw new InvalidArgumentException('That quantity is not available.');
            $_SESSION['cart'][(int) $variantId] = $quantity;
            json_response(['ok' => true, 'message' => 'Your bag is up to date.', 'cart_count' => cart_count(), 'reload' => $action === 'cart.update']);
        }
        case 'cart.change-variant': {
            $oldId = filter_var($_POST['old_variant_id'] ?? null, FILTER_VALIDATE_INT);
            $newId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = (int) ($_SESSION['cart'][$oldId] ?? 0);
            if (!$oldId || !$newId || $quantity < 1) throw new InvalidArgumentException('That bag item could not be found.');
            $stmt = $pdo->prepare('SELECT v.stock_quantity, EXISTS(SELECT 1 FROM limited_drops d WHERE d.product_id = p.id AND d.status = "active" AND d.allow_preorder = 1 AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())) AS preorder_available FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = :id AND v.is_active = 1 AND p.is_active = 1 AND p.is_visible = 1');
            $stmt->execute(['id' => $newId]);
            $variant = $stmt->fetch();
            if (!$variant || ($quantity > (int) $variant['stock_quantity'] && !(int) $variant['preorder_available'])) throw new InvalidArgumentException('That size is not available in this quantity.');
            unset($_SESSION['cart'][$oldId]);
            $quantity += (int) ($_SESSION['cart'][$newId] ?? 0);
            if ($quantity > 10 || ($quantity > (int) $variant['stock_quantity'] && !(int) $variant['preorder_available'])) throw new InvalidArgumentException('That size is not available in this quantity.');
            $_SESSION['cart'][$newId] = $quantity;
            json_response(['ok' => true, 'message' => 'Size updated.', 'cart_count' => cart_count(), 'reload' => true]);
        }
        case 'cart.remove': {
            $variantId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
            unset($_SESSION['cart'][(int) $variantId]);
            json_response(['ok' => true, 'message' => 'Removed from your bag.', 'cart_count' => cart_count(), 'reload' => true]);
        }
        case 'wishlist.toggle': {
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$productId) throw new InvalidArgumentException('Choose a fragrance to save.');
            $check = $pdo->prepare('SELECT id FROM products WHERE id = :id AND is_active = 1 AND is_visible = 1');
            $check->execute(['id' => $productId]);
            if (!$check->fetchColumn()) throw new InvalidArgumentException('That fragrance is not available.');
            $customerId = current_customer_id();
            if ($customerId) {
                $find = $pdo->prepare('SELECT id FROM wishlist_items WHERE customer_id = :customer AND product_id = :product');
                $find->execute(['customer' => $customerId, 'product' => $productId]);
                if ($wishlistId = $find->fetchColumn()) {
                    $delete = $pdo->prepare('DELETE FROM wishlist_items WHERE id = :id');
                    $delete->execute(['id' => $wishlistId]);
                    json_response(['ok' => true, 'message' => 'Removed from your wishlist.', 'saved' => false]);
                }
                $save = $pdo->prepare('INSERT INTO wishlist_items (customer_id, product_id) VALUES (:customer, :product)');
                $save->execute(['customer' => $customerId, 'product' => $productId]);
            } else {
                $saved = array_map('intval', $_SESSION['wishlist'] ?? []);
                if (in_array($productId, $saved, true)) {
                    $_SESSION['wishlist'] = array_values(array_diff($saved, [$productId]));
                    json_response(['ok' => true, 'message' => 'Removed from your wishlist.', 'saved' => false]);
                }
                $saved[] = $productId;
                $_SESSION['wishlist'] = array_values(array_unique($saved));
            }
            json_response(['ok' => true, 'message' => 'Saved to your wishlist.', 'saved' => true]);
        }
        case 'checkout.create': {
            $service = new CheckoutService($pdo);
            $order = $service->placeOrder($_POST, $_SESSION['cart'] ?? [], current_customer_id());
            try {
                $emailResult = (new OrderConfirmationEmailService($pdo))->sendForOrder((int) $order['id']);
                $order['email_status'] = $emailResult['status'];
            } catch (Throwable $emailError) {
                error_log('[THE LAST NOTE BD] Could not process confirmation email for order ' . (int) $order['id'] . ' (' . get_class($emailError) . ').');
                $order['email_status'] = 'failed';
            }
            $_SESSION['last_order'] = $order;
            $_SESSION['checkout_token'] = bin2hex(random_bytes(16));
            $_SESSION['cart'] = [];
            unset($_SESSION['discount_code']);
            json_response(['ok' => true, 'message' => 'Your order is confirmed.', 'redirect' => '?page=success']);
        }
        case 'track.lookup': {
            $orderNumber = strtoupper(trim((string) ($_POST['order_number'] ?? '')));
            $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
            if (!preg_match('/^MS-[A-Z0-9-]{8,28}$/', $orderNumber) || !valid_phone($phone)) throw new InvalidArgumentException('Enter the order number and mobile number used at checkout.');
            $orderStmt = $pdo->prepare('SELECT id, order_number, customer_name, phone_normalized, order_status, payment_status, payment_method, grand_total, created_at FROM orders WHERE order_number = :number AND phone_normalized = :phone LIMIT 1');
            $orderStmt->execute(['number' => $orderNumber, 'phone' => $phone]);
            $order = $orderStmt->fetch();
            if (!$order) throw new InvalidArgumentException('We couldn’t match that order and mobile number. Please check both and try again.');
            $windowStmt = $pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key="customer_cancellation_window_hours" LIMIT 1');
            $windowStmt->execute();
            $windowValue = $windowStmt->fetchColumn();
            $windowHours = max(0, min(168, $windowValue === false ? 24 : (int) $windowValue));
            $order['can_cancel'] = in_array($order['order_status'], ['pending', 'confirmed'], true) && time() - strtotime($order['created_at']) <= $windowHours * 3600;
            $itemStmt = $pdo->prepare('SELECT product_name, size_label, quantity, line_total FROM order_items WHERE order_id = :id ORDER BY id');
            $itemStmt->execute(['id' => $order['id']]);
            $historyStmt = $pdo->prepare('SELECT status, note, created_at FROM order_status_history WHERE order_id = :id ORDER BY created_at, id');
            $historyStmt->execute(['id' => $order['id']]);
            $order['items'] = $itemStmt->fetchAll();
            $order['history'] = $historyStmt->fetchAll();
            unset($order['phone_normalized'], $order['id']);
            json_response(['ok' => true, 'message' => 'We found your order.', 'order' => $order]);
        }
        case 'track.cancel': {
            $orderNumber = strtoupper(trim((string) ($_POST['order_number'] ?? '')));
            $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
            if (!preg_match('/^MS-[A-Z0-9-]{8,28}$/', $orderNumber) || !valid_phone($phone)) throw new InvalidArgumentException('We could not verify that order and mobile number.');
            $pdo->beginTransaction();
            try {
                $find = $pdo->prepare('SELECT id, order_status, created_at FROM orders WHERE order_number=:number AND phone_normalized=:phone FOR UPDATE');
                $find->execute(['number' => $orderNumber, 'phone' => $phone]);
                $order = $find->fetch();
                if (!$order) throw new InvalidArgumentException('We could not verify that order and mobile number.');
                $windowStmt = $pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key="customer_cancellation_window_hours" LIMIT 1');
                $windowStmt->execute();
                $windowValue = $windowStmt->fetchColumn();
                $windowHours = max(0, min(168, $windowValue === false ? 24 : (int) $windowValue));
                $ageSeconds = time() - strtotime($order['created_at']);
                if (!in_array($order['order_status'], ['pending', 'confirmed'], true) || $ageSeconds > $windowHours * 3600) throw new InvalidArgumentException('This order is no longer eligible for customer cancellation. Please contact customer care if you need help.');
                $update = $pdo->prepare('UPDATE orders SET order_status="cancelled", cancellation_note="Cancelled by customer", updated_at=NOW() WHERE id=:id');
                $update->execute(['id' => $order['id']]);
                $restoreSetting = $pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key="stock_restore_on_cancellation" LIMIT 1');
                $restoreSetting->execute();
                $restoreValue = $restoreSetting->fetchColumn();
                if ($restoreValue === false || (string) $restoreValue === '1') {
                    $stockMoves = $pdo->prepare('SELECT variant_id, SUM(quantity_change) AS quantity_change FROM inventory_transactions WHERE order_id=:id AND reason="order" GROUP BY variant_id');
                    $stockMoves->execute(['id' => $order['id']]);
                    foreach ($stockMoves->fetchAll() as $move) {
                    $quantity = abs((int) $move['quantity_change']);
                    if ($quantity < 1) continue;
                    $restore = $pdo->prepare('UPDATE product_variants SET stock_quantity=stock_quantity+:quantity, updated_at=NOW() WHERE id=:id');
                    $restore->execute(['quantity' => $quantity, 'id' => $move['variant_id']]);
                    $audit = $pdo->prepare('INSERT INTO inventory_transactions (variant_id, order_id, quantity_change, reason, note) VALUES (:variant, :order, :quantity, "cancellation", "Customer order cancellation stock restoration")');
                        $audit->execute(['variant' => $move['variant_id'], 'order' => $order['id'], 'quantity' => $quantity]);
                    }
                }
                $items = $pdo->prepare('SELECT product_id, SUM(quantity) AS quantity FROM order_items WHERE order_id=:id GROUP BY product_id');
                $items->execute(['id' => $order['id']]);
                foreach ($items->fetchAll() as $item) {
                    if ($item['product_id']) {
                        $sales = $pdo->prepare('UPDATE products SET sales_count=GREATEST(sales_count-:quantity,0) WHERE id=:id');
                        $sales->execute(['quantity' => $item['quantity'], 'id' => $item['product_id']]);
                    }
                }
                $preorders = $pdo->prepare('UPDATE preorders SET status="cancelled" WHERE order_id=:order AND status="reserved"');
                $preorders->execute(['order' => $order['id']]);
                $cancelPayments = $pdo->prepare('UPDATE payments SET status="rejected", admin_note="Order cancelled before payment verification", verified_at=NOW() WHERE order_id=:order AND status="pending_verification"');
                $cancelPayments->execute(['order' => $order['id']]);
                if ($cancelPayments->rowCount()) $pdo->prepare('UPDATE orders SET payment_status="rejected" WHERE id=:id')->execute(['id' => $order['id']]);
                $usage = $pdo->prepare('SELECT discount_id FROM discount_usage WHERE order_id=:id');
                $usage->execute(['id' => $order['id']]);
                if ($discountId = $usage->fetchColumn()) {
                    $pdo->prepare('DELETE FROM discount_usage WHERE order_id=:id')->execute(['id' => $order['id']]);
                    $pdo->prepare('UPDATE discounts SET usage_count=GREATEST(usage_count-1,0) WHERE id=:id')->execute(['id' => $discountId]);
                }
                $history = $pdo->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (:order,"cancelled","Cancelled by customer")');
                $history->execute(['order' => $order['id']]);
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            json_response(['ok' => true, 'message' => 'Your order has been cancelled.', 'order_status' => 'cancelled']);
        }
        case 'customer.register': {
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if (mb_strlen($name) < 2 || mb_strlen($name) > 120) throw new InvalidArgumentException('Enter your full name.');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) throw new InvalidArgumentException('Enter a valid email address.');
            if (!valid_phone($phone)) throw new InvalidArgumentException('Enter a valid Bangladeshi mobile number.');
            if (strlen($password) < 10 || strlen($password) > 200) throw new InvalidArgumentException('Choose a password with at least 10 characters.');
            $stmt = $pdo->prepare('INSERT INTO customers (full_name, email, phone, phone_normalized, password_hash, is_active) VALUES (:name, :email, :phone, :normalized, :hash, 1)');
            try {
                $stmt->execute(['name' => $name, 'email' => $email, 'phone' => trim((string) $_POST['phone']), 'normalized' => $phone, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
            } catch (PDOException $error) {
                if ($error->getCode() === '23000') throw new InvalidArgumentException('That email address or mobile number is already registered. Sign in or use different details.');
                throw $error;
            }
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['customer_id'] = (int) $pdo->lastInsertId();
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_phone'] = $phone;
            json_response(['ok' => true, 'message' => 'Your account is ready.', 'redirect' => '?page=account']);
        }
        case 'customer.login': {
            $identifier = trim((string) ($_POST['identifier'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $now = time();
            $attempts = $_SESSION['customer_login_attempts'] ?? ['count' => 0, 'started' => $now];
            if ($now - (int) $attempts['started'] > 900) $attempts = ['count' => 0, 'started' => $now];
            if ((int) $attempts['count'] >= 8) throw new InvalidArgumentException('Too many sign-in attempts. Please wait 15 minutes and try again.');
            $email = strtolower($identifier);
            $phone = normalize_phone($identifier);
            $stmt = $pdo->prepare('SELECT id, full_name, email, phone_normalized, password_hash FROM customers WHERE is_active = 1 AND (email = :email OR phone_normalized = :phone) LIMIT 1');
            $stmt->execute(['email' => $email, 'phone' => $phone]);
            $customer = $stmt->fetch();
            if (!$customer || !password_verify($password, $customer['password_hash'])) {
                $attempts['count']++;
                $_SESSION['customer_login_attempts'] = $attempts;
                throw new InvalidArgumentException('The email/phone or password didn’t match.');
            }
            unset($_SESSION['customer_login_attempts']);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['customer_id'] = (int) $customer['id'];
            $_SESSION['customer_name'] = $customer['full_name'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_phone'] = $customer['phone_normalized'];
            json_response(['ok' => true, 'message' => 'Welcome back.', 'redirect' => '?page=account']);
        }
        case 'customer.profile.update': {
            $customerId = current_customer_id();
            if (!$customerId) json_response(['ok' => false, 'message' => 'Sign in to update your account.'], 401);
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 120) throw new InvalidArgumentException('Enter your full name.');
            if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190)) throw new InvalidArgumentException('Enter a valid email address.');
            if (!valid_phone($phone)) throw new InvalidArgumentException('Enter a valid Bangladeshi mobile number.');
            $stmt = $pdo->prepare('UPDATE customers SET full_name=:name, email=:email, phone=:phone_raw, phone_normalized=:phone WHERE id=:id AND is_active=1');
            try {
                $stmt->execute(['name' => $name, 'email' => $email ?: null, 'phone_raw' => trim((string) $_POST['phone']), 'phone' => $phone, 'id' => $customerId]);
            } catch (PDOException $error) {
                if ($error->getCode() === '23000') throw new InvalidArgumentException('That email address or mobile number is already used by another account.');
                throw $error;
            }
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_phone'] = $phone;
            json_response(['ok' => true, 'message' => 'Your account details are saved.', 'reload' => true]);
        }
        case 'customer.logout': {
            unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email'], $_SESSION['customer_phone']);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            json_response(['ok' => true, 'message' => 'You have signed out.', 'redirect' => '?page=home']);
        }
        case 'discount.apply': {
            $code = strtoupper(trim((string) ($_POST['discount_code'] ?? '')));
            if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) throw new InvalidArgumentException('Enter a valid offer code.');
            $stmt = $pdo->prepare('SELECT id FROM discounts WHERE code = :code AND is_active = 1 AND starts_at <= NOW() AND ends_at >= NOW() LIMIT 1');
            $stmt->execute(['code' => $code]);
            if (!$stmt->fetchColumn()) throw new InvalidArgumentException('That offer code is not active.');
            $_SESSION['discount_code'] = $code;
            json_response(['ok' => true, 'message' => 'Offer code saved. Eligibility is checked with your delivery details at checkout.']);
        }
        case 'discount.remove': {
            unset($_SESSION['discount_code']);
            json_response(['ok' => true, 'message' => 'Offer code removed.']);
        }
        case 'checkout.quote': {
            $service = new CheckoutService($pdo);
            $code = strtoupper(trim((string) ($_POST['discount_code'] ?? ($_SESSION['discount_code'] ?? ''))));
            $quote = $service->quote($_POST, $_SESSION['cart'] ?? [], current_customer_id());
            if ($quote['coupon_error'] !== '') {
                unset($_SESSION['discount_code']);
            } elseif ($quote['coupon_code'] !== '') {
                $_SESSION['discount_code'] = $quote['coupon_code'];
            } else {
                unset($_SESSION['discount_code']);
            }
            $message = $quote['coupon_error'] !== ''
                ? $quote['coupon_error']
                : ($quote['coupon_code'] !== '' ? 'Offer applied.' : 'Checkout updated.');
            json_response(['ok' => true, 'message' => $message, 'quote' => $quote]);
        }
        case 'customer.address.save': {
            $customerId = current_customer_id();
            if (!$customerId) json_response(['ok' => false, 'message' => 'Sign in to save an address.'], 401);
            $label = trim((string) ($_POST['label'] ?? ''));
            $recipient = trim((string) ($_POST['recipient_name'] ?? ''));
            $phone = trim((string) ($_POST['phone'] ?? ''));
            $normalized = normalize_phone($phone);
            $district = trim((string) ($_POST['district'] ?? ''));
            $address = trim((string) ($_POST['address'] ?? ''));
            $addressId = filter_var($_POST['address_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $makeDefault = !empty($_POST['is_default']);
            if (mb_strlen($label) < 2 || mb_strlen($label) > 50 || mb_strlen($recipient) < 2 || mb_strlen($recipient) > 120 || !valid_phone($normalized) || mb_strlen($district) < 2 || mb_strlen($district) > 120 || mb_strlen($address) < 8 || mb_strlen($address) > 500) throw new InvalidArgumentException('Check the address label, recipient, phone, district, and full address.');
            $count = $pdo->prepare('SELECT COUNT(*) FROM customer_addresses WHERE customer_id = :customer');
            $count->execute(['customer' => $customerId]);
            $addressCount = (int) $count->fetchColumn();
            if (!$addressId && $addressCount >= 10) throw new InvalidArgumentException('You can save up to 10 addresses.');
            $pdo->beginTransaction();
            if ($makeDefault || $addressCount === 0) {
                $pdo->prepare('UPDATE customer_addresses SET is_default = 0 WHERE customer_id = :customer')->execute(['customer' => $customerId]);
                $makeDefault = true;
            }
            if ($addressId) {
                $stmt = $pdo->prepare('UPDATE customer_addresses SET label = :label, recipient_name = :recipient, phone = :phone, phone_normalized = :normalized, district = :district, address = :address, is_default = :default WHERE id = :id AND customer_id = :customer');
                $stmt->execute(['label' => $label, 'recipient' => $recipient, 'phone' => $phone, 'normalized' => $normalized, 'district' => $district, 'address' => $address, 'default' => (int) $makeDefault, 'id' => $addressId, 'customer' => $customerId]);
                if ($stmt->rowCount() < 1) throw new InvalidArgumentException('That saved address could not be found.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO customer_addresses (customer_id, label, recipient_name, phone, phone_normalized, district, address, is_default) VALUES (:customer, :label, :recipient, :phone, :normalized, :district, :address, :default)');
                $stmt->execute(['customer' => $customerId, 'label' => $label, 'recipient' => $recipient, 'phone' => $phone, 'normalized' => $normalized, 'district' => $district, 'address' => $address, 'default' => (int) $makeDefault]);
            }
            $pdo->commit();
            json_response(['ok' => true, 'message' => 'Address saved.', 'reload' => true]);
        }
        case 'customer.address.delete': {
            $customerId = current_customer_id();
            if (!$customerId) json_response(['ok' => false, 'message' => 'Sign in to manage addresses.'], 401);
            $addressId = filter_var($_POST['address_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$addressId) throw new InvalidArgumentException('Choose a saved address.');
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('DELETE FROM customer_addresses WHERE id = :id AND customer_id = :customer');
            $stmt->execute(['id' => $addressId, 'customer' => $customerId]);
            if ($stmt->rowCount() !== 1) throw new InvalidArgumentException('That saved address could not be found.');
            $pdo->prepare('UPDATE customer_addresses SET is_default = 1 WHERE customer_id = :customer ORDER BY id ASC LIMIT 1')->execute(['customer' => $customerId]);
            $pdo->commit();
            json_response(['ok' => true, 'message' => 'Address removed.', 'reload' => true]);
        }
        case 'customer.address.default': {
            $customerId = current_customer_id();
            if (!$customerId) json_response(['ok' => false, 'message' => 'Sign in to manage addresses.'], 401);
            $addressId = filter_var($_POST['address_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$addressId) throw new InvalidArgumentException('Choose a saved address.');
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE customer_addresses SET is_default = 0 WHERE customer_id = :customer')->execute(['customer' => $customerId]);
            $stmt = $pdo->prepare('UPDATE customer_addresses SET is_default = 1 WHERE id = :id AND customer_id = :customer');
            $stmt->execute(['id' => $addressId, 'customer' => $customerId]);
            if ($stmt->rowCount() !== 1) throw new InvalidArgumentException('That saved address could not be found.');
            $pdo->commit();
            json_response(['ok' => true, 'message' => 'Default address updated.', 'reload' => true]);
        }
        case 'newsletter.subscribe': {
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
            $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email, status) VALUES (:email, "subscribed") ON DUPLICATE KEY UPDATE status = "subscribed"');
            $stmt->execute(['email' => $email]);
            json_response(['ok' => true, 'message' => 'You’re on the list. Thank you.']);
        }
        case 'review.submit': {
            $customerId = current_customer_id();
            if (!$customerId) json_response(['ok' => false, 'message' => 'Sign in to leave a note.', 'redirect' => '?page=login'], 401);
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
            $body = trim((string) ($_POST['review_body'] ?? ''));
            if (!$productId || !$rating || $rating < 1 || $rating > 5 || mb_strlen($body) < 8 || mb_strlen($body) > 2000) throw new InvalidArgumentException('Choose a rating and write a note of at least 8 characters.');
            $purchase = $pdo->prepare('SELECT 1 FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.customer_id = :customer AND oi.product_id = :product AND o.order_status = "delivered" LIMIT 1');
            $purchase->execute(['customer' => $customerId, 'product' => $productId]);
            if (!$purchase->fetchColumn()) throw new InvalidArgumentException('A fragrance review is available after a delivered purchase.');
            $insert = $pdo->prepare('INSERT INTO reviews (product_id, customer_id, customer_name, rating, review_title, review_body, status) SELECT :product, c.id, c.full_name, :rating, :title, :body, "pending" FROM customers c WHERE c.id = :customer');
            $insert->execute(['product' => $productId, 'rating' => $rating, 'title' => trim((string) ($_POST['review_title'] ?? '')) ?: null, 'body' => $body, 'customer' => $customerId]);
            json_response(['ok' => true, 'message' => 'Your note has been sent for review.']);
        }
        case 'admin.login': {
            $now = time();
            $attempts = $_SESSION['admin_login_attempts'] ?? ['count' => 0, 'started' => $now];
            if ($now - (int) $attempts['started'] > 900) $attempts = ['count' => 0, 'started' => $now];
            if ((int) $attempts['count'] >= 6) throw new InvalidArgumentException('Too many attempts. Wait 15 minutes before trying again.');
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch();
            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                $attempts['count']++;
                $_SESSION['admin_login_attempts'] = $attempts;
                throw new InvalidArgumentException('Email or password did not match.');
            }
            unset($_SESSION['admin_login_attempts']);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_last_activity'] = time();
            $update = $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
            $update->execute(['id' => $admin['id']]);
            json_response(['ok' => true, 'message' => 'Welcome to the studio.', 'redirect' => 'index.php?page=dashboard']);
        }
        case 'admin.logout': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => true, 'redirect' => '../index.php']);
            unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_login_attempts'], $_SESSION['admin_last_activity']);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            json_response(['ok' => true, 'message' => 'You have signed out.', 'redirect' => '../index.php']);
        }
        case 'admin.product.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $name = trim((string) ($_POST['name'] ?? ''));
            $brandId = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
            $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $gender = (string) ($_POST['gender'] ?? 'unisex');
            $description = trim((string) ($_POST['description'] ?? ''));
            $short = trim((string) ($_POST['short_description'] ?? ''));
            $family = trim((string) ($_POST['fragrance_family'] ?? ''));
            $size = filter_var($_POST['size_ml'] ?? null, FILTER_VALIDATE_FLOAT);
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock_quantity'] ?? 0, FILTER_VALIDATE_INT);
            if (mb_strlen($name) < 2 || mb_strlen($name) > 180 || !$brandId || !in_array($gender, ['women', 'men', 'unisex'], true)) throw new InvalidArgumentException('Complete the product name, house, and audience.');
            if ($description === '' || mb_strlen($description) > 5000 || $size === false || $size < 1 || $size > 500 || $price === false || $price < 0 || $price > 10000000 || $stock === false || $stock < 0 || $stock > 100000) throw new InvalidArgumentException('Enter a valid description, size, price, and stock quantity.');
            $brandCheck = $pdo->prepare('SELECT id FROM brands WHERE id = :id AND is_active = 1');
            $brandCheck->execute(['id' => $brandId]);
            if (!$brandCheck->fetchColumn()) throw new InvalidArgumentException('Choose an active fragrance house.');
            if ($categoryId) {
                $categoryCheck = $pdo->prepare('SELECT id FROM categories WHERE id = :id AND is_active = 1');
                $categoryCheck->execute(['id' => $categoryId]);
                if (!$categoryCheck->fetchColumn()) throw new InvalidArgumentException('Choose an active collection.');
            }
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name), '-'));
            $pdo->beginTransaction();
            $uploadedFiles = [];
            try {
                if ($id) {
                    $update = $pdo->prepare('UPDATE products SET brand_id = :brand, category_id = :category, name = :name, gender = :gender, short_description = :short, description = :description, fragrance_family = :family, top_notes = :top, heart_notes = :heart, base_notes = :base, is_featured = :featured, is_best_seller = :best_seller, is_new_arrival = :new_arrival WHERE id = :id');
                    $update->execute(['brand' => $brandId, 'category' => $categoryId, 'name' => $name, 'gender' => $gender, 'short' => $short ?: null, 'description' => $description, 'family' => $family ?: null, 'top' => trim((string) ($_POST['top_notes'] ?? '')) ?: null, 'heart' => trim((string) ($_POST['heart_notes'] ?? '')) ?: null, 'base' => trim((string) ($_POST['base_notes'] ?? '')) ?: null, 'featured' => !empty($_POST['is_featured']) ? 1 : 0, 'best_seller' => !empty($_POST['is_best_seller']) ? 1 : 0, 'new_arrival' => !empty($_POST['is_new_arrival']) ? 1 : 0, 'id' => $id]);
                    if (!$update->rowCount()) {
                        $exists = $pdo->prepare('SELECT id FROM products WHERE id = :id');
                        $exists->execute(['id' => $id]);
                        if (!$exists->fetchColumn()) throw new InvalidArgumentException('Product not found.');
                    }
                    $variantId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
                    if ($variantId) {
                        $variant = $pdo->prepare('UPDATE product_variants SET size_ml = :size, size_label = :label, price = :price WHERE id = :variant AND product_id = :product');
                        $variant->execute(['size' => $size, 'label' => rtrim(rtrim(number_format($size, 2, '.', ''), '0'), '.') . ' ml', 'price' => $price, 'variant' => $variantId, 'product' => $id]);
                    }
                    $savedId = $id;
                } else {
                    $uniqueSlug = $slug . '-' . strtolower(bin2hex(random_bytes(2)));
                    $insert = $pdo->prepare('INSERT INTO products (brand_id, category_id, name, slug, gender, short_description, description, fragrance_family, top_notes, heart_notes, base_notes, is_featured, is_best_seller, is_new_arrival) VALUES (:brand, :category, :name, :slug, :gender, :short, :description, :family, :top, :heart, :base, :featured, :best_seller, :new_arrival)');
                    $insert->execute(['brand' => $brandId, 'category' => $categoryId, 'name' => $name, 'slug' => $uniqueSlug, 'gender' => $gender, 'short' => $short ?: null, 'description' => $description, 'family' => $family ?: null, 'top' => trim((string) ($_POST['top_notes'] ?? '')) ?: null, 'heart' => trim((string) ($_POST['heart_notes'] ?? '')) ?: null, 'base' => trim((string) ($_POST['base_notes'] ?? '')) ?: null, 'featured' => !empty($_POST['is_featured']) ? 1 : 0, 'best_seller' => !empty($_POST['is_best_seller']) ? 1 : 0, 'new_arrival' => !empty($_POST['is_new_arrival']) ? 1 : 0]);
                    $savedId = (int) $pdo->lastInsertId();
                    $sku = 'MS-' . strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $slug), 0, 10)) . '-' . strtoupper(bin2hex(random_bytes(2)));
                    $variant = $pdo->prepare('INSERT INTO product_variants (product_id, sku, size_ml, size_label, price, stock_quantity) VALUES (:product, :sku, :size, :label, :price, :stock)');
                    $variant->execute(['product' => $savedId, 'sku' => $sku, 'size' => $size, 'label' => rtrim(rtrim(number_format($size, 2, '.', ''), '0'), '.') . ' ml', 'price' => $price, 'stock' => $stock]);
                    if ($stock > 0) {
                        $audit = $pdo->prepare('INSERT INTO inventory_transactions (variant_id, quantity_change, reason, note, created_by) VALUES (:variant, :quantity, "restock", "Initial stock", :admin)');
                        $audit->execute(['variant' => $pdo->lastInsertId(), 'quantity' => $stock, 'admin' => $_SESSION['admin_id']]);
                    }
                }
                $files = $_FILES['images'] ?? null;
                if (is_array($files) && isset($files['name']) && is_array($files['name'])) {
                    if (count($files['name']) > 5) throw new InvalidArgumentException('Upload no more than five images at a time.');
                    $uploadDir = dirname(__DIR__) . '/assets/uploads/products';
                    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) throw new RuntimeException('The product image directory is unavailable.');
                    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    if (!$finfo) throw new RuntimeException('Image validation is unavailable.');
                    $imageInsert = $pdo->prepare('INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES (:product, :path, :alt, :sort)');
                    $nextOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = :id');
                    $nextOrderStmt->execute(['id' => $savedId]);
                    $imageOrder = (int) $nextOrderStmt->fetchColumn();
                    foreach ($files['name'] as $index => $originalName) {
                        $errorCode = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                        if ($errorCode === UPLOAD_ERR_NO_FILE) continue;
                        $temporaryPath = (string) ($files['tmp_name'][$index] ?? '');
                        if ($errorCode !== UPLOAD_ERR_OK || $temporaryPath === '' || !is_uploaded_file($temporaryPath) || filesize($temporaryPath) > 5242880) throw new InvalidArgumentException('Each image must be a valid upload no larger than 5 MB.');
                        $mime = finfo_file($finfo, $temporaryPath);
                        $imageInfo = getimagesize($temporaryPath);
                        if (!isset($allowedMimes[$mime]) || !$imageInfo || ($imageInfo['mime'] ?? '') !== $mime) throw new InvalidArgumentException('Use only valid JPG, PNG, or WebP product images.');
                        $filename = bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];
                        $absolutePath = $uploadDir . '/' . $filename;
                        if (!move_uploaded_file($temporaryPath, $absolutePath)) throw new RuntimeException('A product image could not be saved.');
                        $uploadedFiles[] = $absolutePath;
                        $relativePath = 'assets/uploads/products/' . $filename;
                        $imageInsert->execute(['product' => $savedId, 'path' => $relativePath, 'alt' => $name . ' perfume bottle', 'sort' => $imageOrder++]);
                    }
                    finfo_close($finfo);
                }
                $log = $pdo->prepare('INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (:admin, :action, "product", :entity, :details, :ip)');
                $log->execute(['admin' => $_SESSION['admin_id'], 'action' => $id ? 'product.updated' : 'product.created', 'entity' => $savedId, 'details' => json_encode(['name' => $name]), 'ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
                $pdo->commit();
            } catch (InvalidArgumentException $error) {
                json_response(['ok' => false, 'message' => $error->getMessage()], 422);
            } catch (PDOException $error) {
                error_log('[THE LAST NOTE BD] Database request failed: ' . $error->getMessage());
                json_response(['ok' => false, 'message' => 'Something went wrong. Please try again.'], 500);
            } catch (RuntimeException $error) {
                if ($error->getMessage() === 'Your session expired. Refresh the page and try again.') {
                    json_response(['ok' => false, 'message' => $error->getMessage()], 419);
                }
                error_log('[THE LAST NOTE BD] Request failed: ' . $error->getMessage());
                json_response(['ok' => false, 'message' => 'Something went wrong. Please try again.'], 500);
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                foreach ($uploadedFiles as $uploadedFile) if (is_file($uploadedFile)) unlink($uploadedFile);
                throw $error;
            }
            json_response(['ok' => true, 'message' => $id ? 'Product updated.' : 'Product created.', 'redirect' => 'index.php?page=products']);
        }
        case 'admin.product.visibility': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $visible = filter_var($_POST['is_visible'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($visible, [0, 1], true)) throw new InvalidArgumentException('Choose a valid visibility state.');
            $stmt = $pdo->prepare('UPDATE products SET is_visible = :visible WHERE id = :id');
            $stmt->execute(['visible' => $visible, 'id' => $id]);
            json_response(['ok' => true, 'message' => $visible ? 'Product is visible.' : 'Product hidden from the storefront.', 'reload' => true]);
        }
        case 'admin.product.active': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid product state.');
            $stmt = $pdo->prepare('UPDATE products SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Product not found or unchanged.');
            json_response(['ok' => true, 'message' => $active ? 'Product activated.' : 'Product deactivated.', 'reload' => true]);
        }
        case 'admin.variant.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $variantId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $size = filter_var($_POST['size_ml'] ?? null, FILTER_VALIDATE_FLOAT);
            $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock_quantity'] ?? 0, FILTER_VALIDATE_INT);
            if (!$productId || $size === false || $size < 1 || $size > 500 || $price === false || $price < 0 || $price > 10000000 || $stock === false || $stock < 0 || $stock > 100000) throw new InvalidArgumentException('Enter a valid size, price, and opening stock.');
            $productCheck = $pdo->prepare('SELECT name FROM products WHERE id=:id');
            $productCheck->execute(['id' => $productId]);
            $productName = $productCheck->fetchColumn();
            if (!$productName) throw new InvalidArgumentException('Product not found.');
            $duplicate = $pdo->prepare('SELECT id FROM product_variants WHERE product_id=:product AND size_ml=:size AND id<>:variant LIMIT 1');
            $duplicate->execute(['product' => $productId, 'size' => $size, 'variant' => $variantId ?: 0]);
            if ($duplicate->fetchColumn()) throw new InvalidArgumentException('That size already exists for this fragrance.');
            $label = rtrim(rtrim(number_format($size, 2, '.', ''), '0'), '.') . ' ml';
            if ($variantId) {
                $stmt = $pdo->prepare('UPDATE product_variants SET size_ml=:size, size_label=:label, price=:price WHERE id=:id AND product_id=:product');
                $stmt->execute(['size' => $size, 'label' => $label, 'price' => $price, 'id' => $variantId, 'product' => $productId]);
                if (!$stmt->rowCount()) throw new InvalidArgumentException('Variant not found or unchanged.');
            } else {
                $skuBase = 'MS-' . strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', (string) $productName), 0, 10));
                $sku = $skuBase . '-' . strtoupper(bin2hex(random_bytes(4)));
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('INSERT INTO product_variants (product_id, sku, size_ml, size_label, price, stock_quantity) VALUES (:product, :sku, :size, :label, :price, :stock)');
                    $stmt->execute(['product' => $productId, 'sku' => $sku, 'size' => $size, 'label' => $label, 'price' => $price, 'stock' => $stock]);
                    $newVariantId = (int) $pdo->lastInsertId();
                    if ($stock > 0) {
                        $audit = $pdo->prepare('INSERT INTO inventory_transactions (variant_id, quantity_change, reason, note, created_by) VALUES (:variant, :quantity, "restock", "Initial variant stock", :admin)');
                        $audit->execute(['variant' => $newVariantId, 'quantity' => $stock, 'admin' => $_SESSION['admin_id']]);
                    }
                    $pdo->commit();
                } catch (Throwable $error) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $error;
                }
            }
            json_response(['ok' => true, 'message' => $variantId ? 'Variant updated.' : 'Variant added.', 'reload' => true]);
        }
        case 'admin.variant.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid variant state.');
            $variantInfo = $pdo->prepare('SELECT product_id, size_ml FROM product_variants WHERE id=:id');
            $variantInfo->execute(['id' => $id]);
            $currentVariant = $variantInfo->fetch();
            if (!$currentVariant) throw new InvalidArgumentException('Variant not found.');
            if ($active) {
                $duplicate = $pdo->prepare('SELECT id FROM product_variants WHERE product_id=:product AND size_ml=:size AND is_active=1 AND id<>:id LIMIT 1');
                $duplicate->execute(['product' => $currentVariant['product_id'], 'size' => $currentVariant['size_ml'], 'id' => $id]);
                if ($duplicate->fetchColumn()) throw new InvalidArgumentException('Another active variant already uses this size.');
            }
            $stmt = $pdo->prepare('UPDATE product_variants SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Variant state did not change.');
            json_response(['ok' => true, 'message' => $active ? 'Variant activated.' : 'Variant deactivated.', 'reload' => true]);
        }
        case 'admin.discount.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $discountIdRaw = trim((string) ($_POST['discount_id'] ?? ''));
            $discountId = $discountIdRaw !== '' ? filter_var($discountIdRaw, FILTER_VALIDATE_INT) : null;
            $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
            $type = (string) ($_POST['discount_type'] ?? '');
            $value = filter_var($_POST['discount_value'] ?? null, FILTER_VALIDATE_FLOAT);
            $minimum = filter_var($_POST['minimum_purchase'] ?? 0, FILTER_VALIDATE_FLOAT);
            $max = filter_var($_POST['max_discount'] ?? null, FILTER_VALIDATE_FLOAT);
            $starts = trim((string) ($_POST['starts_at'] ?? ''));
            $ends = trim((string) ($_POST['ends_at'] ?? ''));
            $startTime = strtotime($starts);
            $endTime = strtotime($ends);
            $usageLimit = filter_var($_POST['usage_limit'] ?? null, FILTER_VALIDATE_INT);
            $productIdRaw = trim((string) ($_POST['product_id'] ?? ''));
            $productId = $productIdRaw !== '' ? filter_var($productIdRaw, FILTER_VALIDATE_INT) : null;
            if ($discountIdRaw !== '' && !$discountId) throw new InvalidArgumentException('Choose a valid offer to update.');
            if ($productIdRaw !== '' && !$productId) throw new InvalidArgumentException('Choose a valid product for this offer.');
            if ($productId) {
                $productCheck = $pdo->prepare('SELECT id FROM products WHERE id=:id AND is_active=1');
                $productCheck->execute(['id' => $productId]);
                if (!$productCheck->fetchColumn()) throw new InvalidArgumentException('Choose an active product for this offer.');
            }
            if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $code) || !in_array($type, ['percentage', 'fixed'], true) || $value === false || $value <= 0 || $value > ($type === 'percentage' ? 100 : 1000000) || $minimum === false || $minimum < 0 || $max === false && trim((string) ($_POST['max_discount'] ?? '')) !== '' || $max !== false && $max < 0 || !$startTime || !$endTime || $endTime <= $startTime || trim((string) ($_POST['usage_limit'] ?? '')) !== '' && ($usageLimit === false || $usageLimit < 1)) throw new InvalidArgumentException('Enter a unique code, valid discount, minimum, usage limit, and end date.');
            $data = ['code' => $code, 'product' => $productId, 'type' => $type, 'value' => $value, 'minimum' => $minimum, 'maximum' => $max === false || $max <= 0 ? null : $max, 'first' => !empty($_POST['first_order_only']) ? 1 : 0, 'starts' => date('Y-m-d H:i:s', $startTime), 'ends' => date('Y-m-d H:i:s', $endTime), 'usage_limit' => trim((string) ($_POST['usage_limit'] ?? '')) === '' ? null : $usageLimit];
            if ($discountId) {
                $usageCheck = $pdo->prepare('SELECT usage_count FROM discounts WHERE id=:id');
                $usageCheck->execute(['id' => $discountId]);
                $usageCount = $usageCheck->fetchColumn();
                if ($usageCount === false) throw new InvalidArgumentException('Offer not found.');
                if ($data['usage_limit'] !== null && $data['usage_limit'] < (int) $usageCount) throw new InvalidArgumentException('The usage limit cannot be lower than the number of redemptions already recorded.');
                $stmt = $pdo->prepare('UPDATE discounts SET code=:code, product_id=:product, discount_type=:type, discount_value=:value, minimum_purchase=:minimum, max_discount=:maximum, first_order_only=:first, starts_at=:starts, ends_at=:ends, usage_limit=:usage_limit WHERE id=:id');
                $stmt->execute($data + ['id' => $discountId]);
                json_response(['ok' => true, 'message' => 'Offer code updated.', 'reload' => true]);
            }
            $stmt = $pdo->prepare('INSERT INTO discounts (code, product_id, discount_type, discount_value, minimum_purchase, max_discount, first_order_only, starts_at, ends_at, usage_limit, is_active) VALUES (:code, :product, :type, :value, :minimum, :maximum, :first, :starts, :ends, :usage_limit, 1)');
            $stmt->execute($data);
            json_response(['ok' => true, 'message' => 'Offer code created.', 'redirect' => 'index.php?page=discounts']);
        }
        case 'admin.discount.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['discount_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid offer state.');
            $stmt = $pdo->prepare('UPDATE discounts SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Offer not found or unchanged.');
            json_response(['ok' => true, 'message' => $active ? 'Offer activated.' : 'Offer paused.', 'reload' => true]);
        }
        case 'admin.setting.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $allowed = ['store_phone', 'store_email', 'whatsapp_number', 'bkash_number', 'nagad_number', 'bkash_instructions', 'nagad_instructions', 'cancellation_policy', 'customer_cancellation_window_hours', 'gift_mode_enabled', 'stock_restore_on_cancellation'];
            $stmt = $pdo->prepare('INSERT INTO store_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($allowed as $key) {
                if (!array_key_exists($key, $_POST)) continue;
                $value = trim((string) $_POST[$key]);
                if (mb_strlen($value) > 500) throw new InvalidArgumentException('A store setting is too long.');
                if ($key === 'store_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid store email address.');
                if ($key === 'customer_cancellation_window_hours' && (!ctype_digit($value) || (int) $value > 168)) throw new InvalidArgumentException('Set a cancellation window between 0 and 168 hours.');
                if (in_array($key, ['gift_mode_enabled', 'stock_restore_on_cancellation'], true) && !in_array($value, ['0', '1'], true)) throw new InvalidArgumentException('Choose a valid store setting.');
                $stmt->execute(['key' => $key, 'value' => $value]);
            }
            if (array_key_exists('accepted_payment_methods', $_POST)) {
                $methods = array_values(array_intersect((array) $_POST['accepted_payment_methods'], ['bkash', 'nagad', 'cod']));
                if (!$methods) throw new InvalidArgumentException('Keep at least one payment method available.');
                $stmt->execute(['key' => 'accepted_payment_methods', 'value' => json_encode($methods)]);
            }
            json_response(['ok' => true, 'message' => 'Store settings saved.']);
        }
        case 'admin.profile.update': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $name = trim((string) ($_POST['full_name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) throw new InvalidArgumentException('Enter a valid name and email address.');
            $stmt = $pdo->prepare('UPDATE admins SET full_name=:name, email=:email WHERE id=:id AND is_active=1');
            try {
                $stmt->execute(['name' => $name, 'email' => $email, 'id' => $_SESSION['admin_id']]);
            } catch (PDOException $error) {
                if ($error->getCode() === '23000') throw new InvalidArgumentException('That email is already assigned to another administrator.');
                throw $error;
            }
            $_SESSION['admin_name'] = $name;
            json_response(['ok' => true, 'message' => 'Studio profile saved.', 'reload' => true]);
        }
        case 'admin.password.change': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $current = (string) ($_POST['current_password'] ?? '');
            $password = (string) ($_POST['new_password'] ?? '');
            $confirmation = (string) ($_POST['confirm_password'] ?? '');
            if (strlen($password) < 12 || strlen($password) > 200 || !hash_equals($password, $confirmation)) throw new InvalidArgumentException('Choose a 12–200 character password and enter it identically twice.');
            $find = $pdo->prepare('SELECT password_hash FROM admins WHERE id=:id AND is_active=1');
            $find->execute(['id' => $_SESSION['admin_id']]);
            $hash = $find->fetchColumn();
            if (!$hash || !password_verify($current, $hash)) throw new InvalidArgumentException('The current password did not match.');
            $update = $pdo->prepare('UPDATE admins SET password_hash=:hash WHERE id=:id');
            $update->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $_SESSION['admin_id']]);
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            json_response(['ok' => true, 'message' => 'Administrator password changed.', 'reload' => true]);
        }
        case 'admin.category.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || mb_strlen($description) > 1000) throw new InvalidArgumentException('Enter a collection name and description within the allowed lengths.');
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name), '-')) . '-' . strtolower(bin2hex(random_bytes(2)));
            if ($id) {
                $stmt = $pdo->prepare('UPDATE categories SET name=:name, slug=:slug, description=:description WHERE id=:id');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null, 'id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES (:name, :slug, :description, 50, 1)');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null]);
            }
            json_response(['ok' => true, 'message' => $id ? 'Collection updated.' : 'Collection created.', 'redirect' => 'index.php?page=settings']);
        }
        case 'admin.category.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid collection state.');
            $stmt = $pdo->prepare('UPDATE categories SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Collection not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Collection visibility updated.', 'reload' => true]);
        }
        case 'admin.brand.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || mb_strlen($description) > 1000) throw new InvalidArgumentException('Enter a valid fragrance house name and description.');
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name), '-')) . '-' . strtolower(bin2hex(random_bytes(2)));
            if ($id) {
                $stmt = $pdo->prepare('UPDATE brands SET name=:name, slug=:slug, description=:description WHERE id=:id');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null, 'id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO brands (name, slug, description, is_active) VALUES (:name, :slug, :description, 1)');
                $stmt->execute(['name' => $name, 'slug' => $slug, 'description' => $description ?: null]);
            }
            json_response(['ok' => true, 'message' => $id ? 'Fragrance house updated.' : 'Fragrance house added.', 'redirect' => 'index.php?page=settings']);
        }
        case 'admin.brand.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid fragrance house state.');
            $stmt = $pdo->prepare('UPDATE brands SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Fragrance house not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Fragrance house visibility updated.', 'reload' => true]);
        }
        case 'admin.order.update': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            $allowed = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!$orderId || !in_array($status, $allowed, true)) throw new InvalidArgumentException('Choose a valid order status.');
            $pdo->beginTransaction();
            try {
                $find = $pdo->prepare('SELECT order_status FROM orders WHERE id = :id FOR UPDATE');
                $find->execute(['id' => $orderId]);
                $oldStatus = $find->fetchColumn();
                if ($oldStatus === false) throw new InvalidArgumentException('Order not found.');
                if ($oldStatus !== $status) {
                    $transitions = [
                        'pending' => ['confirmed', 'cancelled'],
                        'confirmed' => ['processing', 'cancelled'],
                        'processing' => ['shipped', 'cancelled'],
                        'shipped' => ['delivered'],
                        'delivered' => [],
                        'cancelled' => [],
                    ];
                    if (!in_array($status, $transitions[$oldStatus] ?? [], true)) throw new InvalidArgumentException('That order status transition is not allowed.');
                    $update = $pdo->prepare('UPDATE orders SET order_status = :status, cancellation_note = :note, updated_at = NOW() WHERE id = :id');
                    $update->execute(['status' => $status, 'note' => $status === 'cancelled' ? (trim((string) ($_POST['note'] ?? '')) ?: null) : null, 'id' => $orderId]);
                    $history = $pdo->prepare('INSERT INTO order_status_history (order_id, status, note, changed_by) VALUES (:order, :status, :note, :admin)');
                    $history->execute(['order' => $orderId, 'status' => $status, 'note' => trim((string) ($_POST['note'] ?? '')) ?: null, 'admin' => $_SESSION['admin_id']]);
                    if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
                        $restoreSetting = $pdo->prepare('SELECT setting_value FROM store_settings WHERE setting_key="stock_restore_on_cancellation" LIMIT 1');
                        $restoreSetting->execute();
                        $restoreValue = $restoreSetting->fetchColumn();
                        if ($restoreValue === false || (string) $restoreValue === '1') {
                            $stockMoves = $pdo->prepare('SELECT variant_id, SUM(quantity_change) AS quantity_change FROM inventory_transactions WHERE order_id = :id AND reason = "order" GROUP BY variant_id');
                            $stockMoves->execute(['id' => $orderId]);
                            foreach ($stockMoves->fetchAll() as $move) {
                            $restoredQuantity = abs((int) $move['quantity_change']);
                            if ($restoredQuantity < 1) continue;
                            $restore = $pdo->prepare('UPDATE product_variants SET stock_quantity = stock_quantity + :quantity, updated_at = NOW() WHERE id = :id');
                            $restore->execute(['quantity' => $restoredQuantity, 'id' => $move['variant_id']]);
                            $audit = $pdo->prepare('INSERT INTO inventory_transactions (variant_id, order_id, quantity_change, reason, note, created_by) VALUES (:variant, :order, :quantity, "cancellation", "Cancelled order stock restoration", :admin)');
                                $audit->execute(['variant' => $move['variant_id'], 'order' => $orderId, 'quantity' => $restoredQuantity, 'admin' => $_SESSION['admin_id']]);
                            }
                        }
                        $items = $pdo->prepare('SELECT product_id, SUM(quantity) AS quantity FROM order_items WHERE order_id = :id GROUP BY product_id');
                        $items->execute(['id' => $orderId]);
                        foreach ($items->fetchAll() as $item) {
                            if ($item['product_id']) {
                                $sales = $pdo->prepare('UPDATE products SET sales_count = GREATEST(sales_count - :quantity, 0) WHERE id = :id');
                                $sales->execute(['quantity' => $item['quantity'], 'id' => $item['product_id']]);
                            }
                        }
                        $cancelPreorders = $pdo->prepare('UPDATE preorders SET status = "cancelled" WHERE order_id = :order AND status = "reserved"');
                        $cancelPreorders->execute(['order' => $orderId]);
                        $cancelPayments = $pdo->prepare('UPDATE payments SET status="rejected", admin_note="Order cancelled before payment verification", verified_by=:admin, verified_at=NOW() WHERE order_id=:order AND status="pending_verification"');
                        $cancelPayments->execute(['admin' => $_SESSION['admin_id'], 'order' => $orderId]);
                        if ($cancelPayments->rowCount()) $pdo->prepare('UPDATE orders SET payment_status="rejected" WHERE id=:id')->execute(['id' => $orderId]);
                        $discountUse = $pdo->prepare('SELECT discount_id FROM discount_usage WHERE order_id = :id');
                        $discountUse->execute(['id' => $orderId]);
                        $discountId = $discountUse->fetchColumn();
                        if ($discountId) {
                            $releaseUse = $pdo->prepare('DELETE FROM discount_usage WHERE order_id = :id');
                            $releaseUse->execute(['id' => $orderId]);
                            $releaseCount = $pdo->prepare('UPDATE discounts SET usage_count = GREATEST(usage_count - 1, 0) WHERE id = :id');
                            $releaseCount->execute(['id' => $discountId]);
                        }
                    }
                    $log = $pdo->prepare('INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (:admin, "order.status.updated", "order", :entity, :details, :ip)');
                    $log->execute(['admin' => $_SESSION['admin_id'], 'entity' => $orderId, 'details' => json_encode(['from' => $oldStatus, 'to' => $status]), 'ip' => $_SERVER['REMOTE_ADDR'] ?? null]);
                }
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            json_response(['ok' => true, 'message' => 'Order status updated.', 'reload' => true]);
        }
        case 'admin.order.email.retry': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$orderId) throw new InvalidArgumentException('Choose a valid order.');
            $statusStmt = $pdo->prepare('SELECT status, last_attempt_at FROM order_email_notifications WHERE order_id = :id');
            $statusStmt->execute(['id' => $orderId]);
            $emailStatus = $statusStmt->fetch();
            $retryable = $emailStatus && (
                $emailStatus['status'] === 'failed' ||
                ($emailStatus['status'] === 'sending' && $emailStatus['last_attempt_at'] && strtotime($emailStatus['last_attempt_at']) < time() - 600)
            );
            if (!$retryable) {
                throw new InvalidArgumentException('Only a failed or stale confirmation email can be retried.');
            }
            $result = (new OrderConfirmationEmailService($pdo))->sendForOrder((int) $orderId);
            if ($result['status'] !== 'sent') {
                json_response(['ok' => false, 'message' => 'The confirmation email could not be sent. Check SMTP configuration and try again.'], 503);
            }
            json_response(['ok' => true, 'message' => 'Order confirmation email sent.', 'reload' => true]);
        }
        case 'admin.payment.update': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $paymentId = filter_var($_POST['payment_id'] ?? null, FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            if (!$paymentId || !in_array($status, ['verified', 'rejected'], true)) throw new InvalidArgumentException('Choose a valid payment decision.');
            $pdo->beginTransaction();
            try {
            $payment = $pdo->prepare('SELECT p.order_id, p.method, p.status, o.order_status FROM payments p JOIN orders o ON o.id=p.order_id WHERE p.id=:id FOR UPDATE');
            $payment->execute(['id' => $paymentId]);
            $row = $payment->fetch();
            if (!$row) throw new InvalidArgumentException('Payment not found.');
            if ($row['order_status'] === 'cancelled') throw new InvalidArgumentException('A cancelled order payment cannot be verified.');
            $validReview = $row['status'] === 'pending_verification' || ($row['status'] === 'unpaid' && $row['method'] === 'cod' && $status === 'verified');
            if (!$validReview) throw new InvalidArgumentException('This payment has already been reviewed or is not eligible for that action.');
                $update = $pdo->prepare('UPDATE payments SET status = :status, verified_by = :admin, verified_at = NOW(), admin_note = :note WHERE id = :id');
                $update->execute(['status' => $status, 'admin' => $_SESSION['admin_id'], 'note' => trim((string) ($_POST['note'] ?? '')) ?: null, 'id' => $paymentId]);
                $orderUpdate = $pdo->prepare('UPDATE orders SET payment_status = :status, updated_at = NOW() WHERE id = :order');
                $orderUpdate->execute(['status' => $status, 'order' => $row['order_id']]);
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            json_response(['ok' => true, 'message' => 'Payment review saved.', 'reload' => true]);
        }
        case 'admin.stock.threshold': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $variantId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
            $threshold = filter_var($_POST['low_stock_threshold'] ?? null, FILTER_VALIDATE_INT);
            if (!$variantId || $threshold === false || $threshold < 0 || $threshold > 100000) throw new InvalidArgumentException('Enter a low-stock threshold between 0 and 100,000.');
            $stmt = $pdo->prepare('UPDATE product_variants SET low_stock_threshold=:threshold WHERE id=:id');
            $stmt->execute(['threshold' => $threshold, 'id' => $variantId]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Variant not found or threshold unchanged.');
            json_response(['ok' => true, 'message' => 'Low-stock alert threshold saved.', 'reload' => true]);
        }
        case 'admin.stock.adjust': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $variantId = filter_var($_POST['variant_id'] ?? null, FILTER_VALIDATE_INT);
            $change = filter_var($_POST['quantity_change'] ?? null, FILTER_VALIDATE_INT);
            if (!$variantId || !$change || abs($change) > 10000) throw new InvalidArgumentException('Enter a non-zero inventory adjustment.');
            $pdo->beginTransaction();
            try {
                $lock = $pdo->prepare('SELECT stock_quantity FROM product_variants WHERE id = :id FOR UPDATE');
                $lock->execute(['id' => $variantId]);
                $current = $lock->fetchColumn();
                if ($current === false || (int) $current + $change < 0) throw new InvalidArgumentException('The stock level cannot be negative.');
                $update = $pdo->prepare('UPDATE product_variants SET stock_quantity = stock_quantity + :change, updated_at = NOW() WHERE id = :id');
                $update->execute(['change' => $change, 'id' => $variantId]);
                $audit = $pdo->prepare('INSERT INTO inventory_transactions (variant_id, quantity_change, reason, note, created_by) VALUES (:variant, :change, "adjustment", :note, :admin)');
                $audit->execute(['variant' => $variantId, 'change' => $change, 'note' => trim((string) ($_POST['note'] ?? 'Manual inventory adjustment')) ?: null, 'admin' => $_SESSION['admin_id']]);
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            json_response(['ok' => true, 'message' => 'Inventory adjusted.', 'reload' => true]);
        }
        case 'admin.review.moderate': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $reviewId = filter_var($_POST['review_id'] ?? null, FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            if (!$reviewId || !in_array($status, ['approved', 'rejected', 'hidden'], true)) throw new InvalidArgumentException('Choose a valid review status.');
            $update = $pdo->prepare('UPDATE reviews SET status = :status, is_featured = :featured WHERE id = :id');
            $update->execute(['status' => $status, 'featured' => !empty($_POST['featured']) && $status === 'approved' ? 1 : 0, 'id' => $reviewId]);
            if (!$update->rowCount()) throw new InvalidArgumentException('Review not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Review moderation saved.', 'reload' => true]);
        }
        case 'admin.notification.read': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id) throw new InvalidArgumentException('Choose a valid notification.');
            $stmt = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE id=:id');
            $stmt->execute(['id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Notification not found or already read.');
            json_response(['ok' => true, 'message' => 'Notification marked as read.', 'reload' => true]);
        }
        case 'admin.customer.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $customerId = filter_var($_POST['customer_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$customerId || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid customer account state.');
            $stmt = $pdo->prepare('UPDATE customers SET is_active = :active WHERE id = :id');
            $stmt->execute(['active' => $active, 'id' => $customerId]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Customer not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Customer account updated.', 'reload' => true]);
        }
        case 'admin.banner.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['banner_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $title = trim((string) ($_POST['title'] ?? ''));
            $placement = (string) ($_POST['placement'] ?? 'promo');
            $image = trim((string) ($_POST['image_path'] ?? ''));
            $ctaUrl = trim((string) ($_POST['cta_url'] ?? ''));
            $sort = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            $starts = trim((string) ($_POST['starts_at'] ?? ''));
            $ends = trim((string) ($_POST['ends_at'] ?? ''));
            $startTime = $starts !== '' ? strtotime($starts) : false;
            $endTime = $ends !== '' ? strtotime($ends) : false;
            if ($title === '' || mb_strlen($title) > 180 || !in_array($placement, ['hero', 'promo', 'popup'], true) || $sort === false || $sort < 0 || $sort > 999) throw new InvalidArgumentException('Enter a valid banner headline, placement, and order.');
            $safeLocalImage = $image !== '' && preg_match('#^[A-Za-z0-9_./-]+$#', $image) && !str_contains($image, '..') && !str_starts_with($image, '//');
            if ($image !== '' && !$safeLocalImage && !(filter_var($image, FILTER_VALIDATE_URL) && str_starts_with(strtolower($image), 'https://'))) throw new InvalidArgumentException('Use a local image path or secure HTTPS image URL.');
            if ($ctaUrl !== '' && !(str_starts_with($ctaUrl, '/') && !str_starts_with($ctaUrl, '//')) && !str_starts_with($ctaUrl, '?') && !(filter_var($ctaUrl, FILTER_VALIDATE_URL) && str_starts_with(strtolower($ctaUrl), 'https://'))) throw new InvalidArgumentException('Use a same-site path or secure HTTPS button destination.');
            if ($starts !== '' && !$startTime || $ends !== '' && !$endTime || $startTime && $endTime && $endTime <= $startTime) throw new InvalidArgumentException('Check the banner schedule dates.');
            $data = ['title' => $title, 'subtitle' => trim((string) ($_POST['subtitle'] ?? '')) ?: null, 'image' => $image ?: null, 'label' => trim((string) ($_POST['cta_label'] ?? '')) ?: null, 'url' => $ctaUrl ?: null, 'placement' => $placement, 'starts' => $startTime ? date('Y-m-d H:i:s', $startTime) : null, 'ends' => $endTime ? date('Y-m-d H:i:s', $endTime) : null, 'sort' => $sort, 'active' => !empty($_POST['is_active']) ? 1 : 0];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE banners SET title=:title, subtitle=:subtitle, image_path=:image, cta_label=:label, cta_url=:url, placement=:placement, starts_at=:starts, ends_at=:ends, sort_order=:sort, is_active=:active WHERE id=:id');
                $stmt->execute($data + ['id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO banners (title, subtitle, image_path, cta_label, cta_url, placement, starts_at, ends_at, sort_order, is_active) VALUES (:title,:subtitle,:image,:label,:url,:placement,:starts,:ends,:sort,:active)');
                $stmt->execute($data);
            }
            json_response(['ok' => true, 'message' => 'Banner saved.']);
        }
        case 'admin.social.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['social_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $platform = trim((string) ($_POST['platform'] ?? ''));
            $url = trim((string) ($_POST['url'] ?? ''));
            $sort = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            if (mb_strlen($platform) < 2 || mb_strlen($platform) > 40 || $sort === false || $sort < 0 || $sort > 999 || !filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with(strtolower($url), 'https://')) throw new InvalidArgumentException('Enter a platform, secure profile URL, and valid sort order.');
            $data = ['platform' => $platform, 'url' => $url, 'sort' => $sort, 'active' => !empty($_POST['is_active']) ? 1 : 0];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE social_links SET platform=:platform, url=:url, sort_order=:sort, is_active=:active WHERE id=:id');
                $stmt->execute($data + ['id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO social_links (platform, url, sort_order, is_active) VALUES (:platform,:url,:sort,:active)');
                $stmt->execute($data);
            }
            json_response(['ok' => true, 'message' => 'Social link saved.']);
        }
        case 'admin.section.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['section_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $sectionKey = strtolower(trim((string) ($_POST['section_key'] ?? '')));
            $heading = trim((string) ($_POST['heading'] ?? ''));
            $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
            $collectionKey = (string) ($_POST['collection_key'] ?? '');
            $sort = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            $allowedCollections = ['featured', 'best_seller', 'new_arrival', 'men', 'women', 'unisex', 'limited', 'reviews'];
            if (!preg_match('/^[a-z0-9-]{2,60}$/', $sectionKey) || mb_strlen($heading) < 2 || mb_strlen($heading) > 160 || mb_strlen($subtitle) > 240 || !in_array($collectionKey, $allowedCollections, true) || $sort === false || $sort < 0 || $sort > 999) throw new InvalidArgumentException('Enter a valid section key, heading, collection, and order.');
            $data = ['key' => $sectionKey, 'heading' => $heading, 'subtitle' => $subtitle ?: null, 'collection' => $collectionKey, 'sort' => $sort, 'active' => !empty($_POST['is_active']) ? 1 : 0];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE homepage_sections SET section_key=:key, heading=:heading, subtitle=:subtitle, collection_key=:collection, sort_order=:sort, is_active=:active WHERE id=:id');
                $stmt->execute($data + ['id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO homepage_sections (section_key, heading, subtitle, collection_key, sort_order, is_active) VALUES (:key,:heading,:subtitle,:collection,:sort,:active)');
                $stmt->execute($data);
            }
            json_response(['ok' => true, 'message' => 'Homepage section saved.', 'reload' => true]);
        }
        case 'admin.section.toggle': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['section_id'] ?? null, FILTER_VALIDATE_INT);
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || !in_array($active, [0, 1], true)) throw new InvalidArgumentException('Choose a valid homepage section state.');
            $stmt = $pdo->prepare('UPDATE homepage_sections SET is_active=:active WHERE id=:id');
            $stmt->execute(['active' => $active, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Section not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Homepage section updated.', 'reload' => true]);
        }
        case 'admin.delivery.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['zone_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $name = trim((string) ($_POST['name'] ?? ''));
            $fee = filter_var($_POST['delivery_fee'] ?? null, FILTER_VALIDATE_FLOAT);
            $sort = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT);
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || $fee === false || $fee < 0 || $fee > 100000 || $sort === false || $sort < 0 || $sort > 999) throw new InvalidArgumentException('Enter a valid zone name, fee, and order.');
            $data = ['name' => $name, 'fee' => $fee, 'days' => trim((string) ($_POST['estimated_days'] ?? '')) ?: null, 'instructions' => trim((string) ($_POST['instructions'] ?? '')) ?: null, 'sort' => $sort, 'active' => !empty($_POST['is_active']) ? 1 : 0];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE delivery_zones SET name=:name, delivery_fee=:fee, estimated_days=:days, instructions=:instructions, sort_order=:sort, is_active=:active WHERE id=:id');
                $stmt->execute($data + ['id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO delivery_zones (name, delivery_fee, estimated_days, instructions, sort_order, is_active) VALUES (:name,:fee,:days,:instructions,:sort,:active)');
                $stmt->execute($data);
            }
            json_response(['ok' => true, 'message' => 'Delivery zone saved.']);
        }
        case 'admin.drop.save': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['drop_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
            $release = strtotime((string) ($_POST['release_at'] ?? ''));
            $endRaw = trim((string) ($_POST['end_at'] ?? ''));
            $end = $endRaw !== '' ? strtotime($endRaw) : false;
            $limit = trim((string) ($_POST['stock_limit'] ?? '')) === '' ? null : filter_var($_POST['stock_limit'], FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? 'scheduled');
            if (!$productId || !$release || $endRaw !== '' && !$end || $end && $release >= $end || $limit !== null && ($limit === false || $limit < 1 || $limit > 100000) || !in_array($status, ['scheduled', 'active', 'ended', 'hidden'], true)) throw new InvalidArgumentException('Enter a valid product, release window, stock limit, and status.');
            $productCheck = $pdo->prepare('SELECT id FROM products WHERE id=:id AND is_active=1');
            $productCheck->execute(['id' => $productId]);
            if (!$productCheck->fetchColumn()) throw new InvalidArgumentException('Choose an active product.');
            $data = ['product' => $productId, 'release' => date('Y-m-d H:i:s', $release), 'end' => $end ? date('Y-m-d H:i:s', $end) : null, 'limit' => $limit, 'preorder' => !empty($_POST['allow_preorder']) ? 1 : 0, 'status' => $status];
            if ($id) {
                $stmt = $pdo->prepare('UPDATE limited_drops SET product_id=:product, release_at=:release, end_at=:end, stock_limit=:limit, allow_preorder=:preorder, status=:status WHERE id=:id');
                $stmt->execute($data + ['id' => $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO limited_drops (product_id, release_at, end_at, stock_limit, allow_preorder, status) VALUES (:product,:release,:end,:limit,:preorder,:status) ON DUPLICATE KEY UPDATE release_at=VALUES(release_at), end_at=VALUES(end_at), stock_limit=VALUES(stock_limit), allow_preorder=VALUES(allow_preorder), status=VALUES(status)');
                $stmt->execute($data);
            }
            json_response(['ok' => true, 'message' => 'Limited drop saved.']);
        }
        case 'admin.preorder.update': {
            if (empty($_SESSION['admin_id'])) json_response(['ok' => false, 'message' => 'Sign in to the studio first.'], 401);
            $id = filter_var($_POST['preorder_id'] ?? null, FILTER_VALIDATE_INT);
            $status = (string) ($_POST['status'] ?? '');
            $shipDate = trim((string) ($_POST['expected_ship_date'] ?? ''));
            if (!$id || !in_array($status, ['reserved', 'fulfilled', 'cancelled'], true) || $shipDate !== '' && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $shipDate)) throw new InvalidArgumentException('Choose a valid pre-order status and ship date.');
            $stmt = $pdo->prepare('UPDATE preorders SET status=:status, expected_ship_date=:ship WHERE id=:id');
            $stmt->execute(['status' => $status, 'ship' => $shipDate ?: null, 'id' => $id]);
            if (!$stmt->rowCount()) throw new InvalidArgumentException('Pre-order not found or unchanged.');
            json_response(['ok' => true, 'message' => 'Pre-order updated.', 'reload' => true]);
        }
        default:
            json_response(['ok' => false, 'message' => 'Unknown action.'], 404);
    }
} catch (InvalidArgumentException|RuntimeException $error) {
    json_response(['ok' => false, 'message' => $error->getMessage()], 422);
} catch (PDOException $error) {
    error_log('[THE LAST NOTE BD] Database action failed: ' . $error->getMessage());
    json_response(['ok' => false, 'message' => 'We couldn’t complete that request. Please try again.'], 500);
} catch (Throwable $error) {
    error_log('[THE LAST NOTE BD] Request failed: ' . $error->getMessage());
    json_response(['ok' => false, 'message' => 'Something went wrong. Please try again.'], 500);
}
