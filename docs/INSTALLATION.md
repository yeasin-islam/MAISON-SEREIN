# XAMPP installation

## Requirements

- XAMPP with PHP 8.1+ and Apache/MySQL (or MariaDB 10.6+)
- PHP extensions enabled: `pdo_mysql`, `mbstring`, `iconv`, `fileinfo`, and GD (`getimagesize`)
- Apache `mod_rewrite` enabled and `.htaccess` overrides allowed for `htdocs`

## 1. Place the app

Extract the `perfume-ecommerce` folder into XAMPP's `htdocs` directory, for example:

```text
C:\xampp\htdocs\perfume-ecommerce\index.php
```

Keep the folder name `perfume-ecommerce` unless you also update your local URL. Start Apache and MySQL from the XAMPP Control Panel.

## 2. Create an empty database and import the schema

1. Open `http://localhost/phpmyadmin`.
2. Create a database named `maison_serein` with `utf8mb4_unicode_ci` collation.
3. Select that empty database, choose **Import**, and import `database/perfume_ecommerce.sql`.
4. Confirm that the seeded categories, fragrances, variants, settings, and delivery zones appear.

**The SQL import drops and recreates this app's tables.** Only import it into a new, empty database. Back up an existing database before any re-import.

For production, create a dedicated MySQL user with access only to this database; do not use `root`.

## 3. Configure database access

Copy `.env.example` to `.env` in the project root and set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` for your installation. `.env` is excluded from Git and Apache access is denied by `.htaccess`. The included `root`/empty-password values are for a local XAMPP install only.

If PHP does not read `.env`, check that the file is named exactly `.env` (not `.env.txt`) and that PHP's `parse_ini_file` support is enabled. Values in the process environment take precedence over this file.

## 4. Create the first administrator

From the project folder, run the CLI script and follow its prompts. Do not pass the password as a command-line argument.

```text
C:\xampp\php\php.exe scripts\create-admin.php
```

It hashes the password with PHP's `PASSWORD_DEFAULT`, creates an active admin, and refuses to overwrite an existing account. On Windows, console input may be visible while typing. Sign in at `http://localhost/perfume-ecommerce/admin/index.php?page=login`.

## 5. Allow product image uploads

The application stores administrator-uploaded JPG, PNG, and WebP product photography in `assets/uploads/products/`. Apache must be able to write to that folder; on XAMPP, check the folder's permissions in your operating system if uploads cannot be saved. The folder contains an `.htaccess` rule that disables directory listing and denies script-like files. Do not enable PHP execution in upload directories.

## 6. Verify the setup

Run the install smoke test from the project folder:

```text
C:\xampp\php\php.exe tests\smoke.php
```

Then visit `http://localhost/perfume-ecommerce/`. Test a product size, add it to the bag, place a COD order, and track it using its order number plus the same phone number. For bKash/Nagad demo orders, enter a transaction ID and verify it from **Studio → Orders**.

## 7. Configure order-confirmation email (optional)

Install the locked PHP dependencies from the project root:

```text
composer install
```

In Brevo, enable SMTP access and create an SMTP key (this is not the Brevo API key). Add the values to the private root `.env` file:

```dotenv
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-smtp-login
MAIL_PASSWORD=your-brevo-smtp-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=verified-sender@example.com
MAIL_FROM_NAME="THE LAST NOTE BD"
```

Verify the sender address/domain in Brevo before testing. Keep SMTP credentials only in `.env`; do not commit them or enter them in the browser. Order email delivery happens after the order transaction commits. A delivery failure leaves the order confirmed, is recorded in Studio → Orders, and can be retried by an administrator. The local demo URL uses HTTP, so product images are omitted from emails unless `APP_URL` is configured with a public HTTPS origin.

For an existing database, apply `database/migrations/2026_11_27_order_email_notifications.sql` once using phpMyAdmin or the MySQL CLI. Do not re-import `database/perfume_ecommerce.sql` into a database containing orders because that file drops application tables. Fresh installations already create the notification table from the base schema.

## Troubleshooting

- **Store setup required:** confirm MySQL is running, `.env` has the correct database values, and the SQL import completed.
- **`could not find driver`:** enable `pdo_mysql` in the PHP configuration used by Apache and CLI, then restart Apache.
- **Confirmation email not sent:** verify Composer dependencies are installed, the `MAIL_*` values are correct, the sender is verified in Brevo, and outbound SMTP/TLS is allowed by the host. Review the order's email status in Studio → Orders.
- **Empty catalogue:** re-import only into a fresh database and verify the `products`, `product_variants`, and `product_images` rows in phpMyAdmin.
- **500/403 on PHP files in `config`, `includes`, or `services`:** this is intentional direct-access protection; open the public storefront or `/admin` instead.
- **Broken styling/images:** keep the complete folder structure and open the app from its XAMPP URL, not by double-clicking `index.php`.

## Production checklist

Use a maintained PHP 8.1+ release and HTTPS, provision a least-privilege database user, set a strong database password, restrict database network access, back up and test restores, review error logging, and configure real payment-provider verification and refund procedures before accepting live orders. The bundled payment flow is manual verification only.
