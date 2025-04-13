<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIF Attendance System - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
    <style>
        .profile-section {
            padding: 30px 0;
        }
        .system-info {
            padding: 20px;
        }
        .developer-card {
            transition: transform 0.3s;
        }
        .developer-card:hover {
            transform: translateY(-5px);
        }
        .developer-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--primary-color);
        }
        .social-links a {
            color: var(--primary-color);
            margin: 0 10px;
            font-size: 18px;
            transition: color 0.3s;
        }
        .social-links a:hover {
            color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <!-- Main Content -->
    <div class="container profile-section">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="page-title"><i class="fas fa-info-circle"></i> System Information</h2>
            </div>
        </div>
        
        <div class="row">
            <!-- System Information -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i> About the System
                    </div>
                    <div class="card-body system-info">
                        <h5 class="mb-3">DIF Attendance System</h5>
                        <p>The DIF Attendance System is a comprehensive solution designed to streamline student attendance tracking for the Department of Information and Finance (DIF). This system provides efficient management of student attendance records with advanced features for administrators and easy check-in/check-out for students.</p>
                        
                        <h6 class="mt-4 mb-3">Key Features:</h6>
                        <ul>
                            <li>Real-time student attendance tracking</li>
                            <li>Automated attendance reports</li>
                            <li>Student attendance history</li>
                            <li>Admin dashboard with analytics</li>
                            <li>Secure login system</li>
                            <li>Mobile-responsive design</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Developer Information -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-building me-2"></i> About Developer
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="card developer-card h-100">
                                    <div class="card-body text-center">
                                        <img src="assets/images/ar_logo.jpg" alt="Software House Logo" class="developer-image">
                                        <h5>AR Developers</h5>
                                        <p class="text-muted">Softeare Development Company</p>
                                        
                                        <div class="mt-4">
                                            <h6>Contact Information</h6>
                                            <div class="contact-info">
                                                <p><i class="fas fa-globe me-2"></i> <a href="https://www.arpakdevelopers.com" target="_blank">www.arpakdevelopers.com</a></p>
                                                <p><i class="fas fa-envelope me-2"></i> <a href="mailto:info@ardevs.com">info@arpakdevelopers.com</a></p>
                                                <p><i class="fas fa-phone me-2"></i> <a href="tel:+92-3313771572">+92-3313771572</a></p>
                                            </div>
                                        </div>
                                        
                                        <div class="social-links mt-3">
                                            <a href="https://www.facebook.com/ardevs" target="_blank"><i class="fab fa-facebook"></i></a>
                                            <a href="https://www.twitter.com/ardevs" target="_blank"><i class="fab fa-twitter"></i></a>
                                            <a href="https://www.linkedin.com/company/ardevs" target="_blank"><i class="fab fa-linkedin"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Version Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history me-2"></i> Version Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Current Version</h6>
                                <p>Version 1.0.0 (Released: April 13, 2025)</p>
                                
                                <h6 class="mt-4">Release Notes</h6>
                                <ul>
                                    <li>Initial release of the DIF Attendance System</li>
                                    <li>Complete attendance tracking functionality</li>
                                    <li>Admin dashboard with reporting capabilities</li>
                                    <li>User authentication and authorization</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Upcoming Features</h6>
                                <ul>
                                    <li>Mobile application for check-in/check-out</li>
                                    <li>Advanced analytics dashboard</li>
                                    <li>Integration with payroll system</li>
                                    <li>Biometric authentication support</li>
                                </ul>
                                
                                <h6 class="mt-4">Support</h6>
                                <p>For technical support or feature requests, please contact developer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
</body>
</html> 