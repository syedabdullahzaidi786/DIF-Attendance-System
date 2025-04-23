<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify the math problem
    $user_answer = isset($_POST['verification_answer']) ? (int)$_POST['verification_answer'] : 0;
    $correct_answer = isset($_SESSION['verification_answer']) ? $_SESSION['verification_answer'] : 0;
    
    if ($user_answer !== $correct_answer) {
        header("Location: ../index.php?error=2");
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Clear verification answer after successful login
        unset($_SESSION['verification_answer']);
        
        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../index.php?error=1");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?> 