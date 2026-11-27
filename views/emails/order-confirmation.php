<?php
$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$formatAmount = static fn($value): string => '৳' . number_format((float) $value, 2);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation — Maison Serein</title>
</head>

<body style="margin:0;padding:0;background:#f4f1eb;color:#27251f;font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your Maison Serein order <?= $escape($order['order_number']) ?> is confirmed.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f1eb;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:640px;background:#fff;">
                    <tr>
                        <td align="center" style="padding:34px 24px 26px;background:#28271f;color:#fff;">
                            <div style="font-family:Georgia,'Times New Roman',serif;font-size:19px;letter-spacing:4px;">MAISON SEREIN</div>
                            <div style="margin-top:8px;color:#d4c8ad;font-size:10px;letter-spacing:3px;">A QUIETER KIND OF LUXURY</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 34px 16px;">
                            <p style="margin:0 0 8px;color:#8b7654;font-size:11px;letter-spacing:2px;text-transform:uppercase;">Order confirmed</p>
                            <h1 style="margin:0 0 12px;font-family:Georgia,'Times New Roman',serif;font-size:29px;font-weight:400;">Thank you, <?= $escape($order['customer_name']) ?>.</h1>
                            <p style="margin:0;color:#666;font-size:14px;line-height:1.7;">Your order is safely with us. We’ll take care of the details and prepare your fragrance.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 34px 24px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8f6f2;">
                                <tr>
                                    <td style="padding:16px;border-bottom:1px solid #e8e3da;color:#666;font-size:13px;">Order ID / reference</td>
                                    <td align="right" style="padding:16px;border-bottom:1px solid #e8e3da;font-size:13px;font-weight:bold;"><?= (int) $order['id'] ?> / <?= $escape($order['order_number']) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;color:#666;font-size:13px;">Order date</td>
                                    <td align="right" style="padding:14px 16px;font-size:13px;"><?= $escape(date('j M Y, H:i', strtotime((string) $order['created_at']))) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 34px 10px;">
                            <h2 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:400;">Your selection</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td style="padding:14px 0;border-top:1px solid #e8e3da;width:74px;">
                                            <?php if (!empty($item['image_url'])): ?><img src="<?= $escape($item['image_url']) ?>" width="60" height="70" alt="<?= $escape($item['product_name']) ?>" style="display:block;width:60px;height:70px;object-fit:cover;border:0;"><?php endif; ?>
                                        </td>
                                        <td style="padding:14px 8px;border-top:1px solid #e8e3da;font-size:13px;line-height:1.6;">
                                            <strong><?= $escape($item['product_name']) ?></strong><br>
                                            <?= $escape($item['size_label']) ?> · Qty <?= (int) $item['quantity'] ?><br>
                                            Unit price <?= $formatAmount($item['unit_price']) ?>
                                        </td>
                                        <td align="right" style="padding:14px 0;border-top:1px solid #e8e3da;font-size:13px;white-space:nowrap;"><?= $formatAmount($item['line_total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:10px 34px 28px;">
                            <h2 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:400;">Payment summary</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="font-size:13px;">
                                <tr>
                                    <td style="padding:7px 0;color:#666;">Product subtotal</td>
                                    <td align="right" style="padding:7px 0;"><?= $formatAmount($order['subtotal']) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0;color:#666;">Product discount</td>
                                    <td align="right" style="padding:7px 0;"><?= $formatAmount($order['product_discount']) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0;color:#666;">Coupon discount<?= !empty($order['coupon_code']) ? ' (' . $escape($order['coupon_code']) . ')' : '' ?></td>
                                    <td align="right" style="padding:7px 0;">−<?= $formatAmount($order['discount_amount']) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:7px 0;color:#666;">Delivery<?= !empty($order['delivery_zone_name']) ? ' · ' . $escape($order['delivery_zone_name']) : '' ?></td>
                                    <td align="right" style="padding:7px 0;"><?= $formatAmount($order['delivery_fee']) ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 0 0;border-top:1px solid #dcd5c9;font-size:15px;font-weight:bold;">Final payable</td>
                                    <td align="right" style="padding:13px 0 0;border-top:1px solid #dcd5c9;font-size:15px;font-weight:bold;"><?= $formatAmount($order['grand_total']) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 34px;background:#f8f6f2;">
                            <h2 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;font-size:20px;font-weight:400;">Delivery & order details</h2>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.7;"><strong><?= $escape($order['customer_name']) ?></strong><br><?= $escape($order['phone']) ?><br><?= $escape($order['email']) ?><br><?= $escape($order['delivery_address']) ?><br><?= $escape($order['district']) ?><?= !empty($order['delivery_zone_name']) ? ' · ' . $escape($order['delivery_zone_name']) : '' ?></p>
                            <p style="margin:14px 0 0;color:#666;font-size:13px;line-height:1.7;">Payment: <?= $escape(strtoupper((string) $order['payment_method'])) ?> (<?= $escape(ucwords(str_replace('_', ' ', (string) $order['payment_status']))) ?>)<br>Order status: <?= $escape(ucfirst((string) $order['order_status'])) ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:27px 28px 32px;color:#777;font-size:12px;line-height:1.8;">
                            <strong style="color:#28271f;font-family:Georgia,'Times New Roman',serif;letter-spacing:2px;">MAISON SEREIN</strong><br>
                            Thank you for choosing us. We hope your new fragrance becomes part of a beautiful ritual.
                            <?php if ($supportEmail !== '' || $supportPhone !== ''): ?><br>Customer care: <?= $supportEmail !== '' ? $escape($supportEmail) : '' ?><?= $supportEmail !== '' && $supportPhone !== '' ? ' · ' : '' ?><?= $supportPhone !== '' ? $escape($supportPhone) : '' ?><?php endif; ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>