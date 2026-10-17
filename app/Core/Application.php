<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Auth\AuthManager;
use App\Core\Auth\Rbac;
use App\Core\Auth\Session;
use App\Content\ArticleManager;
use App\Content\PageManager;
use App\Content\PublicContentResolver;
use App\Core\PluginManager;
use App\Core\Cache\CacheInterface;
use App\Core\Cache\FileCache;
use App\Core\Cache\NullCache;
use App\Core\Queue\DatabaseQueue;
use App\Media\MediaManager;
use App\Menu\MenuManager;
use App\Menu\MenuResolver;
use App\Theme\ThemeEngine;
use App\Theme\ThemeOptionsManager;
use App\Theme\WidgetManager;
use Dotenv\Dotenv;

final class Application
{
    private static ?Application $instance = null;

    public readonly string $basePath;
    public readonly Database $db;
    public readonly Router $router;
    public readonly EventBus $events;
    public readonly AuthManager $auth;
    public readonly Rbac $rbac;
    public readonly DatabaseQueue $queue;
    public readonly CacheInterface $cache;
    public readonly PluginManager $plugins;

    private function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');

        $this->loadEnvironment();

        $this->db = Database::init([
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? '',
            'username' => $_ENV['DB_USERNAME'] ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        ]);

        $this->events = EventBus::getInstance();
        $this->auth = new AuthManager($this->db);
        $this->rbac = new Rbac($this->db);
        $this->queue = new DatabaseQueue($this->db);

        $cacheEnabled = filter_var($_ENV['CACHE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $this->cache = $cacheEnabled
            ? new FileCache($this->basePath . '/storage/cache')
            : new NullCache();

        $this->router = new Router();
        $this->router->useGlobal(function (Request $request, Response $response, callable $next) {
            Session::start(
                $_ENV['SESSION_NAME'] ?? 'pesantren_session',
                (int) ($_ENV['SESSION_LIFETIME'] ?? 120)
            );

            return $next($request, $response);
        });
        
        $this->router->useGlobal(new \App\Http\Middleware\CsrfMiddleware());

        // Coarse-grained but always-correct invalidation: any successful write
        // (non-GET) clears the whole cache. Simpler and safer than tracking
        // per-entity cache tags for a v1 — see README for the trade-off and
        // how to make this granular later (e.g. tag full-page cache entries
        // by content type and only clear matching tags here).
        $this->router->useGlobal(function (Request $request, Response $response, callable $next) {
            $result = $next($request, $response);

            if ($request->method() !== 'GET' && $result->getStatus() < 400) {
                $this->cache->flush();
            }

            return $result;
        });

        // Initialize plugins
        $this->plugins = new PluginManager(
            $this->db, 
            $this->basePath . '/app/Plugins', 
            $this->router,
            $this->cache,
            $this->basePath . '/public/plugins'
        );
    }

    /** Convenience factory — Media Manager needs the queue + a resolved storage path. */
    public function media(): MediaManager
    {
        return new MediaManager($this->db, $this->queue, $this->basePath . '/storage/uploads');
    }

    public function categories(): \App\Content\CategoryManager
    {
        return new \App\Content\CategoryManager($this->db);
    }

    public function articles(): ArticleManager
    {
        return new ArticleManager($this->db, $this->media());
    }

    public function pages(): PageManager
    {
        return new PageManager($this->db, $this->media());
    }

    public function menus(): MenuManager
    {
        return new MenuManager($this->db, new MenuResolver($this->db));
    }

    public function publicContentResolver(): PublicContentResolver
    {
        return new PublicContentResolver($this->db, $this->pages());
    }

    public function theme(): ThemeEngine
    {
        return new ThemeEngine(
            $this->db,
            $this->basePath . '/resources/themes',
            $this->activeThemeKey(),
            $this->cache,
            (int) ($_ENV['CACHE_TTL'] ?? 300)
        );
    }

    public function themeManager(): \App\Theme\ThemeManager
    {
        return new \App\Theme\ThemeManager($this->db, $this->basePath . '/resources/themes');
    }

    public function themeOptions(): ThemeOptionsManager
    {
        return new ThemeOptionsManager($this->db);
    }

    public function widgets(): WidgetManager
    {
        return new WidgetManager($this->db, $this->cache);
    }

    public function activeThemeKey(): string
    {
        try {
            $active = $this->db->selectOne("SELECT key_name FROM themes WHERE is_active = 1 LIMIT 1");
            return $active['key_name'] ?? 'pesantren-default';
        } catch (\PDOException $e) {
            return 'pesantren-default';
        }
    }

    public static function bootstrap(string $basePath): self
    {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
            self::$instance->plugins->boot();

            // Load theme functions if available
            $themeFunctionsPath = $basePath . '/resources/themes/' . self::$instance->activeThemeKey() . '/functions.php';
            if (file_exists($themeFunctionsPath)) {
                $app = self::$instance;
                try {
                    require $themeFunctionsPath;
                } catch (\Throwable $e) {
                    error_log("Theme functions error: " . $e->getMessage());
                }
            }
        }
        return self::$instance;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application has not been bootstrapped yet.');
        }

        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        if (file_exists($this->basePath . '/.env')) {
            Dotenv::createImmutable($this->basePath)->load();
        }
    }

    public function migrationsPath(): string
    {
        return $this->basePath . '/database/migrations';
    }

    public function run(): void
    {
        $request = new Request();
        $response = new Response();

        $response = $this->router->dispatch($request, $response);
        $response->send();
    }
}
