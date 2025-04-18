<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get all students with their attendance records
$stmt = $pdo->query("
    SELECT s.*, 
           COUNT(a.id) as total_attendance,
           SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
           SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
           SUM(CASE WHEN a.status = 'leave' THEN 1 ELSE 0 END) as leave_count
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id
    GROUP BY s.id
    ORDER BY s.class, s.section, s.roll_number
");
$students = $stmt->fetchAll();

// Get total students count
$total_students = count($students);

// Get students by class
$class_counts = [];
foreach ($students as $student) {
    $class = $student['class'] . '-' . $student['section'];
    if (!isset($class_counts[$class])) {
        $class_counts[$class] = 0;
    }
    $class_counts[$class]++;
}

// Get attendance details for a specific student
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $stmt = $pdo->prepare("
        SELECT a.*, s.name, s.roll_number, s.class, s.section
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.student_id = ?
        ORDER BY a.date DESC
    ");
    $stmt->execute([$student_id]);
    $attendance_records = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Records - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .attendance-stats {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .present {
            background-color: #d4edda;
            color: #155724;
        }
        .absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .leave {
            background-color: #fff3cd;
            color: #856404;
        }
        .attendance-date {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Student Attendance Records</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i>Students List with Attendance
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>GR Number</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Total Attendance</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Leave</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td><?php echo htmlspecialchars($student['section']); ?></td>
                                        <td><?php echo $student['total_attendance']; ?></td>
                                        <td><?php echo $student['present_count']; ?></td>
                                        <td><?php echo $student['absent_count']; ?></td>
                                        <td><?php echo $student['leave_count']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewAttendance(<?php echo $student['id']; ?>)">
                                                <i class="fas fa-calendar-check me-1"></i>View Records
                                            </button>
                                        </td>
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

    <!-- Attendance Records Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-check me-2"></i>Attendance Records
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="attendanceRecords">
                    <!-- Attendance records will be loaded here -->
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                order: [[2, 'asc'], [3, 'asc'], [0, 'asc']],
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search students..."
                }
            });
        });

        function viewAttendance(studentId) {
            $.ajax({
                url: 'get_student_attendance.php',
                type: 'GET',
                data: { student_id: studentId },
                success: function(response) {
                    $('#attendanceRecords').html(response);
                    new bootstrap.Modal(document.getElementById('attendanceModal')).show();
                },
                error: function() {
                    alert('Error loading attendance records');
                }
            });
        }
    </script>
</body>
</html> 