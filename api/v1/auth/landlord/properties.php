<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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

// GET - Fetch properties
if ($method === 'GET') {
    $propertyId = $_GET['id'] ?? null;
    
    if ($propertyId) {
        // Get single property
        $query = "SELECT p.*, COUNT(u.id) as unit_count 
                  FROM properties p
                  LEFT JOIN units u ON p.id = u.property_id
                  WHERE p.id = ? AND p.landlord_id = ?
                  GROUP BY p.id";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $propertyId, $landlordId);
        $stmt->execute();
        $property = $stmt->get_result()->fetch_assoc();
        
        // Get units for this property
        $unitQuery = "SELECT * FROM units WHERE property_id = ?";
        $unitStmt = $conn->prepare($unitQuery);
        $unitStmt->bind_param("i", $propertyId);
        $unitStmt->execute();
        $property['units'] = $unitStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $property]);
    } else {
        // Get all properties
        $query = "SELECT p.*, COUNT(u.id) as unit_count,
                  SUM(CASE WHEN u.is_occupied = 1 THEN 1 ELSE 0 END) as occupied_units
                  FROM properties p
                  LEFT JOIN units u ON p.id = u.property_id
                  WHERE p.landlord_id = ?
                  GROUP BY p.id
                  ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $landlordId);
        $stmt->execute();
        $properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $properties]);
    }
}

// POST - Create property
else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $query = "INSERT INTO properties (landlord_id, property_name, region, total_area, 
              property_type, floor_layout, total_units, unit_prefix, property_image) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issdssiis", 
        $landlordId,
        $data['property_name'],
        $data['region'],
        $data['total_area'],
        $data['property_type'],
        $data['floor_layout'],
        $data['total_units'],
        $data['unit_prefix'],
        $data['property_image']
    );
    
    if ($stmt->execute()) {
        $propertyId = $conn->insert_id;
        
        // Create units automatically
        if ($data['total_units'] > 0) {
            $prefix = $data['unit_prefix'] ?? 'Unit';
            for ($i = 1; $i <= $data['total_units']; $i++) {
                $unitNumber = "$prefix $i";
                $unitQuery = "INSERT INTO units (property_id, unit_number) VALUES (?, ?)";
                $unitStmt = $conn->prepare($unitQuery);
                $unitStmt->bind_param("is", $propertyId, $unitNumber);
                $unitStmt->execute();
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Property created', 'property_id' => $propertyId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create property']);
    }
}

// PUT - Update property
else if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $propertyId = $data['id'];
    
    $query = "UPDATE properties SET property_name=?, region=?, total_area=?, 
              property_type=?, floor_layout=?, total_units=?, unit_prefix=?, property_image=?
              WHERE id=? AND landlord_id=?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdssiisii", 
        $data['property_name'],
        $data['region'],
        $data['total_area'],
        $data['property_type'],
        $data['floor_layout'],
        $data['total_units'],
        $data['unit_prefix'],
        $data['property_image'],
        $propertyId,
        $landlordId
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Property updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update property']);
    }
}

// DELETE - Delete property
else if ($method === 'DELETE') {
    $propertyId = $_GET['id'] ?? null;
    
    $query = "DELETE FROM properties WHERE id=? AND landlord_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $propertyId, $landlordId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Property deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete property']);
    }
}

$conn->close();
?>