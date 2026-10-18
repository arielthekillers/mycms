<?php

declare(strict_types=1);

namespace App\Theme;

use App\Core\Database;
use RuntimeException;
use ZipArchive;

final class ThemeManager
{
    public function __construct(
        private Database $db,
        private string $themesPath
    ) {
    }

    public function getAll(): array
    {
        $themes = [];
        if (!is_dir($this->themesPath)) {
            return $themes;
        }

        $activeTheme = $this->getActiveThemeKey();

        foreach (scandir($this->themesPath) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $path = $this->themesPath . '/' . $dir;
            if (!is_dir($path)) continue;

            $jsonPath = $path . '/theme.json';
            $meta = [];
            
            if (file_exists($jsonPath)) {
                $meta = json_decode(file_get_contents($jsonPath), true) ?? [];
            }

            $screenshotFile = null;
            if (file_exists($path . '/screenshot.png')) {
                $screenshotFile = 'screenshot.png';
            } elseif (file_exists($path . '/screenshot.jpg')) {
                $screenshotFile = 'screenshot.jpg';
            }

            // Provide default metadata if missing
            $themes[] = [
                'directory' => $dir,
                'name' => $meta['name'] ?? $dir,
                'description' => $meta['description'] ?? 'Tidak ada deskripsi.',
                'version' => $meta['version'] ?? '1.0.0',
                'author' => $meta['author'] ?? 'Tidak diketahui',
                'is_active' => ($dir === $activeTheme),
                'screenshot_file' => $screenshotFile,
            ];
        }

        return $themes;
    }

    public function getActiveThemeKey(): string
    {
        $active = $this->db->selectOne("SELECT key_name FROM themes WHERE is_active = 1 LIMIT 1");
        return $active['key_name'] ?? 'pesantren-default';
    }

    public function getThemeMeta(string $themeKey): array
    {
        $path = $this->themesPath . '/' . $themeKey;
        $jsonPath = $path . '/theme.json';
        $meta = [];
        if (file_exists($jsonPath)) {
            $meta = json_decode(file_get_contents($jsonPath), true) ?? [];
        }

        return [
            'key' => $themeKey,
            'name' => $meta['name'] ?? ucfirst($themeKey),
            'description' => $meta['description'] ?? '',
            'version' => $meta['version'] ?? '1.0.0',
            'author' => $meta['author'] ?? '',
            'widget_areas' => $meta['widget_areas'] ?? [],
        ];
    }

    /**
     * @return array<array{key_name: string, label: string, scope: string}>
     */
    public function getThemeWidgetAreas(?string $themeKey = null): array
    {
        $themeKey = $themeKey ?: $this->getActiveThemeKey();
        $meta = $this->getThemeMeta($themeKey);

        $areas = [];
        if (!empty($meta['widget_areas']) && is_array($meta['widget_areas'])) {
            foreach ($meta['widget_areas'] as $item) {
                if (is_string($item)) {
                    $areas[] = [
                        'key_name' => $item,
                        'label' => ucwords(str_replace(['_', '-'], ' ', $item)),
                        'scope' => 'global',
                    ];
                } elseif (is_array($item) && (!empty($item['key_name']) || !empty($item['key']))) {
                    $key = (string) ($item['key_name'] ?? $item['key']);
                    $areas[] = [
                        'key_name' => $key,
                        'label' => (string) ($item['label'] ?? ucwords(str_replace(['_', '-'], ' ', $key))),
                        'scope' => (string) ($item['scope'] ?? 'global'),
                    ];
                }
            }
        }

        // Allow plugins or theme functions to filter/extend registered widget areas
        if (class_exists(\App\Core\Application::class)) {
            try {
                $areas = \App\Core\Application::getInstance()->events->filter('theme.widget_areas', $areas, $themeKey);
            } catch (\Throwable $e) {
                // Application not booted or singleton not ready
            }
        }

        return $areas;
    }

    public function syncThemeWidgetAreas(?string $themeKey = null): void
    {
        $areas = $this->getThemeWidgetAreas($themeKey);
        foreach ($areas as $area) {
            $this->db->execute(
                "INSERT INTO widget_areas (key_name, label, scope) VALUES (:k, :l, :s)
                 ON DUPLICATE KEY UPDATE label = :l2, scope = :s2",
                [
                    'k' => $area['key_name'],
                    'l' => $area['label'],
                    's' => $area['scope'] ?? 'global',
                    'l2' => $area['label'],
                    's2' => $area['scope'] ?? 'global',
                ]
            );
        }
    }

    public function activate(string $directory): void
    {
        $path = $this->themesPath . '/' . $directory;
        if (!is_dir($path)) {
            throw new RuntimeException("Tema tidak ditemukan.");
        }

        $this->db->execute("UPDATE themes SET is_active = 0");
        
        $exists = $this->db->selectOne("SELECT id FROM themes WHERE key_name = :k", ['k' => $directory]);
        
        if ($exists) {
            $this->db->execute("UPDATE themes SET is_active = 1 WHERE key_name = :k", ['k' => $directory]);
        } else {
            $this->db->execute("INSERT INTO themes (key_name, is_active) VALUES (:k, 1)", ['k' => $directory]);
        }

        $this->syncThemeWidgetAreas($directory);
    }

    public function extractZip(string $zipPath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException("Gagal membuka file ZIP tema.");
        }

        $firstFile = $zip->getNameIndex(0);
        if (!$firstFile) {
            $zip->close();
            throw new RuntimeException("File ZIP kosong.");
        }

        $parts = explode('/', str_replace('\\', '/', $firstFile));
        $themeDirName = $parts[0];

        if (empty($themeDirName) || str_contains($themeDirName, '.') || $themeDirName !== basename($themeDirName)) {
            $zip->close();
            throw new RuntimeException("Struktur ZIP tidak valid. Tema harus berada di dalam sebuah folder.");
        }

        $themePath = $this->themesPath . '/' . $themeDirName;
        
        if (is_dir($themePath)) {
            $zip->close();
            throw new RuntimeException("Tema dengan nama folder '{$themeDirName}' sudah ada.");
        }

        if (!$zip->extractTo($this->themesPath)) {
            $zip->close();
            throw new RuntimeException("Gagal mengekstrak tema.");
        }

        $zip->close();

        // Ensure theme.json exists
        if (!file_exists($themePath . '/theme.json')) {
            file_put_contents($themePath . '/theme.json', json_encode([
                'name' => ucfirst($themeDirName),
                'description' => 'Tema baru yang diunggah.',
                'version' => '1.0.0',
                'author' => 'Author'
            ], JSON_PRETTY_PRINT));
        }

        return $themeDirName;
    }

    public function delete(string $directory): void
    {
        if ($directory === $this->getActiveThemeKey()) {
            throw new RuntimeException("Tidak dapat menghapus tema yang sedang aktif.");
        }
        
        if ($directory === 'pesantren-default') {
            throw new RuntimeException("Tema bawaan tidak dapat dihapus.");
        }

        $path = $this->themesPath . '/' . $directory;
        if (!is_dir($path)) {
            throw new RuntimeException("Tema tidak ditemukan.");
        }

        $this->deleteDirectory($path);
        
        $this->db->execute("DELETE FROM themes WHERE key_name = :k", ['k' => $directory]);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            (is_dir($path)) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
