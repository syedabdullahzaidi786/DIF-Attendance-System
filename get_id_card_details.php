<?php
session_start();
require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Only administrators can access this feature'
    ]);
    exit();
}

// Get student ID from request
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid student ID'
    ]);
    exit();
}

try {
    // Get student details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo json_encode([
            'success' => false,
            'message' => 'Student not found'
        ]);
        exit();
    }

    // Return student details as JSON
    echo json_encode([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'name' => $student['name'],
            'class' => $student['class'],
            'section' => $student['section'],
            'roll_number' => $student['roll_number']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 