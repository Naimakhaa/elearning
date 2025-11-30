<?php
// src/Exceptions/ValidationException.php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * ValidationException
 * Dipakai ketika Validatable trait menemukan error input.
 */
class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(string $message = 'Validation failed', array $errors = [])
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
