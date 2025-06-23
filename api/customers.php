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
    $customerType = isset($_GET['type']) ? $_GET['type'] : '';
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (!empty($customerType)) {
        $whereConditions[] = "c.customer_type = ?";
        $params[] = $customerType;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT 
                c.*,
                COUNT(t.id) as total_transactions,
                COALESCE(SUM(t.total_amount), 0) as total_spent,
                MAX(t.transaction_date) as last_purchase_date
            FROM customers c
            LEFT JOIN transactions t ON c.id = t.customer_id AND t.payment_status = 'completed'
            $whereClause
            GROUP BY c.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM customers c $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'customers' => $customers,
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
        echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        return;
    }
    
    // Check if email already exists (if provided)
    if (!empty($input['email'])) {
        $checkSql = "SELECT id FROM customers WHERE email = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$input['email']]);
        
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }
    }
    
    $sql = "INSERT INTO customers (name, email, phone, address, loyalty_points, customer_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $input['name'],
        $input['email'] ?? null,
        $input['phone'] ?? null,
        $input['address'] ?? null,
        $input['loyalty_points'] ?? 0,
        $input['customer_type'] ?? 'regular'
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer_id' => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create customer']);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        return;
    }
    
    $customerId = $input['id'];
    
    // Check if customer exists
    $checkSql = "SELECT id FROM customers WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$customerId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        return;
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['name', 'email', 'phone', 'address', 'loyalty_points', 'customer_type'];
    
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
    
    $params[] = $customerId;
    
    $sql = "UPDATE customers SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update customer']);
    }
}

function handleDelete($db) {
    $customerId = $_GET['id'] ?? null;
    
    if (!$customerId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        return;
    }
    
    // Check if customer has transactions
    $checkSql = "SELECT COUNT(*) as count FROM transactions WHERE customer_id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$customerId]);
    $transactionCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($transactionCount > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete customer with existing transactions. Consider archiving instead.'
        ]);
        return;
    }
    
    $sql = "DELETE FROM customers WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$customerId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete customer']);
    }
}
?>