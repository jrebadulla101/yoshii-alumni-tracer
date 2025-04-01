<?php
// Start session before ANY output (including whitespace)
session_start();

// Any other headers or redirects must come before any HTML output
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

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

// 1. Employment Rate by Course
$course_employment_query = "SELECT 
    course,
    COUNT(*) as total,
    COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) as employed,
    (COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) * 100.0 / COUNT(*)) as employment_rate
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
    COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) as employed,
    (COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) * 100.0 / COUNT(*)) as employment_rate
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
    COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) as employed,
    COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Employed', 'Self-employed') THEN 1 END) as course_related,
    (COUNT(CASE WHEN is_course_related = 'Yes' AND employment_status IN ('Employed', 'Self-employed') THEN 1 END) * 100.0 / 
     NULLIF(COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END), 0)) as related_percentage
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
    WHERE employment_status IN ('Employed', 'Self-employed')
    GROUP BY course";
$salary_range_result = mysqli_query($conn, $salary_range_query);
$salary_range_data = [];
while ($row = mysqli_fetch_assoc($salary_range_result)) {
    $salary_range_data[] = $row;
}

// 5. Employment Status by Gender
$employment_status_query = "SELECT 
    employment_status,
    COUNT(*) as total
    FROM alumni 
    GROUP BY employment_status";
$employment_status_result = mysqli_query($conn, $employment_status_query);
$employment_status_data = [];
if ($employment_status_result) {
    while ($row = mysqli_fetch_assoc($employment_status_result)) {
        $employment_status_data[] = $row;
    }
} else {
    error_log("SQL Error in employment_status_query: " . mysqli_error($conn));
}

// Add new query for employment time comparison
$employment_time_query = "SELECT 
    course,
    AVG(DATEDIFF(CASE WHEN is_current_job = 'No' THEN date_ended ELSE CURRENT_DATE END, date_started) / 30) as avg_months_employed
    FROM alumni 
    WHERE employment_status IN ('Employed', 'Self-employed') 
        AND date_started IS NOT NULL
    GROUP BY course 
    ORDER BY avg_months_employed DESC";
$employment_time_result = mysqli_query($conn, $employment_time_query);
$employment_time_data = [];
if ($employment_time_result) {
    while ($row = mysqli_fetch_assoc($employment_time_result)) {
        $employment_time_data[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn));
}

// Company Distribution by Course query
$company_course_query = "SELECT 
    course,
    COUNT(DISTINCT company_name) as company_count
    FROM alumni 
    WHERE company_name IS NOT NULL AND company_name != ''
    GROUP BY course 
    ORDER BY company_count DESC";
$company_course_result = mysqli_query($conn, $company_course_query);
$company_course_data = [];
if ($company_course_result) {
    while ($row = mysqli_fetch_assoc($company_course_result)) {
        $company_course_data[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn));
}

// Additional queries for salary analysis
$avg_salary_query = "SELECT 
    course,
    ROUND(AVG(salary), 2) as avg_salary,
    MIN(salary) as min_salary,
    MAX(salary) as max_salary
    FROM alumni 
    WHERE employment_status IN ('Employed', 'Self-employed')
        AND salary IS NOT NULL AND salary > 0
    GROUP BY course 
    ORDER BY avg_salary DESC";
$avg_salary_result = mysqli_query($conn, $avg_salary_query);
$avg_salary_data = [];
if ($avg_salary_result) {
    while ($row = mysqli_fetch_assoc($avg_salary_result)) {
        $avg_salary_data[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn));
}

// Salary by year graduated
$salary_year_query = "SELECT 
    year_graduated,
    ROUND(AVG(salary), 2) as avg_salary
    FROM alumni 
    WHERE employment_status IN ('Employed', 'Self-employed')
        AND salary IS NOT NULL AND salary > 0
    GROUP BY year_graduated 
    ORDER BY year_graduated ASC";
$salary_year_result = mysqli_query($conn, $salary_year_query);
$salary_year_data = [];
if ($salary_year_result) {
    while ($row = mysqli_fetch_assoc($salary_year_result)) {
        $salary_year_data[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn));
}

// Top companies by alumni count
$top_companies_query = "SELECT 
    company_name,
    COUNT(*) as alumni_count
    FROM alumni 
    WHERE company_name IS NOT NULL AND company_name != ''
    GROUP BY company_name 
    ORDER BY alumni_count DESC
    LIMIT 10";
$top_companies_result = mysqli_query($conn, $top_companies_query);
$top_companies_data = [];
if ($top_companies_result) {
    while ($row = mysqli_fetch_assoc($top_companies_result)) {
        $top_companies_data[] = $row;
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn));
}

// Now include the navbar which will output HTML
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Analytics - Alumni Tracer System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #ffffff;
            color: #333;
            padding-top: 20px;
        }
        .stats-card {
            background: #fff;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            background: #800000;
            color: #fff;
            padding: 15px 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .nav-tabs .nav-link {
            color: #800000;
        }
        .nav-tabs .nav-link.active {
            color: #800000;
            font-weight: bold;
            border-color: #800000 #800000 #fff;
        }
        .table thead th {
            background-color: #800000;
            color: #fff;
        }
        .comparison-table {
            margin-top: 20px;
        }
        .btn-maroon {
            background-color: #800000;
            color: white;
        }
        .btn-maroon:hover {
            background-color: #600000;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detailed Analytics</h2>
            <a href="analytics.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Quick Stats
            </a>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="analyticsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo (!isset($_GET['view']) || $_GET['view'] == 'employment') ? 'active' : ''; ?>" id="employment-tab" data-bs-toggle="tab" href="#employment" role="tab">Employment Analysis</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'course') ? 'active' : ''; ?>" id="course-tab" data-bs-toggle="tab" href="#course" role="tab">Course Analysis</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'salary') ? 'active' : ''; ?>" id="salary-tab" data-bs-toggle="tab" href="#salary" role="tab">Salary Analysis</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'gender') ? 'active' : ''; ?>" id="gender-tab" data-bs-toggle="tab" href="#gender" role="tab">Employment Status</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'comparison') ? 'active' : ''; ?>" id="comparison-tab" data-bs-toggle="tab" href="#comparison" role="tab">Comparison Analysis</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['view']) && $_GET['view'] == 'company') ? 'active' : ''; ?>" id="company-tab" data-bs-toggle="tab" href="#company" role="tab">Company Analysis</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="analyticsTabContent">
            <!-- Employment Analysis Tab -->
            <div class="tab-pane fade <?php echo (!isset($_GET['view']) || $_GET['view'] == 'employment') ? 'show active' : ''; ?>" id="employment" role="tabpanel">
                <div class="row">
                    <!-- Employment Rate by Course -->
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Employment Rate by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="employmentRateChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Total</th>
                                                <th>Employed</th>
                                                <th>Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($course_employment_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo $data['total']; ?></td>
                                                <td><?php echo $data['employed']; ?></td>
                                                <td><?php echo number_format($data['employment_rate'], 1); ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($course_employment_data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Yearly Employment Trends -->
                    <div class="col-md-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Yearly Employment Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="yearlyTrendsChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>Graduates</th>
                                                <th>Employed</th>
                                                <th>Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($yearly_employment_data as $data): ?>
                                            <tr>
                                                <td><?php echo $data['year_graduated']; ?></td>
                                                <td><?php echo $data['total_graduates']; ?></td>
                                                <td><?php echo $data['employed']; ?></td>
                                                <td><?php echo number_format($data['employment_rate'], 1); ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($yearly_employment_data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
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

            <!-- Course Analysis Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['view']) && $_GET['view'] == 'course') ? 'show active' : ''; ?>" id="course" role="tabpanel">
                <div class="row">
                    <!-- Course-Related Employment -->
                    <div class="col-md-12">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Course-Related Employment</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="courseRelatedChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Employed</th>
                                                <th>Course-Related</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($course_related_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo $data['employed']; ?></td>
                                                <td><?php echo $data['course_related']; ?></td>
                                                <td><?php echo number_format($data['related_percentage'] ?? 0, 1); ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($course_related_data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
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

            <!-- Salary Analysis Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['view']) && $_GET['view'] == 'salary') ? 'show active' : ''; ?>" id="salary" role="tabpanel">
                <div class="row">
                    <!-- Salary Range Distribution -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Salary Range Distribution by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salaryRangeChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Below ₱20k</th>
                                                <th>₱20k-30k</th>
                                                <th>₱30k-40k</th>
                                                <th>Above ₱40k</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salary_range_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo $data['below_20k']; ?></td>
                                                <td><?php echo $data['k20_30']; ?></td>
                                                <td><?php echo $data['k30_40']; ?></td>
                                                <td><?php echo $data['above_40k']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($salary_range_data)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Average Salary by Course -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Average Salary by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="avgSalaryChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Average Salary</th>
                                                <th>Min Salary</th>
                                                <th>Max Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($avg_salary_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td>₱<?php echo number_format($data['avg_salary'], 2); ?></td>
                                                <td>₱<?php echo number_format($data['min_salary'], 2); ?></td>
                                                <td>₱<?php echo number_format($data['max_salary'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($avg_salary_data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Salary Trends by Graduation Year -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Salary Trends by Graduation Year</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="salaryYearChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Graduation Year</th>
                                                <th>Average Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($salary_year_data as $data): ?>
                                            <tr>
                                                <td><?php echo $data['year_graduated']; ?></td>
                                                <td>₱<?php echo number_format($data['avg_salary'], 2); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($salary_year_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
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

            <!-- Gender Analysis Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['view']) && $_GET['view'] == 'gender') ? 'show active' : ''; ?>" id="gender" role="tabpanel">
                <div class="row">
                    <!-- Employment Status Distribution -->
                    <div class="col-md-12">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Employment Status Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="employmentStatusChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employment Status</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employment_status_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['employment_status']); ?></td>
                                                <td><?php echo $data['total']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($employment_status_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
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

            <!-- New Comparison Analysis Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['view']) && $_GET['view'] == 'comparison') ? 'show active' : ''; ?>" id="comparison" role="tabpanel">
                <div class="row">
                    <!-- Time to Employment Comparison -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Average Employment Duration by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="employmentTimeChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Avg. Months Employed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($employment_time_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo number_format($data['avg_months_employed'], 1); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($employment_time_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Company Distribution by Course -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Company Distribution by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="companyDistributionChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Number of Distinct Companies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($company_course_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo $data['company_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($company_course_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course vs. Employment Rate vs. Course-Related Employment -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Course vs. Employment Rate vs. Course-Related Employment</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="courseComparisonChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Employment Rate</th>
                                                <th>Course-Related Rate</th>
                                                <th>Employment Gap</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($course_employment_data as $key => $data): 
                                                $course = $data['course'];
                                                $employment_rate = $data['employment_rate'];
                                                $course_related_rate = 0;
                                                
                                                // Find matching course in course_related_data
                                                foreach ($course_related_data as $related) {
                                                    if ($related['course'] === $course) {
                                                        $course_related_rate = $related['related_percentage'] ?? 0;
                                                        break;
                                                    }
                                                }
                                                
                                                $employment_gap = $employment_rate - $course_related_rate;
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course); ?></td>
                                                <td><?php echo number_format($employment_rate, 1); ?>%</td>
                                                <td><?php echo number_format($course_related_rate, 1); ?>%</td>
                                                <td><?php echo number_format($employment_gap, 1); ?>%</td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($course_employment_data)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No data available</td>
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

            <!-- Company Analysis Tab -->
            <div class="tab-pane fade <?php echo (isset($_GET['view']) && $_GET['view'] == 'company') ? 'show active' : ''; ?>" id="company" role="tabpanel">
                <div class="row">
                    <!-- Top Companies -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Top Employers</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="topCompaniesChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Company</th>
                                                <th>Alumni Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_companies_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['company_name']); ?></td>
                                                <td><?php echo $data['alumni_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($top_companies_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Company Distribution by Course -->
                    <div class="col-md-6 mb-4">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5 class="mb-0">Company Distribution by Course</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="companyDistributionChart"></canvas>
                                </div>
                                <div class="table-responsive comparison-table">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Number of Distinct Companies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($company_course_data as $data): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($data['course']); ?></td>
                                                <td><?php echo $data['company_count']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($company_course_data)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No data available</td>
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
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Function to safely render charts
            function renderChart(chartId, chartConfig) {
                const chartElement = document.getElementById(chartId);
                if (chartElement) {
                    try {
                        new Chart(chartElement, chartConfig);
                    } catch (error) {
                        console.error(`Error rendering chart ${chartId}:`, error);
                        // Display a fallback message in the chart container
                        chartElement.getContext('2d').clearRect(0, 0, chartElement.width, chartElement.height);
                        const container = chartElement.parentNode;
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'alert alert-danger mt-3';
                        errorMessage.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Chart could not be displayed. Please try refreshing the page.`;
                        container.appendChild(errorMessage);
                    }
                }
            }
            
            // Setup charts with error handling
            try {
                // Course Employment Chart
                const courseLabels = <?php echo json_encode(array_column($course_employment_data, 'course')); ?>;
                const employmentRates = <?php echo json_encode(array_column($course_employment_data, 'employment_rate')); ?>;
                
                if (document.getElementById('employmentRateChart')) {
                    renderChart('employmentRateChart', {
                        type: 'bar',
                        data: {
                            labels: courseLabels,
                            datasets: [{
                                label: 'Employment Rate (%)',
                                data: employmentRates,
                                backgroundColor: '#800000',
                                borderColor: '#800000',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }
                
                // Yearly Employment Trends Chart
                const yearlyLabels = <?php echo json_encode(array_column($yearly_employment_data, 'year_graduated')); ?>;
                const yearlyRates = <?php echo json_encode(array_column($yearly_employment_data, 'employment_rate')); ?>;
                
                if (document.getElementById('yearlyTrendsChart')) {
                    renderChart('yearlyTrendsChart', {
                        type: 'line',
                        data: {
                            labels: yearlyLabels,
                            datasets: [{
                                label: 'Employment Rate (%)',
                                data: yearlyRates,
                                borderColor: '#800000',
                                backgroundColor: 'rgba(128, 0, 0, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }
                
                // Course-Related Employment Chart
                const courseRelatedLabels = <?php echo json_encode(array_column($course_related_data, 'course')); ?>;
                const relatedPercentages = <?php echo json_encode(array_column($course_related_data, 'related_percentage')); ?>;
                
                if (document.getElementById('courseRelatedChart')) {
                    renderChart('courseRelatedChart', {
                        type: 'bar',
                        data: {
                            labels: courseRelatedLabels,
                            datasets: [{
                                label: 'Course-Related Employment Rate (%)',
                                data: relatedPercentages,
                                backgroundColor: 'rgba(128, 0, 0, 0.8)',
                                borderColor: 'rgba(128, 0, 0, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Salary Range Chart
                const salaryRangeLabels = <?php echo json_encode(array_column($salary_range_data, 'course')); ?>;
                const below20k = <?php echo json_encode(array_column($salary_range_data, 'below_20k')); ?>;
                const k20_30 = <?php echo json_encode(array_column($salary_range_data, 'k20_30')); ?>;
                const k30_40 = <?php echo json_encode(array_column($salary_range_data, 'k30_40')); ?>;
                const above40k = <?php echo json_encode(array_column($salary_range_data, 'above_40k')); ?>;
                
                if (document.getElementById('salaryRangeChart')) {
                    renderChart('salaryRangeChart', {
                        type: 'bar',
                        data: {
                            labels: salaryRangeLabels,
                            datasets: [
                                {
                                    label: 'Below ₱20k',
                                    data: below20k,
                                    backgroundColor: '#28a745'
                                },
                                {
                                    label: '₱20k-30k',
                                    data: k20_30,
                                    backgroundColor: '#17a2b8'
                                },
                                {
                                    label: '₱30k-40k',
                                    data: k30_40,
                                    backgroundColor: '#800000'
                                },
                                {
                                    label: 'Above ₱40k',
                                    data: above40k,
                                    backgroundColor: '#ffc107'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
                
                // Employment Time Chart
                const employmentTimeLabels = <?php echo json_encode(array_column($employment_time_data, 'course')); ?>;
                const employmentTimeData = <?php echo json_encode(array_column($employment_time_data, 'avg_months_employed')); ?>;
                
                if (document.getElementById('employmentTimeChart')) {
                    renderChart('employmentTimeChart', {
                        type: 'bar',
                        data: {
                            labels: employmentTimeLabels,
                            datasets: [{
                                label: 'Average Months Employed',
                                data: employmentTimeData,
                                backgroundColor: '#800000',
                                borderColor: '#800000',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Months'
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Company Distribution Chart
                const companyDistLabels = <?php echo json_encode(array_column($company_course_data, 'course')); ?>;
                const companyDistData = <?php echo json_encode(array_column($company_course_data, 'company_count')); ?>;
                
                if (document.getElementById('companyDistributionChart')) {
                    renderChart('companyDistributionChart', {
                        type: 'bar',
                        data: {
                            labels: companyDistLabels,
                            datasets: [{
                                label: 'Number of Companies',
                                data: companyDistData,
                                backgroundColor: '#800000',
                                borderColor: '#800000',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
                
                // Course Comparison Chart
                const courseCompLabels = <?php 
                    $compLabels = [];
                    $empRates = [];
                    $relatedRates = [];
                    
                    foreach ($course_employment_data as $data) {
                        $course = $data['course'];
                        $employment_rate = $data['employment_rate'];
                        $course_related_rate = 0;
                        
                        // Find matching course in course_related_data
                        foreach ($course_related_data as $related) {
                            if ($related['course'] === $course) {
                                $course_related_rate = $related['related_percentage'] ?? 0;
                                break;
                            }
                        }
                        
                        $compLabels[] = $course;
                        $empRates[] = $employment_rate;
                        $relatedRates[] = $course_related_rate;
                    }
                    
                    echo json_encode($compLabels);
                ?>;
                
                const employmentRateData = <?php echo json_encode($empRates); ?>;
                const courseRelatedRateData = <?php echo json_encode($relatedRates); ?>;
                
                if (document.getElementById('courseComparisonChart')) {
                    renderChart('courseComparisonChart', {
                        type: 'bar',
                        data: {
                            labels: courseCompLabels,
                            datasets: [
                                {
                                    label: 'Employment Rate',
                                    data: employmentRateData,
                                    backgroundColor: '#28a745',
                                    borderColor: '#28a745',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Course-Related Employment',
                                    data: courseRelatedRateData,
                                    backgroundColor: '#800000',
                                    borderColor: '#800000',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    title: {
                                        display: true,
                                        text: 'Percentage (%)'
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Average Salary Chart
                const avgSalaryLabels = <?php echo json_encode(array_column($avg_salary_data, 'course')); ?>;
                const avgSalaryData = <?php echo json_encode(array_column($avg_salary_data, 'avg_salary')); ?>;
                
                if (document.getElementById('avgSalaryChart')) {
                    renderChart('avgSalaryChart', {
                        type: 'bar',
                        data: {
                            labels: avgSalaryLabels,
                            datasets: [{
                                label: 'Average Salary (₱)',
                                data: avgSalaryData,
                                backgroundColor: '#800000',
                                borderColor: '#800000',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + value.toLocaleString();
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Average Salary: ₱' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Salary Year Chart
                const salaryYearLabels = <?php echo json_encode(array_column($salary_year_data, 'year_graduated')); ?>;
                const salaryYearData = <?php echo json_encode(array_column($salary_year_data, 'avg_salary')); ?>;
                
                if (document.getElementById('salaryYearChart')) {
                    renderChart('salaryYearChart', {
                        type: 'line',
                        data: {
                            labels: salaryYearLabels,
                            datasets: [{
                                label: 'Average Salary by Graduation Year',
                                data: salaryYearData,
                                backgroundColor: 'rgba(128, 0, 0, 0.1)',
                                borderColor: '#800000',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '₱' + value.toLocaleString();
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return 'Average Salary: ₱' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Top Companies Chart
                const topCompanyLabels = <?php echo json_encode(array_column($top_companies_data, 'company_name')); ?>;
                const topCompanyData = <?php echo json_encode(array_column($top_companies_data, 'alumni_count')); ?>;
                
                if (document.getElementById('topCompaniesChart')) {
                    renderChart('topCompaniesChart', {
                        type: 'bar',
                        data: {
                            labels: topCompanyLabels,
                            datasets: [{
                                label: 'Alumni Count',
                                data: topCompanyData,
                                backgroundColor: '#800000',
                                borderColor: '#800000',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',  // Horizontal bar chart
                            scales: {
                                x: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
                
                // Employment Status Chart
                const statusLabels = <?php echo json_encode(array_column($employment_status_data, 'employment_status')); ?>;
                const statusCounts = <?php echo json_encode(array_column($employment_status_data, 'total')); ?>;
                
                if (document.getElementById('employmentStatusChart')) {
                    renderChart('employmentStatusChart', {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [{
                                data: statusCounts,
                                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
                
                // Handle URL parameters to activate the correct tab
                const url = new URL(window.location.href);
                const view = url.searchParams.get('view');
                if (view) {
                    const tabElement = document.getElementById(`${view}-tab`);
                    if (tabElement) {
                        const tab = new bootstrap.Tab(tabElement);
                        tab.show();
                    }
                }
            } catch (error) {
                console.error("Error initializing charts:", error);
                // Display a general error message at the top of the page
                const container = document.querySelector('.container-fluid');
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                errorAlert.innerHTML = `
                    <strong>Error:</strong> There was a problem loading the charts. Please try refreshing the page.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                container.insertBefore(errorAlert, container.firstChild);
            }
        });
    </script>
</body>
</html> 