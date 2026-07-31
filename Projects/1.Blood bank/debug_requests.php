<?php
// Debug script to check blood requests
require_once 'includes/db.php';
session_start();

// Display any PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Blood Requests Debugging</h1>";

// Check the currently logged in user
$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
echo "<p>Currently logged in as User ID: " . $currentUserId . "</p>";

// Get list of users for test requests
$users = fetchAll("SELECT id, full_name, email FROM users ORDER BY id");
echo "<h2>Available Users for Test Requests</h2>";
echo "<ul>";
foreach($users as $user) {
    echo "<li>ID: " . $user['id'] . " - " . $user['full_name'] . " (" . $user['email'] . ") ";
    echo "<a href='?add=yes&user_id=" . $user['id'] . "'>Add test request as this user</a></li>";
}
echo "</ul>";

// Check if table exists
try {
    $checkTable = fetchAll("SHOW TABLES LIKE 'blood_requests'");
    echo "<p>Table exists: " . (count($checkTable) > 0 ? 'Yes' : 'No') . "</p>";
    
    if (count($checkTable) > 0) {
        // Count all records
        $countAll = fetchOne("SELECT COUNT(*) as count FROM blood_requests");
        echo "<p>Total blood requests: " . $countAll['count'] . "</p>";
        
        // Count pending requests
        $countPending = fetchOne("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'pending'");
        echo "<p>Pending blood requests: " . $countPending['count'] . "</p>";
        
        // Show table structure
        $structure = fetchAll("DESCRIBE blood_requests");
        echo "<h2>Table Structure</h2>";
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Add a test request if requested
        if (isset($_GET['add']) && $_GET['add'] == 'yes') {
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1;
            
            // If the user_id is the same as current user, suggest using a different user
            if ($userId == $currentUserId) {
                echo "<div style='background-color: #fff3cd; border: 1px solid #ffeeba; padding: 10px; margin: 10px 0;'>";
                echo "<strong>Warning:</strong> You're adding a test request for the currently logged in user. ";
                echo "This won't appear in 'Other users' blood requests on the request_blood.php page.";
                echo "</div>";
            }
            
            // Generate a random blood type
            $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            $randomBloodType = $bloodTypes[array_rand($bloodTypes)];
            
            // Generate a random urgency
            $urgencies = ['regular', 'urgent', 'critical'];
            $randomUrgency = $urgencies[array_rand($urgencies)];
            
            // Insert a test blood request
            $sql = "INSERT INTO blood_requests 
                    (user_id, patient_name, blood_type, units, hospital, hospital_address, urgency, required_date, reason, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $userId, 
                'Test Patient ' . rand(1000, 9999), 
                $randomBloodType, 
                rand(1, 5), 
                'Test Hospital', 
                '123 Hospital St', 
                $randomUrgency, 
                date('Y-m-d', strtotime('+' . rand(1, 14) . ' days')),
                'Test Reason - Emergency blood needed',
                'pending'
            ];
            
            executeQuery($sql, $params);
            $lastId = lastInsertId();
            echo "<p style='color: green;'>Test blood request inserted! Last ID: " . $lastId . "</p>";
            echo "<p><a href='request_blood.php'>View on Request Blood Page</a></p>";
        }
        
        // List all pending requests
        $pendingRequests = fetchAll("SELECT br.*, u.full_name as requester_name 
                                    FROM blood_requests br 
                                    JOIN users u ON br.user_id = u.id 
                                    WHERE br.status = 'pending'
                                    ORDER BY br.created_at DESC");
                                    
        echo "<h2>Pending Blood Requests (" . count($pendingRequests) . ")</h2>";
        
        if (count($pendingRequests) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Requester</th><th>Patient</th><th>Blood Type</th><th>Units</th><th>Hospital</th><th>Urgency</th><th>Required Date</th><th>Created At</th><th>Actions</th></tr>";
            
            foreach ($pendingRequests as $request) {
                $isCurrentUser = ($request['user_id'] == $currentUserId);
                echo "<tr" . ($isCurrentUser ? " style='background-color: #e2f0fb;'" : "") . ">";
                echo "<td>" . $request['id'] . "</td>";
                echo "<td>" . $request['user_id'] . "</td>";
                echo "<td>" . $request['requester_name'] . ($isCurrentUser ? " (You)" : "") . "</td>";
                echo "<td>" . $request['patient_name'] . "</td>";
                echo "<td>" . $request['blood_type'] . "</td>";
                echo "<td>" . $request['units'] . "</td>";
                echo "<td>" . $request['hospital'] . "</td>";
                echo "<td>" . $request['urgency'] . "</td>";
                echo "<td>" . $request['required_date'] . "</td>";
                echo "<td>" . $request['created_at'] . "</td>";
                echo "<td><a href='?delete=" . $request['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No pending blood requests found.</p>";
        }
        
        // Delete a request if requested
        if (isset($_GET['delete'])) {
            $requestId = (int)$_GET['delete'];
            executeQuery("DELETE FROM blood_requests WHERE id = ?", [$requestId]);
            echo "<p style='color: green;'>Request ID " . $requestId . " deleted!</p>";
            echo "<script>window.location.href = 'debug_requests.php';</script>";
        }
        
        // Debug query in request_blood.php
        echo "<h2>Debug SQL Query from request_blood.php</h2>";
        
        echo "<p>Current user ID: " . $currentUserId . "</p>";
        
        // Run the same query from request_blood.php
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
                
        $bloodRequests = fetchAll($sql, [$currentUserId]);
        echo "<p>Query results (excluding current user): " . count($bloodRequests) . "</p>";
        
        if (count($bloodRequests) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Requester</th><th>Patient</th><th>Blood Type</th><th>Units</th><th>Hospital</th><th>Urgency</th></tr>";
            
            foreach ($bloodRequests as $request) {
                echo "<tr>";
                echo "<td>" . $request['id'] . "</td>";
                echo "<td>" . $request['user_id'] . "</td>";
                echo "<td>" . $request['requester_name'] . "</td>";
                echo "<td>" . $request['patient_name'] . "</td>";
                echo "<td>" . $request['blood_type'] . "</td>";
                echo "<td>" . $request['units'] . "</td>";
                echo "<td>" . $request['hospital'] . "</td>";
                echo "<td>" . $request['urgency'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No results from the query. Possible reasons:</p>";
            echo "<ul>";
            echo "<li>No blood requests with 'pending' status</li>";
            echo "<li>All pending requests belong to the current user</li>";
            echo "<li>Issue with the WHERE clause condition</li>";
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Add links for testing
echo "<p><a href='?'>Refresh Page</a> | <a href='request_blood.php'>Go to Request Blood Page</a></p>";
?> 