<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        $dbName = 'pos_system';
        
        // Get all tables with row counts and sizes
        $sql = "
            SELECT 
                t.TABLE_NAME as name,
                t.TABLE_ROWS as rows,
                ROUND(((t.DATA_LENGTH + t.INDEX_LENGTH) / 1024 / 1024), 2) as size_mb,
                t.ENGINE as engine,
                t.TABLE_COLLATION as collation,
                t.CREATE_TIME as created_at,
                t.UPDATE_TIME as updated_at
            FROM information_schema.TABLES t
            WHERE t.TABLE_SCHEMA = ?
            ORDER BY t.TABLE_NAME
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dbName]);
        $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get additional information for each table
        foreach ($tables as &$table) {
            // Get column count
            $colStmt = $conn->prepare("
                SELECT COUNT(*) as column_count 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ");
            $colStmt->execute([$dbName, $table['name']]);
            $table['column_count'] = (int)$colStmt->fetch(PDO::FETCH_ASSOC)['column_count'];
            
            // Get index count
            $idxStmt = $conn->prepare("
                SELECT COUNT(DISTINCT INDEX_NAME) as index_count 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ");
            $idxStmt->execute([$dbName, $table['name']]);
            $table['index_count'] = (int)$idxStmt->fetch(PDO::FETCH_ASSOC)['index_count'];
            
            // Convert values to appropriate types
            $table['rows'] = (int)$table['rows'];
            $table['size_mb'] = (float)$table['size_mb'];
        }
        
        // Calculate totals
        $totalRows = array_sum(array_column($tables, 'rows'));
        $totalSize = array_sum(array_column($tables, 'size_mb'));
        
        echo json_encode([
            'success' => true,
            'tables' => $tables,
            'summary' => [
                'total_tables' => count($tables),
                'total_rows' => $totalRows,
                'total_size_mb' => round($totalSize, 2),
                'database' => $dbName
            ]
        ]);
        
    } else {
        throw new Exception('Failed to connect to database');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>