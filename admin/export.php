<?php
// Start session before ANY output
session_start();

// Include database connection
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get database tables
$tables_query = "SHOW TABLES";
$tables_result = mysqli_query($conn, $tables_query);
$tables = [];
while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

// Handle export request
if (isset($_POST['export'])) {
    $export_table = $_POST['table'];
    $export_format = $_POST['format'];
    $export_fields = isset($_POST['fields']) ? $_POST['fields'] : [];
    
    // Validate table name to prevent SQL injection
    if (!in_array($export_table, $tables)) {
        $error = "Invalid table selected";
    } else {
        // Get table structure
        $structure_query = "DESCRIBE $export_table";
        $structure_result = mysqli_query($conn, $structure_query);
        $structure = [];
        
        while ($row = mysqli_fetch_assoc($structure_result)) {
            $structure[] = $row;
        }
        
        // Prepare fields for query
        $fields_sql = "*"; // Default to all fields
        if (!empty($export_fields)) {
            // Ensure all field names are valid columns
            $valid_fields = [];
            foreach ($export_fields as $field) {
                foreach ($structure as $column) {
                    if ($column['Field'] === $field) {
                        $valid_fields[] = "`$field`";
                        break;
                    }
                }
            }
            
            if (!empty($valid_fields)) {
                $fields_sql = implode(", ", $valid_fields);
            }
        }
        
        // Build the export query
        $query = "SELECT $fields_sql FROM $export_table";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            $error = "Error querying database: " . mysqli_error($conn);
        } else {
            // Determine the export format and handle accordingly
            switch ($export_format) {
                case 'csv':
                    // Set headers for CSV download
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="export_' . $export_table . '_' . date('Y-m-d') . '.csv"');
                    
                    // Create a file pointer
                    $output = fopen('php://output', 'w');
                    
                    // Get the column headers
                    $headers = [];
                    $fields = mysqli_fetch_fields($result);
                    foreach ($fields as $field) {
                        $headers[] = $field->name;
                    }
                    fputcsv($output, $headers);
                    
                    // Output each row of data
                    while ($row = mysqli_fetch_assoc($result)) {
                        fputcsv($output, $row);
                    }
                    
                    fclose($output);
                    exit();
                
                case 'json':
                    // Set headers for JSON download
                    header('Content-Type: application/json');
                    header('Content-Disposition: attachment; filename="export_' . $export_table . '_' . date('Y-m-d') . '.json"');
                    
                    // Convert data to JSON
                    $data = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[] = $row;
                    }
                    
                    echo json_encode($data, JSON_PRETTY_PRINT);
                    exit();
                
                case 'excel':
                    // Set headers for Excel download
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment; filename="export_' . $export_table . '_' . date('Y-m-d') . '.xls"');
                    
                    echo '<table border="1">';
                    
                    // Get the column headers
                    echo '<tr>';
                    $fields = mysqli_fetch_fields($result);
                    foreach ($fields as $field) {
                        echo '<th>' . $field->name . '</th>';
                    }
                    echo '</tr>';
                    
                    // Output each row of data
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        foreach ($row as $cell) {
                            echo '<td>' . $cell . '</td>';
                        }
                        echo '</tr>';
                    }
                    
                    echo '</table>';
                    exit();
                
                default:
                    $error = "Invalid export format";
            }
        }
    }
}

// Include navbar
require_once 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Alumni Tracer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .export-card {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #800000;
            color: white;
            padding: 15px 20px;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px -20px;
        }
        .field-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container-fluid py-4">
            <h2 class="mb-4">
                <i class="fas fa-file-export me-2"></i>Export Data
            </h2>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="export-card">
                        <div class="card-header">
                            <h5 class="mb-0">Export Options</h5>
                        </div>
                        
                        <form action="export.php" method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="table" class="form-label">Select Table</label>
                                    <select name="table" id="table" class="form-select" required>
                                        <option value="">-- Select Table --</option>
                                        <?php foreach ($tables as $table): ?>
                                        <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="format" class="form-label">Export Format</label>
                                    <select name="format" id="format" class="form-select" required>
                                        <option value="csv">CSV (Comma Separated Values)</option>
                                        <option value="json">JSON (JavaScript Object Notation)</option>
                                        <option value="excel">Excel (XLS)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Select Fields (Leave empty to export all fields)</label>
                                <div id="fields-container" class="field-list">
                                    <div class="text-center py-3">
                                        <i class="fas fa-info-circle me-2"></i>Please select a table first
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="export" class="btn btn-maroon">
                                <i class="fas fa-file-export me-2"></i>Export Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="export-card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Export Options</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-graduate me-2"></i>Alumni List
                                        </h5>
                                        <p class="card-text">Export the complete list of alumni with all their details.</p>
                                        <div class="btn-group">
                                            <a href="export.php?quick=alumni&format=csv" class="btn btn-sm btn-outline-maroon">CSV</a>
                                            <a href="export.php?quick=alumni&format=excel" class="btn btn-sm btn-outline-maroon">Excel</a>
                                            <a href="export.php?quick=alumni&format=json" class="btn btn-sm btn-outline-maroon">JSON</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-briefcase me-2"></i>Employment Status
                                        </h5>
                                        <p class="card-text">Export alumni employment status details grouped by course.</p>
                                        <div class="btn-group">
                                            <a href="export.php?quick=employment&format=csv" class="btn btn-sm btn-outline-maroon">CSV</a>
                                            <a href="export.php?quick=employment&format=excel" class="btn btn-sm btn-outline-maroon">Excel</a>
                                            <a href="export.php?quick=employment&format=json" class="btn btn-sm btn-outline-maroon">JSON</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-graduation-cap me-2"></i>Course Statistics
                                        </h5>
                                        <p class="card-text">Export statistical data about alumni by course and graduation year.</p>
                                        <div class="btn-group">
                                            <a href="export.php?quick=course&format=csv" class="btn btn-sm btn-outline-maroon">CSV</a>
                                            <a href="export.php?quick=course&format=excel" class="btn btn-sm btn-outline-maroon">Excel</a>
                                            <a href="export.php?quick=course&format=json" class="btn btn-sm btn-outline-maroon">JSON</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="export-card">
                        <div class="card-header">
                            <h5 class="mb-0">Data Export Guide</h5>
                        </div>
                        
                        <div class="accordion" id="exportGuide">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        <i class="fas fa-info-circle me-2"></i>About Data Export
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#exportGuide">
                                    <div class="accordion-body">
                                        <p>This tool allows you to export data from the Alumni Tracer System in various formats. You can use this feature to:</p>
                                        <ul>
                                            <li>Create reports for administrative purposes</li>
                                            <li>Analyze data using external tools like Excel or statistical software</li>
                                            <li>Back up specific tables or data</li>
                                            <li>Share alumni information with other departments</li>
                                        </ul>
                                        <p><strong>Note:</strong> All exported data should be handled according to your organization's data privacy policies.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        <i class="fas fa-file-csv me-2"></i>About CSV Format
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#exportGuide">
                                    <div class="accordion-body">
                                        <p>CSV (Comma Separated Values) files are:</p>
                                        <ul>
                                            <li>Simple text files where each line represents a data record</li>
                                            <li>Values are separated by commas</li>
                                            <li>Compatible with most spreadsheet applications (Excel, Google Sheets)</li>
                                            <li>Ideal for data exchange between different systems</li>
                                        </ul>
                                        <p>Use this format when you need to import the data into spreadsheet software or databases.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        <i class="fas fa-file-excel me-2"></i>About Excel Format
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#exportGuide">
                                    <div class="accordion-body">
                                        <p>Excel (XLS) files offer:</p>
                                        <ul>
                                            <li>Direct compatibility with Microsoft Excel</li>
                                            <li>Simple formatting of data tables</li>
                                            <li>Easy to view and analyze data immediately</li>
                                        </ul>
                                        <p>This format is best when you need to work with the data directly in Microsoft Excel.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                        <i class="fas fa-file-code me-2"></i>About JSON Format
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#exportGuide">
                                    <div class="accordion-body">
                                        <p>JSON (JavaScript Object Notation) files provide:</p>
                                        <ul>
                                            <li>A structured, easy-to-read format for data</li>
                                            <li>Compatible with web and mobile applications</li>
                                            <li>Ideal for developers or technical staff</li>
                                            <li>Preserves data types and structure</li>
                                        </ul>
                                        <p>Use this format when working with developers or importing data into web applications.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load fields when a table is selected
        document.getElementById('table').addEventListener('change', function() {
            const table = this.value;
            const fieldsContainer = document.getElementById('fields-container');
            
            if (table === '') {
                fieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-info-circle me-2"></i>Please select a table first</div>';
                return;
            }
            
            // Show loading indicator
            fieldsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading fields...</div>';
            
            // Fetch table structure using AJAX
            fetch('get_table_structure.php?table=' + encodeURIComponent(table))
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        fieldsContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    } else {
                        let html = '';
                        
                        // Create checkbox for each field
                        data.forEach(field => {
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fields[]" value="${field.Field}" id="field-${field.Field}">
                                    <label class="form-check-label" for="field-${field.Field}">
                                        ${field.Field} <small class="text-muted">(${field.Type})</small>
                                    </label>
                                </div>
                            `;
                        });
                        
                        // Add select all option
                        html = `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">
                                    <strong>Select All Fields</strong>
                                </label>
                            </div>
                            <hr>
                        ` + html;
                        
                        fieldsContainer.innerHTML = html;
                        
                        // Add select all functionality
                        document.getElementById('select-all').addEventListener('change', function() {
                            const fieldCheckboxes = document.querySelectorAll('input[name="fields[]"]');
                            fieldCheckboxes.forEach(checkbox => {
                                checkbox.checked = this.checked;
                            });
                        });
                    }
                })
                .catch(error => {
                    fieldsContainer.innerHTML = `<div class="alert alert-danger">Error loading fields: ${error.message}</div>`;
                });
        });
        
        // Handle quick export links
        document.querySelectorAll('a[href^="export.php?quick="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const params = new URLSearchParams(this.href.split('?')[1]);
                const quickType = params.get('quick');
                const format = params.get('format');
                
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'export.php';
                
                // Add the export type
                const exportInput = document.createElement('input');
                exportInput.type = 'hidden';
                exportInput.name = 'export';
                exportInput.value = '1';
                form.appendChild(exportInput);
                
                // Add the format
                const formatInput = document.createElement('input');
                formatInput.type = 'hidden';
                formatInput.name = 'format';
                formatInput.value = format;
                form.appendChild(formatInput);
                
                // Set the table based on quick export type
                const tableInput = document.createElement('input');
                tableInput.type = 'hidden';
                tableInput.name = 'table';
                
                switch (quickType) {
                    case 'alumni':
                        tableInput.value = 'alumni';
                        break;
                    case 'employment':
                        // This would be a custom query handled in the PHP
                        tableInput.value = 'alumni';
                        
                        // Add custom fields for employment
                        const fields = ['student_number', 'first_name', 'last_name', 'gender', 'course', 'year_graduated', 
                                        'employment_status', 'job_title', 'company_name', 'is_course_related'];
                        
                        fields.forEach(field => {
                            const fieldInput = document.createElement('input');
                            fieldInput.type = 'hidden';
                            fieldInput.name = 'fields[]';
                            fieldInput.value = field;
                            form.appendChild(fieldInput);
                        });
                        break;
                    case 'course':
                        tableInput.value = 'courses';
                        break;
                    default:
                        tableInput.value = 'alumni';
                }
                
                form.appendChild(tableInput);
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
</body>
</html> 