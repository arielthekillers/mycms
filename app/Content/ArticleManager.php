<?php

declare(strict_types=1);

namespace App\Content;

use App\Core\Database;
use App\Media\MediaManager;
use RuntimeException;

final class ArticleManager
{
    private const USABLE_TYPE = 'article';
    private const FEATURED_CONTEXT = 'featured';

    public function __construct(private Database $db, private MediaManager $media)
    {
    }

    /**
     * @param array{status?: string, category_id?: int, search?: string} $filters
     * @return array<int, array<string, mixed>>
     */
    public function list(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'desc', int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $bindings = [];

        if (!empty($filters['status'])) {
            $where[] = 'a.status = :status';
            $bindings['status'] = $filters['status'];
        } else {
            $where[] = "a.status != 'deleted'";
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM article_categories ac WHERE ac.article_id = a.id AND ac.category_id = :category_id)';
            $bindings['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = 'a.title LIKE :search';
            $bindings['search'] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $allowedSortCols = ['title', 'created_at', 'author_name'];
        $sortBy = in_array($sortBy, $allowedSortCols, true) ? $sortBy : 'created_at';
        $sortDir = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';
        $sortColSql = $sortBy === 'author_name' ? 'u.name' : "a.{$sortBy}";

        $rows = $this->db->select(
            "SELECT a.*, u.name as author_name, m.disk_path as author_avatar_url 
             FROM articles a 
             LEFT JOIN users u ON a.author_id = u.id 
             LEFT JOIN media m ON u.avatar_media_id = m.id 
             {$whereSql} 
             ORDER BY {$sortColSql} {$sortDir} 
             LIMIT {$limit} OFFSET {$offset}",
            $bindings
        );

        foreach ($rows as &$row) {
            $row['categories'] = $this->categoriesFor((int) $row['id']);
        }

        return $rows;
    }

    public function countFiltered(array $filters = []): int
    {
        $where = [];
        $bindings = [];

        if (!empty($filters['status'])) {
            $where[] = 'a.status = :status';
            $bindings['status'] = $filters['status'];
        } else {
            $where[] = "a.status != 'deleted'";
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM article_categories ac WHERE ac.article_id = a.id AND ac.category_id = :category_id)';
            $bindings['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = 'a.title LIKE :search';
            $bindings['search'] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

        $row = $this->db->selectOne("SELECT COUNT(*) as count FROM articles a {$whereSql}", $bindings);
        return (int) ($row['count'] ?? 0);
    }

    public function counts(): array
    {
        $rows = $this->db->select('SELECT status, COUNT(*) as count FROM articles GROUP BY status');
        $counts = ['published' => 0, 'draft' => 0, 'archived' => 0, 'deleted' => 0, 'all' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['count'];
            if ($row['status'] !== 'deleted') {
                $counts['all'] += (int) $row['count'];
            }
        }
        return $counts;
    }

    public function find(int $id): ?array
    {
        $article = $this->db->selectOne('
            SELECT a.*, m.disk_path as featured_image_path
            FROM articles a
            LEFT JOIN media m ON m.id = a.featured_media_id
            WHERE a.id = :id
        ', ['id' => $id]);

        if ($article !== null) {
            $article['categories'] = $this->categoriesFor($id);
            $article['tags'] = $this->tagsFor($id);
        }

        return $article;
    }

    public function findBySlug(string $slug, bool $publishedOnly = false): ?array
    {
        $sql = 'SELECT * FROM articles WHERE slug = :slug';

        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }

        $article = $this->db->selectOne($sql, ['slug' => $slug]);

        if ($article !== null) {
            $article['categories'] = $this->categoriesFor((int) $article['id']);
            $article['tags'] = $this->tagsFor((int) $article['id']);
        }

        return $article;
    }

    /**
     * @param array{title: string, slug?: string, excerpt?: string, content?: string,
     *              featured_media_id?: int, category_ids?: int[], status?: string} $data
     */
    public function create(array $data, ?int $authorId): array
    {
        if (empty($data['title'])) {
            throw new RuntimeException('Title is required.');
        }

        $slug = $this->uniqueSlug($data['slug'] ?? $data['title']);
        $status = $data['status'] ?? 'draft';

        $articleId = $this->db->insert(
            'INSERT INTO articles (title, slug, excerpt, content, featured_media_id, author_id, status, published_at, meta_title, meta_description)
             VALUES (:title, :slug, :excerpt, :content, :featured_media_id, :author_id, :status, :published_at, :meta_title, :meta_description)',
            [
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? null,
                'featured_media_id' => $data['featured_media_id'] ?? null,
                'author_id' => $authorId,
                'status' => $status,
                'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]
        );

        if (!empty($data['featured_media_id'])) {
            $this->media->attachUsage((int) $data['featured_media_id'], self::USABLE_TYPE, $articleId, self::FEATURED_CONTEXT);
        }

        if (!empty($data['category_ids'])) {
            $this->syncCategories($articleId, $data['category_ids']);
        }

        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            $this->syncTags($articleId, $data['tags']);
        }

        return $this->find($articleId);
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->find($id);

        if ($existing === null) {
            throw new RuntimeException("Article #{$id} not found.");
        }

        $slug = array_key_exists('slug', $data) || array_key_exists('title', $data)
            ? $this->uniqueSlug($data['slug'] ?? $data['title'] ?? $existing['title'], $id)
            : $existing['slug'];

        $newFeaturedId = array_key_exists('featured_media_id', $data)
            ? $data['featured_media_id']
            : $existing['featured_media_id'];

        $status = $data['status'] ?? $existing['status'];
        $publishedAt = $existing['published_at'];
        $newAuthorId = array_key_exists('author_id', $data) ? $data['author_id'] : $existing['author_id'];

        if ($status === 'published' && $existing['status'] !== 'published') {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $titleChanged = isset($data['title']) && $data['title'] !== $existing['title'];
        $excerptChanged = isset($data['excerpt']) && $data['excerpt'] !== $existing['excerpt'];
        $contentChanged = isset($data['content']) && $data['content'] !== $existing['content'];

        if ($titleChanged || $excerptChanged || $contentChanged) {
            $this->db->insert(
                'INSERT INTO article_revisions (article_id, title, excerpt, content, author_id) VALUES (:article_id, :title, :excerpt, :content, :author_id)',
                [
                    'article_id' => $id,
                    'title' => $existing['title'],
                    'excerpt' => $existing['excerpt'],
                    'content' => $existing['content'],
                    'author_id' => $existing['author_id']
                ]
            );
            
            $this->db->execute(
                'DELETE FROM article_revisions WHERE article_id = :article_id1 AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM article_revisions WHERE article_id = :article_id2 ORDER BY created_at DESC LIMIT 10
                    ) as t
                )',
                ['article_id1' => $id, 'article_id2' => $id]
            );
        }

        $this->db->execute(
            'UPDATE articles SET
                title = :title, slug = :slug, excerpt = :excerpt, content = :content,
                featured_media_id = :featured_media_id, status = :status, published_at = :published_at,
                meta_title = :meta_title, meta_description = :meta_description, author_id = :author_id
             WHERE id = :id',
            [
                'title' => $data['title'] ?? $existing['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? $existing['excerpt'],
                'content' => $data['content'] ?? $existing['content'],
                'featured_media_id' => $newFeaturedId,
                'status' => $status,
                'published_at' => $publishedAt,
                'meta_title' => array_key_exists('meta_title', $data) ? $data['meta_title'] : $existing['meta_title'],
                'meta_description' => array_key_exists('meta_description', $data) ? $data['meta_description'] : $existing['meta_description'],
                'author_id' => $newAuthorId,
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

        if (array_key_exists('category_ids', $data)) {
            $this->syncCategories($id, $data['category_ids']);
        }

        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            $this->syncTags($id, $data['tags']);
        }

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $article = $this->find($id);

        if ($article === null) {
            return;
        }

        $this->db->execute('UPDATE articles SET status = \'deleted\' WHERE id = :id', ['id' => $id]);
    }

    public function forceDelete(int $id): void
    {
        $article = $this->find($id);

        if ($article === null) {
            return;
        }

        if (!empty($article['featured_media_id'])) {
            $this->media->detachUsage((int) $article['featured_media_id'], self::USABLE_TYPE, $id, self::FEATURED_CONTEXT);
        }

        // article_categories rows cascade-delete via FK ON DELETE CASCADE.
        $this->db->execute('DELETE FROM articles WHERE id = :id', ['id' => $id]);
    }

    public function publish(int $id): array
    {
        return $this->update($id, ['status' => 'published']);
    }

    public function unpublish(int $id): array
    {
        return $this->update($id, ['status' => 'draft']);
    }

    public function restore(int $id): array
    {
        return $this->update($id, ['status' => 'draft']);
    }

    /** @param int[] $categoryIds */
    private function syncCategories(int $articleId, array $categoryIds): void
    {
        $this->db->execute('DELETE FROM article_categories WHERE article_id = :id', ['id' => $articleId]);

        foreach (array_unique($categoryIds) as $categoryId) {
            $this->db->execute(
                'INSERT IGNORE INTO article_categories (article_id, category_id) VALUES (:article_id, :category_id)',
                ['article_id' => $articleId, 'category_id' => $categoryId]
            );
        }
    }

    private function categoriesFor(int $articleId): array
    {
        return $this->db->select(
            'SELECT c.id, c.name, c.slug FROM categories c
             JOIN article_categories ac ON ac.category_id = c.id
             WHERE ac.article_id = :id',
            ['id' => $articleId]
        );
    }

    private function syncTags(int $articleId, array $tagNames): void
    {
        $this->db->execute('DELETE FROM article_tags WHERE article_id = :id', ['id' => $articleId]);

        foreach (array_unique(array_filter(array_map('trim', $tagNames))) as $tagName) {
            $slug = $this->slugify($tagName);
            
            $tag = $this->db->selectOne('SELECT id FROM tags WHERE slug = :slug', ['slug' => $slug]);
            if ($tag === null) {
                $tagId = $this->db->insert('INSERT INTO tags (name, slug) VALUES (:name, :slug)', ['name' => $tagName, 'slug' => $slug]);
            } else {
                $tagId = $tag['id'];
            }
            
            $this->db->execute(
                'INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)',
                ['article_id' => $articleId, 'tag_id' => $tagId]
            );
        }
    }

    private function tagsFor(int $articleId): array
    {
        return $this->db->select(
            'SELECT t.id, t.name, t.slug FROM tags t
             JOIN article_tags at ON at.tag_id = t.id
             WHERE at.article_id = :id',
            ['id' => $articleId]
        );
    }

    public function revisionsFor(int $articleId): array
    {
        return $this->db->select(
            'SELECT r.id, r.title, r.created_at, u.name as author_name FROM article_revisions r
             LEFT JOIN users u ON u.id = r.author_id
             WHERE r.article_id = :id ORDER BY r.created_at DESC',
            ['id' => $articleId]
        );
    }

    public function restoreRevision(int $id, int $revisionId): array
    {
        $revision = $this->db->selectOne('SELECT * FROM article_revisions WHERE id = :id AND article_id = :article_id', [
            'id' => $revisionId,
            'article_id' => $id
        ]);
        
        if ($revision === null) {
            throw new RuntimeException('Revision not found.');
        }
        
        return $this->update($id, [
            'title' => $revision['title'],
            'excerpt' => $revision['excerpt'],
            'content' => $revision['content'],
        ]);
    }

    public function authors(): array
    {
        return $this->db->select('SELECT id, name, email FROM users ORDER BY name ASC');
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
        $sql = 'SELECT id FROM articles WHERE slug = :slug';
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

        return trim($slug, '-') ?: 'artikel';
    }
}
