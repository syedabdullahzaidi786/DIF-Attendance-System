<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/database.php';

$student = null;
$attendance = null;
$error = '';
$success = '';
$step = 1; // Default to step 1
$user_role = $_SESSION['role'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step1'])) {
        // Step 1: Search for student
        $roll_number = $_POST['roll_number'] ?? '';
        
        try {
            // Get student by roll number
            $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ?");
            $stmt->execute([$roll_number]);
            $student = $stmt->fetch();
            
            if (!$student) {
                throw new Exception("Student not found with roll number: $roll_number");
            }
            
            $step = 2; // Move to step 2
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } 
    elseif (isset($_POST['step2'])) {
        // Step 2: Update attendance
        $roll_number = $_POST['roll_number'];
        $date = $_POST['date'] ?? '';
        $status = $_POST['status'] ?? '';
        
        try {
            // Get student by roll number
            $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ?");
            $stmt->execute([$roll_number]);
            $student = $stmt->fetch();
            
            if (!$student) {
                throw new Exception("Student not found with roll number: $roll_number");
            }
            
            // Check if attendance record exists
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
            $stmt->execute([$student['id'], $date]);
            $attendance = $stmt->fetch();
            
            if ($attendance) {
                if ($user_role == 'admin') {
                    // Admin can update any attendance record
                    $stmt = $pdo->prepare("UPDATE attendance SET status = ?, notes = ? WHERE id = ?");
                    $stmt->execute([$status, $_POST['notes'] ?? '', $attendance['id']]);
                } else {
                    // Check if this teacher has already marked attendance for this student on this date
                    $stmt = $pdo->prepare("SELECT marked_by FROM attendance WHERE student_id = ? AND date = ?");
                    $stmt->execute([$student['id'], $date]);
                    $marked_by = $stmt->fetchColumn();
                    
                    if ($marked_by == $_SESSION['user_id']) {
                        throw new Exception("You have already marked attendance for this student on this date.");
                    } else {
                        // Allow teacher to update attendance once
                        $stmt = $pdo->prepare("UPDATE attendance SET status = ?, notes = ?, marked_by = ? WHERE id = ?");
                        $stmt->execute([$status, $_POST['notes'] ?? '', $_SESSION['user_id'], $attendance['id']]);
                    }
                }
            } else {
                // Create new attendance record
                $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, notes, marked_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$student['id'], $date, $status, $_POST['notes'] ?? '', $_SESSION['user_id']]);
            }
            
            $success = "Attendance updated successfully for " . $student['student_name'];
            $step = 1; // Reset to step 1 after successful update
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            $step = 2; // Stay on step 2 if there's an error
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Attendance - DIF Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Update Student Attendance</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                        <!-- Step 1: Search for student -->
                        <form method="POST" action="">
                            <input type="hidden" name="step1" value="1">
                            <div class="mb-3">
                                <label for="roll_number" class="form-label">Enter GR Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="roll_number" name="roll_number" 
                                           value="<?php echo $_POST['roll_number'] ?? ''; ?>" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php elseif ($step == 2 && $student): ?>
                        <!-- Step 2: Update attendance -->
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <h5 class="mb-1">Student Details:</h5>
                                <p class="mb-1"><strong>Name:</strong> <?php echo $student['student_name']; ?></p>
                                <p class="mb-1"><strong>Father Name:</strong> <?php echo $student['father_name']; ?></p>
                                <p class="mb-1"><strong>Class:</strong> <?php echo $student['class']; ?></p>
                                <p class="mb-0"><strong>Section:</strong> <?php echo $student['section']; ?></p>
                            </div>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="step2" value="1">
                            <input type="hidden" name="roll_number" value="<?php echo $student['roll_number']; ?>">
                            
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" 
                                       value="<?php echo $_POST['date'] ?? date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="present" <?php echo ($attendance['status'] ?? '') == 'present' ? 'selected' : ''; ?>>Present</option>
                                    <option value="absent" <?php echo ($attendance['status'] ?? '') == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    <option value="leave" <?php echo ($attendance['status'] ?? '') == 'leave' ? 'selected' : ''; ?>>Leave</option>
                                    <option value="half_day" <?php echo ($attendance['status'] ?? '') == 'half_day' ? 'selected' : ''; ?>>Half Day</option>
                                    <option value="holi_day" <?php echo ($attendance['status'] ?? '') == 'holi_day' ? 'selected' : ''; ?>>Holi Day</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo $attendance['notes'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="update_attendance.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Attendance
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
</body>
</html> 