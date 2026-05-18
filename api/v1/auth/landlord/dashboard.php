<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once '../../backend/db.php';

// Get token from header
$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit;
}

// Get landlord ID from token
$userQuery = "SELECT id FROM users WHERE api_token = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("s", $token);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

$landlordId = $userResult->fetch_assoc()['id'];

// Get statistics
$stats = [];

// Total Properties
$propQuery = "SELECT COUNT(*) as total FROM properties WHERE landlord_id = ?";
$propStmt = $conn->prepare($propQuery);
$propStmt->bind_param("i", $landlordId);
$propStmt->execute();
$stats['total_properties'] = $propStmt->get_result()->fetch_assoc()['total'];

// Total Tenants
$tenantQuery = "SELECT COUNT(*) as total FROM tenants WHERE landlord_id = ?";
$tenantStmt = $conn->prepare($tenantQuery);
$tenantStmt->bind_param("i", $landlordId);
$tenantStmt->execute();
$stats['total_tenants'] = $tenantStmt->get_result()->fetch_assoc()['total'];

// Active Leases
$leaseQuery = "SELECT COUNT(*) as total FROM leases WHERE landlord_id = ? AND status = 'active'";
$leaseStmt = $conn->prepare($leaseQuery);
$leaseStmt->bind_param("i", $landlordId);
$leaseStmt->execute();
$stats['active_leases'] = $leaseStmt->get_result()->fetch_assoc()['total'];

// Rent Received (current month)
$rentQuery = "SELECT SUM(amount) as total FROM payments p 
              JOIN leases l ON p.lease_id = l.id 
              WHERE l.landlord_id = ? AND MONTH(p.payment_date) = MONTH(CURDATE()) AND p.status = 'paid'";
$rentStmt = $conn->prepare($rentQuery);
$rentStmt->bind_param("i", $landlordId);
$rentStmt->execute();
$stats['rent_received'] = $rentStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Upcoming Payments (next 30 days)
$upcomingQuery = "SELECT SUM(monthly_rent) as total FROM leases 
                  WHERE landlord_id = ? AND status = 'active' 
                  AND DAY(start_date) BETWEEN DAY(CURDATE()) AND DAY(DATE_ADD(CURDATE(), INTERVAL 30 DAY))";
$upcomingStmt = $conn->prepare($upcomingQuery);
$upcomingStmt->bind_param("i", $landlordId);
$upcomingStmt->execute();
$stats['upcoming_payments'] = $upcomingStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Overdue Payments
$overdueQuery = "SELECT SUM(monthly_rent) as total FROM leases 
                 WHERE landlord_id = ? AND status = 'active' 
                 AND DAY(start_date) < DAY(CURDATE())";
$overdueStmt = $conn->prepare($overdueQuery);
$overdueStmt->bind_param("i", $landlordId);
$overdueStmt->execute();
$stats['overdue_payments'] = $overdueStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Maintenance Requests
$mainQuery = "SELECT COUNT(*) as total FROM maintenance_requests WHERE landlord_id = ? AND status = 'new'";
$mainStmt = $conn->prepare($mainQuery);
$mainStmt->bind_param("i", $landlordId);
$mainStmt->execute();
$stats['pending_maintenance'] = $mainStmt->get_result()->fetch_assoc()['total'];

// Recent Properties
$recentPropsQuery = "SELECT id, property_name, region, total_units, created_at 
                     FROM properties WHERE landlord_id = ? 
                     ORDER BY created_at DESC LIMIT 5";
$recentPropsStmt = $conn->prepare($recentPropsQuery);
$recentPropsStmt->bind_param("i", $landlordId);
$recentPropsStmt->execute();
$recentProperties = $recentPropsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recent Maintenance Requests
$recentMainQuery = "SELECT m.*, p.property_name 
                    FROM maintenance_requests m
                    JOIN properties p ON m.property_id = p.id
                    WHERE m.landlord_id = ? 
                    ORDER BY m.created_at DESC LIMIT 5";
$recentMainStmt = $conn->prepare($recentMainQuery);
$recentMainStmt->bind_param("i", $landlordId);
$recentMainStmt->execute();
$recentMaintenance = $recentMainStmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'data' => [
        'statistics' => $stats,
        'recent_properties' => $recentProperties,
        'recent_maintenance' => $recentMaintenance
    ]
]);
?>