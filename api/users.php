<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
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
            handlePost($db);
            break;
        case 'PUT':
            handlePut($db);
            break;
        case 'DELETE':
            handleDelete($db);
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
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at 
                FROM users 
                ORDER BY created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format dates
        foreach ($users as &$user) {
            $user['last_login'] = $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'Never';
            $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Error fetching users: ' . $e->getMessage());
    }
}

function handlePost($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            return;
        }
        
        $required_fields = ['username', 'email', 'password', 'full_name', 'role'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                return;
            }
        }
        
        // Check if username or email already exists
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$input['username'], $input['email']]);
        
        if ($checkStmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
            return;
        }
        
        // Validate role
        $validRoles = ['admin', 'manager', 'cashier'];
        if (!in_array($input['role'], $validRoles)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, full_name, role, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $input['username'],
            $input['email'],
            $hashedPassword,
            $input['full_name'],
            $input['role'],
            $input['status'] ?? 'active'
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $db->lastInsertId()
            ]);
        } else {
            throw new Exception('Failed to create user');
        }
        
    } catch (Exception $e) {
        throw new Exception('Error creating user: ' . $e->getMessage());
    }
}

function handlePut($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        $userId = $input['id'];
        
        // Check if user exists
        $checkSql = "SELECT id FROM users WHERE id = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$userId]);
        
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Build update query
        $updateFields = [];
        $params = [];
        
        $allowedFields = ['username', 'email', 'full_name', 'role', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        
        // Handle password update separately
        if (isset($input['password']) && !empty($input['password'])) {
            $updateFields[] = "password = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            return;
        }
        
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            throw new Exception('Failed to update user');
        }
        
    } catch (Exception $e) {
        throw new Exception('Error updating user: ' . $e->getMessage());
    }
}

function handleDelete($db) {
    try {
        $userId = $_GET['id'] ?? null;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            return;
        }
        
        // Check if user exists
        $checkSql = "SELECT id, username FROM users WHERE id = ?";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$userId]);
        $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Prevent deletion of admin user
        if ($user['username'] === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Cannot delete the admin user']);
            return;
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$userId]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            throw new Exception('Failed to delete user');
        }
        
    } catch (Exception $e) {
        throw new Exception('Error deleting user: ' . $e->getMessage());
    }
}
?>