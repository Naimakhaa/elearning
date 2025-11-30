<?php
// src/Core/Response.php

declare(strict_types=1);

namespace App\Core;

/**
 * Response helper
 */
class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
