<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle student deletion
if (isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    header("Location: students.php?msg=deleted");
    exit();
}

// Handle student update
if (isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $section = $_POST['section'];
    
    $stmt = $pdo->prepare("UPDATE students SET roll_number = ?, name = ?, class = ?, section = ? WHERE id = ?");
    $stmt->execute([$roll_number, $name, $class, $section, $student_id]);
    header("Location: students.php?msg=updated");
    exit();
}

// Get all students
$stmt = $pdo->query("SELECT * FROM students ORDER BY class, section, roll_number");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Students Management</h2>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i>Student Statistics
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $total_students; ?></div>
                                    <div class="stats-label">Total Students</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-chalkboard"></i>
                                    </div>
                                    <div class="stats-number"><?php echo count($class_counts); ?></div>
                                    <div class="stats-label">Total Classes</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $total_students; ?></div>
                                    <div class="stats-label">ID Cards Generated</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>Students List
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-plus me-2"></i>Add New Student
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>Student deleted successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>Student updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-hover" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>GR Number</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>QR Code</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td><?php echo htmlspecialchars($student['section']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="showQRCode('<?php echo $student['qr_code']; ?>')">
                                                <i class="fas fa-qrcode"></i> View QR
                                            </button>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary" onclick="editStudent(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php if($_SESSION['role'] == 'admin'): ?>
                                                <a href="generate_id_card.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
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

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Add New Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_student.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="roll_number" class="form-label">GR Number</label>
                            <input type="text" class="form-control" id="roll_number" name="roll_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="class" class="form-label">Class</label>
                            <input type="text" class="form-control" id="class" name="class" required>
                        </div>
                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="section" name="section" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>Edit Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="students.php" method="POST">
                    <input type="hidden" name="student_id" id="edit_student_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_roll_number" class="form-label">GR Number</label>
                            <input type="text" class="form-control" id="edit_roll_number" name="roll_number" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_class" class="form-label">Class</label>
                            <input type="text" class="form-control" id="edit_class" name="class" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_section" class="form-label">Section</label>
                            <input type="text" class="form-control" id="edit_section" name="section" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_student" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-qrcode me-2"></i>Student QR Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrcode"></div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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

        function showQRCode(qrCode) {
            $('#qrcode').empty();
            new QRCode(document.getElementById("qrcode"), qrCode);
            $('#qrModal').modal('show');
        }

        function editStudent(studentId) {
            // Fetch student data using AJAX
            $.ajax({
                url: 'get_student.php',
                type: 'GET',
                data: { id: studentId },
                success: function(response) {
                    const student = JSON.parse(response);
                    $('#edit_student_id').val(student.id);
                    $('#edit_roll_number').val(student.roll_number);
                    $('#edit_name').val(student.name);
                    $('#edit_class').val(student.class);
                    $('#edit_section').val(student.section);
                    $('#editStudentModal').modal('show');
                }
            });
        }

        function deleteStudent(studentId) {
            if (confirm('Are you sure you want to delete this student?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'students.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_id';
                input.value = studentId;
                
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_student';
                deleteInput.value = '1';
                
                form.appendChild(input);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 