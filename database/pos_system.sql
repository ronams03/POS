-- POS System Database Structure
-- Created for Advanced Point of Sale System
-- Database: pos_system

-- Create database
CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8 COLLATE utf8_general_ci;
USE pos_system;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `vendors`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` int(11) NOT NULL DEFAULT '0',
  `min_stock_level` int(11) NOT NULL DEFAULT '10',
  `category_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `description` text,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `fk_products_category` (`category_id`),
  KEY `fk_products_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `customers`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text,
  `loyalty_points` int(11) NOT NULL DEFAULT '0',
  `customer_type` enum('regular','vip','wholesale') NOT NULL DEFAULT 'regular',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(100) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cash','card','digital','check') NOT NULL,
  `payment_status` enum('pending','completed','refunded','cancelled') NOT NULL DEFAULT 'completed',
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_number` (`transaction_number`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `transaction_items`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `product_scans`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `product_scans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `scan_type` enum('barcode','qr_code','manual') NOT NULL DEFAULT 'barcode',
  `scanned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `inventory_alerts`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `inventory_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `alert_type` enum('low_stock','out_of_stock','overstock') NOT NULL,
  `message` text,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('admin','manager','cashier') NOT NULL DEFAULT 'cashier',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Add foreign key constraints
-- --------------------------------------------------------

ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `product_scans`
  ADD CONSTRAINT `product_scans_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Insert default data
-- --------------------------------------------------------

-- Insert default categories
INSERT IGNORE INTO `categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Food & Beverages', 'Food and drink products'),
('Books', 'Books and educational materials'),
('Home & Garden', 'Home improvement and garden supplies'),
('Health & Beauty', 'Health and beauty products'),
('Sports & Outdoors', 'Sports and outdoor equipment'),
('Toys & Games', 'Toys and gaming products'),
('Automotive', 'Automotive parts and accessories'),
('Office Supplies', 'Office and business supplies');

-- Insert default vendor
INSERT IGNORE INTO `vendors` (`name`, `contact_person`, `email`, `phone`, `address`, `status`) VALUES
('Default Supplier', 'John Doe', 'supplier@example.com', '+1234567890', '123 Business St, City, State', 'active'),
('Tech Solutions Inc', 'Jane Smith', 'tech@solutions.com', '+1234567891', '456 Tech Ave, Silicon Valley', 'active'),
('Fashion Wholesale', 'Mike Johnson', 'orders@fashionwholesale.com', '+1234567892', '789 Fashion Blvd, New York', 'active');

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@pos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'active'),
('manager', 'manager@pos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'manager', 'active'),
('cashier', 'cashier@pos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Cashier', 'cashier', 'active');

-- Insert default system settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('company_name', 'POS System', 'Company/Store name'),
('company_address', '123 Main Street, City, State 12345', 'Company address'),
('company_phone', '+1 (555) 123-4567', 'Company phone number'),
('company_email', 'info@possystem.com', 'Company email address'),
('currency', 'USD', 'Default currency'),
('currency_symbol', '$', 'Currency symbol'),
('tax_rate', '10.00', 'Default tax rate percentage'),
('low_stock_threshold', '10', 'Default low stock alert threshold'),
('scanner_enabled', '1', 'Enable barcode scanner integration'),
('receipt_footer', 'Thank you for your business!', 'Receipt footer message'),
('timezone', 'America/New_York', 'System timezone'),
('date_format', 'Y-m-d', 'Date format'),
('time_format', 'H:i:s', 'Time format');

-- Insert sample products
INSERT IGNORE INTO `products` (`name`, `product_code`, `barcode`, `price`, `cost_price`, `stock_quantity`, `min_stock_level`, `category_id`, `vendor_id`, `description`) VALUES
('Wireless Bluetooth Headphones', 'PRD001', '1234567890123', 79.99, 45.00, 25, 5, 1, 1, 'High-quality wireless Bluetooth headphones with noise cancellation'),
('Cotton T-Shirt - Blue', 'PRD002', '2345678901234', 19.99, 12.00, 50, 10, 2, 3, 'Comfortable cotton t-shirt in blue color, size M'),
('Organic Coffee Beans - 1lb', 'PRD003', '3456789012345', 14.99, 8.50, 30, 8, 3, 1, 'Premium organic coffee beans, medium roast'),
('Programming Book - JavaScript', 'PRD004', '4567890123456', 39.99, 25.00, 15, 3, 4, 1, 'Complete guide to JavaScript programming'),
('Garden Hose - 50ft', 'PRD005', '5678901234567', 29.99, 18.00, 12, 3, 5, 1, 'Durable 50-foot garden hose with spray nozzle'),
('Smartphone Case - Clear', 'PRD006', '6789012345678', 12.99, 6.50, 40, 8, 1, 2, 'Clear protective case for smartphones'),
('Running Shoes - Size 10', 'PRD007', '7890123456789', 89.99, 55.00, 20, 4, 7, 1, 'Comfortable running shoes with cushioned sole'),
('Board Game - Strategy', 'PRD008', '8901234567890', 34.99, 22.00, 18, 5, 8, 1, 'Strategic board game for 2-4 players'),
('Car Air Freshener', 'PRD009', '9012345678901', 4.99, 2.50, 60, 15, 9, 1, 'Long-lasting car air freshener, vanilla scent'),
('Office Notebook - A4', 'PRD010', '0123456789012', 8.99, 4.50, 35, 10, 10, 1, 'Professional A4 notebook with lined pages');

-- Insert sample customers
INSERT IGNORE INTO `customers` (`name`, `email`, `phone`, `address`, `loyalty_points`, `customer_type`) VALUES
('John Smith', 'john.smith@email.com', '+1234567890', '123 Main St, Anytown, ST 12345', 150, 'regular'),
('Sarah Johnson', 'sarah.j@email.com', '+1234567891', '456 Oak Ave, Somewhere, ST 12346', 320, 'vip'),
('Mike Wilson', 'mike.wilson@business.com', '+1234567892', '789 Business Blvd, Commerce, ST 12347', 75, 'wholesale'),
('Emily Davis', 'emily.davis@email.com', '+1234567893', '321 Pine St, Hometown, ST 12348', 200, 'regular'),
('Robert Brown', 'robert.brown@email.com', '+1234567894', '654 Elm Dr, Newtown, ST 12349', 450, 'vip');

-- Insert sample transactions (for demo purposes)
INSERT IGNORE INTO `transactions` (`transaction_number`, `customer_id`, `total_amount`, `tax_amount`, `payment_method`, `payment_status`, `transaction_date`) VALUES
('TXN20240101001', 1, 87.98, 7.98, 'card', 'completed', '2024-01-01 10:30:00'),
('TXN20240101002', 2, 34.98, 3.18, 'cash', 'completed', '2024-01-01 11:45:00'),
('TXN20240101003', NULL, 19.99, 1.81, 'card', 'completed', '2024-01-01 14:20:00'),
('TXN20240101004', 3, 159.96, 14.54, 'digital', 'completed', '2024-01-01 16:10:00'),
('TXN20240102001', 1, 44.98, 4.09, 'cash', 'completed', '2024-01-02 09:15:00');

-- Insert sample transaction items
INSERT IGNORE INTO `transaction_items` (`transaction_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, 79.99, 79.99),
(2, 3, 2, 14.99, 29.98),
(2, 9, 1, 4.99, 4.99),
(3, 2, 1, 19.99, 19.99),
(4, 7, 1, 89.99, 89.99),
(4, 4, 1, 39.99, 39.99),
(4, 6, 2, 12.99, 25.98),
(5, 5, 1, 29.99, 29.99),
(5, 3, 1, 14.99, 14.99);

-- Insert sample product scans (for analytics)
INSERT IGNORE INTO `product_scans` (`product_id`, `scan_type`, `scanned_at`) VALUES
(1, 'barcode', '2024-01-01 10:29:45'),
(3, 'barcode', '2024-01-01 11:44:30'),
(3, 'barcode', '2024-01-01 11:44:35'),
(9, 'barcode', '2024-01-01 11:44:50'),
(2, 'barcode', '2024-01-01 14:19:20'),
(7, 'barcode', '2024-01-01 16:09:15'),
(4, 'barcode', '2024-01-01 16:09:30'),
(6, 'barcode', '2024-01-01 16:09:45'),
(6, 'barcode', '2024-01-01 16:09:50'),
(5, 'barcode', '2024-01-02 09:14:30'),
(3, 'barcode', '2024-01-02 09:14:45');

-- Create indexes for better performance
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_stock ON products(stock_quantity);
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_status ON transactions(payment_status);
CREATE INDEX idx_product_scans_date ON product_scans(scanned_at);
CREATE INDEX idx_inventory_alerts_read ON inventory_alerts(is_read);

-- --------------------------------------------------------
-- Views for common queries
-- --------------------------------------------------------

-- View for low stock products
CREATE OR REPLACE VIEW low_stock_products AS
SELECT 
    p.id,
    p.name,
    p.product_code,
    p.barcode,
    p.stock_quantity,
    p.min_stock_level,
    c.name as category_name,
    v.name as vendor_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN vendors v ON p.vendor_id = v.id
WHERE p.stock_quantity <= p.min_stock_level 
AND p.status = 'active';

-- View for sales summary
CREATE OR REPLACE VIEW daily_sales_summary AS
SELECT 
    DATE(transaction_date) as sale_date,
    COUNT(*) as total_transactions,
    SUM(total_amount) as total_sales,
    SUM(tax_amount) as total_tax,
    AVG(total_amount) as average_transaction
FROM transactions 
WHERE payment_status = 'completed'
GROUP BY DATE(transaction_date)
ORDER BY sale_date DESC;

-- View for product performance
CREATE OR REPLACE VIEW product_performance AS
SELECT 
    p.id,
    p.name,
    p.product_code,
    p.price,
    COALESCE(SUM(ti.quantity), 0) as total_sold,
    COALESCE(SUM(ti.total_price), 0) as total_revenue,
    COALESCE(COUNT(DISTINCT t.id), 0) as transaction_count,
    COALESCE(COUNT(ps.id), 0) as scan_count
FROM products p
LEFT JOIN transaction_items ti ON p.id = ti.product_id
LEFT JOIN transactions t ON ti.transaction_id = t.id AND t.payment_status = 'completed'
LEFT JOIN product_scans ps ON p.id = ps.product_id
WHERE p.status = 'active'
GROUP BY p.id, p.name, p.product_code, p.price
ORDER BY total_sold DESC;

-- --------------------------------------------------------
-- Stored procedures for common operations
-- --------------------------------------------------------

DELIMITER //

-- Procedure to process a sale
CREATE PROCEDURE IF NOT EXISTS ProcessSale(
    IN p_customer_id INT,
    IN p_total_amount DECIMAL(10,2),
    IN p_tax_amount DECIMAL(10,2),
    IN p_discount_amount DECIMAL(10,2),
    IN p_payment_method VARCHAR(20),
    IN p_items JSON,
    OUT p_transaction_id INT,
    OUT p_transaction_number VARCHAR(100)
)
BEGIN
    DECLARE v_transaction_number VARCHAR(100);
    DECLARE v_product_id INT;
    DECLARE v_quantity INT;
    DECLARE v_unit_price DECIMAL(10,2);
    DECLARE v_total_price DECIMAL(10,2);
    DECLARE v_counter INT DEFAULT 0;
    DECLARE v_items_count INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Generate transaction number
    SET v_transaction_number = CONCAT('TXN', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 9999) + 1, 4, '0'));
    
    -- Insert transaction
    INSERT INTO transactions (
        transaction_number, customer_id, total_amount, tax_amount, 
        discount_amount, payment_method, payment_status
    ) VALUES (
        v_transaction_number, p_customer_id, p_total_amount, p_tax_amount,
        p_discount_amount, p_payment_method, 'completed'
    );
    
    SET p_transaction_id = LAST_INSERT_ID();
    SET p_transaction_number = v_transaction_number;
    
    -- Process items
    SET v_items_count = JSON_LENGTH(p_items);
    
    WHILE v_counter < v_items_count DO
        SET v_product_id = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_counter, '].product_id')));
        SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_counter, '].quantity')));
        SET v_unit_price = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_counter, '].unit_price')));
        SET v_total_price = v_quantity * v_unit_price;
        
        -- Insert transaction item
        INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, total_price)
        VALUES (p_transaction_id, v_product_id, v_quantity, v_unit_price, v_total_price);
        
        -- Update product stock
        UPDATE products SET stock_quantity = stock_quantity - v_quantity WHERE id = v_product_id;
        
        SET v_counter = v_counter + 1;
    END WHILE;
    
    COMMIT;
END //

DELIMITER ;

-- --------------------------------------------------------
-- Triggers for automatic operations
-- --------------------------------------------------------

DELIMITER //

-- Trigger to create low stock alerts
CREATE TRIGGER IF NOT EXISTS after_product_stock_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF NEW.stock_quantity <= NEW.min_stock_level AND NEW.stock_quantity > 0 AND OLD.stock_quantity > NEW.min_stock_level THEN
        INSERT INTO inventory_alerts (product_id, alert_type, message)
        VALUES (NEW.id, 'low_stock', CONCAT('Product "', NEW.name, '" is running low on stock (', NEW.stock_quantity, ' remaining)'));
    END IF;
    
    IF NEW.stock_quantity = 0 AND OLD.stock_quantity > 0 THEN
        INSERT INTO inventory_alerts (product_id, alert_type, message)
        VALUES (NEW.id, 'out_of_stock', CONCAT('Product "', NEW.name, '" is out of stock'));
    END IF;
END //

-- Trigger to update product timestamps
CREATE TRIGGER IF NOT EXISTS before_product_update
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
    
    IF NEW.status = 'archived' AND OLD.status != 'archived' THEN
        SET NEW.archived_at = CURRENT_TIMESTAMP;
    END IF;
END //

DELIMITER ;

-- --------------------------------------------------------
-- Final setup
-- --------------------------------------------------------

-- Set AUTO_INCREMENT starting values
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE vendors AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;
ALTER TABLE transactions AUTO_INCREMENT = 1;
ALTER TABLE transaction_items AUTO_INCREMENT = 1;
ALTER TABLE product_scans AUTO_INCREMENT = 1;
ALTER TABLE inventory_alerts AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE system_settings AUTO_INCREMENT = 1;

-- Create database user (optional - for production use)
-- CREATE USER IF NOT EXISTS 'pos_user'@'localhost' IDENTIFIED BY 'pos_password_2024';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON pos_system.* TO 'pos_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Database setup completed
SELECT 'POS System database setup completed successfully!' as Status;