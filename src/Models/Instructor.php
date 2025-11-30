<?php
// src/Models/Instructor.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Instructor Model - extends User
 */
class Instructor extends User
{
    private string $instructorCode = '';
    private string $expertise = '';

    protected function fill(array $data): void
    {
        parent::fill($data);

        $this->instructorCode = $data['instructor_code'] ?? $this->instructorCode;
        $this->expertise      = $data['expertise'] ?? $this->expertise;
    }

    public function getRole(): string
    {
        return 'instructor';
    }

    public function hasPermission(string $permission): bool
    {
        $allowed = [
            'create_course',
            'update_course',
            'publish_course',
            'view_own_courses',
        ];

        return in_array($permission, $allowed, true);
    }

    public function getInstructorCode(): string
    {
        return $this->instructorCode;
    }

    public function getExpertise(): string
    {
        return $this->expertise;
    }

    protected static function getTableName(): string
    {
        return 'instructors';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO instructors 
                (instructor_code, email, password, name, phone, expertise, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->instructorCode,
            $this->email,
            $this->password,
            $this->name,
            $this->phone,
            $this->expertise,
            $this->createdAt?->format('Y-m-d H:i:s'),
        ]);

        if ($result) {
            $this->id = (int) $db->lastInsertId();
        }

        return $result;
    }

    protected function update(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE instructors 
                SET email = ?, name = ?, phone = ?, expertise = ?, updated_at = ?
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->phone,
            $this->expertise,
            $this->updatedAt?->format('Y-m-d H:i:s'),
            $this->id,
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM instructors WHERE id = ?');
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['instructor_code'] = $this->instructorCode;
        $data['expertise']       = $this->expertise;

        return $data;
    }
}
