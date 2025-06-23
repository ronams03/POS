<?php
// POS System Diagnostic and Fix Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>POS System Diagnostic</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
.info { color: blue; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
</style>";

// Test 1: Check if database connection works
echo "<div class='section'>";
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    $database = new Database();
    
    // Test basic MySQL connection
    $testConn = new PDO("mysql:host=localhost", "root", "");
    echo "<p class='success'>✓ MySQL connection successful</p>";
    
    // Test database creation
    if ($database->createDatabase()) {
        echo "<p class='success'>✓ Database 'pos_system' created/exists</p>";
    } else {
        echo "<p class='error'>✗ Failed to create database</p>";
    }
    
    // Test database connection
    $conn = $database->getConnection();
    if ($conn) {
        echo "<p class='success'>✓ Database connection successful</p>";
    } else {
        echo "<p class='error'>✗ Database connection failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
    echo "<p class='info'>Make sure XAMPP MySQL is running</p>";
}
echo "</div>";

// Test 2: Check if tables exist
echo "<div class='section'>";
echo "<h2>2. Database Tables Check</h2>";
try {
    if (isset($conn) && $conn) {
        $tables = ['products', 'categories', 'vendors', 'customers', 'transactions', 'transaction_items', 'product_scans', 'inventory_alerts', 'users', 'system_settings'];
        
        foreach ($tables as $table) {
            $sql = "SHOW TABLES LIKE '$table'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                echo "<p class='success'>✓ Table '$table' exists</p>";
            } else {
                echo "<p class='error'>✗ Table '$table' missing</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error checking tables: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 3: Check API endpoints
echo "<div class='section'>";
echo "<h2>3. API Endpoints Test</h2>";
$apiEndpoints = [
    'products.php',
    'dashboard-stats.php',
    'categories.php',
    'customers.php',
    'vendors.php'
];

foreach ($apiEndpoints as $endpoint) {
    $apiPath = "api/$endpoint";
    if (file_exists($apiPath)) {
        echo "<p class='success'>✓ API file '$endpoint' exists</p>";
        
        // Test if the API returns valid JSON
        try {
            ob_start();
            include $apiPath;
            $output = ob_get_clean();
            
            $json = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p class='success'>✓ API '$endpoint' returns valid JSON</p>";
            } else {
                echo "<p class='warning'>⚠ API '$endpoint' may have issues</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ API '$endpoint' error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>✗ API file '$endpoint' missing</p>";
    }
}
echo "</div>";

// Test 4: Check file permissions and structure
echo "<div class='section'>";
echo "<h2>4. File Structure Check</h2>";
$requiredFiles = [
    'index.php',
    'config/database.php',
    'config/init_database.php',
    'assets/css/style.css',
    'assets/js/main.js'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ File '$file' exists</p>";
    } else {
        echo "<p class='error'>✗ File '$file' missing</p>";
    }
}
echo "</div>";

// Test 5: Initialize database if needed
echo "<div class='section'>";
echo "<h2>5. Database Initialization</h2>";
echo "<p><a href='config/init_database.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Initialize Database</a></p>";
echo "<p class='info'>Click the button above to initialize the database with all required tables and sample data.</p>";
echo "</div>";

// Test 6: Quick fix suggestions
echo "<div class='section'>";
echo "<h2>6. Quick Fix Actions</h2>";
echo "<p><strong>If you're experiencing issues, try these steps:</strong></p>";
echo "<ol>";
echo "<li>Make sure XAMPP is running (Apache and MySQL services)</li>";
echo "<li>Click 'Initialize Database' above to set up the database</li>";
echo "<li>Clear your browser cache and reload the main page</li>";
echo "<li>Check the browser console for JavaScript errors (F12)</li>";
echo "</ol>";

echo "<p><a href='setup.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Run Setup Wizard</a>";
echo "<a href='index.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to POS System</a></p>";
echo "</div>";

// Test 7: System Information
echo "<div class='section'>";
echo "<h2>7. System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "</div>";
?>