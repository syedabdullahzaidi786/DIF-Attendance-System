<?php
// Start output buffering
ob_start();
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
    SELECT s.id, s.roll_number, s.student_name, s.class, s.section, s.father_name,
           COUNT(a.id) as present_days,
           COUNT(DISTINCT DATE(a.date)) as total_days
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id 
        AND MONTH(a.date) = ? 
        AND YEAR(a.date) = ?
        AND a.status != 'Holiday'
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

// Clear any previous output
ob_end_clean();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_report_' . date('Y-m-d') . '.csv"');

// Create a file pointer connected to PHP output
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, array(
    'Roll Number',
    'Student Name',
    'Father Name',
    'Class',
    'Section',
    'Present Days',
    'Total Days',
    'Attendance %'
));

// Add data rows
foreach ($records as $record) {
    $percentage = $record['total_days'] > 0 
        ? round(($record['present_days'] / $record['total_days']) * 100, 2)
        : 0;
    
    fputcsv($output, array(
        $record['roll_number'],
        $record['student_name'],
        $record['father_name'],
        $record['class'],
        $record['section'],
        $record['present_days'],
        $record['total_days'],
        $percentage . '%'
    ));
}

// Add summary
fputcsv($output, array(''));
fputcsv($output, array('Summary:'));
fputcsv($output, array('Total Students:', count($records)));
fputcsv($output, array('Report Generated On:', date('d/m/Y H:i:s')));

// Close the file pointer
fclose($output);
exit();
?> 