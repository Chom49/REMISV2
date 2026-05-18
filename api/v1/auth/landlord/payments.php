<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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
    $type = $_GET['type'] ?? 'all';
    
    $query = "SELECT p.*, l.monthly_rent, CONCAT(t.first_name, ' ', t.last_name) as tenant_name,
              pr.property_name, u.unit_number
              FROM payments p
              JOIN leases l ON p.lease_id = l.id
              JOIN tenants t ON l.tenant_id = t.id
              JOIN properties pr ON l.property_id = pr.id
              JOIN units u ON l.unit_id = u.id
              WHERE l.landlord_id = ?";
    
    if ($type === 'pending') {
        $query .= " AND p.status = 'pending'";
    } else if ($type === 'overdue') {
        $query .= " AND p.status = 'overdue'";
    }
    
    $query .= " ORDER BY p.due_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get summary
    $summaryQuery = "SELECT 
                     SUM(CASE WHEN p.status = 'paid' THEN p.amount ELSE 0 END) as total_paid,
                     SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) as total_pending,
                     SUM(CASE WHEN p.status = 'overdue' THEN p.amount ELSE 0 END) as total_overdue
                     FROM payments p
                     JOIN leases l ON p.lease_id = l.id
                     WHERE l.landlord_id = ?";
    $summaryStmt = $conn->prepare($summaryQuery);
    $summaryStmt->bind_param("i", $landlordId);
    $summaryStmt->execute();
    $summary = $summaryStmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $payments,
        'summary' => $summary
    ]);
}

else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "INSERT INTO payments (lease_id, amount, payment_date, due_date, status, payment_method, transaction_id) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idsssss", 
        $data['lease_id'],
        $data['amount'],
        $data['payment_date'],
        $data['due_date'],
        $data['status'],
        $data['payment_method'],
        $data['transaction_id']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment recorded']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record payment']);
    }
}

$conn->close();
?>