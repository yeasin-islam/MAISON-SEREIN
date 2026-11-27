<section class="page-hero page-hero-small checkout-page-heading">
    <span class="eyebrow">The final little details</span>
    <h1>Make it yours.</h1>
</section>
<?php if (!$cartItems): ?>
    <section class="section empty-state">
        <h2>Your bag is empty.</h2>
        <a class="button button-dark" href="<?= h(app_url('shop')) ?>">Find your fragrance</a>
    </section>
<?php else:
    $defaultAddress = current(array_filter($savedAddresses ?? [], static fn($address) => (int) $address['is_default'] === 1)) ?: ($savedAddresses[0] ?? []);
    $checkoutMoney = static fn(float|int|string|null $amount): string => '৳' . number_format((float) $amount, ((float) $amount === floor((float) $amount)) ? 0 : 2);
?>
    <section class="section checkout-section">
        <form action="api/index.php" method="post" class="checkout-layout" data-api-form data-redirect-success="<?= h(app_url('success')) ?>" data-checkout-form>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="checkout.create">
            <input type="hidden" name="idempotency_token" value="<?= h($_SESSION['checkout_token'] ?? ($_SESSION['checkout_token'] = bin2hex(random_bytes(16)))) ?>">
            <div class="checkout-details">
                <section class="checkout-block">
                    <h2><span>01</span> Your details</h2>
                    <div class="form-grid">
                        <label>Full name<input name="full_name" required minlength="2" maxlength="120" autocomplete="name" value="<?= h($defaultAddress['recipient_name'] ?? ($_SESSION['customer_name'] ?? '')) ?>"></label>
                        <label>Mobile number<input name="phone" required type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" value="<?= h($defaultAddress['phone'] ?? ($_SESSION['customer_phone'] ?? '')) ?>"></label>
                        <label class="form-span">Email address <span class="field-optional">(optional)</span><input name="email" type="email" autocomplete="email" value="<?= h($_SESSION['customer_email'] ?? '') ?>"></label>
                    </div>
                </section>
                <section class="checkout-block">
                    <h2><span>02</span> Delivery</h2>
                    <div class="form-grid">
                        <?php if (!empty($savedAddresses)): ?>
                            <div class="saved-addresses" role="group" aria-label="Saved delivery addresses">
                                <span class="field-label">Use a saved address</span>
                                <?php foreach ($savedAddresses as $savedAddress): ?>
                                    <button type="button" class="saved-address-button" data-saved-address data-name="<?= h($savedAddress['recipient_name']) ?>" data-phone="<?= h($savedAddress['phone']) ?>" data-district="<?= h($savedAddress['district']) ?>" data-address="<?= h($savedAddress['address']) ?>" aria-pressed="<?= (int) $savedAddress['is_default'] ? 'true' : 'false' ?>"><?= h($savedAddress['label']) ?> · <?= h($savedAddress['district']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <label>Area
                            <select name="delivery_zone_id" required>
                                <option value="">Choose a delivery area</option>
                                <?php foreach ($deliveryZones as $zone): ?>
                                    <option value="<?= (int) $zone['id'] ?>"><?= h($zone['name']) ?> · <?= h($checkoutMoney($zone['delivery_fee'])) ?> (<?= h($zone['estimated_days']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>District / city<input name="district" required maxlength="120" autocomplete="address-level2" placeholder="e.g. Gulshan" value="<?= h($defaultAddress['district'] ?? '') ?>"></label>
                        <label class="form-span">Full delivery address<textarea name="address" required minlength="8" maxlength="500" rows="3" autocomplete="street-address" placeholder="House, road, area, and any helpful directions"><?= h($defaultAddress['address'] ?? '') ?></textarea></label>
                        <label class="form-span">A note for us <span class="field-optional">(optional)</span><input name="order_note" maxlength="300" placeholder="Anything we should know?"></label>
                    </div>
                </section>
                <section class="checkout-block">
                    <h2><span>03</span> Payment</h2>
                    <p class="checkout-helper">Choose an available mobile payment method or pay when your order arrives.</p>
                    <?php $acceptedMethods = json_decode((string) ($settings['accepted_payment_methods'] ?? '["bkash","nagad","cod"]'), true) ?: ['bkash', 'nagad', 'cod']; ?>
                    <div class="payment-methods">
                        <?php if (in_array('bkash', $acceptedMethods, true)): ?>
                            <label><input type="radio" name="payment_method" value="bkash" required><span><strong>bKash</strong><small><?= h($settings['bkash_instructions'] ?? 'Send the total to our bKash number, then enter the transaction ID.') ?></small><em><?= h($settings['bkash_number'] ?? '01XXXXXXXXX') ?></em></span></label>
                        <?php endif; ?>
                        <?php if (in_array('nagad', $acceptedMethods, true)): ?>
                            <label><input type="radio" name="payment_method" value="nagad" required><span><strong>Nagad</strong><small><?= h($settings['nagad_instructions'] ?? 'Send the total to our Nagad number, then enter the transaction ID.') ?></small><em><?= h($settings['nagad_number'] ?? '01XXXXXXXXX') ?></em></span></label>
                        <?php endif; ?>
                        <?php if (in_array('cod', $acceptedMethods, true)): ?>
                            <label><input type="radio" name="payment_method" value="cod" required><span><strong>Cash on delivery</strong><small>Pay in cash when your order arrives.</small></span></label>
                        <?php endif; ?>
                    </div>
                    <?php if (array_intersect($acceptedMethods, ['bkash', 'nagad'])): ?>
                        <label class="transaction-field" data-transaction-field>Mobile payment transaction ID<input name="transaction_id" maxlength="100" placeholder="Enter the transaction ID" autocomplete="off"></label>
                    <?php endif; ?>
                </section>
                <?php if (($settings['gift_mode_enabled'] ?? '1') === '1'): ?>
                    <section class="checkout-block">
                        <h2><span>04</span> A thoughtful touch</h2>
                        <label class="gift-check"><input type="checkbox" name="gift_wrap" value="1"><span>Add complimentary gift wrap</span></label>
                        <label class="form-label">Gift message <span class="field-optional">(optional)</span><textarea name="gift_message" maxlength="500" rows="2" placeholder="Write a little note…"></textarea></label>
                    </section>
                <?php endif; ?>
                <section class="checkout-block checkout-offer-block">
                    <h2><span><?php if (($settings['gift_mode_enabled'] ?? '1') !== '1'): ?>04<?php else: ?>05<?php endif; ?></span> Offers</h2>
                    <label class="form-label" for="checkout-discount-code">Offer code</label>
                    <div class="checkout-coupon-row">
                        <input id="checkout-discount-code" name="discount_code" maxlength="40" autocomplete="off" placeholder="Enter code" value="<?= h($_SESSION['discount_code'] ?? '') ?>">
                        <button class="button button-outline" type="button" data-apply-coupon>Apply</button>
                    </div>
                    <div class="checkout-coupon-status" aria-live="polite">
                        <span data-coupon-code><?= !empty($_SESSION['discount_code']) ? 'Offer code saved; validating…' : 'No offer code applied' ?></span>
                        <button class="remove-link" type="button" data-remove-coupon hidden>Remove</button>
                    </div>
                    <p class="form-message" data-coupon-message aria-live="polite"></p>
                </section>
                <p class="form-message" aria-live="polite"></p>
                <button class="button button-dark button-wide checkout-submit" type="submit">Place order <span aria-hidden="true">→</span></button>
                <p class="checkout-terms">By placing your order, you agree to our <a href="<?= h(app_url('contact')) ?>#delivery">delivery and returns policy</a>.</p>
            </div>
            <aside class="order-summary checkout-summary" aria-labelledby="checkout-summary-title">
                <span class="eyebrow">A considered selection</span>
                <h2 id="checkout-summary-title">Order summary</h2>
                <div class="checkout-items" data-checkout-items>
                    <?php foreach ($cartItems as $item): ?>
                        <article class="summary-product">
                            <img src="<?= h($item['image_path'] ?? 'assets/images/amber-veil.png') ?>" alt="<?= h($item['name']) ?>">
                            <div class="summary-product-details">
                                <strong><?= h($item['name']) ?></strong>
                                <small><?= h($item['size_label']) ?> · Qty <?= (int) $item['quantity'] ?></small>
                                <span class="summary-unit-price">
                                    <?php if ((float) $item['price'] > (float) ($item['offer_price'] ?? $item['price'])): ?>
                                        <del><?= h($checkoutMoney($item['price'])) ?></del>
                                    <?php endif; ?>
                                    <span><?= h($checkoutMoney($item['offer_price'] ?? $item['price'])) ?> each</span>
                                </span>
                            </div>
                            <strong class="summary-product-total"><?= h($checkoutMoney($item['line_total'])) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="summary-breakdown">
                    <div><span>Product subtotal</span><strong data-checkout-subtotal><?= h($checkoutMoney(array_sum(array_column($cartItems, 'line_total')))) ?></strong></div>
                    <div><span>Product discount</span><strong data-product-discount>৳0</strong></div>
                    <div><span>Coupon discount</span><strong data-coupon-discount>৳0</strong></div>
                    <div class="delivery-zone-line"><span>Delivery <small data-delivery-zone>Choose an area to see the charge</small></span><strong data-delivery-charge>৳0</strong></div>
                    <div class="summary-total"><span>Grand total</span><strong data-grand-total><?= h($checkoutMoney(array_sum(array_column($cartItems, 'line_total')))) ?></strong></div>
                </div>
                <p class="checkout-summary-note">Final amount is confirmed securely when your order is placed.</p>
            </aside>
        </form>
    </section>
<?php endif; ?>
