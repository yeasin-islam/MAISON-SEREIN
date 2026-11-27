<section class="section auth-section">
    <div class="auth-intro"><span class="eyebrow">A little more personal</span>
        <h1>Make yourself at home.</h1>
        <p>Create an account to keep your orders, details, and favourite fragrances together.</p>
    </div>
    <form class="auth-card" action="api/index.php" method="post" data-api-form data-redirect-success="<?= h(app_url('account')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="customer.register"><label>Full name<input name="full_name" required minlength="2" maxlength="120" autocomplete="name"></label><label>Email address<input name="email" required type="email" autocomplete="email"></label><label>Mobile number<input name="phone" required type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX"></label><label>Password<input name="password" required type="password" minlength="10" autocomplete="new-password"><small>At least 10 characters.</small></label>
        <p class="form-message" aria-live="polite"></p><button class="button button-dark button-wide" type="submit">Create account <span aria-hidden="true">→</span></button>
        <p class="auth-switch">Already have an account? <a href="<?= h(app_url('login')) ?>">Sign in</a></p>
    </form>
</section>