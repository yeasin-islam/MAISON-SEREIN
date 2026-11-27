<?php if ($payments): ?><section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">Check against your payment account</span>
                <h2>Payments and cash collection</h2>
            </div>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Method</th>
                        <th>Transaction</th>
                        <th>Amount</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($payments as $payment): ?><tr>
                            <td><?= h($payment['order_number']) ?></td>
                            <td><?= h($payment['customer_name']) ?></td>
                            <td><?= h($payment['phone']) ?></td>
                            <td><?= h(strtoupper($payment['payment_method'])) ?></td>
                            <td><code><?= h($payment['transaction_id'] ?? '—') ?></code></td>
                            <td><?= money($payment['amount']) ?></td>
                            <td>
                                <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Payment status saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.payment.update"><input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>"><button class="table-action" name="status" value="verified"><?= $payment['payment_method'] === 'cod' ? 'Mark collected' : 'Verify' ?></button><?php if ($payment['status'] === 'pending_verification'): ?><button class="table-action table-action-muted" name="status" value="rejected">Reject</button><?php endif; ?><p class="form-message" aria-live="polite"></p>
                                </form>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </section><?php endif; ?>
<?php if (!empty($orderDetail)): ?><section class="admin-panel">
        <div class="admin-panel-heading">
            <div><span class="eyebrow">Order detail</span>
                <h2><?= h($orderDetail['order_number']) ?></h2>
            </div><a class="text-link" href="?page=orders">Close detail ×</a>
        </div>
        <div class="admin-order-facts">
            <p><strong><?= h($orderDetail['customer_name']) ?></strong><br><?= h($orderDetail['phone']) ?> · <?= h($orderDetail['email'] ?? '') ?></p>
            <p><?= h($orderDetail['delivery_address']) ?><br><?= h($orderDetail['district']) ?></p>
            <p>Payment: <?= h(strtoupper($orderDetail['payment_method'])) ?> · <?= h(ucfirst(str_replace('_', ' ', $orderDetail['payment_status']))) ?><br>Fulfilment: <?= h(ucfirst($orderDetail['order_status'])) ?></p>
            <div>
                <p>Confirmation email: <strong><?= h(ucfirst($orderDetail['email_status'] ?? 'not attempted')) ?></strong><?php if (!empty($orderDetail['email_sent_at'])): ?><br>Sent <?= h(date('j M Y · H:i', strtotime($orderDetail['email_sent_at']))) ?><?php endif; ?></p>
                <?php $emailRetryable = ($orderDetail['email_status'] ?? '') === 'failed' || (($orderDetail['email_status'] ?? '') === 'sending' && !empty($orderDetail['email_last_attempt_at']) && strtotime($orderDetail['email_last_attempt_at']) < time() - 600); ?>
                <?php if ($emailRetryable): ?><form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Order confirmation email sent."><?= csrf_field() ?><input type="hidden" name="action" value="admin.order.email.retry"><input type="hidden" name="order_id" value="<?= (int) $orderDetail['id'] ?>"><button class="table-action" type="submit">Retry email</button><p class="form-message" aria-live="polite"></p></form><?php endif; ?>
            </div>
            <p>Subtotal <?= money($orderDetail['subtotal']) ?> · Discount <?= money($orderDetail['discount_amount']) ?> · Delivery <?= money($orderDetail['delivery_fee']) ?><br><strong>Total <?= money($orderDetail['grand_total']) ?></strong></p><?php if (!empty($orderDetail['order_note'])): ?><p>Customer note: <?= h($orderDetail['order_note']) ?></p><?php endif; ?><?php if (!empty($orderDetail['gift_wrap']) || !empty($orderDetail['gift_message'])): ?><p>Gift wrap: <?= !empty($orderDetail['gift_wrap']) ? 'Yes' : 'No' ?><br>Gift message: <?= h($orderDetail['gift_message'] ?? '') ?></p><?php endif; ?>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Unit price</th>
                        <th>Quantity</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($orderDetail['items'] as $item): ?><tr>
                            <td><?= h($item['product_name']) ?></td>
                            <td><?= h($item['size_label']) ?></td>
                            <td><?= money($item['unit_price']) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= money($item['line_total']) ?></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <h3 class="admin-subheading">Order history</h3>
        <ol class="admin-history-list"><?php foreach ($orderDetail['history'] as $event): ?><li><strong><?= h(ucfirst($event['status'])) ?></strong><time><?= h(date('j M Y · H:i', strtotime($event['created_at']))) ?></time><?php if (!empty($event['note'])): ?><small><?= h($event['note']) ?></small><?php endif; ?></li><?php endforeach; ?></ol>
    </section><?php elseif (isset($detailId) && $detailId): ?><section class="admin-panel">
        <p class="admin-empty">That order could not be found.</p>
    </section><?php endif; ?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">The order desk</span>
            <h2>All orders</h2>
        </div><span>Most recent 100</span>
    </div>
    <form class="admin-toolbar" method="get" action="index.php"><input type="hidden" name="page" value="orders"><label class="sr-only" for="order-search">Search orders</label><input id="order-search" name="q" value="<?= h($orderSearch ?? '') ?>" placeholder="Order number, customer, or phone"><label><span class="sr-only">Order status</span><select name="status">
                <option value="">All statuses</option><?php foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status): ?><option value="<?= h($status) ?>" <?= ($orderStatus ?? '') === $status ? 'selected' : '' ?>><?= h(ucfirst($status)) ?></option><?php endforeach; ?>
            </select></label><button class="button button-dark" type="submit">Filter orders</button><a class="text-link" href="?page=orders">Reset</a></form>
    <div class="admin-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order / date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Fulfilment</th>
                </tr>
            </thead>
            <tbody><?php foreach (($orders ?? []) as $order): ?><tr>
                        <td><a href="?page=orders&amp;id=<?= (int) $order['id'] ?>"><strong><?= h($order['order_number']) ?></strong></a><small class="table-secondary"><?= h(date('j M Y · H:i', strtotime($order['created_at']))) ?></small></td>
                        <td><?= h($order['customer_name']) ?><small class="table-secondary"><?= h($order['phone']) ?></small></td>
                        <td><?= (int) $order['item_count'] ?></td>
                        <td><?= money($order['grand_total']) ?></td>
                        <td><span class="status-pill status-<?= h($order['payment_status']) ?>"><?= h(ucfirst(str_replace('_', ' ', $order['payment_status']))) ?></span></td>
                        <td>
                            <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Order status updated."><?= csrf_field() ?><input type="hidden" name="action" value="admin.order.update"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><select name="status" aria-label="Fulfilment status for <?= h($order['order_number']) ?>"><?php foreach (['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status): ?><option value="<?= h($status) ?>" <?= $order['order_status'] === $status ? 'selected' : '' ?>><?= h(ucfirst($status)) ?></option><?php endforeach; ?></select><button class="table-action" type="submit">Save</button>
                                <p class="form-message" aria-live="polite"></p>
                            </form>
                        </td>
                    </tr><?php endforeach; ?><?php if (empty($orders)): ?><tr>
                        <td colspan="6">
                            <div class="admin-empty">No orders match this filter.</div>
                        </td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>