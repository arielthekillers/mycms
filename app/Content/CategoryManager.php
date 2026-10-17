<?php

declare(strict_types=1);

namespace App\Content;

use App\Core\Database;
use RuntimeException;

final class CategoryManager
{
    public function __construct(private Database $db)
    {
    }

    public function list(): array
    {
        return $this->db->select('SELECT * FROM categories ORDER BY name ASC');
    }

    public function create(array $data): array
    {
        if (empty($data['name'])) {
            throw new RuntimeException('Category name is required.');
        }

        $baseSlug = $this->slugify($data['name']);
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->slugTaken($slug)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $this->db->execute(
            'INSERT INTO categories (name, slug) VALUES (:name, :slug)',
            ['name' => $data['name'], 'slug' => $slug]
        );

        $id = (int) $this->db->pdo()->lastInsertId();
        return $this->find($id) ?? throw new RuntimeException('Failed to retrieve created category.');
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
    }

    public function delete(int $id): void
    {
        // article_categories should cascade delete.
        $this->db->execute('DELETE FROM categories WHERE id = :id', ['id' => $id]);
    }

    private function slugTaken(string $slug): bool
    {
        return $this->db->selectOne('SELECT id FROM categories WHERE slug = :slug', ['slug' => $slug]) !== null;
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', "-", $text);
        return trim($text, '-');
    }
}
