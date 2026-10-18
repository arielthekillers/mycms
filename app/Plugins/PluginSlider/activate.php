<?php
// activate.php - Auto-register SliderWidget when PluginSlider is activated
try {
    $exists = $db->selectOne("SELECT id FROM widgets WHERE key_name = 'slider_showcase'");
    if (!$exists) {
        $db->insert(
            "INSERT INTO widgets (key_name, label, provider, settings_schema) VALUES (:key, :label, :provider, :schema)",
            [
                'key' => 'slider_showcase',
                'label' => 'Slider Showcase (Splide.js)',
                'provider' => 'App\\Plugins\\PluginSlider\\SliderWidget',
                'schema' => json_encode(['slider_id' => 'string']),
            ]
        );
    }
} catch (\Throwable $e) {
    error_log("SliderWidget activation hook error: " . $e->getMessage());
}
