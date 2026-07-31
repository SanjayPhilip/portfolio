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

// Check for success message in session
if (isset($_SESSION['donation_success'])) {
    $success = $_SESSION['donation_success'];
    unset($_SESSION['donation_success']);
}

// Check for error message in session
if (isset($_SESSION['donation_error'])) {
    $error = $_SESSION['donation_error'];
    unset($_SESSION['donation_error']);
}

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$donor = fetchOne($sql, [$userId]);

// Check if there's any previous donation
$sql = "SELECT * FROM blood_donations WHERE donor_id = ? ORDER BY donation_date DESC LIMIT 1";
$lastDonation = fetchOne($sql, [$userId]);

// Get approved blood bank donations
$sql = "SELECT * FROM blood_bank_donations 
        WHERE donor_id = ? AND status = 'approved' 
        ORDER BY created_at DESC";
$approvedDonations = fetchAll($sql, [$userId]);

// Get donation history
$sql = "SELECT * FROM blood_bank_donations 
        WHERE donor_id = ? 
        ORDER BY created_at DESC";
$donationHistory = fetchAll($sql, [$userId]);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donationDate = $_POST['donation_date'];
    $units = (int)$_POST['units'];
    $lastDonationDate = isset($_POST['last_donation']) ? $_POST['last_donation'] : null;
    
    // Validate input
    if (empty($donationDate) || empty($units)) {
        $_SESSION['donation_error'] = "Please fill in all required fields";
        header("Location: donate.php");
        exit();
    } elseif ($units <= 0 || $units > 5) {
        $_SESSION['donation_error'] = "Units must be between 1 and 5";
        header("Location: donate.php");
        exit();
    } else {
        // Check if donation date is today or in the future
        $today = date('Y-m-d');
        if ($donationDate < $today) {
            $_SESSION['donation_error'] = "Donation date cannot be in the past";
            header("Location: donate.php");
            exit();
        } else if ($lastDonationDate) {
            // Check if last donation was at least 90 days ago
            $lastDonationTime = strtotime($lastDonationDate);
            $donationTime = strtotime($donationDate);
            $daysDifference = ($donationTime - $lastDonationTime) / (60 * 60 * 24);
            
            if ($daysDifference < 90) {
                $_SESSION['donation_error'] = "You must wait at least 90 days between donations. You can donate after " . date('F j, Y', strtotime($lastDonationDate . ' + 90 days'));
                header("Location: donate.php");
                exit();
            } else {
                // Check for duplicate donation submission (same date, same user)
                $checkDuplicateSql = "SELECT id FROM blood_bank_donations 
                                    WHERE donor_id = ? AND donation_date = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                $duplicateDonation = fetchOne($checkDuplicateSql, [$userId, $donationDate]);
                
                if ($duplicateDonation) {
                    $_SESSION['donation_error'] = "You have already submitted a donation request for this date. Please check your donation history.";
                    header("Location: donate.php");
                    exit();
                } else {
                    submitDonation($userId, $donor['blood_type'], $units, $donationDate);
                    $_SESSION['donation_success'] = "Thank you for your donation! An administrator will review your request.";
                    header("Location: donate.php");
                    exit();
                }
            }
        } else {
            // Check for duplicate donation submission (same date, same user)
            $checkDuplicateSql = "SELECT id FROM blood_bank_donations 
                                WHERE donor_id = ? AND donation_date = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $duplicateDonation = fetchOne($checkDuplicateSql, [$userId, $donationDate]);
            
            if ($duplicateDonation) {
                $_SESSION['donation_error'] = "You have already submitted a donation request for this date. Please check your donation history.";
                header("Location: donate.php");
                exit();
            } else {
                submitDonation($userId, $donor['blood_type'], $units, $donationDate);
                $_SESSION['donation_success'] = "Thank you for your donation! An administrator will review your request.";
                header("Location: donate.php");
                exit();
            }
        }
    }
}

// Function to submit donation
function submitDonation($donorId, $bloodType, $units, $donationDate) {
    // Insert blood donation
    $sql = "INSERT INTO blood_bank_donations (donor_id, blood_type, units, donation_date, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    
    $params = [$donorId, $bloodType, $units, $donationDate];
    executeQuery($sql, $params);
}

// Get blood requests for the matching blood type
$matchingRequests = [];
if ($donor && isset($donor['blood_type'])) {
    $sql = "SELECT br.*, u.full_name as requester_name, u.phone as requester_phone 
            FROM blood_requests br 
            JOIN users u ON br.user_id = u.id 
            WHERE br.status = 'pending' AND br.blood_type = ? 
            ORDER BY 
              CASE 
                WHEN br.urgency = 'critical' THEN 1
                WHEN br.urgency = 'urgent' THEN 2
                ELSE 3
              END, 
              br.created_at DESC";
              
    $matchingRequests = fetchAll($sql, [$donor['blood_type']]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Blood - E Blood Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #1d3557;
            --accent-color: #457b9d;
            --danger-color: #e63946;
            --success-color: #2a9d8f;
            --light-color: #f1faee;
            --dark-color: #1d3557;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: linear-gradient(to bottom, #f5f5f5 0%, #ffffff 100%);
            background-attachment: fixed;
        }
        
        .page-banner {
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 80px 0 140px;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%);
            position: relative;
            margin-bottom: 80px;
        }
        
        .page-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .page-banner h1 {
            font-size: 3.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            margin: 0;
            position: relative;
        }
        
        .page-banner h1::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background-color: var(--accent-color);
            margin-top: 15px;
            border-radius: 2px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .donate-options {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin: -60px 0 40px;
        }
        
        .donate-option {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            padding: 30px;
            border-top: 5px solid var(--primary-color);
        }
        
        .donate-option:nth-child(2) {
            border-top-color: var(--accent-color);
        }
        
        .donate-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .donate-option i {
            font-size: 2.5rem;
            background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            display: inline-block;
            position: relative;
        }
        
        .donate-option:nth-child(2) i {
            background: linear-gradient(120deg, var(--accent-color), var(--danger-color));
            -webkit-background-clip: text;
            background-clip: text;
        }
        
        .donate-option h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            color: var(--secondary-color);
        }
        
        .donate-option h3::after {
            content: '';
            position: absolute;
            width: 30%;
            height: 3px;
            background-color: var(--primary-color);
            bottom: -5px;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .donate-option:nth-child(2) h3::after {
            background-color: var(--accent-color);
        }
        
        .donate-option:hover h3::after {
            width: 100%;
        }
        
        .donate-info {
            margin: 25px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }
        
        .donate-option:nth-child(2) .donate-info {
            border-left-color: var(--accent-color);
        }
        
        .donate-info p {
            margin-bottom: 12px;
            position: relative;
        }
        
        .compatible-types {
            margin-top: 20px;
            padding: 15px;
            background-color: rgba(42, 157, 143, 0.08);
            border-radius: 12px;
            animation: pulse 3s infinite;
        }
        
        .donate-option:nth-child(2) .compatible-types {
            background-color: rgba(233, 196, 106, 0.08);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(42, 157, 143, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(42, 157, 143, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(42, 157, 143, 0);
            }
        }
        
        .compatible-types h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .donate-option:nth-child(2) .compatible-types h4 {
            color: var(--accent-color);
        }
        
        .blood-type-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        
        .blood-type-item {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .donate-option:nth-child(2) .blood-type-item {
            background-color: var(--accent-color);
            color: var(--secondary-color);
        }
        
        .blood-type-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.15);
        }
        
        .last-donation-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            position: relative;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .primary-btn {
            background-color: #e63946;
            color: white;
        }
        
        .primary-btn:hover {
            background-color: #d62c39;
        }
        
        .primary-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }
        
        .primary-btn:hover::before {
            left: 100%;
        }
        
        .secondary-btn {
            background: linear-gradient(45deg, var(--accent-color), #e9b93b);
            color: var(--secondary-color);
            box-shadow: 0 4px 15px rgba(233, 196, 106, 0.3);
        }
        
        .secondary-btn:hover {
            box-shadow: 0 6px 20px rgba(233, 196, 106, 0.4);
            transform: translateY(-3px);
        }
        
        .secondary-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: 0.5s;
            z-index: -1;
        }
        
        .secondary-btn:hover::before {
            left: 100%;
        }
        
        .section-title {
            font-size: 2.2rem;
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            font-weight: 700;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .form-title {
            color: var(--secondary-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            position: relative;
        }
        
        .form-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
            margin: 0 15px 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        small {
            color: #6c757d;
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
        }
        
        .form-message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease;
        }
        
        .error-message {
            background-color: rgba(231, 111, 81, 0.1);
            border-left: 4px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .success-message {
            background-color: rgba(230, 57, 70, 0.1);
            border-left: 4px solid #e63946;
            color: #e63946;
        }
        
        .form-group h3 {
            color: var(--secondary-color);
            margin: 20px 0 15px;
            font-size: 1.2rem;
        }
        
        .form-group ul {
            padding-left: 25px;
            color: #555;
        }
        
        .form-group ul li {
            margin-bottom: 10px;
            position: relative;
        }
        
        .form-group ul li::before {
            content: '\f058';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: -25px;
            color: var(--primary-color);
        }
        
        .urgency-critical {
            color: var(--danger-color);
            font-weight: 700;
            animation: pulsateText 1.5s infinite;
        }
        
        .urgency-urgent {
            color: #e9c46a;
            font-weight: 600;
        }
        
        .urgency-standard {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        @keyframes pulsateText {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
            100% {
                opacity: 1;
            }
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .donate-options {
                flex-direction: column;
                margin-top: -40px;
            }
            
            .page-banner {
                padding: 60px 0 100px;
            }
            
            .page-banner h1 {
                font-size: 2.5rem;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .form-row {
                margin: 0;
            }
            
            .form-group {
                margin: 0 0 20px;
            }
        }
        
        /* Notification Section Styles */
        .notification-section {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            overflow: hidden;
            border: 1px solid rgba(230, 57, 70, 0.2);
        }
        
        .notification-header {
            background-color: rgba(230, 57, 70, 0.1);
            padding: 20px 30px;
            border-bottom: 1px solid rgba(230, 57, 70, 0.2);
        }
        
        .notification-header h2 {
            color: #e63946;
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .notification-header h2 i {
            margin-right: 10px;
            color: #e63946;
        }
        
        .notification-content {
            padding: 25px 30px;
        }
        
        .notification-content > p {
            font-size: 1.1rem;
            margin-bottom: 25px;
        }
        
        .next-steps {
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            margin-bottom: 25px;
            align-items: flex-start;
        }
        
        .step-number {
            width: 36px;
            height: 36px;
            background-color: #e63946;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-content h3 {
            color: #1d3557;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .step-content p {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .donation-center-address {
            background-color: rgba(241, 250, 238, 0.5);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #e63946;
            margin-top: 10px;
            line-height: 1.6;
        }
        
        .reference-number {
            background-color: rgba(230, 57, 70, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #e63946;
            margin-top: 10px;
            font-size: 1.1rem;
        }
        
        .preparation-list {
            padding-left: 20px;
            margin-top: 10px;
        }
        
        .preparation-list li {
            margin-bottom: 8px;
        }
        
        .donation-details {
            background-color: rgba(38, 70, 83, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .donation-details h3 {
            color: var(--dark-color);
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            background-color: white;
        }
        
        .details-table th, .details-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .details-table th {
            background-color: #e63946;
            color: white;
            font-weight: 600;
        }
        
        .details-table tr:last-child td {
            border-bottom: none;
        }
        
        .notification-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #e2e8f0;
            text-align: center;
            color: var(--dark-color);
        }
        
        .notification-footer p {
            margin: 5px 0;
        }
        
        /* Donation History Styles */
        .donation-history-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
        }
        
        .section-title {
            color: var(--dark-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .history-table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .history-table th, .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .history-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .history-table tr:last-child td {
            border-bottom: none;
        }
        
        .history-table tr:hover {
            background-color: rgba(42, 157, 143, 0.03);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: rgba(233, 196, 106, 0.2);
            color: #e9c46a;
        }
        
        .status-approved {
            background-color: rgba(230, 57, 70, 0.1);
            color: #e63946;
        }
        
        .status-completed {
            background-color: rgba(69, 123, 157, 0.2);
            color: #457b9d;
        }
        
        .status-rejected {
            background-color: rgba(29, 53, 87, 0.2);
            color: #1d3557;
        }
        
        .status-legend {
            margin-top: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .status-item:last-child {
            margin-bottom: 0;
        }
        
        .status-item .status-badge {
            width: 16px;
            height: 16px;
            padding: 0;
            margin-right: 10px;
        }
        
        .status-text {
            font-size: 0.85rem;
            color: #718096;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1>Donate Blood</h1>
        </div>
    </div>

    <?php if (!empty($approvedDonations)): ?>
    <div class="container">
        <div class="notification-section">
            <div class="notification-header">
                <h2><i class="fas fa-heart"></i> Your Donation Request Has Been Approved!</h2>
            </div>
            <div class="notification-content">
                <p>Great news! Your blood donation request has been approved by our administrators. Here's what you need to do next:</p>
                
                <div class="next-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3>Visit the Donation Center</h3>
                            <p>Please visit our donation center at the address below within the next 7 days:</p>
                            <div class="donation-center-address">
                                <strong>E Blood Connect Main Center</strong><br>
                                123 Health Avenue, Medical District<br>
                                Open daily: 8:00 AM - 6:00 PM
                            </div>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3>Bring Identification</h3>
                            <p>Please bring a valid photo ID and your donation approval reference number:</p>
                            <div class="reference-number">
                                <strong>Reference #:</strong> <?php echo $approvedDonations[0]['id']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3>Prepare Properly</h3>
                            <p>For a smooth donation experience:</p>
                            <ul class="preparation-list">
                                <li>Eat a healthy meal before donation</li>
                                <li>Stay hydrated (drink plenty of water)</li>
                                <li>Get a good night's sleep</li>
                                <li>Avoid alcohol for 24 hours before donation</li>
                                <li>Avoid strenuous physical activity before and after donation</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="donation-details">
                    <h3>Your Approved Donation Details:</h3>
                    <table class="details-table">
                        <tr>
                            <th>Blood Type</th>
                            <th>Units</th>
                            <th>Approved Date</th>
                        </tr>
                        <tr>
                            <td><?php echo htmlspecialchars($approvedDonations[0]['blood_type']); ?></td>
                            <td><?php echo htmlspecialchars($approvedDonations[0]['units']); ?></td>
                            <td><?php echo date('F j, Y', strtotime($approvedDonations[0]['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="notification-footer">
                    <p>Thank you for your generosity! Your donation will help save lives.</p>
                    <p>If you have any questions, please contact us at (555) 123-4567.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="donate-options">
            <div class="donate-option" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-hospital"></i>
                <h3>Donate to Blood Bank</h3>
                <p>Donate blood to our central blood bank to help multiple patients in need.</p>
                
                <div class="donate-info">
                    <p><strong>Your Blood Type:</strong> <?php echo ($donor && isset($donor['blood_type'])) ? $donor['blood_type'] : 'Not specified'; ?></p>
                    
                    <?php
                    // Show compatible blood types
                    $compatibilityMatrix = [
                        'O-' => ['O-'],
                        'O+' => ['O+', 'O-'],
                        'A-' => ['A-', 'O-'],
                        'A+' => ['A+', 'A-', 'O+', 'O-'],
                        'B-' => ['B-', 'O-'],
                        'B+' => ['B+', 'B-', 'O+', 'O-'],
                        'AB-' => ['AB-', 'A-', 'B-', 'O-'],
                        'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-']
                    ];
                    
                    $donorType = ($donor && isset($donor['blood_type'])) ? $donor['blood_type'] : '';
                    $compatibleTypes = isset($compatibilityMatrix[$donorType]) ? $compatibilityMatrix[$donorType] : [];
                    ?>
                    
                    <div class="compatible-types">
                        <h4>Your Blood Can Help These Types:</h4>
                        <div class="blood-type-list">
                            <?php if (!empty($donorType)): ?>
                                <?php foreach ($compatibilityMatrix as $recipient => $donors): ?>
                                    <?php if (in_array($donorType, $donors)): ?>
                                        <span class="blood-type-item"><?php echo $recipient; ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Please set your blood type in your profile</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($lastDonation): ?>
                        <div class="last-donation-info">
                            <p><strong>Last Donation:</strong> <?php echo date('F j, Y', strtotime($lastDonation['donation_date'])); ?></p>
                            <p><strong>Eligible to Donate Again:</strong> <?php echo date('F j, Y', strtotime($lastDonation['donation_date'] . ' + 90 days')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <a href="#donate-form" class="btn primary-btn">Donate Now</a>
            </div>
            
            <div class="donate-option" data-aos="fade-up" data-aos-delay="400">
                <i class="fas fa-hands-helping"></i>
                <h3>Respond to Requests</h3>
                <p>Help specific individuals who are in urgent need of your blood type.</p>
                
                <div class="donate-info">
                    <p><strong>Matching Requests:</strong> <?php echo count($matchingRequests); ?></p>
                    
                    <?php if (!empty($matchingRequests)): ?>
                        <p>Urgency Levels:</p>
                        <ul style="text-align: left; list-style-type: none; padding-left: 0;">
                            <?php 
                            $criticalCount = 0;
                            $urgentCount = 0;
                            $standardCount = 0;
                            
                            foreach ($matchingRequests as $request) {
                                if ($request['urgency'] == 'critical') $criticalCount++;
                                elseif ($request['urgency'] == 'urgent') $urgentCount++;
                                else $standardCount++;
                            }
                            ?>
                            
                            <?php if ($criticalCount > 0): ?>
                                <li><i class="fas fa-exclamation-circle" style="color: var(--danger-color);"></i> <span class="urgency-critical"><?php echo $criticalCount; ?> Critical</span></li>
                            <?php endif; ?>
                            
                            <?php if ($urgentCount > 0): ?>
                                <li><i class="fas fa-exclamation-triangle" style="color: #e9c46a;"></i> <span class="urgency-urgent"><?php echo $urgentCount; ?> Urgent</span></li>
                            <?php endif; ?>
                            
                            <?php if ($standardCount > 0): ?>
                                <li><i class="fas fa-info-circle" style="color: var(--primary-color);"></i> <span class="urgency-standard"><?php echo $standardCount; ?> Standard</span></li>
                            <?php endif; ?>
                        </ul>
                    <?php else: ?>
                        <p>No matching requests at the moment.</p>
                    <?php endif; ?>
                </div>
                
                <a href="request_blood.php" class="btn secondary-btn">View Requests</a>
            </div>
        </div>
        
        <div class="form-container" id="donate-form" data-aos="fade-up">
            <h2 class="form-title">Donate to Blood Bank</h2>
            
            <?php if (!empty($error)): ?>
                <div class="form-message error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="form-message success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php else: ?>
            
            <form id="donate-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_type">Your Blood Type</label>
                        <input type="text" id="blood_type" class="form-control" value="<?php echo ($donor && isset($donor['blood_type'])) ? $donor['blood_type'] : 'Not specified'; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="units">Units to Donate*</label>
                        <input type="number" id="units" name="units" min="1" max="5" class="form-control" value="<?php echo isset($_POST['units']) ? htmlspecialchars($_POST['units']) : '1'; ?>" required>
                        <small>Typically, one unit is approximately 450-500ml of blood</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="donation_date">Donation Date*</label>
                    <input type="date" id="donation_date" name="donation_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['donation_date']) ? htmlspecialchars($_POST['donation_date']) : date('Y-m-d'); ?>" required>
                </div>
                
                <?php if ($lastDonation): ?>
                    <input type="hidden" id="last_donation" name="last_donation" value="<?php echo date('Y-m-d', strtotime($lastDonation['donation_date'])); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <h3>Important Information</h3>
                    <ul>
                        <li>You must be at least 18 years old to donate blood</li>
                        <li>You must wait at least 90 days between blood donations</li>
                        <li>Ensure you are well-rested and hydrated before donation</li>
                        <li>Bring a valid ID to the donation center</li>
                        <li>The donation process takes about 30-45 minutes</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn primary-btn">Submit Donation</button>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
        
        <?php if (!empty($donationHistory)): ?>
        <div class="donation-history-section">
            <h2 class="section-title">Your Donation History</h2>
            
            <div class="history-table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Blood Type</th>
                            <th>Units</th>
                            <th>Donation Date</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donationHistory as $donation): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($donation['id']); ?></td>
                                <td><?php echo htmlspecialchars($donation['blood_type']); ?></td>
                                <td><?php echo htmlspecialchars($donation['units']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($donation['status']); ?>">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="status-legend">
                <div class="status-item">
                    <span class="status-badge status-pending"></span>
                    <span class="status-text">Pending: Your donation is under review.</span>
                </div>
                <div class="status-item">
                    <span class="status-badge status-approved"></span>
                    <span class="status-text">Approved: Your donation has been approved. Please proceed to the donation center.</span>
                </div>
                <div class="status-item">
                    <span class="status-badge status-completed"></span>
                    <span class="status-text">Completed: Your donation has been received and processed.</span>
                </div>
                <div class="status-item">
                    <span class="status-badge status-rejected"></span>
                    <span class="status-text">Rejected: Your donation request was declined. Please contact us for more information.</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="js/script.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const target = document.querySelector(this.getAttribute('href'));
                
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html> 