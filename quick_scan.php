<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get today's date
$today = date('Y-m-d');

// Get count of students marked present today
require_once 'config/database.php';
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$marked_count = $stmt->fetchColumn();

// Get total student count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
$stmt->execute();
$total_students = $stmt->fetchColumn();

// Calculate remaining students
$remaining_students = $total_students - $marked_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Attendance Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #0d47a1;
            --secondary-color: #64B5F6;
            --text-light: #ffffff;
            --text-dark: #333333;
            --bg-light: #f8f9fa;
            --bg-dark: #1a237e;
            --accent-color: #81c784;
            --text-on-primary: #ffffff;
            --background-color: #f5f5f5;
            --card-background: #ffffff;
            --border-radius: 10px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --info-color: #2196f3;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 60px;
        }
        
        .navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark)) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            min-height: 60px;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            color: var(--text-light) !important;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 0;
            height: 45px;
        }
        
        .container {
            flex: 1;
            padding: 20px;
        }
        
        .card {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            font-weight: 600;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }
        
        #reader {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
        
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-background);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: 0 0 30px rgba(0,0,0,0.3);
            display: none;
            z-index: 1000;
            min-width: 350px;
            text-align: center;
            border-top: 5px solid var(--primary-color);
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            display: none;
            z-index: 999;
            backdrop-filter: blur(3px);
        }
        
        #popup-icon {
            margin-bottom: 15px;
        }
        
        #popup-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        #popup-message {
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        #error-message, #roll-error-message {
            color: var(--danger-color);
            margin-top: 10px;
            text-align: center;
        }
        
        .camera-controls {
            text-align: center;
            margin: 20px 0;
        }
        
        .scan-status {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            background-color: var(--bg-light);
        }
        
        .success-icon {
            color: var(--success-color);
            font-size: 24px;
        }
        
        .error-icon {
            color: var(--danger-color);
            font-size: 24px;
        }
        
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .nav-tabs .nav-link {
            color: var(--text-dark);
            border: none;
            padding: 10px 15px;
            margin-right: 5px;
            border-radius: 5px;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: var(--text-light);
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .roll-number-form, .mark-attendance-form {
            max-width: 500px;
            margin: 0 auto;
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .form-control, .form-select {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.25rem rgba(83, 75, 174, 0.25);
        }
        
        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .input-group .form-control {
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }
        
        .input-group .btn {
            padding: 10px 20px;
        }
        
        .form-check {
            margin-bottom: 10px;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .footer {
            background: var(--bg-light);
            padding: 1rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .footer p {
            color: var(--text-dark);
            opacity: 0.8;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 3px;
        }
        
        .attendance-stats {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .attendance-stats .badge {
            font-size: 1rem;
            padding: 8px 15px;
            background-color: var(--primary-color);
        }
        
        /* Tab pane styles */
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Button group styles */
        .btn-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .btn-group .btn {
            border-radius: 0;
            padding: 10px 20px;
        }
        
        .btn-group .btn:first-child {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }
        
        .btn-group .btn:last-child {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        
        /* Stats card styles */
        .stats-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            background-color: var(--primary-light);
            color: var(--text-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .stats-icon i {
            font-size: 24px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: var(--text-dark);
            font-size: 1rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Quick Attendance Scanner</h2>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-qrcode me-2"></i>Attendance Statistics
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $marked_count; ?></div>
                                    <div class="stats-label">Students Marked Today</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $remaining_students; ?></div>
                                    <div class="stats-label">Students Remaining</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-qrcode me-2"></i>Mark Attendance
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-4">
                            <div class="btn-group" role="group" aria-label="Attendance methods">
                                <button type="button" class="btn btn-primary active" id="qr-btn" onclick="showTab('qr-content')">
                                    <i class="fas fa-qrcode me-2"></i>QR Code
                                </button>
                                <button type="button" class="btn btn-primary" id="roll-btn" onclick="showTab('roll-content')">
                                    <i class="fas fa-id-card me-2"></i>Roll Number
                                </button>
                                <button type="button" class="btn btn-primary" id="mark-btn" onclick="showTab('mark-content')">
                                    <i class="fas fa-check-circle me-2"></i>Mark Attendance
                                </button>
                            </div>
                        </div>
                        
                        <div id="tab-content">
                            <!-- QR Code Tab -->
                            <div class="tab-pane active" id="qr-content">
                                <div class="camera-controls">
                                    <button id="startButton" class="btn btn-primary">
                                        <i class="fas fa-camera me-2"></i>Start Camera
                                    </button>
                                    <button id="stopButton" class="btn btn-danger" style="display:none;">
                                        <i class="fas fa-stop me-2"></i>Stop Camera
                                    </button>
                                </div>
                                <div class="scan-status" id="scan-status">Ready to scan</div>
                                <div id="reader"></div>
                                <div id="error-message"></div>
                            </div>
                            
                            <!-- Roll Number Tab -->
                            <div class="tab-pane" id="roll-content">
                                <div class="roll-number-form">
                                    <div class="mb-3">
                                        <label for="rollNumber" class="form-label">Enter Roll Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="rollNumber" placeholder="Enter student roll number">
                                            <button class="btn btn-primary" type="button" id="markByRollBtn">
                                                <i class="fas fa-check me-2"></i>Mark Attendance
                                            </button>
                                        </div>
                                        <div id="roll-error-message"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mark Attendance Tab -->
                            <div class="tab-pane" id="mark-content">
                                <div class="mark-attendance-form">
                                    <div class="mb-3">
                                        <label for="studentSearch" class="form-label">Search Student by Roll Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="studentSearch" placeholder="Enter roll number to search">
                                            <button class="btn btn-primary" type="button" id="searchStudentBtn">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                        </div>
                                        <div id="studentSearchResult" class="mt-2"></div>
                                        <input type="hidden" id="studentId" value="">
                                    </div>
                                    <div class="mb-3">
                                        <label for="attendanceDate" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="attendanceStatus" id="statusPresent" value="present" checked>
                                            <label class="form-check-label" for="statusPresent">Present</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="attendanceStatus" id="statusAbsent" value="absent">
                                            <label class="form-check-label" for="statusAbsent">Absent</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="attendanceStatus" id="statusLeave" value="leave">
                                            <label class="form-check-label" for="statusLeave">Leave</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attendanceNotes" class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="attendanceNotes" rows="2"></textarea>
                                    </div>
                                    <div class="text-center">
                                        <button class="btn btn-primary" type="button" id="markAttendanceBtn">
                                            <i class="fas fa-check me-2"></i>Mark Attendance
                                        </button>
                                    </div>
                                    <div id="mark-error-message" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="overlay"></div>
    <div class="popup">
        <div id="popup-icon"></div>
        <h4 id="popup-title"></h4>
        <p id="popup-message"></p>
        <button class="btn btn-primary" onclick="closePopup()">Close</button>
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
    <script>
        // Function to show selected tab
        function showTab(tabId) {
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab pane
            document.getElementById(tabId).classList.add('active');
            
            // Update button states
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Activate the clicked button
            document.getElementById(tabId.replace('-content', '-btn')).classList.add('active');
        }

        let html5QrcodeScanner = null;
        const errorMessage = document.getElementById('error-message');
        const rollErrorMessage = document.getElementById('roll-error-message');
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');
        const scanStatus = document.getElementById('scan-status');
        const markByRollBtn = document.getElementById('markByRollBtn');
        const markAttendanceBtn = document.getElementById('markAttendanceBtn');
        const searchStudentBtn = document.getElementById('searchStudentBtn');
        let lastScannedCode = '';
        let scanTimeout = null;

        function showError(message, element = errorMessage) {
            element.textContent = message;
            element.style.display = 'block';
        }

        function hideError(element = errorMessage) {
            element.style.display = 'none';
        }

        function updateScanStatus(status, isError = false) {
            scanStatus.textContent = status;
            scanStatus.className = 'scan-status ' + (isError ? 'text-danger' : 'text-success');
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Prevent duplicate scans of the same code
            if (decodedText === lastScannedCode) {
                return;
            }
            
            // Update last scanned code
            lastScannedCode = decodedText;
            
            // Update scan status
            updateScanStatus('Scanning QR code...');
            
            // Clear any existing timeout
            if (scanTimeout) {
                clearTimeout(scanTimeout);
            }
            
            // Send attendance data
            fetch('mark_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qr_code: decodedText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateScanStatus('Attendance marked successfully!');
                } else {
                    updateScanStatus('Error: ' + data.message, true);
                }
                showPopup(data.success, data.message, data.student);
            })
            .catch(error => {
                updateScanStatus('Error connecting to server', true);
                showPopup(false, 'Error marking attendance: ' + error.message, null);
            });
            
            // Reset last scanned code after 5 seconds
            scanTimeout = setTimeout(() => {
                lastScannedCode = '';
            }, 5000);
        }

        function onScanFailure(error) {
            // Handle scan failure, usually better to ignore and keep scanning
            console.warn(`QR code scanning failed: ${error}`);
        }

        function showPopup(success, message, student) {
            const title = document.getElementById('popup-title');
            const messageEl = document.getElementById('popup-message');
            const overlay = document.querySelector('.overlay');
            const popup = document.querySelector('.popup');
            const popupIcon = document.getElementById('popup-icon');

            // Set icon
            popupIcon.innerHTML = success 
                ? '<span class="success-icon">✓</span>' 
                : '<span class="error-icon">✗</span>';

            title.textContent = success ? 'Success!' : 'Error';
            title.className = success ? 'text-success' : 'text-danger';

            let messageText = message;
            if (student) {
                messageText += `<br><br>Student: ${student.name}<br>Class: ${student.class} ${student.section}`;
            }
            messageEl.innerHTML = messageText;

            overlay.style.display = 'block';
            popup.style.display = 'block';
        }

        function closePopup() {
            const overlay = document.querySelector('.overlay');
            const popup = document.querySelector('.popup');
            overlay.style.display = 'none';
            popup.style.display = 'none';
            
            // Reset scan status
            updateScanStatus('Ready to scan');
            
            // Restart scanner
            startScanner();
        }

        function startScanner() {
            try {
                hideError();
                
                // Check if browser supports getUserMedia
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error("Your browser doesn't support camera access. Please use a modern browser like Chrome, Firefox, or Edge.");
                }
                
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: 250,
                        aspectRatio: 1.0,
                        showTorchButtonIfSupported: true,
                        rememberLastUsedCamera: true
                    }
                );
                
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
                startButton.style.display = 'none';
                stopButton.style.display = 'inline-block';
                updateScanStatus('Camera started - Ready to scan');
            } catch (error) {
                showError(error.message);
                updateScanStatus('Error starting camera', true);
                console.error("Scanner error:", error);
            }
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }
            startButton.style.display = 'inline-block';
            stopButton.style.display = 'none';
            document.getElementById('reader').innerHTML = '';
            updateScanStatus('Camera stopped');
        }
        
        // Function to mark attendance by roll number
        function markAttendanceByRoll() {
            const rollNumber = document.getElementById('rollNumber').value.trim();
            
            if (!rollNumber) {
                showError('Please enter a roll number', rollErrorMessage);
                return;
            }
            
            hideError(rollErrorMessage);
            
            // Send attendance data
            fetch('mark_attendance_by_roll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    roll_number: rollNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the input field
                    document.getElementById('rollNumber').value = '';
                } else {
                    showError(data.message, rollErrorMessage);
                }
                showPopup(data.success, data.message, data.student);
            })
            .catch(error => {
                showError('Error connecting to server: ' + error.message, rollErrorMessage);
                showPopup(false, 'Error marking attendance: ' + error.message, null);
            });
        }

        // Function to search student by roll number
        function searchStudent() {
            const rollNumber = document.getElementById('studentSearch').value.trim();
            
            if (!rollNumber) {
                showError('Please enter a roll number', document.getElementById('mark-error-message'));
                return;
            }
            
            hideError(document.getElementById('mark-error-message'));
            
            // Send search request
            fetch('search_student.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    roll_number: rollNumber
                })
            })
            .then(response => response.json())
            .then(data => {
                const searchResult = document.getElementById('studentSearchResult');
                
                if (data.success) {
                    // Display student info
                    searchResult.innerHTML = `
                        <div class="alert alert-success">
                            <strong>${data.student.name}</strong><br>
                            Class: ${data.student.class} ${data.student.section}<br>
                            Roll Number: ${data.student.roll_number}
                        </div>
                    `;
                    
                    // Set student ID for attendance marking
                    document.getElementById('studentId').value = data.student.id;
                } else {
                    // Display error
                    searchResult.innerHTML = `
                        <div class="alert alert-danger">
                            ${data.message}
                        </div>
                    `;
                    document.getElementById('studentId').value = '';
                }
            })
            .catch(error => {
                showError('Error connecting to server: ' + error.message, document.getElementById('mark-error-message'));
                document.getElementById('studentId').value = '';
            });
        }

        // Function to mark attendance manually
        function markAttendanceManually() {
            const studentId = document.getElementById('studentId').value;
            const attendanceDate = document.getElementById('attendanceDate').value;
            const attendanceStatus = document.querySelector('input[name="attendanceStatus"]:checked').value;
            const attendanceNotes = document.getElementById('attendanceNotes').value;
            
            if (!studentId) {
                showError('Please search for a student first', document.getElementById('mark-error-message'));
                return;
            }
            
            if (!attendanceDate) {
                showError('Please select a date', document.getElementById('mark-error-message'));
                return;
            }
            
            hideError(document.getElementById('mark-error-message'));
            
            // Send attendance data
            fetch('mark_attendance_manual.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    student_id: studentId,
                    date: attendanceDate,
                    status: attendanceStatus,
                    notes: attendanceNotes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the form
                    document.getElementById('studentSearch').value = '';
                    document.getElementById('studentSearchResult').innerHTML = '';
                    document.getElementById('studentId').value = '';
                    document.getElementById('attendanceNotes').value = '';
                    document.getElementById('statusPresent').checked = true;
                } else {
                    showError(data.message, document.getElementById('mark-error-message'));
                }
                showPopup(data.success, data.message, data.student);
            })
            .catch(error => {
                showError('Error connecting to server: ' + error.message, document.getElementById('mark-error-message'));
                showPopup(false, 'Error marking attendance: ' + error.message, null);
            });
        }

        // Event listeners for camera controls
        startButton.addEventListener('click', startScanner);
        stopButton.addEventListener('click', stopScanner);
        markByRollBtn.addEventListener('click', markAttendanceByRoll);
        markAttendanceBtn.addEventListener('click', markAttendanceManually);
        searchStudentBtn.addEventListener('click', searchStudent);
        
        // Add event listener for Enter key in roll number input
        document.getElementById('rollNumber').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                markAttendanceByRoll();
            }
        });
        
        // Add event listener for Enter key in student search input
        document.getElementById('studentSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchStudent();
            }
        });

        // Check if the page is loaded over HTTPS or localhost
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            showError("Camera access requires HTTPS. Please access this page over HTTPS.");
            startButton.disabled = true;
        }
    </script>
</body>
</html> 