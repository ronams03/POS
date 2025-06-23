<?php
// POS System Auto-Fix Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>POS System Auto-Fix</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
.log { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
</style>";

$fixLog = [];

function addLog($message, $type = 'info') {
    global $fixLog;
    $fixLog[] = ['message' => $message, 'type' => $type, 'time' => date('H:i:s')];
    echo "<p class='$type'>[" . date('H:i:s') . "] $message</p>";
    flush();
}

echo "<div class='section'>";
echo "<h2>Starting Auto-Fix Process...</h2>";

// Step 1: Check and fix database connection
addLog("Checking database connection...");
try {
    require_once 'config/database.php';
    $database = new Database();
    
    // Create database
    if ($database->createDatabase()) {
        addLog("Database 'pos_system' created/verified", 'success');
    } else {
        addLog("Failed to create database", 'error');
        exit;
    }
    
    // Get connection
    $conn = $database->getConnection();
    if ($conn) {
        addLog("Database connection established", 'success');
    } else {
        addLog("Database connection failed", 'error');
        exit;
    }
    
} catch (Exception $e) {
    addLog("Database error: " . $e->getMessage(), 'error');
    addLog("Please ensure XAMPP MySQL is running", 'warning');
    exit;
}

// Step 2: Initialize database tables
addLog("Initializing database tables...");
try {
    ob_start();
    include 'config/init_database.php';
    $output = ob_get_clean();
    
    if (strpos($output, 'successfully') !== false) {
        addLog("Database tables initialized successfully", 'success');
    } else {
        addLog("Database initialization may have issues", 'warning');
    }
    
} catch (Exception $e) {
    addLog("Error initializing database: " . $e->getMessage(), 'error');
}

// Step 3: Test API endpoints
addLog("Testing API endpoints...");
$apiTests = [
    'dashboard-stats.php' => 'Dashboard statistics',
    'products.php' => 'Products management',
    'categories.php' => 'Categories management'
];

foreach ($apiTests as $endpoint => $description) {
    try {
        $apiPath = "api/$endpoint";
        if (file_exists($apiPath)) {
            // Simulate a GET request
            $_SERVER['REQUEST_METHOD'] = 'GET';
            ob_start();
            include $apiPath;
            $output = ob_get_clean();
            
            $json = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['success'])) {
                addLog("API $endpoint working correctly", 'success');
            } else {
                addLog("API $endpoint may have issues", 'warning');
            }
        } else {
            addLog("API file $endpoint missing", 'error');
        }
    } catch (Exception $e) {
        addLog("Error testing API $endpoint: " . $e->getMessage(), 'error');
    }
}

// Step 4: Check and fix file permissions (if needed)
addLog("Checking file structure...");
$requiredDirs = ['api', 'assets', 'assets/css', 'assets/js', 'config'];
foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        addLog("Directory $dir exists", 'success');
    } else {
        addLog("Directory $dir missing", 'error');
    }
}

// Step 5: Create a test user if none exists
addLog("Checking for admin user...");
try {
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($adminCount == 0) {
        addLog("Creating default admin user...");
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute(['admin', 'admin@pos.com', $hashedPassword, 'System Administrator', 'admin']);
        
        if ($result) {
            addLog("Default admin user created (username: admin, password: admin123)", 'success');
        } else {
            addLog("Failed to create admin user", 'error');
        }
    } else {
        addLog("Admin user already exists", 'success');
    }
} catch (Exception $e) {
    addLog("Error checking admin user: " . $e->getMessage(), 'error');
}

// Step 6: Test system functionality
addLog("Testing system functionality...");
try {
    // Test dashboard stats
    $sql = "SELECT COUNT(*) as total FROM products";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    addLog("Found $productCount products in database", 'info');
    
    // Test categories
    $sql = "SELECT COUNT(*) as total FROM categories";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categoryCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    addLog("Found $categoryCount categories in database", 'info');
    
} catch (Exception $e) {
    addLog("Error testing system: " . $e->getMessage(), 'error');
}

addLog("Auto-fix process completed!", 'success');

echo "</div>";

echo "<div class='section'>";
echo "<h2>Fix Summary</h2>";
echo "<p>The auto-fix process has completed. Here's what you can do next:</p>";
echo "<ol>";
echo "<li><strong>Test the system:</strong> <a href='index.php' target='_blank'>Open POS System</a></li>";
echo "<li><strong>Run diagnostics:</strong> <a href='diagnose.php' target='_blank'>Run Diagnostic</a></li>";
echo "<li><strong>Setup wizard:</strong> <a href='setup.php' target='_blank'>Setup Wizard</a></li>";
echo "</ol>";

echo "<h3>Default Login Credentials:</h3>";
echo "<p><strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123</p>";

echo "<h3>Common Issues and Solutions:</h3>";
echo "<ul>";
echo "<li><strong>Blank page:</strong> Check if XAMPP Apache and MySQL are running</li>";
echo "<li><strong>Database errors:</strong> Make sure MySQL service is started in XAMPP</li>";
echo "<li><strong>API errors:</strong> Check browser console (F12) for JavaScript errors</li>";
echo "<li><strong>Permission errors:</strong> Make sure the POS folder has proper read/write permissions</li>";
echo "</ul>";
echo "</div>";
?>