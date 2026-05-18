<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once '../../backend/db.php';

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit;
}

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
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = "SELECT l.*, p.property_name, u.unit_number, 
              CONCAT(t.first_name, ' ', t.last_name) as tenant_name
              FROM leases l
              JOIN properties p ON l.property_id = p.id
              JOIN units u ON l.unit_id = u.id
              JOIN tenants t ON l.tenant_id = t.id
              WHERE l.landlord_id = ?
              ORDER BY l.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $leases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get statistics
    $activeQuery = "SELECT COUNT(*) as active FROM leases WHERE landlord_id = ? AND status = 'active'";
    $activeStmt = $conn->prepare($activeQuery);
    $activeStmt->bind_param("i", $landlordId);
    $activeStmt->execute();
    $active = $activeStmt->get_result()->fetch_assoc()['active'];
    
    $expiringQuery = "SELECT COUNT(*) as expiring FROM leases 
                      WHERE landlord_id = ? AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    $expiringStmt = $conn->prepare($expiringQuery);
    $expiringStmt->bind_param("i", $landlordId);
    $expiringStmt->execute();
    $expiring = $expiringStmt->get_result()->fetch_assoc()['expiring'];
    
    $overdueQuery = "SELECT COUNT(*) as overdue FROM leases WHERE landlord_id = ? AND end_date < CURDATE() AND status = 'active'";
    $overdueStmt = $conn->prepare($overdueQuery);
    $overdueStmt->bind_param("i", $landlordId);
    $overdueStmt->execute();
    $overdue = $overdueStmt->get_result()->fetch_assoc()['overdue'];
    
    echo json_encode([
        'success' => true,
        'data' => $leases,
        'statistics' => [
            'active' => $active,
            'expiring' => $expiring,
            'overdue' => $overdue
        ]
    ]);
}

else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "INSERT INTO leases (property_id, unit_id, tenant_id, landlord_id, 
              start_date, end_date, monthly_rent, deposit_amount, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiissdss", 
        $data['property_id'],
        $data['unit_id'],
        $data['tenant_id'],
        $landlordId,
        $data['start_date'],
        $data['end_date'],
        $data['monthly_rent'],
        $data['deposit_amount'],
        $data['status']
    );
    
    if ($stmt->execute()) {
        // Update unit to occupied
        $updateUnit = "UPDATE units SET is_occupied = 1 WHERE id = ?";
        $unitStmt = $conn->prepare($updateUnit);
        $unitStmt->bind_param("i", $data['unit_id']);
        $unitStmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Lease created', 'lease_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create lease']);
    }
}

$conn->close();
?>