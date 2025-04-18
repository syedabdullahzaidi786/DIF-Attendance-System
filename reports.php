<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get filter parameters
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$class = $_GET['class'] ?? '';
$section = $_GET['section'] ?? '';

// Build query
$query = "
    SELECT s.id, s.roll_number, s.student_name, s.class, s.section,
           COUNT(a.id) as present_days,
           COUNT(DISTINCT DATE(a.date)) as total_days
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id 
        AND MONTH(a.date) = ? 
        AND YEAR(a.date) = ?
        AND a.status != 'Holiday'  -- Exclude holidays
    WHERE 1=1
";

$params = [$month, $year];

if ($class) {
    $query .= " AND s.class = ?";
    $params[] = $class;
}
if ($section) {
    $query .= " AND s.section = ?";
    $params[] = $section;
}

$query .= " GROUP BY s.id ORDER BY s.class, s.section, s.roll_number";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Get unique classes and sections for filters
$stmt = $pdo->query("SELECT DISTINCT class, section FROM students ORDER BY class, section");
$filters = $stmt->fetchAll();

// Calculate statistics
$total_students = count($records);
$total_present = array_sum(array_column($records, 'present_days'));
$total_days = array_sum(array_column($records, 'total_days'));
$overall_attendance = $total_present > 0 ? round(($total_present / $total_days) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports - School Attendance System</title>
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
                <h2 class="page-title">
                    <i class="fas fa-chart-bar me-2"></i>Attendance Reports
                </h2>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_students; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_present; ?></div>
                    <div class="stats-label">Total Present Days</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_days; ?></div>
                    <div class="stats-label">Total Days</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-number"><?php echo $overall_attendance; ?>%</div>
                    <div class="stats-label">Overall Attendance</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-filter me-2"></i>Filter Options
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Month</label>
                                <select name="month" class="form-select">
                                    <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" 
                                                <?= $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    <?php for($i = date('Y'); $i >= date('Y')-2; $i--): ?>
                                        <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Class</label>
                                <select name="class" class="form-select">
                                    <option value="">All Classes</option>
                                    <?php foreach(array_unique(array_column($filters, 'class')) as $c): ?>
                                        <option value="<?= $c ?>" <?= $class == $c ? 'selected' : '' ?>>
                                            <?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select name="section" class="form-select">
                                    <option value="">All Sections</option>
                                    <?php foreach(array_unique(array_column($filters, 'section')) as $s): ?>
                                        <option value="<?= $s ?>" <?= $section == $s ? 'selected' : '' ?>>
                                            <?= $s ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Apply Filters
                                </button>
                                <a href="reports.php" class="btn btn-secondary">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Export to CSV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-table me-2"></i>Attendance Records
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="reportsTable">
                                <thead>
                                    <tr>
                                        <th>GR Number</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Present Days</th>
                                        <th>Total Days</th>
                                        <th>Attendance %</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($record['roll_number']) ?></td>
                                            <td><?= htmlspecialchars($record['student_name']) ?></td>
                                            <td><?= htmlspecialchars($record['class']) ?></td>
                                            <td><?= htmlspecialchars($record['section']) ?></td>
                                            <td><?= $record['present_days'] ?></td>
                                            <td><?= $record['total_days'] ?: 0 ?></td>
                                            <td>
                                                <?php
                                                $percentage = $record['total_days'] > 0 
                                                    ? round(($record['present_days'] / $record['total_days']) * 100, 2)
                                                    : 0;
                                                $color = $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= $percentage ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewDetails(<?= $record['id'] ?>, '<?= $month ?>', '<?= $year ?>')">
                                                    <i class="fas fa-eye"></i>
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

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-graduate me-2"></i>Student Attendance Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="studentDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">© <?php echo date('Y'); ?> School Attendance System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#reportsTable').DataTable({
                order: [[1, 'asc']],
                pageLength: 10,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records..."
                }
            });
        });

        function viewDetails(studentId, month, year) {
            $.ajax({
                url: 'get_student_attendance.php',
                type: 'GET',
                data: { 
                    student_id: studentId,
                    month: month,
                    year: year
                },
                success: function(response) {
                    $('#studentDetailsContent').html(response);
                    $('#studentDetailsModal').modal('show');
                },
                error: function() {
                    alert('Error loading student details');
                }
            });
        }

        function exportToExcel() {
            window.location.href = 'export_attendance.php?' + window.location.search.substring(1);
        }
    </script>
</body>
</html> 