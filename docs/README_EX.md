# THE LAST NOTE BD — Perfume E-Commerce

THE LAST NOTE BD is a server-rendered perfume storefront and staff studio built for Core PHP and MySQL/MariaDB. It supports guest shopping and checkout, optional customer accounts, order tracking, and administrator-managed catalogue and operations. It runs locally on XAMPP without a JavaScript framework or frontend build step.

## Contents

- [Features](#features)
- [Technology and requirements](#technology-and-requirements)
- [Quick start](#quick-start)
- [Email configuration](#order-confirmation-email)
- [Project structure](#project-structure)
- [Validation and testing](#validation-and-testing)
- [Security and operational notes](#security-and-operational-notes)
- [Further documentation](#further-documentation)

## Features

### Storefront and shopping

- Searchable fragrance catalogue with category, brand, price, fragrance, availability, and other supported filters; sorting and pagination are handled by the existing server-side catalogue service.
- Product details include fragrance notes, available sizes, stock information, reviews, and product images.
- Session-based guest cart and wishlist, with optional signed-in customer accounts, saved addresses, and order history.
- Responsive Shop All toolbar and filter controls, including the mobile filter drawer.
- WhatsApp product and bag inquiries with selected sizes, quantities, and totals. An inquiry is not an order; customers must complete checkout to place one.

### Checkout and order management

- Server-validated checkout recalculates product prices, coupon eligibility, delivery charges, and totals from the database.
- Orders, payment records, inventory changes, coupon usage, and order-item price snapshots are written using a database transaction and stock locking.
- Delivery-zone selection updates the quote; coupons support the rules configured in the database, including expiry, minimum purchase, usage limits, product scope, and first-order eligibility.
- bKash/Nagad transaction ID submission for staff verification, plus cash on delivery. The website does not collect card information or verify mobile-money payments through a provider API.
- Guest order tracking with the order number and matching phone number; eligible order cancellation follows the configured time window and stock-restoration rules.
- Optional order-confirmation email sent through authenticated SMTP after an order commits. Email attempts are recorded and failed sends can be retried by staff.

### Staff studio

- Password-protected administration for products, sizes, images, stock, categories, brands, customers, orders, payments, reviews, and reports.
- Store settings for delivery zones, accepted payment methods, coupons, gift options, cancellation rules, limited drops, and pre-orders.
- Homepage content management for banners, sections, and social links; dashboard metrics and CSV sales reporting.

## Technology and requirements

- PHP 8.1 or later with PDO MySQL, `mbstring`, `iconv`, `fileinfo`, GD, and OpenSSL for SMTP/TLS.
- MySQL 8+ or MariaDB 10.6+.
- Apache with `mod_rewrite` and `.htaccess` overrides enabled (XAMPP defaults).
- Composer for installing PHPMailer and its autoloader.
- HTML5, CSS3, and vanilla JavaScript in the browser. No Node.js runtime or frontend build is required.

## Quick start

For detailed XAMPP instructions and troubleshooting, see [docs/INSTALLATION.md](docs/INSTALLATION.md).

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Create an empty database named `maison_serein` with `utf8mb4_unicode_ci` collation.
3. Import [`database/perfume_ecommerce.sql`](database/perfume_ecommerce.sql) into that empty database.
4. Copy `.env.example` to `.env` in the project root. Set the database connection values and update `APP_URL` if the installation URL differs from `http://localhost/perfume-ecommerce`.
5. From the project root, install the locked PHP dependencies:

   ```text
   composer install
   ```

6. Create the first administrator from a terminal. The script prompts for credentials; do not pass the password as a command-line argument:

   ```text
   C:\xampp\php\php.exe scripts\create-admin.php
   ```

7. Open `http://localhost/perfume-ecommerce/` and sign in to the studio at `http://localhost/perfume-ecommerce/admin/index.php?page=login`.

> **Database warning:** `database/perfume_ecommerce.sql` is a fresh-install schema that drops and recreates application tables. Never import it over a database containing orders or other data. For an existing installation, back up the database and apply only the required additive migration(s), such as [`database/migrations/2026_11_27_order_email_notifications.sql`](database/migrations/2026_11_27_order_email_notifications.sql).

The base schema already creates the order-email notification table on fresh installs. Existing databases must run the migration above once before using the updated admin order screens or email-status tracking.

## Order-confirmation email

Email is optional. To enable it, install Composer dependencies and configure the following values in the private root `.env` file. Brevo SMTP uses an SMTP login and SMTP key, not the Brevo API key:

```dotenv
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-smtp-login
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME="THE LAST NOTE BD"
```

Verify the sender address or domain in Brevo. The email is sent only after the order transaction commits; an SMTP failure leaves the order intact, records a failed attempt, and is not reported to the customer as a sent email. Administrators can inspect email status and retry eligible failures in **Studio → Orders**. Product images are included only when a public HTTPS URL can be formed; configure `APP_URL` with the production HTTPS origin for deployed email images.

## Project structure

```text
admin/                 Staff studio pages and views
api/index.php          Same-origin POST actions and JSON responses
assets/css/            Storefront, responsive, and studio styles
assets/js/             Vanilla JavaScript interactions
assets/images/         Store and product imagery
assets/uploads/        Administrator-uploaded product images
config/                Environment loading and PDO connection
database/              Fresh-install schema, seed data, and migrations
docs/                  Installation, architecture, and project documentation
includes/              Bootstrap, sessions, security, layout, and helpers
scripts/               CLI administrator provisioning
services/              Catalogue, checkout, and order email services
tests/                 Database-backed installation smoke test
views/                 Customer-facing pages and email templates
index.php              Storefront entry point and page routing
composer.json          PHP dependencies
composer.lock          Locked dependency versions
```

## Validation and testing

Run the database-backed installation smoke test from the project root:

```text
C:\xampp\php\php.exe tests\smoke.php
```

It checks the database connection, expected tables, seeded catalogue, delivery zones, and configured product-image paths. A passing result requires MySQL to be running and the schema and seed data to be installed.

Lint an individual PHP file with:

```text
C:\xampp\php\php.exe -l path\to\file.php
```

For a manual end-to-end check, exercise browsing and filters, cart quantity changes, coupon application, delivery-zone changes, guest checkout, order tracking, payment review, cancellation/restock, and the admin studio. Test actual email delivery only after configuring Brevo credentials and a real recipient; a configured template or successful order alone does not prove delivery. The broader test matrix is in [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

## Security and operational notes

- Keep `.env` private. It is ignored by Git and denied by Apache; use placeholders only in `.env.example` and never put SMTP keys, passwords, or database secrets in source files or SQL seeds.
- Use a dedicated least-privilege database account outside local development; do not deploy with MySQL's default `root` account.
- Serve production traffic over HTTPS. The application uses prepared PDO statements, output escaping, CSRF checks, HTTP-only SameSite session cookies, secure cookies over HTTPS, session ID rotation, and security response headers.
- Keep PHP and Composer dependencies current. Review `composer audit` when dependencies change.
- Restrict write access to `assets/uploads/products/`. The upload directory disables listing and denies script-like files; do not enable PHP execution there.
- Mobile-money transactions are manually verified by staff; the application does not connect to bKash/Nagad APIs, initiate refunds, or process card payments.
- Deploy to a PHP/Apache host with a supported MySQL-compatible database. A static frontend preview host such as Vercel cannot run this PHP/MySQL application.

## Further documentation

- [XAMPP installation and operations](docs/INSTALLATION.md)
- [Application architecture and manual test matrix](docs/ARCHITECTURE.md)
- [Software engineering documentation](docs/PROJECT-DOCUMENTATION.md)
