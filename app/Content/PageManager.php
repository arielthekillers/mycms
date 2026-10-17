<?php

declare(strict_types=1);

namespace App\Content;

use App\Core\Database;
use App\Media\MediaManager;
use RuntimeException;

final class PageManager
{
    private const USABLE_TYPE = 'page';
    private const FEATURED_CONTEXT = 'featured';

    public function __construct(private Database $db, private MediaManager $media)
    {
    }

    public function list(): array
    {
        return $this->db->select('SELECT * FROM pages ORDER BY title ASC');
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM pages WHERE id = :id', ['id' => $id]);
    }

    public function findBySlug(string $slug, bool $publishedOnly = false): ?array
    {
        $sql = 'SELECT * FROM pages WHERE slug = :slug';

        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }

        return $this->db->selectOne($sql, ['slug' => $slug]);
    }

    public function create(array $data): array
    {
        if (empty($data['title'])) {
            throw new RuntimeException('Title is required.');
        }

        $slug = $this->uniqueSlug($data['slug'] ?? $data['title']);

        $id = $this->db->insert(
            'INSERT INTO pages (title, slug, content, featured_media_id, status, meta_title, meta_description)
             VALUES (:title, :slug, :content, :featured_media_id, :status, :meta_title, :meta_description)',
            [
                'title' => $data['title'],
                'slug' => $slug,
                'content' => $data['content'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]
        );

        if (!empty($data['featured_media_id'])) {
            $this->media->attachUsage((int) $data['featured_media_id'], self::USABLE_TYPE, $id, self::FEATURED_CONTEXT);
        }

        return $this->find($id);
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->find($id);

        if ($existing === null) {
            throw new RuntimeException("Page #{$id} not found.");
        }

        $slug = array_key_exists('slug', $data) || array_key_exists('title', $data)
            ? $this->uniqueSlug($data['slug'] ?? $data['title'] ?? $existing['title'], $id)
            : $existing['slug'];

        $newFeaturedId = array_key_exists('featured_media_id', $data)
            ? $data['featured_media_id']
            : $existing['featured_media_id'];

        $this->db->execute(
            'UPDATE pages SET title = :title, slug = :slug, content = :content,
                featured_media_id = :featured_media_id, status = :status,
                meta_title = :meta_title, meta_description = :meta_description
             WHERE id = :id',
            [
                'title' => $data['title'] ?? $existing['title'],
                'slug' => $slug,
                'content' => $data['content'] ?? $existing['content'],
                'featured_media_id' => $newFeaturedId,
                'status' => $data['status'] ?? $existing['status'],
                'meta_title' => array_key_exists('meta_title', $data) ? $data['meta_title'] : $existing['meta_title'],
                'meta_description' => array_key_exists('meta_description', $data) ? $data['meta_description'] : $existing['meta_description'],
                'id' => $id,
            ]
        );

        if ((int) $existing['featured_media_id'] !== (int) $newFeaturedId) {
            $this->media->replaceUsage(
                $existing['featured_media_id'] !== null ? (int) $existing['featured_media_id'] : null,
                $newFeaturedId !== null ? (int) $newFeaturedId : null,
                self::USABLE_TYPE,
                $id,
                self::FEATURED_CONTEXT
            );
        }

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $page = $this->find($id);

        if ($page === null) {
            return;
        }

        if (!empty($page['featured_media_id'])) {
            $this->media->detachUsage((int) $page['featured_media_id'], self::USABLE_TYPE, $id, self::FEATURED_CONTEXT);
        }

        $this->db->execute('DELETE FROM pages WHERE id = :id', ['id' => $id]);
    }

    private function uniqueSlug(string $source, ?int $excludeId = null): string
    {
        $base = $this->slugify($source);
        $slug = $base;
        $suffix = 1;

        while ($this->slugTaken($slug, $excludeId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function slugTaken(string $slug, ?int $excludeId): bool
    {
        $sql = 'SELECT id FROM pages WHERE slug = :slug';
        $bindings = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
            $bindings['exclude_id'] = $excludeId;
        }

        return $this->db->selectOne($sql, $bindings) !== null;
    }

    private function slugify(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim($slug, '-') ?: 'halaman';
    }
}
