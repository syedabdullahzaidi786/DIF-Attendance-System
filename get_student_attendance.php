<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['student_id'])) {
    exit('Unauthorized access');
}

$student_id = $_GET['student_id'];

// Get student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    exit('Student not found');
}

// Get attendance records
$stmt = $pdo->prepare("
    SELECT a.*, 
           DATE_FORMAT(a.date, '%d %b %Y') as formatted_date,
           DATE_FORMAT(a.created_at, '%h:%i %p') as formatted_time
    FROM attendance a
    WHERE a.student_id = ?
    ORDER BY a.date DESC, a.created_at DESC
");
$stmt->execute([$student_id]);
$attendance_records = $stmt->fetchAll();

// Calculate statistics
$total_days = count($attendance_records);
$present_days = 0;
$absent_days = 0;
$leave_days = 0;

foreach ($attendance_records as $record) {
    switch($record['status']) {
        case 'present':
            $present_days++;
            break;
        case 'absent':
            $absent_days++;
            break;
        case 'leave':
            $leave_days++;
            break;
    }
}

$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
?>

<div class="student-info mb-4">
    <h4><?php echo htmlspecialchars($student['name']); ?></h4>
    <p class="text-muted">
        Roll No: <?php echo htmlspecialchars($student['roll_number']); ?> | 
        Class: <?php echo htmlspecialchars($student['class']); ?>-<?php echo htmlspecialchars($student['section']); ?>
    </p>
</div>

<div class="attendance-summary mb-4">
    <div class="row">
        <div class="col-md-3">
            <div class="attendance-stats present">
                <h5>Present Days</h5>
                <h3><?php echo $present_days; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="attendance-stats absent">
                <h5>Absent Days</h5>
                <h3><?php echo $absent_days; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="attendance-stats" style="background-color: #fff3cd; color: #856404;">
                <h5>Leave Days</h5>
                <h3><?php echo $leave_days; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="attendance-stats" style="background-color: #cce5ff; color: #004085;">
                <h5>Attendance %</h5>
                <h3><?php echo $attendance_percentage; ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="attendance-records">
    <h5 class="mb-3">Detailed Attendance Records</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($attendance_records as $record): ?>
                <tr>
                    <td class="attendance-date"><?php echo $record['formatted_date']; ?></td>
                    <td><?php echo $record['formatted_time']; ?></td>
                    <td>
                        <?php
                        $status_class = '';
                        switch($record['status']) {
                            case 'present':
                                $status_class = 'bg-success';
                                break;
                            case 'absent':
                                $status_class = 'bg-danger';
                                break;
                            case 'leave':
                                $status_class = 'bg-warning text-dark';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($record['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($record['notes'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> 