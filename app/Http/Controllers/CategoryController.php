<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\CategoryManager;
use App\Core\Request;
use App\Core\Response;

final class CategoryController
{
    public function __construct(private CategoryManager $categories)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->categories->list()]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->json();
        $category = $this->categories->create($data);
        return $response->json(['data' => $category]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $this->categories->delete((int) $request->param('id'));
        return $response->json(['success' => true]);
    }
}
