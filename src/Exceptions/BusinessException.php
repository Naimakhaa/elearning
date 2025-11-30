<?php
// src/Exceptions/BusinessException.php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * BusinessException
 * Dipakai untuk error aturan bisnis, misalnya:
 * - Course penuh
 * - Course belum dipublish
 * - Student sudah terdaftar di course yang sama
 */
class BusinessException extends \Exception
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
