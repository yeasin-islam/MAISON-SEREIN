SET
    NAMES utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

INSERT INTO
    brands (id, name, slug, description, is_active)
VALUES
    (
        3,
        'Atelier Botanique',
        'atelier-botanique',
        'Botanical compositions shaped by green notes.',
        1
    ),
    (
        4,
        'Maison d’Aube',
        'maison-daube',
        'Soft, luminous fragrances for everyday rituals.',
        1
    ),
    (
        5,
        'Nocturne Parfums',
        'nocturne-parfums',
        'Deep evening compositions with a modern edge.',
        1
    ),
    (
        6,
        'Fleur de Minuit',
        'fleur-de-minuit',
        'Expressive florals with a quiet signature.',
        1
    ),
    (
        7,
        'Terre & Brume',
        'terre-brume',
        'Earthy fragrances inspired by landscape.',
        1
    ),
    (
        8,
        'Lumière Studio',
        'lumiere-studio',
        'Bright compositions with a clean finish.',
        1
    ),
    (
        9,
        'Bois Moderne',
        'bois-moderne',
        'Contemporary woods, spice and texture.',
        1
    ),
    (
        10,
        'Serein Reserve',
        'serein-reserve',
        'Small-run fragrances from the house archive.',
        1
    );

-- 7 additional categories (the original dump already has 3)
INSERT INTO
    categories (
        id,
        name,
        slug,
        description,
        sort_order,
        is_active
    )
VALUES
    (
        4,
        'Parfum',
        'parfum',
        'Intense compositions with a lasting presence.',
        4,
        1
    ),
    (
        5,
        'Eau de toilette',
        'eau-de-toilette',
        'Lighter compositions for everyday wear.',
        5,
        1
    ),
    (
        6,
        'Floral fragrances',
        'floral-fragrances',
        'Rose, white flowers and modern petals.',
        6,
        1
    ),
    (
        7,
        'Woody fragrances',
        'woody-fragrances',
        'Cedar, sandalwood, vetiver and oud.',
        7,
        1
    ),
    (
        8,
        'Fresh fragrances',
        'fresh-fragrances',
        'Citrus, aromatic and clean compositions.',
        8,
        1
    ),
    (
        9,
        'Amber fragrances',
        'amber-fragrances',
        'Warm amber, resin and soft vanilla.',
        9,
        1
    ),
    (
        10,
        'Gift sets',
        'gift-sets',
        'Curated fragrance gifts and rituals.',
        10,
        1
    );

-- Add 16 fragrance products. Together with the 4 original products = 20.
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
        is_new_arrival,
        is_active,
        is_visible,
        sales_count
    )
VALUES
    (
        5,
        3,
        8,
        'Neroli Morning',
        'neroli-morning',
        'unisex',
        'Fresh neroli with petitgrain and soft musk.',
        'A bright opening of citrus leaves settles into airy neroli and a clean, gentle musk.',
        'Citrus · Floral',
        'Bergamot, Petitgrain',
        'Neroli, Orange blossom',
        'White musk, Cedar',
        '["fresh","office","casual"]',
        1,
        0,
        1,
        1,
        1,
        8
    ),
    (
        6,
        4,
        6,
        'Velvet Peony',
        'velvet-peony',
        'women',
        'Soft peony wrapped in creamy sandalwood.',
        'A delicate floral composition where dewy peony meets a smooth, comforting wood accord.',
        'Floral · Woody',
        'Pink pepper, Pear',
        'Peony, Rose',
        'Sandalwood, Musk',
        '["romantic","elegant","day"]',
        1,
        1,
        0,
        1,
        1,
        16
    ),
    (
        7,
        5,
        7,
        'Midnight Cedar',
        'midnight-cedar',
        'men',
        'Dry cedar with smoky vetiver and spice.',
        'A confident woody fragrance with a crisp spicy opening and a deep cedar-vetiver foundation.',
        'Woody · Spicy',
        'Black pepper, Cardamom',
        'Cypress, Cedar',
        'Vetiver, Amberwood',
        '["formal","evening","bold"]',
        1,
        0,
        1,
        1,
        1,
        5
    ),
    (
        8,
        6,
        6,
        'White Petal',
        'white-petal',
        'women',
        'Airy white florals with a clean musk finish.',
        'A luminous bouquet of jasmine and orange blossom softened by sheer musk and pale woods.',
        'Floral · Musky',
        'Mandarin, Green leaves',
        'Jasmine, Orange blossom',
        'White musk, Blonde woods',
        '["fresh","romantic","office"]',
        0,
        1,
        0,
        1,
        1,
        12
    ),
    (
        9,
        7,
        7,
        'Forest Veil',
        'forest-veil',
        'unisex',
        'Green woods, moss and cool aromatic notes.',
        'Inspired by a quiet walk after rain, this composition blends aromatic greens with moss and woods.',
        'Green · Woody',
        'Juniper, Galbanum',
        'Cypress, Clary sage',
        'Oakmoss, Cedar',
        '["calm","casual","nature"]',
        0,
        0,
        1,
        1,
        1,
        4
    ),
    (
        10,
        8,
        8,
        'Citrus Linen',
        'citrus-linen',
        'unisex',
        'Sparkling citrus over soft clean musk.',
        'A light, effortless blend of lemon zest and neroli with a soft linen-like musk trail.',
        'Citrus · Fresh',
        'Lemon, Mandarin',
        'Neroli, Tea leaves',
        'Clean musk, Vetiver',
        '["fresh","office","day"]',
        1,
        1,
        0,
        1,
        1,
        19
    ),
    (
        11,
        9,
        7,
        'Sandalwood Hour',
        'sandalwood-hour',
        'unisex',
        'Creamy sandalwood with cardamom warmth.',
        'A smooth, intimate wood fragrance built around creamy sandalwood, warm cardamom and skin musk.',
        'Woody · Amber',
        'Cardamom, Bergamot',
        'Sandalwood, Iris',
        'Tonka, Skin musk',
        '["elegant","cozy","evening"]',
        0,
        1,
        0,
        1,
        1,
        14
    ),
    (
        12,
        10,
        9,
        'Amber Nocturne',
        'amber-nocturne',
        'unisex',
        'Golden amber, labdanum and vanilla.',
        'A rich yet balanced amber fragrance with resinous warmth and a soft vanilla finish.',
        'Amber · Oriental',
        'Saffron, Mandarin',
        'Labdanum, Rose',
        'Vanilla, Benzoin, Patchouli',
        '["evening","formal","warm"]',
        1,
        0,
        1,
        1,
        1,
        6
    ),
    (
        13,
        3,
        5,
        'Green Fig',
        'green-fig',
        'unisex',
        'Milky fig and green leaves with cedar.',
        'A relaxed, sunlit composition pairing green fig with creamy woods and a gentle mineral freshness.',
        'Green · Fruity',
        'Fig leaf, Bergamot',
        'Fig, Violet leaf',
        'Cedar, Sandalwood',
        '["casual","fresh","day"]',
        0,
        0,
        1,
        1,
        1,
        3
    ),
    (
        14,
        4,
        6,
        'Rosewater Silk',
        'rosewater-silk',
        'women',
        'Dewy rose with pear and powdery iris.',
        'A sheer rose fragrance with a fresh watery lift and a delicate, silky iris dry-down.',
        'Floral · Powdery',
        'Pear, Pink pepper',
        'Rose, Iris',
        'White musk, Cashmere wood',
        '["romantic","soft","day"]',
        1,
        1,
        0,
        1,
        1,
        21
    ),
    (
        15,
        5,
        4,
        'Oud Ember',
        'oud-ember',
        'men',
        'Smouldering oud with saffron and leather.',
        'A textured, long-wearing composition balancing polished oud with saffron, dry woods and a subtle leather accord.',
        'Oud · Leather',
        'Saffron, Black pepper',
        'Oud, Suede',
        'Patchouli, Dry amber',
        '["bold","formal","evening"]',
        0,
        0,
        1,
        1,
        1,
        7
    ),
    (
        16,
        6,
        6,
        'Jasmine Reverie',
        'jasmine-reverie',
        'women',
        'Jasmine petals over a warm amber base.',
        'An expressive jasmine heart is brightened by citrus and grounded in a soft amber-musk base.',
        'Floral · Amber',
        'Mandarin, Bergamot',
        'Jasmine, Ylang-ylang',
        'Amber, Musk',
        '["romantic","elegant","evening"]',
        0,
        1,
        0,
        1,
        1,
        11
    ),
    (
        17,
        7,
        7,
        'Mineral Woods',
        'mineral-woods',
        'unisex',
        'Cool mineral notes with vetiver and cedar.',
        'A modern, understated woody scent with mineral freshness, aromatic herbs and dry cedar.',
        'Woody · Aromatic',
        'Juniper, Lime',
        'Lavender, Mineral accord',
        'Vetiver, Cedar',
        '["minimal","office","fresh"]',
        0,
        0,
        1,
        1,
        1,
        2
    ),
    (
        18,
        8,
        8,
        'Bergamot Coast',
        'bergamot-coast',
        'unisex',
        'Bergamot and sea air over driftwood.',
        'A breezy citrus opening moves into aromatic herbs and a gentle driftwood finish.',
        'Citrus · Aquatic',
        'Bergamot, Lemon',
        'Sea salt, Rosemary',
        'Driftwood, Musk',
        '["fresh","casual","summer"]',
        1,
        0,
        1,
        1,
        1,
        9
    ),
    (
        19,
        9,
        9,
        'Tonka Suede',
        'tonka-suede',
        'unisex',
        'Soft suede, tonka and a touch of cocoa.',
        'A warm, tactile fragrance blending smooth suede with tonka bean and restrained cocoa warmth.',
        'Leather · Gourmand',
        'Pink pepper, Cocoa',
        'Suede, Iris',
        'Tonka, Vanilla, Cedar',
        '["cozy","evening","warm"]',
        0,
        0,
        0,
        1,
        1,
        5
    ),
    (
        20,
        10,
        4,
        'Serein No. 20',
        'serein-no-20',
        'unisex',
        'A quiet signature of iris, amber and woods.',
        'A composed signature fragrance with a powdery iris heart, transparent amber and a soft woody trail.',
        'Amber · Woody',
        'Bergamot, Pink pepper',
        'Iris, Violet',
        'Amber, Sandalwood, Musk',
        '["signature","elegant","everyday"]',
        1,
        0,
        1,
        1,
        1,
        1
    );

-- One purchasable 50 ml variant for each added fragrance.
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
        is_active,
        sort_order
    )
VALUES
    (13, 5, 'MS-NM-50', 50, '50 ml', 2850, 20, 4, 1, 1),
    (14, 6, 'MS-VP-50', 50, '50 ml', 3150, 16, 4, 1, 1),
    (15, 7, 'NP-MC-50', 50, '50 ml', 3450, 12, 4, 1, 1),
    (16, 8, 'FM-WP-50', 50, '50 ml', 2950, 18, 4, 1, 1),
    (17, 9, 'TB-FV-50', 50, '50 ml', 3250, 14, 4, 1, 1),
    (
        18,
        10,
        'LS-CL-50',
        50,
        '50 ml',
        2650,
        24,
        4,
        1,
        1
    ),
    (
        19,
        11,
        'BM-SH-50',
        50,
        '50 ml',
        3550,
        10,
        4,
        1,
        1
    ),
    (20, 12, 'SR-AN-50', 50, '50 ml', 3950, 8, 3, 1, 1),
    (
        21,
        13,
        'AB-GF-50',
        50,
        '50 ml',
        2750,
        17,
        4,
        1,
        1
    ),
    (
        22,
        14,
        'MA-RS-50',
        50,
        '50 ml',
        3050,
        15,
        4,
        1,
        1
    ),
    (23, 15, 'NP-OE-50', 50, '50 ml', 4250, 7, 3, 1, 1),
    (
        24,
        16,
        'FM-JR-50',
        50,
        '50 ml',
        3350,
        13,
        4,
        1,
        1
    ),
    (
        25,
        17,
        'TB-MW-50',
        50,
        '50 ml',
        3150,
        11,
        4,
        1,
        1
    ),
    (
        26,
        18,
        'LS-BC-50',
        50,
        '50 ml',
        2850,
        21,
        4,
        1,
        1
    ),
    (27, 19, 'BM-TS-50', 50, '50 ml', 3650, 9, 3, 1, 1),
    (
        28,
        20,
        'SR-S20-50',
        50,
        '50 ml',
        3850,
        6,
        3,
        1,
        1
    );

-- Direct image URLs from images.unsplash.com. Product images are deliberately distinct.
INSERT INTO
    product_images (product_id, image_path, alt_text, sort_order)
VALUES
    (
        5,
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85',
        'Neroli Morning fragrance bottle',
        1
    ),
    (
        6,
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=85',
        'Velvet Peony fragrance bottle',
        1
    ),
    (
        7,
        'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=900&q=85',
        'Midnight Cedar fragrance bottle',
        1
    ),
    (
        8,
        'https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?auto=format&fit=crop&w=900&q=85',
        'White Petal fragrance bottle',
        1
    ),
    (
        9,
        'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=85',
        'Forest Veil fragrance bottle',
        1
    ),
    (
        10,
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=85',
        'Citrus Linen fragrance bottle',
        1
    ),
    (
        11,
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85',
        'Sandalwood Hour fragrance bottle',
        1
    ),
    (
        12,
        'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=900&q=85',
        'Amber Nocturne fragrance bottle',
        1
    ),
    (
        13,
        'https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?auto=format&fit=crop&w=900&q=85',
        'Green Fig fragrance bottle',
        1
    ),
    (
        14,
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=85',
        'Rosewater Silk fragrance bottle',
        1
    ),
    (
        15,
        'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=900&q=85',
        'Oud Ember fragrance bottle',
        1
    ),
    (
        16,
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85',
        'Jasmine Reverie fragrance bottle',
        1
    ),
    (
        17,
        'https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?auto=format&fit=crop&w=900&q=85',
        'Mineral Woods fragrance bottle',
        1
    ),
    (
        18,
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=900&q=85',
        'Bergamot Coast fragrance bottle',
        1
    ),
    (
        19,
        'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=900&q=85',
        'Tonka Suede fragrance bottle',
        1
    ),
    (
        20,
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85',
        'Serein No. 20 fragrance bottle',
        1
    );

-- Customers: demo-only accounts; passwords are intentionally non-loginable placeholders.
INSERT INTO
    customers (
        id,
        full_name,
        email,
        phone,
        phone_normalized,
        password_hash,
        is_active
    )
VALUES
    (
        101,
        'Aarav Rahman',
        'aarav.rahman@example.test',
        '01710000001',
        '01710000001',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        102,
        'Nusrat Jahan',
        'nusrat.jahan@example.test',
        '01710000002',
        '01710000002',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        103,
        'Samiha Noor',
        'samiha.noor@example.test',
        '01710000003',
        '01710000003',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        104,
        'Farhan Ahmed',
        'farhan.ahmed@example.test',
        '01710000004',
        '01710000004',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        105,
        'Maliha Islam',
        'maliha.islam@example.test',
        '01710000005',
        '01710000005',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        106,
        'Rafi Chowdhury',
        'rafi.chowdhury@example.test',
        '01710000006',
        '01710000006',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        107,
        'Tanjim Hasan',
        'tanjim.hasan@example.test',
        '01710000007',
        '01710000007',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        108,
        'Anika Sultana',
        'anika.sultana@example.test',
        '01710000008',
        '01710000008',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        109,
        'Imran Kabir',
        'imran.kabir@example.test',
        '01710000009',
        '01710000009',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    ),
    (
        110,
        'Mehjabin Akter',
        'mehjabin.akter@example.test',
        '01710000010',
        '01710000010',
        '$2y$10$DEMO_SEED_ACCOUNT_DISABLED_HASH_00000000000000000000000000000000000000000000000000000',
        1
    );

INSERT INTO
    customer_addresses (
        id,
        customer_id,
        label,
        recipient_name,
        phone,
        phone_normalized,
        district,
        address,
        is_default
    )
VALUES
    (
        101,
        101,
        'Home',
        'Aarav Rahman',
        '01710000001',
        '01710000001',
        'Dhaka',
        'House 12, Road 4, Dhanmondi, Dhaka',
        1
    ),
    (
        102,
        102,
        'Home',
        'Nusrat Jahan',
        '01710000002',
        '01710000002',
        'Chattogram',
        'Road 2, Khulshi, Chattogram',
        1
    ),
    (
        103,
        103,
        'Office',
        'Samiha Noor',
        '01710000003',
        '01710000003',
        'Dhaka',
        'Level 5, Banani, Dhaka',
        1
    ),
    (
        104,
        104,
        'Home',
        'Farhan Ahmed',
        '01710000004',
        '01710000004',
        'Sylhet',
        'Zindabazar, Sylhet',
        1
    ),
    (
        105,
        105,
        'Home',
        'Maliha Islam',
        '01710000005',
        '01710000005',
        'Rajshahi',
        'Shaheb Bazar, Rajshahi',
        1
    ),
    (
        106,
        106,
        'Home',
        'Rafi Chowdhury',
        '01710000006',
        '01710000006',
        'Dhaka',
        'Uttara Sector 7, Dhaka',
        1
    ),
    (
        107,
        107,
        'Office',
        'Tanjim Hasan',
        '01710000007',
        '01710000007',
        'Khulna',
        'Khalishpur, Khulna',
        1
    ),
    (
        108,
        108,
        'Home',
        'Anika Sultana',
        '01710000008',
        '01710000008',
        'Cumilla',
        'Kandirpar, Cumilla',
        1
    ),
    (
        109,
        109,
        'Home',
        'Imran Kabir',
        '01710000009',
        '01710000009',
        'Dhaka',
        'Mirpur 10, Dhaka',
        1
    ),
    (
        110,
        110,
        'Home',
        'Mehjabin Akter',
        '01710000010',
        '01710000010',
        'Rangpur',
        'Jahaj Company More, Rangpur',
        1
    );

-- Add 8 delivery zones (the original dump has 2).
INSERT INTO
    delivery_zones (
        id,
        name,
        delivery_fee,
        estimated_days,
        instructions,
        sort_order,
        is_active
    )
VALUES
    (
        3,
        'Gazipur',
        110,
        '1–3 business days',
        'Standard courier delivery.',
        3,
        1
    ),
    (
        4,
        'Narayanganj',
        100,
        '1–2 business days',
        'Standard courier delivery.',
        4,
        1
    ),
    (
        5,
        'Chattogram',
        140,
        '2–4 business days',
        'Standard courier delivery.',
        5,
        1
    ),
    (
        6,
        'Sylhet',
        150,
        '2–4 business days',
        'Standard courier delivery.',
        6,
        1
    ),
    (
        7,
        'Rajshahi',
        150,
        '2–4 business days',
        'Standard courier delivery.',
        7,
        1
    ),
    (
        8,
        'Khulna',
        150,
        '2–4 business days',
        'Standard courier delivery.',
        8,
        1
    ),
    (
        9,
        'Rangpur',
        160,
        '3–5 business days',
        'Standard courier delivery.',
        9,
        1
    ),
    (
        10,
        'Other districts',
        160,
        '3–5 business days',
        'Standard courier delivery.',
        10,
        1
    );

-- Ten sample orders, one per demo customer. Order totals are internally consistent.
INSERT INTO
    orders (
        id,
        order_number,
        idempotency_token,
        customer_id,
        delivery_zone_id,
        customer_name,
        phone,
        phone_normalized,
        email,
        district,
        delivery_address,
        order_note,
        payment_method,
        payment_status,
        order_status,
        subtotal,
        discount_amount,
        delivery_fee,
        grand_total,
        gift_message,
        gift_wrap
    )
VALUES
    (
        101,
        'MS-DEMO-1001',
        '00000000000000000000000000000101',
        101,
        1,
        'Aarav Rahman',
        '01710000001',
        '01710000001',
        'aarav.rahman@example.test',
        'Dhaka',
        'House 12, Road 4, Dhanmondi, Dhaka',
        NULL,
        'cod',
        'unpaid',
        'delivered',
        2450,
        0,
        80,
        2530,
        NULL,
        0
    ),
    (
        102,
        'MS-DEMO-1002',
        '00000000000000000000000000000102',
        102,
        5,
        'Nusrat Jahan',
        '01710000002',
        '01710000002',
        'nusrat.jahan@example.test',
        'Chattogram',
        'Road 2, Khulshi, Chattogram',
        NULL,
        'bkash',
        'verified',
        'delivered',
        3150,
        0,
        140,
        3290,
        NULL,
        0
    ),
    (
        103,
        'MS-DEMO-1003',
        '00000000000000000000000000000103',
        103,
        1,
        'Samiha Noor',
        '01710000003',
        '01710000003',
        'samiha.noor@example.test',
        'Dhaka',
        'Level 5, Banani, Dhaka',
        NULL,
        'nagad',
        'pending_verification',
        'processing',
        3450,
        0,
        80,
        3530,
        'Happy birthday!',
        1
    ),
    (
        104,
        'MS-DEMO-1004',
        '00000000000000000000000000000104',
        104,
        6,
        'Farhan Ahmed',
        '01710000004',
        '01710000004',
        'farhan.ahmed@example.test',
        'Sylhet',
        'Zindabazar, Sylhet',
        NULL,
        'cod',
        'unpaid',
        'shipped',
        2950,
        0,
        150,
        3100,
        NULL,
        0
    ),
    (
        105,
        'MS-DEMO-1005',
        '00000000000000000000000000000105',
        105,
        7,
        'Maliha Islam',
        '01710000005',
        '01710000005',
        'maliha.islam@example.test',
        'Rajshahi',
        'Shaheb Bazar, Rajshahi',
        NULL,
        'bkash',
        'verified',
        'delivered',
        3250,
        250,
        150,
        3150,
        NULL,
        0
    ),
    (
        106,
        'MS-DEMO-1006',
        '00000000000000000000000000000106',
        106,
        1,
        'Rafi Chowdhury',
        '01710000006',
        '01710000006',
        'rafi.chowdhury@example.test',
        'Dhaka',
        'Uttara Sector 7, Dhaka',
        NULL,
        'cod',
        'unpaid',
        'confirmed',
        2650,
        0,
        80,
        2730,
        NULL,
        0
    ),
    (
        107,
        'MS-DEMO-1007',
        '00000000000000000000000000000107',
        107,
        8,
        'Tanjim Hasan',
        '01710000007',
        '01710000007',
        'tanjim.hasan@example.test',
        'Khulna',
        'Khalishpur, Khulna',
        NULL,
        'nagad',
        'rejected',
        'cancelled',
        3550,
        0,
        150,
        3700,
        NULL,
        0
    ),
    (
        108,
        'MS-DEMO-1008',
        '00000000000000000000000000000108',
        108,
        4,
        'Anika Sultana',
        '01710000008',
        '01710000008',
        'anika.sultana@example.test',
        'Cumilla',
        'Kandirpar, Cumilla',
        NULL,
        'cod',
        'unpaid',
        'pending',
        3950,
        0,
        100,
        4050,
        NULL,
        0
    ),
    (
        109,
        'MS-DEMO-1009',
        '00000000000000000000000000000109',
        109,
        1,
        'Imran Kabir',
        '01710000009',
        '01710000009',
        'imran.kabir@example.test',
        'Dhaka',
        'Mirpur 10, Dhaka',
        NULL,
        'bkash',
        'pending_verification',
        'confirmed',
        2750,
        0,
        80,
        2830,
        NULL,
        0
    ),
    (
        110,
        'MS-DEMO-1010',
        '00000000000000000000000000000110',
        110,
        9,
        'Mehjabin Akter',
        '01710000010',
        '01710000010',
        'mehjabin.akter@example.test',
        'Rangpur',
        'Jahaj Company More, Rangpur',
        NULL,
        'cod',
        'unpaid',
        'delivered',
        3050,
        0,
        160,
        3210,
        NULL,
        0
    );

INSERT INTO
    order_items (
        id,
        order_id,
        product_id,
        variant_id,
        product_name,
        size_label,
        unit_price,
        quantity,
        line_total
    )
VALUES
    (
        101,
        101,
        1,
        1,
        'Amber Veil',
        '30 ml',
        2450,
        1,
        2450
    ),
    (
        102,
        102,
        6,
        14,
        'Velvet Peony',
        '50 ml',
        3150,
        1,
        3150
    ),
    (
        103,
        103,
        7,
        15,
        'Midnight Cedar',
        '50 ml',
        3450,
        1,
        3450
    ),
    (
        104,
        104,
        8,
        16,
        'White Petal',
        '50 ml',
        2950,
        1,
        2950
    ),
    (
        105,
        105,
        9,
        17,
        'Forest Veil',
        '50 ml',
        3250,
        1,
        3250
    ),
    (
        106,
        106,
        10,
        18,
        'Citrus Linen',
        '50 ml',
        2650,
        1,
        2650
    ),
    (
        107,
        107,
        11,
        19,
        'Sandalwood Hour',
        '50 ml',
        3550,
        1,
        3550
    ),
    (
        108,
        108,
        12,
        20,
        'Amber Nocturne',
        '50 ml',
        3950,
        1,
        3950
    ),
    (
        109,
        109,
        13,
        21,
        'Green Fig',
        '50 ml',
        2750,
        1,
        2750
    ),
    (
        110,
        110,
        14,
        22,
        'Rosewater Silk',
        '50 ml',
        3050,
        1,
        3050
    );

INSERT INTO
    payments (
        id,
        order_id,
        method,
        transaction_id,
        amount,
        status,
        verified_by,
        verified_at,
        admin_note
    )
VALUES
    (
        101,
        101,
        'cod',
        NULL,
        2530,
        'unpaid',
        NULL,
        NULL,
        NULL
    ),
    (
        102,
        102,
        'bkash',
        'DEMO-BKASH-1002',
        3290,
        'verified',
        NULL,
        NULL,
        'Demo payment record'
    ),
    (
        103,
        103,
        'nagad',
        'DEMO-NAGAD-1003',
        3530,
        'pending_verification',
        NULL,
        NULL,
        NULL
    ),
    (
        104,
        104,
        'cod',
        NULL,
        3100,
        'unpaid',
        NULL,
        NULL,
        NULL
    ),
    (
        105,
        105,
        'bkash',
        'DEMO-BKASH-1005',
        3150,
        'verified',
        NULL,
        NULL,
        'Demo payment record'
    ),
    (
        106,
        106,
        'cod',
        NULL,
        2730,
        'unpaid',
        NULL,
        NULL,
        NULL
    ),
    (
        107,
        107,
        'nagad',
        'DEMO-NAGAD-1007',
        3700,
        'rejected',
        NULL,
        NULL,
        'Demo transaction rejected'
    ),
    (
        108,
        108,
        'cod',
        NULL,
        4050,
        'unpaid',
        NULL,
        NULL,
        NULL
    ),
    (
        109,
        109,
        'bkash',
        'DEMO-BKASH-1009',
        2830,
        'pending_verification',
        NULL,
        NULL,
        NULL
    ),
    (
        110,
        110,
        'cod',
        NULL,
        3210,
        'unpaid',
        NULL,
        NULL,
        NULL
    );

INSERT INTO
    order_status_history (id, order_id, status, note, changed_by)
VALUES
    (
        101,
        101,
        'delivered',
        'Demo order delivered.',
        NULL
    ),
    (
        102,
        102,
        'delivered',
        'Demo order delivered.',
        NULL
    ),
    (
        103,
        103,
        'processing',
        'Demo order is being prepared.',
        NULL
    ),
    (
        104,
        104,
        'shipped',
        'Demo order handed to courier.',
        NULL
    ),
    (
        105,
        105,
        'delivered',
        'Demo order delivered.',
        NULL
    ),
    (
        106,
        106,
        'confirmed',
        'Demo order confirmed.',
        NULL
    ),
    (
        107,
        107,
        'cancelled',
        'Demo order cancelled.',
        NULL
    ),
    (108, 108, 'pending', 'Demo order received.', NULL),
    (
        109,
        109,
        'confirmed',
        'Demo order confirmed.',
        NULL
    ),
    (
        110,
        110,
        'delivered',
        'Demo order delivered.',
        NULL
    );

INSERT INTO
    inventory_transactions (
        id,
        variant_id,
        order_id,
        quantity_change,
        reason,
        note,
        created_by
    )
VALUES
    (
        101,
        1,
        101,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        102,
        14,
        102,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        103,
        15,
        103,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        104,
        16,
        104,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        105,
        17,
        105,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        106,
        18,
        106,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        107,
        19,
        107,
        1,
        'cancellation',
        'Demo cancellation stock restoration.',
        NULL
    ),
    (
        108,
        20,
        108,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        109,
        21,
        109,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    ),
    (
        110,
        22,
        110,
        -1,
        'order',
        'Demo order allocation.',
        NULL
    );

INSERT INTO
    reviews (
        id,
        product_id,
        customer_id,
        customer_name,
        rating,
        review_title,
        review_body,
        status,
        is_featured
    )
VALUES
    (
        101,
        1,
        101,
        'Aarav Rahman',
        5,
        'Beautifully balanced',
        'Warm, smooth and easy to wear throughout the day.',
        'approved',
        1
    ),
    (
        102,
        6,
        102,
        'Nusrat Jahan',
        5,
        'A lovely floral',
        'Soft petals and a very elegant dry-down.',
        'approved',
        1
    ),
    (
        103,
        7,
        103,
        'Samiha Noor',
        4,
        'Modern woods',
        'A confident woody scent without feeling too heavy.',
        'approved',
        0
    ),
    (
        104,
        8,
        104,
        'Farhan Ahmed',
        5,
        'Clean and graceful',
        'Fresh floral notes that stay close to the skin.',
        'approved',
        0
    ),
    (
        105,
        9,
        105,
        'Maliha Islam',
        4,
        'After the rain',
        'Green and earthy in a very calming way.',
        'approved',
        0
    ),
    (
        106,
        10,
        106,
        'Rafi Chowdhury',
        5,
        'Everyday favourite',
        'Bright citrus with a comfortable clean finish.',
        'approved',
        1
    ),
    (
        107,
        11,
        107,
        'Tanjim Hasan',
        4,
        'Soft sandalwood',
        'Creamy woods with a subtle warm character.',
        'pending',
        0
    ),
    (
        108,
        12,
        108,
        'Anika Sultana',
        5,
        'Perfect for evenings',
        'Rich amber notes with a smooth vanilla finish.',
        'approved',
        0
    ),
    (
        109,
        13,
        109,
        'Imran Kabir',
        4,
        'Fresh fig',
        'A relaxed green scent that feels different.',
        'pending',
        0
    ),
    (
        110,
        14,
        110,
        'Mehjabin Akter',
        5,
        'Delicate rose',
        'A beautiful rose fragrance that is not overpowering.',
        'approved',
        0
    );

-- Add 9 discount codes (the original dump already has WELCOME10).
INSERT INTO
    discounts (
        id,
        code,
        discount_type,
        discount_value,
        minimum_purchase,
        max_discount,
        first_order_only,
        starts_at,
        ends_at,
        usage_limit,
        usage_count,
        is_active
    )
VALUES
    (
        2,
        'SEREIN5',
        'percentage',
        5,
        1000,
        300,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        100,
        0,
        1
    ),
    (
        3,
        'WELCOME200',
        'fixed',
        200,
        2000,
        200,
        1,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        100,
        0,
        1
    ),
    (
        4,
        'FRIDAY10',
        'percentage',
        10,
        2500,
        500,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        50,
        0,
        1
    ),
    (
        5,
        'GIFT250',
        'fixed',
        250,
        3000,
        250,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        50,
        0,
        1
    ),
    (
        6,
        'DISCOVERY8',
        'percentage',
        8,
        1500,
        350,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        100,
        0,
        1
    ),
    (
        7,
        'HOUSE15',
        'percentage',
        15,
        4000,
        700,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        30,
        0,
        1
    ),
    (
        8,
        'FIRST300',
        'fixed',
        300,
        2500,
        300,
        1,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        100,
        0,
        1
    ),
    (
        9,
        'SPRING12',
        'percentage',
        12,
        3500,
        600,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        40,
        0,
        1
    ),
    (
        10,
        'SLOWDAY100',
        'fixed',
        100,
        1800,
        100,
        0,
        NOW (),
        DATE_ADD (NOW (), INTERVAL 1 YEAR),
        100,
        0,
        1
    );

INSERT INTO
    discount_usage (id, discount_id, order_id, phone_normalized)
VALUES
    (101, 2, 101, '01710000001'),
    (102, 3, 102, '01710000002'),
    (103, 4, 103, '01710000003'),
    (104, 5, 104, '01710000004'),
    (105, 6, 105, '01710000005'),
    (106, 7, 106, '01710000006'),
    (107, 8, 107, '01710000007'),
    (108, 9, 108, '01710000008'),
    (109, 10, 109, '01710000009'),
    (110, 1, 110, '01710000010');

INSERT INTO
    wishlist_items (id, customer_id, product_id)
VALUES
    (101, 101, 2),
    (102, 102, 3),
    (103, 103, 5),
    (104, 104, 6),
    (105, 105, 7),
    (106, 106, 8),
    (107, 107, 9),
    (108, 108, 10),
    (109, 109, 11),
    (110, 110, 12);

-- Add 6 banners (the original dump already has 4).
INSERT INTO
    banners (
        id,
        title,
        subtitle,
        image_path,
        cta_label,
        cta_url,
        placement,
        sort_order,
        is_active
    )
VALUES
    (
        5,
        'A softer kind of statement',
        'Discover floral compositions for every day.',
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1600&q=85',
        'Explore florals',
        '?page=shop',
        'promo',
        3,
        1
    ),
    (
        6,
        'The art of a quiet evening',
        'Warm woods and amber, made to linger.',
        'https://images.unsplash.com/photo-1590736969955-71cc94901144?auto=format&fit=crop&w=1600&q=85',
        'Find your scent',
        '?page=mood',
        'promo',
        4,
        1
    ),
    (
        7,
        'A thoughtful gift',
        'A considered fragrance for someone close.',
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=1600&q=85',
        'Discover gifts',
        '?page=discovery',
        'promo',
        5,
        1
    ),
    (
        8,
        'Meet the new arrivals',
        'Fresh notes, new stories.',
        'https://images.unsplash.com/photo-1595425970377-c9703cf48b6d?auto=format&fit=crop&w=1600&q=85',
        'Shop new arrivals',
        '?page=shop&sort=newest',
        'promo',
        6,
        1
    ),
    (
        9,
        'A little welcome from us',
        'Enjoy a considered first order.',
        'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=1600&q=85',
        'Discover the house',
        '?page=shop',
        'popup',
        2,
        1
    ),
    (
        10,
        'The Last Note signature',
        'Find the fragrance that feels like yours.',
        'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=1600&q=85',
        'Explore',
        '?page=shop',
        'promo',
        7,
        1
    );

-- Add 2 sections (the original dump already has 8).
INSERT INTO
    homepage_sections (
        section_key,
        heading,
        subtitle,
        collection_key,
        sort_order,
        is_active
    )
VALUES
    (
        'discovery-edit',
        'The discovery edit',
        'A first meeting with the house.',
        'discovery',
        9,
        1
    ),
    (
        'gift-edit',
        'Considered gifts',
        'Small gestures, beautifully chosen.',
        'gift',
        10,
        1
    );

-- Add 8 social links (the original dump already has 2).
INSERT INTO
    social_links (id, platform, url, sort_order, is_active)
VALUES
    (
        3,
        'Pinterest',
        'https://www.pinterest.com/',
        3,
        1
    ),
    (4, 'TikTok', 'https://www.tiktok.com/', 4, 1),
    (5, 'YouTube', 'https://www.youtube.com/', 5, 1),
    (6, 'LinkedIn', 'https://www.linkedin.com/', 6, 1),
    (7, 'Threads', 'https://www.threads.net/', 7, 1),
    (
        8,
        'Instagram Shop',
        'https://www.instagram.com/',
        8,
        1
    ),
    (
        9,
        'Facebook Page',
        'https://www.facebook.com/',
        9,
        1
    ),
    (
        10,
        'WhatsApp',
        'https://www.whatsapp.com/',
        10,
        1
    );

-- 10 settings are already present in the original dump; add 10 useful settings.
INSERT INTO
    store_settings (setting_key, setting_value)
VALUES
    ('store_name', 'THE LAST NOTE BD'),
    ('store_tagline', 'Fragrance, considered.'),
    ('store_address', 'Dhaka, Bangladesh'),
    ('currency', 'BDT'),
    ('free_delivery_threshold', '5000'),
    ('newsletter_enabled', '1'),
    ('popup_enabled', '1'),
    ('popup_delay_seconds', '4'),
    (
        'default_meta_title',
        'THE LAST NOTE BD | AUTHENTIC FRAGRANCE DECANT HOUSE'
    ),
    (
        'default_meta_description',
        'Discover authentic fragrance decants from THE LAST NOTE BD.'
    );

-- Add 9 limited drops (the original dump already has one).
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
        5,
        DATE_SUB (NOW (), INTERVAL 1 DAY),
        DATE_ADD (NOW (), INTERVAL 20 DAY),
        20,
        0,
        'active'
    ),
    (
        6,
        DATE_SUB (NOW (), INTERVAL 1 DAY),
        DATE_ADD (NOW (), INTERVAL 20 DAY),
        18,
        0,
        'active'
    ),
    (
        7,
        DATE_ADD (NOW (), INTERVAL 5 DAY),
        DATE_ADD (NOW (), INTERVAL 30 DAY),
        12,
        1,
        'scheduled'
    ),
    (
        8,
        DATE_SUB (NOW (), INTERVAL 2 DAY),
        DATE_ADD (NOW (), INTERVAL 15 DAY),
        15,
        0,
        'active'
    ),
    (
        9,
        DATE_ADD (NOW (), INTERVAL 7 DAY),
        DATE_ADD (NOW (), INTERVAL 35 DAY),
        14,
        1,
        'scheduled'
    ),
    (
        10,
        DATE_SUB (NOW (), INTERVAL 1 DAY),
        DATE_ADD (NOW (), INTERVAL 12 DAY),
        24,
        0,
        'active'
    ),
    (
        11,
        DATE_ADD (NOW (), INTERVAL 10 DAY),
        DATE_ADD (NOW (), INTERVAL 40 DAY),
        10,
        1,
        'scheduled'
    ),
    (
        12,
        DATE_SUB (NOW (), INTERVAL 1 DAY),
        DATE_ADD (NOW (), INTERVAL 10 DAY),
        8,
        0,
        'active'
    ),
    (
        13,
        DATE_ADD (NOW (), INTERVAL 14 DAY),
        DATE_ADD (NOW (), INTERVAL 45 DAY),
        17,
        1,
        'scheduled'
    );

INSERT INTO
    preorders (
        id,
        order_id,
        product_id,
        expected_ship_date,
        status
    )
VALUES
    (
        101,
        101,
        1,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'fulfilled'
    ),
    (
        102,
        102,
        2,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'fulfilled'
    ),
    (
        103,
        103,
        7,
        DATE_ADD (CURDATE (), INTERVAL 10 DAY),
        'reserved'
    ),
    (
        104,
        104,
        8,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'fulfilled'
    ),
    (
        105,
        105,
        9,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'fulfilled'
    ),
    (
        106,
        106,
        10,
        DATE_ADD (CURDATE (), INTERVAL 5 DAY),
        'reserved'
    ),
    (
        107,
        107,
        11,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'cancelled'
    ),
    (
        108,
        108,
        12,
        DATE_ADD (CURDATE (), INTERVAL 10 DAY),
        'reserved'
    ),
    (
        109,
        109,
        13,
        DATE_ADD (CURDATE (), INTERVAL 10 DAY),
        'reserved'
    ),
    (
        110,
        110,
        14,
        DATE_ADD (CURDATE (), INTERVAL 3 DAY),
        'fulfilled'
    );

INSERT INTO
    notifications (id, title, body, notification_type, is_read)
VALUES
    (
        101,
        'New order received',
        'Demo order MS-DEMO-1001 was placed.',
        'order',
        0
    ),
    (
        102,
        'Payment verified',
        'Demo payment for MS-DEMO-1002 is verified.',
        'payment',
        1
    ),
    (
        103,
        'Payment awaiting review',
        'Demo payment for MS-DEMO-1003 needs verification.',
        'payment',
        0
    ),
    (
        104,
        'Order shipped',
        'Demo order MS-DEMO-1004 has shipped.',
        'order',
        0
    ),
    (
        105,
        'New review submitted',
        'A customer left a review for Forest Veil.',
        'review',
        0
    ),
    (
        106,
        'Low stock notice',
        'Serein No. 20 has limited stock in the demo catalogue.',
        'inventory',
        0
    ),
    (
        107,
        'New subscriber',
        'A new newsletter subscriber joined.',
        'newsletter',
        1
    ),
    (
        108,
        'Order cancelled',
        'Demo order MS-DEMO-1007 was cancelled.',
        'order',
        1
    ),
    (
        109,
        'New wishlist activity',
        'A customer saved a fragrance to their wishlist.',
        'customer',
        0
    ),
    (
        110,
        'Limited release',
        'A limited fragrance release is scheduled.',
        'product',
        0
    );

-- Admin rows are inactive, with deliberately unusable placeholder hashes.
-- Use scripts/create-admin.php to create your real administrator account.
INSERT INTO
    admins (id, full_name, email, password_hash, is_active)
VALUES
    (
        101,
        'Demo Admin One',
        'demo.admin1@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        102,
        'Demo Admin Two',
        'demo.admin2@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        103,
        'Demo Admin Three',
        'demo.admin3@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        104,
        'Demo Admin Four',
        'demo.admin4@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        105,
        'Demo Admin Five',
        'demo.admin5@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        106,
        'Demo Admin Six',
        'demo.admin6@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        107,
        'Demo Admin Seven',
        'demo.admin7@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        108,
        'Demo Admin Eight',
        'demo.admin8@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        109,
        'Demo Admin Nine',
        'demo.admin9@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    ),
    (
        110,
        'Demo Admin Ten',
        'demo.admin10@example.test',
        '$2y$10$PLACEHOLDER_HASH_NOT_FOR_LOGIN_000000000000000000000000000000000000000000000000000000',
        0
    );

INSERT INTO
    activity_logs (
        id,
        admin_id,
        action,
        entity_type,
        entity_id,
        details,
        ip_address
    )
VALUES
    (
        101,
        NULL,
        'seed_demo_order',
        'order',
        101,
        '{"source":"demo seed","order":"MS-DEMO-1001"}',
        '127.0.0.1'
    ),
    (
        102,
        NULL,
        'seed_demo_order',
        'order',
        102,
        '{"source":"demo seed","order":"MS-DEMO-1002"}',
        '127.0.0.1'
    ),
    (
        103,
        NULL,
        'seed_demo_payment',
        'payment',
        103,
        '{"source":"demo seed","status":"pending_verification"}',
        '127.0.0.1'
    ),
    (
        104,
        NULL,
        'seed_demo_product',
        'product',
        5,
        '{"source":"demo seed","name":"Neroli Morning"}',
        '127.0.0.1'
    ),
    (
        105,
        NULL,
        'seed_demo_product',
        'product',
        6,
        '{"source":"demo seed","name":"Velvet Peony"}',
        '127.0.0.1'
    ),
    (
        106,
        NULL,
        'seed_demo_review',
        'review',
        101,
        '{"source":"demo seed","status":"approved"}',
        '127.0.0.1'
    ),
    (
        107,
        NULL,
        'seed_demo_inventory',
        'variant',
        13,
        '{"source":"demo seed","stock":20}',
        '127.0.0.1'
    ),
    (
        108,
        NULL,
        'seed_demo_banner',
        'banner',
        5,
        '{"source":"demo seed","placement":"promo"}',
        '127.0.0.1'
    ),
    (
        109,
        NULL,
        'seed_demo_discount',
        'discount',
        2,
        '{"source":"demo seed","code":"SEREIN5"}',
        '127.0.0.1'
    ),
    (
        110,
        NULL,
        'seed_demo_newsletter',
        'subscriber',
        101,
        '{"source":"demo seed"}',
        '127.0.0.1'
    );

INSERT INTO
    newsletter_subscribers (email, status)
VALUES
    ('subscriber01@example.test', 'subscribed'),
    ('subscriber02@example.test', 'subscribed'),
    ('subscriber03@example.test', 'subscribed'),
    ('subscriber04@example.test', 'subscribed'),
    ('subscriber05@example.test', 'subscribed'),
    ('subscriber06@example.test', 'unsubscribed'),
    ('subscriber07@example.test', 'subscribed'),
    ('subscriber08@example.test', 'subscribed'),
    ('subscriber09@example.test', 'unsubscribed'),
    ('subscriber10@example.test', 'subscribed');

COMMIT;

-- Notes:
-- * Existing dump contains 4 fragrance products; this adds 16 for 20 total.
-- * Product image URLs are remote Unsplash URLs. For production, download licensed images
--   and store them under assets/uploads/products/, then update product_images.image_path.
-- * Demo customers use .example.test emails and placeholder password hashes; they are not login accounts.
-- * Demo admins are inactive. Create a real admin with: php scripts/create-admin.php
-- * This file is intended for a fresh import of the supplied schema/seed SQL and should be run once.