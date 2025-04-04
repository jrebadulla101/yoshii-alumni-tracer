<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8">
                <div class="card glass-effect text-center">
                    <div class="card-body">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle text-maroon" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-maroon mb-4">Registration Successful!</h2>
                        <p class="lead mb-4">Thank you for registering with the Alumni Tracer System. Your information has been successfully saved.</p>
                        <div class="d-grid gap-3">
                            <a href="index.php" class="btn btn-maroon btn-lg">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                            <a href="login.php" class="btn btn-outline-maroon btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                            </a>
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

        // Add animation to success icon
        document.querySelector('.success-icon').style.animation = 'bounceIn 1s ease-out';
    </script>
</body>
</html> 