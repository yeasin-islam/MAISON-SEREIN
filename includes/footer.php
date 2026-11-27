</main>
<footer class="site-footer">
  <div class="footer-main">
    <div class="footer-brand">
      <a class="wordmark wordmark-light" href="<?= h(app_url('home')) ?>">MAISON <span>SEREIN</span><small>FRAGRANCE, CONSIDERED</small></a>
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
  <div class="footer-bottom"><span>© <?= date('Y') ?> Maison Serein. Made with care.</span><span>Dhaka, Bangladesh</span><a href="admin/index.php">Studio login</a></div>
</footer>
<div class="toast-region" role="status" aria-live="polite" aria-atomic="true" data-toast></div>
<script src="assets/js/store.js?v=8" defer></script>
</body>
</html>
