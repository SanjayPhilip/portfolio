<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle request status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $requestId = $_GET['id'];
    
    if ($action === 'approve') {
        // First get the blood request details to check the inventory
        $requestSql = "SELECT blood_type, units FROM blood_bank_requests WHERE id = ?";
        $request = fetchOne($requestSql, [$requestId]);
        
        if ($request) {
            // Check if we have enough units in inventory
            $inventorySql = "SELECT units FROM blood_bank WHERE blood_type = ?";
            $inventory = fetchOne($inventorySql, [$request['blood_type']]);
            
            if ($inventory && $inventory['units'] >= $request['units']) {
                // Update blood bank inventory
                $updateInventorySql = "UPDATE blood_bank SET units = units - ? WHERE blood_type = ?";
                executeQuery($updateInventorySql, [$request['units'], $request['blood_type']]);
                
                // Update request status
                $updateRequestSql = "UPDATE blood_bank_requests SET status = 'approved', updated_at = NOW() WHERE id = ?";
                executeQuery($updateRequestSql, [$requestId]);
                
                $_SESSION['success_message'] = "Blood bank request #$requestId has been approved and inventory updated.";
            } else {
                $_SESSION['error_message'] = "Cannot approve request #$requestId. Insufficient stock for " . $request['blood_type'] . " blood.";
            }
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE blood_bank_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?";
        $params = [$requestId];
        executeQuery($sql, $params);
        
        $_SESSION['success_message'] = "Blood bank request #$requestId has been rejected.";
    } elseif ($action === 'complete') {
        $sql = "UPDATE blood_bank_requests SET status = 'completed', updated_at = NOW() WHERE id = ?";
        $params = [$requestId];
        executeQuery($sql, $params);
        
        $_SESSION['success_message'] = "Blood bank request #$requestId has been marked as completed.";
    }
    
    // Redirect to prevent form resubmission
    header("Location: blood_bank_requests.php");
    exit();
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$sql = "SELECT bbr.*, u.full_name as requester_name, u.email, u.phone 
        FROM blood_bank_requests bbr 
        JOIN users u ON bbr.user_id = u.id";
$params = [];

// Add filters
$conditions = [];

if ($status !== 'all') {
    $conditions[] = "bbr.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $conditions[] = "(u.full_name LIKE ? OR bbr.patient_name LIKE ? OR bbr.blood_type LIKE ? OR bbr.hospital LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Add ordering
$sql .= " ORDER BY bbr.created_at DESC";

// Execute the query
$requests = fetchAll($sql, $params);

// Add debugging code
echo "<!-- SQL Query: " . $sql . " -->";
echo "<!-- Params: " . json_encode($params) . " -->";
echo "<!-- Result count: " . count($requests) . " -->";
if (count($requests) == 0) {
    // Check if table exists and has data
    $checkTable = fetchAll("SHOW TABLES LIKE 'blood_bank_requests'");
    echo "<!-- Table exists: " . (count($checkTable) > 0 ? 'Yes' : 'No') . " -->";
    
    if (count($checkTable) > 0) {
        $countAll = fetchOne("SELECT COUNT(*) as count FROM blood_bank_requests");
        echo "<!-- Total records in table: " . $countAll['count'] . " -->";
    }
}

// Get admin info
$adminName = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Bank Requests - E Blood Connect</title>
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
        
        .blood-requests-table {
            width: 100%;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .blood-requests-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .blood-requests-table th {
            background-color: #f8f9fa;
            color: var(--dark-color);
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .blood-requests-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .blood-requests-table tr:last-child td {
            border-bottom: none;
        }
        
        .blood-requests-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .patient-info {
            display: flex;
            align-items: center;
        }
        
        .patient-avatar {
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
        
        .patient-details {
            display: flex;
            flex-direction: column;
        }
        
        .patient-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .patient-meta {
            font-size: 0.85rem;
            color: #718096;
        }
        
        .patient-meta span {
            display: block;
            margin-bottom: 3px;
        }
        
        .patient-meta i {
            margin-right: 5px;
            width: 14px;
        }
        
        .contact-display {
            display: flex;
            flex-direction: column;
            font-size: 0.9rem;
        }
        
        .contact-display .requester-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .contact-display span {
            margin-bottom: 3px;
            color: #718096;
        }
        
        .contact-display i {
            margin-right: 5px;
            width: 14px;
            color: var(--secondary-color);
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
        
        .status-completed {
            background-color: rgba(69, 123, 157, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(69, 123, 157, 0.3);
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
        
        .complete-btn {
            background-color: rgba(69, 123, 157, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(69, 123, 157, 0.3);
        }
        
        .complete-btn:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .no-requests {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            color: #718096;
        }
        
        .no-requests i {
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
            
            .blood-requests-table {
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
                <li class="active"><a href="blood_bank_requests.php"><i class="fas fa-hand-holding-medical"></i> Blood Bank Requests</a></li>
                <li><a href="blood_inventory.php"><i class="fas fa-vials"></i> Inventory</a></li>
                <li><a href="donations.php"><i class="fas fa-heart"></i> Donations</a></li>
                <li><a href="admin_register.php"><i class="fas fa-user-shield"></i> Admin Registration</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>Manage Blood Bank Requests</h1>
                
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
                    <a href="?status=all" class="filter-tab <?php echo ($status === 'all' || empty($status)) ? 'active' : ''; ?>">All Requests</a>
                    <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=approved" class="filter-tab <?php echo $status === 'approved' ? 'active' : ''; ?>">Approved</a>
                    <a href="?status=completed" class="filter-tab <?php echo $status === 'completed' ? 'active' : ''; ?>">Completed</a>
                    <a href="?status=rejected" class="filter-tab <?php echo $status === 'rejected' ? 'active' : ''; ?>">Rejected</a>
                </div>
                
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <form action="" method="GET">
                        <?php if ($status !== 'all'): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search by name, blood type, or hospital" class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
            </div>
            
            <?php if (empty($requests)): ?>
                <div class="no-requests">
                    <i class="fas fa-heartbeat"></i>
                    <p>No blood bank requests found.</p>
                </div>
            <?php else: ?>
                <div class="blood-requests-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Requester</th>
                                <th>Blood Type</th>
                                <th>Units</th>
                                <th>Required By</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <div class="patient-info">
                                            <div class="patient-avatar">
                                                <?php echo substr($request['patient_name'], 0, 1); ?>
                                            </div>
                                            <div class="patient-details">
                                                <div class="patient-name"><?php echo htmlspecialchars($request['patient_name']); ?></div>
                                                <div class="patient-meta">
                                                    <span><i class="fas fa-hospital"></i> <?php echo htmlspecialchars($request['hospital']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-display">
                                            <span class="requester-name"><?php echo htmlspecialchars($request['requester_name']); ?></span>
                                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($request['email']); ?></span>
                                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['phone']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="blood-badge"><?php echo htmlspecialchars($request['blood_type']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['units']); ?> Units</td>
                                    <td><?php echo date('M d, Y', strtotime($request['required_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <a href="?action=approve&id=<?php echo $request['id']; ?>" class="action-btn approve-btn" onclick="return confirm('Are you sure you want to approve this request?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                                <a href="?action=reject&id=<?php echo $request['id']; ?>" class="action-btn reject-btn" onclick="return confirm('Are you sure you want to reject this request?');">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>
                                            <?php elseif ($request['status'] === 'approved'): ?>
                                                <a href="?action=complete&id=<?php echo $request['id']; ?>" class="action-btn complete-btn" onclick="return confirm('Are you sure you want to mark this request as completed?');">
                                                    <i class="fas fa-check-double"></i> Complete
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