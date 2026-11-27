<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

try {
    $pdo = database();
    $requiredTables = ['admins', 'customers', 'customer_addresses', 'categories', 'brands', 'products', 'product_images', 'product_variants', 'inventory_transactions', 'orders', 'order_email_notifications', 'order_items', 'payments', 'order_status_history', 'reviews', 'discounts', 'discount_usage', 'wishlist_items', 'banners', 'homepage_sections', 'social_links', 'delivery_zones', 'store_settings', 'limited_drops', 'preorders', 'notifications', 'activity_logs', 'newsletter_subscribers'];
    $tableRows = $pdo->query('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchAll(PDO::FETCH_COLUMN);
    $missingTables = array_values(array_diff($requiredTables, $tableRows));
    if ($missingTables) {
        throw new RuntimeException('Missing tables: ' . implode(', ', $missingTables));
    }

    $productCount = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();
    $variantCount = (int) $pdo->query('SELECT COUNT(*) FROM product_variants WHERE is_active = 1')->fetchColumn();
    $zoneCount = (int) $pdo->query('SELECT COUNT(*) FROM delivery_zones WHERE is_active = 1')->fetchColumn();
    if ($productCount === 0 || $variantCount === 0 || $zoneCount === 0) {
        throw new RuntimeException('The catalogue, active sizes, or delivery zones are empty. Import the seeded SQL file.');
    }
    $negativeStock = (int) $pdo->query('SELECT COUNT(*) FROM product_variants WHERE stock_quantity < 0')->fetchColumn();
    if ($negativeStock !== 0) throw new RuntimeException('One or more product variants have negative stock.');
    $paymentSetting = $pdo->query('SELECT setting_value FROM store_settings WHERE setting_key="accepted_payment_methods"')->fetchColumn();
    $acceptedPayments = json_decode((string) ($paymentSetting ?: '["bkash","nagad","cod"]'), true);
    if (!is_array($acceptedPayments) || !$acceptedPayments || array_diff($acceptedPayments, ['bkash', 'nagad', 'cod'])) throw new RuntimeException('Accepted payment methods are missing or invalid.');

    $images = $pdo->query('SELECT image_path FROM product_images')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($images as $image) {
        $path = dirname(__DIR__) . '/' . ltrim((string) $image, '/');
        if (!is_file($path)) {
            throw new RuntimeException('Missing product image: ' . $image);
        }
    }

    fwrite(STDOUT, "PASS: MySQL connection and required schema\nPASS: {$productCount} active products, {$variantCount} purchasable sizes, {$zoneCount} delivery zones\nPASS: " . count($images) . " product image paths\n");
    $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins WHERE is_active = 1')->fetchColumn();
    if ($adminCount === 0) {
        fwrite(STDOUT, "NOTE: no admin account yet; run scripts/create-admin.php from the CLI\n");
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
