<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Generate random math problem
$num1 = rand(1, 10);
$num2 = rand(1, 10);
$operators = ['+', '-', '*'];
$operator = $operators[array_rand($operators)];
$answer = 0;

switch($operator) {
    case '+':
        $answer = $num1 + $num2;
        break;
    case '-':
        $answer = $num1 - $num2;
        break;
    case '*':
        $answer = $num1 * $num2;
        break;
}

// Store the answer in session
$_SESSION['verification_answer'] = $answer;

// Maintenance mode check
$maintenance = include 'config/maintenance_mode.php';
$maintenance_enabled = $maintenance['enabled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Attendance Software - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
        .verification-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .verification-problem {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #0d47a1;
        }
        .verification-input {
            max-width: 100px;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php if ($maintenance_enabled): ?>
    <div id="maintenance-modal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.18);z-index:9999;display:flex;align-items:center;justify-content:center;">
        <div style="background:linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1);padding:36px 28px;max-width:400px;width:100%;text-align:center;position:relative;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
            <button id="close-maintenance-modal" style="position:absolute;top:12px;right:12px;background:transparent;border:none;font-size:22px;color:#0d47a1;cursor:pointer;" aria-label="Close"><i class="fas fa-times"></i></button>
            <div style="font-size:44px;color:#0d47a1;margin-bottom:10px;"><i class="fas fa-exclamation-triangle"></i></div>
            <h3 style="font-weight:700;color:#0d47a1;">Maintenance Mode</h3>
            <p style="font-size:18px;color:#b71c1c;font-weight:600;">The software is currently <span style='color:#e65100;'>under maintenance</span>.</p>
            <div style="background:#e3eafc;border:1px solid #b6c6e6;border-radius:8px;padding:10px 0;margin:18px 0 10px 0;font-weight:700;color:#0d47a1;">Please Don't try to login at this time.</div>
            <p style="font-size:15px;color:#6d4c41;">We apologize for the inconvenience and appreciate your patience.</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form[action="auth/login.php"]');
            var usernameInput = document.getElementById('username');
            var closeBtn = document.getElementById('close-maintenance-modal');
            function setFormState() {
                if (usernameInput.value.trim().toLowerCase() === 'admin') {
                    form.querySelectorAll('input,button,select,textarea').forEach(function(el){el.disabled=false;});
                } else {
                    form.querySelectorAll('input,button,select,textarea').forEach(function(el){el.disabled=true;});
                    usernameInput.disabled = false;
                }
            }
            setFormState();
            usernameInput.addEventListener('input', setFormState);
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    var modal = document.getElementById('maintenance-modal');
                    if (modal) modal.style.display = 'none';
                });
            }
        });
    </script>
    <?php endif; ?>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <div class="logo-container">
                        <img src="assets/images/dif_logo.png" alt="Dif Logo">
                    </div>
                <h4 class="system-title mb-0">DIF Attendance Software</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php 
                                if($_GET['error'] == 1) {
                                    echo "Invalid username or password";
                                } elseif($_GET['error'] == 2) {
                                    echo "Please solve the verification problem correctly";
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
                            <div class="verification-box">
                                <div class="verification-problem">
                                    <?php echo $num1 . ' ' . $operator . ' ' . $num2 . ' = ?'; ?>
                                </div>
                                <input type="number" class="form-control verification-input" name="verification_answer" required>
                            </div>
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