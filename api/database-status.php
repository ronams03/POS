<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        // Get database name
        $dbName = 'pos_system';
        
        // Get MySQL version
        $versionStmt = $conn->query("SELECT VERSION() as version");
        $version = $versionStmt->fetch(PDO::FETCH_ASSOC)['version'];
        
        // Get table count
        $tableStmt = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '$dbName'");
        $tableCount = $tableStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get database size
        $sizeStmt = $conn->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = '$dbName'
        ");
        $sizeResult = $sizeStmt->fetch(PDO::FETCH_ASSOC);
        $sizeMB = $sizeResult['size_mb'] ?: 0;
        
        // Get additional stats
        $statsStmt = $conn->query("
            SELECT 
                (SELECT COUNT(*) FROM products WHERE status = 'active') as active_products,
                (SELECT COUNT(*) FROM customers) as total_customers,
                (SELECT COUNT(*) FROM transactions WHERE payment_status = 'completed') as completed_transactions,
                (SELECT COUNT(*) FROM inventory_alerts WHERE is_read = 0) as unread_alerts
        ");
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'database' => $dbName,
            'version' => $version,
            'table_count' => (int)$tableCount,
            'size' => $sizeMB . ' MB',
            'size_bytes' => $sizeMB * 1024 * 1024,
            'stats' => [
                'active_products' => (int)$stats['active_products'],
                'total_customers' => (int)$stats['total_customers'],
                'completed_transactions' => (int)$stats['completed_transactions'],
                'unread_alerts' => (int)$stats['unread_alerts']
            ],
            'connection_info' => [
                'host' => 'localhost',
                'charset' => 'utf8',
                'status' => 'Connected'
            ]
        ]);
        
    } else {
        throw new Exception('Failed to connect to database');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>