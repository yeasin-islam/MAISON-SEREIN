<?php if (!empty($collectionProducts)): ?>
  <section class="section product-section collection-section">
    <div class="section-heading">
      <div><span class="eyebrow"><?= h($collectionEyebrow ?? 'The house collection') ?></span>
        <h2><?= h($collectionHeading) ?></h2>
      </div><a class="text-link" href="<?= h(app_url('shop', $collectionUrl ?? [])) ?>">Explore all <span aria-hidden="true">→</span></a>
    </div>
    <div class="product-grid"><?php foreach ($collectionProducts as $item) require __DIR__ . '/product-card.php'; ?></div>
  </section>
<?php endif; ?>