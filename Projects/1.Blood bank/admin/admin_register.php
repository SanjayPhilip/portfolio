<?php
session_start();
require_once '../includes/db.php';

// Check if there are existing admins
$sql = "SELECT COUNT(*) as count FROM admins";
$result = fetchOne($sql);
$hasAdmins = $result['count'] > 0;

// If there are existing admins and the current user is not logged in as admin
if ($hasAdmins && !isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$error = "";
$success = "";

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($fullName) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists in admins table
        $sql = "SELECT id FROM admins WHERE email = ?";
        $existingAdmin = fetchOne($sql, [$email]);
        
        if ($existingAdmin) {
            $error = "Email already exists";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $sql = "INSERT INTO admins (full_name, email, phone, password) VALUES (?, ?, ?, ?)";
            executeQuery($sql, [$fullName, $email, $phone, $hashedPassword]);
            
            $success = "Admin account created successfully!";
            
            // Clear form data
            $fullName = $email = $phone = "";
            
            // If this is the first admin, redirect to login
            if (!$hasAdmins) {
                header("Refresh: 2; URL=admin_login.php");
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - E Blood Connect</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #457b9d;
            --dark-color: #1d3557;
            --light-color: #f1faee;
            --success-color: #2a9d8f;
            --warning-color: #e9c46a;
            --danger-color: #d62828;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1IiBoZWlnaHQ9IjUiPgo8cmVjdCB3aWR0aD0iNSIgaGVpZ2h0PSI1IiBmaWxsPSIjZmZmIj48L3JlY3Q+CjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmMmYyZjIiPjwvcmVjdD4KPC9zdmc+');
        }
        
        .admin-register-container {
            max-width: 800px;
            margin: 60px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .admin-register-header {
            background: linear-gradient(135deg, var(--primary-color), #d00000);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-register-header h1 {
            margin: 0;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .admin-register-header p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .admin-register-body {
            padding: 40px;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        
        .error-message {
            background-color: rgba(214, 40, 40, 0.1);
            border-left: 4px solid #d62828;
            color: #d62828;
        }
        
        .success-message {
            background-color: rgba(42, 157, 143, 0.1);
            border-left: 4px solid #2a9d8f;
            color: #2a9d8f;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 calc(50% - 20px);
            margin: 0 10px 25px;
            position: relative;
        }
        
        .form-group.full-width {
            flex: 1 0 calc(100% - 20px);
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #dbe0e5;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
            outline: none;
        }
        
        .form-control-icon {
            position: relative;
        }
        
        .form-control-icon i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .form-control-icon input,
        .form-control-icon select {
            padding-left: 45px;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 5px;
            color: #6c757d;
        }
        
        .btn-register {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), #d00000);
            color: white;
            border: none;
            padding: 15px 25px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, #d00000, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 40, 40, 0.3);
        }
        
        .btn-register i {
            margin-right: 10px;
        }
        
        .action-links {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .action-links a {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .action-links a:hover {
            color: var(--primary-color);
        }
        
        .action-links a i {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .admin-register-container {
                margin: 20px;
                width: auto;
            }
            
            .form-group {
                flex: 1 0 100%;
            }
            
            .admin-register-header h1 {
                font-size: 2rem;
            }
            
            .admin-register-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-register-container">
        <div class="admin-register-header">
            <h1><?php echo !$hasAdmins ? "Setup Admin Account" : "Register New Admin"; ?></h1>
            <p><?php echo !$hasAdmins ? "Create the first administrator account" : "Add a new administrator to the system"; ?></p>
        </div>
        
        <div class="admin-register-body">
            <?php if (!empty($error)): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <?php if (!$hasAdmins): ?>
                        <p>Redirecting to login page...</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="adminRegisterForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <div class="form-control-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="form-control-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="form-control-icon">
                            <i class="fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="form-control-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="passwordStrengthMeter"></div>
                        </div>
                        <div class="password-strength-text" id="passwordStrengthText">Password strength</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="form-control-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> <?php echo !$hasAdmins ? "Create Admin Account" : "Register Admin"; ?>
                </button>
                
                <div class="action-links">
                    <?php if ($hasAdmins): ?>
                    <a href="admin_login.php"><i class="fas fa-sign-in-alt"></i> Back to Login</a>
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Back to Dashboard</a>
                    <?php endif; ?>
                    <?php else: ?>
                    <a href="../index.php"><i class="fas fa-home"></i> Back to Homepage</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Password strength meter
        const passwordInput = document.getElementById('password');
        const meter = document.getElementById('passwordStrengthMeter');
        const strengthText = document.getElementById('passwordStrengthText');
        
        passwordInput.addEventListener('input', function() {
            const val = passwordInput.value;
            let strength = 0;
            let status = '';
            
            if (val.length >= 8) strength += 1;
            if (val.match(/[a-z]+/)) strength += 1;
            if (val.match(/[A-Z]+/)) strength += 1;
            if (val.match(/[0-9]+/)) strength += 1;
            if (val.match(/[^a-zA-Z0-9]+/)) strength += 1;
            
            switch (strength) {
                case 0:
                case 1:
                    meter.style.width = '20%';
                    meter.style.backgroundColor = '#d62828';
                    status = 'Very Weak';
                    break;
                case 2:
                    meter.style.width = '40%';
                    meter.style.backgroundColor = '#e9c46a';
                    status = 'Weak';
                    break;
                case 3:
                    meter.style.width = '60%';
                    meter.style.backgroundColor = '#90be6d';
                    status = 'Good';
                    break;
                case 4:
                    meter.style.width = '80%';
                    meter.style.backgroundColor = '#43aa8b';
                    status = 'Strong';
                    break;
                case 5:
                    meter.style.width = '100%';
                    meter.style.backgroundColor = '#2a9d8f';
                    status = 'Very Strong';
                    break;
            }
            
            strengthText.innerText = `Password strength: ${status}`;
        });
        
        // Password confirmation check
        const confirmInput = document.getElementById('confirm_password');
        
        confirmInput.addEventListener('input', function() {
            if (passwordInput.value !== confirmInput.value) {
                confirmInput.setCustomValidity('Passwords do not match');
            } else {
                confirmInput.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 