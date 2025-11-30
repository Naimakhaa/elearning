<?php
// src/Models/Student.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Student Model - extends User
 */
class Student extends User
{
    private string $studentNumber = '';
    private int $enrollLimit = 5;

    protected function fill(array $data): void
    {
        parent::fill($data);

        $this->studentNumber = $data['student_number'] ?? $this->studentNumber;
        $this->enrollLimit   = isset($data['enroll_limit'])
            ? (int) $data['enroll_limit']
            : $this->enrollLimit;
    }

    public function getRole(): string
    {
        return 'student';
    }

    public function hasPermission(string $permission): bool
    {
        $allowed = [
            'view_course',
            'enroll_course',
            'view_own_enrollments',
        ];

        return in_array($permission, $allowed, true);
    }

    public function getStudentNumber(): string
    {
        return $this->studentNumber;
    }

    public function getEnrollLimit(): int
    {
        return $this->enrollLimit;
    }

    public function canEnrollMore(int $currentEnrollments): bool
    {
        return $currentEnrollments < $this->enrollLimit;
    }

    protected static function getTableName(): string
    {
        return 'students';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO students 
                (student_number, email, password, name, phone, enroll_limit, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->studentNumber,
            $this->email,
            $this->password,
            $this->name,
            $this->phone,
            $this->enrollLimit,
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

        $sql = "UPDATE students 
                SET email = ?, name = ?, phone = ?, enroll_limit = ?, updated_at = ?
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->email,
            $this->name,
            $this->phone,
            $this->enrollLimit,
            $this->updatedAt?->format('Y-m-d H:i:s'),
            $this->id,
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM students WHERE id = ?');
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['student_number'] = $this->studentNumber;
        $data['enroll_limit']   = $this->enrollLimit;

        return $data;
    }
}
