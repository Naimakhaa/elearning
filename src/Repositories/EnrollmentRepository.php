<?php
// src/Repositories/EnrollmentRepository.php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\QueryLogger;
use App\Models\Enrollment;
use PDO;

class EnrollmentRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Enrollment
    {
        $sql = 'SELECT * FROM enrollments WHERE id = ? LIMIT 1';
        QueryLogger::log($sql, [$id]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Cek apakah student sudah terdaftar di course tertentu
     */
    public function findByCourseAndStudent(int $courseId, int $studentId): ?Enrollment
    {
        $sql = 'SELECT * FROM enrollments WHERE course_id = ? AND student_id = ? LIMIT 1';
        $params = [$courseId, $studentId];

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByStudentId(int $studentId): array
    {
        $sql = 'SELECT * FROM enrollments WHERE student_id = ? ORDER BY enrolled_at DESC';
        $params = [$studentId];

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function countActiveByCourseId(int $courseId): int
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM enrollments 
                WHERE course_id = ? AND status = 'active'";
        $params = [$courseId];

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ? (int) $row['total'] : 0;
    }

    public function save(Enrollment $enrollment): bool
    {
        return $enrollment->save();
    }

    public function delete(int $id): bool
    {
        $enrollment = $this->findById($id);
        if (!$enrollment) {
            return false;
        }

        return $enrollment->delete();
    }

    private function hydrate(array $row): Enrollment
    {
        $enrollment = new Enrollment($row);
        $enrollment->setId((int) $row['id']);

        if (!empty($row['created_at'])) {
            $enrollment->setCreatedAt(new \DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $enrollment->setUpdatedAt(new \DateTime($row['updated_at']));
        }

        return $enrollment;
    }
}
