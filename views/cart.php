<?php $whatsApp = preg_replace('/\\D+/', '', (string) ($settings['whatsapp_number'] ?? ''));
$hasWhatsApp = strlen($whatsApp) >= 10 && strlen($whatsApp) <= 15; ?>
<section class="page-hero page-hero-small"><span class="eyebrow">A little anticipation</span>
    <h1>Your bag.</h1>
</section>
<section class="section cart-section"><?php if (!$cartItems): ?><div class="empty-state">
            <h2>Your bag is waiting.</h2>
            <p>Take a moment to find something that feels like you.</p><a class="button button-dark" href="<?= h(app_url('shop')) ?>">Explore fragrance</a>
        </div><?php else: $subtotal = array_sum(array_column($cartItems, 'line_total')); ?>
        <div class="cart-layout">
            <div class="cart-items">
                <div class="cart-table-head"><span>Fragrance</span><span>Size / quantity</span><span>Total</span></div><?php foreach ($cartItems as $item): $isPreorder = (int) $item['quantity'] > (int) $item['stock_quantity'] && (int) $item['preorder_available']; ?><article class="cart-item"><a class="cart-item-image" href="<?= h(app_url('product', ['id' => $item['product_id']])) ?>"><img src="<?= h($item['image_path'] ?? 'assets/images/amber-veil.png') ?>" alt="<?= h($item['name']) ?>"></a>
                        <div class="cart-item-info"><a href="<?= h(app_url('product', ['id' => $item['product_id']])) ?>"><strong><?= h($item['name']) ?></strong></a><span><?= h($item['size_label']) ?> · <?= money($item['price']) ?> each</span><?php if ($isPreorder): ?><span class="preorder-note">Pre-order · ships after release</span><?php endif; ?><form action="api/index.php" method="post" data-api-form data-success="Removed from your bag."><?= csrf_field() ?><input type="hidden" name="action" value="cart.remove"><input type="hidden" name="variant_id" value="<?= (int) $item['variant_id'] ?>"><button class="remove-link" type="submit">Remove</button></form>
                        </div>
                        <div class="cart-controls">
                            <form class="cart-variant-form" action="api/index.php" method="post" data-api-form data-success="Size updated."><?= csrf_field() ?><input type="hidden" name="action" value="cart.change-variant"><input type="hidden" name="old_variant_id" value="<?= (int) $item['variant_id'] ?>"><label class="sr-only" for="variant-<?= (int) $item['variant_id'] ?>">Choose size for <?= h($item['name']) ?></label><select id="variant-<?= (int) $item['variant_id'] ?>" name="variant_id" onchange="this.form.requestSubmit()">
                                    <option value="<?= (int) $item['variant_id'] ?>" selected><?= h($item['size_label']) ?></option><?php foreach ($item['variants'] as $variant): if ((int) $variant['id'] === (int) $item['variant_id']) continue;
                                                                                                                                        $canPreorder = (int) $variant['preorder_available'];
                                                                                                                                        if ((int) $variant['stock_quantity'] < (int) $item['quantity'] && !$canPreorder) continue; ?><option value="<?= (int) $variant['id'] ?>"><?= h($variant['size_label']) ?><?= (int) $variant['stock_quantity'] > 0 ? '' : ' · Pre-order' ?></option><?php endforeach; ?>
                                </select></form>
                            <form class="cart-quantity" action="api/index.php" method="post" data-api-form data-success="Bag updated."><?= csrf_field() ?><input type="hidden" name="action" value="cart.update"><input type="hidden" name="variant_id" value="<?= (int) $item['variant_id'] ?>"><label class="sr-only" for="quantity-<?= (int) $item['variant_id'] ?>">Quantity for <?= h($item['name']) ?></label><input id="quantity-<?= (int) $item['variant_id'] ?>" type="number" name="quantity" min="1" max="10" value="<?= (int) $item['quantity'] ?>" onchange="this.form.requestSubmit()"></form>
                        </div><strong class="cart-item-total"><?= money($item['line_total']) ?></strong>
                    </article><?php endforeach; ?><a class="text-link continue-shopping" href="<?= h(app_url('shop')) ?>">← Continue browsing</a>
            </div>
            <aside class="order-summary">
                <h2>Order summary</h2>
                <div><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div>
                <p>Delivery and any offer are confirmed at checkout.</p>
                <form class="discount-form" action="api/index.php" method="post" data-api-form data-success="Offer code saved."><?= csrf_field() ?><input type="hidden" name="action" value="discount.apply"><label for="cart-discount-code">Offer code</label>
                    <div class="discount-input"><input id="cart-discount-code" name="discount_code" maxlength="40" autocomplete="off" placeholder="Enter code" value="<?= h($_SESSION['discount_code'] ?? '') ?>" required><button class="button button-outline" type="submit">Apply</button></div>
                    <p class="form-message" aria-live="polite"></p>
                </form><?php if (!empty($_SESSION['discount_code'])): ?><p class="coupon-saved">Offer <?= h($_SESSION['discount_code']) ?> saved. Eligibility is checked at checkout.</p>
                    <form action="api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="discount.remove"><button class="remove-link" type="submit">Remove offer</button></form><?php endif; ?><a class="button button-dark button-wide" href="<?= h(app_url('checkout')) ?>">Continue to checkout <span aria-hidden="true">→</span></a><?php if ($hasWhatsApp): $whatsAppItems = array_map(static fn(array $item): array => ['product' => (string) $item['name'], 'size' => (string) $item['size_label'], 'quantity' => (int) $item['quantity'], 'unit_price' => (float) $item['price'], 'line_total' => (float) $item['line_total']], $cartItems);
                                                                                                                                                                                                                                                                                                                                                                                                $whatsAppLines = array_map(static fn(array $item): string => $item['product'] . ' — ' . $item['size'] . ' × ' . $item['quantity'] . ' · BDT ' . number_format($item['unit_price'], 0, '.', ',') . ' each · BDT ' . number_format($item['line_total'], 0, '.', ','), $whatsAppItems);
                                                                                                                                                                                                                                                                                                                                                                                                $whatsAppMessage = implode("\n", array_merge(['Hello, I have a question about this bag. This is an inquiry only, not a confirmed order.', 'Bag summary:'], $whatsAppLines, ['Bag subtotal: BDT ' . number_format($subtotal, 0, '.', ','), 'Delivery, discounts, and the confirmed total are calculated at website checkout.'])); ?><a class="button button-outline button-wide" data-whatsapp-cart data-whatsapp-number="<?= h($whatsApp) ?>" href="https://wa.me/<?= h($whatsApp) ?>?text=<?= rawurlencode($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Ask about this bag on WhatsApp <span aria-hidden="true">↗</span></a>
                    <p class="secure-note">This sends an inquiry only. Complete website checkout to place and confirm your order.</p>
                    <script type="application/json" id="whatsapp-cart-data">
                        <?= json_encode($whatsAppItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
                    </script><?php endif; ?><span class="secure-note">Thoughtfully packed, delivered with care.</span>
            </aside>
        </div><?php endif; ?>
</section>