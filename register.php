<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    $student_number = mysqli_real_escape_string($conn, $_POST['student_number']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $middle_initial = mysqli_real_escape_string($conn, $_POST['middle_initial']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
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
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
    $additional_info = mysqli_real_escape_string($conn, $_POST['additional_info']);
    $signature_data = mysqli_real_escape_string($conn, $_POST['signature_data']);
    $date_signed = date('Y-m-d H:i:s'); // Add current date and time
    
    // Enhanced password validation
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Password validation rules
    $uppercase = preg_match('/[A-Z]/', $password);
    $lowercase = preg_match('/[a-z]/', $password);
    $number = preg_match('/[0-9]/', $password);
    $special_char = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif (!$uppercase || !$lowercase || !$number || !$special_char) {
        $error = "Password must include at least one uppercase letter, one lowercase letter, one number, and one special character!";
    } else {
        // Hash password with increased cost factor
        $options = ['cost' => 12];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, $options);

        // Handle file upload
        $document_upload = '';
        if (isset($_FILES['document_upload']) && $_FILES['document_upload']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = strtolower(pathinfo($_FILES["document_upload"]["name"], PATHINFO_EXTENSION));
            $new_filename = $student_number . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            // Delete existing file if it exists
            if (file_exists($target_file)) {
                unlink($target_file);
            }

            if (move_uploaded_file($_FILES["document_upload"]["tmp_name"], $target_file)) {
                $document_upload = $target_file;
            }
        }

        // Set employment_status based on employment_check
        $employment_status = $_POST['employment_check'] ?? 'Unemployed';

        $sql = "INSERT INTO alumni (student_number, first_name, middle_name, middle_initial, gender, last_name, course, year_graduated, 
                email, phone, address, job_title, company_name, company_address, work_position, is_course_related, 
                employment_status, date_started, is_current_job, date_ended, document_type, document_upload, 
                additional_info, signature_data, password, date_signed, salary, industry) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssss", 
            $student_number, $first_name, $middle_name, $middle_initial, $gender, $last_name, $course, $year_graduated, 
            $email, $phone, $address, $job_title, $company_name, $company_address, $work_position, 
            $is_course_related, $employment_status, $date_started, $is_current_job, $date_ended, 
            $document_type, $document_upload, $additional_info, $signature_data, $hashed_password, $date_signed);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: success.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Registration - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@2.0.0/dist/tf.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card glass-effect">
                    <div class="card-body">
                        <h2 class="text-center text-maroon mb-4">Alumni Registration</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <!-- Personal Information -->
                            <div class="section-title mb-4">
                                <h4 class="text-maroon">Personal Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Student Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                        <input type="text" class="form-control" name="student_number" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Middle Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="middle_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Middle Initial</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="middle_initial" maxlength="1">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                        <select class="form-select" name="gender" required>
                                            <option value="">Select Gender...</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                            <option value="Prefer not to say">Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name (Maiden Name)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="last_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                        <select class="form-select" name="course" required>
                                            <option value="">Select Course...</option>
                                            <?php
                                            $courses_query = "SELECT course_name FROM courses ORDER BY course_name";
                                            $courses_result = mysqli_query($conn, $courses_query);
                                            while ($course = mysqli_fetch_assoc($courses_result)) {
                                                echo "<option value='" . htmlspecialchars($course['course_name']) . "'>" . 
                                                     htmlspecialchars($course['course_name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Year Graduated</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="number" class="form-control" name="year_graduated" 
                                               min="1900" max="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="tel" class="form-control" name="phone" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Complete Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <input type="text" class="form-control" name="address" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Account Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" name="password" id="password"
                                               minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                               required oninput="checkPasswordStrength(this.value)">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-requirements small text-muted mt-1">
                                        <div id="length-check"><i class="fas fa-times"></i> At least 8 characters</div>
                                        <div id="uppercase-check"><i class="fas fa-times"></i> At least one uppercase letter</div>
                                        <div id="lowercase-check"><i class="fas fa-times"></i> At least one lowercase letter</div>
                                        <div id="number-check"><i class="fas fa-times"></i> At least one number</div>
                                        <div id="special-check"><i class="fas fa-times"></i> At least one special character</div>
                                    </div>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" name="confirm_password" id="confirm_password"
                                               minlength="8" required oninput="checkPasswordMatch()">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match" class="small mt-1"></div>
                                </div>
                            </div>

                            <!-- Employment Status Check -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Employment Status</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="employment_check" 
                                               id="employed" value="employed" onchange="toggleEmploymentSection()" required>
                                        <label class="form-check-label" for="employed">Employed</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="employment_check" 
                                               id="unemployed" value="unemployed" onchange="toggleEmploymentSection()" required>
                                        <label class="form-check-label" for="unemployed">Unemployed</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Information -->
                            <div id="employmentSection" style="display: none;">
                                <div class="section-title mb-4 mt-5">
                                    <h4 class="text-maroon">Employment Information</h4>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Current Job Title</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                            <input type="text" class="form-control" name="job_title" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Company Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                            <input type="text" class="form-control" name="company_name" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Company Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                                            <input type="text" class="form-control" name="company_address" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Work Position/Level</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                            <input type="text" class="form-control" name="work_position" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Is Your Job Related to Your Course?</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                            <select class="form-select" name="is_course_related" required>
                                                <option value="">Select...</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date Started</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                            <input type="date" class="form-control" name="date_started" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Is this your current job?</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                            <select class="form-select" name="is_current_job" required>
                                                <option value="">Select...</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date Ended (if not current job)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                                            <input type="date" class="form-control" name="date_ended">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ID Verification -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">ID Verification</h4>
                                <p class="text-muted small">Please upload a clear photo of your ID for verification</p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Document Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <select class="form-select" name="document_type" required>
                                            <option value="">Select Document Type...</option>
                                            <option value="Alumni ID">Alumni ID</option>
                                            <option value="Student ID">Student ID</option>
                                            <option value="Government ID">Government ID</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Upload Document</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                        <input type="file" class="form-control" id="document_upload" 
                                               name="document_upload" accept="image/*" required 
                                               onchange="previewAndVerifyID(this)">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card verification-card mt-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h5 class="card-title">ID Preview</h5>
                                                    <img id="idPreview" src="" alt="ID Preview" class="img-fluid d-none mb-3" style="max-height: 200px; width: auto;">
                                                    <div id="verification-status" class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i> Please upload your ID for verification
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5 class="card-title">Verification Details</h5>
                                                    <div id="verification-message"></div>
                                                    <div id="verification-progress" class="d-none">
                                                        <div class="progress mb-3">
                                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-maroon" 
                                                                 role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Additional Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Additional Information</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        <textarea class="form-control" name="additional_info" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Signature -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Digital Signature</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="signature-container">
                                        <canvas id="signatureCanvas" class="signature-pad"></canvas>
                                        <div class="signature-controls mt-2">
                                            <button type="button" class="btn btn-outline-maroon btn-sm" id="clearSignature">
                                                <i class="fas fa-eraser me-1"></i>Clear
                                            </button>
                                        </div>
                                        <input type="hidden" name="signature_data" id="signatureData">
                                        <div class="mt-2 text-muted">
                                            <small>By signing this form, you agree to the terms and conditions.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-maroon btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Registration
                                </button>
                                <a href="index.php" class="btn btn-outline-maroon btn-lg ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Home
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
                color: { value: '#8B0000' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: false },
                size: { value: 3, random: true },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#8B0000',
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

        // Signature Pad
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Set canvas size
        canvas.width = canvas.offsetWidth;
        canvas.height = 200;

        // Drawing functions
        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function draw(e) {
            if (!isDrawing) return;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = '#8B0000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function stopDrawing() {
            isDrawing = false;
            ctx.beginPath();
            document.getElementById('signatureData').value = canvas.toDataURL();
        }

        // Event listeners for signature
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Clear signature
        document.getElementById('clearSignature').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signatureData').value = '';
        });

        // Load face-api.js models
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights'),
            faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights'),
            faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights')
        ]).then(() => {
            console.log('Models loaded successfully');
        }).catch(err => console.error('Error loading models:', err));

        // ID Verification
        let verificationStatus = false;
        
        function previewAndVerifyID(input) {
            const preview = document.getElementById('idPreview');
            const verificationStatus = document.getElementById('verification-status');
            const verificationMessage = document.getElementById('verification-message');
            const progressBar = document.getElementById('verification-progress');
            const progress = progressBar.querySelector('.progress-bar');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Show preview
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                    
                    // Start verification
                    verifyID(preview);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function verifyID(imageElement) {
            const verificationStatus = document.getElementById('verification-status');
            const verificationMessage = document.getElementById('verification-message');
            const progressBar = document.getElementById('verification-progress');
            const progress = progressBar.querySelector('.progress-bar');
            
            const firstName = document.querySelector('input[name="first_name"]').value.trim();
            const lastName = document.querySelector('input[name="last_name"]').value.trim();
            
            if (!firstName || !lastName) {
                verificationMessage.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Please fill in your first name and last name before uploading ID.
                    </div>`;
                return;
            }

            try {
                // Show progress
                progressBar.classList.remove('d-none');
                verificationStatus.className = 'alert alert-info';
                verificationStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying your ID...';
                progress.style.width = '30%';

                // Create form data
                const formData = new FormData();
                formData.append('first_name', firstName);
                formData.append('last_name', lastName);
                formData.append('document_upload', document.getElementById('document_upload').files[0]);

                // Send to verify.php
                const response = await $.ajax({
                    url: 'verify.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                progress.style.width = Math.min(percent, 90) + '%';
                            }
                        }, false);
                        return xhr;
                    }
                });

                progress.style.width = '100%';

                if (response.success) {
                    if (response.verified) {
                        verificationStatus.className = 'alert alert-success';
                        verificationStatus.innerHTML = '<i class="fas fa-check-circle"></i> ' + response.message;
                        verificationMessage.innerHTML = `
                            <div class="alert alert-success">
                                <h6><i class="fas fa-check-circle"></i> Verification Successful</h6>
                                <ul class="mb-0">
                                    <li>Name matched with ID</li>
                                    <li>Document verified for: ${firstName} ${lastName}</li>
                                </ul>
                            </div>`;
                        window.verificationStatus = true;
                    } else {
                        verificationStatus.className = 'alert alert-danger';
                        verificationStatus.innerHTML = '<i class="fas fa-times-circle"></i> ' + response.message;
                        verificationMessage.innerHTML = `
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-times-circle"></i> Verification Failed</h6>
                                <p>The name on the ID does not match the provided name.</p>
                                <p>Please ensure:</p>
                                <ul class="mb-0">
                                    <li>The name on the ID matches exactly what you entered</li>
                                    <li>The ID photo is clear and readable</li>
                                    <li>There is no glare or reflection on the ID</li>
                                </ul>
                            </div>`;
                        window.verificationStatus = false;
                    }
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error('Verification error:', error);
                verificationStatus.className = 'alert alert-danger';
                verificationStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> Verification Error';
                verificationMessage.innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-circle"></i> Error During Verification</h6>
                        <p>${error.message || 'An error occurred while verifying your ID. Please try again:'}</p>
                        <ul class="mb-0">
                            <li>Ensure the image is clear and well-lit</li>
                            <li>Try using a JPG or PNG format</li>
                            <li>Make sure the file size is not too large (max 5MB recommended)</li>
                            <li>Try taking a new photo of your ID in better lighting</li>
                        </ul>
                    </div>`;
                window.verificationStatus = false;
            } finally {
                progressBar.classList.add('d-none');
            }
        }

        // Update form validation to use the global verification status
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!window.verificationStatus) {
                e.preventDefault();
                const verificationCard = document.querySelector('.verification-card');
                verificationCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                verificationCard.classList.add('border-danger');
                setTimeout(() => verificationCard.classList.remove('border-danger'), 2000);
                alert('Please complete ID verification before submitting the form.');
            }
        });

        // Add file size validation
        document.getElementById('document_upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file.size > maxSize) {
                alert('File is too large. Please select an image under 5MB.');
                this.value = ''; // Clear the file input
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG or PNG).');
                this.value = '';
                return;
            }
            
            previewAndVerifyID(this);
        });

        // Password visibility toggle
        function togglePassword(inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
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

        // Add this function for employment section toggle
        function toggleEmploymentSection() {
            const employmentSection = document.getElementById('employmentSection');
            const employmentFields = employmentSection.querySelectorAll('input, select');
            const isEmployed = document.getElementById('employed').checked;
            
            employmentSection.style.display = isEmployed ? 'block' : 'none';
            
            // Toggle required attribute for employment fields
            employmentFields.forEach(field => {
                field.required = isEmployed;
            });
            
            // Set employment_status based on radio selection
            if (!isEmployed) {
                const hiddenStatus = document.createElement('input');
                hiddenStatus.type = 'hidden';
                hiddenStatus.name = 'employment_status';
                hiddenStatus.value = 'Unemployed';
                document.querySelector('form').appendChild(hiddenStatus);
            }
        }

        // Add this to your existing JavaScript
        function checkPasswordStrength(password) {
            const length = password.length >= 8;
            const uppercase = /[A-Z]/.test(password);
            const lowercase = /[a-z]/.test(password);
            const number = /[0-9]/.test(password);
            const special = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update check marks
            document.getElementById('length-check').innerHTML = 
                `<i class="fas fa-${length ? 'check text-success' : 'times text-danger'}"></i> At least 8 characters`;
            document.getElementById('uppercase-check').innerHTML = 
                `<i class="fas fa-${uppercase ? 'check text-success' : 'times text-danger'}"></i> At least one uppercase letter`;
            document.getElementById('lowercase-check').innerHTML = 
                `<i class="fas fa-${lowercase ? 'check text-success' : 'times text-danger'}"></i> At least one lowercase letter`;
            document.getElementById('number-check').innerHTML = 
                `<i class="fas fa-${number ? 'check text-success' : 'times text-danger'}"></i> At least one number`;
            document.getElementById('special-check').innerHTML = 
                `<i class="fas fa-${special ? 'check text-success' : 'times text-danger'}"></i> At least one special character`;
            
            // Calculate strength
            let strength = 0;
            if (length) strength += 20;
            if (uppercase) strength += 20;
            if (lowercase) strength += 20;
            if (number) strength += 20;
            if (special) strength += 20;
            
            // Update progress bar
            const strengthBar = document.getElementById('password-strength');
            strengthBar.style.width = strength + '%';
            
            if (strength <= 40) {
                strengthBar.className = 'progress-bar bg-danger';
            } else if (strength <= 80) {
                strengthBar.className = 'progress-bar bg-warning';
            } else {
                strengthBar.className = 'progress-bar bg-success';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
            } else if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check text-success"></i> Passwords match';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times text-danger"></i> Passwords do not match';
            }
        }
    </script>

    <style>
    .verification-card {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .verification-card.border-danger {
        border-color: #dc3545;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
    }

    .progress {
        height: 10px;
        border-radius: 5px;
    }

    .bg-maroon {
        background-color: #800000 !important;
    }

    #idPreview {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 5px;
    }
    </style>
</body>
</html> 