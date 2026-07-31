<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Set a message to inform admins
$_SESSION['info_message'] = "The blood requests page has been merged with blood bank requests. You are being redirected.";

// Redirect to the blood bank requests page
header("Location: blood_bank_requests.php");
exit();
?> 