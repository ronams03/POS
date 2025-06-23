<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $stats = [];
    
    // Total products
    $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Today's sales
    $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
            FROM transactions 
            WHERE DATE(transaction_date) = CURDATE() AND payment_status = 'completed'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['today_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Low stock count
    $sql = "SELECT COUNT(*) as total 
            FROM products 
            WHERE stock_quantity <= min_stock_level AND stock_quantity > 0 AND status = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['low_stock_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Out of stock count
    $sql = "SELECT COUNT(*) as total 
            FROM products 
            WHERE stock_quantity = 0 AND status = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['out_of_stock_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total customers
    $sql = "SELECT COUNT(*) as total FROM customers";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // This week's sales
    $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
            FROM transactions 
            WHERE YEARWEEK(transaction_date) = YEARWEEK(CURDATE()) AND payment_status = 'completed'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['week_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // This month's sales
    $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
            FROM transactions 
            WHERE YEAR(transaction_date) = YEAR(CURDATE()) 
            AND MONTH(transaction_date) = MONTH(CURDATE()) 
            AND payment_status = 'completed'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['month_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total transactions today
    $sql = "SELECT COUNT(*) as total 
            FROM transactions 
            WHERE DATE(transaction_date) = CURDATE()";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['today_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Average transaction value
    $sql = "SELECT COALESCE(AVG(total_amount), 0) as average 
            FROM transactions 
            WHERE payment_status = 'completed'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['avg_transaction_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['average'];
    
    // Most sold product today
    $sql = "SELECT p.name, SUM(ti.quantity) as total_sold
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            WHERE DATE(t.transaction_date) = CURDATE() AND t.payment_status = 'completed'
            GROUP BY p.id, p.name
            ORDER BY total_sold DESC
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $topProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['top_product_today'] = $topProduct ? $topProduct['name'] : 'No sales today';
    
    // Recent alerts count
    $sql = "SELECT COUNT(*) as total 
            FROM inventory_alerts 
            WHERE is_read = FALSE AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats['recent_alerts'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching dashboard stats: ' . $e->getMessage()
    ]);
}
?>