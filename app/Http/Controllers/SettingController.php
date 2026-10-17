<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class SettingController
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function index(Request $request, Response $response): Response
    {
        $rows = $this->db->select("SELECT `key`, `value` FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $response->json(['data' => $settings]);
    }

    public function update(Request $request, Response $response): Response
    {
        $input = $request->json();

        if (empty($input) || !is_array($input)) {
            return $response->status(400)->json(['error' => 'Invalid data.']);
        }

        $this->db->transaction(function () use ($input) {
            foreach ($input as $key => $value) {
                // simple validation or sanitization can be added here
                if (is_scalar($value)) {
                    $exists = $this->db->selectOne("SELECT id FROM settings WHERE `key` = :key", ['key' => $key]);
                    if ($exists) {
                        $this->db->execute(
                            "UPDATE settings SET `value` = :value WHERE `key` = :key",
                            ['key' => $key, 'value' => (string)$value]
                        );
                    } else {
                        $this->db->insert(
                            "INSERT INTO settings (`key`, `value`) VALUES (:key, :value)",
                            ['key' => $key, 'value' => (string)$value]
                        );
                    }
                }
            }
        });

        return $response->json(['message' => 'Settings updated successfully.']);
    }
}
