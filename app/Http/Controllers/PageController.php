<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\PageManager;
use App\Core\Request;
use App\Core\Response;
use RuntimeException;

final class PageController
{
    public function __construct(private PageManager $pages)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->pages->list()]);
    }

    public function show(Request $request, Response $response): Response
    {
        $page = $this->pages->find((int) $request->param('id'));

        return $page !== null
            ? $response->json(['data' => $page])
            : $response->json(['message' => 'Page not found.'], 404);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $page = $this->pages->create($data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $page], 201);
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $page = $this->pages->update((int) $request->param('id'), $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $page]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $this->pages->delete((int) $request->param('id'));

        return $response->json(['message' => 'Deleted.']);
    }
}
