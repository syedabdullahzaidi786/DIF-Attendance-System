<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}

require_once 'config/database.php';

// Handle CSV download
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    // Get filter parameters
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // Build the query
    $query = "
        SELECT 
            s.roll_number as 'GR No',
            s.student_name as 'Student Name',
            CONCAT(s.class, ' ', s.section) as 'Class',
            a.date as 'Date',
            u.username as 'Marked By',
            a.created_at as 'Marked At'
        FROM attendance a 
        JOIN students s ON a.student_id = s.id 
        JOIN users u ON a.marked_by = u.id
        WHERE 1=1
    ";

    $params = [];

    if ($user_id) {
        $query .= " AND a.marked_by = ?";
        $params[] = $user_id;
    }

    if ($start_date) {
        $query .= " AND a.date >= ?";
        $params[] = $start_date;
    }

    if ($end_date) {
        $query .= " AND a.date <= ?";
        $params[] = $end_date;
    }

    $query .= " ORDER BY a.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    if (count($records) > 0) {
        fputcsv($output, array_keys($records[0]));
    }
    
    // Add data
    foreach ($records as $record) {
        fputcsv($output, $record);
    }
    
    fclose($output);
    exit();
}

// Get all users for the filter dropdown
$stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->fetchAll();

// Get filter parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the query
$query = "
    SELECT 
        a.*,
        s.student_name,
        s.roll_number,
        s.class,
        s.section,
        u.username as marked_by,
        a.created_at as marked_at
    FROM attendance a 
    JOIN students s ON a.student_id = s.id 
    JOIN users u ON a.marked_by = u.id
    WHERE 1=1
";

$params = [];

if ($user_id) {
    $query .= " AND a.marked_by = ?";
    $params[] = $user_id;
}

if ($start_date) {
    $query .= " AND a.date >= ?";
    $params[] = $start_date;
}

if ($end_date) {
    $query .= " AND a.date <= ?";
    $params[] = $end_date;
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Admin Reports</h2>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-2"></i>Filter Records
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo ($user_id == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['download' => 'csv'])); ?>" class="btn btn-success w-100">
                            <i class="fas fa-download me-2"></i>Download CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Records Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>Attendance Records
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>GR No</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Marked By</th>
                                <th>Marked At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($records) > 0): ?>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['class'] . ' ' . $record['section']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['marked_by']); ?></td>
                                        <td><?php echo date('d M Y H:i:s', strtotime($record['marked_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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