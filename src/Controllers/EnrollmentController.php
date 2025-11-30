<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\EnrollmentService;
use App\Builders\ApiResponseBuilder;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessException;

/**
 * EnrollmentController - Handle pendaftaran course (enrollments)
 *
 * Endpoint:
 *  - POST /api/enrollments
 *  - PUT  /api/enrollments/:id/complete
 *  - PUT  /api/enrollments/:id/cancel
 *  - GET  /api/students/:id/enrollments
 */
class EnrollmentController extends Controller
{
    public function __construct(
        private EnrollmentService $enrollmentService
    ) {}

    /**
     * POST /api/enrollments
     * Body:
     * {
     *   "student_id": 1,
     *   "course_id": 2
     * }
     */
    public function store(): void
    {
        try {
            $data = $this->getJsonInput();

            if (!isset($data['student_id']) || !isset($data['course_id'])) {
                ApiResponseBuilder::error('student_id and course_id are required', 400)->send();
            }

            $enrollment = $this->enrollmentService->enroll(
                (int)$data['student_id'],
                (int)$data['course_id']
            );

            ApiResponseBuilder::created(
                $enrollment->toArray(),
                'Enrollment created successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();

        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();

        } catch (ValidationException $e) {
            ApiResponseBuilder::validationError($e->getErrors())->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * PUT /api/enrollments/:id/complete
     */
    public function complete(int $id): void
    {
        try {
            $enrollment = $this->enrollmentService->completeEnrollment($id);

            ApiResponseBuilder::success(
                $enrollment->toArray(),
                'Enrollment marked as completed'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();

        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * PUT /api/enrollments/:id/cancel
     */
    public function cancel(int $id): void
    {
        try {
            $enrollment = $this->enrollmentService->cancelEnrollment($id);

            ApiResponseBuilder::success(
                $enrollment->toArray(),
                'Enrollment cancelled successfully'
            )->send();

        } catch (NotFoundException $e) {
            ApiResponseBuilder::notFound($e->getMessage())->send();

        } catch (BusinessException $e) {
            ApiResponseBuilder::error($e->getMessage(), $e->getCode() ?: 400)->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }

    /**
     * GET /api/students/:id/enrollments
     */
    public function studentEnrollments(int $studentId): void
    {
        try {
            $enrollments = $this->enrollmentService->getEnrollmentsByStudentId($studentId);
            $data = array_map(fn($enrollment) => $enrollment->toArray(), $enrollments);

            ApiResponseBuilder::success($data, 'Student enrollments retrieved')
                ->addMeta('total_enrollments', count($data))
                ->send();

        } catch (\Throwable $e) {
            ApiResponseBuilder::error($e->getMessage(), 500)->send();
        }
    }
}
