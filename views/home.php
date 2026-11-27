<?php
$hero = $heroBanners[0] ?? ['title' => 'A quieter kind of luxury.', 'subtitle' => 'Fragrance for the moments that are yours.', 'image_path' => 'assets/images/atelier-hero.png', 'cta_label' => 'Discover the collection', 'cta_url' => '?page=shop'];
?>
<section class="hero" id="homepage-hero"><img class="hero-image" src="<?= h($hero['image_path']) ?>" alt="" fetchpriority="high">
  <div class="hero-copy"><span class="eyebrow">A fragrance house, in its own time</span>
    <h1><?= nl2br(h($hero['title'])) ?></h1>
    <p><?= h($hero['subtitle']) ?></p><a class="button button-dark" href="<?= h(safe_link_url($hero['cta_url'] ?? '', app_url('shop'))) ?>"><?= h($hero['cta_label'] ?: 'Explore the collection') ?><span aria-hidden="true">↗</span></a>
  </div><span class="hero-caption">No. 01 — The quiet collection</span>
</section>
<section class="intro-strip"><span class="intro-mark" aria-hidden="true">✳</span>
  <p>Made to linger. Never to announce.</p><span class="intro-rule"></span><span class="intro-note">Small-batch fragrance<br>with a sense of place.</span>
</section>
<?php if ($promoBanners): ?><section class="promo-grid" aria-label="House announcements"><?php foreach ($promoBanners as $promo): ?><article class="promo-card"><img class="promo-card-image" src="<?= h($promo['image_path'] ?? 'assets/images/rose-atelier.png') ?>" alt="" loading="lazy">
        <div><span class="eyebrow">A note from the house</span>
          <h2><?= h($promo['title']) ?></h2>
          <p><?= h($promo['subtitle'] ?? '') ?></p><?php if (!empty($promo['cta_label'])): ?><a class="text-link" href="<?= h(safe_link_url($promo['cta_url'] ?? '', app_url('shop'))) ?>"><?= h($promo['cta_label']) ?> <span aria-hidden="true">→</span></a><?php endif; ?>
        </div>
      </article><?php endforeach; ?></section><?php endif; ?>
<?php
$homeCollectionMap = [
  'featured' => [$featuredProducts ?? [], ['featured' => 1]],
  'best_seller' => [$products ?? [], ['best_seller' => 1]],
  'best' => [$products ?? [], ['best_seller' => 1]],
  'new_arrival' => [$newArrivals ?? [], ['new_arrival' => 1]],
  'new' => [$newArrivals ?? [], ['new_arrival' => 1]],
  'men' => [$menProducts ?? [], ['gender' => 'men']],
  'women' => [$womenProducts ?? [], ['gender' => 'women']],
  'unisex' => [$unisexProducts ?? [], ['gender' => 'unisex']],
];
foreach (($homepageSections ?? []) as $section):
  $sectionKey = (string) $section['collection_key'];
  if ($sectionKey === 'limited'):
    if (empty($limitedDrops)) continue;
?>
    <section class="section drop-section">
      <div class="section-heading">
        <div><span class="eyebrow"><?= h($section['subtitle'] ?: 'Small runs, short windows') ?></span>
          <h2><?= h($section['heading']) ?></h2>
        </div>
      </div>
      <div class="drop-grid"><?php foreach ($limitedDrops as $drop): $dropSaved = in_array((int) $drop['product_id'], $wishlistIds ?? [], true); ?><article class="drop-card"><a class="drop-image" href="<?= h(app_url('product', ['id' => $drop['product_id']])) ?>"><img src="<?= h($drop['image_path'] ?? 'assets/images/amber-veil.png') ?>" alt="<?= h($drop['name']) ?> limited fragrance release" loading="lazy"><span class="product-badge">Limited release</span></a>
            <div class="drop-copy"><span class="eyebrow"><?= h($drop['brand_name']) ?> · Limited release</span>
              <h3><a href="<?= h(app_url('product', ['id' => $drop['product_id']])) ?>"><?= h($drop['name']) ?></a></h3>
              <p><?= h($drop['short_description'] ?? '') ?></p><strong><?= money($drop['min_price'] ?? 0) ?></strong><?php if (!empty($drop['end_at'])): ?><div class="drop-countdown" data-countdown="<?= h(date(DATE_ATOM, strtotime($drop['end_at']))) ?>" aria-live="polite"><span>Available for a little while longer</span></div><?php endif; ?><div class="drop-actions">
                <form action="api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="cart.add"><input type="hidden" name="variant_id" value="<?= (int) $drop['available_variant_id'] ?>"><input type="hidden" name="quantity" value="1"><button class="button button-dark" type="submit" <?= empty($drop['available_variant_id']) ? 'disabled' : '' ?>>Add to bag</button></form>
                <form action="api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="wishlist.toggle"><input type="hidden" name="product_id" value="<?= (int) $drop['product_id'] ?>"><button class="wishlist-button<?= $dropSaved ? ' is-saved' : '' ?>" type="submit" data-wishlist-button data-product-id="<?= (int) $drop['product_id'] ?>" data-product-name="<?= h($drop['name']) ?>" aria-label="<?= $dropSaved ? 'Remove' : 'Add' ?> <?= h($drop['name']) ?> <?= $dropSaved ? 'from' : 'to' ?> wishlist" aria-pressed="<?= $dropSaved ? 'true' : 'false' ?>"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z" />
                    </svg></button></form>
              </div>
            </div>
          </article><?php endforeach; ?></div>
    </section>
  <?php elseif ($sectionKey === 'reviews'): if (empty($featuredReviews)) continue; ?>
    <section class="section review-section">
      <div class="section-heading">
        <div><span class="eyebrow"><?= h($section['subtitle'] ?: 'Worn and remembered') ?></span>
          <h2><?= h($section['heading']) ?></h2>
        </div>
      </div>
      <div class="review-grid"><?php foreach ($featuredReviews as $review): ?><article class="review-card">
            <div class="stars" aria-label="<?= (int) $review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int) $review['rating']) ?><?= str_repeat('☆', 5 - (int) $review['rating']) ?></div>
            <blockquote>“<?= h($review['review_body']) ?>”</blockquote><span><?= h($review['customer_name']) ?> · <?= h($review['product_name']) ?></span>
          </article><?php endforeach; ?></div>
    </section>
<?php else:
    if (!isset($homeCollectionMap[$sectionKey]) || empty($homeCollectionMap[$sectionKey][0])) continue;
    [$collectionProducts, $collectionUrl] = $homeCollectionMap[$sectionKey];
    $collectionHeading = $section['heading'];
    $collectionEyebrow = $section['subtitle'] ?: 'The house collection';
    require __DIR__ . '/product-collection.php';
  endif;
endforeach;
?>
<section class="story-band">
  <div class="story-image" role="img" aria-label="Warm amber perfume bottle in a sunlit atelier"></div>
  <div class="story-copy"><span class="eyebrow">The Last Note way</span>
    <h2>Less, but with<br>more feeling.</h2>
    <p>We make small, thoughtful compositions for the everyday rituals that deserve to be noticed. Made slowly. Worn close.</p><a class="text-link" href="<?= h(app_url('about')) ?>">A little about us <span aria-hidden="true">→</span></a>
  </div>
</section>

<section class="mood-callout">
  <div><span class="eyebrow">Not sure where to begin?</span>
    <h2>Start with how you want to feel.</h2>
    <p>A few quiet questions can help you find a fragrance that feels like yours.</p><a class="button button-outline" href="<?= h(app_url('mood')) ?>">Find your feeling <span aria-hidden="true">→</span></a>
  </div>
</section>
<section class="quote-band"><span class="eyebrow">A note from the house</span>
  <blockquote>“The most beautiful things stay with you quietly.”</blockquote><span class="quote-source">THE LAST NOTE BD philosophy</span>
</section>

<?php require __DIR__ . '/promo-modal.php'; ?>
<?php
$connectLinks = [];
foreach ($socialLinks as $social) {
    $name = (string) ($social['platform'] ?? '');
    $url = safe_link_url((string) ($social['url'] ?? ''), '');
    if (trim($name) !== '' && $url !== '') {
        $connectLinks[] = ['name' => $name, 'url' => $url];
    }
}
?>
<?php if ($connectLinks): ?><div class="connect-widget" data-connect-widget>
  <button class="connect-toggle" type="button" aria-label="Open contact links" aria-expanded="false" aria-controls="connect-orbit" data-connect-toggle>
    <svg class="connect-open-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H6l-3 2 1-4A7.5 7.5 0 1 1 20 11.5Z" />
      <path d="M8 11.5h.01M12 11.5h.01M16 11.5h.01" stroke-width="2.5" />
    </svg>
    <svg class="connect-close-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
      <path d="m6 6 12 12M18 6 6 18" />
    </svg>
    <span class="sr-only" data-connect-label>Open contact links</span>
  </button>
  <nav class="connect-orbit" id="connect-orbit" aria-label="Contact links" aria-hidden="true" inert data-connect-orbit>
    <?php foreach (array_reverse($connectLinks) as $contact): ?>
      <a class="connect-contact-link" href="<?= h($contact['url']) ?>"<?= str_starts_with(strtolower($contact['url']), 'https://') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= h($contact['name']) ?></a>
    <?php endforeach; ?>
  </nav>
</div><?php endif; ?>