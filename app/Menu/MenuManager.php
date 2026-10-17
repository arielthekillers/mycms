<?php

declare(strict_types=1);

namespace App\Menu;

use App\Core\Database;
use RuntimeException;

final class MenuManager
{
    public function __construct(private Database $db, private MenuResolver $resolver)
    {
    }

    public function createMenu(string $name, string $slug, ?string $location = null): array
    {
        $id = $this->db->insert(
            'INSERT INTO menus (name, slug, location) VALUES (:name, :slug, :location)',
            ['name' => $name, 'slug' => $slug, 'location' => $location]
        );

        return $this->db->selectOne('SELECT * FROM menus WHERE id = :id', ['id' => $id]);
    }

    public function listMenus(): array
    {
        return $this->db->select('SELECT * FROM menus ORDER BY name ASC');
    }

    public function deleteMenu(int $id): void
    {
        $this->db->execute('DELETE FROM menus WHERE id = :id', ['id' => $id]);
    }

    /** Flat (non-tree) list with resolved URLs, for the admin edit screen. */
    public function listItemsFlat(int $menuId): array
    {
        $rows = $this->db->select(
            'SELECT * FROM menu_items WHERE menu_id = :menu_id ORDER BY sort_order ASC',
            ['menu_id' => $menuId]
        );

        return array_map(function (array $item) {
            $item['url'] = $this->resolver->resolveUrl($item);

            return $item;
        }, $rows);
    }

    /**
     * Powers the admin UI's target autocomplete — replaces having to know a
     * raw ID by hand. Each target_type searches its own table by name/title.
     *
     * @return array<int, array{id: int, label: string}>
     */
    public function searchTargets(string $targetType, string $query): array
    {
        $like = '%' . $query . '%';

        return match ($targetType) {
            'article' => $this->db->select(
                "SELECT id, title AS label FROM articles WHERE title LIKE :q ORDER BY title LIMIT 10",
                ['q' => $like]
            ),
            'category' => $this->db->select(
                "SELECT id, name AS label FROM categories WHERE name LIKE :q ORDER BY name LIMIT 10",
                ['q' => $like]
            ),
            'page' => $this->db->select(
                "SELECT id, title AS label FROM pages WHERE title LIKE :q ORDER BY title LIMIT 10",
                ['q' => $like]
            ),
            'archive' => $this->db->select(
                "SELECT id, label_plural AS label FROM content_modules
                 WHERE is_active = 1 AND has_archive = 1 AND label_plural LIKE :q
                 ORDER BY label_plural LIMIT 10",
                ['q' => $like]
            ),
            'module' => $this->db->select(
                "SELECT mc.id, CONCAT(cm.label_singular, ': ', mc.title) AS label
                 FROM module_contents mc
                 JOIN content_modules cm ON cm.id = mc.module_id
                 WHERE mc.title LIKE :q
                 ORDER BY mc.title LIMIT 10",
                ['q' => $like]
            ),
            // 'system' is a small fixed list (see MenuResolver::SYSTEM_ROUTES), not a DB search.
            'system' => array_values(array_filter([
                ['id' => 1, 'label' => 'Login Admin'],
                ['id' => 2, 'label' => 'Cari'],
                ['id' => 3, 'label' => 'Sitemap'],
            ], fn ($opt) => $query === '' || stripos($opt['label'], $query) !== false)),
            default => [],
        };
    }

    public function addItem(int $menuId, array $data): array
    {
        if (empty($data['label']) || empty($data['target_type'])) {
            throw new RuntimeException('label and target_type are required.');
        }

        $sortOrder = $data['sort_order'] ?? null;
        if ($sortOrder === null) {
            $row = $this->db->selectOne(
                'SELECT COALESCE(MAX(sort_order), 0) as max_ord FROM menu_items WHERE menu_id = :menu_id AND parent_id IS NULL',
                ['menu_id' => $menuId]
            );
            $sortOrder = (int) ($row['max_ord'] ?? 0) + 1;
        }

        $id = $this->db->insert(
            'INSERT INTO menu_items
                (menu_id, parent_id, label, icon, target_type, target_id, target_meta, custom_url, open_new_tab, sort_order)
             VALUES
                (:menu_id, :parent_id, :label, :icon, :target_type, :target_id, :target_meta, :custom_url, :open_new_tab, :sort_order)',
            [
                'menu_id' => $menuId,
                'parent_id' => $data['parent_id'] ?? null,
                'label' => $data['label'],
                'icon' => $data['icon'] ?? null,
                'target_type' => $data['target_type'],
                'target_id' => $data['target_id'] ?? null,
                'target_meta' => isset($data['target_meta']) ? json_encode($data['target_meta']) : null,
                'custom_url' => $data['custom_url'] ?? null,
                'open_new_tab' => !empty($data['open_new_tab']) ? 1 : 0,
                'sort_order' => $sortOrder,
            ]
        );

        return $this->db->selectOne('SELECT * FROM menu_items WHERE id = :id', ['id' => $id]);
    }

    public function updateItem(int $itemId, array $data): array
    {
        $existing = $this->db->selectOne('SELECT * FROM menu_items WHERE id = :id', ['id' => $itemId]);

        if ($existing === null) {
            throw new RuntimeException("Menu item #{$itemId} not found.");
        }

        $this->db->execute(
            'UPDATE menu_items SET
                label = :label, icon = :icon, target_type = :target_type, target_id = :target_id,
                target_meta = :target_meta, custom_url = :custom_url, open_new_tab = :open_new_tab,
                sort_order = :sort_order, is_active = :is_active
             WHERE id = :id',
            [
                'label' => $data['label'] ?? $existing['label'],
                'icon' => $data['icon'] ?? $existing['icon'],
                'target_type' => $data['target_type'] ?? $existing['target_type'],
                'target_id' => array_key_exists('target_id', $data) ? $data['target_id'] : $existing['target_id'],
                'target_meta' => array_key_exists('target_meta', $data)
                    ? json_encode($data['target_meta'])
                    : $existing['target_meta'],
                'custom_url' => array_key_exists('custom_url', $data) ? $data['custom_url'] : $existing['custom_url'],
                'open_new_tab' => array_key_exists('open_new_tab', $data)
                    ? (int) $data['open_new_tab']
                    : $existing['open_new_tab'],
                'sort_order' => $data['sort_order'] ?? $existing['sort_order'],
                'is_active' => array_key_exists('is_active', $data) ? (int) $data['is_active'] : $existing['is_active'],
                'id' => $itemId,
            ]
        );

        return $this->db->selectOne('SELECT * FROM menu_items WHERE id = :id', ['id' => $itemId]);
    }

    public function deleteItem(int $itemId): void
    {
        // Child items cascade-delete via FK ON DELETE CASCADE (parent_id -> menu_items.id).
        $this->db->execute('DELETE FROM menu_items WHERE id = :id', ['id' => $itemId]);
    }

    public function reorderItems(int $menuId, array $items): void
    {
        $this->db->transaction(function (Database $db) use ($menuId, $items) {
            foreach ($items as $item) {
                if (!isset($item['id'])) continue;
                $db->execute(
                    'UPDATE menu_items SET parent_id = :parent_id, sort_order = :sort_order WHERE id = :id AND menu_id = :menu_id',
                    [
                        'parent_id' => !empty($item['parent_id']) ? (int) $item['parent_id'] : null,
                        'sort_order' => (int) ($item['sort_order'] ?? 0),
                        'id' => (int) $item['id'],
                        'menu_id' => $menuId,
                    ]
                );
            }
        });
    }

    /**
     * Public-facing: full resolved tree for a menu, ready for the theme to render.
     * @return array<int, array<string, mixed>>
     */
    public function getResolvedTree(string $menuSlug): array
    {
        $menu = $this->db->selectOne('SELECT * FROM menus WHERE slug = :slug', ['slug' => $menuSlug]);

        if ($menu === null) {
            return [];
        }

        $rows = $this->db->select(
            'SELECT * FROM menu_items WHERE menu_id = :menu_id AND is_active = 1 ORDER BY sort_order ASC',
            ['menu_id' => $menu['id']]
        );

        $resolved = array_map(function (array $item) {
            $item['url'] = $this->resolver->resolveUrl($item);
            unset($item['target_meta']); // internal detail, not needed by the theme layer

            return $item;
        }, $rows);

        return $this->buildTree($resolved);
    }

    /** @param array<int, array<string, mixed>> $items */
    private function buildTree(array $items, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, (int) $item['id']);

                if ($children !== []) {
                    $item['children'] = $children;
                }

                $branch[] = $item;
            }
        }

        return $branch;
    }
}
