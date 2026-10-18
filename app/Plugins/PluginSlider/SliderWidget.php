<?php

declare(strict_types=1);

namespace App\Plugins\PluginSlider;

use App\Core\Database;
use App\Theme\WidgetInterface;

final class SliderWidget implements WidgetInterface
{
    /**
     * Render the slider instance in the theme widget area.
     */
    public function render(array $settings, Database $db, ?string $titleOverride): string
    {
        // Fail-safe: jika plugin slider tidak aktif atau fungsi render_slider belum dimuat, jangan render apa-apa
        if (!function_exists('render_slider')) {
            return '';
        }

        $sliderId = $settings['slider_id'] ?? '';
        if (empty($sliderId)) {
            return '';
        }

        $options = [
            'layout' => !empty($settings['layout']) ? $settings['layout'] : null,
            'height' => !empty($settings['height']) ? $settings['height'] : null,
        ];

        $html = render_slider($sliderId, $options);
        if (empty($html)) {
            return '';
        }

        $titleHtml = '';
        if (!empty($titleOverride)) {
            $titleHtml = '<h3 class="widget-title" style="text-align: center; margin-bottom: 1.5rem;">' . htmlspecialchars($titleOverride) . '</h3>';
        }

        $layoutClass = 'slider-layout-' . htmlspecialchars($settings['layout'] ?? 'hero');
        return '<div class="widget widget-slider-showcase ' . $layoutClass . '">' . $titleHtml . $html . '</div>';
    }

    /**
     * Define the form fields in Admin Widget Manager (+ Tambah Widget).
     */
    public function getFormFields(): array
    {
        $basePath = \App\Core\Application::getInstance()->basePath;
        $dir = $basePath . '/storage/data/sliders';
        $sliders = [];
        if (is_dir($dir)) {
            foreach (glob($dir . '/*.json') as $file) {
                $data = json_decode((string) file_get_contents($file), true);
                if (!empty($data['id'])) {
                    $sliders[$data['id']] = ($data['name'] ?? $data['id']) . ' (#' . $data['id'] . ')';
                }
            }
        }

        if (empty($sliders)) {
            return [
                [
                    'name' => 'slider_id',
                    'type' => 'select',
                    'label' => 'Pilih Slider',
                    'options' => ['' => '-- Belum ada slider dibuat (Buka Manajer Slider) --'],
                    'default' => '',
                ]
            ];
        }

        return [
            [
                'name' => 'slider_id',
                'type' => 'select',
                'label' => 'Pilih Slider yang Ingin Ditampilkan',
                'options' => $sliders,
                'default' => array_key_first($sliders) ?? '',
            ],
            [
                'name' => 'layout',
                'type' => 'select',
                'label' => 'Mode Tampilan Ukuran',
                'options' => [
                    'hero' => 'Layar Penuh (Hero 100vh) — Cocok untuk Banner Beranda',
                    'standard' => 'Standar Boxed (~480px, Berbayang) — Cocok untuk Seksi Halaman',
                    'article' => 'Kompak / Artikel (~380px) — Cocok untuk Kolom Berita & Konten',
                ],
                'default' => 'hero',
            ],
            [
                'name' => 'height',
                'type' => 'text',
                'label' => 'Tinggi Kustom (Opsional, contoh: 500px, 60vh — kosongkan untuk default mode)',
                'default' => '',
            ]
        ];
    }
}
