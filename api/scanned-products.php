<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$period = $_GET['period'] ?? 'all'; // all, week, month, year

try {
    $whereClause = '';
    $params = [];
    
    switch ($period) {
        case 'week':
            $whereClause = 'WHERE ps.scanned_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)';
            break;
        case 'month':
            $whereClause = 'WHERE ps.scanned_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)';
            break;
        case 'year':
            $whereClause = 'WHERE ps.scanned_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)';
            break;
    }
    
    $sql = "SELECT 
                p.id,
                p.name,
                p.product_code,
                p.barcode,
                p.price,
                p.stock_quantity,
                p.image_url,
                COUNT(ps.id) as scan_count,
                MAX(ps.scanned_at) as last_scanned,
                COUNT(DISTINCT DATE(ps.scanned_at)) as days_scanned,
                GROUP_CONCAT(DISTINCT ps.scan_type) as scan_types
            FROM products p
            JOIN product_scans ps ON p.id = ps.product_id
            $whereClause
            GROUP BY p.id, p.name, p.product_code, p.barcode, p.price, p.stock_quantity, p.image_url
            ORDER BY scan_count DESC
            LIMIT ?";
    
    $params[] = $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric values and process data
    foreach ($products as &$product) {
        $product['scan_count'] = (int)$product['scan_count'];
        $product['price'] = (float)$product['price'];
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['days_scanned'] = (int)$product['days_scanned'];
        $product['scan_types'] = explode(',', $product['scan_types']);
        
        // Calculate average scans per day
        $product['avg_scans_per_day'] = $product['days_scanned'] > 0 ? 
            $product['scan_count'] / $product['days_scanned'] : 0;
    }
    
    // Get scanning statistics
    $stats = [];
    
    if (!empty($products)) {
        // Total scans
        $stats['total_scans'] = array_sum(array_column($products, 'scan_count'));
        
        // Most scanned product
        $stats['most_scanned'] = $products[0];
        
        // Average scans per product
        $stats['avg_scans_per_product'] = $stats['total_scans'] / count($products);
        
        // Scan type breakdown
        $scanTypesSql = "SELECT 
                            scan_type,
                            COUNT(*) as count,
                            COUNT(DISTINCT product_id) as unique_products
                         FROM product_scans ps
                         JOIN products p ON ps.product_id = p.id";
        
        if ($whereClause) {
            $scanTypesSql .= " " . str_replace('WHERE', 'WHERE', $whereClause);
        }
        
        $scanTypesSql .= " GROUP BY scan_type ORDER BY count DESC";
        
        $scanTypesStmt = $db->prepare($scanTypesSql);
        $scanTypesStmt->execute();
        $scanTypes = $scanTypesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats['scan_type_breakdown'] = $scanTypes;
    }
    
    // Get hourly scan pattern (for current day or period)
    $hourlySql = "SELECT 
                    HOUR(scanned_at) as hour,
                    COUNT(*) as scan_count
                  FROM product_scans ps
                  JOIN products p ON ps.product_id = p.id";
    
    if ($period === 'all') {
        $hourlySql .= " WHERE DATE(ps.scanned_at) = CURDATE()";
    } else {
        $hourlySql .= " " . $whereClause;
    }
    
    $hourlySql .= " GROUP BY HOUR(scanned_at) ORDER BY hour";
    
    $hourlyStmt = $db->prepare($hourlySql);
    $hourlyStmt->execute();
    $hourlyPattern = $hourlyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fill in missing hours with 0 scans
    $hourlyData = array_fill(0, 24, 0);
    foreach ($hourlyPattern as $hour) {
        $hourlyData[(int)$hour['hour']] = (int)$hour['scan_count'];
    }
    
    $stats['hourly_pattern'] = $hourlyData;
    
    // Get recent scan activity
    $recentSql = "SELECT 
                    p.name,
                    p.product_code,
                    ps.scan_type,
                    ps.scanned_at
                  FROM product_scans ps
                  JOIN products p ON ps.product_id = p.id
                  ORDER BY ps.scanned_at DESC
                  LIMIT 10";
    
    $recentStmt = $db->prepare($recentSql);
    $recentStmt->execute();
    $recentScans = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['recent_scans'] = $recentScans;
    
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
        'message' => 'Error fetching scanned products: ' . $e->getMessage()
    ]);
}
?>