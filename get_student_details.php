<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$student_id = $_GET['student_id'] ?? 0;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get student details
$stmt = $pdo->prepare("
    SELECT s.*, 
           COUNT(a.id) as present_days,
           COUNT(DISTINCT DATE(a.date)) as total_days
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id 
        AND MONTH(a.date) = ? 
        AND YEAR(a.date) = ?
    WHERE s.id = ?
    GROUP BY s.id
");
$stmt->execute([$month, $year, $student_id]);
$student = $stmt->fetch();

if (!$student) {
    echo "Student not found";
    exit();
}

// Get daily attendance for the month
$stmt = $pdo->prepare("
    SELECT a.date, a.status, a.created_at, u.username as marked_by
    FROM attendance a
    JOIN users u ON a.marked_by = u.id
    WHERE a.student_id = ? 
    AND MONTH(a.date) = ? 
    AND YEAR(a.date) = ?
    ORDER BY a.date DESC
");
$stmt->execute([$student_id, $month, $year]);
$attendance_records = $stmt->fetchAll();

// Calculate attendance percentage
$percentage = $student['total_days'] > 0 
    ? round(($student['present_days'] / $student['total_days']) * 100, 2)
    : 0;
$color = $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
?>

<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Student Information</h5>
            <table class="table table-bordered">
                <tr>
                    <th>GR Number</th>
                    <td><?= htmlspecialchars($student['roll_number']) ?></td>
                </tr>
                <tr>
                    <th>Student Name</th>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                </tr>
                <tr>
                    <th>Class</th>
                    <td><?= htmlspecialchars($student['class']) ?></td>
                </tr>
                <tr>
                    <th>Section</th>
                    <td><?= htmlspecialchars($student['section']) ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5>Attendance Summary</h5>
            <table class="table table-bordered">
                <tr>
                    <th>Present Days</th>
                    <td><?= $student['present_days'] ?></td>
                </tr>
                <tr>
                    <th>Total Days</th>
                    <td><?= $student['total_days'] ?: 0 ?></td>
                </tr>
                <tr>
                    <th>Attendance %</th>
                    <td>
                        <span class="badge bg-<?= $color ?>">
                            <?= $percentage ?>%
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <h5>Daily Attendance Record</h5>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Marked By</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($record['date'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $record['status'] == 'present' ? 'success' : 'danger' ?>">
                                <?= ucfirst($record['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($record['marked_by']) ?></td>
                        <td><?= date('h:i A', strtotime($record['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($attendance_records)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No attendance records found for this month</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 