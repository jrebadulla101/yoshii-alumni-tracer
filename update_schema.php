<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'alumni_tracer');

// Connect to database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected to the database successfully.<br>";

// Check if the gender column already exists
$check_column_query = "SHOW COLUMNS FROM alumni LIKE 'gender'";
$check_column_result = mysqli_query($conn, $check_column_query);

if (mysqli_num_rows($check_column_result) > 0) {
    echo "The 'gender' column already exists in the alumni table.<br>";
} else {
    // Add the gender column after middle_initial
    $alter_query = "ALTER TABLE alumni ADD COLUMN gender ENUM('Male', 'Female', 'Other', 'Prefer not to say') AFTER middle_initial";
    
    if (mysqli_query($conn, $alter_query)) {
        echo "The 'gender' column has been successfully added to the alumni table.<br>";
    } else {
        echo "Error adding the 'gender' column: " . mysqli_error($conn) . "<br>";
    }
}

// Check if the salary column exists, add if it doesn't
$check_salary_query = "SHOW COLUMNS FROM alumni LIKE 'salary'";
$check_salary_result = mysqli_query($conn, $check_salary_query);

if (mysqli_num_rows($check_salary_result) == 0) {
    $alter_salary_query = "ALTER TABLE alumni ADD COLUMN salary DECIMAL(10,2) AFTER updated_at";
    
    if (mysqli_query($conn, $alter_salary_query)) {
        echo "The 'salary' column has been successfully added to the alumni table.<br>";
    } else {
        echo "Error adding the 'salary' column: " . mysqli_error($conn) . "<br>";
    }
}

// Check if the industry column exists, add if it doesn't
$check_industry_query = "SHOW COLUMNS FROM alumni LIKE 'industry'";
$check_industry_result = mysqli_query($conn, $check_industry_query);

if (mysqli_num_rows($check_industry_result) == 0) {
    $alter_industry_query = "ALTER TABLE alumni ADD COLUMN industry VARCHAR(100) AFTER salary";
    
    if (mysqli_query($conn, $alter_industry_query)) {
        echo "The 'industry' column has been successfully added to the alumni table.<br>";
    } else {
        echo "Error adding the 'industry' column: " . mysqli_error($conn) . "<br>";
    }
}

// Also update the CREATE TABLE statement in config/database.php
echo "<br>Note: You should also update the CREATE TABLE statement in config/database.php to include these columns.<br>";

// Close the connection
mysqli_close($conn);
echo "<br>Database update complete.";
?> 