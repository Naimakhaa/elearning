<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Builders\ApiResponseBuilder;

// Repositories
use App\Repositories\CourseRepository;
use App\Repositories\StudentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\EnrollmentRepository;

// Services
use App\Services\CourseService;
use App\Services\StudentService;
use App\Services\EnrollmentService;
use App\Services\AuthService;

// Controllers
use App\Controllers\CourseController;
use App\Controllers\StudentController;
use App\Controllers\EnrollmentController;
use App\Controllers\AuthController;

// Middleware
use App\Middleware\AuthMiddleware;

/**
 * Global error & exception handling
 */
set_exception_handler(function (\Throwable $e): void {
    ApiResponseBuilder::error(
        $e->getMessage(),
        ($e->getCode() >= 400 && $e->getCode() <= 599) ? $e->getCode() : 500
    )->send();
});

/**
 * CORS headers
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight request (OPTIONS) langsung di-OK-kan
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Default response type
header('Content-Type: application/json');

// =======================================================
//  DEPENDENCY INJECTION (Repositories, Services, Controllers)
// =======================================================

// Repositories
$courseRepository     = new CourseRepository();
$studentRepository    = new StudentRepository();
$instructorRepository = new InstructorRepository();
$enrollmentRepository = new EnrollmentRepository();

// Services
$courseService     = new CourseService($courseRepository, $instructorRepository);
$studentService    = new StudentService($studentRepository);
$enrollmentService = new EnrollmentService(
    $enrollmentRepository,
    $courseRepository,
    $studentRepository
);
$authService = new AuthService($studentRepository, $instructorRepository);

// Controllers
$courseController     = new CourseController($courseService);
$studentController    = new StudentController($studentService);
$enrollmentController = new EnrollmentController($enrollmentService);
$authController       = new AuthController($authService);

// Middleware
$authMiddleware = new AuthMiddleware($authService);

// =======================================================
//  ROUTER SETUP
//  Penting: prefix "/api" diberikan di sini
// =======================================================

// 👇 INI PENTING: beri basePath "/api"
$router = new Router('/api');

/**
 * AUTH ROUTES (LOGIN, REFRESH, ME, LOGOUT)
 */

// POST /api/auth/login
$router->post('/auth/login', [$authController, 'login']);

// POST /api/auth/refresh
$router->post('/auth/refresh', [$authController, 'refresh']);

// GET /api/auth/me  (protected, butuh Authorization: Bearer <token>)
$router->get('/auth/me', function () use ($authController, $authMiddleware): void {
    $authController->me($authMiddleware);
});

// POST /api/auth/logout
$router->post('/auth/logout', [$authController, 'logout']);

/**
 * COURSE ROUTES
 *
 * - GET    /api/courses
 * - GET    /api/courses/{id}
 * - POST   /api/courses
 * - PUT    /api/courses/{id}
 * - DELETE /api/courses/{id}
 * - PUT    /api/courses/{id}/publish
 */

// List courses (optional filter: status, category)
$router->get('/courses', [$courseController, 'index']);

// Get single course
$router->get('/courses/:id', [$courseController, 'show']);

// Create new course
$router->post('/courses', [$courseController, 'store']);

// Update course
$router->put('/courses/:id', [$courseController, 'update']);

// Delete course
$router->delete('/courses/:id', [$courseController, 'destroy']);

// Publish course
$router->put('/courses/:id/publish', [$courseController, 'publish']);

/**
 * STUDENT ROUTES
 *
 * - GET  /api/students
 * - GET  /api/students/{id}
 * - POST /api/students
 */

// List all students
$router->get('/students', [$studentController, 'index']);

// Get single student by ID
$router->get('/students/:id', [$studentController, 'show']);

// Create new student (registrasi student)
$router->post('/students', [$studentController, 'store']);

/**
 * ENROLLMENT ROUTES
 *
 * - POST /api/enrollments
 * - PUT  /api/enrollments/{id}/complete
 * - PUT  /api/enrollments/{id}/cancel
 * - GET  /api/students/{id}/enrollments
 */

// Enroll student ke course
$router->post('/enrollments', [$enrollmentController, 'store']);

// Mark enrollment as completed
$router->put('/enrollments/:id/complete', [$enrollmentController, 'complete']);

// Cancel enrollment
$router->put('/enrollments/:id/cancel', [$enrollmentController, 'cancel']);

// Get all enrollments for a student
$router->get('/students/:id/enrollments', [$enrollmentController, 'studentEnrollments']);

// =======================================================
//  DISPATCH ROUTER
// =======================================================

$router->dispatch();
