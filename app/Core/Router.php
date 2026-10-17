<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple regex-based router.
 *
 * Explicit routes (e.g. /admin/login, /agenda) are matched first.
 * If nothing matches and a fallback handler is registered, it receives
 * the full path so the Content Manager can resolve it dynamically
 * against articles/pages/module slugs (see Menu Target Provider design).
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: callable|array, middleware: callable[]}> */
    private array $routes = [];

    /** @var (callable|array|null) */
    private $fallback = null;

    /** @var callable[] middleware applied to every route (e.g. start session) */
    private array $globalMiddleware = [];

    /**
     * Register middleware that runs before every route, in registration order.
     * Signature: function (Request $request, Response $response, callable $next): Response
     */
    public function useGlobal(callable $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    public function get(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('PUT', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, callable|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    /** @param callable[] $middleware each: function (Request, Response, callable $next): Response */
    public function add(string $method, string $pattern, callable|array $handler, array $middleware = []): void
    {
        $pattern = '/' . trim($pattern, '/');
        // {param*} - greedy match, captures remaining path segments including slashes (e.g. /uploads/{path*})
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\*\}#', '(?P<$1>.+)', $pattern);
        // {param} - single segment match
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $regex);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /** Catch-all used by the CMS content resolver (articles/pages/module archive & detail). */
    public function fallback(callable|array $handler): void
    {
        $this->fallback = $handler;
    }

    public function dispatch(Request $request, Response $response): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (preg_match($route['regex'], $request->uri(), $matches)) {
                $params = array_filter(
                    $matches,
                    static fn ($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );

                $request->setParams($params);

                return $this->runChain($route['middleware'], $route['handler'], $request, $response);
            }
        }

        if ($this->fallback !== null) {
            return $this->runChain([], $this->fallback, $request, $response);
        }

        return $response->status(404)->html('404 Not Found');
    }

    /** @param callable[] $routeMiddleware */
    private function runChain(array $routeMiddleware, callable|array $handler, Request $request, Response $response): Response
    {
        $chain = array_merge($this->globalMiddleware, $routeMiddleware);

        $next = function (Request $request, Response $response) use ($handler): Response {
            return $this->call($handler, $request, $response);
        };

        // Build the chain back-to-front so the first middleware registered runs first.
        foreach (array_reverse($chain) as $middleware) {
            $next = function (Request $request, Response $response) use ($middleware, $next): Response {
                return $middleware($request, $response, $next);
            };
        }

        return $next($request, $response);
    }

    private function call(callable|array $handler, Request $request, Response $response): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = is_object($class) ? $class : new $class();
            $result = $controller->{$method}($request, $response);
        } else {
            $result = $handler($request, $response);
        }

        return $result instanceof Response ? $result : $response;
    }
}
