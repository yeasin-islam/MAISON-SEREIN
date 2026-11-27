<?php
$adminNav = ['dashboard' => 'Overview', 'products' => 'Fragrance', 'orders' => 'Orders', 'inventory' => 'Inventory', 'customers' => 'Customers', 'reviews' => 'Reviews', 'discounts' => 'Offers', 'content' => 'Homepage', 'delivery' => 'Delivery', 'drops' => 'Limited drops', 'preorders' => 'Pre-orders', 'reports' => 'Reports', 'activity' => 'Activity log', 'settings' => 'House settings'];
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f2ed">
  <title><?= h(ucfirst($page)) ?> — THE LAST NOTE BD Studio</title>
  <link rel="icon" href="../assets/images/the-last-note-logo.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/store.css?v=2">
  <link rel="stylesheet" href="../assets/css/store-extras.css">
  <link rel="stylesheet" href="../assets/css/store-interactions.css?v=2">
  <link rel="stylesheet" href="../assets/css/store-redesign.css?v=19">
  <link rel="stylesheet" href="../assets/css/store-account.css">
  <link rel="stylesheet" href="../assets/css/admin-operations.css?v=4"><?php if (in_array($page, ['dashboard', 'reports'], true)): ?><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js" defer></script><?php endif; ?>
</head>

<body class="admin-body">
  <button class="admin-sidebar-backdrop" type="button" aria-label="Close studio navigation" data-admin-menu-backdrop hidden></button>
  <aside class="admin-sidebar" id="admin-sidebar" aria-label="Studio menu">
    <div class="admin-sidebar-brand-row">
      <a class="wordmark brand-lockup wordmark-light" href="../index.php" aria-label="THE LAST NOTE BD admin">
      <img class="brand-logo" src="../assets/images/the-last-note-logo.svg" alt="" width="38" height="38">
      <span class="brand-copy"><span class="brand-name"><span>THE LAST</span><span>NOTE BD</span></span><small>AUTHENTIC FRAGRANCE DECANT HOUSE</small></span>
      </a>
      <button class="admin-sidebar-close" type="button" aria-label="Close studio navigation" data-admin-menu-close>×</button>
    </div>
    <nav aria-label="Studio navigation"><?php foreach ($adminNav as $key => $label): ?><a class="<?= $page === $key ? 'is-current' : '' ?>" href="?page=<?= h($key) ?>"><?= h($label) ?></a><?php endforeach; ?></nav>
    <a class="admin-store-link" href="../index.php" target="_blank" rel="noopener">View storefront ↗</a>
    <form class="admin-logout" action="../api/index.php" method="post" data-api-form data-redirect-success="../index.php"><?= csrf_field() ?><input type="hidden" name="action" value="admin.logout"><button type="submit">Sign out</button></form>
  </aside>
  <main class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-toggle" type="button" aria-label="Open studio navigation" aria-controls="admin-sidebar" aria-expanded="false" data-admin-menu-toggle>
        <span></span><span></span><span></span>
      </button>
      <div><span class="eyebrow">THE LAST NOTE BD / Studio</span>
        <h1><?= h($adminNav[$page] ?? 'Studio') ?></h1>
      </div><span class="admin-greeting">A quiet day, <?= h($_SESSION['admin_name'] ?? 'friend') ?>.</span>
    </header>
    <div class="admin-content">