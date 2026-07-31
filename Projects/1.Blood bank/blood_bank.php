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
$userId = $_SESSION['user_id'];

// Get blood bank inventory
$sql = "SELECT * FROM blood_bank ORDER BY blood_type";
$bloodInventory = fetchAll($sql);

// Process blood bank request form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patientName = trim($_POST['patient_name']);
    $bloodType = $_POST['blood_type'];
    $units = (int)$_POST['units'];
    $hospital = trim($_POST['hospital']);
    $requiredDate = $_POST['required_date'];
    $reason = trim($_POST['reason']);
    
    // Validate input
    if (empty($patientName) || empty($bloodType) || empty($units) || empty($hospital) 
        || empty($requiredDate) || empty($reason)) {
        $error = "Please fill in all required fields";
    } elseif ($units <= 0) {
        $error = "Units must be greater than 0";
    } else {
        // Check if we have enough units in inventory
        $sql = "SELECT units FROM blood_bank WHERE blood_type = ?";
        $availableUnits = fetchOne($sql, [$bloodType]);
        
        if (!$availableUnits || $availableUnits['units'] < $units) {
            $error = "Sorry, we don't have enough units of " . $bloodType . " blood available. Currently we have " . ($availableUnits ? $availableUnits['units'] : 0) . " units.";
        } else {
            // Insert blood bank request
            $sql = "INSERT INTO blood_bank_requests (user_id, patient_name, blood_type, units, hospital, required_date, reason, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
            $params = [$userId, $patientName, $bloodType, $units, $hospital, $requiredDate, $reason, 'pending'];
            
            executeQuery($sql, $params);
            
            // Use Post/Redirect/Get pattern to prevent form resubmission
            $_SESSION['blood_bank_success'] = "Blood bank request submitted successfully! An administrator will review your request.";
            header("Location: blood_bank.php");
            exit();
        }
    }
}

// Check for success message in session
if(isset($_SESSION['blood_bank_success'])) {
    $success = $_SESSION['blood_bank_success'];
    // Clear the message so it doesn't appear again on refresh
    unset($_SESSION['blood_bank_success']);
}

// Get user's blood bank requests (all statuses)
$sql = "SELECT * FROM blood_bank_requests WHERE user_id = ? ORDER BY created_at DESC";
$userRequests = fetchAll($sql, [$userId]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank - E Blood Connect</title>
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
            --theme-warning: #f4a261;
        }
        
        body {
            background-color: #f8f9fa;
            background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
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
        
        .section-title {
            text-align: center;
            font-size: 2.2rem;
            margin: 50px 0 40px;
            color: var(--theme-dark);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--theme-primary), var(--theme-secondary));
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .blood-inventory {
            margin: 40px 0;
        }
        
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
            perspective: 1000px;
        }
        
        .inventory-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid #eaeaea;
            text-align: center;
            padding-bottom: 20px;
            transform-style: preserve-3d;
        }
        
        .inventory-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .inventory-header {
            background: linear-gradient(135deg, var(--theme-primary), #d00000);
            color: white;
            padding: 20px 15px;
            font-weight: 700;
            font-size: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .inventory-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            transform: scaleX(0);
            transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        
        .inventory-card:hover .inventory-header::after {
            transform: scaleX(1);
        }
        
        .inventory-units {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 30px 0;
            color: var(--theme-dark);
            position: relative;
            display: inline-block;
        }
        
        .inventory-units::after {
            content: 'Units';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.9rem;
            font-weight: 400;
            color: #6c757d;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .inventory-status {
            margin: 15px auto;
            padding: 8px 20px;
            display: inline-block;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 120px;
        }
        
        .status-critical {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--theme-danger);
            border: 1px solid rgba(214, 40, 40, 0.3);
        }
        
        .status-low {
            background-color: rgba(244, 162, 97, 0.1);
            color: var(--theme-warning);
            border: 1px solid rgba(244, 162, 97, 0.3);
        }
        
        .status-good {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--theme-success);
            border: 1px solid rgba(42, 157, 143, 0.3);
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
        
        .request-list {
            margin: 30px 0;
        }
        
        .request-item {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid #eaeaea;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .request-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .request-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--theme-primary), var(--theme-secondary));
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.3s ease;
        }
        
        .request-item:hover::before {
            transform: scaleY(1);
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        
        .request-detail {
            margin-bottom: 15px;
        }
        
        .request-detail strong {
            display: block;
            margin-bottom: 8px;
            color: var(--theme-dark);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .request-detail span {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .request-status {
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: rgba(244, 162, 97, 0.1);
            color: var(--theme-warning);
            border: 1px solid rgba(244, 162, 97, 0.3);
        }
        
        .status-approved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--theme-success);
            border: 1px solid rgba(42, 157, 143, 0.3);
        }
        
        .status-rejected {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--theme-danger);
            border: 1px solid rgba(214, 40, 40, 0.3);
        }
        
        .no-requests {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 15px;
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }
        
        .no-requests i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: var(--theme-primary);
            opacity: 0.5;
        }
        
        .blood-type-indicator {
            display: inline-block;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--theme-primary), #d00000);
            color: white;
            line-height: 40px;
            text-align: center;
            font-weight: 700;
            margin-right: 10px;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Add sequential loading animation for inventory cards */
        .inventory-card {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }
        
        <?php for($i = 1; $i <= 20; $i++): ?>
        .inventory-grid .inventory-card:nth-child(<?php echo $i; ?>) {
            animation-delay: <?php echo 0.1 * $i; ?>s;
        }
        <?php endfor; ?>
        
        /* Custom Navigation Styles */
        header {
            margin-bottom: 0;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 5px 25px rgba(230, 57, 70, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        header:hover {
            box-shadow: 0 8px 30px rgba(230, 57, 70, 0.25);
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            position: relative;
            padding: 5px 0;
            display: inline-block;
        }
        
        .logo::before {
            content: '';
            position: absolute;
            width: 35px;
            height: 35px;
            background-color: rgba(230, 57, 70, 0.15);
            border-radius: 50%;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: -1;
            transition: all 0.3s ease;
        }
        
        .logo:hover::before {
            transform: translateY(-50%) scale(1.2);
            background-color: rgba(230, 57, 70, 0.25);
        }
        
        .logo span {
            position: relative;
        }
        
        .logo span::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--theme-primary), var(--theme-secondary));
            bottom: -2px;
            left: 0;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .logo:hover span::after {
            transform: scaleX(1);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            align-items: center;
            margin: 0;
        }
        
        .nav-links li {
            margin: 0 10px;
            position: relative;
        }
        
        .nav-links a:not(.btn) {
            color: var(--theme-dark);
            font-weight: 600;
            padding: 10px 5px;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
        }
        
        .nav-links a:not(.btn)::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--theme-primary), transparent);
            bottom: 0;
            left: 0;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }
        
        .nav-links a:not(.btn):hover {
            color: var(--theme-primary);
        }
        
        .nav-links a:not(.btn):hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .nav-links .btn {
            margin-left: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
            transition: all 0.3s ease;
        }
        
        .nav-links .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(230, 57, 70, 0.4);
        }
        
        .nav-links .btn::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            top: 0;
            left: -100%;
            transition: 0.5s;
        }
        
        .nav-links .btn:hover::after {
            left: 100%;
        }
        
        .hamburger {
            display: none;
            font-size: 1.5rem;
            color: var(--theme-primary);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .hamburger:hover {
            transform: scale(1.1);
        }
        
        /* Active page indicator */
        .nav-links a.active {
            color: var(--theme-primary);
        }
        
        .nav-links a.active::before {
            transform: scaleX(1);
            background: linear-gradient(90deg, var(--theme-primary), transparent);
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 30px 20px;
            }
            
            .form-group {
                flex: 1 1 100%;
            }
            
            .page-banner {
                padding: 40px 0;
            }
            
            .page-banner h1 {
                font-size: 2.2rem;
            }
            
            .request-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .request-header .request-status {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner animate-fadeIn">
        <div class="container">
            <h1 class="animate-fadeInUp">Blood Bank</h1>
        </div>
    </div>
    
    <div class="container">
        <div class="blood-inventory">
            <h2 class="section-title animate-fadeIn">Current Blood Inventory</h2>
            
            <div class="inventory-grid">
                <?php foreach ($bloodInventory as $inventory): ?>
                    <?php 
                        // Determine inventory status
                        $status = 'good';
                        $statusText = 'Good Stock';
                        if ($inventory['units'] <= 5) {
                            $status = 'critical';
                            $statusText = 'Critical';
                        } elseif ($inventory['units'] <= 15) {
                            $status = 'low';
                            $statusText = 'Low Stock';
                        }
                    ?>
                    <div class="inventory-card">
                        <div class="inventory-header">
                            <?php echo htmlspecialchars($inventory['blood_type']); ?>
                        </div>
                        <div class="inventory-units"><?php echo htmlspecialchars($inventory['units']); ?></div>
                        <div class="inventory-status status-<?php echo $status; ?>"><?php echo $statusText; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-container animate-fadeInUp">
            <h2 class="form-title">Request Blood from Bank</h2>
            
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
            
            <form id="bank-request-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="patient_name">Patient Name*</label>
                        <input type="text" id="patient_name" name="patient_name" class="form-control" value="<?php echo isset($_POST['patient_name']) ? htmlspecialchars($_POST['patient_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_type">Blood Type Needed*</label>
                        <select id="blood_type" name="blood_type" class="form-control" required>
                            <option value="">Select Blood Type</option>
                            <?php foreach ($bloodInventory as $inventory): ?>
                                <option value="<?php echo htmlspecialchars($inventory['blood_type']); ?>" <?php echo (isset($_POST['blood_type']) && $_POST['blood_type'] == $inventory['blood_type']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($inventory['blood_type']); ?> (<?php echo htmlspecialchars($inventory['units']); ?> units available)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="units">Units Required*</label>
                        <input type="number" id="units" name="units" min="1" class="form-control" value="<?php echo isset($_POST['units']) ? htmlspecialchars($_POST['units']) : '1'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital">Hospital/Clinic Name*</label>
                        <input type="text" id="hospital" name="hospital" class="form-control" value="<?php echo isset($_POST['hospital']) ? htmlspecialchars($_POST['hospital']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="required_date">Required By Date*</label>
                        <input type="date" id="required_date" name="required_date" class="form-control" value="<?php echo isset($_POST['required_date']) ? htmlspecialchars($_POST['required_date']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason for Request*</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3" required><?php echo isset($_POST['reason']) ? htmlspecialchars($_POST['reason']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn primary-btn" style="width: 100%;">Submit Request</button>
                </div>
            </form>
        </div>
        
        <h2 class="section-title animate-fadeIn">Your Blood Bank Requests</h2>
        
        <?php if (empty($userRequests)): ?>
            <div class="no-requests animate-fadeIn">
                <i class="fas fa-clipboard-list"></i>
                <p>You don't have any blood bank requests yet.</p>
            </div>
        <?php else: ?>
            <div class="request-grid">
                <?php foreach ($userRequests as $index => $request): ?>
                    <div class="request-item">
                        <div class="request-header">
                            <div>
                                <div class="blood-type-indicator"><?php echo htmlspecialchars($request['blood_type']); ?></div>
                                <span style="font-size: 1.2rem; font-weight: 600; color: var(--theme-dark);"><?php echo htmlspecialchars($request['patient_name']); ?></span>
                            </div>
                            <div class="request-status status-<?php echo strtolower($request['status']); ?>"><?php echo ucfirst($request['status']); ?></div>
                        </div>
                        
                        <div class="request-details">
                            <div class="request-detail">
                                <strong>Units Requested</strong>
                                <span><?php echo htmlspecialchars($request['units']); ?></span>
                            </div>
                            
                            <div class="request-detail">
                                <strong>Hospital</strong>
                                <span><?php echo htmlspecialchars($request['hospital']); ?></span>
                            </div>
                            
                            <div class="request-detail">
                                <strong>Required By</strong>
                                <span><?php echo date('F j, Y', strtotime($request['required_date'])); ?></span>
                            </div>
                            
                            <div class="request-detail">
                                <strong>Requested On</strong>
                                <span><?php echo date('F j, Y', strtotime($request['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="request-detail" style="margin-top: 15px;">
                            <strong>Reason</strong>
                            <span style="display: block;"><?php echo htmlspecialchars($request['reason']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script>
        // Add 3D tilt effect for inventory cards
        document.querySelectorAll('.inventory-card').forEach((card) => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const xc = rect.width / 2;
                const yc = rect.height / 2;
                
                const dx = x - xc;
                const dy = y - yc;
                
                // Calculate rotation based on mouse position
                const tiltX = (dy / yc) * 5;
                const tiltY = -(dx / xc) * 5;
                
                this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale3d(1.05, 1.05, 1.05)`;
            });
            
            // Reset transform on mouse leave
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                setTimeout(() => {
                    this.style.transition = '';
                }, 300);
            });
        });
        
        // Form validation
        const form = document.getElementById('bank-request-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const units = document.getElementById('units').value;
                const bloodType = document.getElementById('blood_type').value;
                
                if (bloodType) {
                    // Find available units from the dropdown option text
                    const selectedOption = document.querySelector(`option[value="${bloodType}"]`);
                    const availableText = selectedOption.textContent;
                    const availableUnits = parseInt(availableText.match(/\((\d+) units available\)/)[1]);
                    
                    if (parseInt(units) > availableUnits) {
                        e.preventDefault();
                        alert(`Only ${availableUnits} units of ${bloodType} are available. Please request a smaller amount.`