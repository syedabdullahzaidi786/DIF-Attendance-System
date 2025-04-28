<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id'];

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sender_id, $receiver_id, $message]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Message sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send message. Please try again.";
    }
    
    header('Location: send_message.php');
    exit();
}

// Get all users except admin
$sql = "SELECT id, username, role FROM users WHERE id != ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message - DIF Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .message-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
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
        .form-control, .form-select {
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            padding: 0.75rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 35, 126, 0.25);
        }
        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: var(--border-radius);
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
        }
        .back-btn {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
        }
        .back-btn:hover {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        .alert {
            border-radius: var(--border-radius);
            border: none;
        }
        .alert-success {
            background-color: var(--success-color);
            color: var(--text-light);
        }
        .alert-danger {
            background-color: var(--danger-color);
            color: var(--text-light);
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
                    <i class="fas fa-envelope me-2"></i>Send Message
                </h2>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card message-card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-paper-plane me-2"></i>Compose New Message
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="receiver_id" class="form-label">
                                    <i class="fas fa-user me-2"></i>Select Recipient
                                </label>
                                <select class="form-select form-select-lg" id="receiver_id" name="receiver_id" required>
                                    <option value="">Choose a user...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo htmlspecialchars($user['username']); ?> 
                                            <span class="text-muted">(<?php echo $user['role']; ?>)</span>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment me-2"></i>Message
                                </label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                    placeholder="Type your message here..." required></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 