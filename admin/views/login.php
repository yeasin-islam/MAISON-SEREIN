<?php require_once dirname(__DIR__, 2) . '/includes/bootstrap.php'; ?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f2ed">
  <title>Studio sign in — THE LAST NOTE BD</title>
  <link rel="icon" href="../assets/images/the-last-note-logo.svg" type="image/svg+xml">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/store.css?v=2">
  <link rel="stylesheet" href="../assets/css/store-interactions.css">
  <link rel="stylesheet" href="../assets/css/store-redesign.css?v=19">
  <link rel="stylesheet" href="../assets/css/store-account.css">
  <link rel="stylesheet" href="../assets/css/store-extras.css">
</head>

<body class="admin-login-page">
  <main class="admin-login-wrap"><a class="wordmark brand-lockup" href="../index.php" aria-label="THE LAST NOTE BD admin">
      <img class="brand-logo" src="../assets/images/the-last-note-logo.svg" alt="" width="44" height="44">
      <span class="brand-copy"><span class="brand-name"><span>THE LAST</span><span>NOTE BD</span></span><small>AUTHENTIC FRAGRANCE DECANT HOUSE</small></span>
    </a>
    <div class="admin-login-card"><span class="eyebrow">The house, behind the scenes</span>
      <h1>Welcome in.</h1>
      <p>Sign in to manage your store.</p>
      <form action="../api/index.php" method="post" data-api-form><?= csrf_field() ?><input type="hidden" name="action" value="admin.login"><label>Email address<input name="email" type="email" required autocomplete="username"></label><label>Password<input name="password" type="password" required autocomplete="current-password"></label>
        <p class="form-message" aria-live="polite"></p><button class="button button-dark button-wide" type="submit">Enter the studio →</button>
      </form><a class="text-link" href="../index.php">← Return to the storefront</a>
    </div>
    <script src="../assets/js/store.js" defer></script>
  </main>
</body>

</html>