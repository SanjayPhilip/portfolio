<?php
// Test script to debug blood bank requests
require_once 'includes/db.php';

// Display any PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Blood Bank Requests Debugging</h1>";

// Check if table exists
try {
    $checkTable = fetchAll("SHOW TABLES LIKE 'blood_bank_requests'");
    echo "<p>Table exists: " . (count($checkTable) > 0 ? 'Yes' : 'No') . "</p>";
    
    if (count($checkTable) > 0) {
        // Count all records
        $countAll = fetchOne("SELECT COUNT(*) as count FROM blood_bank_requests");
        echo "<p>Total records in table: " . $countAll['count'] . "</p>";
        
        // Show table structure
        $structure = fetchAll("DESCRIBE blood_bank_requests");
        echo "<h2>Table Structure</h2>";
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
        
        // Insert a test record if requested
        if (isset($_GET['insert']) && $_GET['insert'] == 'yes') {
            // Create a test request
            $sql = "INSERT INTO blood_bank_requests 
                    (user_id, patient_name, blood_type, units, hospital, required_date, reason, status, created_at) 
                    VALUES (1, 'Test Patient', 'O+', 2, 'Test Hospital', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), 'Test Reason', 'pending', NOW())";
            
            executeQuery($sql);
            echo "<p>Test record inserted! Last inserted ID: " . lastInsertId() . "</p>";
        }
        
        // List all records
        $records = fetchAll("SELECT * FROM blood_bank_requests ORDER BY created_at DESC");
        echo "<h2>Current Records (" . count($records) . ")</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>User ID</th><th>Patient</th><th>Blood Type</th><th>Units</th><th>Hospital</th><th>Required Date</th><th>Status</th><th>Created At</th></tr>";
        
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td>" . $record['id'] . "</td>";
            echo "<td>" . $record['user_id'] . "</td>";
            echo "<td>" . $record['patient_name'] . "</td>";
            echo "<td>" . $record['blood_type'] . "</td>";
            echo "<td>" . $record['units'] . "</td>";
            echo "<td>" . $record['hospital'] . "</td>";
            echo "<td>" . $record['required_date'] . "</td>";
            echo "<td>" . $record['status'] . "</td>";
            echo "<td>" . $record['created_at'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Add a link to insert test record
echo "<p><a href='?insert=yes'>Insert Test Record</a> | <a href='admin/blood_bank_requests.php'>Go to Admin Page</a></p>";
?> 