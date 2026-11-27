<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">A time-bound release</span>
            <h2>Schedule a limited drop</h2>
        </div>
    </div>
    <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=drops"><?= csrf_field() ?><input type="hidden" name="action" value="admin.drop.save"><label>Fragrance<select name="product_id" required>
                <option value="">Choose a product</option><?php foreach (($dropProducts ?? []) as $product): ?><option value="<?= (int) $product['id'] ?>"><?= h($product['name']) ?></option><?php endforeach; ?>
            </select></label><label>Release date/time<input name="release_at" type="datetime-local" required></label><label>End date/time<input name="end_at" type="datetime-local"></label><label>Limited stock cap<input name="stock_limit" type="number" min="1" max="100000"></label><label>Status<select name="status">
                <option value="scheduled">Scheduled</option>
                <option value="active">Active</option>
                <option value="ended">Ended</option>
                <option value="hidden">Hidden</option>
            </select></label><label class="admin-check"><input name="allow_preorder" type="checkbox" value="1"> Allow pre-orders</label>
        <div class="admin-span"><button class="button button-dark" type="submit">Save drop</button>
            <p class="form-message" aria-live="polite"></p>
        </div>
    </form>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Release calendar</span>
            <h2>Limited drops</h2>
        </div><span><?= count($drops ?? []) ?> scheduled</span>
    </div>
    <div class="admin-content-list"><?php foreach (($drops ?? []) as $drop): ?><form class="admin-form-grid admin-content-card" action="../api/index.php" method="post" data-api-form data-success="Limited drop updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.drop.save"><input type="hidden" name="drop_id" value="<?= (int) $drop['id'] ?>"><input type="hidden" name="product_id" value="<?= (int) $drop['product_id'] ?>">
                <div class="admin-span"><strong><?= h($drop['product_name']) ?></strong></div><label>Release date/time<input name="release_at" type="datetime-local" value="<?= h(date('Y-m-d\\TH:i', strtotime($drop['release_at']))) ?>" required></label><label>End date/time<input name="end_at" type="datetime-local" value="<?= !empty($drop['end_at']) ? h(date('Y-m-d\\TH:i', strtotime($drop['end_at']))) : '' ?>"></label><label>Limited stock cap<input name="stock_limit" type="number" min="1" max="100000" value="<?= h($drop['stock_limit'] ?? '') ?>"></label><label>Status<select name="status"><?php foreach (['scheduled' => 'Scheduled', 'active' => 'Active', 'ended' => 'Ended', 'hidden' => 'Hidden'] as $value => $label): ?><option value="<?= h($value) ?>" <?= $drop['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label><label class="admin-check"><input name="allow_preorder" type="checkbox" value="1" <?= $drop['allow_preorder'] ? 'checked' : '' ?>> Allow pre-orders</label>
                <div class="admin-span admin-form-actions"><button class="button button-dark" type="submit">Save release</button><span class="table-secondary">#<?= (int) $drop['id'] ?></span>
                    <p class="form-message" aria-live="polite"></p>
                </div>
            </form><?php endforeach; ?><?php if (empty($drops)): ?><div class="admin-empty">No releases are scheduled. Create one above.</div><?php endif; ?></div>
</section>