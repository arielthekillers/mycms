<?php

declare(strict_types=1);

namespace App\Menu;

use App\Core\Database;

/**
 * Resolves a menu_items row into a real URL at render time.
 *
 * Nothing here is precomputed/cached into the menu table — that's the whole
 * point of the Target Provider pattern: if an article's slug changes, the
 * menu link is still correct on the very next render, because we look it
 * up live by target_id, not by a stored path.
 */
final class MenuResolver
{
    /**
     * Internal system routes referenced by menu_items.target_id when
     * target_type = 'system'. target_id here is NOT a foreign key —
     * it's a small stable integer code for a built-in route.
     */
    private const SYSTEM_ROUTES = [
        1 => '/admin/login',
        2 => '/cari',
        3 => '/sitemap.xml',
    ];

    public function __construct(private Database $db)
    {
    }

    /**
     * @param array<string, mixed> $menuItem a row from menu_items
     * @return string the resolved URL, or '#' if the target no longer exists
     */
    public function resolveUrl(array $menuItem): string
    {
        return match ($menuItem['target_type']) {
            'article' => $this->resolveArticle($menuItem['target_id']),
            'category' => $this->resolveCategory($menuItem['target_id']),
            'page' => $this->resolvePage($menuItem['target_id']),
            'archive' => $this->resolveArchive($menuItem['target_id'], $menuItem['target_meta'] ?? null),
            'module' => $this->resolveModuleContent($menuItem['target_id']),
            'system' => self::SYSTEM_ROUTES[(int) $menuItem['target_id']] ?? '#',
            'custom_url' => $menuItem['custom_url'] ?? '#',
            default => '#',
        };
    }

    private function resolveArticle(?int $id): string
    {
        if ($id === null) {
            return '#';
        }

        $row = $this->db->selectOne('SELECT slug FROM articles WHERE id = :id', ['id' => $id]);

        return $row !== null ? "/artikel/{$row['slug']}" : '#';
    }

    private function resolveCategory(?int $id): string
    {
        if ($id === null) {
            return '#';
        }

        $row = $this->db->selectOne('SELECT slug FROM categories WHERE id = :id', ['id' => $id]);

        return $row !== null ? "/kategori/{$row['slug']}" : '#';
    }

    private function resolvePage(?int $id): string
    {
        if ($id === null) {
            return '#';
        }

        $row = $this->db->selectOne('SELECT slug FROM pages WHERE id = :id', ['id' => $id]);

        return $row !== null ? "/halaman/{$row['slug']}" : '#';
    }

    /** target_id here refers to content_modules.id — the module's archive/listing page. */
    private function resolveArchive(?int $moduleId, ?string $targetMeta): string
    {
        if ($moduleId === null) {
            return '#';
        }

        $module = $this->db->selectOne(
            'SELECT base_route FROM content_modules WHERE id = :id AND is_active = 1',
            ['id' => $moduleId]
        );

        if ($module === null) {
            return '#';
        }

        $url = $module['base_route'];
        $meta = $targetMeta !== null ? (json_decode($targetMeta, true) ?: []) : [];

        return $meta === [] ? $url : $url . '?' . http_build_query($meta);
    }

    /** target_id here refers to module_contents.id — a single item's detail page. */
    private function resolveModuleContent(?int $contentId): string
    {
        if ($contentId === null) {
            return '#';
        }

        $row = $this->db->selectOne(
            'SELECT mc.slug, cm.base_route
             FROM module_contents mc
             JOIN content_modules cm ON cm.id = mc.module_id
             WHERE mc.id = :id AND mc.status = "published"',
            ['id' => $contentId]
        );

        return $row !== null ? "{$row['base_route']}/{$row['slug']}" : '#';
    }
}
