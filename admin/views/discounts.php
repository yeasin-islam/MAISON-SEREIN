<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">A considered invitation</span>
            <h2>Create an offer</h2>
        </div>
    </div>
    <form class="admin-form-grid" action="../api/index.php" method="post" data-api-form data-redirect-success="?page=discounts"><?= csrf_field() ?><input type="hidden" name="action" value="admin.discount.save"><label>Offer code<input name="code" required maxlength="40" placeholder="WELCOME10"></label><label>Type<select name="discount_type">
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed amount (BDT)</option>
            </select></label><label>Value<input name="discount_value" type="number" min="0.01" step="0.01" required></label><label>Minimum purchase (BDT)<input name="minimum_purchase" type="number" min="0" step="0.01" value="0"></label><label>Maximum discount (BDT, optional)<input name="max_discount" type="number" min="0" step="0.01"></label><label>Applies to<select name="product_id">
                <option value="">Entire order</option><?php foreach (($discountProducts ?? []) as $product): ?><option value="<?= (int) $product['id'] ?>"><?= h($product['name']) ?></option><?php endforeach; ?>
            </select></label><label>Usage limit (optional)<input name="usage_limit" type="number" min="1" step="1" placeholder="Unlimited"></label><label>Starts<input name="starts_at" type="datetime-local" required></label><label>Ends<input name="ends_at" type="datetime-local" required></label><label class="admin-check"><input type="checkbox" name="first_order_only" value="1"> First order only</label>
        <div class="admin-span"><button class="button button-dark" type="submit">Create offer →</button>
            <p class="form-message" aria-live="polite"></p>
        </div>
    </form>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Offer management</span>
            <h2>Offer codes</h2>
        </div><span><?= count($discounts ?? []) ?> offers</span>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Applies to</th>
                    <th>Discount</th>
                    <th>Minimum</th>
                    <th>Usage</th>
                    <th>Validity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody><?php foreach (($discounts ?? []) as $discount): ?><tr>
                        <td><strong><?= h($discount['code']) ?></strong><?php if ($discount['first_order_only']): ?><small class="table-secondary">First order</small><?php endif; ?><details class="offer-edit">
                                <summary>Edit offer</summary>
                                <form class="admin-form-stack" action="../api/index.php" method="post" data-api-form data-success="Offer code updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.discount.save"><input type="hidden" name="discount_id" value="<?= (int) $discount['id'] ?>"><label>Offer code<input name="code" value="<?= h($discount['code']) ?>" required maxlength="40"></label><label>Type<select name="discount_type">
                                            <option value="percentage" <?= $discount['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                                            <option value="fixed" <?= $discount['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed amount</option>
                                        </select></label><label>Value<input name="discount_value" type="number" min="0.01" step="0.01" value="<?= h($discount['discount_value']) ?>" required></label><label>Minimum purchase<input name="minimum_purchase" type="number" min="0" step="0.01" value="<?= h($discount['minimum_purchase']) ?>"></label><label>Maximum discount<input name="max_discount" type="number" min="0" step="0.01" value="<?= h($discount['max_discount'] ?? '') ?>"></label><label>Applies to<select name="product_id">
                                            <option value="">Entire order</option><?php foreach (($discountProducts ?? []) as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (int) ($discount['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>><?= h($product['name']) ?></option><?php endforeach; ?>
                                        </select></label><label>Usage limit<input name="usage_limit" type="number" min="1" value="<?= h($discount['usage_limit'] ?? '') ?>" placeholder="Unlimited"></label><label>Starts<input name="starts_at" type="datetime-local" value="<?= h(date('Y-m-d\\TH:i', strtotime($discount['starts_at']))) ?>" required></label><label>Ends<input name="ends_at" type="datetime-local" value="<?= h(date('Y-m-d\\TH:i', strtotime($discount['ends_at']))) ?>" required></label><label class="admin-check"><input type="checkbox" name="first_order_only" value="1" <?= $discount['first_order_only'] ? 'checked' : '' ?>> First order only</label><button class="button button-dark" type="submit">Save offer</button>
                                    <p class="form-message" aria-live="polite"></p>
                                </form>
                            </details>
                        </td>
                        <td><?= h($discount['product_name'] ?? 'Entire order') ?></td>
                        <td><?= $discount['discount_type'] === 'percentage' ? h($discount['discount_value']) . '%' : money($discount['discount_value']) ?></td>
                        <td><?= money($discount['minimum_purchase']) ?></td>
                        <td><?= (int) $discount['uses_count'] ?><?= $discount['usage_limit'] ? ' / ' . (int) $discount['usage_limit'] : '' ?></td>
                        <td><?= h(date('j M Y', strtotime($discount['starts_at']))) ?>–<?= h(date('j M Y', strtotime($discount['ends_at']))) ?></td>
                        <td>
                            <form action="../api/index.php" method="post" data-api-form data-success="Offer status saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.discount.toggle"><input type="hidden" name="discount_id" value="<?= (int) $discount['id'] ?>"><input type="hidden" name="is_active" value="<?= $discount['is_active'] ? 0 : 1 ?>"><button class="table-action" type="submit"><?= $discount['is_active'] ? 'Pause' : 'Activate' ?></button></form>
                        </td>
                    </tr><?php endforeach; ?><?php if (empty($discounts)): ?><tr>
                        <td colspan="7">
                            <div class="admin-empty">Create an offer to make a code available at checkout.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>