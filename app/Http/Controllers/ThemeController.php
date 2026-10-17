<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Theme\ThemeManager;
use RuntimeException;

final class ThemeController
{
    public function __construct(private ThemeManager $themes)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json([
            'data' => $this->themes->getAll()
        ]);
    }

    public function upload(Request $request, Response $response): Response
    {
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $response->json(['message' => 'Gagal mengunggah file tema.'], 400);
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'zip') {
            return $response->json(['message' => 'File harus berformat ZIP.'], 400);
        }

        try {
            $dirName = $this->themes->extractZip($tmpFile);
            return $response->json([
                'message' => 'Tema berhasil diunggah dan diekstrak.',
                'directory' => $dirName
            ]);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 400);
        }
    }

    public function activate(Request $request, Response $response): Response
    {
        $dir = $request->param('directory');
        try {
            $this->themes->activate((string) $dir);
            return $response->json(['message' => 'Tema berhasil diaktifkan.']);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, Response $response): Response
    {
        $dir = $request->param('directory');
        try {
            $this->themes->delete((string) $dir);
            return $response->json(['message' => 'Tema berhasil dihapus.']);
        } catch (RuntimeException $e) {
            return $response->json(['message' => $e->getMessage()], 400);
        }
    }
}
