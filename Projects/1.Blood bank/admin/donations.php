<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle donation status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $donationId = $_GET['id'];
    
    if ($action === 'approve') {
        // Get the donation details
        $donationSql = "SELECT blood_type, units FROM blood_bank_donations WHERE id = ?";
        $donation = fetchOne($donationSql, [$donationId]);
        
        if ($donation) {
            // Update blood bank inventory (add units)
            $updateInventorySql = "UPDATE blood_bank SET units = units + ?, last_updated = NOW() WHERE blood_type = ?";
            executeQuery($updateInventorySql, [$donation['units'], $donation['blood_type']]);
            
            // Update donation status
            $updateDonationSql = "UPDATE blood_bank_donations SET status = 'approved', updated_at = NOW() WHERE id = ?";
            executeQuery($updateDonationSql, [$donationId]);
            
            $_SESSION['success_message'] = "Donation #$donationId has been approved and inventory updated.";
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE blood_bank_donations SET status = 'rejected', updated_at = NOW() WHERE id = ?";
        $params = [$donationId];
        executeQuery($sql, $params);
        
        $_SESSION['success_message'] = "Donation #$donationId has been rejected.";
    }
    
    // Redirect to prevent form resubmission
    header("Location: donations.php");
    exit();
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query - now using blood_bank_donations table
$sql = "SELECT bbd.*, u.full_name as donor_name, u.email, u.phone 
        FROM blood_bank_donations bbd 
        JOIN users u ON bbd.donor_id = u.id";
$params = [];

// Add filters
$conditions = [];

if ($status !== 'all') {
    $conditions[] = "bbd.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $conditions[] = "(u.full_name LIKE ? OR bbd.blood_type LIKE ? OR bbd.donation_center LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Add ordering
$sql .= " ORDER BY bbd.created_at DESC";

// Execute the query
$donations = fetchAll($sql, $params);

// Get admin info
$adminName = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Bank Donations - E Blood Connect</title>
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
            margin-right: 10px;
        }
        
        .filter-controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .filter-tabs {
            display: flex;
            flex-wrap: wrap;
            margin-right: auto;
        }
        
        .filter-tab {
            background: none;
            border: none;
            padding: 8px 16px;
            margin-right: 5px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            color: #718096;
            transition: all 0.3s ease;
        }
        
        .filter-tab:hover {
            background-color: #f8f9fa;
            color: var(--dark-color);
        }
        
        .filter-tab.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-input {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 8px 15px 8px 35px;
            width: 250px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
            width: 300px;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }
        
        .donations-table {
            width: 100%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .donations-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .donations-table th {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .donations-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .donations-table tr:last-child td {
            border-bottom: none;
        }
        
        .donations-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .donor-info {
            display: flex;
            align-items: center;
        }
        
        .donor-avatar {
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
            font-size: 1.2rem;
        }
        
        .donor-details {
            display: flex;
            flex-direction: column;
        }
        
        .donor-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .donor-contact {
            font-size: 0.85rem;
            color: #718096;
        }
        
        .donor-contact span {
            display: block;
            margin-bottom: 3px;
        }
        
        .donor-contact i {
            margin-right: 5px;
            width: 14px;
        }
        
        .blood-badge {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 5px;
            text-align: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: rgba(244, 162, 97, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(244, 162, 97, 0.3);
        }
        
        .status-approved {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(42, 157, 143, 0.3);
        }
        
        .status-rejected {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(214, 40, 40, 0.3);
        }
        
        .action-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .action-btn {
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .approve-btn {
            background-color: rgba(42, 157, 143, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(42, 157, 143, 0.3);
        }
        
        .approve-btn:hover {
            background-color: var(--success-color);
            color: white;
        }
        
        .reject-btn {
            background-color: rgba(214, 40, 40, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(214, 40, 40, 0.3);
        }
        
        .reject-btn:hover {
            background-color: var(--danger-color);
            color: white;
        }
        
        .no-donations {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            color: #718096;
        }
        
        .no-donations i {
            font-size: 4rem;
            color: #e2e8f0;
            margin-bottom: 20px;
            display: block;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: rgba(42, 157, 143, 0.1);
            border-left: 4px solid var(--success-color);
            color: var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(214, 40, 40, 0.1);
            border-left: 4px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .admin-sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-tabs {
                margin-bottom: 15px;
                justify-content: center;
            }
            
            .search-box {
                width: 100%;
            }
            
            .search-input {
                width: 100%;
            }
            
            .search-input:focus {
                width: 100%;
            }
            
            .donations-table {
                display: block;
                overflow-x: auto;
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
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="blood_bank_requests.php"><i class="fas fa-hand-holding-medical"></i> Blood Bank Requests</a></li>
                <li><a href="blood_inventory.php"><i class="fas fa-vials"></i> Inventory</a></li>
                <li class="active"><a href="donations.php"><i class="fas fa-heart"></i> Donations</a></li>
                <li><a href="admin_register.php"><i class="fas fa-user-shield"></i> Admin Registration</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Manage Blood Bank Donations</h1>
                
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
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="filter-controls">
                <div class="filter-tabs">
                    <a href="?status=all" class="filter-tab <?php echo ($status === 'all' || empty($status)) ? 'active' : ''; ?>">All Donations</a>
                    <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=approved" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">Approved</a>
                    <a href="?status=rejected" class="filter-tab <?php echo $status === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                </div>
                
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <form action="" method="GET">
                        <?php if ($status !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search by donor, blood type, or center" class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
            </div>
            
            <?php if (empty($donations)): ?>
                <div class="no-donations">
                    <i class="fas fa-heart"></i>
                    <p>No blood bank donations found.</p>
                    <p>Users can donate blood to the blood bank through the 'Blood Bank' section on the website.</p>
                </div>
            <?php else: ?>
                <div class="donations-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Blood Type</th>
                                <th>Units</th>
                                <th>Donation Center</th>
                                <th>Donation Date</th>
                                <th>Status</th>
                                <th>Created On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td>
                                        <div class="donor-info">
                                            <div class="donor-avatar">
                                                <?php echo substr($donation['donor_name'], 0, 1); ?>
                                            </div>
                                            <div class="donor-details">
                                                <div class="donor-name"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                                                <div class="donor-contact">
                                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($donation['email']); ?></span>
                                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($donation['phone']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="blood-badge"><?php echo htmlspecialchars($donation['blood_type']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($donation['units']); ?> Units</td>
                                    <td><?php echo isset($donation['donation_center']) ? htmlspecialchars($donation['donation_center']) : 'N/A'; ?></td>
                                    <td><?php echo isset($donation['donation_date']) ? date('M d, Y', strtotime($donation['donation_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($donation['status']); ?>">
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo isset($donation['created_at']) ? date('M d, Y', strtotime($donation['created_at'])) : 'N/A'; ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ($donation['status'] === 'pending'): ?>
                                                <a href="?action=approve&id=<?php echo $donation['id']; ?>" class="action-btn approve-btn" onclick="return confirm('Are you sure you want to approve this donation? This will add units to the blood inventory.');">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                                <a href="?action=reject&id=<?php echo $donation['id']; ?>" class="action-btn reject-btn" onclick="return confirm('Are you sure you want to reject this donation?');">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Search form submission
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html> 