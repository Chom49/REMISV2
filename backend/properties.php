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

$properties = [];
$stats = [
    "total" => 0,
    "occupied" => 0,
    "vacant" => 0
];

// Get properties with occupancy status
$stmt = $conn->prepare("
    SELECT
        p.id,
        p.name,
        p.location,
        CASE
            WHEN EXISTS (
                SELECT 1 FROM leases l
                WHERE l.property_id = p.id
                AND l.status = 'active'
                AND l.start_date <= CURDATE()
                AND l.end_date >= CURDATE()
            ) THEN 'occupied'
            ELSE 'vacant'
        END as occupancy_status,
        COALESCE(l.rent_amount, 0) as rent_amount,
        COALESCE(l.payment_day, 0) as payment_day,
        COALESCE(t.name, '') as tenant_name,
        COALESCE(t.email, '') as tenant_email,
        COALESCE(t.phone, '') as tenant_phone
    FROM properties p
    LEFT JOIN leases l ON p.id = l.property_id
        AND l.status = 'active'
        AND l.start_date <= CURDATE()
        AND l.end_date >= CURDATE()
    LEFT JOIN tenants t ON l.tenant_id = t.id
    WHERE p.landlord_id = ?
    ORDER BY p.name ASC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $properties[] = [
        'id' => intval($row['id']),
        'name' => $row['name'] ?? '',
        'location' => $row['location'] ?? '',
        'occupancy_status' => $row['occupancy_status'],
        'rent_amount' => floatval($row['rent_amount']),
        'payment_day' => intval($row['payment_day']),
        'tenant_name' => $row['tenant_name'],
        'tenant_email' => $row['tenant_email'],
        'tenant_phone' => $row['tenant_phone']
    ];

    $stats['total']++;
    if ($row['occupancy_status'] === 'occupied') {
        $stats['occupied']++;
    } else {
        $stats['vacant']++;
    }
}
$stmt->close();

echo json_encode([
    'properties' => $properties,
    'stats' => $stats
]);
?>