<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// reCAPTCHA configuration
$recaptcha_site_key = "#"; // Replace with your site key
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIF Attendance System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            background-color: #f8f9fa;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            margin: 20px auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background-color: #0d47a1;
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 90px;
            height: auto;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #0d47a1;
            border-color: #0d47a1;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #0a3d91;
            border-color: #0a3d91;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #0d47a1;
            box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.25);
        }
        .alert {
            border-radius: 8px;
        }
        .system-title {
            font-weight: 700;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <div class="logo-container">
                        <img src="assets/images/dif_logo.png" alt="DIF Logo">
                    </div>
                    <h4 class="system-title mb-0">DIF Attendance System</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php 
                                if($_GET['error'] == 1) {
                                    echo "Invalid username or password";
                                } elseif($_GET['error'] == 2) {
                                    echo "Please complete the reCAPTCHA verification";
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'logged_out'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>You have been successfully logged out.
                        </div>
                    <?php endif; ?>
                    
                    <form action="auth/login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html> 