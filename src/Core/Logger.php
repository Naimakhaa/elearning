<?php
// src/Core/Logger.php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple file logger
 */
class Logger
{
    private static string $logFile = __DIR__ . '/../../storage/logs/app.log';

    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $date = date('Y-m-d H:i:s');
        $contextStr = $context ? json_encode($context) : '';

        $line = sprintf(
            "[%s] %s: %s %s\n",
            $date,
            strtoupper($level),
            $message,
            $contextStr
        );

        @file_put_contents(self::$logFile, $line, FILE_APPEND);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
}
