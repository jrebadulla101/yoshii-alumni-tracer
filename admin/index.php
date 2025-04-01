<?php
session_start();
require_once '../config/database.php';
require_once 'navbar.php';

// Quick Statistics
$total_alumni_query = "SELECT COUNT(*) as total FROM alumni";
$total_alumni_result = mysqli_query($conn, $total_alumni_query);
$total_alumni = mysqli_fetch_assoc($total_alumni_result)['total'];

// Employment Rate
$employment_rate_query = "SELECT 
    (COUNT(CASE WHEN employment_status = 'Employed' OR employment_status = 'Self-employed' THEN 1 END) * 100.0 / COUNT(*)) as rate 
    FROM alumni";
$employment_rate_result = mysqli_query($conn, $employment_rate_query);
$employment_rate = number_format(mysqli_fetch_assoc($employment_rate_result)['rate'] ?? 0, 1);

// Course-Related Employment
$course_related_query = "SELECT 
    (COUNT(CASE WHEN is_course_related = 1 THEN 1 END) * 100.0 / 
    COUNT(CASE WHEN employment_status IN ('Employed', 'Self-employed') THEN 1 END)) as rate 
    FROM alumni 
    WHERE employment_status IN ('Employed', 'Self-employed')";
$course_related_result = mysqli_query($conn, $course_related_query);
$course_related_rate = number_format(mysqli_fetch_assoc($course_related_result)['rate'] ?? 0, 1);

// Course Distribution
$course_stats_query = "SELECT course, COUNT(*) as count FROM alumni GROUP BY course ORDER BY count DESC";
$course_stats_result = mysqli_query($conn, $course_stats_query);
$course_stats = [];
while ($row = mysqli_fetch_assoc($course_stats_result)) {
    $course_stats[$row['course']] = $row['count'];
}

// Yearly Distribution
$year_stats_query = "SELECT year_graduated, COUNT(*) as count FROM alumni GROUP BY year_graduated ORDER BY year_graduated DESC";
$year_stats_result = mysqli_query($conn, $year_stats_query);
$year_stats = [];
while ($row = mysqli_fetch_assoc($year_stats_result)) {
    $year_stats[$row['year_graduated']] = $row['count'];
}

// Employment Status Distribution
$employment_stats_query = "SELECT employment_status, COUNT(*) as count FROM alumni GROUP BY employment_status";
$employment_stats_result = mysqli_query($conn, $employment_stats_query);
$employment_stats = [];
while ($row = mysqli_fetch_assoc($employment_stats_result)) {
    $employment_stats[$row['employment_status']] = $row['count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #ffffff;
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
        }
        .content-wrapper {
            position: relative;
            z-index: 1;
            padding: 20px;
        }
        .stats-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stat-item {
            margin-bottom: 20px;
        }
        .stat-label {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #800000;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .card-header {
            background: #800000;
            color: #fff;
            border-radius: 15px 15px 0 0 !important;
        }
        .text-maroon {
            color: #800000;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="content-wrapper">
        <div class="container-fluid">
            <!-- Overview Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Total Alumni</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-value"><?php echo $total_alumni; ?></div>
                            <div class="stat-label">Registered Alumni</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Employment Rate</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-value"><?php echo $employment_rate; ?>%</div>
                            <div class="stat-label">Overall Employment</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Course-Related</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-value"><?php echo $course_related_rate; ?>%</div>
                            <div class="stat-label">Field-Related Work</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Latest Batch</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-value"><?php echo max(array_keys($year_stats)); ?></div>
                            <div class="stat-label">Most Recent Graduates</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Course Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="courseChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Employment Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="employmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Trends -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="stats-card">
                        <div class="card-header">
                            <h5 class="mb-0">Yearly Graduate Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="yearlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Particles.js configuration with white theme
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#800000' },
                shape: { type: 'circle' },
                opacity: { value: 0.2, random: false },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#800000', opacity: 0.2, width: 1 },
                move: { enable: true, speed: 6, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });

        // Course Distribution Chart
        new Chart(document.getElementById('courseChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($course_stats)); ?>,
                datasets: [{
                    label: 'Number of Alumni',
                    data: <?php echo json_encode(array_values($course_stats)); ?>,
                    backgroundColor: '#800000',
                    borderColor: '#800000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Employment Status Chart
        new Chart(document.getElementById('employmentChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($employment_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($employment_stats)); ?>,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Yearly Trends Chart
        new Chart(document.getElementById('yearlyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($year_stats)); ?>,
                datasets: [{
                    label: 'Number of Graduates',
                    data: <?php echo json_encode(array_values($year_stats)); ?>,
                    borderColor: '#800000',
                    backgroundColor: 'rgba(128, 0, 0, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
