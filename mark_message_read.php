<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify that the message belongs to the current user
    $stmt = $pdo->prepare("SELECT id FROM messages WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$message_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        // Mark the message as read
        $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$message_id]);
    }
}

// Redirect back to dashboard
header('Location: dashboard.php');
exit(); 