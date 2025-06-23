<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }
    
    $file = $_FILES['sql_file'];
    
    // Validate file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'sql') {
        throw new Exception('Invalid file type. Only .sql files are allowed');
    }
    
    // Validate file size (50MB limit)
    $maxSize = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 50MB limit');
    }
    
    // Read file content
    $sqlContent = file_get_contents($file['tmp_name']);
    if ($sqlContent === false) {
        throw new Exception('Failed to read uploaded file');
    }
    
    // Validate SQL content (basic check)
    if (empty(trim($sqlContent))) {
        throw new Exception('SQL file is empty');
    }
    
    // Connect to database
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }
    
    // Split SQL content into individual statements
    $statements = explode(';', $sqlContent);
    $executedStatements = 0;
    $errors = [];
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip empty statements and comments
            if (empty($statement) || 
                strpos($statement, '--') === 0 || 
                strpos($statement, '/*') === 0 ||
                strpos($statement, '#') === 0) {
                continue;
            }
            
            // Execute statement
            $result = $conn->exec($statement);
            $executedStatements++;
            
            // Check for errors
            $errorInfo = $conn->errorInfo();
            if ($errorInfo[0] !== '00000') {
                $errors[] = "Statement $executedStatements: " . $errorInfo[2];
            }
        }
        
        // Commit if no errors
        if (empty($errors)) {
            $conn->commit();
            
            // Log successful upload
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'file_name' => $file['name'],
                'file_size' => $file['size'],
                'statements_executed' => $executedStatements,
                'status' => 'success'
            ];
            
            // Save to log file (optional)
            $logFile = '../logs/sql_uploads.log';
            if (!file_exists(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
            
            echo json_encode([
                'success' => true,
                'message' => 'SQL file executed successfully',
                'statements_executed' => $executedStatements,
                'file_info' => [
                    'name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type']
                ]
            ]);
            
        } else {
            $conn->rollback();
            throw new Exception('SQL execution errors: ' . implode('; ', $errors));
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Log error
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'file_name' => isset($file) ? $file['name'] : 'unknown',
        'error' => $e->getMessage(),
        'status' => 'error'
    ];
    
    $logFile = '../logs/sql_uploads.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, json_encode($errorLog) . "\n", FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'statements_executed' => $executedStatements ?? 0
    ]);
}
?>