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
    $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            GROUP BY c.id 
            ORDER BY c.name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
}

function handlePost($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name']) || empty($input['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        return;
    }
    
    // Check if category already exists
    $checkSql = "SELECT id FROM categories WHERE name = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$input['name']]);
    
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
        return;
    }
    
    $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $input['name'],
        $input['description'] ?? null
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'category_id' => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create category']);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    $categoryId = $input['id'];
    
    // Check if category exists
    $checkSql = "SELECT id FROM categories WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$categoryId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found']);
        return;
    }
    
    $updateFields = [];
    $params = [];
    
    if (isset($input['name'])) {
        $updateFields[] = "name = ?";
        $params[] = $input['name'];
    }
    
    if (isset($input['description'])) {
        $updateFields[] = "description = ?";
        $params[] = $input['description'];
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        return;
    }
    
    $params[] = $categoryId;
    
    $sql = "UPDATE categories SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update category']);
    }
}

function handleDelete($db) {
    $categoryId = $_GET['id'] ?? null;
    
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }
    
    // Check if category has products
    $checkSql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$categoryId]);
    $productCount = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($productCount > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete category with existing products. Please reassign or delete products first.'
        ]);
        return;
    }
    
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$categoryId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
    }
}
?>