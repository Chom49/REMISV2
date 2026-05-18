<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$conn = new mysqli('localhost', 'root', '', 'remis_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (empty($name) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}

$checkQuery = "SELECT id FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(40));

$insertQuery = "INSERT INTO users (name, email, phone, role, password, api_token) VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("sssss", $name, $email, $phone, $role, $hashedPassword, $token);

if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Account created',
        'data' => [
            'user_id' => $conn->insert_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'api_token' => $token
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Signup failed']);
}

$conn->close();
?>