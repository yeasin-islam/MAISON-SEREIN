<section class="page-hero discovery-hero">
  <img class="discovery-hero-image" src="assets/images/atelier-hero.png" alt="A fragrance bottle resting in a warm stone setting">
  <div class="discovery-hero-copy">
    <span class="eyebrow">A first introduction</span>
    <h1>Discover your<br>fragrance wardrobe.</h1>
    <p>A considered way to explore the house, one composition at a time.</p>
    <a class="button button-light" href="#discovery-products">Explore discovery sets <span aria-hidden="true">↓</span></a>
  </div>
</section>

<section class="section discovery-products-section" id="discovery-products">
  <div class="section-heading">
    <div><span class="eyebrow">The collection</span>
      <h2>Discovery sets</h2>
    </div>
    <span class="discovery-count"><?= count($collectionProducts) ?> available</span>
  </div>
  <?php if ($collectionProducts): ?>
    <div class="product-grid"><?php foreach ($collectionProducts as $item) require __DIR__ . '/product-card.php'; ?></div>
  <?php else: ?>
    <div class="discovery-empty">
      <span class="eyebrow">A note from the house</span>
      <h3>No discovery sets are currently listed.</h3>
      <p>We won’t invent a set or price. The collection will appear here when a discovery set is published in the store catalogue.</p>
      <a class="button button-outline" href="<?= h(app_url('shop')) ?>">Explore available fragrances <span aria-hidden="true">→</span></a>
    </div>
  <?php endif; ?>
</section>

<section class="section discovery-benefits-section">
  <div class="section-heading">
    <div><span class="eyebrow">A thoughtful way to explore</span>
      <h2>Why begin with discovery?</h2>
    </div>
  </div>
  <div class="discovery-benefits-grid">
    <article><span>01</span>
      <h3>Explore at your pace</h3>
      <p>Take time to read the notes and discover which fragrance feels most like you.</p>
    </article>
    <article><span>02</span>
      <h3>Find a personal favourite</h3>
      <p>Compare the compositions currently listed in the house collection.</p>
    </article>
    <article><span>03</span>
      <h3>A considered gift idea</h3>
      <p>When a set is available, a selection can make choosing a fragrance feel more personal.</p>
    </article>
  </div>
</section>

<section class="discovery-cta">
  <span class="eyebrow">Begin with what is here</span>
  <h2>Find the fragrance that stays with you.</h2>
  <a class="button button-light" href="<?= h(app_url('shop')) ?>">Browse all fragrances <span aria-hidden="true">→</span></a>
</section>