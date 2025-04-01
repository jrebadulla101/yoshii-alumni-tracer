<?php
// Start session
session_start();

// Include database connection
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if table parameter is provided
if (!isset($_GET['table']) || empty($_GET['table'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Table name is required']);
    exit();
}

$table = $_GET['table'];

// Get all database tables to validate the requested table
$tables_query = "SHOW TABLES";
$tables_result = mysqli_query($conn, $tables_query);
$tables = [];
while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

// Validate that the requested table exists
if (!in_array($table, $tables)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid table']);
    exit();
}

// Get table structure
$structure_query = "DESCRIBE $table";
$structure_result = mysqli_query($conn, $structure_query);

if (!$structure_result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error retrieving table structure: ' . mysqli_error($conn)]);
    exit();
}

// Build the structure array
$structure = [];
while ($row = mysqli_fetch_assoc($structure_result)) {
    $structure[] = $row;
}

// Return the structure as JSON
header('Content-Type: application/json');
echo json_encode($structure); 