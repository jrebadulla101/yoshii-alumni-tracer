<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'alumni_tracer');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
} else {
    die("Error creating database: " . mysqli_error($conn));
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
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    middle_initial CHAR(1),
    last_name VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_graduated YEAR NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    company_address TEXT NOT NULL,
    work_position VARCHAR(100) NOT NULL,
    is_course_related ENUM('Yes', 'No') NOT NULL,
    employment_status ENUM('Full-time', 'Part-time', 'Self-employed', 'Unemployed') NOT NULL,
    date_started DATE NOT NULL,
    is_current_job ENUM('Yes', 'No') NOT NULL,
    date_ended DATE,
    document_type ENUM('Alumni ID', 'Student ID', 'Government ID', 'Other') NOT NULL,
    document_upload VARCHAR(255),
    additional_info TEXT,
    signature_data TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_signed DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating alumni table: " . mysqli_error($conn));
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