<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query for messages
$sql = "
    SELECT m.*, 
           sender.username as sender_name,
           receiver.username as receiver_name,
           sender.role as sender_role,
           receiver.role as receiver_role
    FROM messages m
    JOIN users sender ON m.sender_id = sender.id
    JOIN users receiver ON m.receiver_id = receiver.id
    WHERE 1=1
";

// Add filters
if ($filter === 'sent') {
    $sql .= " AND m.sender_id = ?";
} elseif ($filter === 'received') {
    $sql .= " AND m.receiver_id = ?";
}

if (!empty($search)) {
    $sql .= " AND (sender.username LIKE ? OR receiver.username LIKE ? OR m.message LIKE ?)";
}

$sql .= " ORDER BY m.created_at DESC";

// Prepare and execute query
$stmt = $pdo->prepare($sql);
$params = [];

if ($filter === 'sent') {
    $params[] = $_SESSION['user_id'];
} elseif ($filter === 'received') {
    $params[] = $_SESSION['user_id'];
}

if (!empty($search)) {
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message History - DIF Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .message-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            margin-bottom: 1rem;
        }
        .message-card .card-header {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            padding: 1rem;
        }
        .message-card .card-body {
            padding: 1.5rem;
        }
        .message-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .message-content {
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .search-box {
            max-width: 300px;
        }
        .filter-buttons .btn {
            margin-right: 0.5rem;
        }
        .unread-badge {
            background-color: var(--danger-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <a href="dashboard.php" class="btn btn-outline-primary back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <h2 class="page-title">
                    <i class="fas fa-history me-2"></i>Message History
                </h2>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="filter-buttons">
                    <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-inbox me-2"></i>All Messages
                    </a>
                    <a href="?filter=sent" class="btn <?php echo $filter === 'sent' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-paper-plane me-2"></i>Sent
                    </a>
                    <a href="?filter=received" class="btn <?php echo $filter === 'received' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="fas fa-envelope me-2"></i>Received
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <div class="input-group search-box">
                        <input type="text" name="search" class="form-control" placeholder="Search messages..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Messages List -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($messages)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No messages found.
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="card message-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($message['sender_id'] == $_SESSION['user_id']): ?>
                                            <i class="fas fa-paper-plane me-2"></i>To: 
                                            <?php echo htmlspecialchars($message['receiver_name']); ?>
                                            <span class="text-muted">(<?php echo $message['receiver_role']; ?>)</span>
                                        <?php else: ?>
                                            <i class="fas fa-envelope me-2"></i>From: 
                                            <?php echo htmlspecialchars($message['sender_name']); ?>
                                            <span class="text-muted">(<?php echo $message['sender_role']; ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if (!$message['is_read'] && $message['receiver_id'] == $_SESSION['user_id']): ?>
                                            <span class="unread-badge">New</span>
                                        <?php endif; ?>
                                        <small class="text-light">
                                            <?php echo date('d M Y H:i', strtotime($message['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                                <?php if (!$message['is_read'] && $message['receiver_id'] == $_SESSION['user_id']): ?>
                                    <div class="mt-3">
                                        <form method="POST" action="mark_message_read.php" style="display: inline;">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check me-2"></i>Mark as Read
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 