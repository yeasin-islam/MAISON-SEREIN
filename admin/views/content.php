<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Create a moment</span>
      <h2>Add a homepage banner</h2>
    </div>
  </div>
  <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=content"><?= csrf_field() ?><input type="hidden" name="action" value="admin.banner.save">
    <label>Headline<input name="title" required maxlength="180"></label><label>Placement<select name="placement">
        <option value="hero">Hero</option>
        <option value="promo">Promotion</option>
        <option value="popup">First-visit popup</option>
      </select></label><label>Supporting line<input name="subtitle" maxlength="300"></label><label>Image URL<input name="image_path" type="url" maxlength="500" placeholder="/assets/images/... or https://..."></label><label>Button label<input name="cta_label" maxlength="80"></label><label>Button destination<input name="cta_url" maxlength="500" placeholder="?page=shop"></label><label>Starts at<input name="starts_at" type="datetime-local"></label><label>Ends at<input name="ends_at" type="datetime-local"></label><label>Sort order<input name="sort_order" type="number" value="0" min="0" max="999"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" checked> Publish immediately</label>
    <div class="admin-span"><button class="button button-dark" type="submit">Save banner</button>
      <p class="form-message" aria-live="polite"></p>
    </div>
  </form>
</section>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Published moments</span>
      <h2>Banners and popups</h2>
    </div><span><?= count($banners ?? []) ?> items</span>
  </div>
  <div class="admin-content-list">
    <?php foreach (($banners ?? []) as $banner): ?><form class="admin-form-grid admin-content-card" action="../api/index.php" method="post" data-api-form data-success="Banner saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.banner.save"><input type="hidden" name="banner_id" value="<?= (int) $banner['id'] ?>"><label>Headline<input name="title" value="<?= h($banner['title']) ?>" required maxlength="180"></label><label>Placement<select name="placement"><?php foreach (['hero' => 'Hero', 'promo' => 'Promotion', 'popup' => 'Popup'] as $value => $label): ?><option value="<?= h($value) ?>" <?= $banner['placement'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label><label>Supporting line<input name="subtitle" value="<?= h($banner['subtitle'] ?? '') ?>" maxlength="300"></label><label>Image URL<input name="image_path" value="<?= h($banner['image_path'] ?? '') ?>" maxlength="500"></label><label>Button label<input name="cta_label" value="<?= h($banner['cta_label'] ?? '') ?>" maxlength="80"></label><label>Button destination<input name="cta_url" value="<?= h($banner['cta_url'] ?? '') ?>" maxlength="500"></label><label>Starts at<input name="starts_at" type="datetime-local" value="<?= !empty($banner['starts_at']) ? h(date('Y-m-d\\TH:i', strtotime($banner['starts_at']))) : '' ?>"></label><label>Ends at<input name="ends_at" type="datetime-local" value="<?= !empty($banner['ends_at']) ? h(date('Y-m-d\\TH:i', strtotime($banner['ends_at']))) : '' ?>"></label><label>Sort order<input name="sort_order" type="number" value="<?= (int) $banner['sort_order'] ?>" min="0" max="999"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" <?= $banner['is_active'] ? 'checked' : '' ?>> Active</label>
        <div class="admin-span admin-form-actions"><button class="button button-dark" type="submit">Save changes</button><span class="table-secondary">#<?= (int) $banner['id'] ?> · <?= h(ucfirst($banner['placement'])) ?></span>
          <p class="form-message" aria-live="polite"></p>
        </div>
      </form><?php endforeach; ?>
    <?php if (empty($banners)): ?><div class="admin-empty">No banners yet. Add one above to manage homepage promotions.</div><?php endif; ?>
  </div>
</section>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Find your people</span>
      <h2>Social links</h2>
    </div>
  </div>
  <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=content"><?= csrf_field() ?><input type="hidden" name="action" value="admin.social.save"><label>Platform<input name="platform" required maxlength="40" placeholder="Instagram"></label><label>Profile URL<input name="url" type="url" required maxlength="500" placeholder="https://instagram.com/..."></label><label>Sort order<input name="sort_order" type="number" min="0" max="999" value="0"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" checked> Active</label>
    <div class="admin-span"><button class="button button-dark" type="submit">Add social link</button>
      <p class="form-message" aria-live="polite"></p>
    </div>
  </form>
  <?php foreach (($socials ?? []) as $social): ?><form class="admin-inline-form admin-social-row" action="../api/index.php" method="post" data-api-form data-success="Social link saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.social.save"><input type="hidden" name="social_id" value="<?= (int) $social['id'] ?>"><label><span class="sr-only">Platform</span><input name="platform" value="<?= h($social['platform']) ?>" required maxlength="40"></label><label><span class="sr-only">Profile URL</span><input name="url" type="url" value="<?= h($social['url']) ?>" required maxlength="500"></label><label><span class="sr-only">Sort order</span><input name="sort_order" type="number" value="<?= (int) $social['sort_order'] ?>" min="0" max="999"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" <?= $social['is_active'] ? 'checked' : '' ?>> Active</label><button class="table-action" type="submit">Save</button>
      <p class="form-message" aria-live="polite"></p>
    </form><?php endforeach; ?>
</section>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Home page rhythm</span>
      <h2>Add a collection section</h2>
    </div>
  </div>
  <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=content"><?= csrf_field() ?><input type="hidden" name="action" value="admin.section.save"><label>Section key<input name="section_key" required pattern="[a-z0-9-]{2,60}" maxlength="60" placeholder="summer-edit"></label><label>Heading<input name="heading" required maxlength="160"></label><label>Supporting line<input name="subtitle" maxlength="240"></label><label>Collection<select name="collection_key"><?php foreach (['featured' => 'Featured', 'best_seller' => 'Best sellers', 'new_arrival' => 'New arrivals', 'men' => 'For him', 'women' => 'For her', 'unisex' => 'Unisex', 'limited' => 'Limited drops', 'reviews' => 'Customer notes'] as $value => $label): ?><option value="<?= h($value) ?>"><?= h($label) ?></option><?php endforeach; ?></select></label><label>Order<input name="sort_order" type="number" min="0" max="999" value="0"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" checked> Active</label>
    <div class="admin-span"><button class="button button-dark" type="submit">Add section</button>
      <p class="form-message" aria-live="polite"></p>
    </div>
  </form>
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Live sections</span>
      <h2>Edit homepage order and content</h2>
    </div>
  </div>
  <div class="admin-content-list"><?php foreach (($homepageSections ?? []) as $section): ?><form class="admin-form-grid admin-content-card" action="../api/index.php" method="post" data-api-form data-success="Homepage section saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.section.save"><input type="hidden" name="section_id" value="<?= (int) $section['id'] ?>"><label>Section key<input name="section_key" value="<?= h($section['section_key']) ?>" required pattern="[a-z0-9-]{2,60}" maxlength="60"></label><label>Heading<input name="heading" value="<?= h($section['heading']) ?>" required maxlength="160"></label><label>Supporting line<input name="subtitle" value="<?= h($section['subtitle'] ?? '') ?>" maxlength="240"></label><label>Collection<select name="collection_key"><?php foreach (['featured' => 'Featured', 'best_seller' => 'Best sellers', 'new_arrival' => 'New arrivals', 'men' => 'For him', 'women' => 'For her', 'unisex' => 'Unisex', 'limited' => 'Limited drops', 'reviews' => 'Customer notes'] as $value => $label): ?><option value="<?= h($value) ?>" <?= $section['collection_key'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label><label>Order<input name="sort_order" type="number" min="0" max="999" value="<?= (int) $section['sort_order'] ?>"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" <?= $section['is_active'] ? 'checked' : '' ?>> Active</label>
        <div class="admin-span admin-form-actions"><button class="button button-dark" type="submit">Save section</button><span class="table-secondary">#<?= (int) $section['id'] ?></span>
          <p class="form-message" aria-live="polite"></p>
        </div>
      </form><?php endforeach; ?><?php if (empty($homepageSections)): ?><div class="admin-empty">No home sections are configured.</div><?php endif; ?></div>
</section>