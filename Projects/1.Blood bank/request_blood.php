<?php
session_start();
require_once 'includes/db.php';

// Prevent caching to avoid duplicate forms on reload
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patientName = trim($_POST['patient_name']);
    $bloodType = $_POST['blood_type'];
    $units = (int)$_POST['units'];
    $hospital = trim($_POST['hospital']);
    $hospitalAddress = trim($_POST['hospital_address']);
    $urgency = $_POST['urgency'];
    $requiredDate = $_POST['required_date'];
    $reason = trim($_POST['reason']);
    $userId = $_SESSION['user_id'];
    
    // Validate input
    if (empty($patientName) || empty($bloodType) || empty($units) || empty($hospital) 
        || empty($hospitalAddress) || empty($urgency) || empty($requiredDate) || empty($reason)) {
        $error = "Please fill in all required fields";
    } elseif ($units <= 0) {
        $error = "Units must be greater than 0";
    } else {
        // Check for existing similar requests from this user
        $checkSql = "SELECT id FROM blood_requests 
                    WHERE user_id = ? AND patient_name = ? AND blood_type = ? AND status = 'pending'";
        $existingRequest = fetchOne($checkSql, [$userId, $patientName, $bloodType]);
        
        if ($existingRequest) {
            $error = "You already have a pending request for this patient with the same blood type.";
        } else {
            // Insert blood request
            $sql = "INSERT INTO blood_requests (user_id, patient_name, blood_type, units, hospital, hospital_address, urgency, required_date, reason) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $params = [$userId, $patientName, $bloodType, $units, $hospital, $hospitalAddress, $urgency, $requiredDate, $reason];
            
            executeQuery($sql, $params);
            
            // Redirect to the same page with a success parameter (Post/Redirect/Get pattern)
            header("Location: request_blood.php?success=1");
            exit();
        }
    }
}

// Set success message if redirected after successful submission
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = "Blood request submitted successfully!";
}

// Current Blood Requests Section
$currentRequests = array(); // Initialize an array to store user's current requests

// Get the user's current requests
if (isset($_SESSION['user_id'])) {
    $userRequestsSql = "SELECT * FROM blood_requests 
                      WHERE user_id = ? 
                      ORDER BY created_at DESC";
    $currentRequests = fetchAll($userRequestsSql, [$_SESSION['user_id']]);
    
    // For each request that is "in_process", fetch donor details
    foreach ($currentRequests as $key => $request) {
        if ($request['status'] === 'in_process') {
            $donorSql = "SELECT bd.*, u.full_name as donor_name, u.phone as donor_phone, u.email as donor_email
                        FROM blood_donations bd
                        JOIN users u ON bd.donor_id = u.id
                        WHERE bd.request_id = ? AND bd.status = 'pending'
                        ORDER BY bd.created_at DESC";
            $donorDetails = fetchAll($donorSql, [$request['id']]);
            $currentRequests[$key]['donor_details'] = $donorDetails;
        }
    }
}

// Get all active blood requests to display (exclude user's own requests)
$sql = "SELECT DISTINCT br.id, br.*, u.full_name as requester_name, u.phone as requester_phone 
        FROM blood_requests br 
        JOIN users u ON br.user_id = u.id 
        WHERE br.status = 'pending' AND br.user_id != ?
        GROUP BY br.id
        ORDER BY 
          CASE 
            WHEN br.urgency = 'critical' THEN 1
            WHEN br.urgency = 'urgent' THEN 2
            ELSE 3
          END, 
          br.created_at DESC";
          
$bloodRequests = fetchAll($sql, [$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --theme-primary: #e63946;
            --theme-secondary: #457b9d;
            --theme-dark: #1d3557;
            --theme-light: #f1faee;
            --theme-danger: #d62828;
            --theme-success: #2a9d8f;
        }
        
        body {
            background-color: #f8f9fa;
            background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
        }
        
        .page-banner {
            background: linear-gradient(135deg, var(--theme-primary), #d00000);
            padding: 60px 0;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .page-banner::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDAgMjAwIj48Y2lyY2xlIGN4PSIxMCIgY3k9IjEwIiByPSIzIiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMSIvPjwvc3ZnPg==');
            background-size: 20px 20px;
            opacity: 0.2;
        }
        
        .page-banner h1 {
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            font-size: 2.8rem;
            margin: 0;
            font-weight: 700;
            position: relative;
        }
        
        .form-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 40px;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .form-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 10px;
            height: 100%;
            background: linear-gradient(180deg, var(--theme-primary), var(--theme-secondary));
            border-radius: 15px 0 0 15px;
        }
        
        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .form-title {
            color: var(--theme-dark);
            font-size: 1.8rem;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: var(--theme-primary);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 20px;
        }
        
        .form-group {
            flex: 1 1 300px;
            margin: 0 10px 20px;
            position: relative;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dbe0e5;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--theme-secondary);
            box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
            outline: none;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--theme-dark);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus + label {
            color: var(--theme-primary);
        }
        
        .form-message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            position: relative;
            animation: fadeInDown 0.5s ease;
        }
        
        .error-message {
            background-color: rgba(214, 40, 40, 0.1);
            border-left: 4px solid var(--theme-danger);
            color: var(--theme-danger);
        }
        
        .success-message {
            background-color: rgba(42, 157, 143, 0.1);
            border-left: 4px solid var(--theme-success);
            color: var(--theme-success);
        }
        
        .primary-btn {
            background: linear-gradient(135deg, var(--theme-primary), #d00000);
            border: none;
            color: white;
            padding: 14px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(230, 57, 70, 0.4);
        }
        
        .primary-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(230, 57, 70, 0.4);
        }
        
        .primary-btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .primary-btn:hover::after {
            left: 100%;
        }
        
        .section-title {
            color: var(--theme-dark);
            font-size: 2rem;
            margin-bottom: 10px;
            position: relative;
            text-align: center;
        }
        
        .section-divider {
            height: 4px;
            width: 100px;
            background: linear-gradient(to right, var(--theme-primary), var(--theme-secondary));
            margin: 0 auto 40px;
            border-radius: 2px;
        }
        
        .subsection-title {
            color: var(--theme-secondary);
            font-size: 1.4rem;
            margin-bottom: 20px;
            position: relative;
            padding-left: 15px;
            border-left: 4px solid var(--theme-primary);
        }
        
        .blood-requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .request-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            border-top: 5px solid #a8dadc;
        }
        
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .request-card.critical {
            border-top-color: var(--theme-danger);
        }
        
        .request-card.urgent {
            border-top-color: #e9c46a;
        }
        
        .request-card.regular {
            border-top-color: #a8dadc;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
        }
        
        .blood-type-badge {
            background-color: var(--theme-primary);
            color: white;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .request-status {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .request-status.pending {
            background-color: rgba(233, 196, 106, 0.1);
            color: #e9c46a;
        }
        
        .request-status.approved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--theme-success);
        }
        
        .request-status.completed {
            background-color: rgba(69, 123, 157, 0.1);
            color: var(--theme-secondary);
        }
        
        .request-status.rejected {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--theme-danger);
        }
        
        .urgency-indicator {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .request-card.critical .urgency-indicator {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--theme-danger);
        }
        
        .request-card.urgent .urgency-indicator {
            background-color: rgba(233, 196, 106, 0.1);
            color: #e9c46a;
        }
        
        .request-card.regular .urgency-indicator {
            background-color: rgba(168, 218, 220, 0.1);
            color: #457b9d;
        }
        
        .request-details {
            padding: 20px;
            flex-grow: 1;
        }
        
        .request-details h3 {
            color: var(--theme-dark);
            margin: 0 0 15px 0;
            font-size: 1.2rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #495057;
            font-size: 0.9rem;
        }
        
        .detail-item i {
            color: var(--theme-secondary);
            width: 20px;
            margin-right: 10px;
        }
        
        .request-footer {
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .date-posted {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .donate-btn {
            background-color: var(--theme-primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .donate-btn:hover {
            background-color: #d00000;
            transform: translateY(-2px);
        }
        
        .user-requests-section, .other-requests-section {
            margin-bottom: 50px;
        }
        
        .no-requests-message {
            text-align: center;
            padding: 50px 0;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .no-requests-icon {
            font-size: 3rem;
            color: var(--theme-primary);
            margin-bottom: 20px;
            display: block;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .form-group {
                flex: 1 1 100%;
            }
            
            .blood-requests-grid {
                grid-template-columns: 1fr;
            }
            
            .request-footer {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .donate-btn {
                width: 100%;
                text-align: center;
            }
        }
        
        .donor-details {
            margin-top: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid var(--theme-success);
        }
        
        .donor-details h4 {
            color: var(--theme-success);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .donor-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .donor-card p {
            margin-bottom: 8px;
        }
        
        .donor-card i {
            color: var(--theme-secondary);
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        
        .status-badge.in-process {
            background-color: #ffc107;
            color: #212529;
        }
        
        /* Fix for upside-down blood drop icon */
        .fas.fa-tint {
            transform: rotate(180deg);
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner animate-fadeIn">
        <div class="container">
            <h1 class="animate-fadeInUp">Request Blood</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container animate-fadeInUp">
            <h2 class="form-title">Submit Blood Request</h2>
            
            <?php if (!empty($error)): ?>
                <div class="form-message error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="form-message success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 'own_request'): ?>
                <div class="form-message error-message">
                    <i class="fas fa-exclamation-circle"></i> You cannot donate to your own blood request. Please wait for other donors to respond.
                </div>
            <?php endif; ?>
            
            <form id="request-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_name">Patient Name*</label>
                        <input type="text" id="patient_name" name="patient_name" class="form-control" value="<?php echo isset($_POST['patient_name']) ? htmlspecialchars($_POST['patient_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_type">Blood Type Needed*</label>
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
                        <label for="units">Units Required*</label>
                        <input type="number" id="units" name="units" min="1" class="form-control" value="<?php echo isset($_POST['units']) ? htmlspecialchars($_POST['units']) : '1'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency">Urgency Level*</label>
                        <select id="urgency" name="urgency" class="form-control" required>
                            <option value="">Select Urgency</option>
                            <option value="critical" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'critical') ? 'selected' : ''; ?>>Critical (within 24 hours)</option>
                            <option value="urgent" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'urgent') ? 'selected' : ''; ?>>Urgent (1-3 days)</option>
                            <option value="standard" <?php echo (isset($_POST['urgency']) && $_POST['urgency'] == 'standard') ? 'selected' : ''; ?>>Standard (within a week)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="hospital">Hospital/Clinic Name*</label>
                    <input type="text" id="hospital" name="hospital" class="form-control" value="<?php echo isset($_POST['hospital']) ? htmlspecialchars($_POST['hospital']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hospital_address">Hospital/Clinic Address*</label>
                    <textarea id="hospital_address" name="hospital_address" class="form-control" rows="2" required><?php echo isset($_POST['hospital_address']) ? htmlspecialchars($_POST['hospital_address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="required_date">Required By Date*</label>
                    <input type="date" id="required_date" name="required_date" class="form-control" value="<?php echo isset($_POST['required_date']) ? htmlspecialchars($_POST['required_date']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="reason">Reason for Request*</label>
                    <textarea id="reason" name="reason" class="form-control" rows="3" required><?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn primary-btn" style="width: 100%;">Submit Request</button>
                </div>
            </form>
        </div>
        
        <!-- Blood Requests Section -->
        <section id="blood-requests" class="animate-section">
            <h2 class="section-title">Blood Requests</h2>
            <div class="section-divider"></div>
            
            <!-- User's Current Requests -->
            <div class="user-requests-section">
                <h3 class="subsection-title">Your Current Blood Requests</h3>
                
                <?php if (empty($currentRequests)): ?>
                    <div class="no-requests-message">
                        <i class="fas fa-info-circle no-requests-icon"></i>
                        <p>You don't have any active blood requests.</p>
                        <p>Need blood? Submit a request using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="blood-requests-grid">
                        <?php foreach ($currentRequests as $request): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <div class="blood-type-badge"><?php echo htmlspecialchars($request['blood_type']); ?></div>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <div class="status-badge pending">Pending</div>
                                    <?php elseif ($request['status'] === 'in_process'): ?>
                                        <div class="status-badge in-process">In Process</div>
                                    <?php elseif ($request['status'] === 'completed'): ?>
                                        <div class="status-badge completed">Completed</div>
                                    <?php elseif ($request['status'] === 'rejected'): ?>
                                        <div class="status-badge rejected">Rejected</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="request-body">
                                    <h3>Patient: <?php echo htmlspecialchars($request['patient_name']); ?></h3>
                                    <div class="request-info">
                                        <p><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($request['hospital']); ?></p>
                                        <p><i class="fas fa-tint"></i> <?php echo htmlspecialchars($request['units']); ?> units needed</p>
                                        <p><i class="fas fa-calendar-alt"></i> Required by: <?php echo date('F j, Y', strtotime($request['required_date'])); ?></p>
                                        <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($request['reason']); ?></p>
                                    </div>
                                    
                                    <?php if ($request['status'] === 'in_process' && isset($request['donor_details']) && !empty($request['donor_details'])): ?>
                                        <div class="donor-details">
                                            <h4 class="section-subtitle">Donor Information</h4>
                                            <?php foreach ($request['donor_details'] as $donor): ?>
                                                <div class="donor-card">
                                                    <p><i class="fas fa-user"></i> <strong>Donor:</strong> <?php echo htmlspecialchars($donor['donor_name']); ?></p>
                                                    <p><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($donor['donor_phone']); ?></p>
                                                    <p><i class="fas fa-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($donor['donor_email']); ?></p>
                                                    <p><i class="fas fa-calendar"></i> <strong>Donation Date:</strong> <?php echo date('F j, Y', strtotime($donor['donation_date'])); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="request-footer">
                                    <span class="request-date">Posted on <?php echo date('M j, Y', strtotime($request['created_at'])); ?></span>
                                    <div class="request-actions">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <form action="request_blood.php" method="post" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" class="btn danger-btn btn-sm">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Other Active Requests -->
            <div class="other-requests-section">
                <h3 class="subsection-title">Active Blood Requests From Others</h3>
                
                <?php 
                // Debug info
                echo "<!-- Debug: Found " . count($bloodRequests) . " blood requests from other users -->";
                
                // Modified SQL query to include all pending blood requests (even from the current user, for testing)
                $allRequestsSql = "SELECT DISTINCT br.id, br.*, u.full_name as requester_name, u.phone as requester_phone 
                                FROM blood_requests br 
                                JOIN users u ON br.user_id = u.id 
                                WHERE br.status = 'pending'
                                ORDER BY br.created_at DESC";
                $allRequests = fetchAll($allRequestsSql);
                echo "<!-- Debug: Found " . count($allRequests) . " total pending blood requests -->";
                
                if(empty($bloodRequests)): 
                ?>
                <div class="no-requests-message">
                    <i class="fas fa-check-circle no-requests-icon"></i>
                    <p>There are currently no active blood requests from others.</p>
                    <p>Check back later or help spread the word about blood donation!</p>
                </div>
                <?php else: ?>
                <div class="blood-requests-grid">
                    <?php foreach($bloodRequests as $request): 
                        // Determine urgency class based on days remaining
                        $urgency_class = 'regular';
                        $urgency_text = 'Regular';
                        
                        $required_date = new DateTime($request['required_date']);
                        $today = new DateTime();
                        $days_remaining = $today->diff($required_date)->days;
                        
                        if($required_date < $today) {
                            $urgency_class = 'critical';
                            $urgency_text = 'Critical';
                        } elseif($days_remaining <= 3) {
                            $urgency_class = 'urgent';
                            $urgency_text = 'Urgent';
                        }
                    ?>
                    <div class="request-card <?php echo $urgency_class; ?>">
                        <div class="request-header">
                            <div class="blood-type-badge"><?php echo $request['blood_type']; ?></div>
                            <span class="requester-name">From: <?php echo $request['requester_name']; ?></span>
                        </div>
                        <div class="request-details">
                            <h3>Patient: <?php echo $request['patient_name']; ?></h3>
                            <div class="detail-item">
                                <i class="fas fa-hospital"></i>
                                <span><?php echo $request['hospital']; ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tint"></i>
                                <span><?php echo $request['units']; ?> units needed</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Required by: <?php echo date('F j, Y', strtotime($request['required_date'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-info-circle"></i>
                                <span><?php echo $request['reason']; ?></span>
                            </div>
                        </div>
                        <div class="request-footer">
                            <span class="date-posted">Posted on <?php echo date('M j, Y', strtotime($request['created_at'])); ?></span>
                            <div class="request-actions">
                                <span class="urgency-indicator <?php echo $urgency_class; ?>"><?php echo $urgency_text; ?></span>
                                <a href="respond_request.php?id=<?php echo $request['id']; ?>" class="btn respond-btn">Respond</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        // Add smooth scrolling to form
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html> 