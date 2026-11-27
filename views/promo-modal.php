<?php
$activePopupBanners = $popupBanners ?? [];
$hasConfiguredPopups = (bool) $activePopupBanners;
if ($hasConfiguredPopups) {
  $popupItems = array_map(static function (array $banner): array {
    $banner['cta_url'] = safe_link_url($banner['cta_url'] ?? '', app_url('shop'));
    return $banner;
  }, $activePopupBanners);
  $popupId = 'house-promotions';
  $popupFrequency = 'session';
  $popupEyebrow = 'A little something from the house';
  $popupImageAlt = '';
} else {
  $popupItems = [[
    'title' => 'Welcome to Maison Serein',
    'subtitle' => 'Discover thoughtful fragrances and find a scent that feels like your own.',
    'image_path' => 'assets/images/atelier-hero.png',
    'cta_label' => 'Explore the collection',
    'cta_url' => app_url('shop'),
  ]];
  $popupId = 'maison-serein-welcome';
  $popupFrequency = 'once';
  $popupEyebrow = 'A little something for you';
  $popupImageAlt = 'A fragrance bottle in a softly lit atelier.';
}
$firstPopup = $popupItems[0];
?>
<script type="application/json" id="promo-banner-data">
  <?= json_encode($popupItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>
<div class="promo-modal" role="dialog" aria-modal="true" aria-labelledby="promo-title" aria-describedby="promo-description" data-promo-modal data-popup-id="<?= h($popupId) ?>" data-frequency="<?= h($popupFrequency) ?>" hidden>
  <button class="promo-close" type="button" data-promo-close aria-label="Close welcome message">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
      <path d="m18 6-12 12M6 6l12 12" />
    </svg>
  </button>
  <img data-promo-image src="<?= h($firstPopup['image_path'] ?: 'assets/images/amber-veil.png') ?>" alt="<?= h($popupImageAlt) ?>" loading="lazy">
  <div class="promo-modal-copy">
    <span class="eyebrow"><?= h($popupEyebrow) ?></span>
    <h2 id="promo-title" data-promo-title><?= h($firstPopup['title']) ?></h2>
    <p id="promo-description" data-promo-subtitle><?= h($firstPopup['subtitle'] ?? '') ?></p>
    <a class="button button-dark" data-promo-link href="<?= h(safe_link_url($firstPopup['cta_url'] ?? '', app_url('shop'))) ?>"><?= h($firstPopup['cta_label'] ?: 'Explore the collection') ?></a>
    <?php if (count($popupItems) > 1): ?>
      <div class="promo-carousel-controls">
        <button type="button" data-promo-prev aria-label="Previous promotion">Previous</button>
        <span data-promo-position aria-live="polite">1 / <?= count($popupItems) ?></span>
        <button type="button" data-promo-next aria-label="Next promotion">Next</button>
      </div>
    <?php endif; ?>
  </div>
</div>
<div class="promo-backdrop" data-promo-backdrop hidden></div>