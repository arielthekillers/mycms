<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use ZipArchive;

final class PluginManager
{
    public function __construct(
        private Database $db,
        private string $pluginsPath,
        private Router $router,
        private ?\App\Core\Cache\CacheInterface $cache = null,
        private ?string $publicPluginsPath = null
    ) {
    }

    public function discover(): array
    {
        $cacheKey = 'system_plugins_discover';
        if ($this->cache && ($cached = $this->cache->get($cacheKey)) !== null) {
            return $cached;
        }

        $plugins = [];
        if (!is_dir($this->pluginsPath)) {
            return $plugins;
        }

        $dirs = array_filter(glob($this->pluginsPath . '/*'), 'is_dir');
        
        $activeMap = [];
        try {
            $activePlugins = $this->db->select("SELECT directory, is_active FROM system_plugins");
            foreach ($activePlugins as $ap) {
                $activeMap[$ap['directory']] = (bool) $ap['is_active'];
            }
        } catch (\PDOException $e) {
            // Ignore during migrations
        }

        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $jsonPath = $dir . '/plugin.json';
            
            if (file_exists($jsonPath)) {
                $info = json_decode(file_get_contents($jsonPath), true);
                if (is_array($info)) {
                    $plugins[] = [
                        'directory' => $dirName,
                        'name' => $info['name'] ?? $dirName,
                        'description' => $info['description'] ?? '',
                        'version' => $info['version'] ?? '1.0.0',
                        'author' => !empty($info['author']) ? $info['author'] : 'mycms team',
                        'author_url' => $info['author_url'] ?? null,
                        'admin_url' => $info['admin_url'] ?? null,
                        'icon' => $info['icon'] ?? 'fas fa-puzzle-piece',
                        'is_active' => $activeMap[$dirName] ?? false,
                    ];
                }
            }
        }
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $plugins, 86400);
        }

        return $plugins;
    }

    public function boot(): void
    {
        try {
            $activePlugins = $this->db->select("SELECT directory FROM system_plugins WHERE is_active = 1");
            
            foreach ($activePlugins as $plugin) {
                $dir = $this->pluginsPath . '/' . $plugin['directory'];
                
                // Require routes if exists
                if (file_exists($dir . '/routes.php')) {
                    $router = $this->router; // Expose router to required file
                    try {
                        require $dir . '/routes.php';
                    } catch (\Throwable $e) {
                        error_log("Plugin error ({$plugin['directory']}): " . $e->getMessage());
                    }
                }
            }
        } catch (\PDOException $e) {
            // Ignore for when table doesn't exist yet (e.g. running migrations)
        }
    }

    public function toggle(string $directory, bool $activate): void
    {
        $dirPath = $this->pluginsPath . '/' . $directory;

        if ($activate) {
            if (file_exists($dirPath . '/activate.php')) {
                $db = $this->db;
                try {
                    require $dirPath . '/activate.php';
                } catch (\Throwable $e) {
                    error_log("Plugin activation error ({$directory}): " . $e->getMessage());
                    throw new RuntimeException("Gagal mengaktifkan plugin: " . $e->getMessage());
                }
            }

            if ($this->publicPluginsPath && is_dir($dirPath . '/assets')) {
                if (!is_dir($this->publicPluginsPath)) {
                    @mkdir($this->publicPluginsPath, 0777, true);
                }
                $targetAssetPath = $this->publicPluginsPath . '/' . $directory;
                if (!file_exists($targetAssetPath)) {
                    if (!@symlink($dirPath . '/assets', $targetAssetPath)) {
                        $this->copyDirectory($dirPath . '/assets', $targetAssetPath);
                    }
                }
            }
        } else {
            if (file_exists($dirPath . '/deactivate.php')) {
                $db = $this->db;
                try {
                    require $dirPath . '/deactivate.php';
                } catch (\Throwable $e) {
                    error_log("Plugin deactivation error ({$directory}): " . $e->getMessage());
                }
            }

            if ($this->publicPluginsPath) {
                $targetAssetPath = $this->publicPluginsPath . '/' . $directory;
                if (is_link($targetAssetPath)) {
                    @unlink($targetAssetPath);
                } elseif (is_dir($targetAssetPath)) {
                    $this->removeDirectory($targetAssetPath);
                }
            }
        }

        $exists = $this->db->selectOne("SELECT id FROM system_plugins WHERE directory = :dir", ['dir' => $directory]);
        
        if ($exists) {
            $this->db->execute(
                "UPDATE system_plugins SET is_active = :active WHERE directory = :dir",
                ['active' => $activate ? 1 : 0, 'dir' => $directory]
            );
        } else {
            $this->db->execute(
                "INSERT INTO system_plugins (directory, is_active) VALUES (:dir, :active)",
                ['dir' => $directory, 'active' => $activate ? 1 : 0]
            );
        }

        if ($this->cache) {
            $this->cache->delete('system_plugins_discover');
        }
    }

    public function installFromZip(string $zipFilePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new RuntimeException("Gagal membuka file ZIP.");
        }

        // We assume the zip contains a single root folder which is the plugin directory
        // Or files are directly inside, we will extract to a temp dir to check
        $tempDir = $this->pluginsPath . '/.tmp_' . uniqid();
        mkdir($tempDir);
        
        $zip->extractTo($tempDir);
        $zip->close();

        // Check if plugin.json is at root of tempDir or one level deep
        $pluginJsonPath = $tempDir . '/plugin.json';
        $pluginDirName = null;
        $sourceDir = $tempDir;

        if (!file_exists($pluginJsonPath)) {
            // Check one level deep
            $subdirs = array_filter(glob($tempDir . '/*'), 'is_dir');
            if (count($subdirs) === 1) {
                $sub = $subdirs[0];
                if (file_exists($sub . '/plugin.json')) {
                    $pluginJsonPath = $sub . '/plugin.json';
                    $sourceDir = $sub;
                    $pluginDirName = basename($sub);
                }
            }
        }

        if (!file_exists($pluginJsonPath)) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("File plugin.json tidak ditemukan di dalam ZIP.");
        }

        $info = json_decode(file_get_contents($pluginJsonPath), true);
        if (!$info || empty($info['name'])) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("File plugin.json tidak valid.");
        }

        if (isset($info['requires']) && is_array($info['requires'])) {
            foreach ($info['requires'] as $req => $version) {
                if ($req !== 'cms') {
                    $reqPath = $this->pluginsPath . '/' . $req;
                    if (!is_dir($reqPath)) {
                        $this->removeDirectory($tempDir);
                        throw new RuntimeException("Plugin membutuhkan {$req}.");
                    }
                }
            }
        }

        $targetDirName = $pluginDirName ?: preg_replace('/[^a-zA-Z0-9]/', '', $info['name']);
        $targetPath = $this->pluginsPath . '/' . $targetDirName;

        if (file_exists($targetPath)) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("Plugin dengan direktori {$targetDirName} sudah ada.");
        }

        rename($sourceDir, $targetPath);
        
        // Cleanup temp if we extracted from a subfolder
        if (is_dir($tempDir)) {
            $this->removeDirectory($tempDir);
        }

        if ($this->cache) {
            $this->cache->delete('system_plugins_discover');
        }

        return [
            'directory' => $targetDirName,
            'name' => $info['name']
        ];
    }

    public function updateFromZip(string $zipFilePath, string $targetDirectory): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new RuntimeException("Gagal membuka file ZIP.");
        }

        $tempDir = $this->pluginsPath . '/.tmp_' . uniqid();
        mkdir($tempDir);
        $zip->extractTo($tempDir);
        $zip->close();

        $pluginJsonPath = $tempDir . '/plugin.json';
        $sourceDir = $tempDir;

        if (!file_exists($pluginJsonPath)) {
            $subdirs = array_filter(glob($tempDir . '/*'), 'is_dir');
            if (count($subdirs) === 1) {
                $sub = $subdirs[0];
                if (file_exists($sub . '/plugin.json')) {
                    $pluginJsonPath = $sub . '/plugin.json';
                    $sourceDir = $sub;
                }
            }
        }

        if (!file_exists($pluginJsonPath)) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("File plugin.json tidak ditemukan di dalam ZIP.");
        }

        $info = json_decode(file_get_contents($pluginJsonPath), true);
        if (!$info || empty($info['name'])) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("File plugin.json tidak valid.");
        }

        $targetPath = $this->pluginsPath . '/' . $targetDirectory;
        if (!is_dir($targetPath)) {
            $this->removeDirectory($tempDir);
            throw new RuntimeException("Plugin {$targetDirectory} belum terinstal. Gunakan instalasi baru.");
        }

        // Backup existing plugin
        $backupPath = $this->pluginsPath . '/.backup_' . uniqid();
        rename($targetPath, $backupPath);

        // Move new files
        rename($sourceDir, $targetPath);

        // Run update hook if exists
        if (file_exists($targetPath . '/update.php')) {
            $db = $this->db;
            try {
                require $targetPath . '/update.php';
            } catch (\Throwable $e) {
                error_log("Plugin update error ({$targetDirectory}): " . $e->getMessage());
                // Rollback
                $this->removeDirectory($targetPath);
                rename($backupPath, $targetPath);
                if (is_dir($tempDir)) $this->removeDirectory($tempDir);
                throw new RuntimeException("Gagal melakukan update. Telah di-rollback. Error: " . $e->getMessage());
            }
        }

        // Update assets if plugin is currently active
        $isActive = $this->db->selectOne("SELECT is_active FROM system_plugins WHERE directory = :dir", ['dir' => $targetDirectory]);
        if ($isActive && $isActive['is_active']) {
            if ($this->publicPluginsPath) {
                $targetAssetPath = $this->publicPluginsPath . '/' . $targetDirectory;
                if (is_link($targetAssetPath)) @unlink($targetAssetPath);
                elseif (is_dir($targetAssetPath)) $this->removeDirectory($targetAssetPath);
                
                if (is_dir($targetPath . '/assets')) {
                    if (!is_dir($this->publicPluginsPath)) @mkdir($this->publicPluginsPath, 0777, true);
                    if (!@symlink($targetPath . '/assets', $targetAssetPath)) {
                        $this->copyDirectory($targetPath . '/assets', $targetAssetPath);
                    }
                }
            }
        }

        // Cleanup
        $this->removeDirectory($backupPath);
        if (is_dir($tempDir)) $this->removeDirectory($tempDir);
        if ($this->cache) $this->cache->delete('system_plugins_discover');

        return [
            'directory' => $targetDirectory,
            'name' => $info['name']
        ];
    }

    public function uninstall(string $directory): void
    {
        // Must exist in DB (or at least we try to delete from DB anyway)
        $this->db->execute("DELETE FROM system_plugins WHERE directory = :dir", ['dir' => $directory]);

        $dirPath = $this->pluginsPath . '/' . $directory;
        if (!is_dir($dirPath)) {
            if ($this->cache) {
                $this->cache->delete('system_plugins_discover');
            }
            return; // Already deleted from filesystem
        }

        // Run uninstall.php if exists
        if (file_exists($dirPath . '/uninstall.php')) {
            $db = $this->db; // Expose DB for uninstall script
            try {
                require $dirPath . '/uninstall.php';
            } catch (\Throwable $e) {
                error_log("Plugin uninstall error ({$directory}): " . $e->getMessage());
            }
        }

        // Delete directory
        $this->removeDirectory($dirPath);

        if ($this->publicPluginsPath) {
            $targetAssetPath = $this->publicPluginsPath . '/' . $directory;
            if (is_link($targetAssetPath)) {
                @unlink($targetAssetPath);
            } elseif (is_dir($targetAssetPath)) {
                $this->removeDirectory($targetAssetPath);
            }
        }

        if ($this->cache) {
            $this->cache->delete('system_plugins_discover');
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : @unlink("$dir/$file");
        }
        @rmdir($dir);
    }

    private function copyDirectory(string $src, string $dst): void
    {
        if (!is_dir($dst)) {
            mkdir($dst, 0777, true);
        }
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir("$src/$file")) {
                    $this->copyDirectory("$src/$file", "$dst/$file");
                } else {
                    copy("$src/$file", "$dst/$file");
                }
            }
        }
        closedir($dir);
    }
}
