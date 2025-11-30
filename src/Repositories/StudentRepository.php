<?php
// src/Repositories/StudentRepository.php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\QueryLogger;
use App\Models\Student;
use PDO;

class StudentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Student
    {
        $sql = 'SELECT * FROM students WHERE id = ? LIMIT 1';
        QueryLogger::log($sql, [$id]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByEmail(string $email): ?Student
    {
        $sql = 'SELECT * FROM students WHERE email = ? LIMIT 1';
        QueryLogger::log($sql, [$email]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM students ORDER BY created_at DESC';
        QueryLogger::log($sql);

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function save(Student $student): bool
    {
        return $student->save();
    }

    public function delete(int $id): bool
    {
        $student = $this->findById($id);
        if (!$student) {
            return false;
        }

        return $student->delete();
    }

    private function hydrate(array $row): Student
    {
        $student = new Student($row);
        $student->setId((int) $row['id']);

        if (!empty($row['password'])) {
            $student->setHashedPassword($row['password']);
        }

        if (!empty($row['created_at'])) {
            $student->setCreatedAt(new \DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $student->setUpdatedAt(new \DateTime($row['updated_at']));
        }

        return $student;
    }
}
