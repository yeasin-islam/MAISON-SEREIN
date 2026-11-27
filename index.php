<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/services/StoreService.php';

$page = preg_replace('/[^a-z-]/', '', (string) ($_GET['page'] ?? 'home')) ?: 'home';
$allowedPages = ['home', 'shop', 'product', 'cart', 'checkout', 'success', 'track', 'login', 'register', 'account', 'about', 'contact', 'delivery', 'discovery-sets', 'faq', 'wishlist', 'mood'];
if (!in_array($page, $allowedPages, true)) $page = 'not-found';
if ($page === 'account' && !current_customer_id()) redirect(app_url('login', ['next' => 'account']));

$store = null;
$settings = [];
$databaseAvailable = true;
if ($page !== 'faq') {
    try {
        $store = new StoreService(database());
        $settings = $store->settings();
    } catch (Throwable $error) {
        $databaseAvailable = false;
        $settings = [];
        error_log('[THE LAST NOTE BD] Store database unavailable: ' . $error->getMessage());
    }
}
$storeContactEmail = trim((string) ($settings['store_email'] ?? ''));
$contactEmailDomain = strtolower(substr(strrchr($storeContactEmail, '@') ?: '', 1));
if (!filter_var($storeContactEmail, FILTER_VALIDATE_EMAIL) || preg_match('/(?:^|\.)example$/', $contactEmailDomain)) {
    $storeContactEmail = '';
}
$storeContactPhone = trim((string) ($settings['store_phone'] ?? ''));
if (!valid_phone($storeContactPhone) || normalize_phone($storeContactPhone) === '01700000000') {
    $storeContactPhone = '';
}
$flashMessage = take_flash();
$products = [];
$wishlistIds = array_map('intval', $_SESSION['wishlist'] ?? []);
$product = null;
$cartItems = [];
$featuredReviews = [];
$categories = [];
$brands = [];
$deliveryZones = [];
$newArrivals = [];
$bestSellers = [];
$featuredProducts = [];
$menProducts = [];
$womenProducts = [];
$unisexProducts = [];
$heroBanners = [];
$promoBanners = [];
$popupBanners = [];
$featuredReviews = [];
$socialLinks = [];
$limitedDrops = [];
$homepageSections = [];
$savedAddresses = [];
$productReviews = [];
$moodProducts = [];
$relatedProducts = [];
$collectionProducts = [];
$currentPage = 1;
$productsPerPage = 12;
$totalProducts = 0;
$totalPages = 1;
$filterError = '';

if ($store) {
    try {
        if (current_customer_id() && in_array($page, ['home', 'shop', 'product', 'wishlist', 'mood'], true)) {
            $wish = database()->prepare('SELECT product_id FROM wishlist_items WHERE customer_id = :id');
            $wish->execute(['id' => current_customer_id()]);
            $wishlistIds = array_map('intval', array_column($wish->fetchAll(), 'product_id'));
        }
        if ($page === 'home') {
            $products = $store->products(['best_seller' => 1, 'sort' => 'popular'], 4);
            $newArrivals = $store->products(['new_arrival' => 1, 'sort' => 'new'], 4);
            $featuredProducts = $store->products(['featured' => 1], 4);
            $homepageSections = $store->homepageSections();
            $menProducts = $store->products(['gender' => 'men'], 4);
            $womenProducts = $store->products(['gender' => 'women'], 4);
            $unisexProducts = $store->products(['gender' => 'unisex'], 4);
            $heroBanners = $store->banners('hero');
            $promoBanners = $store->banners('promo');
            $popupBanners = $store->banners('popup');
            $featuredReviews = $store->reviews(3);
            $socialLinks = $store->socialLinks();
            $limitedDrops = $store->limitedDrops();
        } elseif ($page === 'shop') {
            $categories = $store->categories();
            $brands = $store->brands();
            $queryString = static function (string $key): string {
                $value = $_GET[$key] ?? '';
                if (!is_string($value)) throw new InvalidArgumentException('Filter values must be plain text.');
                return trim($value);
            };
            $filters = [
                'search' => $queryString('q'),
                'category' => $queryString('category'),
                'gender' => $queryString('gender'),
                'brand' => $queryString('brand'),
                'sort' => $queryString('sort') ?: 'featured',
                'availability' => $queryString('availability'),
                'featured' => $queryString('featured') === '1' ? 1 : 0,
                'best_seller' => $queryString('best_seller') === '1' ? 1 : 0,
                'new_arrival' => $queryString('new_arrival') === '1' ? 1 : 0,
                'min_price' => $queryString('min_price'),
                'max_price' => $queryString('max_price'),
            ];
            $pageValue = $_GET['p'] ?? '1';
            $requestedPage = is_string($pageValue) || is_int($pageValue)
                ? filter_var($pageValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;
            $requestedPage = $requestedPage === false ? 1 : (int) $requestedPage;
            $totalProducts = $store->countProducts($filters);
            $totalPages = max(1, (int) ceil($totalProducts / $productsPerPage));
            $currentPage = min($requestedPage, $totalPages);
            $products = $store->products($filters, $productsPerPage, ($currentPage - 1) * $productsPerPage);
        } elseif ($page === 'product') {
            $product = $store->product((int) ($_GET['id'] ?? 0));
            $relatedProducts = $product ? $store->products(['category' => (string) ($product['category_slug'] ?? ''), 'exclude_id' => $product['id']], 4) : [];
        } elseif ($page === 'mood') {
            $profile = (string) ($_GET['profile'] ?? '');
            $occasion = (string) ($_GET['occasion'] ?? '');
            $mood = (string) ($_GET['mood'] ?? '');
            $moodProducts = $profile !== '' || $occasion !== ''
                ? $store->scentFinderProducts($profile, $occasion, 8)
                : ($mood !== '' ? $store->moodProducts($mood, 8) : []);
        } elseif ($page === 'discovery-sets') {
            $collectionProducts = $store->products(['category' => 'discovery-sets'], 24);
        } elseif ($page === 'delivery') {
            $deliveryZones = $store->deliveryZones();
        } elseif (in_array($page, ['cart', 'checkout'], true)) {
            $cartItems = $store->cartItems($_SESSION['cart'] ?? []);
            if ($page === 'checkout') {
                $deliveryZones = $store->deliveryZones();
                $savedAddresses = current_customer_id() ? $store->customerAddresses(current_customer_id()) : [];
            }
        } elseif ($page === 'wishlist') {
            $products = [];
            foreach ($wishlistIds as $wishlistId) {
                if ($saved = $store->product($wishlistId)) $products[] = $saved;
            }
        }
    } catch (InvalidArgumentException $error) {
        if ($page === 'shop') {
            $filterError = $error->getMessage();
        } else {
            $databaseAvailable = false;
            error_log('[THE LAST NOTE BD] Store query failed: ' . $error->getMessage());
        }
    } catch (Throwable $error) {
        $databaseAvailable = false;
        error_log('[THE LAST NOTE BD] Store query failed: ' . $error->getMessage());
    }
}

if ($page === 'product' && !$product) $page = 'not-found';
$viewPath = __DIR__ . '/views/' . $page . '.php';
if (!is_file($viewPath)) $viewPath = __DIR__ . '/views/not-found.php';
require __DIR__ . '/includes/header.php';
if (!$databaseAvailable) {
    echo '<div class="setup-notice" role="status"><strong>Store setup required.</strong> Import <code>database/perfume_ecommerce.sql</code> into MySQL and configure <code>.env</code> to load the live catalogue.</div>';
}
if ($flashMessage) {
    echo '<div class="flash-message flash-' . h($flashMessage['type']) . '" role="status">' . h($flashMessage['message']) . '</div>';
}
require $viewPath;
require __DIR__ . '/includes/footer.php';
