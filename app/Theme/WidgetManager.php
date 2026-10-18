<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Cache\CacheInterface;
use App\Core\Database;
use RuntimeException;

final class WidgetManager
{
    public function __construct(
        private Database $db,
        private ?CacheInterface $cache = null,
        private ?ThemeManager $themeManager = null
    ) {
        if ($this->themeManager === null && class_exists(\App\Core\Application::class)) {
            try {
                $this->themeManager = \App\Core\Application::getInstance()->themeManager();
            } catch (\Throwable $e) {
            }
        }
    }

    public function getActiveThemeMeta(?string $themeKey = null): array
    {
        $themeKey = $themeKey ?: $this->resolveActiveThemeKey();
        if ($this->themeManager !== null) {
            return $this->themeManager->getThemeMeta($themeKey);
        }

        return ['key' => $themeKey, 'name' => ucfirst($themeKey)];
    }

    private function resolveActiveThemeKey(): string
    {
        if ($this->themeManager !== null) {
            return $this->themeManager->getActiveThemeKey();
        }

        try {
            $active = $this->db->selectOne("SELECT key_name FROM themes WHERE is_active = 1 LIMIT 1");
            return $active['key_name'] ?? 'pesantren-default';
        } catch (\Throwable $e) {
            return 'pesantren-default';
        }
    }

    public function listWidgetTypes(): array
    {
        $types = $this->db->select('SELECT * FROM widgets ORDER BY label ASC');
        foreach ($types as &$type) {
            $class = $type['provider'];
            $type['form_fields'] = [];
            if (class_exists($class) && is_a($class, WidgetInterface::class, true)) {
                $widget = new $class();
                $type['form_fields'] = $widget->getFormFields();
            }
        }
        return $types;
    }

    public function listWidgetAreas(?string $themeKey = null): array
    {
        $themeKey = $themeKey ?: $this->resolveActiveThemeKey();

        $themeManager = $this->themeManager;
        if ($themeManager === null && class_exists(\App\Core\Application::class)) {
            try {
                $themeManager = \App\Core\Application::getInstance()->themeManager();
            } catch (\Throwable $e) {
            }
        }

        if ($themeManager !== null) {
            $themeManager->syncThemeWidgetAreas($themeKey);
            $themeAreas = $themeManager->getThemeWidgetAreas($themeKey);

            if (!empty($themeAreas)) {
                $supportedKeys = array_column($themeAreas, 'key_name');
                $inClause = implode(',', array_fill(0, count($supportedKeys), '?'));

                $rows = $this->db->select(
                    "SELECT * FROM widget_areas WHERE key_name IN ($inClause)",
                    $supportedKeys
                );

                // Preserve exact ordering defined by the theme and ensure label matches theme definition
                $keyOrder = array_flip($supportedKeys);
                $themeAreaMap = [];
                foreach ($themeAreas as $ta) {
                    $themeAreaMap[$ta['key_name']] = $ta;
                }

                usort($rows, function ($a, $b) use ($keyOrder) {
                    $orderA = $keyOrder[$a['key_name']] ?? 999;
                    $orderB = $keyOrder[$b['key_name']] ?? 999;
                    return $orderA <=> $orderB;
                });

                foreach ($rows as &$row) {
                    if (!empty($themeAreaMap[$row['key_name']]['label'])) {
                        $row['label'] = $themeAreaMap[$row['key_name']]['label'];
                    }
                }
                unset($row);

                return $rows;
            }
        }

        return $this->db->select('SELECT * FROM widget_areas ORDER BY scope ASC, label ASC');
    }

    public function listInstances(string $areaKey): array
    {
        return $this->db->select(
            'SELECT wi.*, w.key_name AS widget_key, w.label AS widget_label
             FROM widget_instances wi
             JOIN widgets w ON w.id = wi.widget_id
             JOIN widget_areas wa ON wa.id = wi.widget_area_id
             WHERE wa.key_name = :area_key
             ORDER BY wi.sort_order ASC',
            ['area_key' => $areaKey]
        );
    }

    public function createInstance(string $areaKey, array $data): array
    {
        $area = $this->db->selectOne('SELECT id FROM widget_areas WHERE key_name = :key', ['key' => $areaKey]);
        $widget = $this->db->selectOne('SELECT id FROM widgets WHERE key_name = :key', ['key' => $data['widget_key'] ?? '']);

        if ($area === null) {
            throw new RuntimeException("Widget area '{$areaKey}' not found.");
        }

        if ($widget === null) {
            throw new RuntimeException("Widget type '{$data['widget_key']}' not found.");
        }

        // New instances go last by default — sort_order is 1-indexed per area.
        $maxOrder = $this->db->selectOne(
            'SELECT MAX(sort_order) AS max_order FROM widget_instances WHERE widget_area_id = :area_id',
            ['area_id' => $area['id']]
        );

        $id = $this->db->insert(
            'INSERT INTO widget_instances (widget_id, widget_area_id, title_override, settings, sort_order)
             VALUES (:widget_id, :area_id, :title_override, :settings, :sort_order)',
            [
                'widget_id' => $widget['id'],
                'area_id' => $area['id'],
                'title_override' => $data['title_override'] ?? null,
                'settings' => isset($data['settings']) ? json_encode($data['settings']) : null,
                'sort_order' => ((int) ($maxOrder['max_order'] ?? 0)) + 1,
            ]
        );

        $this->invalidate($areaKey);

        return $this->db->selectOne('SELECT * FROM widget_instances WHERE id = :id', ['id' => $id]);
    }

    public function updateInstance(int $id, array $data): array
    {
        $existing = $this->db->selectOne('SELECT * FROM widget_instances WHERE id = :id', ['id' => $id]);

        if ($existing === null) {
            throw new RuntimeException("Widget instance #{$id} not found.");
        }

        $this->db->execute(
            'UPDATE widget_instances SET
                title_override = :title_override, settings = :settings, is_active = :is_active
             WHERE id = :id',
            [
                'title_override' => array_key_exists('title_override', $data) ? $data['title_override'] : $existing['title_override'],
                'settings' => array_key_exists('settings', $data) ? json_encode($data['settings']) : $existing['settings'],
                'is_active' => array_key_exists('is_active', $data) ? (int) $data['is_active'] : $existing['is_active'],
                'id' => $id,
            ]
        );

        $this->invalidateByAreaId((int) $existing['widget_area_id']);

        return $this->db->selectOne('SELECT * FROM widget_instances WHERE id = :id', ['id' => $id]);
    }

    public function deleteInstance(int $id): void
    {
        $existing = $this->db->selectOne('SELECT * FROM widget_instances WHERE id = :id', ['id' => $id]);

        if ($existing === null) {
            return;
        }

        $this->db->execute('DELETE FROM widget_instances WHERE id = :id', ['id' => $id]);
        $this->invalidateByAreaId((int) $existing['widget_area_id']);
    }

    /**
     * Drag-drop reorder: pass the widget_instance IDs for this area in their
     * new top-to-bottom order. sort_order is rewritten 1..N to match.
     *
     * @param int[] $orderedInstanceIds
     */
    public function reorder(string $areaKey, array $orderedInstanceIds): void
    {
        $area = $this->db->selectOne('SELECT id FROM widget_areas WHERE key_name = :key', ['key' => $areaKey]);

        if ($area === null) {
            throw new RuntimeException("Widget area '{$areaKey}' not found.");
        }

        $this->db->transaction(function () use ($orderedInstanceIds, $area) {
            foreach ($orderedInstanceIds as $position => $instanceId) {
                $this->db->execute(
                    'UPDATE widget_instances SET sort_order = :sort_order
                     WHERE id = :id AND widget_area_id = :area_id',
                    ['sort_order' => $position + 1, 'id' => $instanceId, 'area_id' => $area['id']]
                );
            }
        });

        $this->invalidate($areaKey);
    }

    private function invalidateByAreaId(int $areaId): void
    {
        $area = $this->db->selectOne('SELECT key_name FROM widget_areas WHERE id = :id', ['id' => $areaId]);

        if ($area !== null) {
            $this->invalidate($area['key_name']);
        }
    }

    private function invalidate(string $areaKey): void
    {
        // Widget area fragment cache is keyed by "widget-area:{theme}:{areaKey}" in
        // ThemeEngine. We don't track the active theme here, so fall back to a
        // full flush — correct, if coarser than a targeted per-theme delete.
        $this->cache?->flush();
    }
}
