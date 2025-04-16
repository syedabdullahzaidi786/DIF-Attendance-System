<?php
require_once 'config.php';

function autoMarkAttendance() {
    global $conn;
    
    // Get current date
    $currentDate = date('Y-m-d');
    
    // Check if attendance is already marked for today
    $checkQuery = "SELECT COUNT(*) as count FROM attendance WHERE date = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $currentDate);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        return "Attendance already marked for today";
    }
    
    // Get all students
    $studentsQuery = "SELECT id FROM students";
    $studentsResult = $conn->query($studentsQuery);
    
    // Get admin user ID for marking attendance
    $adminQuery = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $adminResult = $conn->query($adminQuery);
    $adminRow = $adminResult->fetch_assoc();
    $adminId = $adminRow['id'];
    
    // Mark attendance for all students
    $insertQuery = "INSERT INTO attendance (student_id, date, status, marked_by, is_auto_marked) VALUES (?, ?, 'present', ?, TRUE)";
    $insertStmt = $conn->prepare($insertQuery);
    
    while ($student = $studentsResult->fetch_assoc()) {
        $insertStmt->bind_param("isi", $student['id'], $currentDate, $adminId);
        $insertStmt->execute();
    }
    
    return "Attendance marked automatically for all students";
}

// Run the function
$result = autoMarkAttendance();
echo $result;
?> 