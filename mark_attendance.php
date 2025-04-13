<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $qr_code = $data['qr_code'] ?? '';
    
    try {
        // Get student by QR code
        $stmt = $pdo->prepare("SELECT * FROM students WHERE qr_code = ?");
        $stmt->execute([$qr_code]);
        $student = $stmt->fetch();
        
        if (!$student) {
            throw new Exception('Student not found');
        }
        
        // Check if attendance already marked for today
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("
            SELECT id FROM attendance 
            WHERE student_id = ? AND date = ?
        ");
        $stmt->execute([$student['id'], $today]);
        
        if ($stmt->fetch()) {
            throw new Exception('Attendance already marked for today');
        }
        
        // Mark attendance
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, date, status, marked_by)
            VALUES (?, ?, 'present', ?)
        ");
        $stmt->execute([$student['id'], $today, $_SESSION['user_id']]);
        
        // Return success response with student info
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
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request method']);
}
?> 