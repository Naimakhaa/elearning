-- ===========================
-- PERFORMANCE INDEXES
-- ===========================

USE elearning_db;

-- Course search optimization
CREATE INDEX idx_course_title ON courses (title);
CREATE INDEX idx_course_category ON courses (category);
CREATE INDEX idx_course_status ON courses (status);

-- Student fast lookup
CREATE INDEX idx_student_email ON students (email);

-- Enrollment reporting
CREATE INDEX idx_enroll_status ON enrollments (status);
CREATE INDEX idx_enroll_course ON enrollments (course_id);
CREATE INDEX idx_enroll_student ON enrollments (student_id);
