<?php
if (!isset($_SESSION)) {
    session_start();
}

// Get user role from session
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/images/dif_logo.png" alt="DIF Logo" style="width: 35px; height: 35px; object-fit: contain; margin-right: 10px;">
            <span>DIF Sec School</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <?php if ($user_role == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>" href="attendance.php">
                        <i class="fas fa-qrcode me-1"></i>Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'quick_scan.php' ? 'active' : ''; ?>" href="quick_scan.php">
                        <i class="fas fa-camera me-1"></i>Quick Scan
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'update_attendance.php' ? 'active' : ''; ?>" href="update_attendance.php">
                        <i class="fas fa-edit me-1"></i>Update Attendance
                    </a>
                </li>
                <?php if ($user_role == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mark_all_attendance.php' ? 'active' : ''; ?>" href="mark_all_attendance.php">
                        <i class="fas fa-calendar-check me-1"></i>Mark All
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'update_all_attendance.php' ? 'active' : ''; ?>" href="update_all_attendance.php">
                        <i class="fas fa-calendar-plus me-1"></i>Update All
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>" href="students.php">
                        <i class="fas fa-user-graduate me-1"></i>Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'student_attendance.php' ? 'active' : ''; ?>" href="student_attendance.php">
                        <i class="fas fa-clipboard-check me-1"></i>Student Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                        <i class="fas fa-users me-1"></i>Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : ''; ?>" href="admin_reports.php">
                        <i class="fas fa-chart-line me-1"></i>Admin Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'generate_id_card.php' ? 'active' : ''; ?>" href="generate_id_card.php">
                        <i class="fas fa-id-card me-1"></i>ID Cards
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['username']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="reports.php"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav> 