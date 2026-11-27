</main>
<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand">
      <a class="wordmark brand-lockup wordmark-light" href="<?= h(app_url('home')) ?>" aria-label="THE LAST NOTE BD home">
        <img class="brand-logo" src="assets/images/the-last-note-logo.svg" alt="" width="44" height="44">
        <span class="brand-copy"><span class="brand-name"><span>THE LAST</span><span>NOTE BD</span></span><small>AUTHENTIC FRAGRANCE DECANT HOUSE</small></span>
      </a>
      <p>Quiet compositions, made to feel like your own.</p>
      <div class="footer-contact">
        <?php if ($storeContactEmail): ?><a href="mailto:<?= h($storeContactEmail) ?>"><?= h($storeContactEmail) ?></a><?php endif; ?>
        <?php if ($storeContactPhone): ?><a href="tel:<?= h(preg_replace('/[^+\d]/', '', $storeContactPhone)) ?>"><?= h($storeContactPhone) ?></a><?php endif; ?>
        <?php if (!$storeContactEmail && !$storeContactPhone): ?><span>Customer-care details are not currently published.</span><?php endif; ?>
        <span>Dhaka, Bangladesh</span>
      </div>
    </div>
    <div class="footer-explore"><h2>Explore</h2><a href="<?= h(app_url('shop')) ?>">All fragrances</a><a href="<?= h(app_url('discovery-sets')) ?>">Discovery sets</a><a href="<?= h(app_url('mood')) ?>">Scent finder</a><a href="<?= h(app_url('about')) ?>">Our house</a></div>
    <div class="footer-care"><h2>Customer care</h2><a href="<?= h(app_url('contact')) ?>">Contact us</a><a href="<?= h(app_url('delivery')) ?>">Delivery &amp; returns</a><a href="<?= h(app_url('track')) ?>">Track an order</a><a href="<?= h(app_url('faq')) ?>">FAQ</a></div>
    <div class="newsletter-block"><h2>A slower kind of inbox.</h2><p>Notes on scent, ritual, and new arrivals.</p><form action="api/index.php" method="post" data-api-form data-success="You’re on the list. Thank you.">
      <?= csrf_field() ?><input type="hidden" name="action" value="newsletter.subscribe">
      <label class="sr-only" for="newsletter-email">Email address</label><div class="newsletter-input"><input id="newsletter-email" type="email" name="email" placeholder="Your email address" required><button aria-label="Subscribe" type="submit">→</button></div>
      <p class="form-message" aria-live="polite"></p>
    </form></div>
  </div>
  <div class="footer-bottom"><span>© <?= date('Y') ?> THE LAST NOTE BD. Made with care.</span><span>Dhaka, Bangladesh</span><a href="admin/index.php">Studio login</a></div>
</footer>
<button class="scroll-progress" type="button" aria-label="Scroll to top" title="Back to top" hidden data-scroll-progress>
  <svg class="scroll-progress-ring" aria-hidden="true" viewBox="0 0 48 48">
    <circle class="scroll-progress-track" cx="24" cy="24" r="21" />
    <circle class="scroll-progress-value" cx="24" cy="24" r="21" data-scroll-progress-ring />
  </svg>
  <svg class="scroll-progress-arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 19V5M6.5 10.5 12 5l5.5 5.5" />
  </svg>
</button>
<div class="toast-region" role="status" aria-live="polite" aria-atomic="true" data-toast></div>
<script src="assets/js/store.js?v=14" defer></script>
</body>
</html>
