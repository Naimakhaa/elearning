<?php
// src/Services/CourseService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

/**
 * CourseService - Business logic untuk Course
 * Menerapkan Dependency Injection (SOLID - D)
 */
class CourseService
{
    public function __construct(
        private CourseRepository $courseRepository
    ) {}

    /**
     * Ambil list course dengan optional filter (status, category)
     */
    public function getCourses(array $filters = []): array
    {
        return $this->courseRepository->findAll($filters);
    }

    /**
     * Ambil satu course by ID
     *
     * @throws NotFoundException
     */
    public function getCourseById(int $id): Course
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        return $course;
    }

    /**
     * Create course baru
     *
     * @throws ValidationException
     */
    public function createCourse(array $data): Course
    {
        $course = new Course($data);

        if (!$course->validate()) {
            throw new ValidationException('Course validation failed', $course->getErrors());
        }

        $this->courseRepository->save($course);

        return $course;
    }

    /**
     * Update course
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateCourse(int $id, array $data): Course
    {
        $existing = $this->courseRepository->findById($id);

        if (!$existing) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        // Merge data lama + baru
        $merged = array_merge($existing->toArray(), $data);

        $course = new Course($merged);
        $course->setId($id);
        $course->setCreatedAt($existing->getCreatedAt() ?? new \DateTime());

        if (!$course->validate()) {
            throw new ValidationException('Course validation failed', $course->getErrors());
        }

        $this->courseRepository->save($course);

        return $course;
    }

    /**
     * Hapus course
     *
     * @throws NotFoundException
     */
    public function deleteCourse(int $id): bool
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        return $this->courseRepository->delete($id);
    }

    /**
     * Publish course (ubah status jadi 'published')
     *
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function publishCourse(int $id): Course
    {
        $course = $this->courseRepository->findById($id);

        if (!$course) {
            throw new NotFoundException("Course with ID {$id} not found");
        }

        $course->publish();

        if (!$course->validate()) {
            throw new ValidationException('Course validation failed', $course->getErrors());
        }

        $this->courseRepository->save($course);

        return $course;
    }
}
