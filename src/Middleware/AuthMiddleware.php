<?php
// src/Middleware/AuthMiddleware.php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\AuthenticationException;

class AuthMiddleware
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Verifikasi JWT dari header Authorization: Bearer <token>
     * Return: payload token (object) kalau valid
     */
    public function handle(): object
    {
        $token = $this->extractTokenFromHeader();

        if (!$token) {
            ApiResponseBuilder::error('Unauthorized - token not provided', 401)->send();
        }

        try {
            return $this->authService->verifyToken($token);
        } catch (AuthenticationException $e) {
            ApiResponseBuilder::error($e->getMessage(), 401)->send();
        }

        // seharusnya sudah exit di atas
        throw new AuthenticationException('Unauthorized');
    }

    private function extractTokenFromHeader(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];

            if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
