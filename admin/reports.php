<?php
// Start session before ANY output
session_start();

// Include database connection
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Set default values for filters
$report_type = isset($_GET['type']) ? $_GET['type'] : 'employment';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$format = isset($_GET['format']) ? $_GET['format'] : 'html';

// Get all distinct courses
$courses_query = "SELECT DISTINCT course FROM alumni ORDER BY course";
$courses_result = mysqli_query($conn, $courses_query);
$courses = [];
while ($row = mysqli_fetch_assoc($courses_result)) {
    $courses[] = $row['course'];
}

// Get all distinct years
$years_query = "SELECT DISTINCT year_graduated FROM alumni ORDER BY year_graduated DESC";
$years_result = mysqli_query($conn, $years_query);
$years = [];
while ($row = mysqli_fetch_assoc($years_result)) {
    $years[] = $row['year_graduated'];
}

// Function to get employment reports
function getEmploymentReport($conn, $course = '', $year = '') {
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($course)) {
        $where_clauses[] = "course = ?";
        $params[] = $course;
        $types .= 's';
    }
    
    if (!empty($year)) {
        $where_clauses[] = "year_graduated = ?";
        $params[] = $year;
        $types .= 's';
    }
    
    $where_sql = empty($where_clauses) ? "" : "WHERE " . implode(" AND ", $where_clauses);
    
    $sql = "SELECT 
            alumni_id, 
            student_number,
            CONCAT(first_name, ' ', last_name) as full_name,
            course, 
            year_graduated,
            employment_status,
            job_title,
            company_name,
            is_course_related,
            salary
        FROM alumni 
        $where_sql
        ORDER BY year_graduated DESC, last_name, first_name";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get course reports
function getCourseReport($conn, $course = '', $year = '') {
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($course)) {
        $where_clauses[] = "course = ?";
        $params[] = $course;
        $types .= 's';
    }
    
    if (!empty($year)) {
        $where_clauses[] = "year_graduated = ?";
        $params[] = $year;
        $types .= 's';
    }
    
    $where_sql = empty($where_clauses) ? "" : "WHERE " . implode(" AND ", $where_clauses);
    
    $sql = "SELECT 
            course,
            COUNT(*) as total_alumni,
            COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
            COUNT(CASE WHEN is_course_related = 'Yes' THEN 1 END) as course_related,
            ROUND(AVG(CASE WHEN salary > 0 THEN salary ELSE NULL END), 2) as avg_salary
        FROM alumni 
        $where_sql
        GROUP BY course
        ORDER BY course";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Function to get year reports
function getYearReport($conn, $course = '', $year = '') {
    $where_clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($course)) {
        $where_clauses[] = "course = ?";
        $params[] = $course;
        $types .= 's';
    }
    
    if (!empty($year)) {
        $where_clauses[] = "year_graduated = ?";
        $params[] = $year;
        $types .= 's';
    }
    
    $where_sql = empty($where_clauses) ? "" : "WHERE " . implode(" AND ", $where_clauses);
    
    $sql = "SELECT 
            year_graduated,
            COUNT(*) as total_alumni,
            COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
            COUNT(CASE WHEN is_course_related = 'Yes' THEN 1 END) as course_related,
            ROUND(AVG(CASE WHEN salary > 0 THEN salary ELSE NULL END), 2) as avg_salary
        FROM alumni 
        $where_sql
        GROUP BY year_graduated
        ORDER BY year_graduated DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Get report data based on report type
$report_data = [];
$report_title = '';

switch ($report_type) {
    case 'employment':
        $report_data = getEmploymentReport($conn, $course_filter, $year_filter);
        $report_title = 'Employment Report';
        break;
    case 'course':
        $report_data = getCourseReport($conn, $course_filter, $year_filter);
        $report_title = 'Course Report';
        break;
    case 'year':
        $report_data = getYearReport($conn, $course_filter, $year_filter);
        $report_title = 'Graduation Year Report';
        break;
    default:
        $report_data = getEmploymentReport($conn, $course_filter, $year_filter);
        $report_title = 'Employment Report';
}

// Add filter details to report title
if (!empty($course_filter)) {
    $report_title .= " - Course: $course_filter";
}
if (!empty($year_filter)) {
    $report_title .= " - Year: $year_filter";
}

// Export to CSV if requested
if ($format === 'csv') {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="alumni_report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
    
    // Create a file pointer
    $output = fopen('php://output', 'w');
    
    // Get the column headers from the first row
    if (!empty($report_data)) {
        $headers = array_keys($report_data[0]);
        fputcsv($output, $headers);
        
        // Output each row of data
        foreach ($report_data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

// Export to PDF if requested
if ($format === 'pdf') {
    // Set a flag for PDF download (we'll handle this with JavaScript)
    $pdf_export = true;
}

// Include navbar for HTML view
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report_title; ?> - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php if (isset($pdf_export)): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <?php endif; ?>
    <style>
        .report-header {
            background-color: #800000;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .report-filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .report-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        }
        .table thead th {
            background-color: #800000;
            color: white;
        }
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        @media print {
            .navbar, .report-filters, .export-buttons, .no-print {
                display: none !important;
            }
            .report-header {
                background-color: #f8f9fa;
                color: #333;
                border: 1px solid #ddd;
            }
            .table thead th {
                background-color: #f8f9fa;
                color: #333;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container-fluid py-4" id="report-container">
            <div class="report-header">
                <h2 class="mb-0"><?php echo $report_title; ?></h2>
                <small>Generated on: <?php echo date('F d, Y h:i A'); ?></small>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="report-filters no-print">
                        <form action="reports.php" method="GET" class="row g-3">
                            <input type="hidden" name="type" value="<?php echo $report_type; ?>">
                            
                            <!-- Report Type Buttons -->
                            <div class="col-md-12 mb-3">
                                <div class="btn-group w-100" role="group">
                                    <a href="reports.php?type=employment<?php echo (!empty($course_filter) ? '&course=' . urlencode($course_filter) : ''); ?><?php echo (!empty($year_filter) ? '&year=' . urlencode($year_filter) : ''); ?>" class="btn <?php echo $report_type === 'employment' ? 'btn-maroon' : 'btn-outline-maroon'; ?>">
                                        <i class="fas fa-briefcase me-2"></i>Employment Report
                                    </a>
                                    <a href="reports.php?type=course<?php echo (!empty($course_filter) ? '&course=' . urlencode($course_filter) : ''); ?><?php echo (!empty($year_filter) ? '&year=' . urlencode($year_filter) : ''); ?>" class="btn <?php echo $report_type === 'course' ? 'btn-maroon' : 'btn-outline-maroon'; ?>">
                                        <i class="fas fa-graduation-cap me-2"></i>Course Report
                                    </a>
                                    <a href="reports.php?type=year<?php echo (!empty($course_filter) ? '&course=' . urlencode($course_filter) : ''); ?><?php echo (!empty($year_filter) ? '&year=' . urlencode($year_filter) : ''); ?>" class="btn <?php echo $report_type === 'year' ? 'btn-maroon' : 'btn-outline-maroon'; ?>">
                                        <i class="fas fa-calendar-alt me-2"></i>Year Report
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Filters -->
                            <div class="col-md-5">
                                <label for="course" class="form-label">Course</label>
                                <select name="course" id="course" class="form-select">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $course === $course_filter ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-5">
                                <label for="year" class="form-label">Graduation Year</label>
                                <select name="year" id="year" class="form-select">
                                    <option value="">All Years</option>
                                    <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $year == $year_filter ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-maroon w-100">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Export Buttons -->
                    <div class="export-buttons no-print">
                        <a href="<?php echo $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?') . 'format=csv'; ?>" class="btn btn-success">
                            <i class="fas fa-file-csv me-2"></i>Export to CSV
                        </a>
                        <button id="pdf-export" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                        </button>
                        <button id="print-report" class="btn btn-secondary">
                            <i class="fas fa-print me-2"></i>Print Report
                        </button>
                    </div>
                    
                    <div class="report-content">
                        <?php if (empty($report_data)): ?>
                            <div class="alert alert-info">
                                No data available for the selected filters.
                            </div>
                        <?php else: ?>
                            <!-- Report content based on report type -->
                            <?php if ($report_type === 'employment'): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student #</th>
                                                <th>Name</th>
                                                <th>Course</th>
                                                <th>Year</th>
                                                <th>Status</th>
                                                <th>Job Title</th>
                                                <th>Company</th>
                                                <th>Course-Related</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                                <td><?php echo $row['year_graduated']; ?></td>
                                                <td><?php echo htmlspecialchars($row['employment_status']); ?></td>
                                                <td><?php echo htmlspecialchars($row['job_title'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($row['is_course_related'] ?? 'N/A'); ?></td>
                                                <td><?php echo $row['salary'] ? '₱' . number_format($row['salary'], 2) : 'N/A'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif ($report_type === 'course'): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Total Alumni</th>
                                                <th>Employed</th>
                                                <th>Employment Rate</th>
                                                <th>Course-Related</th>
                                                <th>Course-Related Rate</th>
                                                <th>Average Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $row): 
                                                $employment_rate = $row['total_alumni'] > 0 ? round(($row['employed'] / $row['total_alumni']) * 100, 1) : 0;
                                                $course_related_rate = $row['employed'] > 0 ? round(($row['course_related'] / $row['employed']) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                                <td><?php echo $row['total_alumni']; ?></td>
                                                <td><?php echo $row['employed']; ?></td>
                                                <td><?php echo $employment_rate; ?>%</td>
                                                <td><?php echo $row['course_related']; ?></td>
                                                <td><?php echo $course_related_rate; ?>%</td>
                                                <td><?php echo $row['avg_salary'] ? '₱' . number_format($row['avg_salary'], 2) : 'N/A'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif ($report_type === 'year'): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Graduation Year</th>
                                                <th>Total Alumni</th>
                                                <th>Employed</th>
                                                <th>Employment Rate</th>
                                                <th>Course-Related</th>
                                                <th>Course-Related Rate</th>
                                                <th>Average Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $row): 
                                                $employment_rate = $row['total_alumni'] > 0 ? round(($row['employed'] / $row['total_alumni']) * 100, 1) : 0;
                                                $course_related_rate = $row['employed'] > 0 ? round(($row['course_related'] / $row['employed']) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $row['year_graduated']; ?></td>
                                                <td><?php echo $row['total_alumni']; ?></td>
                                                <td><?php echo $row['employed']; ?></td>
                                                <td><?php echo $employment_rate; ?>%</td>
                                                <td><?php echo $row['course_related']; ?></td>
                                                <td><?php echo $course_related_rate; ?>%</td>
                                                <td><?php echo $row['avg_salary'] ? '₱' . number_format($row['avg_salary'], 2) : 'N/A'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Print report
        document.getElementById('print-report').addEventListener('click', function() {
            window.print();
        });
        
        <?php if (isset($pdf_export)): ?>
        // Export to PDF
        document.getElementById('pdf-export').addEventListener('click', function() {
            // Set filename
            const filename = '<?php echo "alumni_report_{$report_type}_" . date('Y-m-d'); ?>.pdf';
            
            // Get the report container
            const reportContainer = document.getElementById('report-container');
            
            // Set options for PDF export
            const options = {
                margin: 10,
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            
            // Generate PDF
            html2pdf().set(options).from(reportContainer).save();
        });
        <?php endif; ?>
    </script>
</body>
</html> 