<?php
// Start session before ANY output
session_start();

// Any other headers or redirects must come before any HTML output
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Helper functions for data retrieval
function getEmploymentRate($conn) {
    $sql = "SELECT 
            ROUND((COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0) / 
            NULLIF(COUNT(*), 0), 1) as rate 
            FROM alumni";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['rate'] ?? 0;
    }
    return 0;
}

function getCourseRelatedRate($conn) {
    $sql = "SELECT 
            ROUND((COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0) / 
            NULLIF(COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END), 0), 1) as rate 
            FROM alumni";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['rate'] ?? 0;
    }
    return 0;
}

function getTopCourses($conn, $limit = 5) {
    $sql = "SELECT course, COUNT(*) as count 
            FROM alumni 
            GROUP BY course 
            ORDER BY count DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function getTopCompanies($conn, $limit = 5) {
    $sql = "SELECT company_name, COUNT(*) as count 
            FROM alumni 
            WHERE company_name IS NOT NULL AND company_name != '' 
            GROUP BY company_name 
            ORDER BY count DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    }
    return $data;
}

function getYearlyTrends($conn, $limit = 5) {
    $sql = "SELECT year_graduated, COUNT(*) as total,
            COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed
            FROM alumni 
            GROUP BY year_graduated 
            ORDER BY year_graduated DESC 
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['rate'] = ($row['total'] > 0) ? round(($row['employed'] * 100) / $row['total'], 1) : 0;
            $data[] = $row;
        }
    }
    return $data;
}

function getGenderDistribution($conn) {
    $sql = "SELECT gender, COUNT(*) as count 
            FROM alumni 
            WHERE gender IS NOT NULL 
            GROUP BY gender";
    $result = mysqli_query($conn, $sql);
    $data = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['gender']] = $row['count'];
        }
    }
    return $data;
}

// Get statistics data
$total_alumni_query = "SELECT COUNT(*) as total FROM alumni";
$total_alumni_result = mysqli_query($conn, $total_alumni_query);
$total_alumni = mysqli_fetch_assoc($total_alumni_result)['total'] ?? 0;

$employment_rate = getEmploymentRate($conn);
$course_related_rate = getCourseRelatedRate($conn);
$top_courses = getTopCourses($conn);
$top_companies = getTopCompanies($conn);
$yearly_trends = getYearlyTrends($conn);
$gender_distribution = getGenderDistribution($conn);

// Now include the navbar which will output HTML
require_once 'navbar.php';

// Helper function to fetch chart data
function fetchChartData($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("SQL Error: " . mysqli_error($conn));
        return ['labels' => [], 'counts' => []];
    }
    $data = [
        'labels' => [],
        'counts' => []
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['labels'][] = $row['label'];
        $data['counts'][] = $row['count'];
    }
    return $data;
}

// Function to get employment statistics
function getEmploymentStats($conn) {
    $sql = "SELECT 
            employment_status as label, 
            COUNT(*) as count 
            FROM alumni 
            GROUP BY employment_status";
    return fetchChartData($sql);
}

// Function to get course statistics
function getCourseStats($conn) {
    $sql = "SELECT 
            course as label, 
            COUNT(*) as count 
            FROM alumni 
            GROUP BY course 
            ORDER BY count DESC";
    return fetchChartData($sql);
}

// Function to get yearly statistics
function getYearlyStats($conn) {
    $sql = "SELECT 
            CAST(year_graduated AS CHAR) as label, 
            COUNT(*) as count 
            FROM alumni 
            GROUP BY year_graduated 
            ORDER BY year_graduated";
    return fetchChartData($sql);
}

// Function to get salary range statistics
function getSalaryRangeStats($conn) {
    $sql = "SELECT 
            CONCAT(
                CASE 
                    WHEN salary < 20000 THEN 'Below ₱20k'
                    WHEN salary BETWEEN 20000 AND 30000 THEN '₱20k-₱30k'
                    WHEN salary BETWEEN 30001 AND 50000 THEN '₱30k-₱50k'
                    WHEN salary BETWEEN 50001 AND 100000 THEN '₱50k-₱100k'
                    ELSE 'Above ₱100k'
                END
            ) as label,
            COUNT(*) as count
            FROM alumni 
            WHERE salary IS NOT NULL 
            GROUP BY label
            ORDER BY MIN(salary)";
    return fetchChartData($sql);
}

// Function to get employment by course
function getEmploymentByCourse($conn) {
    $sql = "SELECT 
            course as label,
            COUNT(*) as count,
            SUM(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 ELSE 0 END) as employed_count
            FROM alumni 
            GROUP BY course";
    $result = mysqli_query($conn, $sql);
    $data = [
        'labels' => [],
        'counts' => [],
        'employed' => [],
        'rates' => []
    ];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rate = ($row['count'] > 0) ? round(($row['employed_count'] * 100) / $row['count'], 1) : 0;
            $data['labels'][] = $row['label'];
            $data['counts'][] = (int)$row['count'];
            $data['employed'][] = (int)$row['employed_count'];
            $data['rates'][] = $rate;
        }
    }
    return $data;
}

// Function to get average salary by course
function getAverageSalaryByCourse($conn) {
    $sql = "SELECT 
            course as label,
            ROUND(AVG(salary), 2) as avg_salary,
            COUNT(*) as count
            FROM alumni 
            WHERE salary IS NOT NULL 
            GROUP BY course";
    $result = mysqli_query($conn, $sql);
    $data = [
        'labels' => [],
        'averages' => [],
        'counts' => []
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['labels'][] = $row['label'];
        $data['averages'][] = $row['avg_salary'];
        $data['counts'][] = $row['count'];
    }
    return $data;
}

// Add this function after the other helper functions
function verifyRequiredColumns($conn) {
    $required_columns = [
        'employment_status',
        'course',
        'year_graduated',
        'salary',
        'company_name',
        'work_position'
    ];

    $result = mysqli_query($conn, "SHOW COLUMNS FROM alumni");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing_columns[] = $row['Field'];
    }

    $missing_columns = array_diff($required_columns, $existing_columns);
    
    if (!empty($missing_columns)) {
        throw new Exception("Missing required columns: " . implode(", ", $missing_columns));
    }
    
    return true;
}

// Handle export requests
if (isset($_GET['export']) && isset($_GET['type'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="alumni_report_' . $_GET['type'] . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    switch ($_GET['type']) {
        case 'employment':
            fputcsv($output, ['Employment Status', 'Count']);
            $data = getEmploymentStats($conn);
            for ($i = 0; $i < count($data['labels']); $i++) {
                fputcsv($output, [$data['labels'][$i], $data['counts'][$i]]);
            }
            break;
        case 'course':
            fputcsv($output, ['Course', 'Total Alumni', 'Employed', 'Unemployed']);
            $data = getEmploymentByCourse($conn);
            for ($i = 0; $i < count($data['labels']); $i++) {
                fputcsv($output, [
                    $data['labels'][$i],
                    $data['counts'][$i],
                    $data['employed'][$i],
                    $data['counts'][$i] - $data['employed'][$i]
                ]);
            }
            break;
        // Add other export types...
    }
    
    fclose($output);
    exit();
}

// Get all the required data
try {
    // Enable error reporting for debugging
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Test database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if tables exist
    $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'alumni'");
    if (mysqli_num_rows($tables_check) == 0) {
        throw new Exception("Alumni table does not exist");
    }

    // Verify required columns
    verifyRequiredColumns($conn);

    // Test if alumni table has data
    $data_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM alumni");
    $row = mysqli_fetch_assoc($data_check);
    if ($row['count'] == 0) {
        echo "<div class='alert alert-warning'>No alumni data available yet. Please add some alumni records first.</div>";
        exit;
    }

    $employmentData = getEmploymentStats($conn);
    $courseData = getCourseStats($conn);
    $yearlyData = getYearlyStats($conn);
    $salaryData = getSalaryRangeStats($conn);
    $employmentByCourseData = getEmploymentByCourse($conn);
    $salaryByCourseData = getAverageSalaryByCourse($conn);

    // Verify that we got data
    if (empty($employmentData['labels']) && empty($courseData['labels']) && empty($yearlyData['labels'])) {
        throw new Exception("No data retrieved from any of the queries");
    }
} catch (Exception $e) {
    error_log("Analytics Error: " . $e->getMessage());
    echo "<div class='alert alert-danger'>
            <h4>Error Details:</h4>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p>Please check the following:</p>
            <ul>
                <li>Database connection is working</li>
                <li>Alumni table exists and has data</li>
                <li>All required columns are present in the tables</li>
            </ul>
          </div>";
    exit;
}

// Initialize default data if any query returns empty
$defaultData = ['labels' => [], 'counts' => []];
$employmentData = !empty($employmentData['labels']) ? $employmentData : $defaultData;
$courseData = !empty($courseData['labels']) ? $courseData : $defaultData;
$yearlyData = !empty($yearlyData['labels']) ? $yearlyData : $defaultData;
$salaryData = !empty($salaryData['labels']) ? $salaryData : $defaultData;

// 1. Employment Rate by Course
$course_employment_query = "SELECT 
    course,
    COUNT(*) as total,
    COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
    (COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0 / COUNT(*)) as employment_rate
    FROM alumni 
    GROUP BY course 
    ORDER BY employment_rate DESC";
$course_employment_result = mysqli_query($conn, $course_employment_query);
$course_employment_data = [];
while ($row = mysqli_fetch_assoc($course_employment_result)) {
    $course_employment_data[] = $row;
}

// 2. Yearly Employment Trends
$yearly_employment_query = "SELECT 
    year_graduated,
    COUNT(*) as total_graduates,
    COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
    (COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0 / COUNT(*)) as employment_rate
    FROM alumni 
    GROUP BY year_graduated 
    ORDER BY year_graduated DESC";
$yearly_employment_result = mysqli_query($conn, $yearly_employment_query);
$yearly_employment_data = [];
while ($row = mysqli_fetch_assoc($yearly_employment_result)) {
    $yearly_employment_data[] = $row;
}

// 3. Course-Related Employment by Course
$course_related_query = "SELECT 
    course,
    COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
    COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as course_related,
    (COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) * 100.0 / 
     NULLIF(COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END), 0)) as related_percentage
    FROM alumni 
    GROUP BY course 
    ORDER BY related_percentage DESC";
$course_related_result = mysqli_query($conn, $course_related_query);
$course_related_data = [];
while ($row = mysqli_fetch_assoc($course_related_result)) {
    $course_related_data[] = $row;
}

// 4. Salary Range Distribution
$salary_range_query = "SELECT 
    course,
    COUNT(CASE WHEN salary < 20000 THEN 1 END) as below_20k,
    COUNT(CASE WHEN salary BETWEEN 20000 AND 30000 THEN 1 END) as k20_30,
    COUNT(CASE WHEN salary BETWEEN 30001 AND 40000 THEN 1 END) as k30_40,
    COUNT(CASE WHEN salary > 40000 THEN 1 END) as above_40k
    FROM alumni 
    WHERE employment_status IN ('Full-time', 'Part-time', 'Self-employed')
    GROUP BY course";
$salary_range_result = mysqli_query($conn, $salary_range_query);
$salary_range_data = [];
while ($row = mysqli_fetch_assoc($salary_range_result)) {
    $salary_range_data[] = $row;
}

// 5. Employment Status by Gender
$gender_employment_query = "SELECT 
    gender,
    COUNT(*) as total,
    COUNT(CASE WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as employed,
    COUNT(CASE WHEN employment_status NOT IN ('Full-time', 'Part-time', 'Self-employed') THEN 1 END) as unemployed,
    COUNT(CASE WHEN employment_status = 'Self-employed' THEN 1 END) as self_employed
    FROM alumni 
    GROUP BY gender";
$gender_employment_result = mysqli_query($conn, $gender_employment_query);
$gender_employment_data = [];
while ($row = mysqli_fetch_assoc($gender_employment_result)) {
    $gender_employment_data[] = $row;
}

// 6. Time to Employment Analysis
$time_to_employment_query = "SELECT 
    course,
    AVG(CASE 
        WHEN employment_status IN ('Full-time', 'Part-time', 'Self-employed') 
        THEN TIMESTAMPDIFF(MONTH, STR_TO_DATE(CONCAT(year_graduated, '-05-01'), '%Y-%m-%d'), date_hired)
        ELSE NULL 
    END) as avg_months_to_employment
    FROM alumni 
    GROUP BY course 
    HAVING avg_months_to_employment IS NOT NULL
    ORDER BY avg_months_to_employment";
$time_to_employment_result = mysqli_query($conn, $time_to_employment_query);
$time_to_employment_data = [];
while ($row = mysqli_fetch_assoc($time_to_employment_result)) {
    $time_to_employment_data[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Alumni Tracer System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #ffffff;
            color: #333;
            padding-top: 20px;
        }
        .bg-maroon {
            background-color: #800000;
            color: white;
        }
        .text-maroon {
            color: #800000;
        }
        .btn-maroon {
            background-color: #800000;
            color: white;
        }
        .btn-maroon:hover {
            background-color: #600000;
            color: white;
        }
        .stats-overview {
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .stat-card {
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            border-left: 4px solid #800000;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #800000;
            line-height: 1;
        }
        .report-card {
            height: 100%;
        }
        .report-card .card-header {
            background-color: #800000;
            color: white;
            font-weight: 500;
        }
        .report-link {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }
        .report-link:hover {
            background-color: #800000;
            color: white;
        }
        .report-link i {
            margin-right: 0.5rem;
            width: 1.5rem;
            text-align: center;
        }
        .comparison-table thead th {
            background-color: #800000;
            color: white;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="content-wrapper">
        <div class="container-fluid py-4">
            <h2 class="text-maroon mb-4">
                <i class="fas fa-chart-line me-2"></i>Analytics Dashboard
            </h2>
            
            <!-- Quick Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="stats-overview glass-effect">
                        <h4 class="text-maroon mb-4">Quick Statistics Overview</h4>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="m-0">Total Alumni</h5>
                                        <i class="fas fa-user-graduate fa-2x text-muted"></i>
                                    </div>
                                    <div class="stat-value"><?php echo number_format($total_alumni); ?></div>
                                    <div class="text-muted">Registered Alumni</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="m-0">Employment Rate</h5>
                                        <i class="fas fa-briefcase fa-2x text-muted"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $employment_rate; ?>%</div>
                                    <div class="text-muted">Of Alumni Employed</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="m-0">Course-Related</h5>
                                        <i class="fas fa-graduation-cap fa-2x text-muted"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $course_related_rate; ?>%</div>
                                    <div class="text-muted">Working in Field of Study</div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="m-0">Gender Ratio</h5>
                                        <i class="fas fa-venus-mars fa-2x text-muted"></i>
                                    </div>
                                    <?php
                                    $male_count = $gender_distribution['Male'] ?? 0;
                                    $female_count = $gender_distribution['Female'] ?? 0;
                                    $total_gender = $male_count + $female_count;
                                    $male_percent = ($total_gender > 0) ? round(($male_count * 100) / $total_gender, 1) : 0;
                                    $female_percent = ($total_gender > 0) ? round(($female_count * 100) / $total_gender, 1) : 0;
                                    ?>
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $male_percent; ?>%" aria-valuenow="<?php echo $male_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $male_percent; ?>% Male
                                        </div>
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $female_percent; ?>%" aria-valuenow="<?php echo $female_percent; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $female_percent; ?>% Female
                                        </div>
                                    </div>
                                    <div class="text-muted">Male/Female Distribution</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="analytics-detailed.php" class="btn btn-maroon">
                                <i class="fas fa-chart-bar me-2"></i>View Detailed Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Available Reports -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card report-card">
                        <div class="card-header">
                            <h5 class="mb-0">Employment Analysis</h5>
                        </div>
                        <div class="card-body">
                            <a href="analytics-detailed.php?view=employment" class="report-link">
                                <i class="fas fa-briefcase"></i> Employment Status
                            </a>
                            <a href="analytics-detailed.php?view=employment_by_course" class="report-link">
                                <i class="fas fa-graduation-cap"></i> Employment by Course
                            </a>
                            <a href="analytics-detailed.php?view=employment_by_year" class="report-link">
                                <i class="fas fa-calendar-alt"></i> Employment by Year
                            </a>
                            <a href="analytics-detailed.php?view=course_related" class="report-link">
                                <i class="fas fa-check-circle"></i> Course-Related Employment
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card report-card">
                        <div class="card-header">
                            <h5 class="mb-0">Salary Analysis</h5>
                        </div>
                        <div class="card-body">
                            <a href="analytics-detailed.php?view=salary_ranges" class="report-link">
                                <i class="fas fa-money-bill-wave"></i> Salary Ranges
                            </a>
                            <a href="analytics-detailed.php?view=salary_by_course" class="report-link">
                                <i class="fas fa-university"></i> Salary by Course
                            </a>
                            <a href="analytics-detailed.php?view=salary_by_year" class="report-link">
                                <i class="fas fa-history"></i> Salary Trends Over Time
                            </a>
                            <a href="analytics-detailed.php?view=salary_by_gender" class="report-link">
                                <i class="fas fa-venus-mars"></i> Salary by Gender
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card report-card">
                        <div class="card-header">
                            <h5 class="mb-0">Company & Industry</h5>
                        </div>
                        <div class="card-body">
                            <a href="analytics-detailed.php?view=top_companies" class="report-link">
                                <i class="fas fa-building"></i> Top Employers
                            </a>
                            <a href="analytics-detailed.php?view=companies_by_course" class="report-link">
                                <i class="fas fa-user-tie"></i> Companies by Course
                            </a>
                            <a href="analytics-detailed.php?view=industry_breakdown" class="report-link">
                                <i class="fas fa-industry"></i> Industry Breakdown
                            </a>
                            <a href="analytics-detailed.php?view=location_distribution" class="report-link">
                                <i class="fas fa-map-marker-alt"></i> Location Distribution
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top Courses and Companies -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Top Courses</h5>
                            <a href="analytics-detailed.php?view=course" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped comparison-table">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th class="text-end">Alumni Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_courses as $course): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course['course']); ?></td>
                                        <td class="text-end"><?php echo number_format($course['count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($top_courses)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No course data available</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Top Employers</h5>
                            <a href="analytics-detailed.php?view=company" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped comparison-table">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th class="text-end">Alumni Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_companies as $company): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                        <td class="text-end"><?php echo number_format($company['count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($top_companies)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center">No company data available</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Yearly Employment Trends -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Yearly Employment Trends</h5>
                            <a href="analytics-detailed.php?view=year" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-striped comparison-table">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th class="text-end">Total Alumni</th>
                                        <th class="text-end">Employed</th>
                                        <th class="text-end">Employment Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($yearly_trends as $year): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($year['year_graduated']); ?></td>
                                        <td class="text-end"><?php echo number_format($year['total']); ?></td>
                                        <td class="text-end"><?php echo number_format($year['employed']); ?></td>
                                        <td class="text-end"><?php echo $year['rate']; ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($yearly_trends)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No yearly data available</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Add this JavaScript function for safe chart rendering
    document.addEventListener('DOMContentLoaded', function() {
        // Function to safely render charts
        function renderChart(chartId, chartConfig) {
            const chartElement = document.getElementById(chartId);
            if (chartElement) {
                try {
                    return new Chart(chartElement, chartConfig);
                } catch (error) {
                    console.error(`Error rendering chart ${chartId}:`, error);
                    // Display a fallback message in the chart container
                    chartElement.getContext('2d').clearRect(0, 0, chartElement.width, chartElement.height);
                    const container = chartElement.parentNode;
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'alert alert-danger mt-3';
                    errorMessage.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Chart could not be displayed. Please try refreshing the page.`;
                    container.appendChild(errorMessage);
                    return null;
                }
            }
            return null;
        }
        
        try {
            // Initialize Employment Status Chart
            const employmentStats = <?php echo json_encode(getEmploymentStats($conn)); ?>;
            if (document.getElementById('employmentChart')) {
                renderChart('employmentChart', {
                    type: 'pie',
                    data: {
                        labels: Object.keys(employmentStats.labels).map(label => label),
                        datasets: [{
                            data: Object.keys(employmentStats.labels).map(label => employmentStats.counts[label]),
                            backgroundColor: [
                                'rgba(128, 0, 0, 0.8)',
                                'rgba(190, 30, 45, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(220, 200, 200, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }
            
            // Initialize Course Distribution Chart
            const courseStats = <?php echo json_encode(getCourseStats($conn)); ?>;
            if (document.getElementById('courseChart')) {
                renderChart('courseChart', {
                    type: 'bar',
                    data: {
                        labels: courseStats.labels,
                        datasets: [{
                            label: 'Number of Alumni',
                            data: courseStats.counts,
                            backgroundColor: 'rgba(128, 0, 0, 0.7)'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Initialize Year Distribution Chart
            const yearlyStats = <?php echo json_encode(getYearlyStats($conn)); ?>;
            if (document.getElementById('yearChart')) {
                renderChart('yearChart', {
                    type: 'line',
                    data: {
                        labels: yearlyStats.labels,
                        datasets: [{
                            label: 'Number of Graduates',
                            data: yearlyStats.counts,
                            fill: true,
                            tension: 0.3,
                            backgroundColor: 'rgba(128, 0, 0, 0.2)',
                            borderColor: 'rgba(128, 0, 0, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Initialize Salary Range Chart
            const salaryStats = <?php echo json_encode(getSalaryRangeStats($conn)); ?>;
            if (document.getElementById('salaryChart')) {
                renderChart('salaryChart', {
                    type: 'pie',
                    data: {
                        labels: salaryStats.labels,
                        datasets: [{
                            data: salaryStats.counts,
                            backgroundColor: [
                                'rgba(128, 0, 0, 0.8)',
                                'rgba(190, 30, 45, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(220, 190, 190, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }
            
            // Initialize Employment by Course Chart
            const employmentByCourse = <?php echo json_encode(getEmploymentByCourse($conn)); ?>;
            if (document.getElementById('employmentByCourseChart')) {
                renderChart('employmentByCourseChart', {
                    type: 'bar',
                    data: {
                        labels: employmentByCourse.labels,
                        datasets: [{
                            label: 'Employment Rate (%)',
                            data: employmentByCourse.rates,
                            backgroundColor: 'rgba(128, 0, 0, 0.8)'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error("Error initializing charts:", error);
            // Display a general error message at the top of the page
            const container = document.querySelector('.container-fluid');
            if (container) {
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                errorAlert.innerHTML = `
                    <strong>Error:</strong> There was a problem loading the charts. Please try refreshing the page.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                container.insertBefore(errorAlert, container.firstChild);
            }
        }
    });
    </script>
</body>
</html> 