<?php
session_start();
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
            SUM(CASE WHEN employment_status != 'Unemployed' THEN 1 ELSE 0 END) as employed_count
            FROM alumni 
            GROUP BY course";
    $result = mysqli_query($conn, $sql);
    $data = [
        'labels' => [],
        'employed' => [],
        'total' => []
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $data['labels'][] = $row['label'];
        $data['employed'][] = $row['employed_count'];
        $data['total'][] = $row['count'];
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
                    $data['total'][$i],
                    $data['employed'][$i],
                    $data['total'][$i] - $data['employed'][$i]
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .tab-pane {
        padding: 20px 0;
    }
    .chart-container {
        position: relative;
        margin: auto;
        height: 300px;
        margin-bottom: 30px;
    }
    .nav-tabs .nav-link {
        color: #800000;
    }
    .nav-tabs .nav-link.active {
        color: #800000;
        font-weight: bold;
        border-bottom: 2px solid #800000;
    }
    .export-btn {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Analytics Dashboard</h2>
                        
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="analyticsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="employment-tab" data-bs-toggle="tab" 
                                        data-bs-target="#employment" type="button" role="tab">
                                    Employment Analysis
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="course-tab" data-bs-toggle="tab" 
                                        data-bs-target="#course" type="button" role="tab">
                                    Course Analysis
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="year-tab" data-bs-toggle="tab" 
                                        data-bs-target="#year" type="button" role="tab">
                                    Year Analysis
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="comparison-tab" data-bs-toggle="tab" 
                                        data-bs-target="#comparison" type="button" role="tab">
                                    Comparison Analysis
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="analyticsTabContent">
                            <!-- Employment Analysis -->
                            <div class="tab-pane fade show active" id="employment" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="employmentStatusChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="salaryRangeChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="employmentByCourseChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="salaryByCourseChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="?export=csv&type=employment" class="btn btn-maroon">
                                        <i class="fas fa-download me-2"></i>Export Employment Data
                                    </a>
                                </div>
                            </div>

                            <!-- Course Analysis -->
                            <div class="tab-pane fade" id="course" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseDistributionChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseEmploymentChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseSalaryChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseProgressionChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="?export=csv&type=course" class="btn btn-maroon">
                                        <i class="fas fa-download me-2"></i>Export Course Data
                                    </a>
                                </div>
                            </div>

                            <!-- Year Analysis -->
                            <div class="tab-pane fade" id="year" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="yearlyTrendChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="yearEmploymentChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="yearSalaryChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="yearProgressionChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="?export=csv&type=year" class="btn btn-maroon">
                                        <i class="fas fa-download me-2"></i>Export Year Data
                                    </a>
                                </div>
                            </div>

                            <!-- Comparison Analysis -->
                            <div class="tab-pane fade" id="comparison" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseSalaryComparisonChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="yearEmploymentComparisonChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="courseProgressionComparisonChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="chart-container">
                                            <canvas id="salaryTrendComparisonChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <a href="?export=csv&type=comparison" class="btn btn-maroon">
                                        <i class="fas fa-download me-2"></i>Export Comparison Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize charts when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration for chart colors
        const chartColors = {
            primary: '#800000',
            secondary: '#36A2EB',
            tertiary: '#FFCE56',
            quaternary: '#4BC0C0',
            quinary: '#FF6384',
            senary: '#9966FF'
        };

        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        };

        // Initialize all charts
        function initializeCharts() {
            // Employment Status Chart
            new Chart(document.getElementById('employmentStatusChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($employmentData['labels']); ?>,
                    datasets: [{
                        data: <?php echo json_encode($employmentData['counts']); ?>,
                        backgroundColor: Object.values(chartColors)
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Employment Status Distribution'
                        }
                    }
                }
            });

            // Salary Range Chart
            new Chart(document.getElementById('salaryRangeChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($salaryData['labels']); ?>,
                    datasets: [{
                        label: 'Number of Alumni',
                        data: <?php echo json_encode($salaryData['counts']); ?>,
                        backgroundColor: chartColors.primary
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Salary Range Distribution'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Employment by Course Chart
            new Chart(document.getElementById('employmentByCourseChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($employmentByCourseData['labels']); ?>,
                    datasets: [{
                        label: 'Employed',
                        data: <?php echo json_encode($employmentByCourseData['employed']); ?>,
                        backgroundColor: chartColors.secondary
                    }, {
                        label: 'Total Alumni',
                        data: <?php echo json_encode($employmentByCourseData['total']); ?>,
                        backgroundColor: chartColors.primary
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Employment by Course'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Course Distribution Chart
            new Chart(document.getElementById('courseDistributionChart'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($courseData['labels']); ?>,
                    datasets: [{
                        data: <?php echo json_encode($courseData['counts']); ?>,
                        backgroundColor: Object.values(chartColors)
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Alumni Distribution by Course'
                        }
                    }
                }
            });

            // Yearly Trend Chart
            new Chart(document.getElementById('yearlyTrendChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($yearlyData['labels']); ?>,
                    datasets: [{
                        label: 'Number of Graduates',
                        data: <?php echo json_encode($yearlyData['counts']); ?>,
                        borderColor: chartColors.primary,
                        tension: 0.1,
                        fill: false
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Graduation Trends Over Years'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Salary by Course Chart
            new Chart(document.getElementById('salaryByCourseChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($salaryByCourseData['labels']); ?>,
                    datasets: [{
                        label: 'Average Salary',
                        data: <?php echo json_encode($salaryByCourseData['averages']); ?>,
                        backgroundColor: chartColors.tertiary
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Average Salary by Course'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Add table data
            function createDataTable(tableId, headers, data) {
                const table = document.createElement('table');
                table.className = 'table table-striped table-hover';
                
                // Create header
                const thead = document.createElement('thead');
                const headerRow = document.createElement('tr');
                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.textContent = header;
                    headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Create body
                const tbody = document.createElement('tbody');
                data.forEach(row => {
                    const tr = document.createElement('tr');
                    row.forEach(cell => {
                        const td = document.createElement('td');
                        td.textContent = cell;
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);

                document.getElementById(tableId).appendChild(table);
            }

            // Add tables to each tab
            // Employment Analysis Table
            createDataTable('employmentTable', 
                ['Employment Status', 'Count', 'Percentage'],
                <?php 
                    $total = array_sum($employmentData['counts']);
                    $tableData = array_map(function($label, $count) use ($total) {
                        return [$label, $count, round(($count/$total)*100, 2) . '%'];
                    }, $employmentData['labels'], $employmentData['counts']);
                    echo json_encode($tableData);
                ?>
            );

            // Course Analysis Table
            createDataTable('courseTable',
                ['Course', 'Total Alumni', 'Employed', 'Employment Rate'],
                <?php 
                    $courseTableData = array_map(function($label, $total, $employed) {
                        return [
                            $label, 
                            $total, 
                            $employed, 
                            round(($employed/$total)*100, 2) . '%'
                        ];
                    }, 
                    $employmentByCourseData['labels'],
                    $employmentByCourseData['total'],
                    $employmentByCourseData['employed']);
                    echo json_encode($courseTableData);
                ?>
            );
        }

        initializeCharts();

        // Add export functionality
        document.querySelectorAll('.export-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const chartId = this.dataset.chart;
                const canvas = document.getElementById(chartId);
                const image = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = chartId + '.png';
                link.href = image;
                link.click();
            });
        });
    });
    </script>
</body>
</html> 