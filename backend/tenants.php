<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['user_id']) || intval($_SESSION['user_id']) <= 0) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$userId = intval($_SESSION['user_id']);

$tenants = [];

// Get tenants through active leases on landlord's properties
$stmt = $conn->prepare("
    SELECT DISTINCT
        t.id,
        t.name,
        t.email,
        t.phone,
        t.created_at,
        p.name as property_name,
        l.rent_amount,
        l.start_date,
        l.end_date,
        l.status as lease_status
    FROM tenants t
    JOIN leases l ON t.id = l.tenant_id
    JOIN properties p ON l.property_id = p.id
    WHERE p.landlord_id = ?
    ORDER BY t.name ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $tenants[] = [
        'id' => intval($row['id']),
        'name' => $row['name'] ?? '',
        'email' => $row['email'] ?? '',
        'phone' => $row['phone'] ?? '',
        'property_name' => $row['property_name'] ?? '',
        'rent_amount' => floatval($row['rent_amount'] ?? 0),
        'lease_start' => $row['start_date'] ?? '',
        'lease_end' => $row['end_date'] ?? '',
        'lease_status' => $row['lease_status'] ?? 'inactive',
        'created_at' => $row['created_at'] ?? ''
    ];
}
$stmt->close();

echo json_encode([
    'tenants' => $tenants
]);
?>