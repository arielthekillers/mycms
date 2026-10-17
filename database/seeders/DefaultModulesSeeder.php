<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;

final class DefaultModulesSeeder
{
    /** @var array<int, array<string, mixed>> */
    private const MODULES = [
        [
            'key_name' => 'agenda',
            'label_singular' => 'Agenda',
            'label_plural' => 'Agenda & Kegiatan',
            'base_route' => '/agenda',
            'provider' => 'App\\Modules\\Agenda\\ModuleProvider',
            'icon' => 'calendar',
            'fields_schema' => ['event_date' => 'date', 'end_date' => 'date|nullable', 'location' => 'string'],
        ],
        [
            'key_name' => 'prestasi',
            'label_singular' => 'Prestasi',
            'label_plural' => 'Prestasi',
            'base_route' => '/prestasi',
            'provider' => 'App\\Modules\\Prestasi\\ModuleProvider',
            'icon' => 'trophy',
            'fields_schema' => ['year' => 'integer', 'level' => 'string', 'achievement_by' => 'string'],
        ],
        [
            'key_name' => 'pengajar',
            'label_singular' => 'Pengajar',
            'label_plural' => 'Direktori Guru',
            'base_route' => '/pengajar',
            'provider' => 'App\\Modules\\Pengajar\\ModuleProvider',
            'icon' => 'user',
            'fields_schema' => ['position' => 'string', 'email' => 'string|nullable', 'phone' => 'string|nullable'],
        ],
        [
            'key_name' => 'galeri',
            'label_singular' => 'Galeri',
            'label_plural' => 'Galeri',
            'base_route' => '/galeri',
            'provider' => 'App\\Modules\\Galeri\\ModuleProvider',
            'icon' => 'image',
            'fields_schema' => [],
        ],
        [
            'key_name' => 'fasilitas',
            'label_singular' => 'Fasilitas',
            'label_plural' => 'Fasilitas',
            'base_route' => '/fasilitas',
            'provider' => 'App\\Modules\\Fasilitas\\ModuleProvider',
            'icon' => 'building',
            'fields_schema' => [],
        ],
        [
            'key_name' => 'alumni',
            'label_singular' => 'Alumni',
            'label_plural' => 'Alumni',
            'base_route' => '/alumni',
            'provider' => 'App\\Modules\\Alumni\\ModuleProvider',
            'icon' => 'graduation-cap',
            'fields_schema' => ['graduation_year' => 'integer', 'current_activity' => 'string|nullable'],
        ],
    ];

    public function run(Database $db): void
    {
        foreach (self::MODULES as $module) {
            $exists = $db->selectOne(
                'SELECT id FROM content_modules WHERE key_name = :key_name',
                ['key_name' => $module['key_name']]
            );

            if ($exists !== null) {
                continue; // already registered — trigger already fired, skip to stay idempotent
            }

            $db->insert(
                'INSERT INTO content_modules
                    (key_name, label_singular, label_plural, base_route, provider, icon, fields_schema)
                 VALUES
                    (:key_name, :label_singular, :label_plural, :base_route, :provider, :icon, :fields_schema)',
                [
                    'key_name' => $module['key_name'],
                    'label_singular' => $module['label_singular'],
                    'label_plural' => $module['label_plural'],
                    'base_route' => $module['base_route'],
                    'provider' => $module['provider'],
                    'icon' => $module['icon'],
                    'fields_schema' => json_encode($module['fields_schema']),
                ]
            );
        }
    }
}
