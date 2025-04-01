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