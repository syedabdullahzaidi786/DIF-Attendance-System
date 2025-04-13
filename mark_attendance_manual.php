<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once 'config/database.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;
$date = isset($data['date']) ? trim($data['date']) : '';
$status = isset($data['status']) ? trim($data['status']) : 'present';
$notes = isset($data['notes']) ? trim($data['notes']) : '';

if (empty($student_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

if (empty($date)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit();
}

try {
    // Check if student exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }

    // Check if attendance already marked for this date
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
    $stmt->execute([$student_id, $date]);
    $existing_attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_attendance) {
        // Update existing attendance
        $stmt = $pdo->prepare("UPDATE attendance SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $notes, $existing_attendance['id']]);
        
        $message = 'Attendance updated successfully';
    } else {
        // Insert new attendance
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, notes, marked_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $date, $status, $notes, $_SESSION['user_id']]);
        
        $message = 'Attendance marked successfully';
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'student' => [
            'name' => $student['name'],
            'class' => $student['class'],
            'section' => $student['section']
        ]
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 