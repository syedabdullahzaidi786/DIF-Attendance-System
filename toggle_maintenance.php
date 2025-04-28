<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Read current status
$maintenance = include 'config/maintenance_mode.php';
$is_enabled = $maintenance['enabled'];

// Toggle status
$new_status = !$is_enabled;

// Write new status to file
$file_content = "<?php\nreturn [\n    'enabled' => " . ($new_status ? 'true' : 'false') . " // true = maintenance mode ON, false = OFF\n];\n";
file_put_contents('config/maintenance_mode.php', $file_content);

header('Location: dashboard.php');
exit(); 