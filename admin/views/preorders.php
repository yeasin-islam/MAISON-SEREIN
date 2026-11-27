<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Customer commitments</span>
            <h2>Pre-orders</h2>
        </div><span><?= count($preorders ?? []) ?> records</span>
    </div>
    <p class="admin-note">Pre-orders are tracked separately from on-hand inventory so reserving a future release never creates negative stock.</p>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Fragrance</th>
                    <th>Reserved</th>
                    <th>Expected ship</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody><?php foreach (($preorders ?? []) as $preorder): ?><tr>
                        <td><strong><?= h($preorder['order_number']) ?></strong><small class="table-secondary"><?= h(ucfirst($preorder['order_status'])) ?></small></td>
                        <td><?= h($preorder['product_name']) ?></td>
                        <td><?= h(date('j M Y', strtotime($preorder['created_at']))) ?></td>
                        <td><?= !empty($preorder['expected_ship_date']) ? h(date('j M Y', strtotime($preorder['expected_ship_date']))) : '—' ?></td>
                        <td><span class="status-pill status-<?= h($preorder['status']) ?>"><?= h(ucfirst($preorder['status'])) ?></span></td>
                        <td>
                            <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Pre-order status saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.preorder.update"><input type="hidden" name="preorder_id" value="<?= (int) $preorder['id'] ?>"><select name="status" aria-label="Pre-order status"><?php foreach (['reserved' => 'Reserved', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'] as $value => $label): ?><option value="<?= h($value) ?>" <?= $preorder['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select><input name="expected_ship_date" type="date" aria-label="Expected ship date" value="<?= h($preorder['expected_ship_date'] ?? '') ?>"><button class="table-action" type="submit">Save</button>
                                <p class="form-message" aria-live="polite"></p>
                            </form>
                        </td>
                    </tr><?php endforeach; ?><?php if (empty($preorders)): ?><tr>
                        <td colspan="6">
                            <div class="admin-empty">Pre-orders will appear here after checkout.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>