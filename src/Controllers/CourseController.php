<?php
// src/Controllers/CourseController.php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CourseService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;

/**
 * CourseController
 * 
 * Endpoint (prefix: /api):
 *  - GET    /courses
 *  - GET    /courses/{id}
 *  - POST   /courses
 *  - PUT    /courses/{id}
 *  - DELETE /courses/{id}
 *  - PUT    /courses/{id}/publish
 */
class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService
    ) {}

    /**
     * GET /api/courses
     */
    public function index(): void
    {
        try {
            $filters = $this->getQueryParams();
            $courses = $this->courseService->getCourses($filters);

            $data = array_map(fn($course) => $course->toArray(), $courses);

            ApiResponseBuilder::success($data, 'Courses retrieved successfully')
                ->addMeta('total', count($data))
                ->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * GET /api/courses/{id}
     */
    public function show(int $id): void
    {
        try {
            $course = $this->courseService->getCourseById($id);

            ApiResponseBuilder::success(
                $course->toArray(),
                'Course retrieved successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * POST /api/courses
     */
    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            if (empty($data)) {
                ApiResponseBuilder::error('Request body is empty', 400)->send();
            }

            $course = $this->courseService->createCourse($data);

            ApiResponseBuilder::created(
                $course->toArray(),
                'Course created successfully'
            )->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * PUT /api/courses/{id}
     */
    public function update(int $id): void
    {
        try {
            $data = $this->getJsonInput();

            if (empty($data)) {
                ApiResponseBuilder::error('Request body is empty', 400)->send();
            }

            $course = $this->courseService->updateCourse($id, $data);

            ApiResponseBuilder::success(
                $course->toArray(),
                'Course updated successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * DELETE /api/courses/{id}
     */
    public function destroy(int $id): void
    {
        try {
            $this->courseService->deleteCourse($id);

            ApiResponseBuilder::success(null, 'Course deleted successfully')->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * PUT /api/courses/{id}/publish
     */
    public function publish(int $id): void
    {
        try {
            $course = $this->courseService->publishCourse($id);

            ApiResponseBuilder::success(
                $course->toArray(),
                'Course published successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();
        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}
