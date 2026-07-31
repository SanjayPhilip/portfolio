<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get admin info
$adminName = $_SESSION['admin_name'];

// Handle inventory updates
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $inventoryId = $_POST['inventory_id'];
    $units = $_POST['units'];
    
    // Validate units (must be zero or positive)
    if ($units < 0) {
        $error = "Units must be zero or a positive number.";
    } else {
        // Update the inventory
        $sql = "UPDATE blood_bank SET units = ?, last_updated = NOW() WHERE id = ?";
        $params = [$units, $inventoryId];
        
        if (executeQuery($sql, $params)) {
            $success = "Blood inventory has been updated successfully.";
        } else {
            $error = "Failed to update blood inventory.";
        }
    }
}

// Get blood inventory
$sql = "SELECT * FROM blood_bank ORDER BY blood_type";
$bloodInventory = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - E Blood Connect</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #e63946;
            --secondary-color: #457b9d;
            --dark-color: #1d3557;
            --light-color: #f1faee;
            --success-color: #2a9d8f;
            --warning-color: #e9c46a;
            --danger-color: #d62828;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: var(--dark-color);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }
        
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-header h1 {
            margin: 0;
            color: var(--dark-color);
            font-size: 2rem;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
        }
        
        .admin-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 600;
        }
        
        .admin-user-info {
            font-size: 0.95rem;
        }
        
        .admin-user-info span {
            display: block;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .admin-user-info small {
            color: #718096;
        }
        
        .admin-logo {
            padding: 20px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .admin-logo span {
            color: var(--primary-color);
        }
        
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-menu li {
            margin-bottom: 5px;
        }
        
        .admin-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .admin-menu a:hover, .admin-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .admin-menu a i {
            width: 20px;
            margin-right: 10px;
        }
        
        .alert-container {
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
            border-left: 5px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
            border-left: 5px solid var(--danger-color);
        }
        
        .alert-icon {
            font-size: 1.2rem;
            margin-right: 15px;
        }
        
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .inventory-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .blood-type-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .blood-badge {
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            margin-right: 15px;
        }
        
        .blood-info h2 {
            margin: 0 0 5px 0;
            font-size: 1.5rem;
            color: var(--dark-color);
        }
        
        .blood-info p {
            margin: 0;
            font-size: 0.9rem;
            color: #718096;
        }
        
        .inventory-details {
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .inventory-stat {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .inventory-stat:last-child {
            margin-bottom: 0;
        }
        
        .inventory-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background-color: rgba(69, 123, 157, 0.1);
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .inventory-stat-info {
            flex: 1;
        }
        
        .inventory-stat-label {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 5px;
        }
        
        .inventory-stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .inventory-status {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .status-critical {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
        }
        
        .status-low {
            background-color: rgba(233, 196, 106, 0.1);
            color: var(--warning-color);
        }
        
        .status-good {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
        }
        
        .update-form {
            margin-top: auto;
        }
        
        .input-group {
            display: flex;
            margin-bottom: 15px;
        }
        
        .input-group input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 5px 0 0 5px;
            font-size: 0.9rem;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .update-button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 5px 5px 0;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .update-button:hover {
            background-color: #3b6a8a;
        }
        
        .no-inventory-message {
            text-align: center;
            padding: 50px 0;
            grid-column: 1 / -1;
            color: #718096;
        }
        
        .no-inventory-message i {
            display: block;
            font-size: 3rem;
            margin-bottom: 20px;
            color: #cbd5e0;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .admin-sidebar .admin-logo {
                padding: 15px 0;
                font-size: 1.2rem;
            }
            
            .admin-sidebar .admin-logo span {
                display: none;
            }
            
            .admin-menu a span {
                display: none;
            }
            
            .admin-menu a {
                justify-content: center;
                padding: 12px 0;
            }
            
            .admin-menu a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .admin-content {
                margin-left: 70px;
            }
            
            .inventory-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                E <span>Blood Connect</span>
            </div>
            
            <ul class="admin-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="blood_bank_requests.php"><i class="fas fa-hand-holding-medical"></i> <span>Blood Bank Requests</span></a></li>
                <li class="active"><a href="blood_inventory.php"><i class="fas fa-vials"></i> <span>Inventory</span></a></li>
                <li><a href="donations.php"><i class="fas fa-heart"></i> <span>Donations</span></a></li>
                <li><a href="admin_register.php"><i class="fas fa-user-shield"></i> <span>Admin Registration</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Blood Inventory Management</h1>
                
                <div class="admin-user">
                    <div class="admin-user-avatar">
                        <?php echo substr($adminName, 0, 1); ?>
                    </div>
                    <div class="admin-user-info">
                        <span><?php echo $adminName; ?></span>
                        <small>Administrator</small>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert-container">
                    <div class="alert alert-error">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-message">
                            <?php echo $error; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert-container">
                    <div class="alert alert-success">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-message">
                            <?php echo $success; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="inventory-grid">
                <?php if (empty($bloodInventory)): ?>
                    <div class="no-inventory-message">
                        <i class="fas fa-vial"></i>
                        <p>No blood inventory records found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($bloodInventory as $inventory): ?>
                        <?php 
                            // Determine inventory status
                            $statusClass = 'status-good';
                            $statusText = 'Good Stock';
                            
                            if ($inventory['units'] <= 5) {
                                $statusClass = 'status-critical';
                                $statusText = 'Critical';
                            } elseif ($inventory['units'] <= 15) {
                                $statusClass = 'status-low';
                                $statusText = 'Low Stock';
                            }
                        ?>
                        <div class="inventory-card">
                            <div class="inventory-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></div>
                            
                            <div class="blood-type-header">
                                <div class="blood-badge"><?php echo htmlspecialchars($inventory['blood_type']); ?></div>
                                <div class="blood-info">
                                    <h2><?php echo htmlspecialchars($inventory['blood_type']); ?> Blood</h2>
                                    <p>Updated <?php echo isset($inventory['last_updated']) ? date('M d, Y', strtotime($inventory['last_updated'])) : 'N/A'; ?></p>
                                </div>
                            </div>
                            
                            <div class="inventory-details">
                                <div class="inventory-stat">
                                    <div class="inventory-stat-icon">
                                        <i class="fas fa-tint"></i>
                                    </div>
                                    <div class="inventory-stat-info">
                                        <div class="inventory-stat-label">Available Units</div>
                                        <div class="inventory-stat-value"><?php echo htmlspecialchars($inventory['units']); ?> Units</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="update-form">
                                <form action="" method="POST">
                                    <input type="hidden" name="inventory_id" value="<?php echo $inventory['id']; ?>">
                                    <div class="input-group">
                                        <input type="number" name="units" value="<?php echo htmlspecialchars($inventory['units']); ?>" min="0" required>
                                        <button type="submit" class="update-button" name="update_inventory">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 