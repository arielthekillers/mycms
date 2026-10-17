<?php

declare(strict_types=1);

namespace App\Content;

use App\Core\Database;

/**
 * The counterpart to MenuResolver: given an incoming request path, find
 * what content it points to. Used as the Router's fallback so /agenda,
 * /agenda/{slug}, /halaman/{slug}, /kategori/{slug} etc. don't need one
 * explicit route each — new modules "just work" once registered in
 * content_modules, with zero router changes.
 *
 * @return array{type: string, data: array}|null null means "not found"
 */
final class PublicContentResolver
{
    public function __construct(private Database $db, private PageManager $pages)
    {
    }

    public function resolve(string $path): ?array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn ($s) => $s !== ''));

        if ($segments === []) {
            return null; // homepage — handled by an explicit route, not this resolver
        }

        if ($segments[0] === 'halaman' && count($segments) === 2) {
            return $this->resolvePage($segments[1]);
        }

        if ($segments[0] === 'kategori' && count($segments) === 2) {
            return $this->resolveCategoryArchive($segments[1]);
        }

        return null;
    }

    private function resolvePage(string $slug): ?array
    {
        $page = $this->pages->findBySlug($slug, publishedOnly: true);

        return $page !== null ? ['type' => 'page', 'data' => ['page' => $page]] : null;
    }

    private function resolveCategoryArchive(string $slug): ?array
    {
        $category = $this->db->selectOne('SELECT * FROM categories WHERE slug = :slug', ['slug' => $slug]);

        if ($category === null) {
            return null;
        }

        $articles = $this->db->select(
            "SELECT a.* FROM articles a
             JOIN article_categories ac ON ac.article_id = a.id
             WHERE ac.category_id = :category_id AND a.status = 'published'
             ORDER BY a.published_at DESC",
            ['category_id' => $category['id']]
        );

        return ['type' => 'category_archive', 'data' => ['category' => $category, 'articles' => $articles]];
    }

}
