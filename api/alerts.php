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
    
    $alertType = isset($_GET['type']) ? $_GET['type'] : '';
    $isRead = isset($_GET['is_read']) ? $_GET['is_read'] : '';
    $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($alertType)) {
        $whereConditions[] = "ia.alert_type = ?";
        $params[] = $alertType;
    }
    
    if ($isRead !== '') {
        $whereConditions[] = "ia.is_read = ?";
        $params[] = $isRead === 'true' ? 1 : 0;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(ia.created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT 
                ia.*,
                p.name as product_name,
                p.product_code,
                p.barcode,
                p.stock_quantity,
                p.min_stock_level,
                p.price
            FROM inventory_alerts ia
            JOIN products p ON ia.product_id = p.id
            $whereClause
            ORDER BY ia.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM inventory_alerts ia JOIN products p ON ia.product_id = p.id $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get alert summary
    $summarySql = "SELECT 
                    alert_type,
                    COUNT(*) as count,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count
                   FROM inventory_alerts ia
                   JOIN products p ON ia.product_id = p.id
                   WHERE ia.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   GROUP BY alert_type";
    
    $summaryStmt = $db->prepare($summarySql);
    $summaryStmt->execute();
    $summary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'alerts' => $alerts,
        'summary' => $summary,
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
    
    $required_fields = ['product_id', 'alert_type'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Check if product exists
    $checkSql = "SELECT id FROM products WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$input['product_id']]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    // Check if similar alert already exists (within last 24 hours)
    $duplicateCheckSql = "SELECT id FROM inventory_alerts 
                          WHERE product_id = ? AND alert_type = ? 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $duplicateStmt = $db->prepare($duplicateCheckSql);
    $duplicateStmt->execute([$input['product_id'], $input['alert_type']]);
    
    if ($duplicateStmt->fetch()) {
        echo json_encode([
            'success' => true,
            'message' => 'Similar alert already exists within 24 hours',
            'duplicate' => true
        ]);
        return;
    }
    
    $sql = "INSERT INTO inventory_alerts (product_id, alert_type, message) VALUES (?, ?, ?)";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $input['product_id'],
        $input['alert_type'],
        $input['message'] ?? generateAlertMessage($input['alert_type'])
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Alert created successfully',
            'alert_id' => $db->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create alert']);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alert ID is required']);
        return;
    }
    
    $alertId = $input['id'];
    
    // Check if alert exists
    $checkSql = "SELECT id FROM inventory_alerts WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$alertId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Alert not found']);
        return;
    }
    
    // Handle bulk mark as read
    if (isset($input['mark_as_read']) && $input['mark_as_read'] === true) {
        $sql = "UPDATE inventory_alerts SET is_read = 1 WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$alertId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Alert marked as read']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update alert']);
        }
        return;
    }
    
    // Handle other updates
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['message', 'is_read'];
    
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
    
    $params[] = $alertId;
    
    $sql = "UPDATE inventory_alerts SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Alert updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update alert']);
    }
}

function handleDelete($db) {
    $alertId = $_GET['id'] ?? null;
    
    if (!$alertId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Alert ID is required']);
        return;
    }
    
    // Handle bulk delete
    if (isset($_GET['bulk']) && $_GET['bulk'] === 'true') {
        $type = $_GET['type'] ?? '';
        $isRead = $_GET['is_read'] ?? '';
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($type)) {
            $whereConditions[] = "alert_type = ?";
            $params[] = $type;
        }
        
        if ($isRead !== '') {
            $whereConditions[] = "is_read = ?";
            $params[] = $isRead === 'true' ? 1 : 0;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "DELETE FROM inventory_alerts $whereClause";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Alerts deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete alerts']);
        }
        return;
    }
    
    // Single alert delete
    $sql = "DELETE FROM inventory_alerts WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$alertId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Alert deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete alert']);
    }
}

function generateAlertMessage($alertType) {
    switch ($alertType) {
        case 'low_stock':
            return 'Product stock is running low and needs to be restocked';
        case 'out_of_stock':
            return 'Product is out of stock and unavailable for sale';
        case 'overstock':
            return 'Product has excessive stock levels';
        default:
            return 'Inventory alert for product';
    }
}

// Auto-generate alerts for low stock products
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['auto_generate']) && $_GET['auto_generate'] === 'true') {
    try {
        // Find products with low stock that don't have recent alerts
        $sql = "SELECT p.id, p.name, p.stock_quantity, p.min_stock_level
                FROM products p
                LEFT JOIN inventory_alerts ia ON p.id = ia.product_id 
                    AND ia.alert_type = 'low_stock' 
                    AND ia.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                WHERE p.stock_quantity <= p.min_stock_level 
                    AND p.stock_quantity > 0 
                    AND p.status = 'active'
                    AND ia.id IS NULL";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $alertsCreated = 0;
        foreach ($lowStockProducts as $product) {
            $insertSql = "INSERT INTO inventory_alerts (product_id, alert_type, message) 
                          VALUES (?, 'low_stock', ?)";
            $insertStmt = $db->prepare($insertSql);
            $message = "Product '{$product['name']}' is low on stock ({$product['stock_quantity']} remaining, minimum: {$product['min_stock_level']})";
            
            if ($insertStmt->execute([$product['id'], $message])) {
                $alertsCreated++;
            }
        }
        
        // Find out of stock products
        $outOfStockSql = "SELECT p.id, p.name
                          FROM products p
                          LEFT JOIN inventory_alerts ia ON p.id = ia.product_id 
                              AND ia.alert_type = 'out_of_stock' 
                              AND ia.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                          WHERE p.stock_quantity = 0 
                              AND p.status = 'active'
                              AND ia.id IS NULL";
        
        $outOfStockStmt = $db->prepare($outOfStockSql);
        $outOfStockStmt->execute();
        $outOfStockProducts = $outOfStockStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($outOfStockProducts as $product) {
            $insertSql = "INSERT INTO inventory_alerts (product_id, alert_type, message) 
                          VALUES (?, 'out_of_stock', ?)";
            $insertStmt = $db->prepare($insertSql);
            $message = "Product '{$product['name']}' is out of stock";
            
            if ($insertStmt->execute([$product['id'], $message])) {
                $alertsCreated++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Auto-generated $alertsCreated new alerts",
            'alerts_created' => $alertsCreated
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error auto-generating alerts: ' . $e->getMessage()]);
    }
}
?>