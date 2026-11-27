<?php
$filterKeys = ['q', 'min_price', 'max_price', 'gender', 'category', 'brand', 'availability', 'sort', 'featured', 'best_seller', 'new_arrival'];
$paginationParams = [];
$activeFilterCount = 0;
foreach ($filterKeys as $key) {
  $value = $_GET[$key] ?? '';
  if (!is_string($value) || $value === '') continue;
  $paginationParams[$key] = $value;
  if ($key !== 'sort' || $value !== 'featured') $activeFilterCount++;
}
$paginationUrl = static fn(int $pageNumber): string => app_url('shop', $paginationParams + ['p' => $pageNumber]);
$rangeStart = $totalProducts > 0 ? (($currentPage - 1) * $productsPerPage) + 1 : 0;
$rangeEnd = min($currentPage * $productsPerPage, $totalProducts);
$paginationStart = max(1, $currentPage - 2);
$paginationEnd = min($totalPages, $currentPage + 2);
?>
<section class="page-hero page-hero-shop">
  <span class="eyebrow">Find your familiar</span>
  <h1>The fragrance wardrobe.</h1>
  <p>Compositions for the moments you want to keep close.</p>
</section>
<section class="section catalog-section">
  <div class="catalog-top">
    <div class="catalog-result-summary">
      <p>Showing <?= number_format($rangeStart) ?>–<?= number_format($rangeEnd) ?> of <?= number_format($totalProducts) ?> fragrance<?= $totalProducts === 1 ? '' : 's' ?></p>
      <?php if ($activeFilterCount > 0): ?><span><?= $activeFilterCount ?> active filter<?= $activeFilterCount === 1 ? '' : 's' ?></span><?php endif; ?>
    </div>
    <div class="catalog-toolbar-controls">
      <div class="catalog-sort-control">
        <label for="catalog-sort">Sort by</label>
        <select id="catalog-sort" name="sort" form="catalog-filter-form" data-shop-sort>
          <option value="featured" <?= ($_GET['sort'] ?? 'featured') === 'featured' ? 'selected' : '' ?>>Recommended</option>
          <option value="popular" <?= ($_GET['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most loved</option>
          <option value="new" <?= ($_GET['sort'] ?? '') === 'new' ? 'selected' : '' ?>>Newest</option>
          <option value="price-asc" <?= ($_GET['sort'] ?? '') === 'price-asc' ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price-desc" <?= ($_GET['sort'] ?? '') === 'price-desc' ? 'selected' : '' ?>>Price: high to low</option>
        </select>
      </div>
      <button class="catalog-filter-trigger" type="button" data-filter-open aria-controls="catalog-filter-panel" aria-expanded="false">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 7h9m4 0h3M4 17h3m4 0h9" />
          <circle cx="15" cy="7" r="2" />
          <circle cx="9" cy="17" r="2" />
        </svg>
        <span>Filters</span>
        <span class="catalog-filter-count" data-filter-count <?= $activeFilterCount > 0 ? '' : 'hidden' ?>><?= $activeFilterCount ?></span>
      </button>
    </div>
  </div>
  <div class="catalog-layout">
    <div class="catalog-filter-shell" data-filter-shell>
      <div class="catalog-filter-backdrop" data-filter-backdrop hidden aria-hidden="true"></div>
      <aside class="catalog-filters" id="catalog-filter-panel" data-filter-panel aria-label="Product filters">
        <div class="catalog-filter-heading">
          <div><span class="eyebrow">Refine your search</span>
            <h2>Filters</h2>
          </div>
          <button class="catalog-filter-close" type="button" data-filter-close aria-label="Close filters">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
              <path d="m18 6-12 12M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form action="index.php" method="get" id="catalog-filter-form" data-price-filter class="catalog-filter-form">
          <input type="hidden" name="page" value="shop">
          <?php foreach (['featured', 'best_seller', 'new_arrival'] as $preservedFilter): ?>
            <?php if (($_GET[$preservedFilter] ?? '') === '1'): ?><input type="hidden" name="<?= h($preservedFilter) ?>" value="1"><?php endif; ?>
          <?php endforeach; ?>
          <div class="catalog-filter-fields">
            <label for="catalog-search">Search fragrances</label>
            <div class="filter-search">
              <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                <circle cx="10.8" cy="10.8" r="6.8" />
                <path d="m16 16 4.5 4.5" />
              </svg>
              <input id="catalog-search" type="search" name="q" maxlength="100" placeholder="Search fragrances, names or notes..." value="<?= h(is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>">
            </div>
            <fieldset class="price-range-filter">
              <legend>Price range</legend>
              <div class="price-range-inputs">
                <label for="min-price">Minimum price <span class="price-input-wrap"><span aria-hidden="true">৳</span><input id="min-price" type="number" name="min_price" min="0" max="10000000" step="0.01" inputmode="decimal" placeholder="e.g. 1,500" aria-describedby="price-range-hint price-range-error" value="<?= h(is_string($_GET['min_price'] ?? null) ? $_GET['min_price'] : '') ?>"></span></label>
                <label for="max-price">Maximum price <span class="price-input-wrap"><span aria-hidden="true">৳</span><input id="max-price" type="number" name="max_price" min="0" max="10000000" step="0.01" inputmode="decimal" placeholder="No limit" aria-describedby="price-range-hint price-range-error" value="<?= h(is_string($_GET['max_price'] ?? null) ? $_GET['max_price'] : '') ?>"></span></label>
              </div>
              <p id="price-range-hint" class="price-range-hint">Prices in Bangladeshi Taka.</p>
              <p id="price-range-error" class="price-range-error" role="alert" hidden></p>
            </fieldset>
            <fieldset class="catalog-filter-group">
              <legend>Wear it</legend>
              <?php foreach (['women' => 'For her', 'men' => 'For him', 'unisex' => 'Unisex'] as $key => $label): ?>
                <label class="filter-option"><input type="radio" name="gender" value="<?= h($key) ?>" <?= ($_GET['gender'] ?? '') === $key ? 'checked' : '' ?>><?= h($label) ?></label>
              <?php endforeach; ?>
              <label class="filter-option"><input type="radio" name="gender" value="" <?= empty($_GET['gender']) ? 'checked' : '' ?>>All fragrance</label>
            </fieldset>
            <label for="category-filter">Collection</label>
            <select id="category-filter" name="category">
              <option value="">All collections</option>
              <?php foreach ($categories as $category): ?><option value="<?= h($category['slug']) ?>" <?= ($_GET['category'] ?? '') === $category['slug'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?>
            </select>
            <label for="brand-filter">House</label>
            <select id="brand-filter" name="brand">
              <option value="">All houses</option>
              <?php foreach ($brands as $brand): ?><option value="<?= h($brand['slug']) ?>" <?= ($_GET['brand'] ?? '') === $brand['slug'] ? 'selected' : '' ?>><?= h($brand['name']) ?></option><?php endforeach; ?>
            </select>
            <label class="filter-option filter-stock"><input type="checkbox" name="availability" value="in-stock" <?= ($_GET['availability'] ?? '') === 'in-stock' ? 'checked' : '' ?>>Only in stock</label>
          </div>
          <div class="catalog-filter-actions">
            <a class="button button-outline" href="<?= h(app_url('shop')) ?>">Clear</a>
            <button class="button button-dark" type="submit">Apply</button>
          </div>
        </form>
      </aside>
    </div>
    <div class="catalog-results">
      <?php if ($filterError !== ''): ?><p class="catalog-filter-error" role="alert"><?= h($filterError) ?></p><?php endif; ?>
      <?php if ($products): ?>
        <div class="product-grid"><?php foreach ($products as $item) require __DIR__ . '/product-card.php'; ?></div>
        <?php if ($totalPages > 1): ?>
          <nav class="catalog-pagination" aria-label="Product pages">
            <?php if ($currentPage > 1): ?><a class="pagination-previous" href="<?= h($paginationUrl($currentPage - 1)) ?>" rel="prev">Previous</a><?php else: ?><span class="pagination-previous is-disabled" aria-disabled="true">Previous</span><?php endif; ?>
            <div class="pagination-pages">
              <?php if ($paginationStart > 1): ?>
                <a href="<?= h($paginationUrl(1)) ?>" aria-label="Page 1">1</a>
                <?php if ($paginationStart > 2): ?><span class="pagination-ellipsis" aria-hidden="true">…</span><?php endif; ?>
              <?php endif; ?>
              <?php for ($pageNumber = $paginationStart; $pageNumber <= $paginationEnd; $pageNumber++): ?>
                <?php if ($pageNumber === $currentPage): ?><span aria-current="page"><?= $pageNumber ?></span><?php else: ?><a href="<?= h($paginationUrl($pageNumber)) ?>" aria-label="Page <?= $pageNumber ?>"><?= $pageNumber ?></a><?php endif; ?>
              <?php endfor; ?>
              <?php if ($paginationEnd < $totalPages): ?>
                <?php if ($paginationEnd < $totalPages - 1): ?><span class="pagination-ellipsis" aria-hidden="true">…</span><?php endif; ?>
                <a href="<?= h($paginationUrl($totalPages)) ?>" aria-label="Page <?= $totalPages ?>"><?= $totalPages ?></a>
              <?php endif; ?>
            </div>
            <?php if ($currentPage < $totalPages): ?><a class="pagination-next" href="<?= h($paginationUrl($currentPage + 1)) ?>" rel="next">Next</a><?php else: ?><span class="pagination-next is-disabled" aria-disabled="true">Next</span><?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php elseif ($filterError === ''): ?>
        <div class="empty-state catalog-empty-state">
          <span class="eyebrow">A moment of pause</span>
          <h2>Nothing found just yet.</h2>
          <p>No fragrances match this search and filter combination. Try another note or clear your filters.</p>
          <a class="button button-dark" href="<?= h(app_url('shop')) ?>">Explore all fragrances</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>