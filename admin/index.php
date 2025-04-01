<?php
// Start session at the beginning of the file
session_start();

// Include database connection
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Quick Statistics
// Total Alumni Count
$total_alumni_query = "SELECT COUNT(*) as total FROM alumni";
$total_alumni_result = mysqli_query($conn, $total_alumni_query);
$total_alumni = mysqli_fetch_assoc($total_alumni_result)['total'] ?? 0;

// Employment Rate
$employment_rate_query = "SELECT 
    ROUND((COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END) * 100.0) / 
    NULLIF(COUNT(*), 0), 1) as rate 
    FROM alumni";
$employment_rate_result = mysqli_query($conn, $employment_rate_query);
$employment_rate = mysqli_fetch_assoc($employment_rate_result)['rate'] ?? 0;

// Course-Related Employment
$course_related_query = "SELECT 
    ROUND((COUNT(CASE WHEN is_course_related = 1 AND employment_status IN ('Employed', 'Self-employed') THEN 1 END) * 100.0) / 
    NULLIF(COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END), 0), 1) as rate 
    FROM alumni";
$course_related_result = mysqli_query($conn, $course_related_query);
$course_related_rate = mysqli_fetch_assoc($course_related_result)['rate'] ?? 0;

// Get latest batch year
$latest_batch_query = "SELECT MAX(year_graduated) as latest_year FROM alumni";
$latest_batch_result = mysqli_query($conn, $latest_batch_query);
$latest_batch = mysqli_fetch_assoc($latest_batch_result)['latest_year'] ?? date('Y');

// Course Distribution
$course_stats_query = "SELECT course, COUNT(*) as count FROM alumni GROUP BY course ORDER BY count DESC LIMIT 10";
$course_stats_result = mysqli_query($conn, $course_stats_query);
$course_stats = [];
$course_labels = [];
$course_counts = [];
while ($row = mysqli_fetch_assoc($course_stats_result)) {
    $course_stats[$row['course']] = $row['count'];
    $course_labels[] = $row['course'];
    $course_counts[] = $row['count'];
}

// Year Distribution
$year_stats_query = "SELECT year_graduated, COUNT(*) as count FROM alumni GROUP BY year_graduated ORDER BY year_graduated ASC";
$year_stats_result = mysqli_query($conn, $year_stats_query);
$year_stats = [];
$year_labels = [];
$year_counts = [];
while ($row = mysqli_fetch_assoc($year_stats_result)) {
    $year_stats[$row['year_graduated']] = $row['count'];
    $year_labels[] = $row['year_graduated'];
    $year_counts[] = $row['count'];
}

// Employment Status Distribution
$employment_stats_query = "SELECT employment_status, COUNT(*) as count FROM alumni GROUP BY employment_status";
$employment_stats_result = mysqli_query($conn, $employment_stats_query);
$employment_stats = [];
$employment_labels = [];
$employment_counts = [];
while ($row = mysqli_fetch_assoc($employment_stats_result)) {
    $employment_stats[$row['employment_status']] = $row['count'];
    $employment_labels[] = $row['employment_status'];
    $employment_counts[] = $row['count'];
}

// Alumni Listing with Filtering
// Initialize filter variables
$filter_course = isset($_GET['course']) ? $_GET['course'] : '';
$filter_year = isset($_GET['year']) ? $_GET['year'] : '';
$filter_employment = isset($_GET['employment']) ? $_GET['employment'] : '';
$filter_gender = isset($_GET['gender']) ? $_GET['gender'] : '';
$filter_search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Build WHERE clause for filtering
$where_clauses = [];
if (!empty($filter_course)) {
    $filter_course = mysqli_real_escape_string($conn, $filter_course);
    $where_clauses[] = "course = '$filter_course'";
}
if (!empty($filter_year)) {
    $filter_year = mysqli_real_escape_string($conn, $filter_year);
    $where_clauses[] = "year_graduated = '$filter_year'";
}
if (!empty($filter_employment)) {
    $filter_employment = mysqli_real_escape_string($conn, $filter_employment);
    $where_clauses[] = "employment_status = '$filter_employment'";
}
if (!empty($filter_gender)) {
    $filter_gender = mysqli_real_escape_string($conn, $filter_gender);
    $where_clauses[] = "gender = '$filter_gender'";
}
if (!empty($filter_search)) {
    $filter_search = mysqli_real_escape_string($conn, $filter_search);
    $where_clauses[] = "(CONCAT(first_name, ' ', last_name) LIKE '%$filter_search%' OR email LIKE '%$filter_search%' OR course LIKE '%$filter_search%' OR company_name LIKE '%$filter_search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count total filtered records for pagination
$count_query = "SELECT COUNT(*) as total FROM alumni $where_sql";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'] ?? 0;
$total_pages = ceil($total_records / $limit);

// Get alumni records with filtering and pagination
$alumni_query = "SELECT *, CONCAT(first_name, ' ', last_name) as full_name FROM alumni $where_sql ORDER BY first_name, last_name ASC LIMIT $offset, $limit";
$alumni_result = mysqli_query($conn, $alumni_query);

// Get all courses for filter dropdown
$all_courses_query = "SELECT DISTINCT course FROM alumni ORDER BY course ASC";
$all_courses_result = mysqli_query($conn, $all_courses_query);
$all_courses = [];
while ($row = mysqli_fetch_assoc($all_courses_result)) {
    $all_courses[] = $row['course'];
}

// Get all years for filter dropdown
$all_years_query = "SELECT DISTINCT year_graduated FROM alumni ORDER BY year_graduated DESC";
$all_years_result = mysqli_query($conn, $all_years_query);
$all_years = [];
while ($row = mysqli_fetch_assoc($all_years_result)) {
    $all_years[] = $row['year_graduated'];
}

// Get all employment statuses for filter dropdown
$all_employment_query = "SELECT DISTINCT employment_status FROM alumni ORDER BY employment_status ASC";
$all_employment_result = mysqli_query($conn, $all_employment_query);
$all_employment = [];
while ($row = mysqli_fetch_assoc($all_employment_result)) {
    $all_employment[] = $row['employment_status'];
}

// Get all genders for filter dropdown
$all_gender_query = "SELECT DISTINCT gender FROM alumni WHERE gender IS NOT NULL ORDER BY gender ASC";
$all_gender_result = mysqli_query($conn, $all_gender_query);
$all_genders = [];
while ($row = mysqli_fetch_assoc($all_gender_result)) {
    $all_genders[] = $row['gender'];
}

// Include the navbar
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #ffffff;
            color: #333;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        .content-wrapper {
            position: relative;
            z-index: 1;
            padding: 20px;
        }
        .stats-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stat-item {
            margin-bottom: 20px;
        }
        .stat-label {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #800000;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .card-header {
            background: #800000;
            color: #fff;
            border-radius: 15px 15px 0 0 !important;
        }
        .text-maroon {
            color: #800000;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="content-wrapper">
        <div class="container-fluid py-4">
            <h2 class="text-maroon mb-4">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard Overview
            </h2>

            <!-- Overview Statistics -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Total Alumni</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon rounded-circle bg-maroon p-3 me-3">
                                    <i class="fas fa-user-graduate text-white fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="stat-value m-0"><?php echo number_format($total_alumni); ?></h3>
                                    <div class="text-muted">Registered Alumni</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Employment Rate</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon rounded-circle bg-success p-3 me-3">
                                    <i class="fas fa-briefcase text-white fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="stat-value m-0"><?php echo $employment_rate; ?>%</h3>
                                    <div class="text-muted">Currently Employed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Course-Related</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon rounded-circle bg-primary p-3 me-3">
                                    <i class="fas fa-graduation-cap text-white fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="stat-value m-0"><?php echo $course_related_rate; ?>%</h3>
                                    <div class="text-muted">Field-Related Work</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Latest Batch</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon rounded-circle bg-warning p-3 me-3">
                                    <i class="fas fa-calendar-alt text-white fa-2x"></i>
                                </div>
                                <div>
                                    <h3 class="stat-value m-0"><?php echo $latest_batch; ?></h3>
                                    <div class="text-muted">Most Recent Graduates</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Course Distribution</h5>
                            <a href="analytics.php?view=course" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="courseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Employment Status</h5>
                            <a href="analytics.php?view=employment" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="employmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Trends -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="stats-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Yearly Graduate Distribution</h5>
                            <a href="analytics.php?view=year" class="btn btn-sm btn-light">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="yearlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alumni Listing -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card glass-effect">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Alumni Records</h5>
                            <a href="add_alumni.php" class="btn btn-sm btn-light">
                                <i class="fas fa-plus"></i> Add New Alumni
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Filters -->
                            <form method="GET" action="index.php" class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Course</label>
                                    <select class="form-select" name="course">
                                        <option value="">All Courses</option>
                                        <?php foreach ($all_courses as $course): ?>
                                            <option value="<?php echo htmlspecialchars($course); ?>" <?php echo $filter_course === $course ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Year Graduated</label>
                                    <select class="form-select" name="year">
                                        <option value="">All Years</option>
                                        <?php foreach ($all_years as $year): ?>
                                            <option value="<?php echo htmlspecialchars($year); ?>" <?php echo $filter_year === $year ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($year); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Employment Status</label>
                                    <select class="form-select" name="employment">
                                        <option value="">All Statuses</option>
                                        <?php foreach ($all_employment as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $filter_employment === $status ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">All Genders</option>
                                        <?php foreach ($all_genders as $gender): ?>
                                            <option value="<?php echo htmlspecialchars($gender); ?>" <?php echo $filter_gender === $gender ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($gender); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Name, Email, Course...">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-maroon w-100">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <!-- Results Count -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="text-muted">Showing <?php echo min($total_records, $offset + 1); ?>-<?php echo min($total_records, $offset + $limit); ?> of <?php echo $total_records; ?> records</span>
                                </div>
                                <div>
                                    <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline-maroon">
                                        <i class="fas fa-download me-1"></i>Export Results
                                    </a>
                                </div>
                            </div>

                            <!-- Alumni Table -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Course</th>
                                            <th>Year</th>
                                            <th>Employment</th>
                                            <th>Company</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($alumni_result) > 0) {
                                            $counter = $offset + 1;
                                            while ($alumni = mysqli_fetch_assoc($alumni_result)):
                                        ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td><?php echo htmlspecialchars($alumni['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['gender'] ?? 'Not specified'); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['course']); ?></td>
                                            <td><?php echo htmlspecialchars($alumni['year_graduated']); ?></td>
                                            <td>
                                                <?php
                                                $status_class = 'secondary';
                                                switch($alumni['employment_status']) {
                                                    case 'Employed':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'Self-employed':
                                                        $status_class = 'primary';
                                                        break;
                                                    case 'Unemployed':
                                                        $status_class = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge bg-<?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($alumni['employment_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($alumni['company_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="view_alumni.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this alumni record?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                            endwhile;
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="empty-state">
                                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                                    <h5>No alumni records found</h5>
                                                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Alumni pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $start_page + 4);
                                    if ($end_page - $start_page < 4) {
                                        $start_page = max(1, $end_page - 4);
                                    }
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Charts initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Course Distribution Chart
            new Chart(document.getElementById('courseChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($course_labels); ?>,
                    datasets: [{
                        label: 'Number of Alumni',
                        data: <?php echo json_encode($course_counts); ?>,
                        backgroundColor: '#800000',
                        borderColor: '#800000',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Employment Status Chart
            new Chart(document.getElementById('employmentChart'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($employment_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($employment_counts); ?>,
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8'],
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

            // Yearly Trends Chart
            new Chart(document.getElementById('yearlyChart'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($year_labels); ?>,
                    datasets: [{
                        label: 'Number of Graduates',
                        data: <?php echo json_encode($year_counts); ?>,
                        borderColor: '#800000',
                        backgroundColor: 'rgba(128, 0, 0, 0.1)',
                        borderWidth: 2,
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
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });

        // Additional styling for stats cards
        document.querySelectorAll('.stats-icon').forEach(icon => {
            icon.style.width = '64px';
            icon.style.height = '64px';
            icon.style.display = 'flex';
            icon.style.alignItems = 'center';
            icon.style.justifyContent = 'center';
        });
    </script>
</body>
</html>
