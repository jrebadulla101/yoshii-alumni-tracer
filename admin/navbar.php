<?php
// Prevent direct output by moving all HTML to after session checks
if (!isset($conn)) {
    die("Database connection not available");
}

// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-maroon">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-graduation-cap me-2"></i>
            Alumni Tracer Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Alumni Management -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['alumni_list.php', 'add_alumni.php']) ? 'active' : ''; ?>" 
                       href="#" 
                       id="alumniDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="fas fa-user-graduate me-1"></i> Alumni
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="alumniDropdown">
                        <li>
                            <a class="dropdown-item" href="alumni_list.php">
                                <i class="fas fa-list me-2"></i> View All Alumni
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="add_alumni.php">
                                <i class="fas fa-user-plus me-2"></i> Add New Alumni
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Analytics -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['analytics.php', 'analytics-detailed.php']) ? 'active' : ''; ?>" 
                       href="#" 
                       id="analyticsDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="fas fa-chart-line me-1"></i> Analytics
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="analyticsDropdown">
                        <li>
                            <a class="dropdown-item" href="analytics.php">
                                <i class="fas fa-chart-pie me-2"></i> Quick Statistics
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=employment">
                                <i class="fas fa-briefcase me-2"></i> Employment Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=course">
                                <i class="fas fa-graduation-cap me-2"></i> Course Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=year">
                                <i class="fas fa-calendar-alt me-2"></i> Year Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=salary">
                                <i class="fas fa-money-bill-wave me-2"></i> Salary Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=company">
                                <i class="fas fa-building me-2"></i> Company Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=gender">
                                <i class="fas fa-venus-mars me-2"></i> Gender Analysis
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="analytics-detailed.php?view=comparison">
                                <i class="fas fa-balance-scale me-2"></i> Comparison Reports
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Reports & Exports -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['reports.php', 'export.php']) ? 'active' : ''; ?>" 
                       href="#" 
                       id="reportsDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="fas fa-file-alt me-1"></i> Reports
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                        <li>
                            <a class="dropdown-item" href="reports.php?type=employment">
                                <i class="fas fa-briefcase me-2"></i> Employment Reports
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="reports.php?type=course">
                                <i class="fas fa-graduation-cap me-2"></i> Course Reports
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="reports.php?type=year">
                                <i class="fas fa-calendar-alt me-2"></i> Graduation Year Reports
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="export.php">
                                <i class="fas fa-file-export me-2"></i> Export Data
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <!-- Right side of navbar -->
            <ul class="navbar-nav">
                <!-- Import Data -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'import.php' ? 'active' : ''; ?>" href="import.php">
                        <i class="fas fa-file-import me-1"></i> Import
                    </a>
                </li>
                
                <!-- Admin settings -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> 
                        <?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-cog me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Common CSS for all admin pages -->
<style>
:root {
    --maroon: #800000;
    --maroon-light: #a52a2a;
    --maroon-dark: #600000;
}

body {
    background: #f8f9fa;
    color: #333;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

#particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 0;
    opacity: 0.5;
}

.content-wrapper {
    position: relative;
    z-index: 1;
    padding: 20px;
}

.bg-maroon {
    background-color: var(--maroon) !important;
}

.text-maroon {
    color: var(--maroon) !important;
}

.btn-maroon {
    background-color: var(--maroon);
    color: #fff;
}

.btn-maroon:hover {
    background-color: var(--maroon-dark);
    color: #fff;
}

.btn-outline-maroon {
    border: 1px solid var(--maroon);
    color: var(--maroon);
}

.btn-outline-maroon:hover {
    background-color: var(--maroon);
    color: #fff;
}

.navbar-dark {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1030;
}

.navbar-dark .navbar-brand,
.navbar-dark .nav-link {
    color: #fff !important;
}

.navbar-dark .nav-link:hover {
    color: rgba(255, 255, 255, 0.8) !important;
}

.navbar .nav-link.active {
    color: #fff !important;
    font-weight: bold;
    position: relative;
}

.navbar .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #fff;
}

.dropdown-menu {
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: none;
}

.dropdown-item {
    padding: 0.5rem 1.5rem;
    color: #333;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: var(--maroon);
}

.dropdown-item i {
    width: 1.25rem;
    text-align: center;
}

.card {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: var(--maroon);
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
}

.stats-card {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    background: white;
    height: 100%;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 1.5rem;
}

/* Table styling */
.table thead th {
    background-color: var(--maroon);
    color: white;
    font-weight: 500;
    border: none;
}

.table-hover tbody tr:hover {
    background-color: rgba(128, 0, 0, 0.05);
}

/* Glass effect */
.glass-effect {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}

/* Animations */
.fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Common form styling */
.form-control:focus, .form-select:focus {
    border-color: var(--maroon-light);
    box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.25);
}

.form-label {
    font-weight: 500;
    color: #555;
}

.section-title {
    color: var(--maroon);
    border-bottom: 2px solid var(--maroon);
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .chart-container {
        height: 250px;
    }
}
</style>

<!-- Particles.js Configuration -->
<script>
    // This function will be called after the page loads to initialize particles.js
    function initParticles() {
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: '#800000' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.2, random: false },
                    size: { value: 3, random: true },
                    line_linked: { enable: true, distance: 150, color: '#800000', opacity: 0.2, width: 1 },
                    move: { enable: true, speed: 3, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                    modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
                },
                retina_detect: true
            });
        }
    }

    // Call this after the page loads
    document.addEventListener('DOMContentLoaded', function() {
        initParticles();
    });
</script> 