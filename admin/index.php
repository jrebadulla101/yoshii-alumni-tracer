<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM alumni WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("Location: index.php");
    exit();
}

// Handle import action
if (isset($_POST['import'])) {
    if (isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $sql = "INSERT INTO alumni (full_name, course, year_graduated, email, phone, address, 
                    job_title, company_name, company_address, work_position, is_course_related, 
                    employment_status, date_started, is_current_job, date_ended) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssssssss", 
                $data[0], $data[1], $data[2], $data[3], $data[4], $data[5],
                $data[6], $data[7], $data[8], $data[9], $data[10], $data[11],
                $data[12], $data[13], $data[14]);
            
            mysqli_stmt_execute($stmt);
        }
        fclose($handle);
        $success = "Data imported successfully!";
    }
}

// Handle export action
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="alumni_data.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, array(
        'Full Name', 'Course', 'Year Graduated', 'Email', 'Phone', 'Address',
        'Job Title', 'Company Name', 'Company Address', 'Work Position',
        'Is Course Related', 'Employment Status', 'Date Started',
        'Is Current Job', 'Date Ended'
    ));
    
    // Get data
    $sql = "SELECT * FROM alumni";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, array(
            $row['full_name'], $row['course'], $row['year_graduated'],
            $row['email'], $row['phone'], $row['address'],
            $row['job_title'], $row['company_name'], $row['company_address'],
            $row['work_position'], $row['is_course_related'],
            $row['employment_status'], $row['date_started'],
            $row['is_current_job'], $row['date_ended']
        ));
    }
    
    fclose($output);
    exit();
}

// Build query with filters
$where = "1=1";
$params = array();
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (full_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, array($search_param, $search_param, $search_param));
    $types .= "sss";
}

if (isset($_GET['course']) && !empty($_GET['course'])) {
    $where .= " AND course = ?";
    $params[] = $_GET['course'];
    $types .= "s";
}

if (isset($_GET['year']) && !empty($_GET['year'])) {
    $where .= " AND year_graduated = ?";
    $params[] = $_GET['year'];
    $types .= "s";
}

if (isset($_GET['employment']) && !empty($_GET['employment'])) {
    $where .= " AND employment_status = ?";
    $params[] = $_GET['employment'];
    $types .= "s";
}

// Get unique courses for filter
$courses_sql = "SELECT DISTINCT course FROM alumni ORDER BY course";
$courses_result = mysqli_query($conn, $courses_sql);

// Get unique years for filter
$years_sql = "SELECT DISTINCT year_graduated FROM alumni ORDER BY year_graduated DESC";
$years_result = mysqli_query($conn, $years_sql);

// Get alumni data with filters
$sql = "SELECT * FROM alumni WHERE $where ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-maroon">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card glass-effect mb-4">
            <div class="card-body">
                <h4 class="text-maroon mb-4">Filters</h4>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="course">
                            <option value="">All Courses</option>
                            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                <option value="<?php echo htmlspecialchars($course['course']); ?>" <?php echo isset($_GET['course']) && $_GET['course'] == $course['course'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="year">
                            <option value="">All Years</option>
                            <?php while ($year = mysqli_fetch_assoc($years_result)): ?>
                                <option value="<?php echo htmlspecialchars($year['year_graduated']); ?>" <?php echo isset($_GET['year']) && $_GET['year'] == $year['year_graduated'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year['year_graduated']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="employment">
                            <option value="">All Employment Status</option>
                            <option value="Full-time" <?php echo isset($_GET['employment']) && $_GET['employment'] == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo isset($_GET['employment']) && $_GET['employment'] == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Self-employed" <?php echo isset($_GET['employment']) && $_GET['employment'] == 'Self-employed' ? 'selected' : ''; ?>>Self-employed</option>
                            <option value="Unemployed" <?php echo isset($_GET['employment']) && $_GET['employment'] == 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-maroon">Apply Filters</button>
                        <a href="index.php" class="btn btn-outline-maroon">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card glass-effect">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="text-maroon mb-0">Alumni Records</h4>
                    <div>
                        <a href="?export=1" class="btn btn-maroon me-2">
                            <i class="fas fa-download me-2"></i>Export CSV
                        </a>
                        <button type="button" class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload me-2"></i>Import CSV
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Email</th>
                                <th>Employment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year_graduated']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['employment_status']); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-maroon">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this record?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                        </div>
                        <div class="alert alert-info">
                            <small>CSV file should have the following columns: Full Name, Course, Year Graduated, Email, Phone, Address, Job Title, Company Name, Company Address, Work Position, Is Course Related, Employment Status, Date Started, Is Current Job, Date Ended</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="import" class="btn btn-maroon">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize particles.js
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#800000' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: false },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#800000',
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 6,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out',
                    bounce: false
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'repulse' },
                    onclick: { enable: true, mode: 'push' },
                    resize: true
                }
            },
            retina_detect: true
        });
    </script>
</body>
</html> 