<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/database.php';

// Get today's date
$today = date('Y-m-d');

// Get total students count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
$stmt->execute();
$total_students = $stmt->fetchColumn();

// Get today's attendance count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$today_attendance = $stmt->fetchColumn();

// Get total users count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();
$total_users = $stmt->fetchColumn();

// Get recent attendance records
$stmt = $pdo->prepare("
    SELECT a.*, s.student_name, s.roll_number, s.class, s.section 
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    ORDER BY a.date DESC 
    LIMIT 5
");
$stmt->execute();
$recent_attendance = $stmt->fetchAll();

// Calculate attendance percentage
$attendance_percentage = $total_students > 0 ? round(($today_attendance / $total_students) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Dashboard</h2>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_students; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $today_attendance; ?></div>
                    <div class="stats-label">Today's Attendance</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stats-number"><?php echo $attendance_percentage; ?>%</div>
                    <div class="stats-label">Attendance Rate</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_users; ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="quick_scan.php" class="btn btn-primary w-100">
                                    <i class="fas fa-camera me-2"></i>Quick Scan
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="attendance.php" class="btn btn-success w-100">
                                    <i class="fas fa-qrcode me-2"></i>Mark Attendance
                                </a>
                            </div>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="col-md-3 mb-3">
                                <a href="students.php" class="btn btn-info w-100">
                                    <i class="fas fa-user-graduate me-2"></i>Manage Students
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="users.php" class="btn btn-warning w-100">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Attendance -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history me-2"></i>Recent Attendance
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>GR No</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_attendance as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['class'] . ' ' . $record['section']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($record['date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 DIF Attendance System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Developed by AR Developers</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 