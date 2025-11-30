<?php
// src/Controllers/AuthController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Middleware\AuthMiddleware;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\AuthenticationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * POST /api/auth/login
     * Body:
     * {
     *   "email": "student1@elearning.com",
     *   "password": "password",
     *   "user_type": "student" | "instructor"
     * }
     */
    public function login(): void
    {
        try {
            $data = $this->getJsonInput();

            $email    = $data['email']     ?? '';
            $password = $data['password']  ?? '';
            $userType = $data['user_type'] ?? 'student';

            if ($email === '' || $password === '') {
                ApiResponseBuilder::error('Email and password are required', 400)->send();
            }

            $result = $this->authService->login($email, $password, $userType);

            ApiResponseBuilder::success($result, 'Login successful')->send();

        } catch (AuthenticationException $e) {
            ApiResponseBuilder::error($e->getMessage(), 401)->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * POST /api/auth/refresh
     * Body:
     * {
     *   "refresh_token": "..."
     * }
     */
    public function refresh(): void
    {
        try {
            $data = $this->getJsonInput();
            $refreshToken = $data['refresh_token'] ?? '';

            if ($refreshToken === '') {
                ApiResponseBuilder::error('Refresh token is required', 400)->send();
            }

            $result = $this->authService->refreshToken($refreshToken);

            ApiResponseBuilder::success($result, 'Token refreshed successfully')->send();

        } catch (AuthenticationException $e) {
            ApiResponseBuilder::error($e->getMessage(), 401)->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * GET /api/auth/me
     * Butuh Authorization: Bearer <token>
     */
    public function me(AuthMiddleware $authMiddleware): void
    {
        try {
            $payload = $authMiddleware->handle();

            ApiResponseBuilder::success([
                'id'    => $payload->sub,
                'email' => $payload->email ?? null,
                'name'  => $payload->name ?? null,
                'role'  => $payload->role ?? null,
            ], 'Current user retrieved')->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * POST /api/auth/logout
     * JWT stateless → cukup hapus token di client
     */
    public function logout(): void
    {
        ApiResponseBuilder::success(null, 'Logout successful')->send();
    }
}
