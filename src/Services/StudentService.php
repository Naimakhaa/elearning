<?php
// src/Services/StudentService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

/**
 * StudentService - CRUD & business logic dasar untuk Student
 */
class StudentService
{
    public function __construct(
        private StudentRepository $studentRepository
    ) {}

    public function getAllStudents(): array
    {
        return $this->studentRepository->findAll();
    }

    /**
     * @throws NotFoundException
     */
    public function getStudentById(int $id): Student
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        return $student;
    }

    /**
     * @throws ValidationException
     */
    public function createStudent(array $data): Student
    {
        $student = new Student($data);

        if (!$student->validate()) {
            throw new ValidationException('Student validation failed', $student->getErrors());
        }

        $this->studentRepository->save($student);

        return $student;
    }

    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function updateStudent(int $id, array $data): Student
    {
        $existing = $this->studentRepository->findById($id);

        if (!$existing) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        $merged = array_merge($existing->toArray(), $data);

        $student = new Student($merged);
        $student->setId($id);
        $student->setCreatedAt($existing->getCreatedAt() ?? new \DateTime());

        if (!$student->validate()) {
            throw new ValidationException('Student validation failed', $student->getErrors());
        }

        $this->studentRepository->save($student);

        return $student;
    }

    /**
     * @throws NotFoundException
     */
    public function deleteStudent(int $id): bool
    {
        $student = $this->studentRepository->findById($id);

        if (!$student) {
            throw new NotFoundException("Student with ID {$id} not found");
        }

        return $this->studentRepository->delete($id);
    }
}
