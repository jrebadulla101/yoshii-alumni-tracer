<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get alumni information
$sql = "SELECT * FROM alumni WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$alumni = mysqli_fetch_assoc($result);

if (!$alumni) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_graduated = mysqli_real_escape_string($conn, $_POST['year_graduated']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $company_address = mysqli_real_escape_string($conn, $_POST['company_address']);
    $work_position = mysqli_real_escape_string($conn, $_POST['work_position']);
    $is_course_related = mysqli_real_escape_string($conn, $_POST['is_course_related']);
    $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
    $date_started = mysqli_real_escape_string($conn, $_POST['date_started']);
    $is_current_job = mysqli_real_escape_string($conn, $_POST['is_current_job']);
    $date_ended = mysqli_real_escape_string($conn, $_POST['date_ended']);
    $additional_info = mysqli_real_escape_string($conn, $_POST['additional_info']);

    $update_sql = "UPDATE alumni SET 
                   full_name = ?, 
                   course = ?, 
                   year_graduated = ?, 
                   email = ?, 
                   phone = ?, 
                   address = ?, 
                   job_title = ?, 
                   company_name = ?, 
                   company_address = ?, 
                   work_position = ?, 
                   is_course_related = ?, 
                   employment_status = ?, 
                   date_started = ?, 
                   is_current_job = ?, 
                   date_ended = ?, 
                   additional_info = ? 
                   WHERE id = ?";

    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssssssssssssssssi", 
        $full_name, $course, $year_graduated, $email, $phone, $address,
        $job_title, $company_name, $company_address, $work_position,
        $is_course_related, $employment_status, $date_started,
        $is_current_job, $date_ended, $additional_info, $id);

    if (mysqli_stmt_execute($update_stmt)) {
        $success = "Information updated successfully!";
        // Refresh alumni data
        $result = mysqli_stmt_get_result($stmt);
        $alumni = mysqli_fetch_assoc($result);
    } else {
        $error = "Error updating information: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-maroon">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Dashboard</a>
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
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card glass-effect">
                    <div class="card-body">
                        <h2 class="text-center text-maroon mb-4">Edit Alumni Information</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <!-- Personal Information -->
                            <div class="section-title mb-4">
                                <h4 class="text-maroon">Personal Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($alumni['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course</label>
                                    <input type="text" class="form-control" name="course" value="<?php echo htmlspecialchars($alumni['course']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Year Graduated</label>
                                    <input type="number" class="form-control" name="year_graduated" value="<?php echo htmlspecialchars($alumni['year_graduated']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($alumni['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($alumni['phone']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Complete Address</label>
                                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($alumni['address']); ?>" required>
                                </div>
                            </div>

                            <!-- Employment Information -->
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
                                <a href="index.php" class="btn btn-outline-maroon btn-lg ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
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
</body>
</html> 