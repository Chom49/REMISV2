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

$reportType = $_GET['type'] ?? 'summary';

// Base data for all reports
$summary = [
    'total_properties' => 0,
    'occupied_properties' => 0,
    'vacant_properties' => 0,
    'total_tenants' => 0,
    'total_rent_collected' => 0,
    'pending_payments' => 0,
    'overdue_payments' => 0,
    'maintenance_requests' => 0
];

// Get summary stats
$summaryStmt = $conn->prepare("
    SELECT
        COUNT(DISTINCT p.id) as total_properties,
        COUNT(DISTINCT CASE WHEN l.id IS NOT NULL THEN p.id END) as occupied_properties,
        COUNT(DISTINCT CASE WHEN l.id IS NULL THEN p.id END) as vacant_properties,
        COUNT(DISTINCT t.id) as total_tenants,
        COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount END), 0) as total_rent_collected,
        COALESCE(SUM(CASE WHEN pay.status = 'pending' THEN pay.amount END), 0) as pending_payments,
        COALESCE(SUM(CASE WHEN pay.status = 'overdue' THEN pay.amount END), 0) as overdue_payments,
        COUNT(DISTINCT m.id) as maintenance_requests
    FROM properties p
    LEFT JOIN leases l ON p.id = l.property_id
        AND l.status = 'active'
        AND l.start_date <= CURDATE()
        AND l.end_date >= CURDATE()
    LEFT JOIN tenants t ON l.tenant_id = t.id
    LEFT JOIN payments pay ON p.id = pay.property_id
    LEFT JOIN maintenance m ON p.id = m.property_id
    WHERE p.landlord_id = ?
");
$summaryStmt->bind_param("i", $userId);
$summaryStmt->execute();
$summaryResult = $summaryStmt->get_result();
if ($summaryRow = $summaryResult->fetch_assoc()) {
    $summary = array_merge($summary, $summaryRow);
}
$summaryStmt->close();

$data = ['summary' => $summary];

switch ($reportType) {
    case 'properties':
        // Property directory
        $propStmt = $conn->prepare("
            SELECT
                p.id, p.name, p.location,
                CASE WHEN l.id IS NOT NULL THEN 'Occupied' ELSE 'Vacant' END as status,
                COALESCE(t.name, '') as tenant_name,
                COALESCE(l.rent_amount, 0) as rent_amount
            FROM properties p
            LEFT JOIN leases l ON p.id = l.property_id
                AND l.status = 'active'
                AND l.start_date <= CURDATE()
                AND l.end_date >= CURDATE()
            LEFT JOIN tenants t ON l.tenant_id = t.id
            WHERE p.landlord_id = ?
            ORDER BY p.name
        ");
        $propStmt->bind_param("i", $userId);
        $propStmt->execute();
        $data['properties'] = $propStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $propStmt->close();
        break;

    case 'tenants':
        // Tenants report
        $tenantStmt = $conn->prepare("
            SELECT
                t.name, t.email, t.phone,
                p.name as property_name,
                l.rent_amount, l.start_date, l.end_date
            FROM tenants t
            JOIN leases l ON t.id = l.tenant_id
            JOIN properties p ON l.property_id = p.id
            WHERE p.landlord_id = ?
            ORDER BY t.name
        ");
        $tenantStmt->bind_param("i", $userId);
        $tenantStmt->execute();
        $data['tenants'] = $tenantStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $tenantStmt->close();
        break;

    case 'payments':
        // Rent payments report
        $payStmt = $conn->prepare("
            SELECT
                pay.amount, pay.status, pay.date,
                p.name as property_name,
                COALESCE(t.name, '') as tenant_name
            FROM payments pay
            JOIN properties p ON pay.property_id = p.id
            LEFT JOIN leases l ON p.id = l.property_id
                AND l.status = 'active'
            LEFT JOIN tenants t ON l.tenant_id = t.id
            WHERE p.landlord_id = ?
            ORDER BY pay.date DESC
        ");
        $payStmt->bind_param("i", $userId);
        $payStmt->execute();
        $data['payments'] = $payStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $payStmt->close();
        break;

    case 'maintenance':
        // Maintenance report
        $maintStmt = $conn->prepare("
            SELECT
                m.issue, m.priority, m.status, m.created_at,
                p.name as property_name,
                COALESCE(t.name, '') as tenant_name
            FROM maintenance m
            JOIN properties p ON m.property_id = p.id
            LEFT JOIN leases l ON p.id = l.property_id
                AND l.status = 'active'
            LEFT JOIN tenants t ON l.tenant_id = t.id
            WHERE p.landlord_id = ?
            ORDER BY m.created_at DESC
        ");
        $maintStmt->bind_param("i", $userId);
        $maintStmt->execute();
        $data['maintenance'] = $maintStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $maintStmt->close();
        break;
}

echo json_encode($data);
?>