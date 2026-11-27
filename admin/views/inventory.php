<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">What&apos;s on the shelf</span>
            <h2>Inventory levels</h2>
        </div><span>Each size uses its own low-stock threshold</span>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Fragrance</th>
                    <th>SKU</th>
                    <th>Size</th>
                    <th>Price</th>
                    <th>On hand</th>
                    <th>Adjust</th>
                </tr>
            </thead>
            <tbody><?php foreach (($inventory ?? []) as $row): ?><tr>
                        <td><strong><?= h($row['product_name']) ?></strong><small class="table-secondary"><?= h($row['brand_name']) ?></small></td>
                        <td><code><?= h($row['sku']) ?></code></td>
                        <td><?= h($row['size_label']) ?></td>
                        <td><?= money($row['price']) ?></td>
                        <td><span class="stock-indicator <?= (int) $row['stock_quantity'] <= (int) $row['low_stock_threshold'] ? 'stock-low' : '' ?>"><?= (int) $row['stock_quantity'] ?></span>
                            <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Low-stock threshold saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.stock.threshold"><input type="hidden" name="variant_id" value="<?= (int) $row['id'] ?>"><label class="sr-only" for="threshold-<?= (int) $row['id'] ?>">Low-stock alert threshold for <?= h($row['product_name']) ?></label><input id="threshold-<?= (int) $row['id'] ?>" name="low_stock_threshold" type="number" min="0" max="100000" value="<?= (int) $row['low_stock_threshold'] ?>" required><button class="table-action" type="submit">Set alert</button>
                                <p class="form-message" aria-live="polite"></p>
                            </form>
                        </td>
                        <td>
                            <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Inventory adjusted."><?= csrf_field() ?><input type="hidden" name="action" value="admin.stock.adjust"><input type="hidden" name="variant_id" value="<?= (int) $row['id'] ?>"><label class="sr-only" for="adjust-<?= (int) $row['id'] ?>">Quantity change for <?= h($row['product_name']) ?></label><input id="adjust-<?= (int) $row['id'] ?>" name="quantity_change" type="number" step="1" placeholder="± qty" required><button class="table-action" type="submit">Adjust</button>
                                <p class="form-message" aria-live="polite"></p>
                            </form>
                        </td>
                    </tr><?php endforeach; ?><?php if (empty($inventory)): ?><tr>
                        <td colspan="6">
                            <div class="admin-empty">No active variants are available for inventory management.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Auditable stock movement</span>
            <h2>Inventory transaction history</h2>
        </div><span>Latest 50 movements</span>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Fragrance / size</th>
                    <th>Change</th>
                    <th>Reason</th>
                    <th>Order</th>
                    <th>Recorded by</th>
                </tr>
            </thead>
            <tbody><?php foreach (($inventoryHistory ?? []) as $entry): ?><tr>
                        <td><?= h(date('j M Y · H:i', strtotime($entry['created_at']))) ?></td>
                        <td><strong><?= h($entry['product_name']) ?></strong><small class="table-secondary"><?= h($entry['size_label']) ?> · <?= h($entry['sku']) ?></small></td>
                        <td><strong><?= (int) $entry['quantity_change'] > 0 ? '+' : '' ?><?= (int) $entry['quantity_change'] ?></strong></td>
                        <td><?= h(ucfirst($entry['reason'])) ?><small class="table-secondary"><?= h($entry['note'] ?? '') ?></small></td>
                        <td><?= $entry['order_id'] ? h((string) $entry['order_id']) : '—' ?></td>
                        <td><?= h($entry['admin_name'] ?? 'System') ?></td>
                    </tr><?php endforeach; ?><?php if (empty($inventoryHistory)): ?><tr>
                        <td colspan="6">
                            <div class="admin-empty">Stock movements will appear after an order or adjustment.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>