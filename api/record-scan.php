<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    
    $productId = $input['product_id'];
    $scanType = $input['scan_type'] ?? 'barcode';
    
    // Validate scan type
    $validScanTypes = ['barcode', 'qr_code', 'manual'];
    if (!in_array($scanType, $validScanTypes)) {
        $scanType = 'barcode';
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }
    
    // Check if product exists
    $checkSql = "SELECT id FROM products WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$productId]);
    
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Record the scan
    $sql = "INSERT INTO product_scans (product_id, scan_type, scanned_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$productId, $scanType]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Scan recorded successfully',
            'scan_id' => $conn->lastInsertId(),
            'product_id' => $productId,
            'scan_type' => $scanType,
            'scanned_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Failed to record scan');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error recording scan: ' . $e->getMessage()
    ]);
}
?>