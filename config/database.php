<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'alumni_tracer');

// First, try to connect to the database directly
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// If direct connection fails, try creating the database
if (!$conn) {
    // Connect without database selected
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (mysqli_query($conn, $sql)) {
        // Select the database
        if (!mysqli_select_db($conn, DB_NAME)) {
            die("Error selecting database: " . mysqli_error($conn));
        }
    } else {
        die("Error creating database: " . mysqli_error($conn));
    }
}

// Set the correct charset
mysqli_set_charset($conn, "utf8mb4");

// Debug connection
error_log("=== Database Connection ===");
error_log("Connected to MySQL: " . ($conn ? "Yes" : "No"));
error_log("Selected database: " . DB_NAME);

// Verify current database
$result = mysqli_query($conn, "SELECT DATABASE()");
$db_name = mysqli_fetch_row($result)[0];
error_log("Current database: " . $db_name);

// Debug query to check if alumni table exists and has data
$debug_query = "SELECT COUNT(*) as count FROM alumni";
$debug_result = mysqli_query($conn, $debug_query);
if ($debug_result) {
    $count = mysqli_fetch_assoc($debug_result)['count'];
    error_log("Number of alumni records: " . $count);
} else {
    error_log("Error checking alumni table: " . mysqli_error($conn));
}

// Create courses table
$sql = "CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating courses table: " . mysqli_error($conn));
}

// Insert default courses if not exists
$default_courses = [
    'Bachelor of Science in Information Technology',
    'Bachelor of Science in Computer Science',
    'Bachelor of Science in Business Administration',
    'Bachelor of Science in Accountancy',
    'Bachelor of Science in Engineering'
];

foreach ($default_courses as $course) {
    $sql = "INSERT IGNORE INTO courses (course_name) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $course);
    mysqli_stmt_execute($stmt);
}

// Create alumni table
$sql = "CREATE TABLE IF NOT EXISTS alumni (
    alumni_id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    middle_initial CHAR(1),
    gender ENUM('Male', 'Female', 'Other', 'Prefer not to say'),
    last_name VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_graduated YEAR NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    job_title VARCHAR(100),
    company_name VARCHAR(100),
    company_address TEXT,
    work_position VARCHAR(100),
    is_course_related ENUM('Yes', 'No'),
    employment_status ENUM('Full-time', 'Part-time', 'Self-employed', 'Unemployed') NOT NULL,
    date_started DATE,
    is_current_job ENUM('Yes', 'No'),
    date_ended DATE,
    document_type ENUM('Alumni ID', 'Student ID', 'Government ID', 'Other') NOT NULL,
    document_upload VARCHAR(255),
    additional_info TEXT,
    signature_data TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_signed DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    salary DECIMAL(10,2),
    industry VARCHAR(100)
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating alumni table: " . mysqli_error($conn));
}

// Add student_number column if it doesn't exist
$sql = "SHOW COLUMNS FROM alumni LIKE 'student_number'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE alumni ADD COLUMN student_number VARCHAR(20) NOT NULL UNIQUE AFTER alumni_id";
    if (!mysqli_query($conn, $sql)) {
        die("Error adding student_number column: " . mysqli_error($conn));
    }
}

// Create admin table
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating admin table: " . mysqli_error($conn));
}

// Insert default admin if not exists
$default_username = 'admin';
$default_password = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "INSERT IGNORE INTO admin (username, password) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $default_username, $default_password);
mysqli_stmt_execute($stmt);
?> 