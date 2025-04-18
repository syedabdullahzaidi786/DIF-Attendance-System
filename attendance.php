<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get today's date
$today = date('Y-m-d');

// Get students who haven't been marked present today
$stmt = $pdo->prepare("
    SELECT s.* 
    FROM students s 
    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
    WHERE a.id IS NULL
    ORDER BY s.class, s.section, s.roll_number
");
$stmt->execute([$today]);
$unmarked_students = $stmt->fetchAll();

// Get today's attendance count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$stmt->execute([$today]);
$today_attendance = $stmt->fetchColumn();

// Get total students count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students");
$stmt->execute();
$total_students = $stmt->fetchColumn();

// Calculate attendance percentage
$attendance_percentage = $total_students > 0 ? round(($today_attendance / $total_students) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        #reader {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        .scan-status {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .success-icon {
            color: var(--success-color);
            font-size: 24px;
        }
        .error-icon {
            color: var(--danger-color);
            font-size: 24px;
        }
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card-background);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            display: none;
            z-index: 1000;
            min-width: 300px;
            text-align: center;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            color: var(--text-color);
            border: none;
            padding: 10px 15px;
            margin-right: 5px;
            border-radius: 5px;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: var(--text-on-primary);
        }
        .tab-content {
            padding: 20px 0;
        }
        .roll-number-form {
            max-width: 500px;
            margin: 0 auto;
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
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">Mark Attendance</h2>
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
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $today_attendance; ?></div>
                                    <div class="stats-label">Students Marked Today</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div class="stats-number"><?php echo count($unmarked_students); ?></div>
                                    <div class="stats-label">Students Remaining</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <div class="stats-number"><?php echo $attendance_percentage; ?>%</div>
                                    <div class="stats-label">Attendance Rate</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-qrcode me-2"></i>Mark Attendance
                    </div>
                            <div class="card-body">
                        <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="qr-tab" data-bs-toggle="tab" data-bs-target="#qr-content" type="button" role="tab" aria-controls="qr-content" aria-selected="true">
                                    <i class="fas fa-qrcode me-2"></i>QR Code
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="roll-tab" data-bs-toggle="tab" data-bs-target="#roll-content" type="button" role="tab" aria-controls="roll-content" aria-selected="false">
                                    <i class="fas fa-id-card me-2"></i>GR Number
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="attendanceTabsContent">
                            <!-- QR Code Tab -->
                            <div class="tab-pane fade show active" id="qr-content" role="tabpanel" aria-labelledby="qr-tab">
                                <div class="camera-controls text-center mb-3">
                                    <button id="startButton" class="btn btn-primary">
                                        <i class="fas fa-camera me-2"></i>Start Camera
                                    </button>
                                    <button id="stopButton" class="btn btn-danger" style="display:none;">
                                        <i class="fas fa-stop me-2"></i>Stop Camera
                                    </button>
                                </div>
                                <div class="scan-status" id="scan-status">Ready to scan</div>
                                <div id="reader"></div>
                                <div id="error-message" class="text-danger text-center mt-2"></div>
                            </div>
                            
                            <!-- Roll Number Tab -->
                            <div class="tab-pane fade" id="roll-content" role="tabpanel" aria-labelledby="roll-tab">
                                <div class="roll-number-form">
                                    <div class="mb-3">
                                        <label for="rollNumber" class="form-label">Enter GR Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="rollNumber" placeholder="Enter Student GR Number">
                                            <button class="btn btn-primary" type="button" id="markByRollBtn">
                                                <i class="fas fa-check me-2"></i>Mark Attendance
                                            </button>
                                        </div>
                                        <div id="roll-error-message" class="text-danger text-center mt-2"></div>
                                    </div>
                                </div>
                            </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i>Today's Attendance
                    </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="attendanceList">
                                            <?php
                                            $stmt = $pdo->prepare("
                                                SELECT a.*, s.student_name, s.class, s.section
                                                FROM attendance a
                                                JOIN students s ON a.student_id = s.id
                                                WHERE a.date = ?
                                                ORDER BY a.created_at DESC
                                            ");
                                            $stmt->execute([$today]);
                                            while ($row = $stmt->fetch()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['class'] . "-" . $row['section']) . "</td>";
                                                echo "<td><span class='badge bg-success'>Present</span></td>";
                                                echo "<td>" . date('h:i A', strtotime($row['created_at'])) . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        const errorMessage = document.getElementById('error-message');
        const rollErrorMessage = document.getElementById('roll-error-message');
        const startButton = document.getElementById('startButton');
        const stopButton = document.getElementById('stopButton');
        const scanStatus = document.getElementById('scan-status');
        const markByRollBtn = document.getElementById('markByRollBtn');
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
                    // Add new row to attendance list
                                const newRow = `
                                    <tr>
                                        <td>${data.student.name}</td>
                                        <td>${data.student.class}-${data.student.section}</td>
                            <td><span class="badge bg-success">Present</span></td>
                            <td>${new Date().toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
                                    </tr>
                                `;
                    document.getElementById('attendanceList').insertAdjacentHTML('afterbegin', newRow);
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
                    // Add new row to attendance list
                    const newRow = `
                        <tr>
                            <td>${data.student.name}</td>
                            <td>${data.student.class}-${data.student.section}</td>
                            <td><span class="badge bg-success">Present</span></td>
                            <td>${new Date().toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
                        </tr>
                    `;
                    document.getElementById('attendanceList').insertAdjacentHTML('afterbegin', newRow);
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

        // Event listeners for camera controls
        startButton.addEventListener('click', startScanner);
        stopButton.addEventListener('click', stopScanner);
        markByRollBtn.addEventListener('click', markAttendanceByRoll);
        
        // Add event listener for Enter key in roll number input
        document.getElementById('rollNumber').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                markAttendanceByRoll();
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