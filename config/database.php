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

// Create tables
$sql = "CREATE TABLE IF NOT EXISTS alumni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_graduated YEAR NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    job_title VARCHAR(100),
    company_name VARCHAR(100),
    company_address TEXT,
    work_position VARCHAR(50),
    is_course_related ENUM('Yes', 'No') NOT NULL,
    employment_status VARCHAR(50) NOT NULL,
    date_started DATE,
    is_current_job ENUM('Yes', 'No') NOT NULL,
    date_ended DATE,
    document_type VARCHAR(50),
    document_upload VARCHAR(255),
    additional_info TEXT,
    signature_data TEXT,
    date_signed DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("Error creating table: " . mysqli_error($conn));
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