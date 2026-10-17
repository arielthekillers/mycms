<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Cache\CacheInterface;
use App\Core\Database;
use RuntimeException;

final class WidgetManager
{
    public function __construct(private Database $db, private ?CacheInterface $cache = null)
    {
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

    public function listWidgetAreas(): array
    {
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
