<div class="admin-settings-grid">
    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">The house, online</span>
                <h2>Store details</h2>
            </div>
        </div>
        <form class="admin-form-stack" action="../api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="admin.setting.save"><label>Customer care email<input name="store_email" type="email" maxlength="190" value="<?= h($storeSettings['store_email'] ?? '') ?>"></label><label>Customer care phone<input name="store_phone" maxlength="40" value="<?= h($storeSettings['store_phone'] ?? '') ?>"></label><label>WhatsApp number<input name="whatsapp_number" maxlength="40" value="<?= h($storeSettings['whatsapp_number'] ?? '') ?>"></label><label>bKash number<input name="bkash_number" maxlength="40" value="<?= h($storeSettings['bkash_number'] ?? '') ?>"></label><label>bKash payment instructions<input name="bkash_instructions" maxlength="255" value="<?= h($storeSettings['bkash_instructions'] ?? '') ?>"></label><label>Nagad number<input name="nagad_number" maxlength="40" value="<?= h($storeSettings['nagad_number'] ?? '') ?>"></label><label>Nagad payment instructions<input name="nagad_instructions" maxlength="255" value="<?= h($storeSettings['nagad_instructions'] ?? '') ?>"></label><label>Cancellation policy<textarea name="cancellation_policy" maxlength="500" rows="3"><?= h($storeSettings['cancellation_policy'] ?? '') ?></textarea></label><label>Customer cancellation window (hours)<input name="customer_cancellation_window_hours" type="number" min="0" max="168" value="<?= h($storeSettings['customer_cancellation_window_hours'] ?? '24') ?>"></label><input type="hidden" name="gift_mode_enabled" value="0"><label class="admin-check"><input name="gift_mode_enabled" type="checkbox" value="1" <?= ($storeSettings['gift_mode_enabled'] ?? '1') === '1' ? 'checked' : '' ?>> Enable gift mode</label><input type="hidden" name="stock_restore_on_cancellation" value="0"><label class="admin-check"><input name="stock_restore_on_cancellation" type="checkbox" value="1" <?= ($storeSettings['stock_restore_on_cancellation'] ?? '1') === '1' ? 'checked' : '' ?>> Restore stock after eligible cancellations</label><?php $acceptedPayments = json_decode((string) ($storeSettings['accepted_payment_methods'] ?? '["bkash","nagad","cod"]'), true) ?: ['bkash', 'nagad', 'cod']; ?><fieldset class="admin-span">
                <legend>Accepted payment methods</legend><input type="hidden" name="accepted_payment_methods[]" value=""><label class="admin-check"><input type="checkbox" name="accepted_payment_methods[]" value="bkash" <?= in_array('bkash', $acceptedPayments, true) ? 'checked' : '' ?>> bKash</label><label class="admin-check"><input type="checkbox" name="accepted_payment_methods[]" value="nagad" <?= in_array('nagad', $acceptedPayments, true) ? 'checked' : '' ?>> Nagad</label><label class="admin-check"><input type="checkbox" name="accepted_payment_methods[]" value="cod" <?= in_array('cod', $acceptedPayments, true) ? 'checked' : '' ?>> Cash on delivery</label>
            </fieldset><button class="button button-dark" type="submit">Save store details</button>
            <p class="form-message" aria-live="polite"></p>
        </form>
    </section>
    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">A new chapter</span>
                <h2>Collections</h2>
            </div>
        </div>
        <form class="admin-form-stack" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=settings"><?= csrf_field() ?><input type="hidden" name="action" value="admin.category.save"><label>Collection name<input name="name" required maxlength="100" placeholder="Discovery sets"></label><label>Description<textarea name="description" rows="2" maxlength="1000"></textarea></label><button class="button button-outline" type="submit">Add collection</button>
            <p class="form-message" aria-live="polite"></p>
        </form>
        <ul class="category-admin-list"><?php foreach ($categories as $category): ?><li>
                    <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Collection updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.category.save"><input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>"><label><span class="sr-only">Collection name</span><input name="name" value="<?= h($category['name']) ?>" required maxlength="100"></label><label><span class="sr-only">Collection description</span><input name="description" value="<?= h($category['description'] ?? '') ?>" maxlength="1000" placeholder="Description"></label><button class="table-action" type="submit">Save</button>
                        <p class="form-message" aria-live="polite"></p>
                    </form><code><?= h($category['slug']) ?></code>
                    <form action="../api/index.php" method="post" data-api-form data-success="Collection visibility updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.category.toggle"><input type="hidden" name="category_id" value="<?= (int) $category['id'] ?>"><input type="hidden" name="is_active" value="<?= $category['is_active'] ? 0 : 1 ?>"><button class="table-action" type="submit"><?= $category['is_active'] ? 'Deactivate' : 'Activate' ?></button></form>
                </li><?php endforeach; ?></ul>
    </section>
    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">Fragrance houses</span>
                <h2>Brands</h2>
            </div>
        </div>
        <form class="admin-form-stack" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=settings"><?= csrf_field() ?><input type="hidden" name="action" value="admin.brand.save"><label>Brand name<input name="name" required maxlength="120" placeholder="Atelier des Senteurs"></label><label>Short description<textarea name="description" rows="2" maxlength="1000"></textarea></label><button class="button button-outline" type="submit">Add brand</button>
            <p class="form-message" aria-live="polite"></p>
        </form>
        <ul class="category-admin-list"><?php foreach (($brands ?? []) as $brand): ?><li>
                    <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Fragrance house updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.brand.save"><input type="hidden" name="brand_id" value="<?= (int) $brand['id'] ?>"><label><span class="sr-only">Brand name</span><input name="name" value="<?= h($brand['name']) ?>" required maxlength="120"></label><label><span class="sr-only">Brand description</span><input name="description" value="<?= h($brand['description'] ?? '') ?>" maxlength="1000" placeholder="Description"></label><button class="table-action" type="submit">Save</button>
                        <p class="form-message" aria-live="polite"></p>
                    </form><code><?= h($brand['slug']) ?></code>
                    <form action="../api/index.php" method="post" data-api-form data-success="Brand visibility updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.brand.toggle"><input type="hidden" name="brand_id" value="<?= (int) $brand['id'] ?>"><input type="hidden" name="is_active" value="<?= $brand['is_active'] ? 0 : 1 ?>"><button class="table-action" type="submit"><?= $brand['is_active'] ? 'Deactivate' : 'Activate' ?></button></form>
                </li><?php endforeach; ?></ul>
    </section>
    <section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">Administrator</span>
                <h2>Studio profile</h2>
            </div>
        </div>
        <form class="admin-form-stack" action="../api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="admin.profile.update"><label>Full name<input name="full_name" value="<?= h($adminProfile['full_name'] ?? '') ?>" required maxlength="120"></label><label>Login email<input name="email" type="email" value="<?= h($adminProfile['email'] ?? '') ?>" required maxlength="190" autocomplete="username"></label><button class="button button-dark" type="submit">Save profile</button>
            <p class="form-message" aria-live="polite"></p>
        </form>
        <form class="admin-form-stack admin-password-form" action="../api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="admin.password.change"><label>Current password<input name="current_password" type="password" autocomplete="current-password" required></label><label>New password<input name="new_password" type="password" autocomplete="new-password" minlength="12" maxlength="200" required></label><label>Confirm new password<input name="confirm_password" type="password" autocomplete="new-password" minlength="12" maxlength="200" required></label><button class="button button-outline" type="submit">Change password</button>
            <p class="form-message" aria-live="polite"></p>
        </form>
    </section>
</div>