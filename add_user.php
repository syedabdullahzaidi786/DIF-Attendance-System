<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Store password as plain text
    $role = $_POST['role'];

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: users.php?error=username_exists");
        exit();
    }

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt->execute([$username, $password, $role])) {
        header("Location: users.php?msg=added");
    } else {
        header("Location: users.php?error=failed");
    }
    exit();
}

header("Location: users.php");
exit(); 