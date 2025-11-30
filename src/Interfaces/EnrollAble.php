<?php
// src/Interfaces/EnrollAble.php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface untuk entitas yang mendukung enrollment.
 * Pada kasus ini diimplementasikan oleh Course.
 */
interface EnrollAble
{
    /**
     * Cek apakah mahasiswa masih bisa mendaftar ke course ini.
     * Biasanya cek:
     * - status course = published
     * - current_enrolled < max_students
     */
    public function canEnroll(int $currentEnrolledCount): bool;

    /**
     * Dipanggil ketika enrollment berhasil dibuat.
     * Contoh implementasi: increment current_enrolled.
     */
    public function onEnroll(): void;

    /**
     * Dipanggil ketika enrollment dibatalkan/dihapus.
     * Contoh implementasi: decrement current_enrolled.
     */
    public function onCancelEnrollment(): void;
}
