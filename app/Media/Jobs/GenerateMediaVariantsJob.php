<?php

declare(strict_types=1);

namespace App\Media\Jobs;

use App\Core\Application;
use App\Core\Database;
use App\Core\Queue\ShouldQueue;

/**
 * Runs off the request cycle (via bin/console queue:work) so a large
 * upload never blocks the HTTP response. Produces:
 *   - a thumbnail (max width 300px)
 *   - a medium size (max width 800px)
 *   - a .webp copy of the original, for modern browsers
 */
final class GenerateMediaVariantsJob implements ShouldQueue
{
    private const SIZES = [
        'thumb' => 300,
        'medium' => 800,
    ];

    public static function handle(Database $db, array $payload): void
    {
        $mediaId = (int) $payload['media_id'];
        $media = $db->selectOne('SELECT * FROM media WHERE id = :id', ['id' => $mediaId]);

        if ($media === null || !str_starts_with($media['mime_type'], 'image/')) {
            return;
        }

        $uploadRoot = Application::getInstance()->basePath . '/storage/uploads';
        $sourcePath = $uploadRoot . '/' . ltrim($media['disk_path'], '/');

        if (!is_file($sourcePath)) {
            return;
        }

        $sourceImage = self::loadImage($sourcePath, $media['mime_type']);

        if ($sourceImage === null) {
            return; // unsupported/corrupt image — leave media row as-is, no variants
        }

        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);

        $pathInfo = pathinfo($media['disk_path']);
        $dir = $uploadRoot . '/' . $pathInfo['dirname'];
        $filenameNoExt = $pathInfo['filename'];

        $variants = [];

        foreach (self::SIZES as $label => $maxWidth) {
            if ($originalWidth <= $maxWidth) {
                continue; // never upscale
            }

            $ratio = $maxWidth / $originalWidth;
            $targetHeight = (int) round($originalHeight * $ratio);
            $resized = imagescale($sourceImage, $maxWidth, $targetHeight);

            $variantRelative = "{$pathInfo['dirname']}/{$filenameNoExt}_{$label}.{$pathInfo['extension']}";
            self::saveImage($resized, "{$dir}/{$filenameNoExt}_{$label}.{$pathInfo['extension']}", $media['mime_type']);
            imagedestroy($resized);

            $variants[$label] = $variantRelative;
        }

        // WebP copy of the original, if the GD build supports it.
        if (function_exists('imagewebp')) {
            $webpRelative = "{$pathInfo['dirname']}/{$filenameNoExt}.webp";
            imagewebp($sourceImage, "{$dir}/{$filenameNoExt}.webp", 82);
            $variants['webp'] = $webpRelative;
        }

        imagedestroy($sourceImage);

        $db->execute(
            'UPDATE media SET variants = :variants, width = :width, height = :height WHERE id = :id',
            [
                'variants' => json_encode($variants, JSON_UNESCAPED_UNICODE),
                'width' => $originalWidth,
                'height' => $originalHeight,
                'id' => $mediaId,
            ]
        );
    }

    /** @return \GdImage|null */
    private static function loadImage(string $path, string $mimeType)
    {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        } ?: null;
    }

    private static function saveImage(\GdImage $image, string $path, string $mimeType): void
    {
        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, 85),
            'image/png' => imagepng($image, $path, 6),
            'image/gif' => imagegif($image, $path),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, 85) : null,
            default => null,
        };
    }
}
