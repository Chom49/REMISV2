<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
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
    $status = $_GET['status'] ?? 'all';
    
    $query = "SELECT m.*, p.property_name, u.unit_number,
              CONCAT(t.first_name, ' ', t.last_name) as tenant_name
              FROM maintenance_requests m
              JOIN properties p ON m.property_id = p.id
              LEFT JOIN units u ON m.unit_id = u.id
              LEFT JOIN tenants t ON m.tenant_id = t.id
              WHERE m.landlord_id = ?";
    
    if ($status !== 'all') {
        $query .= " AND m.status = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $landlordId, $status);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $landlordId);
    }
    
    $stmt->execute();
    $requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $requests]);
}

else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "INSERT INTO maintenance_requests (property_id, unit_id, tenant_id, landlord_id, 
              title, description, due_date, priority, viewable_by) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiissis", 
        $data['property_id'],
        $data['unit_id'],
        $data['tenant_id'],
        $landlordId,
        $data['title'],
        $data['description'],
        $data['due_date'],
        $data['priority'],
        $data['viewable_by']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Maintenance request created']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create request']);
    }
}

else if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "UPDATE maintenance_requests SET status = ? WHERE id = ? AND landlord_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $data['status'], $data['id'], $landlordId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Request updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update request']);
    }
}

$conn->close();
?>