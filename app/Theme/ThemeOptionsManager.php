<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Database;

final class ThemeOptionsManager
{
    public function __construct(private Database $db)
    {
    }

    /** @return array<string, mixed> option_key => decoded value */
    public function all(string $themeKey): array
    {
        $rows = $this->db->select(
            'SELECT o.option_key, o.option_value
             FROM theme_options o
             JOIN themes t ON t.id = o.theme_id
             WHERE t.key_name = :theme',
            ['theme' => $themeKey]
        );

        $options = [];

        foreach ($rows as $row) {
            $options[$row['option_key']] = json_decode($row['option_value'], true);
        }

        return $options;
    }

    public function set(string $themeKey, string $key, mixed $value): void
    {
        $theme = $this->db->selectOne('SELECT id FROM themes WHERE key_name = :key', ['key' => $themeKey]);

        if ($theme === null) {
            throw new \RuntimeException("Theme '{$themeKey}' not found.");
        }

        $this->db->execute(
            'INSERT INTO theme_options (theme_id, option_key, option_value)
             VALUES (:theme_id, :option_key, :option_value)
             ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)',
            ['theme_id' => $theme['id'], 'option_key' => $key, 'option_value' => json_encode($value)]
        );
    }

    /** @param array<string, mixed> $options */
    public function setMany(string $themeKey, array $options): void
    {
        foreach ($options as $key => $value) {
            $this->set($themeKey, $key, $value);
        }
    }
}
