<?php
// src/Controllers/StudentController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\StudentService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

/**
 * StudentController - Handle HTTP requests untuk Student resources
 *
 * Endpoint:
 *  - POST /api/students
 *  - GET  /api/students
 *  - GET  /api/students/:id
 */
class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {}

    /**
     * GET /api/students
     * Get all students (opsional)
     */
    public function index(): void
    {
        try {
            $filters = $this->getQueryParams();
            $students = $this->studentService->getAllStudents($filters);

            $data = array_map(fn($student) => $student->toArray(), $students);

            ApiResponseBuilder::success($data, 'Students retrieved successfully')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * GET /api/students/:id
     * Get single student by ID
     */
    public function show(int $id): void
    {
        try {
            $student = $this->studentService->getStudentById($id);

            ApiResponseBuilder::success(
                $student->toArray(),
                'Student retrieved successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * POST /api/students
     * Register new student
     *
     * Body contoh:
     * {
     *   "student_number": "STU20250010",
     *   "email": "x@y.com",
     *   "password": "secret",
     *   "name": "Nama Student",
     *   "phone": "0812xxxx",
     *   "enroll_limit": 5
     * }
     */
    public function store(): void
    {
        try {
            $data = $this->getJsonInput();
            $student = $this->studentService->registerStudent($data);

            ApiResponseBuilder::created(
                $student->toArray(),
                'Student registered successfully'
            )->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}
