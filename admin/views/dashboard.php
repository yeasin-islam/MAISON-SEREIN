<section class="admin-stat-grid">
  <article class="admin-stat"><span>Gross verified sales</span><strong><?= money($stats['gross_sales'] ?? 0) ?></strong><small>Payments verified or collected</small></article>
  <article class="admin-stat"><span>Orders today</span><strong><?= (int) ($stats['today_orders'] ?? 0) ?></strong><small><?= money($stats['today_revenue'] ?? 0) ?> verified today</small></article>
  <article class="admin-stat"><span>Total orders</span><strong><?= (int) ($stats['total_orders'] ?? 0) ?></strong><small>All fulfilment states</small></article>
  <article class="admin-stat"><span>Active fragrances</span><strong><?= (int) ($stats['total_products'] ?? 0) ?></strong><small><?= (int) ($stats['total_customers'] ?? 0) ?> active customer accounts</small></article>
  <article class="admin-stat"><span>Low stock</span><strong><?= (int) ($stats['low_stock'] ?? 0) ?></strong><small>Variants at or below threshold</small></article>
</section>
<section class="admin-status-grid" aria-label="Order status totals">
  <?php foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $key => $label): ?><article class="admin-status-card"><span><?= h($label) ?></span><strong><?= (int) ($stats[$key . '_orders'] ?? 0) ?></strong></article><?php endforeach; ?>
</section>
<div class="admin-dashboard-grid">
  <section class="admin-panel">
    <div class="admin-panel-heading">
      <div><span class="eyebrow">Last seven days</span>
        <h2>Sales overview</h2>
      </div><a class="text-link" href="?page=reports">Full reports →</a>
    </div>
    <div class="admin-chart-wrap"><canvas id="dashboard-sales-chart" aria-label="Verified sales over the last seven days" role="img"></canvas></div>
  </section>
  <section class="admin-panel">
    <div class="admin-panel-heading">
      <div><span class="eyebrow">Popular this season</span>
        <h2>Top sellers</h2>
      </div>
    </div><?php if (!empty($dashboardTopProducts)): ?><div class="admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Fragrance</th>
              <th>Units</th>
              <th>Sales</th>
            </tr>
          </thead>
          <tbody><?php foreach ($dashboardTopProducts as $product): ?><tr>
                <td><?= h($product['product_name']) ?></td>
                <td><?= (int) $product['units_sold'] ?></td>
                <td><?= money($product['revenue']) ?></td>
              </tr><?php endforeach; ?></tbody>
        </table>
      </div><?php else: ?><div class="admin-empty">Top sellers will appear after the first verified order.</div><?php endif; ?>
  </section>
</div>
<?php if (!empty($pendingPayments)): ?><section class="admin-panel">
    <div class="admin-panel-heading">
      <div><span class="eyebrow">A small check-in</span>
        <h2>Payments to review</h2>
      </div><a class="text-link" href="?page=orders">All orders →</a>
    </div>
    <div class="admin-table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Method</th>
            <th>Transaction</th>
            <th>Amount</th>
            <th>Review</th>
          </tr>
        </thead>
        <tbody><?php foreach ($pendingPayments as $payment): ?><tr>
              <td><?= h($payment['order_number']) ?></td>
              <td><?= h($payment['customer_name']) ?></td>
              <td><?= h(strtoupper($payment['payment_method'])) ?></td>
              <td><code><?= h($payment['transaction_id'] ?? '—') ?></code></td>
              <td><?= money($payment['amount']) ?></td>
              <td>
                <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Payment review saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.payment.update"><input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>"><button class="table-action" name="status" value="verified">Verify</button><button class="table-action table-action-muted" name="status" value="rejected">Reject</button>
                  <p class="form-message" aria-live="polite"></p>
                </form>
              </td>
            </tr><?php endforeach; ?></tbody>
      </table>
    </div>
  </section><?php endif; ?>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">At a glance</span>
      <h2>Recent orders</h2>
    </div><a class="text-link" href="?page=orders">Open order desk →</a>
  </div><?php if ($recentOrders): ?><div class="admin-table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Placed</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody><?php foreach ($recentOrders as $order): ?><tr>
              <td><strong><?= h($order['order_number']) ?></strong></td>
              <td><?= h($order['customer_name']) ?></td>
              <td><?= h(date('j M, H:i', strtotime($order['created_at']))) ?></td>
              <td><span class="status-pill status-<?= h($order['order_status']) ?>"><?= h(ucfirst($order['order_status'])) ?></span></td>
              <td><?= h(ucfirst(str_replace('_', ' ', $order['payment_status']))) ?></td>
              <td><?= money($order['grand_total']) ?></td>
            </tr><?php endforeach; ?></tbody>
      </table>
    </div><?php else: ?><div class="admin-empty">Your first order will appear here.</div><?php endif; ?>
</section>
<?php if (!empty($notifications)): ?><section class="admin-panel">
    <div class="admin-panel-heading">
      <div><span class="eyebrow">Notification center</span>
        <h2>Recent store activity</h2>
      </div>
    </div>
    <div class="admin-notification-list"><?php foreach ($notifications as $notification): ?><article class="admin-notification">
          <div><strong><?= h($notification['title']) ?></strong>
            <p><?= h($notification['body']) ?></p><small><?= h(date('j M, H:i', strtotime($notification['created_at']))) ?></small>
          </div>
          <form action="../api/index.php" method="post" data-api-form data-success="Notification cleared."><?= csrf_field() ?><input type="hidden" name="action" value="admin.notification.read"><input type="hidden" name="notification_id" value="<?= (int) $notification['id'] ?>"><button class="table-action" type="submit">Mark read</button></form>
        </article><?php endforeach; ?></div>
  </section><?php endif; ?>
<section class="admin-panel">
  <div class="admin-panel-heading">
    <div><span class="eyebrow">Quick studio links</span>
      <h2>Store health and actions</h2>
    </div>
  </div>
  <div class="admin-quick-links"><a class="button button-light" href="?page=inventory">Review inventory</a><a class="button button-light" href="?page=delivery">Delivery fees</a><a class="button button-light" href="?page=content">Homepage content</a><a class="button button-light" href="?page=drops">Schedule a drop</a><a class="button button-light" href="?page=reports">Sales reports</a></div>
  <p class="admin-note">Inventory alerts are based on each variant&apos;s configured low-stock threshold. Payment totals include only verified or collected payments.</p>
</section>
<script type="application/json" id="dashboard-sales-data">
  <?= json_encode(['labels' => array_column($dashboardSales ?? [], 'sale_day'), 'values' => array_map('floatval', array_column($dashboardSales ?? [], 'total'))], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const node = document.getElementById('dashboard-sales-data');
    const canvas = document.getElementById('dashboard-sales-chart');
    if (!node || !canvas || !window.Chart) return;
    const sales = JSON.parse(node.textContent);
    new Chart(canvas, {
      type: 'line',
      data: {
        labels: sales.labels,
        datasets: [{
          label: 'Verified sales (BDT)',
          data: sales.values,
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