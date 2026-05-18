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

$maintenance = [];

// Get maintenance requests for landlord's properties
$stmt = $conn->prepare("
    SELECT
        m.id,
        m.issue,
        m.priority,
        m.status,
        m.created_at,
        m.description,
        p.name as property_name,
        p.location as property_location,
        COALESCE(t.name, '') as tenant_name
    FROM maintenance m
    JOIN properties p ON m.property_id = p.id
    LEFT JOIN leases l ON p.id = l.property_id
        AND l.status = 'active'
        AND l.start_date <= CURDATE()
        AND l.end_date >= CURDATE()
    LEFT JOIN tenants t ON l.tenant_id = t.id
    WHERE p.landlord_id = ?
    ORDER BY
        CASE m.priority
            WHEN 'High' THEN 1
            WHEN 'Medium' THEN 2
            WHEN 'Low' THEN 3
            ELSE 4
        END,
        m.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $maintenance[] = [
        'id' => intval($row['id']),
        'property_name' => $row['property_name'] ?? '',
        'property_location' => $row['property_location'] ?? '',
        'tenant_name' => $row['tenant_name'] ?? '',
        'issue' => $row['issue'] ?? '',
        'description' => $row['description'] ?? '',
        'priority' => $row['priority'] ?? 'Low',
        'status' => $row['status'] ?? 'Pending',
        'created_at' => $row['created_at'] ?? ''
    ];
}
$stmt->close();

echo json_encode([
    'maintenance' => $maintenance
]);
?>