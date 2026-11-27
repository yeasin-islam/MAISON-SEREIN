<section class="page-hero page-hero-small"><span class="eyebrow">A list for another day</span>
    <h1>Your saved fragrances.</h1>
</section>
<section class="section wishlist-section"><?php if ($products): ?><div class="product-grid"><?php foreach ($products as $item) require __DIR__ . '/product-card.php'; ?></div><?php else: ?><div class="empty-state">
            <h2>No saved fragrances, for now.</h2>
            <p>Save the scents you want to return to.</p><a class="button button-dark" href="<?= h(app_url('shop')) ?>">Browse the collection</a>
        </div><?php endif; ?></section>