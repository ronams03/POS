<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$period = $_GET['period'] ?? 'all'; // all, week, month, year

try {
    $whereClause = '';
    $params = [];
    
    switch ($period) {
        case 'week':
            $whereClause = 'AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)';
            break;
        case 'month':
            $whereClause = 'AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
            break;
        case 'year':
            $whereClause = 'AND t.transaction_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
            break;
    }
    
    $sql = "SELECT 
                p.id,
                p.name,
                p.product_code,
                p.price,
                p.image_url,
                SUM(ti.quantity) as total_sold,
                SUM(ti.total_price) as total_revenue,
                COUNT(DISTINCT t.id) as transaction_count,
                AVG(ti.unit_price) as avg_price
            FROM products p
            JOIN transaction_items ti ON p.id = ti.product_id
            JOIN transactions t ON ti.transaction_id = t.id
            WHERE t.payment_status = 'completed' $whereClause
            GROUP BY p.id, p.name, p.product_code, p.price, p.image_url
            ORDER BY total_sold DESC
            LIMIT ?";
    
    $params[] = $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric values to proper types
    foreach ($products as &$product) {
        $product['total_sold'] = (int)$product['total_sold'];
        $product['total_revenue'] = (float)$product['total_revenue'];
        $product['transaction_count'] = (int)$product['transaction_count'];
        $product['avg_price'] = (float)$product['avg_price'];
        $product['price'] = (float)$product['price'];
    }
    
    // Get additional statistics
    $stats = [];
    
    if (!empty($products)) {
        // Total items sold across all top products
        $stats['total_items_sold'] = array_sum(array_column($products, 'total_sold'));
        
        // Total revenue from top products
        $stats['total_revenue'] = array_sum(array_column($products, 'total_revenue'));
        
        // Most popular product
        $stats['most_popular'] = $products[0];
        
        // Average items per transaction for top products
        $totalTransactions = array_sum(array_column($products, 'transaction_count'));
        $stats['avg_items_per_transaction'] = $totalTransactions > 0 ? 
            $stats['total_items_sold'] / $totalTransactions : 0;
    }
    
    // Get category breakdown for top products
    if (!empty($products)) {
        $productIds = array_column($products, 'id');
        $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
        
        $categorySql = "SELECT 
                            c.name as category_name,
                            COUNT(p.id) as product_count,
                            SUM(ti.quantity) as total_sold
                        FROM categories c
                        JOIN products p ON c.id = p.category_id
                        JOIN transaction_items ti ON p.id = ti.product_id
                        JOIN transactions t ON ti.transaction_id = t.id
                        WHERE p.id IN ($placeholders) 
                        AND t.payment_status = 'completed' $whereClause
                        GROUP BY c.id, c.name
                        ORDER BY total_sold DESC";
        
        $categoryStmt = $db->prepare($categorySql);
        $categoryStmt->execute($productIds);
        $categoryBreakdown = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats['category_breakdown'] = $categoryBreakdown;
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'stats' => $stats,
        'period' => $period,
        'limit' => $limit
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching top products: ' . $e->getMessage()
    ]);
}
?>