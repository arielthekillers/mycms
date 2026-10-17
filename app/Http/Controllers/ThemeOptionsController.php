<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Theme\ThemeOptionsManager;
use RuntimeException;

final class ThemeOptionsController
{
    public function __construct(private ThemeOptionsManager $options, private string $activeTheme)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->options->all($this->activeTheme)]);
    }

    /** Accepts a flat object, e.g. {"primary_color": "#0f766e", "font_heading": "Poppins, sans-serif"} */
    public function update(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $this->options->setMany($this->activeTheme, $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $this->options->all($this->activeTheme)]);
    }
}
