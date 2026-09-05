<?php
class Article {
    public static function getBySlug(string $category, string $slug): ?array {
        return Database::fetchOne(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug,
                    u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.slug = ? AND c.slug = ? AND a.status = "published"',
            [$slug, $category]
        );
    }

    public static function getByCategory(string $categorySlug, int $page = 1, string $type = ''): array {
        $offset = ($page - 1) * ARTICLES_PER_PAGE;
        $typeWhere = $type ? 'AND a.article_type = ?' : '';
        $params = array_filter([$categorySlug, $type ?: null]);

        return Database::fetchAll(
            "SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE c.slug = ? AND a.status = 'published' $typeWhere
             ORDER BY a.published_at DESC
             LIMIT " . ARTICLES_PER_PAGE . " OFFSET $offset",
            array_values(array_filter([$categorySlug, $type ?: null]))
        );
    }

    public static function getPopular(int $limit = 6): array {
        return Database::fetchAll(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = "published"
             ORDER BY a.views DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function getRecent(int $limit = 6, string $category = ''): array {
        $where = $category ? 'AND c.slug = ?' : '';
        $params = $category ? [$limit, $category] : [$limit];
        // Need to adjust query based on category
        if ($category) {
            return Database::fetchAll(
                "SELECT a.*, c.name AS category_name, c.slug AS category_slug
                 FROM articles a
                 LEFT JOIN categories c ON a.category_id = c.id
                 WHERE a.status = 'published' AND c.slug = ?
                 ORDER BY a.published_at DESC LIMIT ?",
                [$category, $limit]
            );
        }
        return Database::fetchAll(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = "published"
             ORDER BY a.published_at DESC LIMIT ?',
            [$limit]
        );
    }

    public static function getDiaspora(int $limit = 4): array {
        return self::getRecent($limit, 'diaspora');
    }

    public static function getDigital(int $limit = 4): array {
        return self::getRecent($limit, 'digital-ai');
    }

    public static function search(string $query, int $page = 1): array {
        $offset = ($page - 1) * ARTICLES_PER_PAGE;
        $q = '%' . $query . '%';
        return Database::fetchAll(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = "published"
               AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ? OR a.tags LIKE ?)
             ORDER BY a.views DESC, a.published_at DESC
             LIMIT ' . ARTICLES_PER_PAGE . " OFFSET $offset",
            [$q, $q, $q, $q]
        );
    }

    public static function countSearch(string $query): int {
        $q = '%' . $query . '%';
        return (int)Database::fetchColumn(
            'SELECT COUNT(*) FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = "published"
               AND (a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ? OR a.tags LIKE ?)',
            [$q, $q, $q, $q]
        );
    }

    public static function incrementViews(int $id): void {
        Database::query('UPDATE articles SET views = views + 1 WHERE id = ?', [$id]);
    }

    public static function logSearch(string $query): void {
        $existing = Database::fetchOne(
            'SELECT id, count FROM search_queries WHERE query = ?', [$query]
        );
        if ($existing) {
            Database::update('search_queries', ['count' => $existing['count'] + 1, 'last_searched' => date('Y-m-d H:i:s')], 'id = ?', [$existing['id']]);
        } else {
            Database::insert('search_queries', ['query' => $query, 'count' => 1, 'last_searched' => date('Y-m-d H:i:s')]);
        }
    }

    public static function getRelated(int $articleId, int $categoryId, int $limit = 4): array {
        return Database::fetchAll(
            'SELECT a.*, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = "published" AND a.category_id = ? AND a.id != ?
             ORDER BY a.views DESC
             LIMIT ?',
            [$categoryId, $articleId, $limit]
        );
    }

    public static function needsUpdate(int $limit = 10): array {
        return Database::fetchAll(
            "SELECT a.*, c.name AS category_name
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.status = 'published'
               AND a.review_date IS NOT NULL
               AND a.review_date <= DATE('now')
             ORDER BY a.review_date ASC
             LIMIT ?",
            [$limit]
        );
    }

    public static function autoPublishCheck(array $article): array {
        $errors = [];

        if ($article['risk_level'] !== 'verde') {
            $errors[] = 'Articolul nu este verde — necesită aprobare manuală.';
        }
        if (empty($article['meta_title'])) $errors[] = 'Lipsește meta title.';
        if (empty($article['meta_description'])) $errors[] = 'Lipsește meta description.';
        if (empty($article['slug'])) $errors[] = 'Lipsește slug-ul URL.';
        if (empty($article['category_id'])) $errors[] = 'Lipsește categoria.';
        if (empty($article['review_date'])) $errors[] = 'Lipsește data de reverificare.';

        // Verifică conținut problematic
        $contentLower = mb_strtolower($article['content'] ?? '');
        $forbiddenTerms = ['garantat că', 'sigur vei obține', 'obligatoriu vei primi', 'te asigurăm că'];
        foreach ($forbiddenTerms as $term) {
            if (str_contains($contentLower, $term)) {
                $errors[] = "Conține promisiune interzisă: \"$term\"";
            }
        }

        // Verificare anti-duplicat (titlu identic)
        if (!empty($article['title'])) {
            $similar = Database::fetchColumn(
                'SELECT COUNT(*) FROM articles WHERE LOWER(title) = LOWER(?) AND id != ?',
                [$article['title'], $article['id'] ?? 0]
            );
            if ($similar > 0) {
                $errors[] = 'Există deja un articol cu același titlu — verifică dacă este duplicat.';
            }
        }

        return $errors;
    }
}
