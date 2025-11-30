<?php
// src/Interfaces/Searchable.php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface umum untuk repository yang mendukung pencarian.
 * Contoh implementasi: CourseRepository, StudentRepository.
 */
interface Searchable
{
    /**
     * Pencarian bebas (full text / LIKE) berdasarkan query.
     */
    public function search(string $query): array;

    /**
     * Pencarian spesifik berdasarkan field tertentu.
     * Contoh: searchBy('category', 'Programming')
     */
    public function searchBy(string $field, mixed $value): array;
}
