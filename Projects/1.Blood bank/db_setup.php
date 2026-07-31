<?php
// E Blood Connect Database Setup Script

// Define configuration
$config = [
    'host' => 'localhost',  // Database host
    'user' => 'root',       // Database username (usually 'root' in local setups)
    'pass' => '',           // Database password (often empty in local setups)
    'db'   => 'ebloodconnect' // Database name
];

// Initialize variables
$message = '';
$status = 'info';
$dbCreated = false;
$tablesCreated = false;

// Create connection to MySQL server (without selecting a database)
$conn = new mysqli($config['host'], $config['user'], $config['pass']);

// Check connection
if ($conn->connect_error) {
    $message = "Connection failed: " . $conn->connect_error;
    $status = 'error';
} else {
    // Try to create the database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . $config['db'];
    if ($conn->query($sql) === TRUE) {
        $dbCreated = true;
        $conn->select_db($config['db']);
        
        // Check if tables already exist using the stored procedure
        $checkTablesExist = "
        DROP PROCEDURE IF EXISTS check_tables_exist;
        DELIMITER //
        CREATE PROCEDURE check_tables_exist()
        BEGIN
            DECLARE table_count INT DEFAULT 0;
            
            -- Count how many of our expected tables exist
            SELECT COUNT(*) INTO table_count
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name IN ('users', 'blood_bank', 'blood_requests', 'blood_donations', 'blood_bank_requests');
            
            -- If we don't have all 5 tables, we'll return 0 (not setup)
            IF table_count < 5 THEN
                SELECT 0 AS is_setup;
            ELSE
                SELECT 1 AS is_setup;
            END IF;
        END //
        DELIMITER ;
        ";
        
        // This will not work directly because of DELIMITER, so we need to split and execute
        $conn->query("DROP PROCEDURE IF EXISTS check_tables_exist");
        $conn->query("
        CREATE PROCEDURE check_tables_exist()
        BEGIN
            DECLARE table_count INT DEFAULT 0;
            
            -- Count how many of our expected tables exist
            SELECT COUNT(*) INTO table_count
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name IN ('users', 'blood_bank', 'blood_requests', 'blood_donations', 'blood_bank_requests');
            
            -- If we don't have all 5 tables, we'll return 0 (not setup)
            IF table_count < 5 THEN
                SELECT 0 AS is_setup;
            ELSE
                SELECT 1 AS is_setup;
            END IF;
        END
        ");
        
        // Call the procedure
        $result = $conn->query("CALL check_tables_exist()");
        $row = $result->fetch_assoc();
        $isSetup = $row['is_setup'];
        
        if ($isSetup) {
            $message = "Database already set up! Tables already exist.";
            $tablesCreated = true;
        } else {
            // Time to create tables and import data
            // Read the SQL file content
            $sqlFile = file_get_contents('db_setup.sql');
            
            // Split by semicolon to get individual queries
            // This is a simplistic approach and might not handle all SQL files perfectly
            $queries = explode(';', $sqlFile);
            
            // Execute each query
            $hasErrors = false;
            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue; // Skip empty queries
                
                // Skip the DELIMITER statements as they won't work in this context
                if (strpos($query, 'DELIMITER') === 0) continue;
                if (strpos($query, 'CREATE PROCEDURE') === 0) continue;
                
                if ($conn->query($query) !== TRUE) {
                    $message .= "Error executing query: " . $conn->error . "<br>";
                    $hasErrors = true;
                }
            }
            
            if (!$hasErrors) {
                $message = "Database setup completed successfully!";
                $tablesCreated = true;
                $status = 'success';
            } else {
                $message = "Database setup completed with errors: " . $message;
                $status = 'error';
            }
        }
    } else {
        $message = "Error creating database: " . $conn->error;
        $status = 'error';
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E Blood Connect - Database Setup</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2 {
            color: #d62828;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 20px 0;
        }
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info {
            background-color: #cce5ff;
            border-left: 5px solid #0d6efd;
        }
        .success {
            background-color: #d4edda;
            border-left: 5px solid #198754;
        }
        .error {
            background-color: #f8d7da;
            border-left: 5px solid #dc3545;
        }
        .steps {
            margin-top: 25px;
        }
        .step {
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 3px solid #ddd;
        }
        .step.completed {
            border-left-color: #198754;
        }
        .step.pending {
            border-left-color: #6c757d;
        }
        .step.error {
            border-left-color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #d62828;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #b91919;
        }
    </style>
</head>
<body>
    <h1>E Blood Connect - Database Setup</h1>
    
    <div class="card">
        <div class="message <?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
        
        <div class="steps">
            <h2>Setup Progress</h2>
            
            <div class="step <?php echo $dbCreated ? 'completed' : 'error'; ?>">
                <h3>1. Create Database</h3>
                <p><?php echo $dbCreated ? '✓ Database created/selected successfully' : '✗ Failed to create database'; ?></p>
            </div>
            
            <div class="step <?php echo $tablesCreated ? 'completed' : ($dbCreated ? 'error' : 'pending'); ?>">
                <h3>2. Create Tables & Import Data</h3>
                <p>
                    <?php 
                    if ($tablesCreated) {
                        echo '✓ Tables created and initial data imported';
                    } elseif ($dbCreated) {
                        echo '✗ Failed to create tables';
                    } else {
                        echo 'Pending database creation';
                    }
                    ?>
                </p>
            </div>
            
            <?php if ($tablesCreated): ?>
            <div class="step completed">
                <h3>3. Setup Complete</h3>
                <p>✓ Your database is ready! You can now start using E Blood Connect.</p>
                
                <h4>Default Admin Login:</h4>
                <ul>
                    <li><strong>Email:</strong> admin@ebloodconnect.com</li>
                    <li><strong>Password:</strong> admin123</li>
                </ul>
                
                <p><strong>Important:</strong> Please change the admin password after your first login.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <?php if ($tablesCreated): ?>
                <a href="index.php" class="btn">Go to Homepage</a>
            <?php else: ?>
                <a href="db_setup.php" class="btn">Try Again</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 