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

if (!$student) {
    echo "Student not found";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print ID Card - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Load QR code library from a more reliable CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Fallback QR code library -->
    <script>
        if (typeof QRCode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"><\/script>');
        }
    </script>
    <style>
        @page {
            size: 85.6mm 53.98mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 85.6mm;
            height: 53.98mm;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .id-card {
            width: 85.6mm;
            height: 53.98mm;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
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
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
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
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="card-pattern"></div>
        <div class="wave-pattern"></div>
        
        <div class="card-header">
            <div class="school-logo">
                <img src="assets/images/dif_logo.png" alt="DIF Logo">
            </div>
            <div class="header-text">
                <div class="header-title">Student ID Card</div>
                <div class="header-subtitle">DIF School System</div>
            </div>
        </div>
        
        <div class="card-content">
            <div class="student-info">
                <div class="label">
                    <i class="fas fa-user"></i>Name
                </div>
                <div class="value"><?php echo htmlspecialchars($student['name']); ?></div>
                
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
        
        <div class="qr-code">
            <div id="qrCode"></div>
        </div>
    </div>

    <script>
        // Function to create QR code
        function createQRCode(element, text) {
            return new Promise((resolve, reject) => {
                try {
                    // Clear existing content
                    element.innerHTML = '';
                    
                    // Create new QR code with smaller size
                    const qr = new QRCode(element, text, {
                        width: 50,
                        height: 50,
                        margin: 0
                    });
                    
                    // Check if QR code was created successfully
                    const img = element.querySelector('img');
                    if (img) {
                        // Add a class to the image for styling
                        img.classList.add('qr-image');
                        
                        img.onload = () => resolve(true);
                        img.onerror = () => reject(new Error('QR code image failed to load'));
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
            qrElement.innerHTML = '<div style="color: red; padding: 5px; font-size: 10px; text-align: center;">QR Error</div>';
            
            const errorMsg = document.createElement('div');
            errorMsg.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); ' +
                'background: #ff5252; color: white; padding: 10px 20px; border-radius: 4px; ' +
                'font-size: 14px; z-index: 9999;';
            errorMsg.textContent = message;
            document.body.appendChild(errorMsg);
        }

        // Main QR code generation function with retries
        async function generateQRCode(retryCount = 0) {
            const maxRetries = 3;
            const qrElement = document.getElementById('qrCode');
            
            try {
                // Check if QRCode is available
                if (typeof QRCode === 'undefined') {
                    throw new Error('QR Code library not loaded');
                }

                // Use just the qr_code value like in students.php
                const qrText = '<?php echo $student['qr_code']; ?>';

                // Try to create QR code
                await createQRCode(qrElement, qrText);

                // If successful, wait a moment and print
                setTimeout(() => window.print(), 500);
            } catch (error) {
                console.error('QR code generation error:', error);

                // Retry if under max attempts
                if (retryCount < maxRetries) {
                    console.log(`Retrying QR code generation (${retryCount + 1}/${maxRetries})...`);
                    setTimeout(() => generateQRCode(retryCount + 1), 1000);
                    return;
                }

                // Show error if all retries failed
                showError('Error generating QR code. Please refresh the page or try again.');
            }
        }

        // Function to load QR code library
        function loadQRCodeLibrary() {
            return new Promise((resolve, reject) => {
                if (typeof QRCode !== 'undefined') {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
                script.onload = resolve;
                script.onerror = () => {
                    // Try alternate CDN if first one fails
                    const fallbackScript = document.createElement('script');
                    fallbackScript.src = 'https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js';
                    fallbackScript.onload = resolve;
                    fallbackScript.onerror = reject;
                    document.head.appendChild(fallbackScript);
                };
                document.head.appendChild(script);
            });
        }

        // Initialize everything
        async function init() {
            try {
                await loadQRCodeLibrary();
                await generateQRCode();
            } catch (error) {
                console.error('Initialization error:', error);
                showError('Failed to initialize QR code generator. Please check your internet connection and try again.');
            }
        }

        // Start when page is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    </script>
</body>
</html> 