<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
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
        case 'PUT':
            handleUpdate($db);
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
    try {
        // Get all system settings
        $sql = "SELECT setting_key, setting_value, description FROM system_settings";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to key-value pairs
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = [
                'value' => $setting['setting_value'],
                'description' => $setting['description']
            ];
        }
        
        // Get system information
        $systemInfo = getSystemInfo($db);
        
        echo json_encode([
            'success' => true,
            'settings' => $settingsArray,
            'system_info' => $systemInfo
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Error fetching settings: ' . $e->getMessage());
    }
}

function handleUpdate($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['settings'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Settings data is required']);
            return;
        }
        
        $settings = $input['settings'];
        $updatedCount = 0;
        
        $db->beginTransaction();
        
        foreach ($settings as $key => $value) {
            // Handle boolean values
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            
            // Update or insert setting
            $sql = "INSERT INTO system_settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()";
            
            $stmt = $db->prepare($sql);
            if ($stmt->execute([$key, $value])) {
                $updatedCount++;
            }
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Updated $updatedCount settings successfully",
            'updated_count' => $updatedCount
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw new Exception('Error updating settings: ' . $e->getMessage());
    }
}

function getSystemInfo($db) {
    try {
        $info = [];
        
        // Database size
        $sql = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = 'pos_system'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $info['database_size'] = ($result['size_mb'] ?: 0) . ' MB';
        
        // Total products
        $sql = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total transactions
        $sql = "SELECT COUNT(*) as count FROM transactions";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['total_transactions'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total customers
        $sql = "SELECT COUNT(*) as count FROM customers";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['total_customers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total sales today
        $sql = "SELECT COALESCE(SUM(total_amount), 0) as total 
                FROM transactions 
                WHERE DATE(transaction_date) = CURDATE() AND payment_status = 'completed'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['today_sales'] = '$' . number_format($stmt->fetch(PDO::FETCH_ASSOC)['total'], 2);
        
        // Low stock items
        $sql = "SELECT COUNT(*) as count 
                FROM products 
                WHERE stock_quantity <= min_stock_level AND status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['low_stock_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // PHP version
        $info['php_version'] = phpversion();
        
        // MySQL version
        $sql = "SELECT VERSION() as version";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $info['mysql_version'] = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
        
        // Server time
        $info['server_time'] = date('Y-m-d H:i:s');
        
        return $info;
        
    } catch (Exception $e) {
        return ['error' => 'Unable to fetch system information'];
    }
}
?>