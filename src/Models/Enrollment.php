<?php
// src/Models/Enrollment.php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Traits\Validatable;
use DateTime;

/**
 * Enrollment Model
 */
class Enrollment extends Model
{
    use Validatable;

    private int $courseId = 0;
    private int $studentId = 0;
    private DateTime $enrolledAt;
    private ?DateTime $completedAt = null;
    private string $status = 'active'; // active, completed, cancelled
    private ?float $grade = null;

    public function __construct(array $data = [])
    {
        $this->enrolledAt = new DateTime();

        if (!empty($data)) {
            $this->fill($data);
        }
    }

    private function fill(array $data): void
    {
        $this->courseId  = isset($data['course_id']) ? (int) $data['course_id'] : $this->courseId;
        $this->studentId = isset($data['student_id']) ? (int) $data['student_id'] : $this->studentId;

        if (isset($data['enrolled_at'])) {
            $this->enrolledAt = new DateTime($data['enrolled_at']);
        }

        if (isset($data['completed_at']) && $data['completed_at'] !== null) {
            $this->completedAt = new DateTime($data['completed_at']);
        }

        $this->status = $data['status'] ?? $this->status;

        if (isset($data['grade'])) {
            $this->grade = $data['grade'] !== null ? (float) $data['grade'] : null;
        }
    }

    public function validate(): bool
    {
        $this->clearErrors();

        if ($this->courseId <= 0) {
            $this->addError('course_id', 'Valid course_id is required');
        }

        if ($this->studentId <= 0) {
            $this->addError('student_id', 'Valid student_id is required');
        }

        return !$this->hasErrors();
    }

    public function complete(?float $grade = null): void
    {
        $this->status      = 'completed';
        $this->completedAt = new DateTime();
        $this->grade       = $grade;
    }

    public function cancel(): void
    {
        $this->status      = 'cancelled';
        $this->completedAt = null;
        $this->grade       = null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    protected static function getTableName(): string
    {
        return 'enrollments';
    }

    protected function insert(): bool
    {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO enrollments
                (course_id, student_id, enrolled_at, completed_at, status, grade, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);

        $result = $stmt->execute([
            $this->courseId,
            $this->studentId,
            $this->enrolledAt->format('Y-m-d H:i:s'),
            $this->completedAt?->format('Y-m-d H:i:s'),
            $this->status,
            $this->grade,
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

        $sql = "UPDATE enrollments
                SET completed_at = ?, status = ?, grade = ?, updated_at = ?
                WHERE id = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            $this->completedAt?->format('Y-m-d H:i:s'),
            $this->status,
            $this->grade,
            $this->updatedAt?->format('Y-m-d H:i:s'),
            $this->id,
        ]);
    }

    public function delete(): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM enrollments WHERE id = ?');
        return $stmt->execute([$this->id]);
    }

    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'course_id'    => $this->courseId,
            'student_id'   => $this->studentId,
            'enrolled_at'  => $this->enrolledAt->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'status'       => $this->status,
            'grade'        => $this->grade,
            'created_at'   => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
