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
$sql = "SELECT *, CONCAT(first_name, ' ', last_name) AS full_name FROM alumni WHERE alumni_id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($conn));
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
if (!mysqli_stmt_execute($stmt)) {
    error_log("Execute failed: " . mysqli_error($conn));
    die("Database error: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    error_log("Get result failed: " . mysqli_error($conn));
    die("Database error: " . mysqli_error($conn));
}

$alumni = mysqli_fetch_assoc($result);

if (!$alumni) {
    error_log("No alumni found with ID: " . $id);
    header("Location: index.php");
    exit();
}

// Include the navbar
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Alumni - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        .info-section {
            margin-bottom: 2rem;
        }
        .info-item {
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-weight: bold;
            color: #800000;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1.1rem;
        }
        .section-divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 2rem 0;
        }
        .signature-container {
            max-width: 400px;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        .document-preview {
            max-width: 300px;
            max-height: 200px;
            object-fit: contain;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card glass-effect">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="text-maroon mb-0">
                                <i class="fas fa-user-graduate me-2"></i>
                                Alumni Details
                            </h2>
                            <div>
                                <a href="edit.php?id=<?php echo $alumni['alumni_id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="index.php" class="btn btn-outline-maroon ms-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        
                        <!-- Personal Information -->
                        <div class="info-section">
                            <h4 class="section-title">Personal Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Student Number</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['student_number']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Full Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['full_name']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Gender</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['gender'] ?? 'Not specified'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Course</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['course']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Year Graduated</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['year_graduated']); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value">
                                            <a href="mailto:<?php echo htmlspecialchars($alumni['email']); ?>">
                                                <?php echo htmlspecialchars($alumni['email']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value">
                                            <a href="tel:<?php echo htmlspecialchars($alumni['phone']); ?>">
                                                <?php echo htmlspecialchars($alumni['phone']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['address']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-divider"></div>
                        
                        <!-- Employment Information -->
                        <div class="info-section">
                            <h4 class="section-title">Employment Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Employment Status</div>
                                        <div class="info-value">
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
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($alumni['employment_status'] != 'Unemployed'): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Job Title</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['job_title'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Company Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['company_name'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Company Address</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['company_address'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Work Position/Level</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['work_position'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Is Job Related to Course?</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['is_course_related'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Date Started</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['date_started'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Is Current Job?</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['is_current_job'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <?php if ($alumni['is_current_job'] == 'No'): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Date Ended</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['date_ended'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="section-divider"></div>
                        
                        <!-- Additional Information -->
                        <div class="info-section">
                            <h4 class="section-title">Additional Information</h4>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-item">
                                        <div class="info-label">Additional Notes</div>
                                        <div class="info-value">
                                            <?php echo !empty($alumni['additional_info']) ? 
                                                nl2br(htmlspecialchars($alumni['additional_info'])) : 'No additional information provided.'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-divider"></div>
                        
                        <!-- Document Information -->
                        <div class="info-section">
                            <h4 class="section-title">Verification Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Document Type</div>
                                        <div class="info-value"><?php echo htmlspecialchars($alumni['document_type']); ?></div>
                                    </div>
                                </div>
                                <?php if (!empty($alumni['document_upload'])): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Uploaded Document</div>
                                        <div class="info-value">
                                            <a href="../<?php echo htmlspecialchars($alumni['document_upload']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-alt me-1"></i> View Document
                                            </a>
                                            <?php if (strpos($alumni['document_upload'], '.jpg') !== false || 
                                                      strpos($alumni['document_upload'], '.jpeg') !== false ||
                                                      strpos($alumni['document_upload'], '.png') !== false): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo htmlspecialchars($alumni['document_upload']); ?>" class="document-preview" alt="Document Preview">
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Registration Date</div>
                                        <div class="info-value">
                                            <?php echo date('F j, Y, g:i a', strtotime($alumni['date_signed'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($alumni['signature_data'])): ?>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <div class="info-label">Digital Signature</div>
                                        <div class="info-value">
                                            <div class="signature-container">
                                                <img src="<?php echo htmlspecialchars($alumni['signature_data']); ?>" alt="Digital Signature" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
    </script>
</body>
</html> 