<?php
// src/Core/QueryLogger.php

declare(strict_types=1);

namespace App\Core;

/**
 * QueryLogger - opsional, untuk log query manual dari repository
 */
class QueryLogger
{
    public static function log(string $sql, array $params = []): void
    {
        Logger::info('SQL Query', [
            'sql'    => $sql,
            'params' => $params,
        ]);
    }
}
