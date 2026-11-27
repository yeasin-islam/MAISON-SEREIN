CREATE DATABASE IF NOT EXISTS maison_serein CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

USE maison_serein;

SET
  NAMES utf8mb4;

SET
  FOREIGN_KEY_CHECKS = 0;

DROP VIEW IF EXISTS inventory;

DROP VIEW IF EXISTS wishlists;

DROP TABLE IF EXISTS preorders,
limited_drops,
activity_logs,
notifications,
newsletter_subscribers,
social_links,
homepage_sections,
banners,
discount_usage,
discounts,
wishlist_items,
reviews,
inventory_transactions,
order_status_history,
payments,
order_email_notifications,
order_items,
orders,
delivery_zones,
customer_addresses,
product_variants,
product_images,
products,
categories,
brands,
customers,
admins,
store_settings;

SET
  FOREIGN_KEY_CHECKS = 1;

CREATE TABLE
  admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

CREATE TABLE
  customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NULL UNIQUE,
    phone VARCHAR(24) NOT NULL,
    phone_normalized VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_name (full_name)
  ) ENGINE = InnoDB;

CREATE TABLE
  customer_addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(60) NOT NULL DEFAULT 'Home',
    recipient_name VARCHAR(120) NOT NULL,
    phone VARCHAR(24) NOT NULL,
    phone_normalized VARCHAR(20) NOT NULL,
    district VARCHAR(120) NOT NULL,
    address TEXT NOT NULL,
    is_default TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE,
    INDEX idx_addresses_customer (customer_id, is_default)
  ) ENGINE = InnoDB;

CREATE TABLE
  brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

CREATE TABLE
  categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

CREATE TABLE
  products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    gender ENUM ('women', 'men', 'unisex') NOT NULL DEFAULT 'unisex',
    short_description VARCHAR(300) NULL,
    description TEXT NOT NULL,
    fragrance_family VARCHAR(120) NULL,
    top_notes VARCHAR(300) NULL,
    heart_notes VARCHAR(300) NULL,
    base_notes VARCHAR(300) NULL,
    mood_tags JSON NULL,
    is_featured TINYINT (1) NOT NULL DEFAULT 0,
    is_best_seller TINYINT (1) NOT NULL DEFAULT 0,
    is_new_arrival TINYINT (1) NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    is_visible TINYINT (1) NOT NULL DEFAULT 1,
    sales_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE RESTRICT,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL,
    INDEX idx_products_browse (is_active, is_visible, gender, created_at),
    INDEX idx_products_features (is_featured, is_best_seller, is_new_arrival)
  ) ENGINE = InnoDB;

CREATE TABLE
  product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(80) NOT NULL UNIQUE,
    size_ml DECIMAL(7, 2) NOT NULL,
    size_label VARCHAR(32) NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT chk_variant_price CHECK (price >= 0),
    CONSTRAINT chk_variant_size CHECK (size_ml > 0),
    INDEX idx_variants_product (product_id, is_active, size_ml),
    INDEX idx_variants_stock (stock_quantity, low_stock_threshold)
  ) ENGINE = InnoDB;

CREATE VIEW
  inventory AS
SELECT
  id AS variant_id,
  product_id,
  sku,
  stock_quantity,
  low_stock_threshold,
  updated_at
FROM
  product_variants;

CREATE TABLE
  product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(240) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_images_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    INDEX idx_images_product_sort (product_id, sort_order)
  ) ENGINE = InnoDB;

CREATE TABLE
  delivery_zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0,
    estimated_days VARCHAR(40) NULL,
    instructions VARCHAR(300) NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    CONSTRAINT chk_delivery_fee CHECK (delivery_fee >= 0)
  ) ENGINE = InnoDB;

CREATE TABLE
  orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(32) NOT NULL UNIQUE,
    idempotency_token CHAR(32) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NULL,
    delivery_zone_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(120) NOT NULL,
    phone VARCHAR(24) NOT NULL,
    phone_normalized VARCHAR(20) NOT NULL,
    email VARCHAR(190) NULL,
    district VARCHAR(120) NOT NULL,
    delivery_address TEXT NOT NULL,
    order_note TEXT NULL,
    payment_method ENUM ('bkash', 'nagad', 'cod') NOT NULL,
    payment_status ENUM (
      'unpaid',
      'pending_verification',
      'verified',
      'rejected',
      'refunded'
    ) NOT NULL DEFAULT 'unpaid',
    order_status ENUM (
      'pending',
      'confirmed',
      'processing',
      'shipped',
      'delivered',
      'cancelled'
    ) NOT NULL DEFAULT 'pending',
    subtotal DECIMAL(12, 2) NOT NULL,
    discount_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(12, 2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(12, 2) NOT NULL,
    gift_message VARCHAR(500) NULL,
    gift_wrap TINYINT (1) NOT NULL DEFAULT 0,
    cancellation_note VARCHAR(300) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL,
    CONSTRAINT fk_orders_zone FOREIGN KEY (delivery_zone_id) REFERENCES delivery_zones (id) ON DELETE SET NULL,
    INDEX idx_orders_status_date (order_status, created_at),
    INDEX idx_orders_phone_number (phone_normalized, order_number),
    INDEX idx_orders_customer (customer_id, created_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  order_email_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    recipient_email VARCHAR(190) NULL,
    status ENUM ('pending', 'sending', 'sent', 'failed', 'skipped') NOT NULL DEFAULT 'pending',
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    last_error VARCHAR(255) NULL,
    last_attempt_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_email_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    UNIQUE KEY uq_order_email_order (order_id),
    INDEX idx_order_email_status (status, last_attempt_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    variant_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    size_label VARCHAR(32) NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    line_total DECIMAL(12, 2) NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
    CONSTRAINT fk_items_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL,
    INDEX idx_items_order (order_id)
  ) ENGINE = InnoDB;

CREATE TABLE
  payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    method ENUM ('bkash', 'nagad', 'cod') NOT NULL,
    transaction_id VARCHAR(100) NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status ENUM (
      'pending_verification',
      'verified',
      'rejected',
      'unpaid',
      'refunded'
    ) NOT NULL,
    verified_by BIGINT UNSIGNED NULL,
    verified_at DATETIME NULL,
    admin_note VARCHAR(300) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_admin FOREIGN KEY (verified_by) REFERENCES admins (id) ON DELETE SET NULL,
    INDEX idx_payments_status (status, created_at),
    INDEX idx_payments_transaction (transaction_id)
  ) ENGINE = InnoDB;

CREATE TABLE
  order_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(24) NOT NULL,
    note VARCHAR(300) NULL,
    changed_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_status_admin FOREIGN KEY (changed_by) REFERENCES admins (id) ON DELETE SET NULL,
    INDEX idx_status_order_date (order_id, created_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  inventory_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    variant_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    quantity_change INT NOT NULL,
    reason ENUM ('order', 'restock', 'cancellation', 'adjustment') NOT NULL,
    note VARCHAR(240) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE RESTRICT,
    CONSTRAINT fk_inventory_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL,
    CONSTRAINT fk_inventory_admin FOREIGN KEY (created_by) REFERENCES admins (id) ON DELETE SET NULL,
    INDEX idx_inventory_variant_date (variant_id, created_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(120) NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    review_title VARCHAR(160) NULL,
    review_body TEXT NOT NULL,
    status ENUM ('pending', 'approved', 'rejected', 'hidden') NOT NULL DEFAULT 'pending',
    is_featured TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    INDEX idx_reviews_public (product_id, status, created_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  discounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(40) NOT NULL UNIQUE,
    product_id BIGINT UNSIGNED NULL,
    discount_type ENUM ('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    minimum_purchase DECIMAL(12, 2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(12, 2) NULL,
    first_order_only TINYINT (1) NOT NULL DEFAULT 0,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    usage_limit INT UNSIGNED NULL,
    usage_count INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_discount_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL,
    CONSTRAINT chk_discount_value CHECK (discount_value > 0),
    INDEX idx_discounts_active (is_active, starts_at, ends_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  discount_usage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    discount_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    phone_normalized VARCHAR(20) NOT NULL,
    used_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usage_discount FOREIGN KEY (discount_id) REFERENCES discounts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_usage_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    UNIQUE KEY uq_discount_phone (discount_id, phone_normalized),
    UNIQUE KEY uq_discount_order (discount_id, order_id)
  ) ENGINE = InnoDB;

CREATE TABLE
  wishlist_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    UNIQUE KEY uq_wishlist_product (customer_id, product_id)
  ) ENGINE = InnoDB;

CREATE VIEW
  wishlists AS
SELECT
  customer_id,
  product_id,
  created_at
FROM
  wishlist_items;

CREATE TABLE
  banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    subtitle VARCHAR(300) NULL,
    image_path VARCHAR(500) NULL,
    cta_label VARCHAR(80) NULL,
    cta_url VARCHAR(500) NULL,
    placement ENUM ('hero', 'promo', 'popup') NOT NULL DEFAULT 'promo',
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

CREATE TABLE
  homepage_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(60) NOT NULL UNIQUE,
    heading VARCHAR(160) NOT NULL,
    subtitle VARCHAR(240) NULL,
    collection_key VARCHAR(60) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1
  ) ENGINE = InnoDB;

CREATE TABLE
  social_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(40) NOT NULL,
    url VARCHAR(500) NOT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT (1) NOT NULL DEFAULT 1
  ) ENGINE = InnoDB;

CREATE TABLE
  store_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

CREATE TABLE
  limited_drops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL UNIQUE,
    release_at DATETIME NOT NULL,
    end_at DATETIME NULL,
    stock_limit INT UNSIGNED NULL,
    allow_preorder TINYINT (1) NOT NULL DEFAULT 0,
    status ENUM ('scheduled', 'active', 'ended', 'hidden') NOT NULL DEFAULT 'scheduled',
    CONSTRAINT fk_drops_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    INDEX idx_drops_dates (status, release_at, end_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  preorders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    expected_ship_date DATE NULL,
    status ENUM ('reserved', 'fulfilled', 'cancelled') NOT NULL DEFAULT 'reserved',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_preorder_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_preorder_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
    UNIQUE KEY uq_preorder_order_product (order_id, product_id)
  ) ENGINE = InnoDB;

CREATE TABLE
  notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    notification_type VARCHAR(40) NOT NULL,
    is_read TINYINT (1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_unread (is_read, created_at)
  ) ENGINE = InnoDB;

CREATE TABLE
  activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(60) NULL,
    entity_id BIGINT UNSIGNED NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_admin FOREIGN KEY (admin_id) REFERENCES admins (id) ON DELETE SET NULL,
    INDEX idx_activity_date (created_at, action)
  ) ENGINE = InnoDB;

CREATE TABLE
  newsletter_subscribers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    status ENUM ('subscribed', 'unsubscribed') NOT NULL DEFAULT 'subscribed',
    subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB;

INSERT INTO
  brands (id, name, slug, description)
VALUES
  (
    1,
    'THE LAST NOTE BD',
    'maison-serein',
    'Modern fragrance, considered slowly.'
  ),
  (
    2,
    'Atelier No. 8',
    'atelier-no-8',
    'Small-batch compositions inspired by place.'
  );

INSERT INTO
  categories (id, name, slug, description, sort_order)
VALUES
  (
    1,
    'Eau de parfum',
    'eau-de-parfum',
    'Our concentrated signature compositions.',
    1
  ),
  (
    2,
    'Discovery sets',
    'discovery-sets',
    'A considered introduction to the house.',
    2
  ),
  (
    3,
    'Home fragrance',
    'home-fragrance',
    'Quiet rituals for a softer room.',
    3
  );

INSERT INTO
  products (
    id,
    brand_id,
    category_id,
    name,
    slug,
    gender,
    short_description,
    description,
    fragrance_family,
    top_notes,
    heart_notes,
    base_notes,
    mood_tags,
    is_featured,
    is_best_seller,
    is_new_arrival
  )
VALUES
  (
    1,
    1,
    1,
    'Amber Veil',
    'amber-veil',
    'unisex',
    'Warm amber, softened by vanilla and skin musk.',
    'A close, golden composition that settles like late light on linen. Rounded amber and vanilla meet a quiet trail of soft woods.',
    'Amber · Vanilla',
    'Bergamot, Pink pepper',
    'Warm amber, iris',
    'Vanilla, Skin musk, Sandalwood',
    '["elegant","romantic","casual"]',
    1,
    1,
    0
  ),
  (
    2,
    2,
    1,
    'Oud No. 8',
    'oud-no-8',
    'men',
    'Smoky oud balanced with dry cedar and spice.',
    'A modern study in shadow and warmth. Polished oud and cedar are lifted by a restrained flash of cardamom.',
    'Woody · Spicy',
    'Cardamom, Saffron',
    'Oud, Black tea',
    'Cedarwood, Vetiver, Incense',
    '["formal","party","elegant"]',
    1,
    0,
    1
  ),
  (
    3,
    1,
    1,
    'Rose Atelier',
    'rose-atelier',
    'women',
    'A fresh rose wrapped in soft woods and pear.',
    'Garden rose at its most unguarded, brushed with pear and carried by a sheer, modern wood accord.',
    'Floral · Fresh',
    'Pear, Mandarin',
    'Rose petals, Peony',
    'Cashmere woods, White musk',
    '["romantic","fresh","office"]',
    1,
    1,
    1
  ),
  (
    4,
    1,
    1,
    'Citrus Study',
    'citrus-study',
    'unisex',
    'Bright neroli and green citrus for unhurried days.',
    'A crisp, quietly energising cologne built around neroli, green citrus and a clean mineral finish.',
    'Citrus · Aromatic',
    'Bitter orange, Lime',
    'Neroli, Basil',
    'Vetiver, Mineral musk',
    '["fresh","office","casual"]',
    0,
    1,
    1
  );

INSERT INTO
  product_images (product_id, image_path, alt_text, sort_order)
VALUES
  (
    1,
    'assets/images/amber-veil.png',
    'Amber Veil perfume in warm amber glass',
    1
  ),
  (
    2,
    'assets/images/oud-noir.png',
    'Oud No. 8 perfume in charcoal glass',
    1
  ),
  (
    3,
    'assets/images/rose-atelier.png',
    'Rose Atelier perfume with rose petals',
    1
  ),
  (
    4,
    'assets/images/amber-veil.png',
    'Citrus Study perfume bottle',
    1
  );

INSERT INTO
  product_variants (
    id,
    product_id,
    sku,
    size_ml,
    size_label,
    price,
    stock_quantity,
    low_stock_threshold,
    sort_order
  )
VALUES
  (1, 1, 'MS-AV-30', 30, '30 ml', 2450, 18, 4, 1),
  (2, 1, 'MS-AV-50', 50, '50 ml', 3650, 12, 4, 2),
  (3, 1, 'MS-AV-100', 100, '100 ml', 5900, 7, 3, 3),
  (4, 2, 'AN8-30', 30, '30 ml', 2900, 10, 4, 1),
  (5, 2, 'AN8-50', 50, '50 ml', 4250, 8, 4, 2),
  (6, 2, 'AN8-100', 100, '100 ml', 6900, 3, 3, 3),
  (7, 3, 'MS-RA-30', 30, '30 ml', 2550, 15, 4, 1),
  (8, 3, 'MS-RA-50', 50, '50 ml', 3800, 11, 4, 2),
  (9, 3, 'MS-RA-100', 100, '100 ml', 6100, 5, 3, 3),
  (10, 4, 'MS-CS-30', 30, '30 ml', 2200, 14, 4, 1),
  (11, 4, 'MS-CS-50', 50, '50 ml', 3350, 9, 4, 2),
  (12, 4, 'MS-CS-100', 100, '100 ml', 5400, 6, 3, 3);

INSERT INTO
  delivery_zones (
    id,
    name,
    delivery_fee,
    estimated_days,
    instructions,
    sort_order
  )
VALUES
  (
    1,
    'Inside Dhaka',
    80,
    '1–2 business days',
    'Delivery fee is calculated from the selected zone.',
    1
  ),
  (
    2,
    'Outside Dhaka',
    140,
    '2–4 business days',
    'Delivery fee is calculated from the selected zone.',
    2
  );

INSERT INTO
  store_settings (setting_key, setting_value)
VALUES
  ('store_phone', '+880 1700 000000'),
  ('store_email', 'hello@maisonserein.example'),
  ('whatsapp_number', '8801700000000'),
  ('bkash_number', '01XXXXXXXXX'),
  ('nagad_number', '01XXXXXXXXX'),
  (
    'bkash_instructions',
    'Send the exact total to our bKash number, then enter the transaction ID. Payment remains pending until verified.'
  ),
  (
    'nagad_instructions',
    'Send the exact total to our Nagad number, then enter the transaction ID. Payment remains pending until verified.'
  ),
  (
    'cancellation_policy',
    'Customer cancellations are accepted for pending or confirmed orders within the configured window.'
  ),
  ('customer_cancellation_window_hours', '24'),
  (
    'accepted_payment_methods',
    '["bkash","nagad","cod"]'
  ),
  ('popup_frequency', 'session'),
  ('stock_restore_on_cancellation', '1'),
  ('gift_mode_enabled', '1');

INSERT INTO
  banners (
    title,
    subtitle,
    image_path,
    cta_label,
    cta_url,
    placement,
    sort_order
  )
VALUES
  (
    'A quieter kind of luxury',
    'Fragrance for the moments that are yours.',
    'assets/images/atelier-hero.png',
    'Discover the collection',
    '?page=shop',
    'hero',
    1
  ),
  (
    'A small welcome, from us to you',
    'Take 10% off your first order with WELCOME10.',
    'assets/images/rose-atelier.png',
    'Use WELCOME10',
    '?page=shop',
    'promo',
    1
  ),
  (
    'Made slowly. Worn close.',
    'A considered fragrance for everyday rituals.',
    'assets/images/amber-veil.png',
    'Find your feeling',
    '?page=mood',
    'promo',
    2
  ),
  (
    'Your first note from the house',
    'Enjoy 10% off your first order with WELCOME10.',
    'assets/images/atelier-hero.png',
    'Discover the collection',
    '?page=shop',
    'popup',
    1
  );

INSERT INTO
  limited_drops (
    product_id,
    release_at,
    end_at,
    stock_limit,
    allow_preorder,
    status
  )
VALUES
  (
    4,
    DATE_SUB (NOW (), INTERVAL 1 DAY),
    DATE_ADD (NOW (), INTERVAL 14 DAY),
    20,
    0,
    'active'
  );

INSERT INTO
  homepage_sections (
    section_key,
    heading,
    subtitle,
    collection_key,
    sort_order
  )
VALUES
  (
    'featured',
    'A few house favourites',
    'Compositions our community returns to.',
    'featured',
    1
  ),
  (
    'best-sellers',
    'House favourites',
    'Considered, not collected.',
    'best_seller',
    2
  ),
  (
    'new-arrivals',
    'Recently arrived',
    'A new chapter.',
    'new_arrival',
    3
  ),
  (
    'mens',
    'For him',
    'A more personal signature.',
    'men',
    4
  ),
  (
    'womens',
    'For her',
    'A softer kind of statement.',
    'women',
    5
  ),
  (
    'unisex',
    'Made to be shared',
    'Without a label.',
    'unisex',
    6
  ),
  (
    'limited',
    'A limited release',
    'Small runs, short windows.',
    'limited',
    7
  ),
  (
    'reviews',
    'Notes from you',
    'Worn and remembered.',
    'reviews',
    8
  );

INSERT INTO
  social_links (platform, url, sort_order)
VALUES
  ('Instagram', 'https://instagram.com/', 1),
  ('Facebook', 'https://facebook.com/', 2);

INSERT INTO
  discounts (
    code,
    discount_type,
    discount_value,
    minimum_purchase,
    max_discount,
    first_order_only,
    starts_at,
    ends_at,
    is_active
  )
VALUES
  (
    'WELCOME10',
    'percentage',
    10,
    1500,
    600,
    1,
    NOW (),
    DATE_ADD (NOW (), INTERVAL 1 YEAR),
    1
  );