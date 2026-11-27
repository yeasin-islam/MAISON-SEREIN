<?php
$searchValue = trim((string) ($_GET['q'] ?? ''));
$pageTitle = $page === 'faq' ? 'FAQ' : ucfirst($page === 'home' ? APP_NAME : str_replace('-', ' ', $page));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f2ed">
  <meta name="description" content="Discover considered fragrance, crafted for the moments that are yours.">
  <title><?= h($pageTitle . ' — Maison Serein') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/store.css">
  <link rel="stylesheet" href="assets/css/store-extras.css"><link rel="stylesheet" href="assets/css/store-account.css">
  <link rel="stylesheet" href="assets/css/store-interactions.css">
  <link rel="stylesheet" href="assets/css/store-redesign.css?v=16">
</head>
<body>
<div class="announcement">Complimentary delivery on orders over ৳5,000 <span aria-hidden="true">—</span> A little something, on us.</div>
<header class="site-header">
  <div class="header-inner">
    <a class="wordmark" href="<?= h(app_url('home')) ?>" aria-label="Maison Serein home">MAISON <span>SEREIN</span><small>FRAGRANCE, CONSIDERED</small></a>
    <button class="mobile-menu-button" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle><span></span><span></span></button>
    <nav class="main-nav" aria-label="Main navigation" data-main-nav>
      <a class="<?= $page === 'shop' ? 'is-current' : '' ?>" href="<?= h(app_url('shop')) ?>">Shop all</a>
      <a href="<?= h(app_url('shop', ['gender' => 'women'])) ?>">For her</a>
      <a href="<?= h(app_url('shop', ['gender' => 'men'])) ?>">For him</a>
      <a href="<?= h(app_url('shop', ['gender' => 'unisex'])) ?>">Unisex</a>
      <a href="<?= h(app_url('mood')) ?>">Scent finder</a>
      <a href="<?= h(app_url('about')) ?>">Our house</a>
      <a class="mobile-nav-extra" href="<?= h(app_url('discovery-sets')) ?>">Discovery sets</a>
      <a class="mobile-nav-extra" href="<?= h(app_url('contact')) ?>">Contact us</a>
      <a class="mobile-nav-extra" href="<?= h(app_url('delivery')) ?>">Delivery &amp; returns</a>
      <a class="mobile-nav-extra" href="<?= h(app_url('track')) ?>">Track an order</a>
      <a class="mobile-nav-extra" href="<?= h(app_url('faq')) ?>">FAQ</a>
    </nav>
    <div class="header-actions">
      <button class="mobile-search-button" type="button" aria-label="Open search" aria-expanded="false" aria-controls="site-search-form" data-search-toggle title="Search"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button>
      <form id="site-search-form" class="search-form" role="search" action="" method="get">
        <input type="hidden" name="page" value="shop">
        <label class="sr-only" for="site-search">Search fragrances</label>
        <input id="site-search" type="search" name="q" placeholder="Search" value="<?= h($searchValue) ?>">
        <button type="submit" aria-label="Search"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button>
      </form>
      <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark theme" aria-pressed="false" title="Switch theme">
        <svg class="theme-icon theme-icon-moon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg>
        <svg class="theme-icon theme-icon-sun" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
      </button>
      <a class="header-icon" href="<?= h(app_url(current_customer_id() ? 'account' : 'login')) ?>" aria-label="Account" title="Account">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/></svg>
      </a>
      <a class="header-icon" href="<?= h(app_url('wishlist')) ?>" aria-label="Wishlist" title="Wishlist">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
      </a>
      <a class="cart-link" href="<?= h(app_url('cart')) ?>" aria-label="Shopping Bag, <?= cart_count() ?> <?= cart_count() === 1 ? 'item' : 'items' ?>" title="Shopping Bag" data-cart-link>
        <svg class="cart-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span class="cart-count" data-cart-count aria-hidden="true"><?= cart_count() ?></span>
      </a>
    </div>
  </div>
</header>
<main id="main-content">
