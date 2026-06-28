<?php
// config.php
session_start();

// ============================================
// DATABASE CONFIGURATION - AUTO DETECT ENVIRONMENT
// ============================================

// Detect if running on localhost or production
$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($isLocal) {
    // Local development
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'school_management');
    define('APP_URL', 'http://localhost/school-management');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // AwardSpace production
    define('DB_HOST', 'fdb1029.awardspace.net');
    define('DB_USER', '4768508_modernhotel');
    define('DB_PASS', 'ModernHotel2026');
    define('DB_NAME', '4768508_modernhotel');
    define('APP_URL', 'https://michaelphotofolio.atwebpages.com/schoolmanagement');
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Application configuration
define('APP_NAME', 'School Management System');
define('TIMEZONE', 'Africa/Nairobi');

// Set timezone
date_default_timezone_set(TIMEZONE);

// File upload configuration
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Pagination
define('ITEMS_PER_PAGE', 15);

// Fee status constants
define('FEE_PENDING', 'pending');
define('FEE_PAID', 'paid');
define('FEE_PARTIAL', 'partial');
define('FEE_OVERDUE', 'overdue');

// Attendance status constants
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_LATE', 'late');
define('ATTENDANCE_EXCUSED', 'excused');

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_PARENT', 'parent');
define('ROLE_STUDENT', 'student');

// Database table prefix
define('DB_PREFIX', 'school_');
?>