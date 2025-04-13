<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Store password as plain text
    $role = $_POST['role'];

    // Check if username already exists for other users
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: users.php?error=username_exists");
        exit();
    }

    // Update user
    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
    if ($stmt->execute([$username, $password, $role, $user_id])) {
        header("Location: users.php?msg=updated");
    } else {
        header("Location: users.php?error=failed");
    }
    exit();
}

header("Location: users.php");
exit(); 