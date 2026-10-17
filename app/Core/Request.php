<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $uri;
    /** @var array<string, string> */
    private array $params = [];

    public function __construct(?string $method = null, ?string $uri = null)
    {
        $this->method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $path = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $this->uri = '/' . trim($path, '/');
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri === '' ? '/' : $this->uri;
    }

    /** Route params resolved by the Router, e.g. {slug} */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->method === 'GET' ? $_GET : $_POST;
    }

    public function isJson(): bool
    {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $serverKey = 'HTTP_' . str_replace('-', '_', strtoupper($key));
        return $_SERVER[$serverKey] ?? $default;
    }

    public function json(): array
    {
        $raw = file_get_contents('php://input') ?: '';

        return json_decode($raw, true) ?: [];
    }
}
