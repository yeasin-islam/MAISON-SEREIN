<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">A new composition</span>
      <h2>Add a fragrance</h2>
    </div>
  </div>
  <form class="admin-form-grid" action="../api/index.php" method="post" enctype="multipart/form-data" data-api-form data-redirect-success="?page=products"><?= csrf_field() ?><input type="hidden" name="action" value="admin.product.save"><label>Fragrance name<input name="name" required maxlength="180"></label><label>House<select name="brand_id" required>
        <option value="">Choose house</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>"><?= h($brand['name']) ?></option><?php endforeach; ?>
      </select></label><label>Collection<select name="category_id">
        <option value="">No collection</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>"><?= h($category['name']) ?></option><?php endforeach; ?>
      </select></label><label>For<select name="gender">
        <option value="unisex">Unisex</option>
        <option value="women">For her</option>
        <option value="men">For him</option>
      </select></label><label>Fragrance family<input name="fragrance_family" maxlength="120" placeholder="Amber · Woody"></label><label>Initial size in ml<input name="size_ml" type="number" min="1" max="500" step="0.1" value="50" required></label><label>Price in BDT<input name="price" type="number" min="0" max="10000000" step="0.01" required></label><label>Opening notes<input name="top_notes" maxlength="300"></label><label>Heart notes<input name="heart_notes" maxlength="300"></label><label>Lasting notes<input name="base_notes" maxlength="300"></label><label>Opening stock<input name="stock_quantity" type="number" min="0" max="100000" value="0"></label><label class="admin-span">Short description<input name="short_description" maxlength="300"></label><label class="admin-span">Product story<textarea name="description" rows="3" required maxlength="5000"></textarea></label><label class="admin-span">Product images <span class="field-optional">(up to 5, JPG/PNG/WebP, 5 MB each)</span><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></label>
    <fieldset class="admin-span">
      <legend>Store collections</legend><label class="admin-check"><input type="checkbox" name="is_featured" value="1"> Featured</label><label class="admin-check"><input type="checkbox" name="is_best_seller" value="1"> Best seller</label><label class="admin-check"><input type="checkbox" name="is_new_arrival" value="1"> New arrival</label>
    </fieldset>
    <div class="admin-span"><button class="button button-dark" type="submit">Create fragrance</button>
      <p class="form-message" aria-live="polite"></p>
    </div>
  </form>
</section>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Your current collection</span>
      <h2>Fragrances, sizes, and visibility</h2>
    </div><span><?= count($products ?? []) ?> products</span>
  </div>
  <div class="admin-content-list">
    <?php foreach (($products ?? []) as $product): $firstVariant = $product['variants'][0] ?? ['size_ml' => 50, 'price' => 0, 'stock_quantity' => 0]; ?>
      <article class="admin-content-card">
        <div class="admin-panel-heading">
          <div><strong><?= h($product['name']) ?></strong><small class="table-secondary"><?= h($product['brand_name']) ?> · <?= h($product['category_name'] ?? 'No collection') ?></small></div>
          <form action="../api/index.php" method="post" data-api-form data-success="Visibility updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.product.visibility"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="is_visible" value="<?= $product['is_visible'] ? 0 : 1 ?>"><button class="table-action" type="submit"><?= $product['is_visible'] ? 'Hide from shop' : 'Show in shop' ?></button></form>
          <form action="../api/index.php" method="post" data-api-form data-success="Listing status updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.product.active"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="is_active" value="<?= $product['is_active'] ? 0 : 1 ?>"><button class="table-action table-action-muted" type="submit"><?= $product['is_active'] ? 'Deactivate' : 'Activate' ?></button></form>
        </div>
        <?php if (!empty($productImages[(int) $product['id']])): ?><div class="admin-product-images"><?php foreach ($productImages[(int) $product['id']] as $image): ?><img src="../<?= h($image['image_path']) ?>" alt="<?= h($image['alt_text']) ?>" loading="lazy"><?php endforeach; ?></div><?php endif; ?>
        <form class="admin-form-grid" action="../api/index.php" method="post" enctype="multipart/form-data" data-api-form data-success="Product details saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.product.save"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="size_ml" value="<?= h($firstVariant['size_ml']) ?>"><input type="hidden" name="price" value="<?= h($firstVariant['price']) ?>"><input type="hidden" name="stock_quantity" value="<?= (int) $firstVariant['stock_quantity'] ?>"><label>Fragrance name<input name="name" value="<?= h($product['name']) ?>" required maxlength="180"></label><label>House<select name="brand_id" required><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id'] ?>" <?= (int) $product['brand_id'] === (int) $brand['id'] ? 'selected' : '' ?>><?= h($brand['name']) ?></option><?php endforeach; ?></select></label><label>Collection<select name="category_id">
              <option value="">No collection</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?>
            </select></label><label>For<select name="gender"><?php foreach (['unisex' => 'Unisex', 'women' => 'For her', 'men' => 'For him'] as $value => $label): ?><option value="<?= h($value) ?>" <?= $product['gender'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label><label>Fragrance family<input name="fragrance_family" value="<?= h($product['fragrance_family'] ?? '') ?>" maxlength="120"></label><label>Opening notes<input name="top_notes" value="<?= h($product['top_notes'] ?? '') ?>" maxlength="300"></label><label>Heart notes<input name="heart_notes" value="<?= h($product['heart_notes'] ?? '') ?>" maxlength="300"></label><label>Lasting notes<input name="base_notes" value="<?= h($product['base_notes'] ?? '') ?>" maxlength="300"></label><label class="admin-span">Short description<input name="short_description" value="<?= h($product['short_description'] ?? '') ?>" maxlength="300"></label><label class="admin-span">Product story<textarea name="description" rows="3" required maxlength="5000"><?= h($product['description']) ?></textarea></label><label class="admin-span">Add product images <span class="field-optional">(up to 5, JPG/PNG/WebP, 5 MB each)</span><input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></label>
          <fieldset class="admin-span">
            <legend>Store collections</legend><label class="admin-check"><input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>> Featured</label><label class="admin-check"><input type="checkbox" name="is_best_seller" value="1" <?= $product['is_best_seller'] ? 'checked' : '' ?>> Best seller</label><label class="admin-check"><input type="checkbox" name="is_new_arrival" value="1" <?= $product['is_new_arrival'] ? 'checked' : '' ?>> New arrival</label>
          </fieldset>
          <div class="admin-span"><button class="button button-dark" type="submit">Save fragrance</button>
            <p class="form-message" aria-live="polite"></p>
          </div>
        </form>
        <div class="admin-variant-heading">
          <h3>Sizes and pricing</h3><span>Stock changes are audited in Inventory.</span>
        </div>
        <?php foreach ($product['variants'] as $variant): ?><div class="admin-variant-row">
            <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Size and price updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.variant.save"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="variant_id" value="<?= (int) $variant['id'] ?>"><label><span class="sr-only">Size in ml</span><input name="size_ml" type="number" min="1" max="500" step="0.1" value="<?= h($variant['size_ml']) ?>" required></label><label><span class="sr-only">Price in BDT</span><input name="price" type="number" min="0" max="10000000" step="0.01" value="<?= h($variant['price']) ?>" required></label><span class="variant-stock">On hand: <?= (int) $variant['stock_quantity'] ?></span><button class="table-action" type="submit">Save size</button>
              <p class="form-message" aria-live="polite"></p>
            </form>
            <form action="../api/index.php" method="post" data-api-form data-success="Variant status updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.variant.toggle"><input type="hidden" name="variant_id" value="<?= (int) $variant['id'] ?>"><input type="hidden" name="is_active" value="<?= $variant['is_active'] ? 0 : 1 ?>"><button class="table-action table-action-muted" type="submit"><?= $variant['is_active'] ? 'Disable size' : 'Enable size' ?></button></form>
          </div><?php endforeach; ?>
        <form class="admin-variant-add" action="../api/index.php" method="post" data-api-form data-success="Variant added."><?= csrf_field() ?><input type="hidden" name="action" value="admin.variant.save"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><label>Add size (ml)<input name="size_ml" type="number" min="1" max="500" step="0.1" required></label><label>Price (BDT)<input name="price" type="number" min="0" max="10000000" step="0.01" required></label><label>Initial stock<input name="stock_quantity" type="number" min="0" max="100000" value="0"></label><button class="button button-light" type="submit">Add size</button>
          <p class="form-message" aria-live="polite"></p>
        </form>
      </article><?php endforeach; ?>
    <?php if (empty($products)): ?><div class="admin-empty">Your first fragrance will appear here after you create it.</div><?php endif; ?>
  </div>
</section>