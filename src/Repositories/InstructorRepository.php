<?php
// src/Repositories/InstructorRepository.php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\QueryLogger;
use App\Models\Instructor;
use PDO;

class InstructorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Instructor
    {
        $sql = 'SELECT * FROM instructors WHERE id = ? LIMIT 1';
        QueryLogger::log($sql, [$id]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByEmail(string $email): ?Instructor
    {
        $sql = 'SELECT * FROM instructors WHERE email = ? LIMIT 1';
        QueryLogger::log($sql, [$email]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByCode(string $code): ?Instructor
    {
        $sql = 'SELECT * FROM instructors WHERE instructor_code = ? LIMIT 1';
        QueryLogger::log($sql, [$code]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM instructors ORDER BY created_at DESC';
        QueryLogger::log($sql);

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function save(Instructor $instructor): bool
    {
        return $instructor->save();
    }

    public function delete(int $id): bool
    {
        $instructor = $this->findById($id);
        if (!$instructor) {
            return false;
        }

        return $instructor->delete();
    }

    private function hydrate(array $row): Instructor
    {
        $instructor = new Instructor($row);
        $instructor->setId((int) $row['id']);

        if (!empty($row['password'])) {
            $instructor->setHashedPassword($row['password']);
        }

        if (!empty($row['created_at'])) {
            $instructor->setCreatedAt(new \DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $instructor->setUpdatedAt(new \DateTime($row['updated_at']));
        }

        return $instructor;
    }
}
