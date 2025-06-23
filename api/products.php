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
    // Check if requesting a single product by ID
    if (isset($_GET['id'])) {
        $productId = $_GET['id'];
        $sql = "SELECT p.*, c.name as category_name, v.name as vendor_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN vendors v ON p.vendor_id = v.id 
                WHERE p.id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo json_encode([
                'success' => true,
                'products' => [$product]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        return;
    }
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = ($page - 1) * $limit;
    
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($category)) {
        $whereConditions[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "p.status = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(p.name LIKE ? OR p.product_code LIKE ? OR p.barcode LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT p.*, c.name as category_name, v.name as vendor_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vendors v ON p.vendor_id = v.id 
            $whereClause 
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'products' => $products,
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
    
    $required_fields = ['name', 'product_code', 'barcode', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Check if product code or barcode already exists
    $checkSql = "SELECT id FROM products WHERE product_code = ? OR barcode = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$input['product_code'], $input['barcode']]);
    
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Product code or barcode already exists']);
        return;
    }
    
    $sql = "INSERT INTO products (name, product_code, barcode, qr_code, price, cost_price, stock_quantity, min_stock_level, category_id, vendor_id, description, image_url, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $input['name'],
        $input['product_code'],
        $input['barcode'],
        $input['qr_code'] ?? null,
        $input['price'],
        $input['cost_price'] ?? 0,
        $input['stock_quantity'] ?? 0,
        $input['min_stock_level'] ?? 10,
        $input['category_id'] ?? null,
        $input['vendor_id'] ?? null,
        $input['description'] ?? null,
        $input['image_url'] ?? null,
        $input['status'] ?? 'active'
    ]);
    
    if ($result) {
        $productId = $db->lastInsertId();
        
        // Check for low stock alert
        if (($input['stock_quantity'] ?? 0) <= ($input['min_stock_level'] ?? 10)) {
            createLowStockAlert($db, $productId);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product']);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    $productId = $input['id'];
    
    // Check if product exists
    $checkSql = "SELECT * FROM products WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$productId]);
    $existingProduct = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingProduct) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['name', 'product_code', 'barcode', 'qr_code', 'price', 'cost_price', 'stock_quantity', 'min_stock_level', 'category_id', 'vendor_id', 'description', 'image_url', 'status'];
    
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
    
    $params[] = $productId;
    
    $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        // Check for stock level changes and create alerts if needed
        if (isset($input['stock_quantity'])) {
            $minLevel = $input['min_stock_level'] ?? $existingProduct['min_stock_level'];
            if ($input['stock_quantity'] <= $minLevel) {
                createLowStockAlert($db, $productId);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update product']);
    }
}

function handleDelete($db) {
    $productId = $_GET['id'] ?? null;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        return;
    }
    
    $permanent = isset($_GET['permanent']) && $_GET['permanent'] === 'true';
    
    if ($permanent) {
        // Permanent delete
        $sql = "DELETE FROM products WHERE id = ?";
    } else {
        // Archive (soft delete)
        $sql = "UPDATE products SET status = 'archived', archived_at = CURRENT_TIMESTAMP WHERE id = ?";
    }
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$productId]);
    
    if ($result) {
        $message = $permanent ? 'Product deleted permanently' : 'Product archived successfully';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete product']);
    }
}

function createLowStockAlert($db, $productId) {
    $sql = "INSERT INTO inventory_alerts (product_id, alert_type, message) 
            VALUES (?, 'low_stock', 'Product stock is running low')";
    $stmt = $db->prepare($sql);
    $stmt->execute([$productId]);
}
?>