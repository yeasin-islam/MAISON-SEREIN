<section class="page-hero page-hero-small"><span class="eyebrow">We’ll find its way</span>
    <h1>Track your order.</h1>
    <p>Enter the order number and mobile number used at checkout.</p>
</section>
<section class="section tracking-section">
    <form action="api/index.php" method="post" class="tracking-form" data-api-form data-result-container="#track-result"><?= csrf_field() ?><input type="hidden" name="action" value="track.lookup"><label>Order number<input name="order_number" required maxlength="32" placeholder="MS-260101-ABC123"></label><label>Mobile number<input name="phone" required type="tel" inputmode="tel" placeholder="01XXXXXXXXX"></label><button class="button button-dark" type="submit">Find my order <span aria-hidden="true">→</span></button>
        <p class="form-message" aria-live="polite"></p>
    </form>
    <div id="track-result" class="track-result" aria-live="polite"></div>
</section>