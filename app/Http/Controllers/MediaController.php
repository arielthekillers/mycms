<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Media\MediaManager;
use RuntimeException;

final class MediaController
{
    public function __construct(private MediaManager $media, private Database $db)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $folderId = $request->query('folder_id');

        $rows = $folderId !== null
            ? $this->db->select('SELECT * FROM media WHERE folder_id = :folder_id ORDER BY id DESC', ['folder_id' => $folderId])
            : $this->db->select('SELECT * FROM media ORDER BY id DESC LIMIT 100');

        return $response->json(['data' => $rows]);
    }

    public function store(Request $request, Response $response): Response
    {
        if (!isset($_FILES['file'])) {
            return $response->json(['message' => 'No file provided.'], 422);
        }

        try {
            $media = $this->media->upload(
                $_FILES['file'],
                $request->input('folder_id') !== null ? (int) $request->input('folder_id') : null,
                null // set from Auth::id() once wired into the controller factory
            );
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 422);
        }

        return $response->json(['data' => $media], 201);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $id = (int) $request->param('id');

        try {
            $this->media->delete($id);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 409); // 409 Conflict: still in use
        }

        return $response->json(['message' => 'Deleted.']);
    }

    public function usages(Request $request, Response $response): Response
    {
        $id = (int) $request->param('id');

        return $response->json(['data' => $this->media->usages($id)]);
    }
}
