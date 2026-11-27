<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$page = preg_replace('/[^a-z-]/', '', (string) ($_GET['page'] ?? 'dashboard')) ?: 'dashboard';
$adminPages = ['login', 'dashboard', 'products', 'orders', 'inventory', 'reviews', 'discounts', 'settings', 'customers', 'content', 'delivery', 'drops', 'preorders', 'reports', 'activity'];
if (!in_array($page, $adminPages, true)) $page = 'dashboard';
$flashMessage = take_flash();
try {
    $pdo = database();
} catch (Throwable $error) {
    error_log('[Maison Serein] Studio database unavailable: ' . $error->getMessage());
    $pdo = null;
}
if ($page === 'login' && !empty($_SESSION['admin_id'])) redirect('index.php?page=dashboard');
if ($page !== 'login' && empty($_SESSION['admin_id'])) redirect('index.php?page=login');

if ($pdo && !empty($_SESSION['admin_id'])) {
    if ($page === 'dashboard') {
        $stats = [
            'today_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()')->fetchColumn(),
            'total_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'pending_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "pending"')->fetchColumn(),
            'confirmed_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "confirmed"')->fetchColumn(),
            'processing_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "processing"')->fetchColumn(),
            'shipped_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "shipped"')->fetchColumn(),
            'delivered_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "delivered"')->fetchColumn(),
            'cancelled_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "cancelled"')->fetchColumn(),
            'total_products' => (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
            'total_customers' => (int) $pdo->query('SELECT COUNT(*) FROM customers WHERE is_active = 1')->fetchColumn(),
            'today_revenue' => (float) $pdo->query('SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = "verified"')->fetchColumn(),
            'gross_sales' => (float) $pdo->query('SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE payment_status = "verified"')->fetchColumn(),
            'low_stock' => (int) $pdo->query('SELECT COUNT(*) FROM product_variants WHERE is_active = 1 AND stock_quantity <= low_stock_threshold')->fetchColumn(),
        ];
        $recentOrders = $pdo->query('SELECT id, order_number, customer_name, order_status, payment_status, grand_total, created_at FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();
        $pendingPayments = $pdo->query('SELECT p.id, p.order_id, p.method AS payment_method, p.transaction_id, p.amount, o.order_number, o.customer_name FROM payments p JOIN orders o ON o.id = p.order_id WHERE p.status = "pending_verification" ORDER BY p.created_at LIMIT 6')->fetchAll();
        $dashboardSales = $pdo->query('SELECT DATE(created_at) AS sale_day, COALESCE(SUM(grand_total),0) AS total FROM orders WHERE payment_status = "verified" AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY sale_day')->fetchAll();
        $dashboardTopProducts = $pdo->query('SELECT oi.product_name, SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.payment_status = "verified" GROUP BY oi.product_name ORDER BY units_sold DESC LIMIT 5')->fetchAll();
        $notifications = $pdo->query('SELECT id, title, body, notification_type, created_at FROM notifications WHERE is_read=0 ORDER BY created_at DESC LIMIT 8')->fetchAll();
    } elseif ($page === 'products') {
        $productRows = $pdo->query('SELECT p.*, b.name AS brand_name, c.name AS category_name, v.id AS variant_id, v.sku, v.size_ml, v.size_label, v.price, v.stock_quantity, v.low_stock_threshold, v.is_active AS variant_active FROM products p JOIN brands b ON b.id = p.brand_id LEFT JOIN categories c ON c.id = p.category_id LEFT JOIN product_variants v ON v.product_id = p.id ORDER BY p.created_at DESC, v.sort_order, v.size_ml')->fetchAll();
        $products = [];
        foreach ($productRows as $productRow) {
            $productId = (int) $productRow['id'];
            if (!isset($products[$productId])) {
                $products[$productId] = $productRow;
                $products[$productId]['variants'] = [];
            }
            if ($productRow['variant_id'] !== null) $products[$productId]['variants'][] = ['id' => (int) $productRow['variant_id'], 'sku' => $productRow['sku'], 'size_ml' => $productRow['size_ml'], 'size_label' => $productRow['size_label'], 'price' => $productRow['price'], 'stock_quantity' => $productRow['stock_quantity'], 'low_stock_threshold' => $productRow['low_stock_threshold'], 'is_active' => $productRow['variant_active']];
        }
        $products = array_values($products);
        $imageStmt = $pdo->query('SELECT id, product_id, image_path, alt_text, sort_order FROM product_images ORDER BY product_id, sort_order, id');
        $productImages = [];
        foreach ($imageStmt->fetchAll() as $image) $productImages[(int) $image['product_id']][] = $image;
        $brands = $pdo->query('SELECT id, name FROM brands WHERE is_active = 1 ORDER BY name')->fetchAll();
        $categories = $pdo->query('SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name')->fetchAll();
    } elseif ($page === 'orders') {
        $orderSearch = trim((string) ($_GET['q'] ?? ''));
        $orderStatus = (string) ($_GET['status'] ?? '');
        $orderConditions = [];
        $orderParams = [];
        if (in_array($orderStatus, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $orderConditions[] = 'o.order_status = :status';
            $orderParams['status'] = $orderStatus;
        } else {
            $orderStatus = '';
        }
        if ($orderSearch !== '') {
            $orderConditions[] = '(o.order_number LIKE :order_number OR o.customer_name LIKE :customer_name OR o.phone_normalized LIKE :phone)';
            $like = '%' . $orderSearch . '%';
            $orderParams += ['order_number' => $like, 'customer_name' => $like, 'phone' => $like];
        }
        $orderSql = 'SELECT o.*, (SELECT COUNT(*) FROM order_items i WHERE i.order_id = o.id) AS item_count,
            (SELECT e.status FROM order_email_notifications e WHERE e.order_id = o.id) AS email_status
            FROM orders o' . ($orderConditions ? ' WHERE ' . implode(' AND ', $orderConditions) : '') . ' ORDER BY o.created_at DESC LIMIT 100';
        $orderStmt = $pdo->prepare($orderSql);
        $orderStmt->execute($orderParams);
        $orders = $orderStmt->fetchAll();
        $detailId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if ($detailId) {
            $detailStmt = $pdo->prepare('SELECT o.*, e.status AS email_status, e.attempts AS email_attempts, e.last_error AS email_error,
                e.last_attempt_at AS email_last_attempt_at, e.sent_at AS email_sent_at
                FROM orders o LEFT JOIN order_email_notifications e ON e.order_id = o.id WHERE o.id=:id');
            $detailStmt->execute(['id' => $detailId]);
            $orderDetail = $detailStmt->fetch() ?: null;
            if ($orderDetail) {
                $detailItems = $pdo->prepare('SELECT product_name, size_label, unit_price, quantity, line_total FROM order_items WHERE order_id=:id ORDER BY id');
                $detailItems->execute(['id' => $detailId]);
                $orderDetail['items'] = $detailItems->fetchAll();
                $detailHistory = $pdo->prepare('SELECT status, note, created_at FROM order_status_history WHERE order_id=:id ORDER BY created_at, id');
                $detailHistory->execute(['id' => $detailId]);
                $orderDetail['history'] = $detailHistory->fetchAll();
            }
        }
        $payments = $pdo->query('SELECT p.id, p.order_id, p.method AS payment_method, p.transaction_id, p.amount, p.status, o.order_number, o.customer_name, o.phone FROM payments p JOIN orders o ON o.id = p.order_id WHERE p.status IN ("pending_verification", "unpaid") ORDER BY p.created_at')->fetchAll();
    } elseif ($page === 'inventory') {
        $inventory = $pdo->query('SELECT v.id, v.sku, v.size_label, v.price, v.stock_quantity, v.low_stock_threshold, p.name AS product_name, b.name AS brand_name FROM product_variants v JOIN products p ON p.id = v.product_id JOIN brands b ON b.id = p.brand_id WHERE v.is_active = 1 ORDER BY v.stock_quantity, p.name')->fetchAll();
        $inventoryHistory = $pdo->query('SELECT t.*, v.sku, v.size_label, p.name AS product_name, a.full_name AS admin_name FROM inventory_transactions t JOIN product_variants v ON v.id=t.variant_id JOIN products p ON p.id=v.product_id LEFT JOIN admins a ON a.id=t.created_by ORDER BY t.created_at DESC, t.id DESC LIMIT 50')->fetchAll();
    } elseif ($page === 'reviews') {
        $reviews = $pdo->query('SELECT r.*, p.name AS product_name FROM reviews r JOIN products p ON p.id = r.product_id ORDER BY FIELD(r.status, "pending", "approved", "rejected", "hidden"), r.created_at DESC LIMIT 100')->fetchAll();
    } elseif ($page === 'discounts') {
        $discounts = $pdo->query('SELECT d.*, p.name AS product_name, (SELECT COUNT(*) FROM discount_usage u WHERE u.discount_id = d.id) AS uses_count FROM discounts d LEFT JOIN products p ON p.id = d.product_id ORDER BY d.created_at DESC')->fetchAll();
        $discountProducts = $pdo->query('SELECT id, name FROM products WHERE is_active=1 ORDER BY name')->fetchAll();
    } elseif ($page === 'settings') {
        $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM store_settings')->fetchAll();
        $storeSettings = array_column($settingsRows, 'setting_value', 'setting_key');
        $categories = $pdo->query('SELECT id, name, slug, description, is_active FROM categories ORDER BY sort_order, name')->fetchAll();
        $brands = $pdo->query('SELECT id, name, slug, description, is_active FROM brands ORDER BY name')->fetchAll();
        $adminStmt = $pdo->prepare('SELECT id, full_name, email FROM admins WHERE id=:id AND is_active=1');
        $adminStmt->execute(['id' => $_SESSION['admin_id']]);
        $adminProfile = $adminStmt->fetch();
    } elseif ($page === 'customers') {
        $customerSearch = trim((string) ($_GET['q'] ?? ''));
        $stmt = $pdo->prepare('SELECT c.id, c.full_name, c.email, c.phone_normalized, c.is_active, c.created_at, COUNT(o.id) AS order_count, COALESCE(SUM(CASE WHEN o.payment_status = "verified" THEN o.grand_total ELSE 0 END), 0) AS verified_spend FROM customers c LEFT JOIN orders o ON o.customer_id = c.id WHERE (:query = "" OR c.full_name LIKE :name_query OR c.email LIKE :email_query OR c.phone_normalized LIKE :phone_query) GROUP BY c.id ORDER BY c.created_at DESC LIMIT 100');
        $stmt->execute(['query' => $customerSearch, 'name_query' => '%' . $customerSearch . '%', 'email_query' => '%' . $customerSearch . '%', 'phone_query' => '%' . $customerSearch . '%']);
        $customers = $stmt->fetchAll();
        $guestStmt = $pdo->prepare('SELECT phone_normalized, MAX(customer_name) AS full_name, MAX(email) AS email, COUNT(*) AS order_count, COALESCE(SUM(CASE WHEN payment_status = "verified" THEN grand_total ELSE 0 END), 0) AS verified_spend, MAX(created_at) AS last_order_at FROM orders WHERE customer_id IS NULL AND (:query = "" OR customer_name LIKE :name_query OR email LIKE :email_query OR phone_normalized LIKE :phone_query) GROUP BY phone_normalized ORDER BY last_order_at DESC LIMIT 100');
        $guestStmt->execute(['query' => $customerSearch, 'name_query' => '%' . $customerSearch . '%', 'email_query' => '%' . $customerSearch . '%', 'phone_query' => '%' . $customerSearch . '%']);
        $guestCustomers = $guestStmt->fetchAll();
    } elseif ($page === 'content') {
        $banners = $pdo->query('SELECT * FROM banners ORDER BY placement, sort_order, id')->fetchAll();
        $socials = $pdo->query('SELECT * FROM social_links ORDER BY sort_order, id')->fetchAll();
        $homepageSections = $pdo->query('SELECT * FROM homepage_sections ORDER BY sort_order, id')->fetchAll();
    } elseif ($page === 'delivery') {
        $deliveryZones = $pdo->query('SELECT * FROM delivery_zones ORDER BY sort_order, name')->fetchAll();
    } elseif ($page === 'drops') {
        $drops = $pdo->query('SELECT d.*, p.name AS product_name FROM limited_drops d JOIN products p ON p.id = d.product_id ORDER BY d.release_at DESC')->fetchAll();
        $dropProducts = $pdo->query('SELECT id, name FROM products WHERE is_active = 1 ORDER BY name')->fetchAll();
    } elseif ($page === 'preorders') {
        $preorders = $pdo->query('SELECT pr.*, o.order_number, o.order_status, p.name AS product_name FROM preorders pr JOIN orders o ON o.id = pr.order_id JOIN products p ON p.id = pr.product_id ORDER BY pr.created_at DESC LIMIT 100')->fetchAll();
    } elseif ($page === 'activity') {
        $activityLogs = $pdo->query('SELECT l.*, a.full_name AS admin_name FROM activity_logs l LEFT JOIN admins a ON a.id=l.admin_id ORDER BY l.created_at DESC, l.id DESC LIMIT 100')->fetchAll();
    } elseif ($page === 'reports') {
        $validDate = static fn($date) => is_string($date) && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date) && strtotime($date) !== false && date('Y-m-d', strtotime($date)) === $date;
        $reportFrom = $validDate($_GET['from'] ?? null) ? (string) $_GET['from'] : date('Y-m-01');
        $reportTo = $validDate($_GET['to'] ?? null) ? (string) $_GET['to'] : date('Y-m-d');
        if ($reportTo < $reportFrom) {
            $reportFrom = date('Y-m-01');
            $reportTo = date('Y-m-d');
        }
        $stmt = $pdo->prepare('SELECT DATE(created_at) AS report_day, COUNT(*) AS order_count, COALESCE(SUM(grand_total),0) AS paid_total FROM orders WHERE payment_status = "verified" AND DATE(created_at) BETWEEN :from_date AND :to_date GROUP BY DATE(created_at) ORDER BY report_day');
        $stmt->execute(['from_date' => $reportFrom, 'to_date' => $reportTo]);
        $reportRows = $stmt->fetchAll();
        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="maison-serein-sales.csv"');
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Date', 'Order number', 'Customer', 'Payment method', 'Order status', 'Paid total']);
            $export = $pdo->prepare('SELECT DATE(created_at) AS order_day, order_number, customer_name, payment_method, order_status, grand_total FROM orders WHERE payment_status = "verified" AND DATE(created_at) BETWEEN :from_date AND :to_date ORDER BY created_at');
            $export->execute(['from_date' => $reportFrom, 'to_date' => $reportTo]);
            while ($row = $export->fetch(PDO::FETCH_NUM)) {
                foreach ($row as &$cell) {
                    $cell = (string) $cell;
                    if (preg_match('/^[=+\\-@]/', $cell)) $cell = "'" . $cell;
                }
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }
        $salesChartLabels = array_column($reportRows, 'report_day');
        $salesChartValues = array_map('floatval', array_column($reportRows, 'paid_total'));
        $reportStats = [
            'total_sales' => array_sum($salesChartValues),
            'total_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE DATE(created_at) BETWEEN ' . $pdo->quote($reportFrom) . ' AND ' . $pdo->quote($reportTo))->fetchColumn(),
            'pending_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "pending" AND DATE(created_at) BETWEEN ' . $pdo->quote($reportFrom) . ' AND ' . $pdo->quote($reportTo))->fetchColumn(),
            'delivered_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "delivered" AND DATE(created_at) BETWEEN ' . $pdo->quote($reportFrom) . ' AND ' . $pdo->quote($reportTo))->fetchColumn(),
            'cancelled_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "cancelled" AND DATE(created_at) BETWEEN ' . $pdo->quote($reportFrom) . ' AND ' . $pdo->quote($reportTo))->fetchColumn(),
        ];
        $productPerformance = $pdo->prepare('SELECT oi.product_id, oi.product_name, SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.payment_status = "verified" AND DATE(o.created_at) BETWEEN :from_date AND :to_date GROUP BY oi.product_id, oi.product_name ORDER BY revenue DESC LIMIT 10');
        $productPerformance->execute(['from_date' => $reportFrom, 'to_date' => $reportTo]);
        $productPerformance = $productPerformance->fetchAll();
        $inventorySummary = $pdo->query('SELECT COUNT(*) AS active_variants, COALESCE(SUM(stock_quantity),0) AS units_in_stock, SUM(stock_quantity = 0) AS out_of_stock, SUM(stock_quantity > 0 AND stock_quantity <= low_stock_threshold) AS low_stock FROM product_variants WHERE is_active = 1')->fetch();
    }
}

$view = dirname(__DIR__) . '/admin/views/' . $page . '.php';
if (!is_file($view)) $view = dirname(__DIR__) . '/admin/views/dashboard.php';
if ($page === 'login') {
    require dirname(__DIR__) . '/admin/views/login.php';
    exit;
}
require dirname(__DIR__) . '/admin/views/header.php';
if (!$pdo) {
    echo '<div class="admin-alert" role="status">Studio database connection is unavailable. Check the database setup in the installation guide.</div>';
    require dirname(__DIR__) . '/admin/views/footer.php';
    exit;
}
if ($flashMessage) echo '<div class="admin-alert" role="status">' . h($flashMessage['message']) . '</div>';
require $view;
require dirname(__DIR__) . '/admin/views/footer.php';
