<?php
// src/Traits/Timestampable.php

namespace App\Traits;

use DateTime;

/**
 * Trait untuk menambahkan timestamp otomatis
 * Traits memungkinkan reuse kode tanpa inheritance
 */
trait Timestampable
{
    protected ?DateTime $createdAt = null;
    protected ?DateTime $updatedAt = null;
    
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }
    
    public function setCreatedAt(DateTime $date): void
    {
        $this->createdAt = $date;
    }
    
    public function setUpdatedAt(DateTime $date): void
    {
        $this->updatedAt = $date;
    }
    
    protected function updateTimestamps(): void
    {
        $now = new DateTime();
        
        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }
        
        $this->updatedAt = $now;
    }
}
