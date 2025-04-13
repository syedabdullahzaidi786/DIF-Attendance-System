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
$roll_number = isset($data['roll_number']) ? trim($data['roll_number']) : '';

if (empty($roll_number)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Roll number is required']);
    exit();
}

try {
    // Get today's date
    $today = date('Y-m-d');

    // Check if student exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ?");
    $stmt->execute([$roll_number]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }

    // Check if attendance already marked for today
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
    $stmt->execute([$student['id'], $today]);
    $existing_attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_attendance) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Attendance already marked for today',
            'student' => [
                'name' => $student['name'],
                'class' => $student['class'],
                'section' => $student['section']
            ]
        ]);
        exit();
    }

    // Mark attendance
    $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, marked_by) VALUES (?, ?, ?)");
    $stmt->execute([$student['id'], $today, $_SESSION['user_id']]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Attendance marked successfully',
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