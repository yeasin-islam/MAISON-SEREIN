<?php
$scentProfiles = [
  'fresh' => 'Fresh',
  'floral' => 'Floral',
  'woody' => 'Woody',
  'oriental' => 'Oriental',
  'citrus' => 'Citrus',
  'sweet' => 'Sweet',
  'spicy' => 'Spicy',
];
$occasions = [
  '' => 'Any occasion',
  'everyday' => 'Everyday',
  'office' => 'Office',
  'evening' => 'Evening',
  'special-occasion' => 'Special occasion',
  'gift' => 'Gift',
];
$legacyMoods = [
  'romantic' => 'Soft & romantic',
  'fresh' => 'Fresh & bright',
  'elegant' => 'Quietly elegant',
  'office' => 'For the everyday',
  'party' => 'After dark',
  'casual' => 'Easy-going',
  'formal' => 'For a special occasion',
  'special-occasion' => 'Something memorable',
];
$selectedProfile = (string) ($_GET['profile'] ?? '');
$selectedOccasion = (string) ($_GET['occasion'] ?? '');
$legacyMood = (string) ($_GET['mood'] ?? '');
$hasFinderResults = isset($_GET['find']) && isset($scentProfiles[$selectedProfile]);
?>
<section class="page-hero scent-hero">
  <img class="scent-hero-image" src="assets/images/atelier-hero.png" alt="A fragrance bottle in a quiet, sunlit setting">
  <div class="scent-hero-copy">
    <span class="eyebrow">A little guidance</span>
    <h1>Find your<br>signature scent.</h1>
    <p>Start with the notes and moments you’re drawn to. We’ll look for matches in the fragrances currently in the catalogue.</p>
    <a class="button button-light" href="#scent-preferences">Start exploring <span aria-hidden="true">↓</span></a>
  </div>
</section>

<section class="section scent-finder-section" id="scent-preferences">
  <div class="section-heading scent-finder-heading">
    <div><span class="eyebrow">Your preferences</span>
      <h2>What feels like you?</h2>
    </div>
    <p>Matches use the fragrance-family and note text already stored for each product, plus any selected occasion tags.</p>
  </div>
  <form class="scent-finder-form" method="get" action="">
    <input type="hidden" name="page" value="mood">
    <input type="hidden" name="find" value="1">
    <fieldset class="scent-choice-group">
      <legend>Choose a scent direction</legend>
      <div class="scent-choice-grid">
        <?php foreach ($scentProfiles as $key => $label): ?>
          <label class="scent-choice-card">
            <input type="radio" name="profile" value="<?= h($key) ?>" required <?= $selectedProfile === $key ? 'checked' : '' ?>>
            <span class="scent-choice-icon" aria-hidden="true"><?= $key === 'floral' ? '✳' : ($key === 'woody' ? '⌁' : ($key === 'citrus' ? '◌' : '✧')) ?></span>
            <span><?= h($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
    <div class="scent-preference-bottom">
      <label class="scent-select-label" for="scent-occasion">When will you wear it?
        <select id="scent-occasion" name="occasion">
          <?php foreach ($occasions as $key => $label): ?>
            <option value="<?= h($key) ?>" <?= $selectedOccasion === $key ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="scent-select-label" for="scent-intensity">Scent intensity
        <select id="scent-intensity" disabled aria-describedby="intensity-unavailable">
          <option>Not available in product data</option>
        </select>
      </label>
    </div>
    <p class="scent-data-note" id="intensity-unavailable">Intensity is not stored for the current products, so it cannot be used to filter recommendations yet.</p>
    <button class="button button-dark" type="submit">Find my fragrance <span aria-hidden="true">→</span></button>
  </form>

  <?php if ($hasFinderResults || ($legacyMood !== '' && isset($legacyMoods[$legacyMood]))): ?>
    <div class="scent-results" aria-live="polite">
      <div class="section-heading">
        <div><span class="eyebrow">From the current catalogue</span>
          <h2><?= $hasFinderResults ? h($scentProfiles[$selectedProfile]) : h($legacyMoods[$legacyMood]) ?> fragrances</h2>
        </div>
        <?php if ($hasFinderResults && $selectedOccasion === 'gift'): ?><p>No gift-specific product tag is stored, so these results use your scent direction only.</p><?php endif; ?>
      </div>
      <?php if ($moodProducts): ?>
        <div class="product-grid"><?php foreach ($moodProducts as $item) require __DIR__ . '/product-card.php'; ?></div>
      <?php else: ?>
        <div class="empty-state">
          <h3>No exact match in the current collection.</h3>
          <p>Try another scent direction or remove the occasion preference. Results are based only on the notes and tags stored for active products.</p><a class="text-link" href="<?= h(app_url('shop')) ?>">Explore all fragrances →</a>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>