<?php

declare(strict_types=1);

namespace App\Media;

use App\Core\Database;
use App\Core\Queue\QueueInterface;
use App\Media\Jobs\GenerateMediaVariantsJob;
use RuntimeException;

final class MediaManager
{
    public function __construct(
        private Database $db,
        private QueueInterface $queue,
        private string $uploadBasePath
    ) {
    }

    /**
     * @param array $file a single entry from PHP's $_FILES superglobal
     * @return array<string, mixed> the created media row
     */
    public function upload(array $file, ?int $folderId = null, ?int $uploaderId = null): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed with error code ' . ($file['error'] ?? 'unknown') . '.');
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $mimeType = mime_content_type($tmpPath) ?: ($file['type'] ?? 'application/octet-stream');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(8)) . ($extension !== '' ? ".{$extension}" : '');

        $subDir = date('Y/m');
        $relativePath = "{$subDir}/{$safeName}";
        $destinationDir = rtrim($this->uploadBasePath, '/') . "/{$subDir}";

        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            throw new RuntimeException("Failed to create upload directory: {$destinationDir}");
        }

        $destination = "{$destinationDir}/{$safeName}";

        $moved = is_uploaded_file($tmpPath)
            ? move_uploaded_file($tmpPath, $destination)
            : copy($tmpPath, $destination); // allows CLI/test usage without a real HTTP upload

        if (!$moved) {
            throw new RuntimeException('Failed to move uploaded file to storage.');
        }

        [$width, $height] = str_starts_with($mimeType, 'image/')
            ? (@getimagesize($destination) ?: [null, null])
            : [null, null];

        $mediaId = $this->db->insert(
            'INSERT INTO media (folder_id, uploader_id, file_name, disk_path, disk, mime_type, file_size, width, height)
             VALUES (:folder_id, :uploader_id, :file_name, :disk_path, :disk, :mime_type, :file_size, :width, :height)',
            [
                'folder_id' => $folderId,
                'uploader_id' => $uploaderId,
                'file_name' => $originalName,
                'disk_path' => $relativePath,
                'disk' => 'local',
                'mime_type' => $mimeType,
                'file_size' => filesize($destination) ?: 0,
                'width' => $width,
                'height' => $height,
            ]
        );

        if (str_starts_with($mimeType, 'image/')) {
            $this->queue->push(GenerateMediaVariantsJob::class, ['media_id' => $mediaId]);
        }

        return $this->db->selectOne('SELECT * FROM media WHERE id = :id', ['id' => $mediaId]);
    }

    /**
     * Deletes a media file — but refuses if it's still attached anywhere
     * (see media_usages / "Used In" tracking), to avoid breaking live content.
     */
    public function delete(int $mediaId): void
    {
        $usageCount = (int) ($this->db->selectOne(
            'SELECT COUNT(*) AS c FROM media_usages WHERE media_id = :id',
            ['id' => $mediaId]
        )['c'] ?? 0);

        if ($usageCount > 0) {
            throw new RuntimeException(
                "Media #{$mediaId} is still used in {$usageCount} place(s) and cannot be deleted. " .
                'Detach it from those items first.'
            );
        }

        $media = $this->db->selectOne('SELECT * FROM media WHERE id = :id', ['id' => $mediaId]);

        if ($media === null) {
            return;
        }

        $this->db->execute('DELETE FROM media WHERE id = :id', ['id' => $mediaId]);
        $this->deleteFiles($media);
    }

    /** Returns everywhere a media item is currently attached — the "Used In" list. */
    public function usages(int $mediaId): array
    {
        return $this->db->select(
            'SELECT usable_type, usable_id, usage_context, created_at FROM media_usages WHERE media_id = :id',
            ['id' => $mediaId]
        );
    }

    public function attachUsage(int $mediaId, string $usableType, int $usableId, string $context): void
    {
        $this->db->execute(
            'INSERT IGNORE INTO media_usages (media_id, usable_type, usable_id, usage_context)
             VALUES (:media_id, :usable_type, :usable_id, :context)',
            ['media_id' => $mediaId, 'usable_type' => $usableType, 'usable_id' => $usableId, 'context' => $context]
        );
    }

    public function detachUsage(int $mediaId, string $usableType, int $usableId, string $context): void
    {
        $this->db->execute(
            'DELETE FROM media_usages
             WHERE media_id = :media_id AND usable_type = :usable_type
               AND usable_id = :usable_id AND usage_context = :context',
            ['media_id' => $mediaId, 'usable_type' => $usableType, 'usable_id' => $usableId, 'context' => $context]
        );
    }

    /** Replace a "featured"-style single attachment (detach the old one, attach the new one). */
    public function replaceUsage(?int $oldMediaId, ?int $newMediaId, string $usableType, int $usableId, string $context): void
    {
        if ($oldMediaId !== null && $oldMediaId !== $newMediaId) {
            $this->detachUsage($oldMediaId, $usableType, $usableId, $context);
        }

        if ($newMediaId !== null) {
            $this->attachUsage($newMediaId, $usableType, $usableId, $context);
        }
    }

    private function deleteFiles(array $media): void
    {
        $base = rtrim($this->uploadBasePath, '/');
        @unlink($base . '/' . ltrim($media['disk_path'], '/'));

        $variants = json_decode($media['variants'] ?? '[]', true) ?: [];

        foreach ($variants as $variantPath) {
            @unlink($base . '/' . ltrim($variantPath, '/'));
        }
    }
}
