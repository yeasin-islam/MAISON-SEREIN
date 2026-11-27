<?php
$adminNav = ['dashboard' => 'Overview', 'products' => 'Fragrance', 'orders' => 'Orders', 'inventory' => 'Inventory', 'customers' => 'Customers', 'reviews' => 'Reviews', 'discounts' => 'Offers', 'content' => 'Homepage', 'delivery' => 'Delivery', 'drops' => 'Limited drops', 'preorders' => 'Pre-orders', 'reports' => 'Reports', 'activity' => 'Activity log', 'settings' => 'House settings'];
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f2ed">
  <title><?= h(ucfirst($page)) ?> — Maison Serein Studio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/store.css">
  <link rel="stylesheet" href="../assets/css/store-extras.css">
  <link rel="stylesheet" href="../assets/css/store-interactions.css">
  <link rel="stylesheet" href="../assets/css/store-account.css">
  <link rel="stylesheet" href="../assets/css/admin-operations.css"><?php if (in_array($page, ['dashboard', 'reports'], true)): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js" defer></script><?php endif; ?>
</head>

<body class="admin-body">
  <aside class="admin-sidebar"><a class="wordmark wordmark-light" href="../index.php">MAISON <span>SEREIN</span><small>STUDIO</small></a>
    <nav aria-label="Studio navigation"><?php foreach ($adminNav as $key => $label): ?><a class="<?= $page === $key ? 'is-current' : '' ?>" href="?page=<?= h($key) ?>"><?= h($label) ?></a><?php endforeach; ?></nav><a class="admin-store-link" href="../index.php" target="_blank" rel="noopener">View storefront ↗</a>
    <form class="admin-logout" action="../api/index.php" method="post" data-api-form data-redirect-success="../index.php"><?= csrf_field() ?><input type="hidden" name="action" value="admin.logout"><button type="submit">Sign out</button></form>
  </aside>
  <main class="admin-main">
    <header class="admin-topbar">
      <div><span class="eyebrow">Maison Serein / Studio</span>
        <h1><?= h($adminNav[$page] ?? 'Studio') ?></h1>
      </div><span class="admin-greeting">A quiet day, <?= h($_SESSION['admin_name'] ?? 'friend') ?>.</span>
    </header>
    <div class="admin-content">