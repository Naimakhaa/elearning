<?php
// src/Services/EnrollmentService.php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Enrollment;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\StudentRepository;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

/**
 * EnrollmentService - Business logic pendaftaran course
 *
 * Aturan bisnis utama:
 * - Course harus ada & published
 * - Course belum penuh (active_enrollments < max_students)
 * - Student harus ada
 * - Student belum terdaftar di course yang sama
 * - Optional: cek limit enroll student
 */
class EnrollmentService
{
    public function __construct(
        private EnrollmentRepository $enrollmentRepository,
        private CourseRepository $courseRepository,
        private StudentRepository $studentRepository
    ) {}

    /**
     * Student mendaftar ke course
     *
     * @throws NotFoundException
     * @throws BusinessException
     * @throws ValidationException
     */
    public function enroll(int $studentId, int $courseId): Enrollment
    {
        $pdo = Database::getInstance()->getConnection();

        // Mulai transaksi
        $pdo->beginTransaction();

        try {
            // 1. Cek course
            $course = $this->courseRepository->findById($courseId);
            if (!$course) {
                throw new NotFoundException("Course with ID {$courseId} not found");
            }

            // 2. Cek student
            $student = $this->studentRepository->findById($studentId);
            if (!$student) {
                throw new NotFoundException("Student with ID {$studentId} not found");
            }

            // 3. Cek apakah sudah terdaftar di course yang sama
            $existing = $this->enrollmentRepository->findByCourseAndStudent($courseId, $studentId);
            if ($existing) {
                throw new BusinessException('Student already enrolled in this course');
            }

            // 4. Cek kapasitas course (pakai hitung active enrollment)
            $activeCount = $this->enrollmentRepository->countActiveByCourseId($courseId);
            if (!$course->canEnroll($activeCount)) {
                throw new BusinessException('Course is full or not published');
            }

            // 5. (Opsional) cek limit enrollment student
            $studentActiveEnrollments = $this->countActiveEnrollmentsByStudentId($studentId);
            if (!$student->canEnrollMore($studentActiveEnrollments)) {
                throw new BusinessException('Student has reached enroll limit');
            }

            // 6. Buat enrollment
            $enrollment = new Enrollment([
                'course_id'  => $courseId,
                'student_id' => $studentId,
            ]);

            if (!$enrollment->validate()) {
                throw new ValidationException('Enrollment validation failed', $enrollment->getErrors());
            }

            $this->enrollmentRepository->save($enrollment);

            // 7. Update current_enrolled pada course
            $course->onEnroll();
            $this->courseRepository->save($course);

            $pdo->commit();

            return $enrollment;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Tandai enrollment completed
     *
     * @throws NotFoundException
     * @throws BusinessException
     */
    public function completeEnrollment(int $id): Enrollment
    {
        $enrollment = $this->enrollmentRepository->findById($id);

        if (!$enrollment) {
            throw new NotFoundException("Enrollment with ID {$id} not found");
        }

        if ($enrollment->getStatus() !== 'active') {
            throw new BusinessException('Only active enrollments can be completed');
        }

        $enrollment->complete();
        $this->enrollmentRepository->save($enrollment);

        return $enrollment;
    }

    /**
     * Batalkan enrollment
     *
     * @throws NotFoundException
     * @throws BusinessException
     */
    public function cancelEnrollment(int $id): Enrollment
    {
        $pdo = Database::getInstance()->getConnection();
        $pdo->beginTransaction();

        try {
            $enrollment = $this->enrollmentRepository->findById($id);

            if (!$enrollment) {
                throw new NotFoundException("Enrollment with ID {$id} not found");
            }

            if ($enrollment->getStatus() !== 'active') {
                throw new BusinessException('Only active enrollments can be cancelled');
            }

            // Ambil course untuk update current_enrolled
            $course = $this->courseRepository->findById($enrollment->getCourseId());
            if ($course) {
                $course->onCancelEnrollment();
                $this->courseRepository->save($course);
            }

            $enrollment->cancel();
            $this->enrollmentRepository->save($enrollment);

            $pdo->commit();

            return $enrollment;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Ambil semua enrollment milik student tertentu
     */
    public function getEnrollmentsByStudentId(int $studentId): array
    {
        return $this->enrollmentRepository->findByStudentId($studentId);
    }

    /**
     * Hitung jumlah enrollment aktif student (membantu cek limit)
     */
    private function countActiveEnrollmentsByStudentId(int $studentId): int
    {
        // pakai repository kalau kamu tambahkan method khusus
        $enrollments = $this->enrollmentRepository->findByStudentId($studentId);

        $count = 0;
        foreach ($enrollments as $enrollment) {
            if ($enrollment->getStatus() === 'active') {
                $count++;
            }
        }

        return $count;
    }
}
