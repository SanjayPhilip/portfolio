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

// Get statistics
// Total users
$sql = "SELECT COUNT(*) as total FROM users";
$totalUsers = fetchOne($sql)['total'];

// Total admins
$sql = "SELECT COUNT(*) as total FROM admins";
$totalAdmins = fetchOne($sql)['total'];

// Total blood bank requests
$sql = "SELECT COUNT(*) as total FROM blood_bank_requests";
$totalBankRequests = fetchOne($sql)['total'];

// Debug info for blood bank requests
echo "<!-- Blood Bank Requests Debug: Total count = " . $totalBankRequests . " -->";

// Pending blood bank requests
$sql = "SELECT COUNT(*) as total FROM blood_bank_requests WHERE status = 'pending'";
$pendingBankRequests = fetchOne($sql)['total'];

// Debug info for pending bank requests
echo "<!-- Pending Blood Bank Requests Debug: Count = " . $pendingBankRequests . " -->";

// Total donations
$sql = "SELECT COUNT(*) as total FROM blood_bank_donations";
$totalDonations = fetchOne($sql)['total'];

// Pending donations
$sql = "SELECT COUNT(*) as total FROM blood_bank_donations WHERE status = 'pending'";
$pendingDonations = fetchOne($sql)['total'];

// Get blood bank inventory
$sql = "SELECT * FROM blood_bank ORDER BY blood_type";
$bloodInventory = fetchAll($sql);

// Get recent blood bank requests
$sql = "SELECT bbr.*, u.full_name as requester_name 
        FROM blood_bank_requests bbr 
        JOIN users u ON bbr.user_id = u.id 
        ORDER BY bbr.created_at DESC 
        LIMIT 5";
$recentBankRequests = fetchAll($sql);

// Get recent donations
$sql = "SELECT bbd.*, u.full_name as donor_name 
        FROM blood_bank_donations bbd 
        JOIN users u ON bbd.donor_id = u.id 
        ORDER BY bbd.created_at DESC 
        LIMIT 5";
$recentDonations = fetchAll($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E Blood Connect</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .users-icon {
            background-color: rgba(69, 123, 157, 0.1);
            color: var(--secondary-color);
        }
        
        .admins-icon {
            background-color: rgba(157, 78, 221, 0.1);
            color: #9D4EDD;
        }
        
        .requests-icon {
            background-color: rgba(230, 57, 70, 0.1);
            color: var(--primary-color);
        }
        
        .pending-icon {
            background-color: rgba(233, 196, 106, 0.1);
            color: var(--warning-color);
        }
        
        .donations-icon {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            position: relative;
        }
        
        .inventory-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .inventory-card:last-child {
            border-bottom: none;
        }
        
        .blood-type {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }
        
        .blood-badge {
            background-color: var(--primary-color);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 700;
        }
        
        .units {
            font-size: 1.1rem;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .units-status {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 5px;
        }
        
        .units-critical {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
        }
        
        .units-low {
            background-color: rgba(233, 196, 106, 0.1);
            color: var(--warning-color);
        }
        
        .units-good {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--dark-color);
        }
        
        .card-header a {
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-decoration: none;
            font-weight: 600;
        }
        
        .card-header a:hover {
            color: var(--primary-color);
        }
        
        .request-item, .donation-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .request-item:last-child, .donation-item:last-child {
            border-bottom: none;
        }
        
        .request-info, .donation-info {
            flex: 1;
        }
        
        .request-name, .donation-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .request-details, .donation-details {
            display: flex;
            font-size: 0.85rem;
            color: #718096;
        }
        
        .request-details span, .donation-details span {
            margin-right: 15px;
            display: flex;
            align-items: center;
        }
        
        .request-details i, .donation-details i {
            margin-right: 5px;
            font-size: 0.8rem;
        }
        
        .request-status, .donation-status {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .status-pending {
            background-color: rgba(233, 196, 106, 0.1);
            color: var(--warning-color);
        }
        
        .status-approved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
        }
        
        .status-completed {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
        }
        
        .status-rejected {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
        }
        
        .no-items-message {
            text-align: center;
            padding: 30px 0;
            color: #718096;
        }
        
        .no-items-message i {
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #cbd5e0;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
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
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-heartbeat"></i> E Blood <span>Connect</span>
            </div>
            
            <ul class="admin-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="blood_bank_requests.php"><i class="fas fa-hand-holding-medical"></i> Blood Bank Requests</a></li>
                <li><a href="blood_inventory.php"><i class="fas fa-vials"></i> Inventory</a></li>
                <li><a href="donations.php"><i class="fas fa-heart"></i> Donations</a></li>
                <li><a href="admin_register.php"><i class="fas fa-user-shield"></i> Admin Registration</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                
                <div class="admin-user">
                    <div class="admin-user-avatar">
                        <?php echo substr($adminName, 0, 1); ?>
                    </div>
                    <div class="admin-user-info">
                        <span><?php echo htmlspecialchars($adminName); ?></span>
                        <small>Administrator</small>
                    </div>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon users-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $totalUsers; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon admins-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $totalAdmins; ?></div>
                        <div class="stat-label">Total Admins</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon donations-icon">
                        <i class="fas fa-hand-holding-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $totalDonations; ?></div>
                        <div class="stat-label">Total Donations</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pendingDonations; ?></div>
                        <div class="stat-label">Pending Donations</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon requests-icon">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $totalBankRequests; ?></div>
                        <div class="stat-label">Bank Requests</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pendingBankRequests; ?></div>
                        <div class="stat-label">Pending Bank Requests</div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Blood Inventory</h2>
                        <a href="blood_inventory.php">View All</a>
                    </div>
                    
                    <?php if (empty($bloodInventory)): ?>
                        <div class="no-items-message">
                            <i class="fas fa-vial"></i>
                            <p>No blood inventory records found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bloodInventory as $inventory): ?>
                            <?php 
                                // Determine inventory status
                                $statusClass = 'units-good';
                                $statusText = 'Good Stock';
                                
                                if ($inventory['units'] <= 5) {
                                    $statusClass = 'units-critical';
                                    $statusText = 'Critical';
                                } elseif ($inventory['units'] <= 15) {
                                    $statusClass = 'units-low';
                                    $statusText = 'Low Stock';
                                }
                            ?>
                            <div class="inventory-card">
                                <div class="blood-type">
                                    <div class="blood-badge"><?php echo htmlspecialchars($inventory['blood_type']); ?></div>
                                    <span><?php echo htmlspecialchars($inventory['blood_type']); ?> Blood</span>
                                </div>
                                <div class="units">
                                    <?php echo htmlspecialchars($inventory['units']); ?> Units
                                    <span class="units-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Blood Bank Requests</h2>
                        <a href="blood_bank_requests.php">View All</a>
                    </div>
                    
                    <?php if (empty($recentBankRequests)): ?>
                        <div class="no-items-message">
                            <i class="fas fa-hospital"></i>
                            <p>No blood bank requests found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentBankRequests as $request): ?>
                            <div class="request-item">
                                <div class="request-info">
                                    <div class="request-name"><?php echo htmlspecialchars($request['requester_name']); ?></div>
                                    <div class="request-details">
                                        <span><i class="fas fa-tint"></i> <?php echo htmlspecialchars($request['blood_type']); ?></span>
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['patient_name']); ?></span>
                                        <span><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($request['hospital']); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="request-status status-<?php echo strtolower($request['status']); ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Blood Bank Donations</h2>
                        <a href="donations.php">View All</a>
                    </div>
                    
                    <?php if (empty($recentDonations)): ?>
                        <div class="no-items-message">
                            <i class="fas fa-hand-holding-medical"></i>
                            <p>No donations found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentDonations as $donation): ?>
                            <div class="donation-item">
                                <div class="donation-info">
                                    <div class="donation-name"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                                    <div class="donation-details">
                                        <span><i class="fas fa-tint"></i> <?php echo htmlspecialchars($donation['blood_type']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></span>
                                    </div>
                                </div>
                                <div class="donation-status status-<?php echo strtolower($donation['status']); ?>">
                                    <?php echo ucfirst($donation['status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 