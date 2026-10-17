<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Theme\WidgetManager;
use RuntimeException;

final class WidgetController
{
    public function __construct(private WidgetManager $widgets)
    {
    }

    public function types(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->widgets->listWidgetTypes()]);
    }

    public function areas(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->widgets->listWidgetAreas()]);
    }

    public function instances(Request $request, Response $response): Response
    {
        return $response->json(['data' => $this->widgets->listInstances((string) $request->param('area'))]);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $instance = $this->widgets->createInstance((string) $request->param('area'), $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $instance], 201);
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        try {
            $instance = $this->widgets->updateInstance((int) $request->param('id'), $data);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $instance]);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $this->widgets->deleteInstance((int) $request->param('id'));

        return $response->json(['message' => 'Deleted.']);
    }

    /** Body: {"order": [3, 1, 2]} — widget_instance IDs in their new top-to-bottom order. */
    public function reorder(Request $request, Response $response): Response
    {
        $data = $request->isJson() ? $request->json() : $request->all();

        if (!isset($data['order']) || !is_array($data['order'])) {
            return $response->json(['message' => 'order (array of widget_instance IDs) is required.'], 422);
        }

        try {
            $this->widgets->reorder((string) $request->param('area'), array_map('intval', $data['order']));
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $this->widgets->listInstances((string) $request->param('area'))]);
    }
}
