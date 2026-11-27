<section class="page-hero page-hero-small contact-hero">
  <span class="eyebrow">A human is here</span>
  <h1>We’re listening.</h1>
  <p>Questions about a scent, an order, or anything in between — we’d love to hear from you.</p>
</section>

<section class="section contact-page-grid">
  <div class="contact-page-intro">
    <span class="eyebrow">Get in touch</span>
    <h2>A little conversation goes a long way.</h2>
    <p>For order questions, include your order number so we can help sooner.</p>
    <div class="contact-detail-list">
      <?php if ($storeContactEmail): ?>
        <a class="contact-detail" href="mailto:<?= h($storeContactEmail) ?>"><span class="contact-detail-icon" aria-hidden="true">✉</span><span><small>Email</small><strong><?= h($storeContactEmail) ?></strong></span></a>
      <?php endif; ?>
      <?php if ($storeContactPhone): ?>
        <a class="contact-detail" href="tel:<?= h(preg_replace('/[^+\d]/', '', $storeContactPhone)) ?>"><span class="contact-detail-icon" aria-hidden="true">↗</span><span><small>Phone</small><strong><?= h($storeContactPhone) ?></strong></span></a>
      <?php endif; ?>
      <div class="contact-detail"><span class="contact-detail-icon" aria-hidden="true">⌖</span><span><small>House</small><strong>Dhaka, Bangladesh</strong></span></div>
    </div>
    <?php if (!$storeContactEmail && !$storeContactPhone): ?>
      <p class="contact-config-note">Customer-care email and phone are not configured with verified contact details. The street address and business hours are not published in the current store settings.</p>
    <?php endif; ?>
  </div>

  <div class="contact-form-panel">
    <span class="eyebrow">Write to the house</span>
    <h2>Send us a note.</h2>
    <?php if ($storeContactEmail): ?>
      <p>Your email app will open with your message addressed to customer care. This site does not store or send contact-form submissions.</p>
      <form class="contact-form" action="mailto:<?= h($storeContactEmail) ?>" method="post" enctype="text/plain" data-contact-mailto data-recipient="<?= h($storeContactEmail) ?>">
        <div class="contact-form-pair">
          <label for="contact-name">Your name<input id="contact-name" name="name" autocomplete="name" required maxlength="120"></label>
          <label for="contact-email">Email address<input id="contact-email" name="email" type="email" autocomplete="email" required maxlength="190"></label>
        </div>
        <label for="contact-subject">Subject<input id="contact-subject" name="subject" required maxlength="120"></label>
        <label for="contact-message">Message<textarea id="contact-message" name="message" rows="6" required maxlength="3000"></textarea></label>
        <button class="button button-dark" type="submit">Open your email app <span aria-hidden="true">↗</span></button>
        <p class="form-message" aria-live="polite"></p>
      </form>
    <?php else: ?>
      <p>The contact form is unavailable until a real customer-care email is configured in the Studio settings. No placeholder address is shown.</p>
      <div class="contact-config-note" role="status">You can still browse <a href="<?= h(app_url('faq')) ?>">frequently asked questions</a> or <a href="<?= h(app_url('track')) ?>">track an order</a>.</div>
    <?php endif; ?>
  </div>
</section>

<section class="contact-help-strip">
  <div><span class="eyebrow">Need a quick answer?</span>
    <h2>Helpful details, gathered in one place.</h2>
  </div>
  <a class="button button-outline" href="<?= h(app_url('faq')) ?>">Visit the FAQ <span aria-hidden="true">→</span></a>
</section>