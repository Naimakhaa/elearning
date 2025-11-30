<?php
// src/Services/AuthService.php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\InstructorRepository;
use App\Models\User;
use App\Exceptions\AuthenticationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $tokenExpiry = 3600; // 1 jam

    public function __construct(
        private StudentRepository $studentRepository,
        private InstructorRepository $instructorRepository
    ) {
        // ambil dari env kalau ada, kalau tidak pakai default
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'ganti-ini-di-production';
    }

    /**
     * Login user dan generate access_token + refresh_token
     *
     * $userType: 'student' atau 'instructor'
     */
    public function login(string $email, string $password, string $userType = 'student'): array
    {
        $user = $this->findUserByEmailAndType($email, $userType);

        if (!$user) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (!$user->authenticate($email, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $accessToken  = $this->generateToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => $this->tokenExpiry,
            'user'          => $user->toArray(),
        ];
    }

    private function generateToken(User $user): string
    {
        $issuedAt = time();
        $expire   = $issuedAt + $this->tokenExpiry;

        $payload = [
            'iss'   => 'elearning-api',
            'iat'   => $issuedAt,
            'exp'   => $expire,
            'sub'   => $user->getId(),
            'email' => $user->getEmail(),
            'role'  => $user->getRole(),
            'name'  => $user->getName(),
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    private function generateRefreshToken(User $user): string
    {
        $issuedAt = time();
        $expire   = $issuedAt + (7 * 24 * 3600); // 7 hari

        $payload = [
            'iss'  => 'elearning-api',
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'sub'  => $user->getId(),
            'type' => 'refresh',
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (\Throwable $e) {
            throw new AuthenticationException('Invalid or expired token');
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        $decoded = $this->verifyToken($refreshToken);

        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            throw new AuthenticationException('Invalid refresh token');
        }

        $user = $this->studentRepository->findById((int)$decoded->sub)
             ?? $this->instructorRepository->findById((int)$decoded->sub);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        $newAccessToken = $this->generateToken($user);

        return [
            'access_token' => $newAccessToken,
            'token_type'   => 'Bearer',
            'expires_in'   => $this->tokenExpiry,
        ];
    }

    public function getCurrentUser(string $token): ?User
    {
        $decoded = $this->verifyToken($token);

        return $this->studentRepository->findById((int)$decoded->sub)
            ?? $this->instructorRepository->findById((int)$decoded->sub);
    }

    private function findUserByEmailAndType(string $email, string $userType): ?User
    {
        return match ($userType) {
            'student'    => $this->studentRepository->findByEmail($email),
            'instructor' => $this->instructorRepository->findByEmail($email),
            default      => null,
        };
    }
}
