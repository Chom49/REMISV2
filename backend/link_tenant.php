<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

include '../backend/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$tenant_id = $data['tenant_id'];
$property_name = $data['property'];

if (!$tenant_id || !$property_name) {
    http_response_code(400);
    exit;
}

// Get property_id
$sql = "SELECT id FROM properties WHERE name = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $property_name, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    http_response_code(404);
    exit;
}

$property_id = $property['id'];

// Check if lease already exists
$sql = "SELECT id FROM leases WHERE property_id = ? AND tenant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $property_id, $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Already linked
    echo json_encode(['success' => true]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert new lease with default values
$sql = "INSERT INTO leases (property_id, tenant_id, rent_amount, start_date, end_date) VALUES (?, ?, 0, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $property_id, $tenant_id);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => true]);
?>