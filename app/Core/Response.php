<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private int $status = 200;
    /** @var array<string, string> */
    private array $headers = [];
    private string $body = '';

    public function status(int $code): self
    {
        $this->status = $code;

        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function html(string $content): self
    {
        $this->header('Content-Type', 'text/html; charset=UTF-8');
        $this->body = $content;

        return $this;
    }

    public function raw(string $content, string $contentType): self
    {
        $this->header('Content-Type', $contentType);
        $this->body = $content;

        return $this;
    }

    public function json(mixed $data, int $status = 200): self
    {
        $this->status = $status;
        $this->header('Content-Type', 'application/json; charset=UTF-8');
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }

    public function redirect(string $to, int $status = 302): self
    {
        $this->status = $status;
        $this->header('Location', $to);

        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $this->body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
