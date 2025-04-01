<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Check session status
error_log("Dashboard - Session check");
error_log("Session ID exists: " . (isset($_SESSION['alumni_id']) ? "Yes" : "No"));
if (isset($_SESSION['alumni_id'])) {
    error_log("Alumni ID: " . $_SESSION['alumni_id']);
}

// Check if user is logged in
if (!isset($_SESSION['alumni_id'])) {
    error_log("No session found - redirecting to login");
    header("Location: login.php");
    exit();
}

// Get alumni information
$alumni_id = $_SESSION['alumni_id'];

// Debug: Log the alumni_id
error_log("Fetching alumni info for ID: " . $alumni_id);

// Get alumni information with correct column name
$sql = "SELECT * FROM alumni WHERE alumni_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($conn));
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $alumni_id);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute failed: " . mysqli_error($conn));
    die("Database error: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($stmt);
$alumni = mysqli_fetch_assoc($result);

if (!$alumni) {
    error_log("No alumni found with ID: " . $alumni_id);
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get work history with correct column name
$work_sql = "SELECT * FROM work_history WHERE alumni_id = ? ORDER BY date_started DESC";
$work_stmt = mysqli_prepare($conn, $work_sql);
mysqli_stmt_bind_param($work_stmt, "i", $alumni_id);
mysqli_stmt_execute($work_stmt);
$work_result = mysqli_stmt_get_result($work_stmt);
$work_history = [];
while ($work = mysqli_fetch_assoc($work_result)) {
    $work_history[] = $work;
}

// Debug: Check alumni data
error_log("Alumni data retrieved successfully");
error_log("Name: " . $alumni['first_name'] . " " . $alumni['last_name']);

// Handle new work information submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_profile') {
        // Process profile update
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $additional_info = mysqli_real_escape_string($conn, $_POST['additional_info']);
        
        $update_profile_sql = "UPDATE alumni SET 
                               email = ?, phone = ?, address = ?, additional_info = ? 
                               WHERE alumni_id = ?";
        $update_profile_stmt = mysqli_prepare($conn, $update_profile_sql);
        mysqli_stmt_bind_param($update_profile_stmt, "ssssi", 
            $email, $phone, $address, $additional_info, $alumni_id);
            
        if (mysqli_stmt_execute($update_profile_stmt)) {
            $success = "Profile information updated successfully!";
            
            // Refresh alumni data
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $alumni = mysqli_fetch_assoc($result);
        } else {
            $error = "Error updating profile information: " . mysqli_error($conn);
        }
    } elseif ($_POST['action'] == 'add_work') {
        $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $company_address = mysqli_real_escape_string($conn, $_POST['company_address']);
        $work_position = mysqli_real_escape_string($conn, $_POST['work_position']);
        $is_course_related = mysqli_real_escape_string($conn, $_POST['is_course_related']);
        $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
        $date_started = mysqli_real_escape_string($conn, $_POST['date_started']);
        $is_current_job = mysqli_real_escape_string($conn, $_POST['is_current_job']);
        $date_ended = mysqli_real_escape_string($conn, $_POST['date_ended']);
        $salary = mysqli_real_escape_string($conn, $_POST['salary']);
        $industry = mysqli_real_escape_string($conn, $_POST['industry']);

        $insert_sql = "INSERT INTO work_history (alumni_id, job_title, company_name, company_address, 
                       work_position, is_course_related, employment_status, date_started, is_current_job, 
                       date_ended, salary, industry, date_added) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "isssssssssss", 
            $alumni_id, $job_title, $company_name, $company_address, $work_position, 
            $is_course_related, $employment_status, $date_started, $is_current_job, 
            $date_ended, $salary, $industry);

        if (mysqli_stmt_execute($insert_stmt)) {
            $success = "New work information added successfully!";
            
            // Refresh the work history data
            mysqli_stmt_execute($work_stmt);
            $work_result = mysqli_stmt_get_result($work_stmt);
            $work_history = [];
            while ($work = mysqli_fetch_assoc($work_result)) {
                $work_history[] = $work;
            }
        } else {
            $error = "Error adding work information: " . mysqli_error($conn);
        }
    } elseif ($_POST['action'] == 'edit_work') {
        $work_id = mysqli_real_escape_string($conn, $_POST['work_id']);
        $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $company_address = mysqli_real_escape_string($conn, $_POST['company_address']);
        $work_position = mysqli_real_escape_string($conn, $_POST['work_position']);
        $is_course_related = mysqli_real_escape_string($conn, $_POST['is_course_related']);
        $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
        $date_started = mysqli_real_escape_string($conn, $_POST['date_started']);
        $is_current_job = mysqli_real_escape_string($conn, $_POST['is_current_job']);
        $date_ended = mysqli_real_escape_string($conn, $_POST['date_ended']);
        $salary = mysqli_real_escape_string($conn, $_POST['salary']);
        $industry = mysqli_real_escape_string($conn, $_POST['industry']);
        
        // Verify that this work history entry belongs to the current alumni
        $verify_sql = "SELECT id FROM work_history WHERE id = ? AND alumni_id = ?";
        $verify_stmt = mysqli_prepare($conn, $verify_sql);
        mysqli_stmt_bind_param($verify_stmt, "ii", $work_id, $alumni_id);
        mysqli_stmt_execute($verify_stmt);
        $verify_result = mysqli_stmt_get_result($verify_stmt);
        
        if (mysqli_num_rows($verify_result) == 0) {
            $error = "You don't have permission to edit this work history entry.";
        } else {
            $update_sql = "UPDATE work_history SET 
                          job_title = ?, company_name = ?, company_address = ?,
                          work_position = ?, is_course_related = ?, employment_status = ?,
                          date_started = ?, is_current_job = ?, date_ended = ?,
                          salary = ?, industry = ? WHERE id = ?";
            
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sssssssssssi", 
                $job_title, $company_name, $company_address, $work_position, 
                $is_course_related, $employment_status, $date_started, $is_current_job, 
                $date_ended, $salary, $industry, $work_id);
                
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Work information updated successfully!";
                
                // Refresh the work history data
                mysqli_stmt_execute($work_stmt);
                $work_result = mysqli_stmt_get_result($work_stmt);
                $work_history = [];
                while ($work = mysqli_fetch_assoc($work_result)) {
                    $work_history[] = $work;
                }
            } else {
                $error = "Error updating work information: " . mysqli_error($conn);
            }
        }
    } elseif ($_POST['action'] == 'delete_work') {
        $work_id = mysqli_real_escape_string($conn, $_POST['work_id']);
        
        // Verify that this work history entry belongs to the current alumni
        $verify_sql = "SELECT id FROM work_history WHERE id = ? AND alumni_id = ?";
        $verify_stmt = mysqli_prepare($conn, $verify_sql);
        mysqli_stmt_bind_param($verify_stmt, "ii", $work_id, $alumni_id);
        mysqli_stmt_execute($verify_stmt);
        $verify_result = mysqli_stmt_get_result($verify_stmt);
        
        if (mysqli_num_rows($verify_result) == 0) {
            $error = "You don't have permission to delete this work history entry.";
        } else {
            $delete_sql = "DELETE FROM work_history WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "i", $work_id);
                
            if (mysqli_stmt_execute($delete_stmt)) {
                $success = "Work information deleted successfully!";
                
                // Refresh the work history data
                mysqli_stmt_execute($work_stmt);
                $work_result = mysqli_stmt_get_result($work_stmt);
                $work_history = [];
                while ($work = mysqli_fetch_assoc($work_result)) {
                    $work_history[] = $work;
                }
            } else {
                $error = "Error deleting work information: " . mysqli_error($conn);
            }
        }
    }
}

// Helper function to determine badge color for employment status
function getStatusColor($status) {
    switch ($status) {
        case 'Full-time':
            return 'success';
        case 'Part-time':
            return 'info';
        case 'Contract':
            return 'warning';
        case 'Self-employed':
            return 'primary';
        case 'Unemployed':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div id="particles-js"></div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-maroon">
        <div class="container">
            <a class="navbar-brand" href="#">Alumni Tracer System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['alumni_name']); ?></span>
                    </li>
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
        <div class="row">
            <div class="col-md-12">
                <div class="card glass-effect">
                    <div class="card-body">
                        <h2 class="text-center text-maroon mb-4">Alumni Dashboard</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="update_profile">
                            <!-- Personal Information (Read-only) -->
                            <div class="section-title mb-4">
                                <h4 class="text-maroon">Personal Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Student Number</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni['student_number']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni['first_name'] . ' ' . $alumni['last_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni['course']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Year Graduated</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni['year_graduated']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($alumni['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" value="<?php echo htmlspecialchars($alumni['phone']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Complete Address</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($alumni['address']); ?>" readonly>
                                </div>
                            </div>

                            <!-- Employment Information (Editable) -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Employment Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Job Title</label>
                                    <input type="text" class="form-control" name="job_title" value="<?php echo htmlspecialchars($alumni['job_title']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($alumni['company_name']); ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Company Address</label>
                                    <input type="text" class="form-control" name="company_address" value="<?php echo htmlspecialchars($alumni['company_address']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Position/Level</label>
                                    <input type="text" class="form-control" name="work_position" value="<?php echo htmlspecialchars($alumni['work_position']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Is Your Job Related to Your Course?</label>
                                    <select class="form-select" name="is_course_related" required>
                                        <option value="Yes" <?php echo $alumni['is_course_related'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="No" <?php echo $alumni['is_course_related'] == 'No' ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employment Status</label>
                                    <select class="form-select" name="employment_status" required>
                                        <option value="Full-time" <?php echo $alumni['employment_status'] == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                        <option value="Part-time" <?php echo $alumni['employment_status'] == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                        <option value="Self-employed" <?php echo $alumni['employment_status'] == 'Self-employed' ? 'selected' : ''; ?>>Self-employed</option>
                                        <option value="Unemployed" <?php echo $alumni['employment_status'] == 'Unemployed' ? 'selected' : ''; ?>>Unemployed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Started</label>
                                    <input type="date" class="form-control" name="date_started" value="<?php echo htmlspecialchars($alumni['date_started']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Is this your current job?</label>
                                    <select class="form-select" name="is_current_job" required>
                                        <option value="Yes" <?php echo $alumni['is_current_job'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                        <option value="No" <?php echo $alumni['is_current_job'] == 'No' ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Ended (if not current job)</label>
                                    <input type="date" class="form-control" name="date_ended" value="<?php echo htmlspecialchars($alumni['date_ended']); ?>">
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Additional Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Additional Information</label>
                                    <textarea class="form-control" name="additional_info" rows="3"><?php echo htmlspecialchars($alumni['additional_info']); ?></textarea>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-maroon btn-lg">
                                    <i class="fas fa-save me-2"></i>Update Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work History Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card glass-effect">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="text-maroon">
                                <i class="fas fa-briefcase me-2"></i>Work History
                            </h3>
                            <button type="button" class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#addWorkModal">
                                <i class="fas fa-plus-circle me-2"></i>Add New Work Experience
                            </button>
                        </div>

                        <?php if (empty($work_history)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                            <h5>No work history entries yet</h5>
                            <p class="text-muted">Add your work experiences to help us track your career progress</p>
                            <button type="button" class="btn btn-outline-maroon mt-2" data-bs-toggle="modal" data-bs-target="#addWorkModal">
                                <i class="fas fa-plus-circle me-2"></i>Add Work Experience
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($work_history as $index => $work): ?>
                            <div class="timeline-item">
                                <div class="card mb-3">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($work['job_title']); ?></h5>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editWorkModal<?php echo $work['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteWorkModal<?php echo $work['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-building me-2"></i>Company:</strong> <?php echo htmlspecialchars($work['company_name']); ?></p>
                                                <p><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> <?php echo htmlspecialchars($work['company_address']); ?></p>
                                                <p><strong><i class="fas fa-user-tie me-2"></i>Position:</strong> <?php echo htmlspecialchars($work['work_position']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-calendar-alt me-2"></i>Period:</strong> 
                                                    <?php 
                                                    echo date('M Y', strtotime($work['date_started']));
                                                    echo ' - ';
                                                    echo ($work['is_current_job'] == 'Yes') ? 'Present' : date('M Y', strtotime($work['date_ended']));
                                                    ?>
                                                </p>
                                                <p><strong><i class="fas fa-briefcase me-2"></i>Status:</strong> 
                                                    <span class="badge bg-<?php echo getStatusColor($work['employment_status']); ?>">
                                                        <?php echo htmlspecialchars($work['employment_status']); ?>
                                                    </span>
                                                </p>
                                                <p><strong><i class="fas fa-graduation-cap me-2"></i>Course Related:</strong> 
                                                    <span class="badge bg-<?php echo $work['is_course_related'] == 'Yes' ? 'success' : 'secondary'; ?>">
                                                        <?php echo htmlspecialchars($work['is_course_related']); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <?php if (!empty($work['industry']) || !empty($work['salary'])): ?>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <?php if (!empty($work['industry'])): ?>
                                                <p><strong><i class="fas fa-industry me-2"></i>Industry:</strong> <?php echo htmlspecialchars($work['industry']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if (!empty($work['salary'])): ?>
                                                <p><strong><i class="fas fa-money-bill-wave me-2"></i>Salary Range:</strong> <?php echo htmlspecialchars($work['salary']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Work Modal -->
                            <div class="modal fade" id="editWorkModal<?php echo $work['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-maroon text-white">
                                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Work Information</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" class="needs-validation" novalidate>
                                            <input type="hidden" name="action" value="edit_work">
                                            <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Job Title</label>
                                                        <input type="text" class="form-control" name="job_title" value="<?php echo htmlspecialchars($work['job_title']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Company Name</label>
                                                        <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($work['company_name']); ?>" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Company Address</label>
                                                        <input type="text" class="form-control" name="company_address" value="<?php echo htmlspecialchars($work['company_address']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Position Level</label>
                                                        <select class="form-select" name="work_position" required>
                                                            <option value="">Select Position Level...</option>
                                                            <option value="Entry Level" <?php echo $work['work_position'] == 'Entry Level' ? 'selected' : ''; ?>>Entry Level</option>
                                                            <option value="Junior Level" <?php echo $work['work_position'] == 'Junior Level' ? 'selected' : ''; ?>>Junior Level</option>
                                                            <option value="Mid Level" <?php echo $work['work_position'] == 'Mid Level' ? 'selected' : ''; ?>>Mid Level</option>
                                                            <option value="Senior Level" <?php echo $work['work_position'] == 'Senior Level' ? 'selected' : ''; ?>>Senior Level</option>
                                                            <option value="Manager" <?php echo $work['work_position'] == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                                            <option value="Executive" <?php echo $work['work_position'] == 'Executive' ? 'selected' : ''; ?>>Executive</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Employment Status</label>
                                                        <select class="form-select" name="employment_status" required>
                                                            <option value="">Select Status...</option>
                                                            <option value="Full-time" <?php echo $work['employment_status'] == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                                            <option value="Part-time" <?php echo $work['employment_status'] == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                                            <option value="Contract" <?php echo $work['employment_status'] == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                                            <option value="Self-employed" <?php echo $work['employment_status'] == 'Self-employed' ? 'selected' : ''; ?>>Self-employed</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Industry</label>
                                                        <input type="text" class="form-control" name="industry" value="<?php echo htmlspecialchars($work['industry']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Salary Range</label>
                                                        <select class="form-select" name="salary" required>
                                                            <option value="">Select Range...</option>
                                                            <option value="Below ₱20,000" <?php echo $work['salary'] == 'Below ₱20,000' ? 'selected' : ''; ?>>Below ₱20,000</option>
                                                            <option value="₱20,000 - ₱30,000" <?php echo $work['salary'] == '₱20,000 - ₱30,000' ? 'selected' : ''; ?>>₱20,000 - ₱30,000</option>
                                                            <option value="₱30,000 - ₱40,000" <?php echo $work['salary'] == '₱30,000 - ₱40,000' ? 'selected' : ''; ?>>₱30,000 - ₱40,000</option>
                                                            <option value="₱40,000 - ₱50,000" <?php echo $work['salary'] == '₱40,000 - ₱50,000' ? 'selected' : ''; ?>>₱40,000 - ₱50,000</option>
                                                            <option value="Above ₱50,000" <?php echo $work['salary'] == 'Above ₱50,000' ? 'selected' : ''; ?>>Above ₱50,000</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Date Started</label>
                                                        <input type="date" class="form-control" name="date_started" value="<?php echo htmlspecialchars($work['date_started']); ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Is Current Job?</label>
                                                        <select class="form-select" name="is_current_job" onchange="toggleDateEndedEdit(this, <?php echo $work['id']; ?>)" required>
                                                            <option value="">Select...</option>
                                                            <option value="Yes" <?php echo $work['is_current_job'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                                            <option value="No" <?php echo $work['is_current_job'] == 'No' ? 'selected' : ''; ?>>No</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6" id="dateEndedField<?php echo $work['id']; ?>" style="display: <?php echo $work['is_current_job'] == 'No' ? 'block' : 'none'; ?>;">
                                                        <label class="form-label">Date Ended</label>
                                                        <input type="date" class="form-control" name="date_ended" value="<?php echo htmlspecialchars($work['date_ended']); ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Is Course Related?</label>
                                                        <select class="form-select" name="is_course_related" required>
                                                            <option value="">Select...</option>
                                                            <option value="Yes" <?php echo $work['is_course_related'] == 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                                            <option value="No" <?php echo $work['is_course_related'] == 'No' ? 'selected' : ''; ?>>No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-maroon">Update Work Information</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Work Modal -->
                            <div class="modal fade" id="deleteWorkModal<?php echo $work['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Confirmation</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="delete_work">
                                            <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete your work history entry for <strong><?php echo htmlspecialchars($work['job_title']); ?></strong> at <strong><?php echo htmlspecialchars($work['company_name']); ?></strong>?</p>
                                                <p class="text-danger"><small>This action cannot be undone.</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Work Modal -->
    <div class="modal fade" id="addWorkModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-maroon text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Work Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="add_work">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Job Title</label>
                                <input type="text" class="form-control" name="job_title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="company_name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Company Address</label>
                                <input type="text" class="form-control" name="company_address" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position Level</label>
                                <select class="form-select" name="work_position" required>
                                    <option value="">Select Position Level...</option>
                                    <option value="Entry Level">Entry Level</option>
                                    <option value="Junior Level">Junior Level</option>
                                    <option value="Mid Level">Mid Level</option>
                                    <option value="Senior Level">Senior Level</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Executive">Executive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employment Status</label>
                                <select class="form-select" name="employment_status" required>
                                    <option value="">Select Status...</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Self-employed">Self-employed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Industry</label>
                                <input type="text" class="form-control" name="industry" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Salary Range</label>
                                <select class="form-select" name="salary" required>
                                    <option value="">Select Range...</option>
                                    <option value="Below ₱20,000">Below ₱20,000</option>
                                    <option value="₱20,000 - ₱30,000">₱20,000 - ₱30,000</option>
                                    <option value="₱30,000 - ₱40,000">₱30,000 - ₱40,000</option>
                                    <option value="₱40,000 - ₱50,000">₱40,000 - ₱50,000</option>
                                    <option value="Above ₱50,000">Above ₱50,000</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Started</label>
                                <input type="date" class="form-control" name="date_started" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Is Current Job?</label>
                                <select class="form-select" name="is_current_job" onchange="toggleDateEnded(this)" required>
                                    <option value="">Select...</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="dateEndedField" style="display: none;">
                                <label class="form-label">Date Ended</label>
                                <input type="date" class="form-control" name="date_ended">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Is Course Related?</label>
                                <select class="form-select" name="is_course_related" required>
                                    <option value="">Select...</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-maroon">Add Work Information</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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

        function toggleDateEnded(select) {
            const dateEndedField = document.getElementById('dateEndedField');
            const dateEndedInput = document.querySelector('input[name="date_ended"]');
            
            if (select.value === 'No') {
                dateEndedField.style.display = 'block';
                dateEndedInput.required = true;
            } else {
                dateEndedField.style.display = 'none';
                dateEndedInput.required = false;
                dateEndedInput.value = '';
            }
        }
        
        function toggleDateEndedEdit(select, id) {
            const dateEndedField = document.getElementById('dateEndedField' + id);
            const dateEndedInput = dateEndedField.querySelector('input[name="date_ended"]');
            
            if (select.value === 'No') {
                dateEndedField.style.display = 'block';
                dateEndedInput.required = true;
            } else {
                dateEndedField.style.display = 'none';
                dateEndedInput.required = false;
                dateEndedInput.value = '';
            }
        }

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

    <style>
    .bg-maroon {
        background-color: #800000 !important;
    }
    .btn-maroon {
        background-color: #800000;
        color: white;
    }
    .btn-maroon:hover {
        background-color: #600000;
        color: white;
    }
    .timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 20px;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #800000;
    }
    .timeline-item:after {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #800000;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    </style>
</body>
</html> 