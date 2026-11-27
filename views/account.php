<?php
$accountCustomerId = current_customer_id();
$pdo = database();
$accountCustomerId = (int) $accountCustomerId;
$accountName = trim((string) ($_SESSION['customer_name'] ?? ''));
if ($accountName === '') $accountName = 'there';

$summaryQuery = $pdo->prepare(
  "SELECT COUNT(*) AS total_orders,
        COALESCE(SUM(order_status = 'pending'), 0) AS pending_orders,
        COALESCE(SUM(order_status = 'delivered'), 0) AS completed_orders
     FROM orders
     WHERE customer_id = :customer_id"
);
$summaryQuery->execute(['customer_id' => $accountCustomerId]);
$orderSummary = $summaryQuery->fetch() ?: ['total_orders' => 0, 'pending_orders' => 0, 'completed_orders' => 0];

$ordersQuery = $pdo->prepare(
  'SELECT o.id, o.order_number, o.order_status, o.payment_status, o.grand_total, o.created_at,
        COALESCE((SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id), 0) AS item_count
     FROM orders o
     WHERE o.customer_id = :customer_id
     ORDER BY o.created_at DESC
     LIMIT 5'
);
$ordersQuery->execute(['customer_id' => $accountCustomerId]);
$accountOrders = $ordersQuery->fetchAll();

$orderItemsByOrder = [];
if ($accountOrders) {
  $orderIds = array_map(static function (array $order): int {
    return (int) $order['id'];
  }, $accountOrders);
  $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
  $itemsQuery = $pdo->prepare(
    'SELECT oi.order_id, oi.product_id, oi.product_name, oi.size_label, oi.quantity, oi.unit_price, oi.line_total, p.slug
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id IN (' . $placeholders . ')
         ORDER BY oi.id'
  );
  $itemsQuery->execute($orderIds);
  foreach ($itemsQuery->fetchAll() as $item) {
    $orderItemsByOrder[(int) $item['order_id']][] = $item;
  }
}

$accountAddresses = (new StoreService($pdo))->customerAddresses($accountCustomerId);
$wishlistCountQuery = $pdo->prepare('SELECT COUNT(*) FROM wishlist_items WHERE customer_id = :customer_id');
$wishlistCountQuery->execute(['customer_id' => $accountCustomerId]);
$wishlistCount = (int) $wishlistCountQuery->fetchColumn();
$wishlistQuery = $pdo->prepare(
  'SELECT p.id, p.name, p.slug, p.short_description, b.name AS brand_name,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS image_path,
        (SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1) AS min_price,
        (SELECT v.id FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1 AND v.stock_quantity > 0 ORDER BY v.sort_order, v.price, v.id LIMIT 1) AS available_variant_id
     FROM wishlist_items wi
     JOIN products p ON p.id = wi.product_id
     JOIN brands b ON b.id = p.brand_id
     WHERE wi.customer_id = :customer_id AND p.is_active = 1 AND p.is_visible = 1
     ORDER BY wi.created_at DESC'
);
$wishlistQuery->execute(['customer_id' => $accountCustomerId]);
$accountWishlist = $wishlistQuery->fetchAll();
?>

<section class="section account-dashboard">
  <header class="account-hero">
    <div class="account-hero-copy">
      <span class="eyebrow">Your account</span>
      <h1>Hello, <?= h($accountName) ?>.</h1>
      <p>Your orders, details and saved fragrances, together in one quiet corner.</p>
    </div>
    <div class="account-avatar" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4" />
        <path d="M5 21a7 7 0 0 1 14 0" />
      </svg>
    </div>
  </header>

  <nav class="account-nav" aria-label="Account navigation" data-account-nav>
    <a href="#account-overview" aria-current="page">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="8" height="8" rx="1" />
        <rect x="13" y="3" width="8" height="5" rx="1" />
        <rect x="13" y="10" width="8" height="11" rx="1" />
        <rect x="3" y="13" width="8" height="8" rx="1" />
      </svg>
      Overview
    </a>
    <a href="#account-orders">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M8 3H5a2 2 0 0 0-2 2v16h18V5a2 2 0 0 0-2-2h-3" />
        <path d="M8 3h8v4H8zM8 12h8m-8 4h8" />
      </svg>
      My orders
    </a>
    <a href="#account-personal">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4" />
        <path d="M5 21a7 7 0 0 1 14 0" />
      </svg>
      Personal information
    </a>
    <a href="#account-addresses">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
        <circle cx="12" cy="10" r="2.5" />
      </svg>
      Saved addresses
    </a>
    <a href="#account-wishlist">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" />
      </svg>
      Wishlist
    </a>
    <form action="api/index.php" method="post" data-api-form data-account-signout>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="customer.logout">
      <button type="submit">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 17l5-5-5-5m5 5H3" />
          <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" />
        </svg>
        Sign out
      </button>
    </form>
  </nav>

  <section class="account-panel" id="account-overview" aria-labelledby="account-overview-title">
    <div class="account-section-heading">
      <div>
        <span class="eyebrow">At a glance</span>
        <h2 id="account-overview-title">Your overview</h2>
      </div>
    </div>
    <div class="account-stat-grid">
      <article class="account-stat"><span>Total orders</span><strong><?= (int) $orderSummary['total_orders'] ?></strong></article>
      <article class="account-stat"><span>Pending orders</span><strong><?= (int) $orderSummary['pending_orders'] ?></strong></article>
      <article class="account-stat"><span>Completed orders</span><strong><?= (int) $orderSummary['completed_orders'] ?></strong></article>
      <article class="account-stat"><span>Wishlist items</span><strong data-wishlist-total><?= $wishlistCount ?></strong></article>
    </div>
  </section>

  <section class="account-panel" id="account-orders" aria-labelledby="account-orders-title">
    <div class="account-section-heading">
      <div>
        <span class="eyebrow">Your purchases</span>
        <h2 id="account-orders-title">Recent orders</h2>
      </div>
      <?php if ($accountOrders): ?><a class="text-link" href="<?= h(app_url('track')) ?>">Track an order <span aria-hidden="true">→</span></a><?php endif; ?>
    </div>
    <?php if (!$accountOrders): ?>
      <div class="account-empty">
        <h3>Your first fragrance is still out there.</h3>
        <p>When you place an order, its details will find a home here.</p>
        <a class="button button-dark" href="<?= h(app_url('shop')) ?>">Explore the collection</a>
      </div>
    <?php else: ?>
      <div class="account-orders">
        <?php foreach ($accountOrders as $order): ?>
          <?php $orderItems = $orderItemsByOrder[(int) $order['id']] ?? []; ?>
          <article class="account-order">
            <div class="account-order-top">
              <div>
                <span class="eyebrow">Order <?= h($order['order_number']) ?></span>
                <span class="order-date"><?= h(date('j M Y', strtotime($order['created_at']))) ?></span>
              </div>
              <span class="status-pill status-<?= h($order['order_status']) ?>"><?= h(ucfirst($order['order_status'])) ?></span>
            </div>
            <div class="account-order-summary">
              <div><span>Items</span><strong><?= (int) $order['item_count'] ?></strong></div>
              <div><span>Total</span><strong><?= money($order['grand_total']) ?></strong></div>
              <div><span>Payment</span><span class="status-pill status-<?= h($order['payment_status']) ?>"><?= h(ucfirst(str_replace('_', ' ', $order['payment_status']))) ?></span></div>
            </div>
            <details class="account-order-details">
              <summary>View details <span aria-hidden="true">＋</span></summary>
              <div class="account-order-detail-content">
                <?php if ($orderItems): ?>
                  <ul class="account-order-items">
                    <?php foreach ($orderItems as $item): ?>
                      <li>
                        <span><?= h($item['product_name']) ?> <small><?= h($item['size_label']) ?> · Qty <?= (int) $item['quantity'] ?></small></span>
                        <strong><?= money($item['line_total']) ?></strong>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <p>Item details are not available for this order.</p>
                <?php endif; ?>
                <?php if ($order['order_status'] === 'delivered'): ?>
                  <?php foreach ($orderItems as $reviewItem): ?>
                    <?php if (!$reviewItem['product_id']) continue; ?>
                    <details class="review-form-details">
                      <summary>Leave a note for <?= h($reviewItem['product_name']) ?></summary>
                      <form action="api/index.php" method="post" data-api-form data-success="Your note was sent for review.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="review.submit">
                        <input type="hidden" name="product_id" value="<?= (int) $reviewItem['product_id'] ?>">
                        <label>Rating<select name="rating" required>
                            <option value="">Choose a rating</option><?php for ($rating = 5; $rating >= 1; $rating--): ?><option value="<?= $rating ?>"><?= $rating ?> / 5</option><?php endfor; ?>
                          </select></label>
                        <label>Title <span class="field-optional">(optional)</span><input name="review_title" maxlength="160"></label>
                        <label>Your note<textarea name="review_body" minlength="8" maxlength="2000" required rows="3"></textarea></label>
                        <button class="button button-outline" type="submit">Send for review</button>
                        <p class="form-message" aria-live="polite"></p>
                      </form>
                    </details>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </details>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="account-panel" id="account-personal" aria-labelledby="account-personal-title">
    <div class="account-section-heading">
      <div>
        <span class="eyebrow">Your details</span>
        <h2 id="account-personal-title">Personal information</h2>
      </div>
    </div>
    <form class="account-form-grid" action="api/index.php" method="post" data-api-form>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="customer.profile.update">
      <label>Full name<input name="full_name" value="<?= h($_SESSION['customer_name'] ?? '') ?>" required minlength="2" maxlength="120" autocomplete="name"></label>
      <label>Email address<input name="email" type="email" value="<?= h($_SESSION['customer_email'] ?? '') ?>" maxlength="190" autocomplete="email"></label>
      <label>Mobile number<input name="phone" type="tel" value="<?= h($_SESSION['customer_phone'] ?? '') ?>" required maxlength="24" autocomplete="tel"></label>
      <div class="account-form-actions"><button class="button button-dark" type="submit">Save changes</button>
        <p class="form-message" aria-live="polite"></p>
      </div>
    </form>
  </section>

  <section class="account-panel" id="account-addresses" aria-labelledby="account-addresses-title">
    <div class="account-section-heading">
      <div>
        <span class="eyebrow">For next time</span>
        <h2 id="account-addresses-title">Saved addresses</h2>
      </div>
    </div>
    <div class="account-address-grid">
      <?php foreach ($accountAddresses as $savedAddress): ?>
        <article class="account-address-card">
          <div class="account-address-heading">
            <h3><?= h($savedAddress['label']) ?></h3>
            <?php if ((int) $savedAddress['is_default']): ?><span class="account-default-label">Default address</span><?php endif; ?>
          </div>
          <div class="account-address-copy">
            <strong><?= h($savedAddress['recipient_name']) ?></strong>
            <a href="tel:<?= h(preg_replace('/[^+\d]/', '', $savedAddress['phone'])) ?>"><?= h($savedAddress['phone']) ?></a>
            <span><?= h($savedAddress['district']) ?></span>
            <p><?= nl2br(h($savedAddress['address'])) ?></p>
          </div>
          <div class="account-address-actions">
            <details class="account-address-edit">
              <summary>Edit</summary>
              <form action="api/index.php" method="post" data-api-form data-success="Address saved.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="customer.address.save">
                <input type="hidden" name="address_id" value="<?= (int) $savedAddress['id'] ?>">
                <label>Address label<input name="label" maxlength="50" required value="<?= h($savedAddress['label']) ?>"></label>
                <label>Recipient name<input name="recipient_name" maxlength="120" required value="<?= h($savedAddress['recipient_name']) ?>"></label>
                <label>Phone<input name="phone" maxlength="24" required value="<?= h($savedAddress['phone']) ?>"></label>
                <label>District<input name="district" maxlength="120" required value="<?= h($savedAddress['district']) ?>"></label>
                <label>Full address<textarea name="address" maxlength="500" required minlength="8" rows="3"><?= h($savedAddress['address']) ?></textarea></label>
                <label class="account-checkbox"><input type="checkbox" name="is_default" value="1" <?= (int) $savedAddress['is_default'] ? 'checked' : '' ?>><span>Use as default address</span></label>
                <button class="button button-outline" type="submit">Save address</button>
                <p class="form-message" aria-live="polite"></p>
              </form>
            </details>
            <?php if (!(int) $savedAddress['is_default']): ?>
              <form action="api/index.php" method="post" data-api-form data-success="Default address updated.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="customer.address.default">
                <input type="hidden" name="address_id" value="<?= (int) $savedAddress['id'] ?>">
                <button class="account-text-button" type="submit">Set as default</button>
              </form>
            <?php endif; ?>
            <form action="api/index.php" method="post" data-api-form data-success="Address removed.">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="customer.address.delete">
              <input type="hidden" name="address_id" value="<?= (int) $savedAddress['id'] ?>">
              <button class="account-text-button account-remove-button" type="submit">Remove</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>

      <article class="account-address-card account-address-new">
        <div class="account-address-heading">
          <h3>Add a new address</h3>
          <span class="account-address-add-icon" aria-hidden="true">+</span>
        </div>
        <p class="account-address-prompt">Keep another delivery address ready for your next order.</p>
        <form action="api/index.php" method="post" data-api-form data-success="Address saved.">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="customer.address.save">
          <label>Address label<input name="label" maxlength="50" placeholder="Home, Work" required></label>
          <label>Recipient name<input name="recipient_name" maxlength="120" required autocomplete="name"></label>
          <label>Phone<input name="phone" maxlength="24" required autocomplete="tel"></label>
          <label>District<input name="district" maxlength="120" required></label>
          <label>Full address<textarea name="address" maxlength="500" minlength="8" required rows="3"></textarea></label>
          <label class="account-checkbox"><input type="checkbox" name="is_default" value="1"><span>Use as default address</span></label>
          <button class="button button-dark" type="submit">Save address</button>
          <p class="form-message" aria-live="polite"></p>
        </form>
      </article>
    </div>
  </section>

  <section class="account-panel" id="account-wishlist" aria-labelledby="account-wishlist-title">
    <div class="account-section-heading">
      <div>
        <span class="eyebrow">Kept close</span>
        <h2 id="account-wishlist-title">Your wishlist</h2>
      </div>
      <a class="text-link" href="<?= h(app_url('wishlist')) ?>">View wishlist <span aria-hidden="true">→</span></a>
    </div>
    <div class="account-wishlist-grid" data-account-wishlist-grid <?= $accountWishlist ? '' : 'hidden' ?>>
      <?php foreach ($accountWishlist as $wishlistProduct): ?>
        <article class="account-wishlist-card" data-account-wishlist-item>
          <a class="account-wishlist-image" href="<?= h(app_url('product', ['id' => $wishlistProduct['id']])) ?>">
            <img src="<?= h($wishlistProduct['image_path'] ?: 'assets/images/amber-veil.png') ?>" alt="<?= h($wishlistProduct['name']) ?>" loading="lazy">
          </a>
          <div class="account-wishlist-copy">
            <span class="eyebrow"><?= h($wishlistProduct['brand_name']) ?></span>
            <h3><a href="<?= h(app_url('product', ['id' => $wishlistProduct['id']])) ?>"><?= h($wishlistProduct['name']) ?></a></h3>
            <?php if (!empty($wishlistProduct['short_description'])): ?><p><?= h($wishlistProduct['short_description']) ?></p><?php endif; ?>
            <strong class="account-wishlist-price"><?= money($wishlistProduct['min_price']) ?></strong>
            <span class="account-stock <?= $wishlistProduct['available_variant_id'] ? 'is-available' : 'is-unavailable' ?>"><?= $wishlistProduct['available_variant_id'] ? 'Available' : 'Currently unavailable' ?></span>
          </div>
          <div class="account-wishlist-actions">
            <a class="button button-outline" href="<?= h(app_url('product', ['id' => $wishlistProduct['id']])) ?>">View product</a>
            <?php if ($wishlistProduct['available_variant_id']): ?>
              <form action="api/index.php" method="post" data-api-form data-success="Added to your bag.">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="cart.add">
                <input type="hidden" name="variant_id" value="<?= (int) $wishlistProduct['available_variant_id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="button button-dark" type="submit">Add to bag</button>
              </form>
            <?php endif; ?>
            <form action="api/index.php" method="post" data-api-form data-wishlist-remove>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="wishlist.toggle">
              <input type="hidden" name="product_id" value="<?= (int) $wishlistProduct['id'] ?>">
              <button class="account-text-button account-remove-button" type="submit">Remove from wishlist</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="account-empty account-wishlist-empty" data-account-wishlist-empty <?= $accountWishlist ? 'hidden' : '' ?>>
      <h3>Keep the fragrances you love close.</h3>
      <p>Your saved pieces will appear here when you add them to your wishlist.</p>
      <a class="button button-outline" href="<?= h(app_url('shop')) ?>">Explore the collection</a>
    </div>
  </section>
</section>