<?php
// src/Factories/CourseFactory.php

namespace App\Factories;

use App\Models\Course;

/**
 * CourseFactory
 * --------------
 * Factory Pattern untuk membuat objek Course
 * tanpa harus menulis konfigurasi berulang di Service/Controller.
 *
 * Menerapkan prinsip Open/Closed:
 * - Jika ingin jenis course baru → cukup tambah method/case baru,
 *   tanpa mengubah class Course.
 */
class CourseFactory
{
    /**
     * Create course berdasarkan tipe.
     *
     * @param string $type  Jenis course: "basic", "premium", "workshop", dll.
     * @param array  $data  Data input (title, description, category, max_students, dll).
     */
    public static function create(string $type, array $data): Course
    {
        switch ($type) {
            case 'basic':
                return self::createBasicCourse($data);

            case 'premium':
                return self::createPremiumCourse($data);

            case 'workshop':
                return self::createWorkshopCourse($data);

            default:
                // fallback: course umum
                $data['course_code'] = $data['course_code'] ?? self::generateCourseCode('GEN');
                return new Course($data);
        }
    }

    /**
     * Basic Course
     * - category default: "Programming"
     * - max_students default: 30
     * - status default: "draft"
     */
    private static function createBasicCourse(array $data): Course
    {
        $data['category']     = $data['category']    ?? 'Programming';
        $data['max_students'] = $data['max_students'] ?? 30;
        $data['status']       = $data['status']      ?? 'draft';
        $data['course_code']  = $data['course_code'] ?? self::generateCourseCode('BSC');

        return new Course($data);
    }

    /**
     * Premium Course
     * - category default: "Premium"
     * - max_students default: 15
     * - status default: "draft"
     */
    private static function createPremiumCourse(array $data): Course
    {
        $data['category']     = $data['category']    ?? 'Premium';
        $data['max_students'] = $data['max_students'] ?? 15;
        $data['status']       = $data['status']      ?? 'draft';
        $data['course_code']  = $data['course_code'] ?? self::generateCourseCode('PRM');

        return new Course($data);
    }

    /**
     * Workshop Course
     * - category default: "Workshop"
     * - max_students default: 10
     * - status default: "published"
     */
    private static function createWorkshopCourse(array $data): Course
    {
        $data['category']     = $data['category']    ?? 'Workshop';
        $data['max_students'] = $data['max_students'] ?? 10;
        $data['status']       = $data['status']      ?? 'published';
        $data['course_code']  = $data['course_code'] ?? self::generateCourseCode('WS');

        return new Course($data);
    }

    /**
     * Bulk create courses dari array data.
     *
     * @param array<int, array<string, mixed>> $coursesData
     * @return Course[]
     */
    public static function createBulk(array $coursesData): array
    {
        $courses = [];

        foreach ($coursesData as $courseData) {
            $type = $courseData['type'] ?? 'basic';
            unset($courseData['type']);

            $courses[] = self::create($type, $courseData);
        }

        return $courses;
    }

    /**
     * Helper sederhana untuk generate course_code.
     * Contoh output: BSC-20251209153001
     */
    private static function generateCourseCode(string $prefix): string
    {
        return sprintf('%s-%s', $prefix, date('YmdHis'));
    }
}
