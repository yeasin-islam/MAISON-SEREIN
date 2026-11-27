<?php
$cardPrice = isset($item['min_price']) ? (float) $item['min_price'] : (float) ($item['variants'][0]['price'] ?? 0);
$cardImage = $item['image_path'] ?? $item['images'][0]['image_path'] ?? 'assets/images/amber-veil.png';
$cardId = (int) $item['id'];
$cardSaved = in_array($cardId, $wishlistIds ?? [], true);
?>
<article class="product-card">
  <a class="product-image-link" href="<?= h(app_url('product', ['id' => $cardId])) ?>">
    <div class="product-image"><img src="<?= h($cardImage) ?>" alt="<?= h($item['name']) ?> fragrance bottle" loading="lazy">
      <?php if (!empty($item['is_featured'])): ?><span class="product-badge">House favourite</span><?php elseif (!empty($item['is_new_arrival'])): ?><span class="product-badge">New arrival</span><?php endif; ?>
    </div>
  </a>
  <div class="product-card-content">
    <span class="product-brand"><?= h($item['brand_name'] ?? APP_NAME) ?></span>
    <div class="product-title-row">
      <h3><a href="<?= h(app_url('product', ['id' => $cardId])) ?>"><?= h($item['name']) ?></a></h3>
      <form action="api/index.php" method="post" data-api-form data-success="Added to your wishlist.">
        <?= csrf_field() ?><input type="hidden" name="action" value="wishlist.toggle"><input type="hidden" name="product_id" value="<?= $cardId ?>">
        <button class="wishlist-button<?= $cardSaved ? ' is-saved' : '' ?>" type="submit" data-wishlist-button data-product-id="<?= $cardId ?>" data-product-name="<?= h($item['name']) ?>" aria-label="<?= $cardSaved ? 'Remove ' : 'Add ' ?><?= h($item['name']) ?> <?= $cardSaved ? 'from' : 'to' ?> wishlist" title="<?= $cardSaved ? 'Remove from wishlist' : 'Add to wishlist' ?>" aria-pressed="<?= $cardSaved ? 'true' : 'false' ?>">
          <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z" />
          </svg>
        </button>
      </form>
    </div>
    <p><?= h($item['short_description'] ?? '') ?></p>
    <?php if (!empty($item['average_rating'])): ?><div class="card-rating" aria-label="Rated <?= number_format((float) $item['average_rating'], 1) ?> out of 5"><?= number_format((float) $item['average_rating'], 1) ?> <span aria-hidden="true">★</span> <small>(<?= (int) ($item['review_count'] ?? 0) ?>)</small></div><?php endif; ?>
    <div class="product-card-bottom"><strong><?= money($cardPrice) ?></strong>
      <div class="product-card-actions"><?php if (!empty($item['available_variant_id'])): ?><form action="api/index.php" method="post" data-api-form data-success="Added to your bag."><?= csrf_field() ?><input type="hidden" name="action" value="cart.add"><input type="hidden" name="variant_id" value="<?= (int) $item['available_variant_id'] ?>"><input type="hidden" name="quantity" value="1"><button class="quick-add" type="submit" aria-label="Add <?= h($item['name']) ?> to bag">Add to bag</button></form><?php endif; ?><a href="<?= h(app_url('product', ['id' => $cardId])) ?>" aria-label="View <?= h($item['name']) ?>">Discover <span aria-hidden="true">↗</span></a></div>
    </div>
  </div>
</article>