<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Get student ID from URL parameter
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get student details
$student = null;
if ($student_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
}

// Get all students for dropdown
$stmt = $pdo->prepare("SELECT id, student_name, class, section, roll_number FROM students ORDER BY class, section, student_name");
$stmt->execute();
$students = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['roll_number'])) {
    $roll_number = trim($_POST['roll_number']);
    if (!empty($roll_number)) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_number = ?");
        $stmt->execute([$roll_number]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $_SESSION['error'] = "No student found with Roll Number: " . htmlspecialchars($roll_number);
        }
    }
}

// Get statistics
$total_students = count($students);
$id_cards_generated = 0;
foreach ($students as $s) {
    if (!empty($s['qr_code'])) {
        $id_cards_generated++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Student ID Card - School Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <!-- Primary QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Fallback QR Code Library -->
    <script>
        window.addEventListener('error', function(e) {
            if (e.target.src && e.target.src.indexOf('qrcode.min.js') !== -1) {
                var fallback = document.createElement('script');
                fallback.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js';
                document.head.appendChild(fallback);
            }
        }, true);
    </script>
    <style>
        .id-card {
            width: 85.6mm;
            height: 53.98mm;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        .card-header {
            background: linear-gradient(90deg, #1a237e 0%, #0d47a1 100%);
            height: 20mm;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 15px;
        }
        
        /* Custom style for the card headers in the main interface */
        .card > .card-header {
            background: linear-gradient(90deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            font-weight: bold;
        }

        .school-logo {
            width: 45px;
            height: 45px;
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 50%;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .school-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: none;
        }

        .header-text {
            color: white;
            margin-left: 10px;
            text-align: center;
            z-index: 2;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .header-subtitle {
            font-size: 12px;
            opacity: 0.9;
            margin: 2px 0 0 0;
        }

        .card-content {
            padding: 12px 15px;
            position: relative;
            z-index: 2;
        }
        
        .student-info {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 8px 12px;
            font-size: 12px;
        }
        
        .label {
            color: #1a237e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .value {
            color: #333;
            font-weight: 500;
        }
        
        .qr-code {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 4px;
            padding: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .qr-code img {
            max-width: 100%;
            max-height: 100%;
            display: block;
        }

        .card-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.02;
            background-image: repeating-linear-gradient(45deg, #1a237e 0%, #1a237e 1%, transparent 1%, transparent 50%);
            background-size: 10px 10px;
            z-index: 1;
        }

        .wave-pattern {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(45deg, #1a237e 0%, #0d47a1 100%);
            opacity: 0.03;
            transform: skewY(-5deg) translateY(50%);
        }

        .label i {
            margin-right: 4px;
            color: #1a237e;
            font-size: 11px;
            width: 14px;
            text-align: center;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            
            .id-card, .id-card * {
                visibility: visible;
            }
            
            .id-card {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0;
                box-shadow: none;
            }

            .card-header {
                background: linear-gradient(90deg, #1a237e 0%, #0d47a1 100%) !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .school-logo {
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .school-logo img {
                filter: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .label, .label i {
                color: #1a237e !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .wave-pattern {
                background: linear-gradient(45deg, #1a237e 0%, #0d47a1 100%) !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .qr-code {
                box-shadow: none !important;
            }
            
            .print-button, .card-header, .card-body > :not(#idCardContainer), 
            #noStudentSelected, .container > :not(.row:last-child), 
            .footer {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title">
                    <i class="fas fa-id-card me-2"></i>Generate ID Card
                </h2>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_students; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="stats-number"><?php echo $id_cards_generated; ?></div>
                    <div class="stats-label">ID Cards Generated</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Student Selection Form -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-graduate me-2"></i>Select Student
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <div class="mb-3">
                                <label for="roll_number" class="form-label">GR Number</label>
                                <div class="input-group">
                                    <input type="text" name="roll_number" id="roll_number" class="form-control" placeholder="Enter student GR Number..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ID Card Preview -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-id-card me-2"></i>ID Card Preview
                    </div>
                    <div class="card-body">
                        <?php if ($student): ?>
                            <div id="idCardContainer">
                                <div class="id-card">
                                    <div class="card-pattern"></div>
                                    <div class="wave-pattern"></div>
                                    
                                    <div class="card-header">
                                        <div class="school-logo">
                                            <img src="assets/images/dif_logo.png" alt="DIF Logo">
                                        </div>
                                        <div class="header-text">
                                            <div class="header-title">Student ID Card</div>
                                            <div class="header-subtitle">Darul Ilm Foundation Sec School</div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-content">
                                        <div class="student-info">
                                            <div class="label">
                                                <i class="fas fa-user"></i>Name
                                            </div>
                                            <div class="value"><?php echo htmlspecialchars($student['student_name']); ?></div>
                                            
                                            <div class="label">
                                                <i class="fas fa-id-badge"></i>Roll No
                                            </div>
                                            <div class="value"><?php echo htmlspecialchars($student['roll_number']); ?></div>
                                            
                                            <div class="label">
                                                <i class="fas fa-graduation-cap"></i>Class
                                            </div>
                                            <div class="value"><?php echo htmlspecialchars($student['class']); ?></div>
                                            
                                            <div class="label">
                                                <i class="fas fa-users"></i>Section
                                            </div>
                                            <div class="value"><?php echo htmlspecialchars($student['section']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="qr-code" id="qrCode"></div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="print_id_card.php?id=<?php echo $student['id']; ?>" 
                                   class="btn btn-primary print-button" target="_blank">
                                    <i class="fas fa-print me-2"></i>Print ID Card
                                </a>
                            </div>
                        <?php else: ?>
                            <div id="noStudentSelected" class="text-center py-5">
                                <i class="fas fa-user-graduate fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">Please enter  student GR Number to Generate ID card</p>
                            </div>
                        <?php endif; ?>
                    </div>
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
    <script>
        <?php if ($student): ?>
        // Function to create QR code
        function createQRCode(element, text) {
            return new Promise((resolve, reject) => {
                try {
                    if (typeof QRCode === 'undefined') {
                        throw new Error('QR Code library not loaded');
                    }

                    // Clear existing content
                    element.innerHTML = '';
                    
                    // Create new QR code with smaller size
                    new QRCode(element, text, {
                        width: 50,
                        height: 50,
                        margin: 0
                    });

                    // Verify QR code was created
                    const img = element.querySelector('img');
                    if (img) {
                        // Add a class to the image for styling
                        img.classList.add('qr-image');
                        
                        if (img.complete) {
                            resolve(true);
                        } else {
                            img.onload = () => resolve(true);
                            img.onerror = () => reject(new Error('QR code image failed to load'));
                        }
                    } else {
                        reject(new Error('QR code element not created'));
                    }
                } catch (error) {
                    reject(error);
                }
            });
        }

        // Function to show error message
        function showError(message) {
            const qrElement = document.getElementById('qrCode');
            if (qrElement) {
                qrElement.innerHTML = '<div style="color: red; padding: 5px; font-size: 10px; text-align: center;">QR Error</div>';
            }
            
            // Remove any existing alerts
            const existingAlerts = document.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());
            
            // Create new alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Insert alert in appropriate location
            const idCardContainer = document.getElementById('idCardContainer');
            if (idCardContainer) {
                idCardContainer.parentNode.insertBefore(alertDiv, idCardContainer);
            }
        }

        // Main QR code generation function with retries
        async function generateQRCode(retryCount = 0) {
            const maxRetries = 3;
            const qrElement = document.getElementById('qrCode');
            
            if (!qrElement) {
                console.error('QR code element not found');
                return;
            }

            try {
                // Use just the qr_code value like in students.php
                const qrText = '<?php echo $student['qr_code']; ?>';
                
                await createQRCode(qrElement, qrText);
                console.log('QR code generated successfully');
            } catch (error) {
                console.error('QR code generation error:', error);

                if (retryCount < maxRetries) {
                    console.log(`Retrying QR code generation (${retryCount + 1}/${maxRetries})...`);
                    setTimeout(() => generateQRCode(retryCount + 1), 1000);
                    return;
                }

                showError('Error generating QR code. Please refresh the page or try again.');
            }
        }

        // Wait for everything to be ready
        function initQRCode() {
            // Check if QR code library is loaded
            if (typeof QRCode === 'undefined') {
                // Wait a bit and try again
                setTimeout(initQRCode, 500);
                return;
            }

            // Start QR code generation
            generateQRCode();
        }

        // Start initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initQRCode);
        } else {
            initQRCode();
        }
        <?php endif; ?>
    </script>
</body>
</html> 