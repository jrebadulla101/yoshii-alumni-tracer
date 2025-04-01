<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$where = "";
if (!empty($_GET['course'])) {
    $course = mysqli_real_escape_string($conn, $_GET['course']);
    $where .= " AND course = '$course'";
}
if (!empty($_GET['year'])) {
    $year = mysqli_real_escape_string($conn, $_GET['year']);
    $where .= " AND year_graduated = '$year'";
}

// Helper function to get employment statistics
function getEmploymentStats($conn, $where) {
    $sql = "SELECT 
            employment_status as status, 
            COUNT(*) as count 
            FROM alumni 
            WHERE 1=1 $where
            GROUP BY employment_status";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['status']] = (int)$row['count'];
        }
    }
    return $data;
}

// Helper function to get course-related employment statistics
function getCourseRelatedStats($conn, $where) {
    $sql = "SELECT 
            COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
            COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as course_related,
            ROUND((COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0) / 
            NULLIF(COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END), 0), 1) as percentage
            FROM alumni 
            WHERE 1=1 $where";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $data = $row;
    }
    return $data;
}

// Helper function to get company statistics
function getCompanyStats($conn, $where) {
    $sql = "SELECT 
            company_name, 
            COUNT(*) as count 
            FROM alumni 
            WHERE company_name IS NOT NULL AND company_name != '' $where
            GROUP BY company_name 
            ORDER BY count DESC 
            LIMIT 10";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

// Helper function to get salary statistics
function getSalaryStats($conn, $where) {
    $sql = "SELECT 
            ROUND(AVG(salary), 2) as avg_salary,
            MIN(salary) as min_salary,
            MAX(salary) as max_salary,
            COUNT(CASE WHEN salary < 20000 THEN 1 END) as below_20k,
            COUNT(CASE WHEN salary BETWEEN 20000 AND 30000 THEN 1 END) as k20_30,
            COUNT(CASE WHEN salary BETWEEN 30001 AND 50000 THEN 1 END) as k30_50,
            COUNT(CASE WHEN salary > 50000 THEN 1 END) as above_50k
            FROM alumni 
            WHERE salary IS NOT NULL AND salary > 0 AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') $where";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $data = $row;
    }
    return $data;
}

$stats = [
    'employment' => getEmploymentStats($conn, $where),
    'courseRelated' => getCourseRelatedStats($conn, $where),
    'companies' => getCompanyStats($conn, $where),
    'salary' => getSalaryStats($conn, $where),
    // Add other statistics...
];

header('Content-Type: application/json');
echo json_encode($stats);
?> 