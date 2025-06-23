<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handlePost($db);
            break;
        case 'PUT':
            handlePut($db);
            break;
        case 'DELETE':
            handleDelete($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGet($db) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(v.name LIKE ? OR v.contact_person LIKE ? OR v.email LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "v.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT 
                v.*,
                COUNT(p.id) as total_products,
                COUNT(CASE WHEN p.status = 'active' THEN 1 END) as active_products
            FROM vendors v
            LEFT JOIN products p ON v.id = p.vendor_id
            $whereClause
            GROUP BY v.id
            ORDER BY v.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM vendors v $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'vendors' => $vendors,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalCount / $limit),
            'total_items' => $totalCount,
            'items_per_page' => $limit
        ]
    ]);
}

function handlePost($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        return;
    }
    
    if (!isset($input['name']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vendor name is required']);
        return;
    }
    
    // Check if vendor name already exists
    $checkSql = "SELECT id FROM vendors WHERE name = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$input['name']]);
    
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Vendor name already exists']);
        return;
    }
    
    $sql = "INSERT INTO vendors (name, contact_person, email, phone, address, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $input['name'],
        $input['contact_person'] ?? null,
        $input['email'] ?? null,
        $input['phone'] ?? null,
        $input['address'] ?? null,
        $input['status'] ?? 'active'
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Vendor created successfully',
            'vendor_id' => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create vendor']);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vendor ID is required']);
        return;
    }
    
    $vendorId = $input['id'];
    
    // Check if vendor exists
    $checkSql = "SELECT id FROM vendors WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$vendorId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Vendor not found']);
        return;
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['name', 'contact_person', 'email', 'phone', 'address', 'status'];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        return;
    }
    
    $params[] = $vendorId;
    
    $sql = "UPDATE vendors SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Vendor updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update vendor']);
    }
}

function handleDelete($db) {
    $vendorId = $_GET['id'] ?? null;
    
    if (!$vendorId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vendor ID is required']);
        return;
    }
    
    // Check if vendor has products
    $checkSql = "SELECT COUNT(*) as count FROM products WHERE vendor_id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$vendorId]);
    $productCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productCount > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete vendor with existing products. Please reassign or delete products first.'
        ]);
        return;
    }
    
    $sql = "DELETE FROM vendors WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$vendorId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Vendor deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete vendor']);
    }
}
?>