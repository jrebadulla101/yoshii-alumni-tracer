<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
    .bg-maroon {
        background-color: #800000 !important;
    }
    
    .text-maroon {
        color: #800000 !important;
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
    
    footer {
        border-top: 1px solid rgba(255,255,255,0.2);
    }
    
    /* Add padding to body to prevent content from being hidden behind the fixed footer */
    body {
        padding-bottom: 100px;
    }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8">
                <div class="card glass-effect">
                    <div class="card-body text-center">
                        <img src="assets/images/earist-logo.png" alt="EARIST Logo" style="height: 80px;" class="mb-3">
                        <h1 class="display-4 mb-4 text-maroon">Alumni Tracer System</h1>
                        <p class="lead mb-4">Eulogio "Amang" Rodriguez Institute of Science and Technology</p>
                        <p class="mb-4">Track your career journey and stay connected with your alma mater</p>
                        <div class="d-grid gap-3">
                            <a href="register.php" class="btn btn-maroon btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register as Alumni
                            </a>
                            <a href="login.php" class="btn btn-outline-maroon btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-maroon text-white py-4 fixed-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <img src="assets/images/earist-logo.png" alt="EARIST Logo" class="me-3" style="height: 50px;">
                        <div>
                            <h5 class="mb-0">Alumni Tracer System</h5>
                            <p class="mb-0">Eulogio "Amang" Rodriguez Institute of Science and Technology</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <p class="mb-0">This web system is developed by EARIST CCS</p>
                    <a href="https://facebook.com/yoshiidesuu" class="text-white" target="_blank">
                        <i class="fab fa-facebook me-1"></i>facebook.com/yoshiidesuu
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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