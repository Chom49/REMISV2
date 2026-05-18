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
    $tenantId = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? '';
    
    if ($tenantId) {
        $query = "SELECT t.*, 
                  (SELECT COUNT(*) FROM leases WHERE tenant_id = t.id AND status = 'active') as active_leases
                  FROM tenants t 
                  WHERE t.id = ? AND t.landlord_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $tenantId, $landlordId);
        $stmt->execute();
        $tenant = $stmt->get_result()->fetch_assoc();
        
        // Get leases for this tenant
        $leaseQuery = "SELECT l.*, p.property_name, u.unit_number 
                       FROM leases l
                       JOIN properties p ON l.property_id = p.id
                       JOIN units u ON l.unit_id = u.id
                       WHERE l.tenant_id = ? AND l.landlord_id = ?";
        $leaseStmt = $conn->prepare($leaseQuery);
        $leaseStmt->bind_param("ii", $tenantId, $landlordId);
        $leaseStmt->execute();
        $tenant['leases'] = $leaseStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $tenant]);
    } else {
        $query = "SELECT t.*, 
                  (SELECT COUNT(*) FROM leases WHERE tenant_id = t.id AND status = 'active') as active_leases
                  FROM tenants t 
                  WHERE t.landlord_id = ?";
        
        if (!empty($search)) {
            $query .= " AND (t.first_name LIKE ? OR t.last_name LIKE ? OR t.email LIKE ?)";
            $searchParam = "%$search%";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $landlordId, $searchParam, $searchParam, $searchParam);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $landlordId);
        }
        
        $stmt->execute();
        $tenants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get statistics
        $totalQuery = "SELECT COUNT(*) as total FROM tenants WHERE landlord_id = ?";
        $totalStmt = $conn->prepare($totalQuery);
        $totalStmt->bind_param("i", $landlordId);
        $totalStmt->execute();
        $total = $totalStmt->get_result()->fetch_assoc()['total'];
        
        $activeQuery = "SELECT COUNT(DISTINCT t.id) as active FROM tenants t
                        JOIN leases l ON t.id = l.tenant_id
                        WHERE t.landlord_id = ? AND l.status = 'active'";
        $activeStmt = $conn->prepare($activeQuery);
        $activeStmt->bind_param("i", $landlordId);
        $activeStmt->execute();
        $active = $activeStmt->get_result()->fetch_assoc()['active'];
        
        $expiringQuery = "SELECT COUNT(DISTINCT t.id) as expiring FROM tenants t
                          JOIN leases l ON t.id = l.tenant_id
                          WHERE t.landlord_id = ? AND l.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $expiringStmt = $conn->prepare($expiringQuery);
        $expiringStmt->bind_param("i", $landlordId);
        $expiringStmt->execute();
        $expiring = $expiringStmt->get_result()->fetch_assoc()['expiring'];
        
        echo json_encode([
            'success' => true,
            'data' => $tenants,
            'statistics' => [
                'total' => $total,
                'active' => $active,
                'expiring' => $expiring
            ]
        ]);
    }
}

else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "INSERT INTO tenants (landlord_id, tin_number, first_name, last_name, email, 
              phone, date_of_birth, gender, nationality, notes) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssssss", 
        $landlordId,
        $data['tin_number'],
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['date_of_birth'],
        $data['gender'],
        $data['nationality'],
        $data['notes']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Tenant added', 'tenant_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add tenant']);
    }
}

else if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "UPDATE tenants SET first_name=?, last_name=?, email=?, phone=?, 
              date_of_birth=?, gender=?, nationality=?, notes=?
              WHERE id=? AND landlord_id=?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssii", 
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['date_of_birth'],
        $data['gender'],
        $data['nationality'],
        $data['notes'],
        $data['id'],
        $landlordId
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Tenant updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update tenant']);
    }
}

$conn->close();
?>