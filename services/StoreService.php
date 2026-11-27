<?php
declare(strict_types=1);

final class StoreService
{
    public function __construct(private readonly PDO $pdo) {}

    public function products(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        [$where, $params] = $this->productFilterConditions($filters);
        $sort = match ($filters['sort'] ?? '') {
            'price-asc' => 'min_price ASC, p.name ASC',
            'price-desc' => 'min_price DESC, p.name ASC',
            'popular' => 'p.sales_count DESC, p.created_at DESC',
            'new' => 'p.is_new_arrival DESC, p.created_at DESC',
            default => 'p.is_featured DESC, p.created_at DESC',
        };
        $sql = 'SELECT p.id, p.name, p.slug, p.gender, p.short_description, p.is_featured, p.is_best_seller, p.is_new_arrival, b.name AS brand_name,
            (SELECT id FROM product_variants av WHERE av.product_id = p.id AND av.is_active = 1 AND av.stock_quantity > 0 ORDER BY av.sort_order, av.id LIMIT 1) AS available_variant_id,
            (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS image_path,
            (SELECT MIN(price) FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_active = 1) AS min_price,
            (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id AND r.status = "approved") AS average_rating,
            (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status = "approved") AS review_count
            FROM products p JOIN brands b ON b.id = p.brand_id LEFT JOIN categories c ON c.id = p.category_id
            WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $sort . ' LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, min($limit, 48)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countProducts(array $filters = []): int
    {
        [$where, $params] = $this->productFilterConditions($filters);
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products p
            JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE ' . implode(' AND ', $where));
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function productFilterConditions(array $filters): array
    {
        $where = ['p.is_active = 1', 'p.is_visible = 1'];
        $params = [];
        $search = trim((string) ($filters['search'] ?? ''));
        if (mb_strlen($search) > 100) throw new InvalidArgumentException('Search must be 100 characters or fewer.');
        if (!empty($filters['sort']) && !in_array($filters['sort'], ['featured', 'popular', 'new', 'price-asc', 'price-desc'], true)) {
            throw new InvalidArgumentException('Choose a valid sort order.');
        }
        if ($search !== '') {
            $where[] = '(p.name LIKE :search_name OR b.name LIKE :search_brand OR
                COALESCE(p.fragrance_family, "") LIKE :search_family OR
                COALESCE(p.short_description, "") LIKE :search_description OR
                COALESCE(p.top_notes, "") LIKE :search_top OR
                COALESCE(p.heart_notes, "") LIKE :search_heart OR
                COALESCE(p.base_notes, "") LIKE :search_base OR
                p.description LIKE :search_full_description)';
            $term = '%' . $search . '%';
            $params['search_name'] = $term;
            $params['search_brand'] = $term;
            $params['search_family'] = $term;
            $params['search_description'] = $term;
            $params['search_top'] = $term;
            $params['search_heart'] = $term;
            $params['search_base'] = $term;
            $params['search_full_description'] = $term;
        }
        if (!empty($filters['gender'])) {
            if (!in_array($filters['gender'], ['women', 'men', 'unisex'], true)) throw new InvalidArgumentException('Choose a valid audience filter.');
            $where[] = 'p.gender = :gender';
            $params['gender'] = $filters['gender'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['brand'])) {
            $where[] = 'b.slug = :brand';
            $params['brand'] = $filters['brand'];
        }
        if (($filters['availability'] ?? '') !== '' && ($filters['availability'] ?? '') !== 'in-stock') {
            throw new InvalidArgumentException('Choose a valid availability filter.');
        }
        if (($filters['availability'] ?? '') === 'in-stock') {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants av WHERE av.product_id = p.id AND av.is_active = 1 AND av.stock_quantity > 0)';
        }
        foreach (['featured' => 'is_featured', 'best_seller' => 'is_best_seller', 'new_arrival' => 'is_new_arrival'] as $filter => $column) {
            if (!empty($filters[$filter])) $where[] = 'p.' . $column . ' = 1';
        }
        if (!empty($filters['mood']) && in_array($filters['mood'], ['romantic', 'fresh', 'elegant', 'office', 'party', 'casual', 'formal', 'special-occasion'], true)) {
            $where[] = 'JSON_CONTAINS(p.mood_tags, JSON_QUOTE(:mood))';
            $params['mood'] = $filters['mood'];
        }
        if (!empty($filters['moods']) && is_array($filters['moods'])) {
            $allowedMoods = ['romantic', 'fresh', 'elegant', 'office', 'party', 'casual', 'formal', 'special-occasion'];
            $moods = array_values(array_intersect($filters['moods'], $allowedMoods));
            if ($moods) {
                $moodConditions = [];
                foreach ($moods as $index => $mood) {
                    $parameter = 'finder_mood_' . $index;
                    $moodConditions[] = 'JSON_CONTAINS(p.mood_tags, JSON_QUOTE(:' . $parameter . '))';
                    $params[$parameter] = $mood;
                }
                $where[] = '(' . implode(' OR ', $moodConditions) . ')';
            }
        }
        if (!empty($filters['scent_terms']) && is_array($filters['scent_terms'])) {
            $scentColumns = ['fragrance_family', 'top_notes', 'heart_notes', 'base_notes'];
            $scentConditions = [];
            foreach (array_values($filters['scent_terms']) as $termIndex => $term) {
                if (!is_string($term) || $term === '') continue;
                $columnConditions = [];
                foreach ($scentColumns as $column) {
                    $parameter = 'finder_scent_' . $termIndex . '_' . $column;
                    $columnConditions[] = 'COALESCE(p.' . $column . ', "") LIKE :' . $parameter;
                    $params[$parameter] = '%' . $term . '%';
                }
                $scentConditions[] = '(' . implode(' OR ', $columnConditions) . ')';
            }
            if ($scentConditions) $where[] = '(' . implode(' OR ', $scentConditions) . ')';
        }
        $minimum = $this->validatedPrice($filters['min_price'] ?? null, 'Minimum');
        $maximum = $this->validatedPrice($filters['max_price'] ?? null, 'Maximum');
        if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
            throw new InvalidArgumentException('Minimum price cannot exceed maximum price.');
        }
        if ($minimum !== null && $maximum !== null) {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants rangev WHERE rangev.product_id = p.id AND rangev.is_active = 1 AND rangev.price >= :min_price AND rangev.price <= :max_price)';
            $params['min_price'] = $minimum;
            $params['max_price'] = $maximum;
        } elseif ($minimum !== null) {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants minv WHERE minv.product_id = p.id AND minv.is_active = 1 AND minv.price >= :min_price)';
            $params['min_price'] = $minimum;
        } elseif ($maximum !== null) {
            $where[] = 'EXISTS (SELECT 1 FROM product_variants maxv WHERE maxv.product_id = p.id AND maxv.is_active = 1 AND maxv.price <= :max_price)';
            $params['max_price'] = $maximum;
        }
        if (!empty($filters['exclude_id']) && filter_var($filters['exclude_id'], FILTER_VALIDATE_INT)) {
            $where[] = 'p.id <> :exclude_id';
            $params['exclude_id'] = (int) $filters['exclude_id'];
        }
        return [$where, $params];
    }

    private function validatedPrice(mixed $value, string $label): ?float
    {
        if ($value === null || $value === '') return null;
        $validated = is_scalar($value) ? filter_var((string) $value, FILTER_VALIDATE_FLOAT) : false;
        if ($validated === false || $validated < 0 || $validated > 10000000) {
            throw new InvalidArgumentException($label . ' price must be a valid amount between 0 and 10,000,000.');
        }
        return (float) $validated;
    }

    public function product(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT p.*, b.name AS brand_name, c.name AS category_name, c.slug AS category_slug,
            EXISTS(SELECT 1 FROM limited_drops d WHERE d.product_id = p.id AND d.status = "active" AND d.allow_preorder = 1 AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())) AS preorder_available,
            (SELECT AVG(r.rating) FROM reviews r WHERE r.product_id = p.id AND r.status = "approved") AS average_rating,
            (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.status = "approved") AS review_count
            FROM products p JOIN brands b ON b.id = p.brand_id LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id = :id AND p.is_active = 1 AND p.is_visible = 1 LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        if (!$product) return null;
        $images = $this->pdo->prepare('SELECT image_path, alt_text FROM product_images WHERE product_id = :id ORDER BY sort_order, id');
        $images->execute(['id' => $id]);
        $product['images'] = $images->fetchAll();
        $variants = $this->pdo->prepare('SELECT id, size_ml, size_label, price, stock_quantity FROM product_variants WHERE product_id = :id AND is_active = 1 ORDER BY sort_order, size_ml');
        $variants->execute(['id' => $id]);
        $product['variants'] = $variants->fetchAll();
        $product['reviews'] = $this->productReviews($id, 8);
        return $product;
    }

    public function cartItems(array $cart): array
    {
        if (!$cart) return [];
        $ids = array_values(array_filter(array_map('intval', array_keys($cart)), fn ($id) => $id > 0));
        if (!$ids) return [];
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT v.id AS variant_id, v.size_label, v.price, v.stock_quantity, p.id AS product_id, p.name, p.slug,
            EXISTS(SELECT 1 FROM limited_drops d WHERE d.product_id = p.id AND d.status = 'active' AND d.allow_preorder = 1 AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())) AS preorder_available,
            (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.sort_order, pi.id LIMIT 1) AS image_path
            FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id IN ($marks) AND v.is_active = 1 AND p.is_active = 1 AND p.is_visible = 1");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();
        $variantOptions = $this->pdo->prepare('SELECT v.id, v.size_label, v.price, v.stock_quantity,
            EXISTS(SELECT 1 FROM limited_drops d WHERE d.product_id = v.product_id AND d.status = "active" AND d.allow_preorder = 1 AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())) AS preorder_available
            FROM product_variants v WHERE v.product_id = :product AND v.is_active = 1 ORDER BY v.sort_order, v.size_ml');
        foreach ($rows as &$row) {
            $row['quantity'] = max(1, (int) ($cart[$row['variant_id']] ?? 1));
            $row['line_total'] = (float) $row['price'] * $row['quantity'];
            $variantOptions->execute(['product' => $row['product_id']]);
            $row['variants'] = $variantOptions->fetchAll();
        }
        return $rows;
    }

    public function categories(): array
    {
        return $this->pdo->query('SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order, name')->fetchAll();
    }

    public function brands(): array
    {
        return $this->pdo->query('SELECT id, name, slug FROM brands WHERE is_active = 1 ORDER BY name')->fetchAll();
    }

    public function settings(): array
    {
        $result = [];
        foreach ($this->pdo->query('SELECT setting_key, setting_value FROM store_settings') as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function deliveryZones(): array
    {
        return $this->pdo->query('SELECT id, name, delivery_fee, estimated_days FROM delivery_zones WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public function banners(string $placement): array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, subtitle, image_path, cta_label, cta_url FROM banners WHERE placement = :placement AND is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (ends_at IS NULL OR ends_at >= NOW()) ORDER BY sort_order, id');
        $stmt->execute(['placement' => $placement]);
        return $stmt->fetchAll();
    }

    public function homepageSections(): array
    {
        return $this->pdo->query('SELECT section_key, heading, subtitle, collection_key, sort_order FROM homepage_sections WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
    }

    public function reviews(int $limit = 8): array
    {
        $stmt = $this->pdo->prepare('SELECT r.rating, r.review_title, r.review_body, r.customer_name, p.name AS product_name
            FROM reviews r JOIN products p ON p.id = r.product_id WHERE r.status = "approved" AND r.is_featured = 1 ORDER BY r.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function productReviews(int $productId, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare('SELECT rating, review_title, review_body, customer_name, created_at FROM reviews
            WHERE product_id = :product AND status = "approved" ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':product', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(30, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function socialLinks(): array
    {
        return $this->pdo->query('SELECT platform, url FROM social_links WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public function customerAddresses(int $customerId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, label, recipient_name, phone, district, address, is_default FROM customer_addresses WHERE customer_id = :id ORDER BY is_default DESC, created_at DESC');
        $stmt->execute(['id' => $customerId]);
        return $stmt->fetchAll();
    }

    public function limitedDrops(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare('SELECT d.id, d.release_at, d.end_at, d.stock_limit, d.allow_preorder, p.id AS product_id,
            p.name, p.slug, p.short_description, b.name AS brand_name,
            (SELECT image_path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image_path,
            (SELECT MIN(price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1) AS min_price,
            (SELECT id FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1 AND (v.stock_quantity > 0 OR d.allow_preorder = 1) ORDER BY v.sort_order, v.id LIMIT 1) AS available_variant_id
            FROM limited_drops d JOIN products p ON p.id = d.product_id JOIN brands b ON b.id = p.brand_id
            WHERE d.status = "active" AND d.release_at <= NOW() AND (d.end_at IS NULL OR d.end_at >= NOW())
            AND p.is_active = 1 AND p.is_visible = 1 ORDER BY d.release_at LIMIT :limit');
        $stmt->bindValue(':limit', max(1, min(12, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function moodProducts(string $mood, int $limit = 8): array
    {
        $allowed = ['romantic', 'fresh', 'elegant', 'office', 'party', 'casual', 'formal', 'special-occasion'];
        if (!in_array($mood, $allowed, true)) return [];
        return $this->products(['mood' => $mood], $limit);
    }

    public function scentFinderProducts(string $profile, string $occasion, int $limit = 8): array
    {
        $profiles = [
            'fresh' => ['fresh', 'citrus', 'aromatic', 'bergamot', 'lime', 'orange', 'neroli', 'basil'],
            'floral' => ['floral', 'rose', 'peony', 'jasmine', 'petal'],
            'woody' => ['woody', 'wood', 'cedar', 'sandalwood', 'vetiver'],
            'oriental' => ['amber', 'oud', 'incense', 'saffron', 'spice', 'musk'],
            'citrus' => ['citrus', 'bergamot', 'mandarin', 'orange', 'lime', 'neroli'],
            'sweet' => ['vanilla', 'pear', 'amber'],
            'spicy' => ['spicy', 'spice', 'pepper', 'cardamom', 'saffron'],
        ];
        $occasions = [
            '' => [],
            'everyday' => ['casual'],
            'office' => ['office'],
            'evening' => ['party'],
            'special-occasion' => ['formal', 'special-occasion'],
            'gift' => [],
        ];
        if (!isset($profiles[$profile]) || !array_key_exists($occasion, $occasions)) return [];
        return $this->products([
            'scent_terms' => $profiles[$profile],
            'moods' => $occasions[$occasion],
        ], $limit);
    }
}
