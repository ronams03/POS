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
    
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $customerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
    
    $whereConditions = [];
    $params = [];
    
    if (!empty($status)) {
        $whereConditions[] = "t.payment_status = ?";
        $params[] = $status;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(t.transaction_date) >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(t.transaction_date) <= ?";
        $params[] = $dateTo;
    }
    
    if (!empty($customerId)) {
        $whereConditions[] = "t.customer_id = ?";
        $params[] = $customerId;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get transactions with customer info
    $sql = "SELECT 
                t.*,
                c.name as customer_name,
                c.email as customer_email,
                COUNT(ti.id) as item_count
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            $whereClause
            GROUP BY t.id
            ORDER BY t.transaction_date DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get transaction items for each transaction if requested
    if (isset($_GET['include_items']) && $_GET['include_items'] === 'true') {
        foreach ($transactions as &$transaction) {
            $itemsSql = "SELECT 
                            ti.*,
                            p.name as product_name,
                            p.product_code,
                            p.barcode
                         FROM transaction_items ti
                         JOIN products p ON ti.product_id = p.id
                         WHERE ti.transaction_id = ?";
            
            $itemsStmt = $db->prepare($itemsSql);
            $itemsStmt->execute([$transaction['id']]);
            $transaction['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM transactions t $whereClause";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'transactions' => $transactions,
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
    
    // Validate required fields
    $required_fields = ['total_amount', 'payment_method', 'items'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    if (empty($input['items']) || !is_array($input['items'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Transaction must have at least one item']);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Generate transaction number
        $transactionNumber = 'TXN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if transaction number exists
        $checkSql = "SELECT id FROM transactions WHERE transaction_number = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$transactionNumber]);
        
        while ($checkStmt->fetch()) {
            $transactionNumber = 'TXN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $checkStmt->execute([$transactionNumber]);
        }
        
        // Insert transaction
        $sql = "INSERT INTO transactions (
                    transaction_number, customer_id, total_amount, tax_amount, 
                    discount_amount, payment_method, payment_status, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $transactionNumber,
            $input['customer_id'] ?? null,
            $input['total_amount'],
            $input['tax_amount'] ?? 0,
            $input['discount_amount'] ?? 0,
            $input['payment_method'],
            $input['payment_status'] ?? 'completed',
            $input['notes'] ?? null,
            $input['created_by'] ?? 1 // Default user ID
        ]);
        
        if (!$result) {
            throw new Exception('Failed to create transaction');
        }
        
        $transactionId = $db->lastInsertId();
        
        // Insert transaction items and update stock
        foreach ($input['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['unit_price'])) {
                throw new Exception('Invalid item data');
            }
            
            $totalPrice = $item['quantity'] * $item['unit_price'];
            
            // Insert transaction item
            $itemSql = "INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, total_price) 
                        VALUES (?, ?, ?, ?, ?)";
            $itemStmt = $db->prepare($itemSql);
            $itemResult = $itemStmt->execute([
                $transactionId,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price'],
                $totalPrice
            ]);
            
            if (!$itemResult) {
                throw new Exception('Failed to add transaction item');
            }
            
            // Update product stock
            $stockSql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stockStmt = $db->prepare($stockSql);
            $stockResult = $stockStmt->execute([$item['quantity'], $item['product_id']]);
            
            if (!$stockResult) {
                throw new Exception('Failed to update product stock');
            }
            
            // Check for low stock alert
            $checkStockSql = "SELECT stock_quantity, min_stock_level FROM products WHERE id = ?";
            $checkStockStmt = $db->prepare($checkStockSql);
            $checkStockStmt->execute([$item['product_id']]);
            $stockInfo = $checkStockStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stockInfo && $stockInfo['stock_quantity'] <= $stockInfo['min_stock_level']) {
                createLowStockAlert($db, $item['product_id']);
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Transaction created successfully',
            'transaction_id' => $transactionId,
            'transaction_number' => $transactionNumber
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handlePut($db) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        return;
    }
    
    $transactionId = $input['id'];
    
    // Check if transaction exists
    $checkSql = "SELECT * FROM transactions WHERE id = ?";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([$transactionId]);
    $existingTransaction = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingTransaction) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        return;
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = ['customer_id', 'total_amount', 'tax_amount', 'discount_amount', 'payment_method', 'payment_status', 'notes'];
    
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
    
    $params[] = $transactionId;
    
    $sql = "UPDATE transactions SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Transaction updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update transaction']);
    }
}

function handleDelete($db) {
    $transactionId = $_GET['id'] ?? null;
    
    if (!$transactionId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        return;
    }
    
    $db->beginTransaction();
    
    try {
        // Get transaction items to restore stock
        $itemsSql = "SELECT product_id, quantity FROM transaction_items WHERE transaction_id = ?";
        $itemsStmt = $db->prepare($itemsSql);
        $itemsStmt->execute([$transactionId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore stock for each item
        foreach ($items as $item) {
            $stockSql = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
            $stockStmt = $db->prepare($stockSql);
            $stockStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Delete transaction items
        $deleteItemsSql = "DELETE FROM transaction_items WHERE transaction_id = ?";
        $deleteItemsStmt = $db->prepare($deleteItemsSql);
        $deleteItemsStmt->execute([$transactionId]);
        
        // Delete transaction
        $deleteSql = "DELETE FROM transactions WHERE id = ?";
        $deleteStmt = $db->prepare($deleteSql);
        $result = $deleteStmt->execute([$transactionId]);
        
        if ($result) {
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
        } else {
            throw new Exception('Failed to delete transaction');
        }
        
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function createLowStockAlert($db, $productId) {
    $sql = "INSERT INTO inventory_alerts (product_id, alert_type, message) 
            VALUES (?, 'low_stock', 'Product stock is running low after sale')";
    $stmt = $db->prepare($sql);
    $stmt->execute([$productId]);
}
?>