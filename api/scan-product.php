<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$barcode = $_GET['barcode'] ?? '';

if (empty($barcode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Barcode is required']);
    exit;
}

try {
    // Search for product by barcode or QR code
    $sql = "SELECT p.*, c.name as category_name, v.name as vendor_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vendors v ON p.vendor_id = v.id 
            WHERE (p.barcode = ? OR p.qr_code = ?) AND p.status = 'active'";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$barcode, $barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Record the scan
        $scanSql = "INSERT INTO product_scans (product_id, scan_type) VALUES (?, 'barcode')";
        $scanStmt = $db->prepare($scanSql);
        $scanStmt->execute([$product['id']]);
        
        echo json_encode([
            'success' => true,
            'product' => $product,
            'message' => 'Product found successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found or inactive',
            'barcode' => $barcode
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>