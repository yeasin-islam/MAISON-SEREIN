<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Store performance</span>
            <h2>Sales and order reports</h2>
        </div>
    </div>
    <form class="admin-toolbar" method="get" action="index.php"><input type="hidden" name="page" value="reports"><label>From<input type="date" name="from" value="<?= h($reportFrom) ?>" required></label><label>To<input type="date" name="to" value="<?= h($reportTo) ?>" required></label><button class="button button-dark" type="submit">Apply dates</button><a class="button button-light" href="?page=reports&amp;from=<?= rawurlencode($reportFrom) ?>&amp;to=<?= rawurlencode($reportTo) ?>&amp;export=csv">Export CSV</a></form>
</section>
<section class="admin-stat-grid">
    <article class="admin-stat"><span>Verified sales</span><strong><?= money($reportStats['total_sales'] ?? 0) ?></strong><small><?= h($reportFrom) ?> through <?= h($reportTo) ?></small></article>
    <article class="admin-stat"><span>Orders</span><strong><?= (int) ($reportStats['total_orders'] ?? 0) ?></strong><small>All payment states</small></article>
    <article class="admin-stat"><span>Pending orders</span><strong><?= (int) ($reportStats['pending_orders'] ?? 0) ?></strong><small>Awaiting confirmation</small></article>
    <article class="admin-stat"><span>Delivered</span><strong><?= (int) ($reportStats['delivered_orders'] ?? 0) ?></strong><small><?= (int) ($reportStats['cancelled_orders'] ?? 0) ?> cancelled</small></article>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Verified payments</span>
            <h2>Daily sales</h2>
        </div>
    </div>
    <div class="admin-chart-wrap"><canvas id="sales-report-chart" aria-label="Verified sales chart" role="img"></canvas><?php if (empty($reportRows)): ?><p class="admin-empty">No verified sales in this date range.</p><?php endif; ?></div>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Product performance</span>
            <h2>Best-selling fragrances</h2>
        </div>
    </div>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Fragrance</th>
                    <th>Units sold</th>
                    <th>Verified revenue</th>
                </tr>
            </thead>
            <tbody><?php foreach (($productPerformance ?? []) as $product): ?><tr>
                        <td><strong><?= h($product['product_name']) ?></strong></td>
                        <td><?= (int) $product['units_sold'] ?></td>
                        <td><?= money($product['revenue']) ?></td>
                    </tr><?php endforeach; ?><?php if (empty($productPerformance)): ?><tr>
                        <td colspan="3">
                            <div class="admin-empty">No product sales for this period.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Stock health</span>
            <h2>Inventory summary</h2>
        </div>
    </div>
    <div class="admin-stat-grid">
        <article class="admin-stat"><span>Active variants</span><strong><?= (int) ($inventorySummary['active_variants'] ?? 0) ?></strong></article>
        <article class="admin-stat"><span>Units on hand</span><strong><?= (int) ($inventorySummary['units_in_stock'] ?? 0) ?></strong></article>
        <article class="admin-stat"><span>Low stock</span><strong><?= (int) ($inventorySummary['low_stock'] ?? 0) ?></strong></article>
        <article class="admin-stat"><span>Out of stock</span><strong><?= (int) ($inventorySummary['out_of_stock'] ?? 0) ?></strong></article>
    </div>
</section>
<script type="application/json" id="sales-report-data">
    <?= json_encode(['labels' => $salesChartLabels ?? [], 'values' => $salesChartValues ?? []], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const node = document.getElementById('sales-report-data');
        const canvas = document.getElementById('sales-report-chart');
        if (!node || !canvas || !window.Chart) return;
        const report = JSON.parse(node.textContent);
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: report.labels,
                datasets: [{
                    label: 'Verified sales (BDT)',
                    data: report.values,
                    borderColor: '#9b7654',
                    backgroundColor: 'rgba(155,118,84,.14)',
                    fill: true,
                    tension: .32,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '৳' + Number(value).toLocaleString()
                        }
                    }
                }
            }
        });
    });
</script>