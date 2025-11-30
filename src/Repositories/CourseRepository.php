<?php
// src/Repositories/CourseRepository.php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\QueryLogger;
use App\Interfaces\Searchable;
use App\Models\Course;
use PDO;

class CourseRepository implements Searchable
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?Course
    {
        $sql = 'SELECT * FROM courses WHERE id = ? LIMIT 1';
        QueryLogger::log($sql, [$id]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByCode(string $code): ?Course
    {
        $sql = 'SELECT * FROM courses WHERE course_code = ? LIMIT 1';
        QueryLogger::log($sql, [$code]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$code]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Ambil semua course (bisa pakai filter: status, category)
     */
    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT * FROM courses WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $sql .= ' AND category = ?';
            $params[] = $filters['category'];
        }

        $sql .= ' ORDER BY created_at DESC';

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    /**
     * Implementasi Searchable::search
     * Cari di title, description, category
     */
    public function search(string $query): array
    {
        $sql = "SELECT * FROM courses
                WHERE title LIKE ?
                   OR description LIKE ?
                   OR category LIKE ?
                ORDER BY title ASC";

        $term = '%' . $query . '%';
        $params = [$term, $term, $term];

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    /**
     * Implementasi Searchable::searchBy
     */
    public function searchBy(string $field, mixed $value): array
    {
        $allowed = ['course_code', 'category', 'status', 'title'];

        if (!in_array($field, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid search field: {$field}");
        }

        $sql = "SELECT * FROM courses WHERE {$field} = ? ORDER BY created_at DESC";
        $params = [$value];

        QueryLogger::log($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function save(Course $course): bool
    {
        return $course->save();
    }

    public function delete(int $id): bool
    {
        $course = $this->findById($id);
        if (!$course) {
            return false;
        }

        return $course->delete();
    }

    private function hydrate(array $row): Course
    {
        $course = new Course($row);
        $course->setId((int) $row['id']);

        if (!empty($row['created_at'])) {
            $course->setCreatedAt(new \DateTime($row['created_at']));
        }
        if (!empty($row['updated_at'])) {
            $course->setUpdatedAt(new \DateTime($row['updated_at']));
        }

        return $course;
    }
}
