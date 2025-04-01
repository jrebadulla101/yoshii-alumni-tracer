<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get all courses for dropdown
$courses_query = "SELECT DISTINCT course_name FROM courses ORDER BY course_name";
$courses_result = mysqli_query($conn, $courses_query);
$courses = [];
while ($row = mysqli_fetch_assoc($courses_result)) {
    $courses[] = $row['course_name'];
}

// Process form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $student_number = mysqli_real_escape_string($conn, $_POST['student_number'] ?? '');
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name'] ?? '');
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name'] ?? '');
    $middle_initial = !empty($_POST['middle_name']) ? substr($_POST['middle_name'], 0, 1) : null;
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '');
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    $course = mysqli_real_escape_string($conn, $_POST['course'] ?? '');
    $year_graduated = mysqli_real_escape_string($conn, $_POST['year_graduated'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status'] ?? '');
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title'] ?? '');
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name'] ?? '');
    $company_address = mysqli_real_escape_string($conn, $_POST['company_address'] ?? '');
    $work_position = mysqli_real_escape_string($conn, $_POST['work_position'] ?? '');
    $is_course_related = mysqli_real_escape_string($conn, $_POST['is_course_related'] ?? '');
    $date_started = !empty($_POST['date_started']) ? $_POST['date_started'] : null;
    $is_current_job = mysqli_real_escape_string($conn, $_POST['is_current_job'] ?? '');
    $date_ended = !empty($_POST['date_ended']) ? $_POST['date_ended'] : null;
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type'] ?? '');
    $additional_info = mysqli_real_escape_string($conn, $_POST['additional_info'] ?? '');
    
    // Basic validation
    if (empty($student_number)) $errors[] = "Student number is required.";
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($course)) $errors[] = "Course is required.";
    if (empty($year_graduated)) $errors[] = "Year graduated is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($employment_status)) $errors[] = "Employment status is required.";
    
    // Check if student_number already exists
    $check_query = "SELECT student_number FROM alumni WHERE student_number = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $student_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $errors[] = "Student number already exists.";
    }
    
    // Handle file upload if needed
    $document_upload = null;
    if (isset($_FILES['document_upload']) && $_FILES['document_upload']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['document_upload']['name'], PATHINFO_EXTENSION);
        $filename = $student_number . '.' . $file_extension;
        $target_file = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['document_upload']['tmp_name'], $target_file)) {
            $document_upload = 'uploads/' . $filename;
        } else {
            $errors[] = "Failed to upload document.";
        }
    }
    
    // Generate a random password
    $random_password = substr(md5(rand()), 0, 8);
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
    
    // Create a dummy signature data
    $signature_data = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASwAAACWCAYAAABkW7XSAAAAAXNSR0IArs4c6QAABGJJREFUeF7t3UFynDAQRlGPVjaZy2bWc9nMJquUPQ6UUG3ZElhNx+8uExu1uvrec3E5OfZ+5IMAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQIAAAQIECBAgQGBygedpmp6maXqepuk0eD9aJ3C3wCyoy+UfHbPAs7QeWVzzZyvCutt70A88ksDnvYFziup2/P0RP94YBQYRuCesH19sgRVXY4vKfCsua4wCgwnUCOtZXP/GNUarQx6nzBxnBaYR1vWHX0uGNs1TsJEgUCSsW2ldz0dZwop1lU0/l02/FQ3+YgKPLnArrI9WtmreGgqs7d/XV4o16+9Hf1jbEyhOWCWnGWo8apaP5q19KfwZgYkEahXWPGQpPNVQs7BWrZ8IwnYHFKgZVqvhSn/vvA0+4MhEbRKoHZZA79+z2rRNTyGwg0CLsEoW04bfUvB2mKRPHUWgVVg1Frm6DKsoBrvMWaBVWKWzrNLFPHOOxj6wQMuwSsLKcbA9BHvkQh48rJbCyvEvxm4JwEYIvAvUCmtPMDMs+yQwiEC0sHYZeJTNG4vAoAK1w5qPT1lMD/bMbDr7mmNmAq3CavVaWWaGNbOR20/rsNoE9TOdLML1ZMLfmUCLsFqEtXm168757/L1tc5b7QJ04Pu8l9olLBFWr7MElYZY/HXZWevS1whMRqB2WL2CWprdjXMHn1Gm7URgA4HaYe1xmrFE4+EJx1rJbzC+Pm9qGdauByae56vvZOULpMfYtBRWj+BaLNL1MV42+ohCI4ZVY8G9ZCG+dEH+1gzLCceBBYYMK/M8eTUOG5acLKh9l8KAcTn0EQQih3W9LLtUWNf7uUQWnXQ0mXJBJSyvExPIGlbNRHK83fMx/L2JXCfBHNdq4T1ygayDUHqK4Pqznv/5v6frV2M+CiuzgK9PKDA0rBovnkXOYcjbPYs5vwvvKzKb4wBNJ6ySgSjdxzVr2Xe2W2jvfrvwvYQ09y1LWPIaRUCBI4S1ds707KeZdWdnbddh/9G3ec5jl1PBhX97I4+OAN/tHCGsjEPR44X5vdd7i98nf8/MYZS/d304WkDD4BohrFHOVEVYsNu6z+ULssfXj6xhRZzHHE3l2QYiDaIxCQTbU5awetyK0B+8nG+nt1RoMx3YgQe36/AihJXpH+7O/FV9nT2eM3ZH4H6xPsewhrvhYE8P64kElh5ycv1fD4v7P/6eWCDKIcx+QjBQ/AkCBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAj8AiEsPq936mo9AAAAAElFTkSuQmCC';
    $date_signed = date('Y-m-d H:i:s');
    
    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO alumni (
            student_number, first_name, middle_name, middle_initial, gender, last_name, course, 
            year_graduated, email, phone, address, job_title, company_name, company_address, 
            work_position, is_course_related, employment_status, date_started, is_current_job, 
            date_ended, document_type, document_upload, additional_info, signature_data, password, date_signed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssss", 
            $student_number, $first_name, $middle_name, $middle_initial, $gender, $last_name, $course, 
            $year_graduated, $email, $phone, $address, $job_title, $company_name, $company_address, 
            $work_position, $is_course_related, $employment_status, $date_started, $is_current_job, 
            $date_ended, $document_type, $document_upload, $additional_info, $signature_data, $hashed_password, $date_signed
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $alumni_id = mysqli_insert_id($conn);
            $success = true;
            
            // Redirect to view page after successful creation
            header("Location: view_alumni.php?id=$alumni_id&success=created");
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_stmt_error($stmt);
        }
    }
}

// Include the navbar
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Alumni - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        body { background: #ffffff; color: #333; min-height: 100vh; position: relative; overflow-x: hidden; }
        #particles-js { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: 0; }
        .content-wrapper { position: relative; z-index: 1; padding: 20px; }
        .card-header { background: #800000; color: #fff; border-radius: 15px 15px 0 0 !important; }
        .text-maroon { color: #800000; }
        .btn-maroon { background-color: #800000; color: white; }
        .btn-maroon:hover { background-color: #600000; color: white; }
        .glass-effect { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .section-title { color: #800000; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin-bottom: 20px; }
        .form-label { font-weight: 500; }
        .required-field::after { content: "*"; color: red; margin-left: 4px; }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="content-wrapper">
        <div class="container py-4">
            <div class="card glass-effect">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Alumni</h4>
                        <a href="alumni_list.php" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        <!-- Personal Information -->
                        <h5 class="section-title">Personal Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="student_number" class="form-label required-field">Student Number</label>
                                <input type="text" class="form-control" id="student_number" name="student_number" required>
                            </div>
                            <div class="col-md-4">
                                <label for="first_name" class="form-label required-field">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label required-field">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                    <option value="Prefer not to say">Prefer not to say</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="course" class="form-label required-field">Course</label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course); ?>"><?php echo htmlspecialchars($course); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="year_graduated" class="form-label required-field">Year Graduated</label>
                                <select class="form-select" id="year_graduated" name="year_graduated" required>
                                    <option value="">Select Year</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($year = $current_year; $year >= ($current_year - 30); $year--) {
                                        echo "<option value=\"$year\">$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <h5 class="section-title">Contact Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="email" class="form-label required-field">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-4">
                                <label for="phone" class="form-label required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-4">
                                <label for="address" class="form-label required-field">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                        </div>
                        
                        <!-- Employment Information -->
                        <h5 class="section-title">Employment Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="employment_status" class="form-label required-field">Employment Status</label>
                                <select class="form-select" id="employment_status" name="employment_status" required>
                                    <option value="">Select Status</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Self-employed">Self-employed</option>
                                    <option value="Unemployed">Unemployed</option>
                                </select>
                            </div>
                            <div class="col-md-4 employment-field">
                                <label for="job_title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="job_title" name="job_title">
                            </div>
                            <div class="col-md-4 employment-field">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name">
                            </div>
                            <div class="col-md-4 employment-field">
                                <label for="document_type" class="form-label required-field">Document Type</label>
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="">Select Document Type</option>
                                    <option value="Alumni ID">Alumni ID</option>
                                    <option value="Student ID">Student ID</option>
                                    <option value="Government ID">Government ID</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="additional_info" class="form-label">Additional Information</label>
                                <textarea class="form-control" id="additional_info" name="additional_info" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-maroon">Save Alumni Record</button>
                        </div>
                    </form>
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
            
            // Show/hide employment fields based on employment status
            const employmentStatus = document.getElementById('employment_status');
            const employmentFields = document.querySelectorAll('.employment-field');
            
            function toggleEmploymentFields() {
                const isEmployed = employmentStatus.value !== 'Unemployed' && employmentStatus.value !== '';
                
                employmentFields.forEach(field => {
                    if (field.querySelector('#document_type')) return; // Skip document type field
                    field.style.display = isEmployed ? 'block' : 'none';
                });
                
                if (!isEmployed) {
                    employmentFields.forEach(field => {
                        if (field.querySelector('#document_type')) return; // Skip document type field
                        const input = field.querySelector('input, select');
                        if (input) input.value = '';
                    });
                }
            }
            
            employmentStatus.addEventListener('change', toggleEmploymentFields);
            
            // Initialize fields on page load
            toggleEmploymentFields();
        });
    </script>
</body>
</html> 