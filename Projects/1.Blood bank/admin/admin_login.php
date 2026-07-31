<?php
session_start();
require_once '../includes/db.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Check if admin exists in the admins table
        $sql = "SELECT * FROM admins WHERE email = ?";
        $admin = fetchOne($sql, [$email]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            // Update last_login time
            $updateSql = "UPDATE admins SET last_login = NOW() WHERE id = ?";
            executeQuery($updateSql, [$admin['id']]);
            
            // Redirect to admin dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E Blood Connect</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #457b9d;
            --dark-color: #1d3557;
            --light-color: #f1faee;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1IiBoZWlnaHQ9IjUiPgo8cmVjdCB3aWR0aD0iNSIgaGVpZ2h0PSI1IiBmaWxsPSIjZmZmIj48L3JlY3Q+CjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjEiIGZpbGw9IiNmMmYyZjIiPjwvcmVjdD4KPC9zdmc+');
        }
        
        .admin-login-container {
            max-width: 500px;
            margin: 80px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .admin-login-header {
            background: linear-gradient(135deg, var(--primary-color), #d00000);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-login-header h1 {
            margin: 0;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .admin-login-header p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .admin-login-body {
            padding: 40px;
        }
        
        .admin-login-icon {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .admin-login-icon .icon-circle {
            width: 100px;
            height: 100px;
            background-color: #ffccd5;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 3rem;
        }
        
        .form-group {
            margin-bottom: 25px;
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
        
        .form-control-icon input {
            padding-left: 45px;
        }
        
        .btn-login {
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
        
        .btn-login:hover {
            background: linear-gradient(135deg, #d00000, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 40, 40, 0.3);
        }
        
        .btn-login i {
            margin-right: 10px;
        }
        
        .error-message {
            background-color: rgba(214, 40, 40, 0.1);
            border-left: 4px solid #d62828;
            color: #d62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: rgba(69, 123, 157, 0.1);
            border-radius: 5px;
        }
        
        .register-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }
        
        .back-link a:hover {
            color: var(--primary-color);
        }
        
        .back-link a i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-header">
            <h1>Admin Login</h1>
            <p>E Blood Connect Management Panel</p>
        </div>
        
        <div class="admin-login-body">
            <div class="admin-login-icon">
                <div class="icon-circle">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Check if database has any admins
            $sql = "SELECT COUNT(*) as count FROM admins";
            $result = fetchOne($sql);
            
            if ($result['count'] == 0):
            ?>
                <div class="message" style="background-color: rgba(69, 123, 157, 0.1); border-left: 4px solid #457b9d; color: #457b9d; padding: 15px; border-radius: 5px; margin-bottom: 25px;">
                    <i class="fas fa-info-circle"></i> No admin accounts found. Please <a href="admin_register.php" style="color: var(--primary-color); font-weight: 600;">create an admin account</a> first.
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="form-control-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="form-control-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
                </button>
            </form>
            
            <div class="register-link">
                <i class="fas fa-info-circle"></i> Need a new admin account? 
                <a href="admin_register.php">Register here</a> 
            </div>
            
            <div class="back-link">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Homepage</a>
            </div>
        </div>
    </div>
    
    <script>
        // Add password visibility toggle
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.createElement('i');
            eyeIcon.className = 'fas fa-eye';
            eyeIcon.style.position = 'absolute';
            eyeIcon.style.right = '15px';
            eyeIcon.style.top = '50%';
            eyeIcon.style.transform = 'translateY(-50%)';
            eyeIcon.style.cursor = 'pointer';
            eyeIcon.style.color = '#6c757d';
            
            passwordField.parentNode.appendChild(eyeIcon);
            
            eyeIcon.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    eyeIcon.className = 'fas fa-eye-slash';
                } else {
                    passwordField.type = 'password';
                    eyeIcon.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html> 