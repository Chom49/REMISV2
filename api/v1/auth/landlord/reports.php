<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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
$reportType = $_GET['type'] ?? 'rent_payments';

$reports = [];

if ($reportType === 'rent_payments') {
    // Rent payments by month
    $query = "SELECT MONTH(p.payment_date) as month, YEAR(p.payment_date) as year, 
              SUM(p.amount) as total
              FROM payments p
              JOIN leases l ON p.lease_id = l.id
              WHERE l.landlord_id = ? AND p.status = 'paid'
              GROUP BY YEAR(p.payment_date), MONTH(p.payment_date)
              ORDER BY year DESC, month DESC
              LIMIT 12";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

else if ($reportType === 'overdue_rent') {
    $query = "SELECT l.*, p.property_name, u.unit_number, CONCAT(t.first_name, ' ', t.last_name) as tenant_name,
              DATEDIFF(CURDATE(), l.start_date) as days_overdue
              FROM leases l
              JOIN properties p ON l.property_id = p.id
              JOIN units u ON l.unit_id = u.id
              JOIN tenants t ON l.tenant_id = t.id
              WHERE l.landlord_id = ? AND l.status = 'active' 
              AND DAY(l.start_date) < DAY(CURDATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

else if ($reportType === 'lease_expiry') {
    $query = "SELECT l.*, p.property_name, u.unit_number, CONCAT(t.first_name, ' ', t.last_name) as tenant_name,
              DATEDIFF(l.end_date, CURDATE()) as days_until_expiry
              FROM leases l
              JOIN properties p ON l.property_id = p.id
              JOIN units u ON l.unit_id = u.id
              JOIN tenants t ON l.tenant_id = t.id
              WHERE l.landlord_id = ? AND l.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
              ORDER BY l.end_date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

else if ($reportType === 'occupancy') {
    $query = "SELECT p.id, p.property_name, 
              COUNT(u.id) as total_units,
              SUM(CASE WHEN u.is_occupied = 1 THEN 1 ELSE 0 END) as occupied_units
              FROM properties p
              LEFT JOIN units u ON p.id = u.property_id
              WHERE p.landlord_id = ?
              GROUP BY p.id";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $landlordId);
    $stmt->execute();
    $reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

echo json_encode(['success' => true, 'data' => $reports]);
$conn->close();
?>