<?php
session_start();
require_once 'config/database.php';

// Set header to indicate JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to continue'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$roll_number = isset($data['roll_number']) ? trim($data['roll_number']) : '';

// Validate roll number
if (empty($roll_number)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a roll number'
    ]);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $pdo->prepare("
        SELECT id, name, class, section, roll_number 
        FROM students 
        WHERE roll_number = ? 
        LIMIT 1
    ");
    
    // Execute query
    $stmt->execute([$roll_number]);
    
    // Get result
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        echo json_encode([
            'success' => true,
            'student' => $student
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No student found with this roll number'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} 