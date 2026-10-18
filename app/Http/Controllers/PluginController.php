<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\PluginManager;
use App\Core\Request;
use App\Core\Response;

final class PluginController
{
    public function __construct(private PluginManager $plugins)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $response->json([
            'data' => $this->plugins->discover()
        ]);
    }

    public function toggle(Request $request, Response $response): Response
    {
        $dir = $request->param('directory');
        $active = (bool) ($request->isJson() ? ($request->json()['is_active'] ?? false) : $request->input('is_active'));
        
        $this->plugins->toggle((string)$dir, $active);
        
        try {
            \App\Core\Application::getInstance()->cache->flush();
        } catch (\Throwable $e) {}

        return $response->json([
            'message' => 'Status plugin berhasil diubah.'
        ]);
    }

    public function upload(Request $request, Response $response): Response
    {
        $file = $_FILES['zip_file'] ?? null;
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $response->status(400)->json([
                'message' => 'Gagal mengunggah file. Pastikan file valid.'
            ]);
        }
        
        if ($file['type'] !== 'application/zip' && $file['type'] !== 'application/x-zip-compressed' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
            return $response->status(400)->json([
                'message' => 'Format file tidak didukung. Harap unggah file ZIP.'
            ]);
        }

        try {
            $info = $this->plugins->installFromZip($file['tmp_name']);
            return $response->json([
                'message' => "Plugin '{$info['name']}' berhasil dipasang.",
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return $response->status(400)->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request, Response $response): Response
    {
        $dir = $request->param('directory');
        if (!$dir) {
            return $response->status(400)->json(['message' => 'Direktori plugin tidak valid.']);
        }

        try {
            $this->plugins->uninstall((string)$dir);
            return $response->json(['message' => 'Plugin berhasil dihapus secara permanen.']);
        } catch (\Exception $e) {
            return $response->status(400)->json(['message' => 'Gagal menghapus plugin: ' . $e->getMessage()]);
        }
    }
}
