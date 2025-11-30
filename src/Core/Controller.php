<?php
// src/Core/Controller.php

declare(strict_types=1);

namespace App\Core;

/**
 * Abstract Controller - Base untuk semua controllers
 */
abstract class Controller
{
    /**
     * Ambil body JSON dari request dan decode ke array asosiatif.
     */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');

        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        // Jika JSON invalid atau bukan array, kembalikan array kosong
        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Ambil query string ?key=value dari URL.
     */
    protected function getQueryParams(): array
    {
        return $_GET ?? [];
    }

    /**
     * Helper jika mau kirim response manual (jarang dipakai,
     * karena kamu sudah punya ApiResponseBuilder).
     */
    protected function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
