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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['query'])) {
        throw new Exception('No SQL query provided');
    }
    
    $query = trim($input['query']);
    
    if (empty($query)) {
        throw new Exception('SQL query is empty');
    }
    
    // Security check - prevent dangerous operations
    $dangerousKeywords = [
        'DROP DATABASE',
        'DROP SCHEMA',
        'TRUNCATE',
        'DELETE FROM users',
        'UPDATE users SET password',
        'GRANT',
        'REVOKE',
        'CREATE USER',
        'DROP USER',
        'ALTER USER'
    ];
    
    $upperQuery = strtoupper($query);
    foreach ($dangerousKeywords as $keyword) {
        if (strpos($upperQuery, $keyword) !== false) {
            throw new Exception("Dangerous operation detected: $keyword. This query is not allowed for security reasons.");
        }
    }
    
    // Connect to database
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Failed to connect to database');
    }
    
    // Determine query type
    $queryType = strtoupper(trim(explode(' ', $query)[0]));
    
    try {
        if (in_array($queryType, ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'])) {
            // For SELECT queries, return results
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Query executed successfully',
                'query_type' => $queryType,
                'result' => $result,
                'row_count' => count($result)
            ]);
            
        } else {
            // For other queries (INSERT, UPDATE, DELETE, etc.)
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $affectedRows = $stmt->rowCount();
            
            echo json_encode([
                'success' => true,
                'message' => 'Query executed successfully',
                'query_type' => $queryType,
                'affected_rows' => $affectedRows
            ]);
        }
        
        // Log successful query execution
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'query_type' => $queryType,
            'query' => substr($query, 0, 200) . (strlen($query) > 200 ? '...' : ''),
            'affected_rows' => $affectedRows ?? 0,
            'status' => 'success'
        ];
        
        $logFile = '../logs/sql_queries.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
    } catch (PDOException $e) {
        throw new Exception('SQL Error: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    // Log error
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'query' => isset($query) ? substr($query, 0, 200) . (strlen($query) > 200 ? '...' : '') : 'unknown',
        'error' => $e->getMessage(),
        'status' => 'error'
    ];
    
    $logFile = '../logs/sql_queries.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, json_encode($errorLog) . "\n", FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'query_type' => isset($queryType) ? $queryType : 'unknown'
    ]);
}
?>