<?php
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
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
    $document_type = mysqli_real_escape_string($conn, $_POST['document_type']);
    $additional_info = mysqli_real_escape_string($conn, $_POST['additional_info']);
    $signature_data = mysqli_real_escape_string($conn, $_POST['signature_data']);

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

    $sql = "INSERT INTO alumni (full_name, course, year_graduated, email, phone, address, 
            job_title, company_name, company_address, work_position, is_course_related, 
            employment_status, date_started, is_current_job, date_ended, document_type, 
            document_upload, additional_info, signature_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssssssssssssss", 
        $full_name, $course, $year_graduated, $email, $phone, $address,
        $job_title, $company_name, $company_address, $work_position, $is_course_related,
        $employment_status, $date_started, $is_current_job, $date_ended, $document_type,
        $document_upload, $additional_info, $signature_data);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: success.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Alumni Tracer System</title>
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
                        <h2 class="text-center text-maroon mb-4">Alumni Registration Form</h2>
                        <form id="registrationForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <!-- Personal Information -->
                            <div class="section-title mb-4">
                                <h4 class="text-maroon">Personal Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course</label>
                                    <input type="text" class="form-control" name="course" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Year Graduated</label>
                                    <input type="number" class="form-control" name="year_graduated" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Complete Address</label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                            </div>

                            <!-- Employment Information -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Employment Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Current Job Title</label>
                                    <input type="text" class="form-control" name="job_title" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Company Address</label>
                                    <input type="text" class="form-control" name="company_address" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Position/Level</label>
                                    <input type="text" class="form-control" name="work_position" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Is Your Job Related to Your Course?</label>
                                    <select class="form-select" name="is_course_related" required>
                                        <option value="">Select...</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employment Status</label>
                                    <select class="form-select" name="employment_status" required>
                                        <option value="">Select...</option>
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Self-employed">Self-employed</option>
                                        <option value="Unemployed">Unemployed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Started</label>
                                    <input type="date" class="form-control" name="date_started" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Is this your current job?</label>
                                    <select class="form-select" name="is_current_job" required>
                                        <option value="">Select...</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Ended (if not current job)</label>
                                    <input type="date" class="form-control" name="date_ended">
                                </div>
                            </div>

                            <!-- ID Verification -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">ID Verification</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Document Type</label>
                                    <select class="form-select" name="document_type" required>
                                        <option value="">Select...</option>
                                        <option value="ID Card">ID Card</option>
                                        <option value="Passport">Passport</option>
                                        <option value="Driver's License">Driver's License</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Upload ID Document</label>
                                    <input type="file" class="form-control" name="document_upload" accept="image/*" required>
                                </div>
                                <div class="col-12">
                                    <div id="ocr-preview" class="mt-3"></div>
                                    <div id="ocr-result" class="mt-3"></div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Additional Information</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Additional Information</label>
                                    <textarea class="form-control" name="additional_info" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Signature -->
                            <div class="section-title mb-4 mt-5">
                                <h4 class="text-maroon">Signature</h4>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <canvas id="signatureCanvas" class="border rounded"></canvas>
                                    <input type="hidden" name="signature_data" id="signature_data">
                                    <button type="button" class="btn btn-outline-maroon mt-2" onclick="clearSignature()">Clear Signature</button>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-maroon btn-lg">Submit Registration</button>
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

        // Signature pad functionality
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        canvas.width = canvas.offsetWidth;
        canvas.height = 200;

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function draw(e) {
            if (!isDrawing) return;
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.strokeStyle = '#800000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
            [lastX, lastY] = [e.offsetX, e.offsetY];
        }

        function stopDrawing() {
            isDrawing = false;
            document.getElementById('signature_data').value = canvas.toDataURL();
        }

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature_data').value = '';
        }

        // OCR functionality
        document.querySelector('input[name="document_upload"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const preview = document.getElementById('ocr-preview');
                        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid">`;
                        
                        // Perform OCR
                        Tesseract.recognize(
                            e.target.result,
                            'eng',
                            {
                                logger: m => {
                                    if (m.status === 'recognizing text') {
                                        document.getElementById('ocr-result').innerHTML = 
                                            `<div class="alert alert-info">Processing... ${Math.round(m.progress * 100)}%</div>`;
                                    }
                                }
                            }
                        ).then(({ data: { text } }) => {
                            document.getElementById('ocr-result').innerHTML = 
                                `<div class="alert alert-success">OCR Result: ${text}</div>`;
                        });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
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