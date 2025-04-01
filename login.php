<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Rate limiting function
function checkRateLimit($email) {
    $attempts_file = "login_attempts.json";
    $max_attempts = 5;
    $lockout_time = 900; // 15 minutes in seconds
    
    // Load existing attempts
    $attempts = [];
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    }
    
    // Clean up old attempts
    $now = time();
    foreach ($attempts as $ip => $data) {
        if ($now - $data['timestamp'] > $lockout_time) {
            unset($attempts[$ip]);
        }
    }
    
    // Get client IP
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Check if IP is locked out
    if (isset($attempts[$ip])) {
        if ($attempts[$ip]['count'] >= $max_attempts) {
            $time_remaining = $lockout_time - ($now - $attempts[$ip]['timestamp']);
            if ($time_remaining > 0) {
                return [false, ceil($time_remaining / 60)];
            }
            // Reset attempts if lockout time has passed
            unset($attempts[$ip]);
        }
    }
    
    // Save attempts data
    file_put_contents($attempts_file, json_encode($attempts));
    return [true, 0];
}

// Record failed attempt
function recordFailedAttempt($email) {
    $attempts_file = "login_attempts.json";
    $attempts = [];
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!isset($attempts[$ip])) {
        $attempts[$ip] = ['count' => 0, 'timestamp' => time()];
    }
    
    $attempts[$ip]['count']++;
    $attempts[$ip]['timestamp'] = time();
    
    file_put_contents($attempts_file, json_encode($attempts));
}

// Reset attempts on successful login
function resetAttempts() {
    $attempts_file = "login_attempts.json";
    $attempts = [];
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($attempts[$ip])) {
            unset($attempts[$ip]);
            file_put_contents($attempts_file, json_encode($attempts));
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Check rate limit
    list($allowed, $minutes_remaining) = checkRateLimit($email);
    if (!$allowed) {
        $error = "Too many failed attempts. Please try again in {$minutes_remaining} minutes.";
    } else {
        // Debug: Check if we're getting the form data
        error_log("Login attempt - Email: " . $email);

        $sql = "SELECT * FROM alumni WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (!mysqli_stmt_execute($stmt)) {
            die("Execute failed: " . mysqli_error($conn));
        }
        
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Debug: Check if password verification is working
            error_log("Found user with email: " . $email);
            error_log("Stored password hash: " . $row['password']);
            
            // Verify the password hash
            if (password_verify($password, $row['password'])) {
                error_log("Password verified successfully");
                
                // Reset failed attempts on successful login
                resetAttempts();
                
                // Debug: Log the row data
                error_log("User data from database:");
                error_log(print_r($row, true));
                
                // Set session variables with correct column names
                $_SESSION['alumni_id'] = $row['alumni_id'];
                $_SESSION['alumni_name'] = $row['first_name'] . ' ' . $row['last_name'];
                $_SESSION['student_number'] = $row['student_number'];
                
                // Debug: Log session variables
                error_log("Session variables set:");
                error_log("alumni_id: " . $_SESSION['alumni_id']);
                error_log("alumni_name: " . $_SESSION['alumni_name']);
                error_log("student_number: " . $_SESSION['student_number']);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                error_log("Password verification failed");
                recordFailedAttempt($email);
                $error = "Invalid email or password";
            }
        } else {
            error_log("No user found with email: " . $email);
            recordFailedAttempt($email);
            $error = "Invalid email or password";
        }
    }
}

// Debug: Check session status
if (isset($_SESSION['alumni_id'])) {
    error_log("Session exists - ID: " . $_SESSION['alumni_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6">
                <div class="card glass-effect">
                    <div class="card-body">
                        <h2 class="text-center text-maroon mb-4">Alumni Login</h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" required>
                                    <div class="invalid-feedback">Please enter your email address.</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <div class="invalid-feedback">Please enter your password.</div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-maroon btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                                <a href="register.php" class="btn btn-outline-maroon">
                                    <i class="fas fa-user-plus me-2"></i>Register as New Alumni
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
    </script>
</body>
</html> 