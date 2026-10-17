<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Menu\MenuManager;
use RuntimeException;

final class MenuController
{
    public function __construct(private MenuManager $menus)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->menus->listMenus()]);
    }

    public function items(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->menus->listItemsFlat((int) $request->param('menuId'))]);
    }

    /** GET /admin/menu-target-search?type=article&q=... — powers the autocomplete in the admin UI. */
    public function searchTargets(Request $request, Response $response): Response
    {
        $type = (string) $request->query('type', '');
        $query = (string) $request->query('q', '');

        return $response->json(['data' => $this->menus->searchTargets($type, $query)]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();
        $menu = $this->menus->createMenu($data['name'], $data['slug'], $data['location'] ?? null);

        return $response->json(['data' => $menu], 201);
    }

    public function deleteMenu(Request $request, Response $response): Response
    {
        $this->menus->deleteMenu((int) $request->param('menuId'));
        return $response->json(['message' => 'Menu deleted.']);
    }

    public function addItem(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $item = $this->menus->addItem((int) $request->param('menuId'), $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $item], 201);
    }

    public function updateItem(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $item = $this->menus->updateItem((int) $request->param('itemId'), $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $item]);
    }

    public function deleteItem(Request $request, Response $response): Response
    {
        $this->menus->deleteItem((int) $request->param('itemId'));

        return $response->json(['message' => 'Deleted.']);
    }

    public function reorderItems(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();
        $items = $data['items'] ?? [];

        try {
            $this->menus->reorderItems((int) $request->param('menuId'), $items);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['message' => 'Reordered successfully.']);
    }

    /** Public — resolved tree ready for the theme layer to render. */
    public function showTree(Request $request, Response $response): Response
    {
        $tree = $this->menus->getResolvedTree((string) $request->param('slug'));

        return $response->json(['data' => $tree]);
    }
}
