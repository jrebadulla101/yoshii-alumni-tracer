<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle_name']);
    $middle_initial = mysqli_real_escape_string($conn, $_POST['middle_initial']);
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
    
    // Password handling
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Handle file upload
        $document_upload = '';
        if (isset($_FILES['document_upload']) && $_FILES['document_upload']['error'] == 0) {
            $target_dir = "uploads/";
            $file_extension = strtolower(pathinfo($_FILES["document_upload"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["document_upload"]["tmp_name"], $target_file)) {
                $document_upload = $target_file;
            }
        }

        $sql = "INSERT INTO alumni (first_name, middle_name, middle_initial, last_name, course, year_graduated, 
                email, phone, address, job_title, company_name, company_address, work_position, is_course_related, 
                employment_status, date_started, is_current_job, date_ended, document_type, document_upload, 
                additional_info, signature_data, password, date_signed) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssss", 
            $first_name, $middle_name, $middle_initial, $last_name, $course, $year_graduated, 
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
    <script src='https://unpkg.com/tesseract.js@v2.1.1/dist/tesseract.min.js'></script>
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
                                    <label class="form-label">Last Name</label>
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
                                        <input type="password" class="form-control" name="password" 
                                               minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Password must be at least 8 characters long</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" name="confirm_password" 
                                               minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Information -->
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
                                    <label class="form-label">Employment Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                        <select class="form-select" name="employment_status" required>
                                            <option value="">Select...</option>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Self-employed">Self-employed</option>
                                            <option value="Unemployed">Unemployed</option>
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

                            <!-- ID Verification -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">ID Verification</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Document Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <select class="form-select" name="document_type" required>
                                            <option value="">Select...</option>
                                            <option value="Alumni ID">Alumni ID</option>
                                            <option value="Student ID">Student ID</option>
                                            <option value="Government ID">Government ID</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Upload ID Document</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                        <input type="file" class="form-control" name="document_upload" accept="image/*" required>
                                    </div>
                                    <div class="form-text">Upload a clear photo of your ID</div>
                                </div>
                                <div class="col-12">
                                    <div class="card verification-card">
                                        <div class="card-body">
                                            <h5 class="card-title">ID Verification Preview</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="preview-container">
                                                        <img id="idPreview" src="" alt="ID Preview" class="img-fluid d-none">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="verification-results">
                                                        <h6>Verification Results:</h6>
                                                        <div id="verificationStatus" class="alert alert-info">
                                                            Upload an ID to verify information
                                                        </div>
                                                        <div id="verificationDetails" class="mt-3"></div>
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

        // ID Verification
        const idInput = document.querySelector('input[name="document_upload"]');
        const idPreview = document.getElementById('idPreview');
        const verificationStatus = document.getElementById('verificationStatus');
        const verificationDetails = document.getElementById('verificationDetails');

        idInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    idPreview.src = e.target.result;
                    idPreview.classList.remove('d-none');
                    verifyID(e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        async function verifyID(imageData) {
            verificationStatus.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying ID...';
            
            try {
                const result = await Tesseract.recognize(imageData);
                const text = result.data.text.toLowerCase();
                
                // Get form values
                const firstName = document.querySelector('input[name="first_name"]').value.toLowerCase();
                const lastName = document.querySelector('input[name="last_name"]').value.toLowerCase();
                const course = document.querySelector('select[name="course"]').value.toLowerCase();
                const yearGraduated = document.querySelector('input[name="year_graduated"]').value;
                
                // Check for matches
                const nameMatch = text.includes(firstName) && text.includes(lastName);
                const courseMatch = text.includes(course);
                const yearMatch = text.includes(yearGraduated);
                
                let verificationHTML = '<ul class="list-unstyled">';
                verificationHTML += `<li><i class="fas ${nameMatch ? 'fa-check text-success' : 'fa-times text-danger'} me-2"></i>Name Match: ${nameMatch ? 'Verified' : 'Not Found'}</li>`;
                verificationHTML += `<li><i class="fas ${courseMatch ? 'fa-check text-success' : 'fa-times text-danger'} me-2"></i>Course Match: ${courseMatch ? 'Verified' : 'Not Found'}</li>`;
                verificationHTML += `<li><i class="fas ${yearMatch ? 'fa-check text-success' : 'fa-times text-danger'} me-2"></i>Year Match: ${yearMatch ? 'Verified' : 'Not Found'}</li>`;
                verificationHTML += '</ul>';
                
                verificationDetails.innerHTML = verificationHTML;
                
                if (nameMatch && courseMatch && yearMatch) {
                    verificationStatus.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>ID Verified Successfully';
                    verificationStatus.className = 'alert alert-success';
                } else {
                    verificationStatus.innerHTML = '<i class="fas fa-exclamation-triangle text-warning me-2"></i>Some Information Not Found';
                    verificationStatus.className = 'alert alert-warning';
                }
            } catch (error) {
                verificationStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-2"></i>Error Verifying ID';
                verificationStatus.className = 'alert alert-danger';
                console.error('Error:', error);
            }
        }

        // Add password toggle function
        function togglePassword(inputId) {
            const input = document.querySelector(`input[name="${inputId}"]`);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
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
</body>
</html> 