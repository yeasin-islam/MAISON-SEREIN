<?php
$cancellationWindow = max(0, min(168, (int) ($settings['customer_cancellation_window_hours'] ?? 24)));
$cancellationPolicy = trim((string) ($settings['cancellation_policy'] ?? ''));
?>
<section class="page-hero page-hero-small delivery-hero">
  <span class="eyebrow">From our house to yours</span>
  <h1>Delivery &amp; returns.</h1>
  <p>Clear information about delivery areas, charges, order tracking, and the policies currently supported by the store.</p>
</section>

<section class="section delivery-section">
  <div class="delivery-intro">
    <span class="eyebrow">Delivery coverage</span>
    <h2>Choose the area that matches your address.</h2>
    <p>Checkout offers the active delivery zones listed here. We do not promise service outside the areas currently configured in the store.</p>
  </div>
  <?php if ($deliveryZones): ?>
    <div class="delivery-zone-grid">
      <?php foreach ($deliveryZones as $zone): ?>
        <article class="delivery-zone-card">
          <span class="delivery-zone-mark" aria-hidden="true">01</span>
          <h3><?= h($zone['name']) ?></h3>
          <dl>
            <div>
              <dt>Delivery fee</dt>
              <dd><?= money($zone['delivery_fee']) ?></dd>
            </div>
            <?php if (!empty($zone['estimated_days'])): ?><div>
                <dt>Estimated delivery</dt>
                <dd><?= h($zone['estimated_days']) ?></dd>
              </div><?php endif; ?>
          </dl>
          <?php if (!empty($zone['instructions'])): ?><p><?= h($zone['instructions']) ?></p><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <h3>No delivery zones are currently published.</h3>
      <p>Please check back later or use the contact page for an order question.</p><a class="text-link" href="<?= h(app_url('contact')) ?>">Contact the house →</a>
    </div>
  <?php endif; ?>
</section>

<section class="delivery-guidance">
  <div class="delivery-guidance-image"><img src="assets/images/atelier-hero.png" alt="A fragrance prepared for its journey" loading="lazy"></div>
  <div class="delivery-guidance-copy">
    <span class="eyebrow">Order care</span>
    <h2>Know what to expect after checkout.</h2>
    <p>The application does not publish a separate order-processing time. The estimated delivery window shown for your selected zone is the current store estimate; actual timing can vary.</p>
    <p>To follow an order, use its order number and the phone number provided at checkout.</p>
    <a class="button button-dark" href="<?= h(app_url('track')) ?>">Track an order <span aria-hidden="true">→</span></a>
  </div>
</section>

<section class="section delivery-policy-grid">
  <article class="delivery-policy-card">
    <span class="eyebrow">Cancellations</span>
    <h2>Before an order moves forward.</h2>
    <?php if ($cancellationPolicy): ?><p><?= h($cancellationPolicy) ?></p><?php endif; ?>
    <p>Customers may cancel only while an order is pending or confirmed and within the configured <?= $cancellationWindow ?>-hour window. Once it is processing, shipped, or delivered, the customer cancellation action is no longer available.</p>
    <p>Eligible cancellation may restore stock according to the store setting. Payment refunds are not automatic.</p>
  </article>
  <article class="delivery-policy-card">
    <span class="eyebrow">Returns &amp; refunds</span>
    <h2>Ask us before sending anything back.</h2>
    <p>The current application does not publish a product-return eligibility policy or automate refunds. If an order arrives damaged or incorrect, contact customer care with your order number and details so the store can review the issue.</p>
    <p>Please do not assume a return or refund is approved until the store has responded. Any payment-provider refund must be handled manually outside this website.</p>
    <a class="text-link" href="<?= h(app_url('contact')) ?>">Contact us about an order →</a>
  </article>
</section>

<section class="delivery-faq">
  <span class="eyebrow">Still wondering?</span>
  <h2>Find a little more clarity.</h2>
  <p>Visit the FAQ for ordering, payment, product, and delivery questions.</p>
  <a class="button button-outline" href="<?= h(app_url('faq')) ?>">Read the FAQ <span aria-hidden="true">→</span></a>
</section>