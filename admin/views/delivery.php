<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Checkout configuration</span>
            <h2>Delivery zones</h2>
        </div><span>Charges are read from MySQL at checkout</span>
    </div>
    <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=delivery"><?= csrf_field() ?><input type="hidden" name="action" value="admin.delivery.save"><label>Zone name<input name="name" required maxlength="100" placeholder="Inside Dhaka"></label><label>Delivery fee (BDT)<input name="delivery_fee" type="number" min="0" step="0.01" required value="0"></label><label>Estimated delivery<input name="estimated_days" maxlength="40" placeholder="1–2 business days"></label><label>Sort order<input name="sort_order" type="number" min="0" max="999" value="0"></label><label class="admin-span">Customer instructions<input name="instructions" maxlength="300"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" checked> Active at checkout</label>
        <div class="admin-span"><button class="button button-dark" type="submit">Add delivery zone</button>
            <p class="form-message" aria-live="polite"></p>
        </div>
    </form>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Current zones</span>
            <h2>Fees and service notes</h2>
        </div>
    </div><?php foreach (($deliveryZones ?? []) as $zone): ?><form class="admin-form-grid admin-content-card" action="../api/index.php" method="post" data-api-form data-success="Delivery zone saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.delivery.save"><input type="hidden" name="zone_id" value="<?= (int) $zone['id'] ?>"><label>Zone name<input name="name" value="<?= h($zone['name']) ?>" required maxlength="100"></label><label>Delivery fee (BDT)<input name="delivery_fee" type="number" min="0" step="0.01" value="<?= h($zone['delivery_fee']) ?>" required></label><label>Estimated delivery<input name="estimated_days" value="<?= h($zone['estimated_days'] ?? '') ?>" maxlength="40"></label><label>Sort order<input name="sort_order" type="number" value="<?= (int) $zone['sort_order'] ?>" min="0" max="999"></label><label class="admin-span">Customer instructions<input name="instructions" value="<?= h($zone['instructions'] ?? '') ?>" maxlength="300"></label><label class="admin-check"><input name="is_active" type="checkbox" value="1" <?= $zone['is_active'] ? 'checked' : '' ?>> Available in checkout</label>
            <div class="admin-span admin-form-actions"><button class="button button-dark" type="submit">Save zone</button><span class="table-secondary">#<?= (int) $zone['id'] ?></span>
                <p class="form-message" aria-live="polite"></p>
            </div>
        </form><?php endforeach; ?><?php if (empty($deliveryZones)): ?><div class="admin-empty">Create delivery zones to make checkout available.</div><?php endif; ?>
</section>