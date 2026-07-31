<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Check if request ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: request_blood.php");
    exit();
}

$requestId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Get the blood request details
$sql = "SELECT br.*, u.full_name as requester_name, u.phone as requester_phone 
        FROM blood_requests br 
        JOIN users u ON br.user_id = u.id 
        WHERE br.id = ? AND br.status = 'pending'";
        
$request = fetchOne($sql, [$requestId]);

// Check if request exists and is still pending
if (!$request) {
    header("Location: request_blood.php");
    exit();
}

// Check if the user is trying to donate to their own request
if ($request['user_id'] == $userId) {
    header("Location: request_blood.php?error=own_request");
    exit();
}

// Get donor details
$sql = "SELECT * FROM users WHERE id = ?";
$donor = fetchOne($sql, [$userId]);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donationDate = $_POST['donation_date'];
    $message = trim($_POST['message']);
    
    // Validate input
    if (empty($donationDate)) {
        $error = "Please select a donation date";
    } else {
        // Check if donation date is in the future or today
        $today = date('Y-m-d');
        if ($donationDate < $today) {
            $error = "Donation date cannot be in the past";
        } else {
            // Insert donation
            $sql = "INSERT INTO blood_donations (donor_id, request_id, blood_type, units, donation_date, donation_type, status) 
                    VALUES (?, ?, ?, ?, ?, 'direct', 'pending')";
                    
            $params = [$userId, $requestId, $request['blood_type'], $request['units'], $donationDate];
            
            executeQuery($sql, $params);
            
            // Update request status to 'in_process'
            $sql = "UPDATE blood_requests SET status = 'in_process' WHERE id = ?";
            executeQuery($sql, [$requestId]);
            
            $success = "Thank you for your donation! The requester will be notified of your response.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respond to Blood Request - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1>Respond to Blood Request</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="request-details">
            <h2 class="section-title">Request Details</h2>
            
            <div class="request-card">
                <div class="request-header">
                    <span><?php echo htmlspecialchars($request['patient_name']); ?></span>
                    <div class="blood-type-badge"><?php echo htmlspecialchars($request['blood_type']); ?></div>
                </div>
                
                <div class="request-body">
                    <div class="request-info">
                        <p><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($request['hospital']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($request['hospital_address']); ?></p>
                        <p><i class="fas fa-tint"></i> Units needed: <?php echo htmlspecialchars($request['units']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Required by: <?php echo date('F j, Y', strtotime($request['required_date'])); ?></p>
                        <p><i class="fas fa-exclamation-circle"></i> Urgency: <span class="urgency-<?php echo $request['urgency']; ?>"><?php echo ucfirst($request['urgency']); ?></span></p>
                        <p><i class="fas fa-user"></i> Posted by: <?php echo htmlspecialchars($request['requester_name']); ?></p>
                        <p><i class="fas fa-phone"></i> Contact: <?php echo htmlspecialchars($request['requester_phone']); ?></p>
                    </div>
                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="form-container">
                <div class="form-message success-message" style="margin-top: 20px;">
                    <?php echo $success; ?>
                </div>
                <div class="text-center" style="margin-top: 20px;">
                    <a href="request_blood.php" class="btn secondary-btn">Back to Blood Requests</a>
                </div>
            </div>
        <?php else: ?>
            <div class="form-container">
                <h2 class="form-title">Donation Information</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="form-message error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="compatibility-check">
                    <h3>Blood Type Compatibility Check</h3>
                    <?php
                    $compatible = false;
                    $requestBloodType = $request['blood_type'];
                    $donorBloodType = $donor['blood_type'];
                    
                    // Define compatibility matrix
                    $compatibilityMatrix = [
                        'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
                        'O+' => ['O+', 'A+', 'B+', 'AB+'],
                        'A-' => ['A-', 'A+', 'AB-', 'AB+'],
                        'A+' => ['A+', 'AB+'],
                        'B-' => ['B-', 'B+', 'AB-', 'AB+'],
                        'B+' => ['B+', 'AB+'],
                        'AB-' => ['AB-', 'AB+'],
                        'AB+' => ['AB+']
                    ];
                    
                    if (isset($compatibilityMatrix[$donorBloodType]) && in_array($requestBloodType, $compatibilityMatrix[$donorBloodType])) {
                        $compatible = true;
                    }
                    ?>
                    
                    <?php if ($compatible): ?>
                        <div class="compatibility-result compatible">
                            <i class="fas fa-check-circle"></i>
                            <p>Your blood type (<?php echo $donorBloodType; ?>) is compatible with the requested blood type (<?php echo $requestBloodType; ?>).</p>
                        </div>
                    <?php else: ?>
                        <div class="compatibility-result incompatible">
                            <i class="fas fa-times-circle"></i>
                            <p>Your blood type (<?php echo $donorBloodType; ?>) is not compatible with the requested blood type (<?php echo $requestBloodType; ?>).</p>
                            <p>Thank you for your willingness to help, but unfortunately, you cannot donate to this request.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($compatible): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $requestId; ?>" method="post">
                        <div class="form-group">
                            <label for="donation_date">When can you donate?*</label>
                            <input type="date" id="donation_date" name="donation_date" class="form-control" 
                                min="<?php echo date('Y-m-d'); ?>" 
                                max="<?php echo date('Y-m-d', strtotime($request['required_date'])); ?>" 
                                required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Additional Message (Optional)</label>
                            <textarea id="message" name="message" class="form-control" rows="3"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <p class="donor-agreement">
                                <strong>By submitting this form, you agree to:</strong>
                                <ul>
                                    <li>Be physically present at the specified hospital on the selected date</li>
                                    <li>Follow all donation protocols and procedures at the medical facility</li>
                                    <li>Inform the requester if you cannot fulfill the commitment</li>
                                </ul>
                            </p>
                        </div>
                        
                        <div class="form-buttons">
                            <a href="request_blood.php" class="btn secondary-btn">Cancel</a>
                            <button type="submit" class="btn primary-btn">Confirm Donation</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="form-buttons" style="margin-top: 20px;">
                        <a href="request_blood.php" class="btn secondary-btn">Back to Blood Requests</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
</body>
</html> 