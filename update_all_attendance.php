<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

// Get all classes and sections for dropdowns
try {
    $classStmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class");
    $classes = $classStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $sectionStmt = $pdo->query("SELECT DISTINCT section FROM students ORDER BY section");
    $sections = $sectionStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Error fetching classes and sections: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $class = $_POST['class'] ?? null;
    $section = $_POST['section'] ?? null;
    $search = $_POST['search'] ?? '';
    
    try {
        // Build the query based on selected filters
        $query = "SELECT s.id, s.roll_number, s.student_name, s.class, s.section, 
                         a.status as current_status, a.notes as current_notes
                 FROM students s
                 LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
                 WHERE 1=1";
        $params = [$date];
        
        if ($class) {
            $query .= " AND s.class = ?";
            $params[] = $class;
        }
        
        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        if ($search) {
            $query .= " AND (s.student_name LIKE ? OR s.roll_number LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $query .= " ORDER BY s.class, s.section, s.roll_number";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($students)) {
            $error = "No students found matching the selected criteria.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle attendance update
if (isset($_POST['update_attendance'])) {
    $date = $_POST['date'];
    $bulk_status = $_POST['bulk_status'] ?? null;
    $bulk_note = $_POST['bulk_note'] ?? null;
    $class = $_POST['class'] ?? null;
    $section = $_POST['section'] ?? null;
    $search = $_POST['search'] ?? null;
    
    try {
        $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $adminStmt->execute();
        $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);
        $adminId = $admin['id'];
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Build the query to get student IDs
            $query = "SELECT id FROM students WHERE 1=1";
            $params = [];
            
            if ($class) {
                $query .= " AND class = ?";
                $params[] = $class;
            }
            
            if ($section) {
                $query .= " AND section = ?";
                $params[] = $section;
            }
            
            if ($search) {
                $query .= " AND (student_name LIKE ? OR roll_number LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Prepare the update statement
            $updateStmt = $pdo->prepare("
                INSERT INTO attendance (student_id, date, status, marked_by, is_auto_marked, notes) 
                VALUES (?, ?, ?, ?, FALSE, ?)
                ON DUPLICATE KEY UPDATE 
                    status = VALUES(status),
                    marked_by = VALUES(marked_by),
                    notes = VALUES(notes)
            ");
            
            $successCount = 0;
            $failedStudents = [];
            
            // Process students in chunks of 100
            $chunks = array_chunk($studentIds, 100);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $studentId) {
                    try {
                        $updateStmt->execute([$studentId, $date, $bulk_status, $adminId, $bulk_note]);
                        $successCount++;
                    } catch (PDOException $e) {
                        $failedStudents[] = $studentId;
                        error_log("Failed to update attendance for student $studentId: " . $e->getMessage());
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Verify the updates
            $verifyStmt = $pdo->prepare("
                SELECT COUNT(*) as total_count,
                       SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as status_count
                FROM attendance 
                WHERE date = ?
            ");
            $verifyStmt->execute([$bulk_status, $date]);
            $verifyResult = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($verifyResult['total_count'] >= count($studentIds)) {
                $success = "Attendance updated successfully for " . $successCount . " students on " . date('d-m-Y', strtotime($date));
            } else {
                $error = "Verification failed: Expected at least " . count($studentIds) . " records, found " . $verifyResult['total_count'];
            }
            
            if (!empty($failedStudents)) {
                $error .= "<br>Failed to update attendance for student IDs: " . implode(", ", $failedStudents);
            }
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error updating attendance: " . $e->getMessage();
            error_log("Attendance update error: " . $e->getMessage());
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    }
}

// Get the current date or selected date
$current_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update All Attendance - DIF Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .attendance-table th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }
        .status-select {
            min-width: 120px;
        }
        .notes-input {
            min-width: 200px;
        }
        .bulk-update-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Update All Attendance</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="mb-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Select Date</label>
                                        <input type="date" class="form-control" id="date" name="date" required 
                                               value="<?php echo htmlspecialchars($current_date); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="class" class="form-label">Class (Optional)</label>
                                        <select class="form-select" id="class" name="class">
                                            <option value="">All Classes</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo htmlspecialchars($class); ?>" 
                                                    <?php echo (isset($_POST['class']) && $_POST['class'] == $class) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="section" class="form-label">Section (Optional)</label>
                                        <select class="form-select" id="section" name="section">
                                            <option value="">All Sections</option>
                                            <?php foreach ($sections as $section): ?>
                                                <option value="<?php echo htmlspecialchars($section); ?>"
                                                    <?php echo (isset($_POST['section']) && $_POST['section'] == $section) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($section); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="search" class="form-label">Search Students</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               placeholder="Search by name or roll number"
                                               value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Load Students
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <?php if (isset($students) && !empty($students)): ?>
                        <form method="POST" action="" id="attendanceForm">
                            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                            <input type="hidden" name="update_attendance" value="1">
                            
                            <div class="bulk-update-section mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bulk_status" class="form-label">Update All Status</label>
                                            <select class="form-select" id="bulk_status" name="bulk_status">
                                                <option value="">Select Status</option>
                                                <option value="present">Present</option>
                                                <option value="absent">Absent</option>
                                                <option value="leave">Leave</option>
                                                <option value="half_day">Half Day</option>
                                                <option value="holi_day">Holi Day</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bulk_note" class="form-label">Bulk Note (Optional)</label>
                                            <input type="text" class="form-control" id="bulk_note" 
                                                   placeholder="Enter note for all students">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-info w-100" id="applyBulkUpdate">
                                                <i class="fas fa-sync me-2"></i>Apply to All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover attendance-table">
                                    <thead>
                                        <tr>
                                            <th>Roll No.</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Section</th>
                                            <th>Current Status</th>
                                            <th>Update Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                                            <td><?php echo htmlspecialchars($student['section']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $student['current_status'] == 'present' ? 'success' : 
                                                        ($student['current_status'] == 'absent' ? 'danger' : 
                                                        ($student['current_status'] == 'leave' ? 'warning' : 
                                                        ($student['current_status'] == 'half_day' ? 'info' : 
                                                        ($student['current_status'] == 'holi_day' ? 'primary' : 'secondary')))); 
                                                ?>">
                                                    <?php echo htmlspecialchars($student['current_status'] ?? 'Not Marked'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <select class="form-select status-select" name="attendance[<?php echo $student['id']; ?>]" required>
                                                    <option value="present" <?php echo ($student['current_status'] ?? '') == 'present' ? 'selected' : ''; ?>>Present</option>
                                                    <option value="absent" <?php echo ($student['current_status'] ?? '') == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                    <option value="leave" <?php echo ($student['current_status'] ?? '') == 'leave' ? 'selected' : ''; ?>>Leave</option>
                                                    <option value="half_day" <?php echo ($student['current_status'] ?? '') == 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                                                    <option value="holi_day" <?php echo ($student['current_status'] ?? '') == 'holi_day' ? 'selected' : ''; ?>>Holi Day</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control notes-input" 
                                                       name="notes[<?php echo $student['id']; ?>]" 
                                                       value="<?php echo htmlspecialchars($student['current_notes'] ?? ''); ?>"
                                                       placeholder="Enter notes">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Save All Changes
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Add Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">
                        <i class="fas fa-check-circle me-2"></i>Attendance Updated Successfully
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Date:</strong> <span id="updateDate"></span></p>
                            <p><strong>Total Students Updated:</strong> <span id="totalStudents"></span></p>
                            <p><strong>Status Applied:</strong> <span id="updateStatus"></span></p>
                            <p><strong>Note:</strong> <span id="updateNote"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.reload()">Refresh Page</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const bulkStatus = document.getElementById('bulk_status');
        const bulkNote = document.getElementById('bulk_note');
        const applyBulkBtn = document.getElementById('applyBulkUpdate');
        const attendanceForm = document.getElementById('attendanceForm');
        
        if (applyBulkBtn && attendanceForm) {
            applyBulkBtn.addEventListener('click', function() {
                const status = bulkStatus.value;
                
                if (!status) {
                    alert('Please select a status to apply to all students');
                    return;
                }
                
                // Show loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'alert alert-info';
                loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating attendance...';
                attendanceForm.parentNode.insertBefore(loadingDiv, attendanceForm);
                
                // Get form data
                const formData = {
                    update_attendance: true,
                    date: document.getElementById('date').value,
                    bulk_status: status,
                    bulk_note: bulkNote.value,
                    class: document.getElementById('class').value,
                    section: document.getElementById('section').value,
                    search: document.getElementById('search').value
                };
                
                // Send AJAX request
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: Object.keys(formData)
                        .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(formData[key]))
                        .join('&')
                })
                .then(response => response.text())
                .then(html => {
                    // Replace the current page content with the response
                    document.documentElement.innerHTML = html;
                    // Scroll to top
                    window.scrollTo(0, 0);
                })
                .catch(error => {
                    loadingDiv.className = 'alert alert-danger';
                    loadingDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Error updating attendance: ' + error;
                });
            });
        }
    });
    </script>
</body>
</html> 