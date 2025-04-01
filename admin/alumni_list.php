<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15; // Default 15 records per page
if ($limit <= 0) $limit = 15;
$offset = ($page - 1) * $limit;

// Get filter parameters
$filter_course = isset($_GET['course']) ? mysqli_real_escape_string($conn, $_GET['course']) : '';
$filter_year = isset($_GET['year']) ? mysqli_real_escape_string($conn, $_GET['year']) : '';
$filter_employment = isset($_GET['employment']) ? mysqli_real_escape_string($conn, $_GET['employment']) : '';
$filter_gender = isset($_GET['gender']) ? mysqli_real_escape_string($conn, $_GET['gender']) : '';
$filter_search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if ($filter_course) {
    $conditions[] = "course = ?";
    $params[] = $filter_course;
    $types .= 's';
}

if ($filter_year) {
    $conditions[] = "year_graduated = ?";
    $params[] = $filter_year;
    $types .= 's';
}

if ($filter_employment) {
    $conditions[] = "employment_status = ?";
    $params[] = $filter_employment;
    $types .= 's';
}

if ($filter_gender) {
    $conditions[] = "gender = ?";
    $params[] = $filter_gender;
    $types .= 's';
}

if ($filter_search) {
    $conditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR student_number LIKE ? OR course LIKE ?)";
    $search_param = "%$filter_search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

// Construct the WHERE clause
$where_clause = '';
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Count total records for pagination
$count_query = "SELECT COUNT(*) as total FROM alumni $where_clause";
$stmt = mysqli_prepare($conn, $count_query);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_records = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total_records / $limit);

// Ensure page is within valid range
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Fetch records with pagination
$alumni_query = "SELECT *, CONCAT(first_name, ' ', last_name) AS full_name FROM alumni $where_clause ORDER BY created_at DESC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $alumni_query);

if (!empty($params)) {
    $params[] = $offset;
    $params[] = $limit;
    $types .= 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $offset, $limit);
}

mysqli_stmt_execute($stmt);
$alumni_result = mysqli_stmt_get_result($stmt);

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
    <title>Alumni List - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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
        .card-header {
            background: #800000;
            color: #fff;
            border-radius: 15px 15px 0 0 !important;
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
        .btn-outline-maroon {
            color: #800000;
            border-color: #800000;
        }
        .btn-outline-maroon:hover {
            background-color: #800000;
            color: white;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .empty-state {
            padding: 40px;
            text-align: center;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .page-link {
            color: #800000;
        }
        .page-item.active .page-link {
            background-color: #800000;
            border-color: #800000;
        }
        .badge.bg-success, .badge.bg-primary, .badge.bg-danger, .badge.bg-secondary, .badge.bg-warning {
            font-weight: 500;
            padding: 5px 8px;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="content-wrapper">
        <div class="container-fluid py-4">
            <div class="card glass-effect">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            Alumni List
                        </h4>
                        <div>
                            <a href="add_alumni.php" class="btn btn-light">
                                <i class="fas fa-plus me-1"></i>Add New Alumni
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="filter-form">
                        <form method="GET" action="" class="row g-3 align-items-end">
                            <div class="col-md-2">
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Name, Email, ID...">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-maroon w-100">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="alumni_list.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Results Count and Export -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">Showing <?php echo $total_records > 0 ? min($total_records, $offset + 1) : 0; ?>-<?php echo min($total_records, $offset + $limit); ?> of <?php echo $total_records; ?> records</span>
                        </div>
                        <div>
                            <a href="export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-sm btn-outline-maroon">
                                <i class="fas fa-download me-1"></i>Export Results
                            </a>
                        </div>
                    </div>

                    <!-- Alumni Table -->
                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student No.</th>
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
                                    <td><?php echo htmlspecialchars($alumni['student_number']); ?></td>
                                    <td><?php echo htmlspecialchars($alumni['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($alumni['gender'] ?? 'Not specified'); ?></td>
                                    <td><?php echo htmlspecialchars($alumni['course']); ?></td>
                                    <td><?php echo htmlspecialchars($alumni['year_graduated']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        switch($alumni['employment_status']) {
                                            case 'Full-time':
                                            case 'Part-time':
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
                                    <td class="action-buttons">
                                        <div class="btn-group btn-group-sm">
                                            <a href="view_alumni.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="btn btn-danger" title="Delete" 
                                               onclick="confirmDelete(<?php echo $alumni['alumni_id']; ?>, '<?php echo htmlspecialchars($alumni['full_name']); ?>')">
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
                                    <td colspan="9" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                            <h5>No alumni records found</h5>
                                            <p class="text-muted">Try adjusting your search or filter criteria</p>
                                            <a href="add_alumni.php" class="btn btn-maroon mt-3">
                                                <i class="fas fa-plus me-1"></i>Add New Alumni
                                            </a>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the alumni record for <strong id="deleteAlumniName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteButton" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Particle.js initialization
        document.addEventListener('DOMContentLoaded', function() {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: '#800000' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.5, random: false },
                    size: { value: 3, random: true },
                    line_linked: { enable: true, distance: 150, color: '#800000', opacity: 0.4, width: 1 },
                    move: { enable: true, speed: 2, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                    modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
                },
                retina_detect: true
            });
        });
        
        // Delete confirmation
        function confirmDelete(id, name) {
            document.getElementById('deleteAlumniName').textContent = name;
            document.getElementById('confirmDeleteButton').href = 'delete.php?id=' + id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html> 