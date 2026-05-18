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

$metrics       = ["rentReceived" => 0, "upcoming" => 0, "overdue" => 0, "properties" => 0];
$collection    = ["collected" => 0, "pending" => 0, "rate" => 0];
$maintenance   = [];
$propertiesList = [];
$chartData     = ["labels" => [], "income" => [], "expenses" => []];

/* ---------- Properties owned by this landlord ---------- */
$stmt = $conn->prepare("SELECT id, name, location FROM properties WHERE landlord_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $propertiesList[] = [
        'id'       => intval($row['id']),
        'name'     => $row['name'] ?? '',
        'location' => $row['location'] ?? ''
    ];
}
$metrics['properties'] = count($propertiesList);
$stmt->close();

/* ---------- Rent totals BY LANDLORD (join payments -> properties) ---------- */
$payStmt = $conn->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN pay.status='paid'    THEN pay.amount END), 0) AS rentReceived,
        COALESCE(SUM(CASE WHEN pay.status='pending' THEN pay.amount END), 0) AS upcoming,
        COALESCE(SUM(CASE WHEN pay.status='overdue' THEN pay.amount END), 0) AS overdue
    FROM payments pay
    JOIN properties p ON pay.property_id = p.id
    WHERE p.landlord_id = ?
");
$payStmt->bind_param("i", $userId);
$payStmt->execute();
$payResult = $payStmt->get_result();
if ($payRow = $payResult->fetch_assoc()) {
    $metrics['rentReceived'] = floatval($payRow['rentReceived']);
    $metrics['upcoming']     = floatval($payRow['upcoming']);
    $metrics['overdue']      = floatval($payRow['overdue']);
}
$payStmt->close();

/* ---------- Rent collection units BY LANDLORD ---------- */
$collectStmt = $conn->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN pay.status='paid'    THEN 1 ELSE 0 END), 0) AS collected,
        COALESCE(SUM(CASE WHEN pay.status='pending' THEN 1 ELSE 0 END), 0) AS pending
    FROM payments pay
    JOIN properties p ON pay.property_id = p.id
    WHERE p.landlord_id = ?
");
$collectStmt->bind_param("i", $userId);
$collectStmt->execute();
$collectResult = $collectStmt->get_result();
if ($collectRow = $collectResult->fetch_assoc()) {
    $collection['collected'] = intval($collectRow['collected']);
    $collection['pending']   = intval($collectRow['pending']);
}
$collectStmt->close();

$totalUnits = $collection['collected'] + $collection['pending'];
$collection['rate'] = $totalUnits > 0 ? round(($collection['collected'] / $totalUnits) * 100, 2) : 0;

/* ---------- Income vs Expenses (last 6 months) BY LANDLORD ---------- */
$monthStmt = $conn->prepare("
    SELECT COALESCE(SUM(pay.amount), 0) AS total
    FROM payments pay
    JOIN properties p ON pay.property_id = p.id
    WHERE p.landlord_id = ?
      AND pay.status = 'paid'
      AND DATE_FORMAT(pay.`date`, '%Y-%m') = ?
");

for ($i = 5; $i >= 0; $i--) {
    $monthKey   = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M Y', strtotime("-$i months"));
    $chartData['labels'][] = $monthLabel;

    $monthStmt->bind_param("is", $userId, $monthKey);
    $monthStmt->execute();
    $monthResult = $monthStmt->get_result();
    $monthRow = $monthResult->fetch_assoc();
    $incomeValue = floatval($monthRow['total'] ?? 0);

    $chartData['income'][]   = $incomeValue;
    // Real expenses table doesn't exist yet — placeholder 35% of income
    $chartData['expenses'][] = round($incomeValue * 0.35, 2);
}
$monthStmt->close();

/* ---------- Maintenance requests (already filtered correctly) ---------- */
$stmt = $conn->prepare("
    SELECT m.id, m.issue, m.priority, m.status, p.name AS property_name
    FROM maintenance m
    JOIN properties p ON m.property_id = p.id
    WHERE p.landlord_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $maintenance[] = [
        'id'       => intval($row['id']),
        'property' => $row['property_name'],
        'issue'    => $row['issue'] ?? '',
        'priority' => $row['priority'] ?? 'Low',
        'status'   => $row['status'] ?? 'Pending'
    ];
}
$stmt->close();

echo json_encode([
    'metrics'        => $metrics,
    'collection'     => $collection,
    'charts'         => $chartData,
    'propertiesList' => $propertiesList,
    'maintenance'    => $maintenance
]);
?>