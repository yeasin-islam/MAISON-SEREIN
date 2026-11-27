<section class="section auth-section">
    <div class="auth-intro"><span class="eyebrow">Good to see you again</span>
        <h1>Welcome back.</h1>
        <p>Sign in to revisit your orders and saved fragrances.</p>
    </div>
    <form class="auth-card" action="api/index.php" method="post" data-api-form data-redirect-success="<?= h(app_url('account')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="customer.login"><label>Email or mobile number<input name="identifier" required autocomplete="username" placeholder="you@example.com"></label><label>Password<input name="password" type="password" required autocomplete="current-password"></label>
        <p class="form-message" aria-live="polite"></p><button class="button button-dark button-wide" type="submit">Sign in <span aria-hidden="true">→</span></button>
        <p class="auth-switch">New to the house? <a href="<?= h(app_url('register')) ?>">Create an account</a></p>
    </form>
</section>