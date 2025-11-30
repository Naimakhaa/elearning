<?php
// src/Middleware/RoleMiddleware.php

namespace App\Middleware;

use App\Builders\ApiResponseBuilder;

/**
 * RoleMiddleware
 * --------------
 * Memastikan user punya role tertentu (RBAC sederhana).
 */
class RoleMiddleware
{
    /**
     * @param array $payload hasil dari AuthMiddleware::requireAuth()
     * @param string[] $allowedRoles contoh: ['instructor']
     */
    public static function requireRole(array $payload, array $allowedRoles): void
    {
        $role = $payload['role'] ?? null;

        if ($role === null || !in_array($role, $allowedRoles, true)) {
            ApiResponseBuilder::error('Forbidden: insufficient permissions', 403)->send();
        }
    }
}
