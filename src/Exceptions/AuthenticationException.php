<?php
// src/Exceptions/AuthenticationException.php

declare(strict_types=1);

namespace App\Exceptions;

class AuthenticationException extends \Exception
{
    public function __construct(string $message = 'Authentication failed')
    {
        parent::__construct($message, 401);
    }
}
