<?php
require_once 'database.php';

$database = new Database();

// Create database first
if ($database->createDatabase()) {
    echo "Database created successfully or already exists.<br>";
} else {
    die("Failed to create database.");
}

// Get connection to the database
$conn = $database->getConnection();

if ($conn) {
    try {
        // Products table
        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            product_code VARCHAR(100) UNIQUE NOT NULL,
            barcode VARCHAR(255) UNIQUE NOT NULL,
            qr_code VARCHAR(255),
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            stock_quantity INT NOT NULL DEFAULT 0,
            min_stock_level INT NOT NULL DEFAULT 10,
            category_id INT,
            vendor_id INT,
            description TEXT,
            image_url VARCHAR(500),
            status ENUM('active', 'archived') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            archived_at TIMESTAMP NULL
        )";
        $conn->exec($sql);
        echo "Products table created successfully.<br>";

        // Categories table
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Categories table created successfully.<br>";

        // Vendors table
        $sql = "CREATE TABLE IF NOT EXISTS vendors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(50),
            address TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Vendors table created successfully.<br>";

        // Customers table
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(50),
            address TEXT,
            loyalty_points INT DEFAULT 0,
            customer_type ENUM('regular', 'vip', 'wholesale') DEFAULT 'regular',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Customers table created successfully.<br>";

        // Transactions table
        $sql = "CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_number VARCHAR(100) UNIQUE NOT NULL,
            customer_id INT,
            total_amount DECIMAL(10,2) NOT NULL,
            tax_amount DECIMAL(10,2) DEFAULT 0.00,
            discount_amount DECIMAL(10,2) DEFAULT 0.00,
            payment_method ENUM('cash', 'card', 'digital', 'check') NOT NULL,
            payment_status ENUM('pending', 'completed', 'refunded', 'cancelled') DEFAULT 'completed',
            transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            created_by INT,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
        )";
        $conn->exec($sql);
        echo "Transactions table created successfully.<br>";

        // Transaction items table
        $sql = "CREATE TABLE IF NOT EXISTS transaction_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->exec($sql);
        echo "Transaction items table created successfully.<br>";

        // Product scans table (for tracking scan frequency)
        $sql = "CREATE TABLE IF NOT EXISTS product_scans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            scan_type ENUM('barcode', 'qr_code', 'manual') DEFAULT 'barcode',
            scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->exec($sql);
        echo "Product scans table created successfully.<br>";

        // Inventory alerts table
        $sql = "CREATE TABLE IF NOT EXISTS inventory_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            alert_type ENUM('low_stock', 'out_of_stock', 'overstock') NOT NULL,
            message TEXT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        $conn->exec($sql);
        echo "Inventory alerts table created successfully.<br>";

        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            role ENUM('admin', 'manager', 'cashier') DEFAULT 'cashier',
            status ENUM('active', 'inactive') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Users table created successfully.<br>";

        // System settings table
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "System settings table created successfully.<br>";

        // Add foreign key constraints
        $sql = "ALTER TABLE products 
                ADD CONSTRAINT fk_products_category 
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                ADD CONSTRAINT fk_products_vendor 
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL";
        try {
            $conn->exec($sql);
            echo "Foreign key constraints added successfully.<br>";
        } catch(PDOException $e) {
            // Constraints might already exist
            echo "Foreign key constraints already exist or error: " . $e->getMessage() . "<br>";
        }

        // Insert default data
        insertDefaultData($conn);

        echo "<br><strong>Database initialization completed successfully!</strong>";

    } catch(PDOException $exception) {
        echo "Error creating tables: " . $exception->getMessage();
    }
} else {
    echo "Failed to connect to database.";
}

function insertDefaultData($conn) {
    try {
        // Insert default categories
        $sql = "INSERT IGNORE INTO categories (name, description) VALUES 
                ('Electronics', 'Electronic devices and accessories'),
                ('Clothing', 'Apparel and fashion items'),
                ('Food & Beverages', 'Food and drink products'),
                ('Books', 'Books and educational materials'),
                ('Home & Garden', 'Home improvement and garden supplies')";
        $conn->exec($sql);

        // Insert default vendor
        $sql = "INSERT IGNORE INTO vendors (name, contact_person, email, phone, address) VALUES 
                ('Default Supplier', 'John Doe', 'supplier@example.com', '+1234567890', '123 Business St, City, State')";
        $conn->exec($sql);

        // Insert default admin user (password: admin123)
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT IGNORE INTO users (username, email, password, full_name, role) VALUES 
                ('admin', 'admin@pos.com', '$hashedPassword', 'System Administrator', 'admin')";
        $conn->exec($sql);

        // Insert default system settings
        $sql = "INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES 
                ('company_name', 'POS System', 'Company/Store name'),
                ('currency', 'USD', 'Default currency'),
                ('tax_rate', '10.00', 'Default tax rate percentage'),
                ('low_stock_threshold', '10', 'Default low stock alert threshold'),
                ('scanner_enabled', '1', 'Enable barcode scanner integration')";
        $conn->exec($sql);

        // Insert sample products
        $sql = "INSERT IGNORE INTO products (name, product_code, barcode, price, cost_price, stock_quantity, category_id, vendor_id, description) VALUES 
                ('Sample Product 1', 'PRD001', '1234567890123', 29.99, 15.00, 50, 1, 1, 'Sample electronic product'),
                ('Sample Product 2', 'PRD002', '2345678901234', 19.99, 10.00, 30, 2, 1, 'Sample clothing item'),
                ('Sample Product 3', 'PRD003', '3456789012345', 9.99, 5.00, 100, 3, 1, 'Sample food product')";
        $conn->exec($sql);

        echo "Default data inserted successfully.<br>";
    } catch(PDOException $e) {
        echo "Error inserting default data: " . $e->getMessage() . "<br>";
    }
}
?>