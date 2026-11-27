<?php
$formatAmount = static fn($value): string => 'BDT ' . number_format((float) $value, 2);
?>
THE LAST
NOTE BD
AUTHENTIC FRAGRANCE DECANT HOUSE
ORDER CONFIRMATION

Thank you, <?= $order['customer_name'] ?>. Your order is confirmed.

Order ID: <?= (int) $order['id'] ?>
Order reference: <?= $order['order_number'] ?>
Date: <?= date('j M Y, H:i', strtotime((string) $order['created_at'])) ?>

YOUR SELECTION
<?php foreach ($items as $item): ?>
- <?= $item['product_name'] ?> / <?= $item['size_label'] ?> / Qty <?= (int) $item['quantity'] ?> / Unit <?= $formatAmount($item['unit_price']) ?> / Line total <?= $formatAmount($item['line_total']) ?>
<?php endforeach; ?>

PAYMENT SUMMARY
Product subtotal: <?= $formatAmount($order['subtotal']) ?>
Product discount: <?= $formatAmount($order['product_discount']) ?>
Coupon<?= !empty($order['coupon_code']) ? ' (' . $order['coupon_code'] . ')' : '' ?>: -<?= $formatAmount($order['discount_amount']) ?>
Delivery<?= !empty($order['delivery_zone_name']) ? ' (' . $order['delivery_zone_name'] . ')' : '' ?>: <?= $formatAmount($order['delivery_fee']) ?>
Final payable: <?= $formatAmount($order['grand_total']) ?>

DELIVERY
<?= $order['customer_name'] ?>
<?= $order['phone'] ?>
<?= $order['email'] ?>
<?= $order['delivery_address'] ?>
<?= $order['district'] ?><?= !empty($order['delivery_zone_name']) ? ' / ' . $order['delivery_zone_name'] : '' ?>

Payment: <?= strtoupper((string) $order['payment_method']) ?> (<?= ucwords(str_replace('_', ' ', (string) $order['payment_status'])) ?>)
Order status: <?= ucfirst((string) $order['order_status']) ?>

Thank you for choosing THE LAST NOTE BD.
<?php if ($supportEmail !== '' || $supportPhone !== ''): ?>
Customer care: <?= $supportEmail ?><?= $supportEmail !== '' && $supportPhone !== '' ? ' / ' : '' ?><?= $supportPhone ?>
<?php endif; ?>
