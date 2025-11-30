<?php
// src/Exceptions/NotFoundException.php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * NotFoundException
 * Dipakai ketika resource (Course, Student, Enrollment, dll) tidak ditemukan.
 */
class NotFoundException extends \Exception
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message, 404);
    }
}
