<section class="admin-panel">
    <div class="admin-panel-heading">
        <div><span class="eyebrow">Customer notes</span>
            <h2>Review moderation</h2>
        </div><span><?= count($reviews) ?> notes</span>
    </div><?php if ($reviews): ?><div class="admin-reviews"><?php foreach ($reviews as $review): ?><article class="admin-review">
                    <div class="review-meta"><strong><?= h($review['customer_name']) ?></strong><span class="stars" aria-label="<?= (int) $review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int) $review['rating']) ?></span><span><?= h($review['product_name']) ?></span><span class="status-pill status-<?= h($review['status']) ?>"><?= h(ucfirst($review['status'])) ?></span></div><?php if ($review['review_title']): ?><h3><?= h($review['review_title']) ?></h3><?php endif; ?><p><?= h($review['review_body']) ?></p>
                    <form class="admin-inline-form" action="../api/index.php" method="post" data-api-form data-success="Review moderation saved."><?= csrf_field() ?><input type="hidden" name="action" value="admin.review.moderate"><input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>"><button class="table-action" name="status" value="approved">Approve</button><button class="table-action table-action-muted" name="status" value="rejected">Reject</button><button class="table-action table-action-muted" name="status" value="hidden">Hide</button><label class="admin-check"><input type="checkbox" name="featured" value="1" <?= $review['is_featured'] ? 'checked' : '' ?>> Feature</label>
                        <p class="form-message" aria-live="polite"></p>
                    </form>
                </article><?php endforeach; ?></div><?php else: ?><div class="admin-empty">There are no customer notes to review yet.</div><?php endif; ?>
</section>