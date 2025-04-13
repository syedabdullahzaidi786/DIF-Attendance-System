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
    $class = $_POST['class'];
    $section = $_POST['section'];
    
    // Generate unique QR code
    $qr_code = uniqid($roll_number . '_', true);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO students (roll_number, name, class, section, qr_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$roll_number, $name, $class, $section, $qr_code]);
        
        header("Location: students.php?msg=added");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            header("Location: students.php?error=duplicate");
            exit();
        }
        header("Location: students.php?error=unknown");
        exit();
    }
} else {
    header("Location: students.php");
    exit();
}
?> 