<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO students (roll_number, student_name, father_name, class, section) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$roll_number, $name, $father_name, $class, $section]);
        
        header("Location: students.php?msg=added");
        exit();
    } catch (PDOException $e) {
        // Log the error
        error_log("Database Error: " . $e->getMessage());
        
        if ($e->getCode() == 23000) { // Duplicate entry
            header("Location: students.php?error=duplicate");
            exit();
        }
        // Show the actual error message instead of just "unknown"
        header("Location: students.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: students.php");
    exit();
}
?> 