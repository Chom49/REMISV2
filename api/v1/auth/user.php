<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Include database connection
require_once __DIR__ . '/../../../backend/db.php';

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get token from Authorization header
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($authHeader)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit;
}

// Extract Bearer token
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token format. Use: Bearer your_token_here']);
    exit;
}

$token = $matches[1];

// Verify token in database
$query = "SELECT id, name, email, api_token_expires_at FROM users WHERE api_token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();

// Check if token expired
$currentTime = date('Y-m-d H:i:s');
if ($user['api_token_expires_at'] < $currentTime) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token expired. Please login again']);
    $stmt->close();
    exit;
}

// Return user data
echo json_encode([
    'success' => true,
    'data' => [
        'user_id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ]
]);

$stmt->close();
$conn->close();
?>