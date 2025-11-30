<?php
// src/Core/Model.php

declare(strict_types=1);

namespace App\Core;

use App\Traits\Timestampable;

/**
 * Abstract Model - Base untuk semua model
 * Menerapkan Abstraksi dan Template Method Pattern
 */
abstract class Model
{
    use Timestampable;

    // Enkapsulasi ID
    protected ?int $id = null;

    // Wajib diimplementasi model turunannya
    abstract public function validate(): bool;
    abstract public function toArray(): array;
    abstract protected static function getTableName(): string;
    abstract protected function insert(): bool;
    abstract protected function update(): bool;
    abstract public function delete(): bool;

    // Getter & Setter ID
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Template method untuk save (insert atau update)
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            // Model biasanya punya trait Validatable yang menyimpan errors
            return false;
        }

        $this->updateTimestamps();

        if ($this->id === null) {
            return $this->insert();
        }

        return $this->update();
    }
}
