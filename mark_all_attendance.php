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
    $date = $_POST['date'];
    $status = $_POST['status'];
    $class = $_POST['class'] ?? null;
    $section = $_POST['section'] ?? null;
    $note = $_POST['note'] ?? null;
    
    try {
        // Build the query based on selected filters
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
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $students = $stmt->fetchAll();
        
        if (empty($students)) {
            $error = "No students found matching the selected criteria.";
        } else {
            // Get admin user ID for marking attendance
            $adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $adminStmt->execute();
            $admin = $adminStmt->fetch();
            $adminId = $admin['id'];
            
            // Mark attendance for filtered students
            $insertStmt = $pdo->prepare("
                INSERT INTO attendance (student_id, date, status, marked_by, is_auto_marked, notes) 
                VALUES (?, ?, ?, ?, FALSE, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes)
            ");
            
            foreach ($students as $student) {
                $insertStmt->execute([$student['id'], $date, $status, $adminId, $note]);
            }
            
            $success = "Attendance marked successfully for " . count($students) . " students on " . date('d-m-Y', strtotime($date));
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark All Attendance - DIF Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Mark All Attendance</h4>
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
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="date" class="form-label">Select Date</label>
                                <input type="date" class="form-control" id="date" name="date" required 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="class" class="form-label">Class (Optional)</label>
                                <select class="form-select" id="class" name="class">
                                    <option value="">All Classes</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo htmlspecialchars($class); ?>">
                                            <?php echo htmlspecialchars($class); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="section" class="form-label">Section (Optional)</label>
                                <select class="form-select" id="section" name="section">
                                    <option value="">All Sections</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo htmlspecialchars($section); ?>">
                                            <?php echo htmlspecialchars($section); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="holi_day">Holi Day</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">Note (Optional)</label>
                                <textarea class="form-control" id="note" name="note" rows="3" 
                                          placeholder="Enter any additional notes or remarks"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-check me-2"></i>Mark Attendance
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 