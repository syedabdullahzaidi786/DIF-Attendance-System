<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Don't allow deleting self
    if ($user_id == $_SESSION['user_id']) {
        header("Location: users.php?error=cannot_delete_self");
        exit();
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        header("Location: users.php?msg=deleted");
    } else {
        header("Location: users.php?error=failed");
    }
    exit();
}

header("Location: users.php");
exit(); 