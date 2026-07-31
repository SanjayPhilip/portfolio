<?php
session_start();
require_once 'includes/db.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $dob = $_POST['dob'];
    $bloodType = $_POST['blood_type'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $medicalConditions = isset($_POST['medical_conditions']) ? trim($_POST['medical_conditions']) : '';
    
    // Validate input
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword) 
        || empty($dob) || empty($bloodType) || empty($phone) || empty($address)) {
        $error = "Please fill in all required fields";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check age (must be 18 or older)
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $dobDate->diff($today)->y;
        
        if ($age < 18) {
            $error = "You must be at least 18 years old to register";
        } else {
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            $existingUser = fetchOne($sql, [$email]);
            
            if ($existingUser) {
                $error = "Email already in use. Please use a different email or login";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $sql = "INSERT INTO users (full_name, email, password, dob, blood_type, phone, address, medical_conditions) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        
                $params = [$fullName, $email, $hashedPassword, $dob, $bloodType, $phone, $address, $medicalConditions];
                
                executeQuery($sql, $params);
                
                $success = "Registration successful! You can now login.";
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
    <title>Register - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1>Register</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Create an Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="form-message error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="form-message success-message">
                    <?php echo $success; ?>
                </div>
                <p class="text-center">Go to <a href="login.php">Login Page</a></p>
            <?php else: ?>
            
            <form id="register-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name*</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password*</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <small>At least 6 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password*</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth* (18+ only)</label>
                        <input type="date" id="dob" name="dob" class="form-control" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_type">Blood Type*</label>
                        <select id="blood_type" name="blood_type" class="form-control" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number*</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address*</label>
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="medical_conditions">Medical Conditions (if any)</label>
                    <textarea id="medical_conditions" name="medical_conditions" class="form-control" rows="3"><?php echo isset($_POST['medical_conditions']) ? htmlspecialchars($_POST['medical_conditions']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn primary-btn" style="width: 100%;">Register</button>
                </div>
                
                <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html> 