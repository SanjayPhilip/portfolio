<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$error = "";
$success = "";

// Get user profile
$sql = "SELECT * FROM users WHERE id = ?";
$user = fetchOne($sql, [$userId]);

// Process profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate input
    if (empty($fullName) || empty($phone) || empty($address)) {
        $error = "Please fill in all required fields";
    } else if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // Password change requested
        if (empty($currentPassword)) {
            $error = "Current password is required to change password";
        } else if (empty($newPassword) || empty($confirmPassword)) {
            $error = "New password and confirmation are required";
        } else if ($newPassword !== $confirmPassword) {
            $error = "New password and confirmation do not match";
        } else if (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters long";
        } else if (!password_verify($currentPassword, $user['password'])) {
            $error = "Current password is incorrect";
        } else {
            // All validations passed, update with new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?";
            $params = [$fullName, $phone, $address, $hashedPassword, $userId];
            
            executeQuery($sql, $params);
            $success = "Profile updated successfully";
            
            // Update user profile data
            $user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        }
    } else {
        // Update without changing password
        $sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        $params = [$fullName, $phone, $address, $userId];
        
        executeQuery($sql, $params);
        $success = "Profile updated successfully";
        
        // Update user profile data
        $user = fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
    }
}

// Get blood bank donation history
$sql = "SELECT * FROM blood_bank_donations WHERE donor_id = ? ORDER BY created_at DESC";
$donations = fetchAll($sql, [$userId]);

// Get blood bank request history
$sql = "SELECT * FROM blood_bank_requests WHERE user_id = ? ORDER BY created_at DESC";
$bankRequests = fetchAll($sql, [$userId]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: 40px 0;
        }
        
        .profile-sidebar {
            flex: 1;
            min-width: 300px;
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .profile-content {
            flex: 2;
            min-width: 500px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
        }
        
        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .blood-type {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--dark-color);
        }
        
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-link {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 600;
        }
        
        .tab-link.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .history-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .history-type {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .history-type i {
            color: var(--primary-color);
        }
        
        .history-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: rgba(233, 196, 106, 0.2);
            color: #e9c46a;
        }
        
        .status-approved {
            background-color: rgba(42, 157, 143, 0.2);
            color: #2a9d8f;
        }
        
        .status-completed {
            background-color: rgba(6, 214, 160, 0.2);
            color: #06d6a0;
        }
        
        .status-rejected {
            background-color: rgba(231, 111, 81, 0.2);
            color: #e76f51;
        }
        
        .status-in_process {
            background-color: rgba(20, 110, 240, 0.2);
            color: #146ef0;
        }
        
        .history-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .history-detail {
            margin-bottom: 10px;
        }
        
        .history-detail strong {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .section-divider {
            margin: 40px 0;
            border-top: 1px solid #eee;
            position: relative;
        }
        
        .section-divider h2 {
            position: absolute;
            top: -15px;
            left: 20px;
            background-color: white;
            padding: 0 10px;
            color: var(--dark-color);
            font-size: 1.2rem;
        }
        
        .basic-info-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .basic-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .basic-info-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--dark-color);
        }
        
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .info-item strong {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-color);
            font-size: 0.9rem;
        }
        
        .info-item span {
            font-size: 1.1rem;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar, .profile-content {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1>My Profile</h1>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="form-message error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="form-message success-message">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo substr($user['full_name'], 0, 1); ?>
                </div>
                
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="blood-type"><?php echo $user['blood_type']; ?></div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                    <div style="margin-top: 10px;"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($donations); ?></div>
                        <div class="stat-label">Donations</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($bankRequests); ?></div>
                        <div class="stat-label">Requests</div>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn primary-btn" onclick="document.getElementById('edit-profile-form').style.display = document.getElementById('edit-profile-form').style.display === 'none' ? 'block' : 'none';">
                        <i class="fas fa-pencil-alt"></i> Edit Profile
                    </button>
                </div>
                
                <div id="edit-profile-form" style="display: none; margin-top: 20px;">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="full_name">Full Name*</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number*</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address*</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <h3>Change Password</h3>
                            <small>Leave empty if you don't want to change</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="update_profile" class="btn primary-btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="basic-info-card">
                    <div class="basic-info-header">
                        <h2>Basic Information</h2>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <strong>Email</strong>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Blood Type</strong>
                            <span><?php echo $user['blood_type']; ?></span>
                        </div>
                        <?php if(!empty($user['date_of_birth'])): ?>
                        <div class="info-item">
                            <strong>Date of Birth</strong>
                            <span><?php echo date('F j, Y', strtotime($user['date_of_birth'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($user['gender'])): ?>
                        <div class="info-item">
                            <strong>Gender</strong>
                            <span><?php echo ucfirst($user['gender']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <strong>Address</strong>
                            <span><?php echo htmlspecialchars($user['address']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Member Since</strong>
                            <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="section-divider">
                    <h2>Blood Donations</h2>
                </div>
                
                <?php if (empty($donations)): ?>
                    <p>You haven't made any donations yet.</p>
                <?php else: ?>
                    <?php foreach ($donations as $donation): ?>
                        <div class="history-item">
                            <div class="history-header">
                                <div class="history-type">
                                    <i class="fas fa-tint"></i>
                                    <h3>Blood Bank Donation (#<?php echo $donation['id']; ?>)</h3>
                                </div>
                                <span class="history-status status-<?php echo strtolower($donation['status']); ?>"><?php echo ucfirst($donation['status']); ?></span>
                            </div>
                            
                            <div class="history-details">
                                <div class="history-detail">
                                    <strong>Blood Type:</strong> <?php echo $donation['blood_type']; ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Units:</strong> <?php echo $donation['units']; ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Donation Date:</strong> <?php echo date('F j, Y', strtotime($donation['donation_date'])); ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Submitted On:</strong> <?php echo date('F j, Y', strtotime($donation['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="section-divider">
                    <h2>Blood Bank Requests</h2>
                </div>
                
                <?php if (empty($bankRequests)): ?>
                    <p>You haven't made any blood bank requests yet.</p>
                <?php else: ?>
                    <?php foreach ($bankRequests as $request): ?>
                        <div class="history-item">
                            <div class="history-header">
                                <div class="history-type">
                                    <i class="fas fa-hospital"></i>
                                    <h3><?php echo htmlspecialchars($request['patient_name']); ?> - <?php echo $request['blood_type']; ?> Blood</h3>
                                </div>
                                <span class="history-status status-<?php echo strtolower($request['status']); ?>"><?php echo ucfirst($request['status']); ?></span>
                            </div>
                            
                            <div class="history-details">
                                <div class="history-detail">
                                    <strong>Units:</strong> <?php echo $request['units']; ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Hospital:</strong> <?php echo htmlspecialchars($request['hospital']); ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Required By:</strong> <?php echo date('F j, Y', strtotime($request['required_date'])); ?>
                                </div>
                                
                                <div class="history-detail">
                                    <strong>Requested On:</strong> <?php echo date('F j, Y', strtotime($request['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="history-detail">
                                <strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html> 