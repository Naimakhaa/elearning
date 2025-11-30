<?php
// src/Interfaces/Publishable.php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface untuk entitas yang punya siklus publish/draft.
 * Diimplementasikan oleh Course.
 */
interface Publishable
{
    /**
     * Publish resource (misalnya ubah status -> "published").
     */
    public function publish(): void;

    /**
     * Kembalikan ke draft / unpublish.
     */
    public function unpublish(): void;

    /**
     * Status saat ini (draft/published/archived).
     */
    public function getStatus(): string;

    /**
     * True kalau status = "published".
     */
    public function isPublished(): bool;
}
