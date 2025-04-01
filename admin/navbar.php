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
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                
                <!-- Add Analytics Button -->
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>" 
                       href="analytics.php">
                        <i class="fas fa-chart-line me-1"></i> Analytics
                    </a>
                </li>
                
                <!-- Quick Stats Button with Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="statsDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-pie me-1"></i> Quick Stats
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="statsDropdown">
                        <li>
                            <a class="dropdown-item" href="analytics.php#individual">
                                <i class="fas fa-user me-2"></i> Individual Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics.php#course">
                                <i class="fas fa-graduation-cap me-2"></i> Course Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics.php#year">
                                <i class="fas fa-calendar-alt me-2"></i> Year Analysis
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="analytics.php#graduation">
                                <i class="fas fa-user-graduate me-2"></i> Graduation Analysis
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="analytics.php?export=all">
                                <i class="fas fa-file-export me-2"></i> Export All Reports
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <!-- Right side of navbar -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-file-export me-1"></i> Export
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-file-import me-1"></i> Import
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-cog me-2"></i> Profile
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

<!-- Analytics Quick Access Modal -->
<div class="modal fade" id="quickStatsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line me-2"></i>Quick Statistics
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Employment Stats -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Employment Rate</h6>
                                <div class="display-4 text-center text-success mb-2">
                                    <?php
                                    $sql = "SELECT 
                                            ROUND((COUNT(CASE WHEN employment_status != 'Unemployed' THEN 1 END) * 100.0) / 
                                            COUNT(*), 1) as rate 
                                            FROM alumni";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['rate'] . '%';
                                    ?>
                                </div>
                                <p class="text-muted text-center">Overall Employment Rate</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Related Jobs -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Course-Related Employment</h6>
                                <div class="display-4 text-center text-primary mb-2">
                                    <?php
                                    $sql = "SELECT 
                                            ROUND((COUNT(CASE WHEN is_course_related = 'Yes' THEN 1 END) * 100.0) / 
                                            COUNT(*), 1) as rate 
                                            FROM alumni 
                                            WHERE employment_status != 'Unemployed'";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    echo $row['rate'] . '%';
                                    ?>
                                </div>
                                <p class="text-muted text-center">Working in Field of Study</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="analytics.php" class="btn btn-maroon">
                    <i class="fas fa-chart-bar me-2"></i>View Full Analytics
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add some custom CSS -->
<style>
/* Updated navbar styles */
.navbar-dark {
    background-color: #800000 !important;
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

.dropdown-menu {
    background-color: #fff;
    z-index: 1031;
}

.glass-effect {
    position: relative;
    z-index: 1;
}

#particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 0;
}

.dropdown-item {
    color: #333;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #800000;
}

.navbar .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
}

.navbar .nav-link:hover {
    color: #fff !important;
}

.navbar .nav-link.active {
    color: #fff !important;
    font-weight: bold;
}

.btn-maroon {
    background-color: #800000;
    color: #fff;
}

.btn-maroon:hover {
    background-color: #600000;
    color: #fff;
}

/* Quick stats cards hover effect */
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

/* Modal animations */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}
</style> 