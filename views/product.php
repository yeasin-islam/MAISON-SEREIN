<?php if (!$product): ?><section class="section empty-state">
        <h1>We couldn’t find that fragrance.</h1>
        <p>It may have moved to another chapter.</p><a class="button button-dark" href="<?= h(app_url('shop')) ?>">Browse the collection</a>
    </section><?php else:
                $mainImage = $product['images'][0]['image_path'] ?? 'assets/images/amber-veil.png';
                $preorderAvailable = (int) ($product['preorder_available'] ?? 0) === 1;
                $availableVariants = array_values(array_filter($product['variants'], static fn($variant) => (int) $variant['stock_quantity'] > 0 || $preorderAvailable));
                $selectedVariant = $availableVariants[0] ?? ($product['variants'][0] ?? null);
                $whatsApp = preg_replace('/\\D+/', '', (string) ($settings['whatsapp_number'] ?? ''));
                $hasWhatsApp = strlen($whatsApp) >= 10 && strlen($whatsApp) <= 15;
                ?>
    <section class="section product-detail">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= h(app_url('home')) ?>">Home</a><span aria-hidden="true">/</span><a href="<?= h(app_url('shop')) ?>">Fragrance</a><span aria-hidden="true">/</span><span><?= h($product['name']) ?></span></nav>
        <div class="product-detail-grid">
            <div class="product-gallery">
                <div class="product-detail-image"><img data-main-product-image src="<?= h($mainImage) ?>" alt="<?= h($product['name']) ?> fragrance bottle"></div><?php if (count($product['images']) > 1): ?><div class="product-thumbnails" aria-label="Product gallery"><?php foreach ($product['images'] as $index => $image): ?><button class="gallery-thumb" type="button" data-gallery-image aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="View image <?= $index + 1 ?> of <?= count($product['images']) ?>"><img src="<?= h($image['image_path']) ?>" alt="<?= h($image['alt_text'] ?: $product['name']) ?>" loading="lazy"></button><?php endforeach; ?></div><?php endif; ?>
            </div>
            <div class="product-detail-copy"><span class="eyebrow"><?= h($product['brand_name']) ?> · <?= h($product['fragrance_family'] ?? 'Fine fragrance') ?></span>
                <h1><?= h($product['name']) ?></h1>
                <p class="product-deck"><?= h($product['short_description']) ?></p><?php if ((int) $product['review_count'] > 0): ?><p class="rating-line"><span class="stars" aria-hidden="true">★★★★★</span> <?= number_format((float) $product['average_rating'], 1) ?> <a href="#reviews">(<?= (int) $product['review_count'] ?> notes)</a></p><?php endif; ?>
                <p class="detail-price" data-price><?= $selectedVariant ? money($selectedVariant['price']) : 'Currently unavailable' ?></p>
                <form action="api/index.php" method="post" data-api-form data-success="Added to your bag."><?= csrf_field() ?><input type="hidden" name="action" value="cart.add"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <div class="option-label"><span>Choose a size</span><a href="#scent-notes">Explore the notes</a></div>
                    <div class="size-options"><?php foreach ($product['variants'] as $variant): ?><label class="size-option"><input type="radio" name="variant_id" value="<?= (int) $variant['id'] ?>" data-price-value="<?= (float) $variant['price'] ?>" data-size-label="<?= h($variant['size_label']) ?>" data-stock-value="<?= (int) $variant['stock_quantity'] ?>" data-preorder="<?= $preorderAvailable ? '1' : '0' ?>" <?= $selectedVariant && (int) $selectedVariant['id'] === (int) $variant['id'] ? 'checked' : '' ?> <?= (int) $variant['stock_quantity'] <= 0 && !$preorderAvailable ? 'disabled' : '' ?>><span><?= h($variant['size_label']) ?> · <?= money($variant['price']) ?><?= (int) $variant['stock_quantity'] <= 0 ? ($preorderAvailable ? ' · Pre-order' : ' · Sold out') : '' ?></span></label><?php endforeach; ?></div>
                    <?php if ($availableVariants): ?><div class="purchase-row"><label class="sr-only" for="quantity">Quantity</label><input class="quantity-input" id="quantity" type="number" name="quantity" min="1" max="<?= max(1, min(10, (int) $selectedVariant['stock_quantity'] ?: 10)) ?>" value="1"><button class="button button-dark button-wide" type="submit"><?= (int) $selectedVariant['stock_quantity'] > 0 ? 'Add to bag' : 'Pre-order and add to bag' ?> <span aria-hidden="true">→</span></button></div>
                        <p class="form-message" aria-live="polite"></p><?php else: ?><p class="out-of-stock">A little pause — currently out of stock.</p><?php endif; ?>
                </form>
                <div class="product-trust"><span>Thoughtfully packed</span><span>Delivery across Bangladesh</span><span>Secure checkout</span></div>
                <div class="product-description">
                    <h2>The feeling</h2>
                    <p><?= nl2br(h($product['description'])) ?></p>
                </div>
                <?php if ($hasWhatsApp): $selectedSizeLabel = (string) ($selectedVariant['size_label'] ?? 'To be confirmed');
                    $selectedPrice = (float) ($selectedVariant['price'] ?? 0);
                    $whatsAppMessage = implode("\n", ['Hello, I’m interested in this fragrance. This is an inquiry only, not a confirmed order.', 'Product: ' . $product['name'], 'Size: ' . $selectedSizeLabel, 'Quantity: 1', 'Unit price: BDT ' . number_format($selectedPrice, 0, '.', ','), 'Item total: BDT ' . number_format($selectedPrice, 0, '.', ',')]); ?><a class="whatsapp-link" data-whatsapp-link data-whatsapp-number="<?= h($whatsApp) ?>" data-product-name="<?= h($product['name']) ?>" data-selected-size="<?= h($selectedSizeLabel) ?>" data-selected-price="<?= h((string) $selectedPrice) ?>" data-whatsapp-quantity="#quantity" href="https://wa.me/<?= h($whatsApp) ?>?text=<?= rawurlencode($whatsAppMessage) ?>" target="_blank" rel="noopener noreferrer">Ask about this fragrance on WhatsApp <span aria-hidden="true">↗</span></a>
                    <p class="secure-note">WhatsApp messages are inquiries only. Place your order through website checkout.</p><?php endif; ?>
                <div id="scent-notes" class="note-accordions">
                    <details open>
                        <summary>Opening notes</summary>
                        <p><?= h($product['top_notes'] ?? 'To be discovered') ?></p>
                    </details>
                    <details>
                        <summary>At the heart</summary>
                        <p><?= h($product['heart_notes'] ?? 'To be discovered') ?></p>
                    </details>
                    <details>
                        <summary>The lasting trail</summary>
                        <p><?= h($product['base_notes'] ?? 'To be discovered') ?></p>
                    </details>
                </div>
                <?php $productSaved = in_array((int) $product['id'], $wishlistIds ?? [], true); ?>
                <form class="product-wishlist-form" action="api/index.php" method="post" data-api-form data-success="Saved to your wishlist."><?= csrf_field() ?><input type="hidden" name="action" value="wishlist.toggle"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><button class="wishlist-button wishlist-button-detail<?= $productSaved ? ' is-saved' : '' ?>" type="submit" data-wishlist-button data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= h($product['name']) ?>" aria-label="<?= $productSaved ? 'Remove ' : 'Add ' ?><?= h($product['name']) ?> <?= $productSaved ? 'from' : 'to' ?> wishlist" title="<?= $productSaved ? 'Remove from wishlist' : 'Add to wishlist' ?>" aria-pressed="<?= $productSaved ? 'true' : 'false' ?>"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z" />
                        </svg></button>
                    <p class="form-message" aria-live="polite"></p>
                </form>
            </div>
        </div>
    </section>
    <?php if (!empty($product['reviews'])): ?><section class="section product-reviews" id="reviews">
            <div class="section-heading">
                <div><span class="eyebrow">Worn and remembered</span>
                    <h2>Notes on <?= h($product['name']) ?></h2>
                </div>
            </div>
            <div class="review-grid"><?php foreach ($product['reviews'] as $review): ?><article class="review-card">
                        <div class="stars" aria-label="<?= (int) $review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int) $review['rating']) ?><?= str_repeat('☆', 5 - (int) $review['rating']) ?></div><?php if ($review['review_title']): ?><h3><?= h($review['review_title']) ?></h3><?php endif; ?><blockquote><?= h($review['review_body']) ?></blockquote><span><?= h($review['customer_name']) ?></span>
                    </article><?php endforeach; ?></div>
        </section><?php endif; ?>
    <?php if (!empty($relatedProducts)): ?><section class="section product-section">
            <div class="section-heading">
                <div><span class="eyebrow">More to discover</span>
                    <h2>Stay a little longer</h2>
                </div>
            </div>
            <div class="product-grid"><?php foreach ($relatedProducts as $item) require __DIR__ . '/product-card.php'; ?></div>
        </section><?php endif; ?>
<?php endif; ?>