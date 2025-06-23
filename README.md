# Advanced Point of Sale (POS) System

A comprehensive web-based Point of Sale system with barcode scanning, inventory management, sales analytics, and customer management features.

## Features

### 🏪 Core POS Functionality
- **Product Management**: Add, edit, delete, and archive products
- **Barcode/QR Code Scanning**: USB scanner integration and camera scanning
- **Real-time Inventory**: Live stock tracking with low stock alerts
- **Transaction Processing**: Complete sales transaction management
- **Customer Management**: Customer profiles and purchase history

### 📊 Analytics & Reporting
- **Sales Dashboard**: Real-time sales overview with interactive charts
- **Product Analytics**: Most sold and most scanned products tracking
- **Time-based Reports**: Weekly, monthly, and yearly sales analysis
- **Inventory Reports**: Stock levels and movement tracking

### 🔧 Advanced Features
- **Multi-scanner Support**: USB barcode scanners and camera scanning
- **Archive System**: Soft delete with restore functionality
- **Alert System**: Low stock and out-of-stock notifications
- **Vendor Management**: Supplier tracking and management
- **Category Management**: Product categorization system
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### 🛡️ System Features
- **MySQL Database**: Robust data storage with relationships
- **RESTful API**: Clean API architecture for all operations
- **Real-time Updates**: Live data refresh and notifications
- **Export Functionality**: Data export capabilities
- **User Management**: Role-based access control

## Installation

### Prerequisites
- **XAMPP** (or similar LAMP/WAMP stack)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)

### Quick Setup

1. **Download and Extract**
   ```
   Extract the POS folder to your XAMPP htdocs directory
   Path: C:\xampp\htdocs\POS
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Run Setup**
   - Open your browser
   - Navigate to: `http://localhost/POS/setup.php`
   - Click "Start Setup" to initialize the database
   - Wait for setup completion
   - Click "Continue to POS System"

4. **Access the System**
   - URL: `http://localhost/POS/`
   - Default login: `admin` / `admin123`

### Manual Database Setup (Alternative)

If the automatic setup doesn't work:

1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create a new database named `pos_system`
3. Navigate to `http://localhost/POS/config/init_database.php`
4. This will create all tables and insert sample data

## Usage Guide

### Getting Started

1. **Dashboard Overview**
   - View sales statistics and key metrics
   - Monitor low stock alerts
   - Access quick actions

2. **Product Management**
   - Add new products with barcodes
   - Set stock levels and minimum thresholds
   - Organize products by categories
   - Upload product images

3. **Barcode Scanning**
   - Click the "Scan" button in the header
   - Use USB barcode scanner (plug and play)
   - Use camera scanning for mobile devices
   - Manual barcode entry option

4. **Processing Sales**
   - Scan products to add to transaction
   - Select customer (optional)
   - Choose payment method
   - Complete transaction

### Key Sections

#### Products Section
- **Add Products**: Complete product information with barcode
- **Bulk Operations**: Select multiple products for batch actions
- **Filtering**: Filter by category, status, or stock level
- **Search**: Find products by name, code, or barcode

#### Inventory Management
- **Stock Tracking**: Real-time inventory levels
- **Low Stock Alerts**: Automatic notifications
- **Stock Adjustments**: Manual stock level corrections
- **Reorder Management**: Track when to reorder products

#### Analytics & Reports
- **Sales Charts**: Visual representation of sales data
- **Product Performance**: Top selling and most scanned items
- **Time Period Analysis**: Weekly, monthly, yearly comparisons
- **Export Options**: Download reports in various formats

#### Customer Management
- **Customer Profiles**: Store customer information
- **Purchase History**: Track customer buying patterns
- **Loyalty Points**: Manage customer rewards
- **Customer Categories**: VIP, wholesale, regular customers

## Database Structure

### Core Tables
- **products**: Product information and inventory
- **categories**: Product categorization
- **vendors**: Supplier information
- **customers**: Customer profiles
- **transactions**: Sales transactions
- **transaction_items**: Individual transaction line items
- **product_scans**: Barcode scan tracking
- **inventory_alerts**: Stock level notifications
- **users**: System user accounts
- **system_settings**: Configuration options

### Key Relationships
- Products belong to categories and vendors
- Transactions link to customers and contain multiple items
- Alerts are generated based on product stock levels
- Scans are tracked per product for analytics

## API Endpoints

### Products API (`/api/products.php`)
- `GET`: List products with filtering and pagination
- `POST`: Create new product
- `PUT`: Update existing product
- `DELETE`: Delete or archive product

### Transactions API (`/api/transactions.php`)
- `GET`: List transactions with filtering
- `POST`: Create new transaction
- `PUT`: Update transaction
- `DELETE`: Delete transaction (with stock restoration)

### Analytics APIs
- `/api/dashboard-stats.php`: Dashboard statistics
- `/api/sales-data.php`: Sales data for charts
- `/api/top-products.php`: Best selling products
- `/api/scanned-products.php`: Most scanned products

### Other APIs
- `/api/scan-product.php`: Barcode scanning
- `/api/categories.php`: Category management
- `/api/customers.php`: Customer management
- `/api/vendors.php`: Vendor management
- `/api/alerts.php`: Inventory alerts

## Barcode Scanner Integration

### USB Barcode Scanners
- **Plug and Play**: Most USB barcode scanners work automatically
- **Keyboard Emulation**: Scanners that act as keyboard input
- **Real-time Scanning**: Instant product lookup on scan
- **Audio Feedback**: Beep sound on successful scan

### Camera Scanning
- **QuaggaJS Integration**: Browser-based barcode scanning
- **Multiple Formats**: Supports various barcode types
- **Mobile Friendly**: Works on smartphones and tablets
- **Manual Entry**: Fallback option for manual input

### Supported Barcode Formats
- Code 128
- EAN-13/EAN-8
- UPC-A/UPC-E
- Code 39
- Codabar
- Interleaved 2 of 5

## Customization

### Styling
- Modify `/assets/css/style.css` for custom styling
- Bootstrap 5 framework for responsive design
- CSS custom properties for easy color theming

### Configuration
- Database settings in `/config/database.php`
- System settings stored in database
- Configurable through admin interface

### Extensions
- Modular API structure for easy additions
- JavaScript plugin architecture
- Database schema designed for extensibility

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL service is running
   - Verify database credentials in `/config/database.php`
   - Ensure `pos_system` database exists

2. **Barcode Scanner Not Working**
   - Check USB connection
   - Test scanner in notepad (should type numbers)
   - Try manual barcode entry as alternative

3. **Charts Not Loading**
   - Check browser console for JavaScript errors
   - Ensure Chart.js library is loading
   - Verify API endpoints are accessible

4. **Permission Errors**
   - Check file permissions on web server
   - Ensure PHP has write access to necessary directories

### Browser Compatibility
- **Chrome**: Full support including camera scanning
- **Firefox**: Full support including camera scanning
- **Safari**: Full support (camera requires HTTPS in production)
- **Edge**: Full support including camera scanning

## Security Considerations

### Production Deployment
- Change default admin password
- Use HTTPS for secure communication
- Implement proper user authentication
- Regular database backups
- Update PHP and MySQL regularly

### Data Protection
- Customer data encryption
- Secure API endpoints
- Input validation and sanitization
- SQL injection prevention

## Support & Development

### File Structure
```
POS/
├── index.php              # Main application
├── setup.php             # Installation wizard
├── README.md             # This file
├── config/               # Configuration files
│   ├── database.php      # Database connection
│   └── init_database.php # Database initialization
├── api/                  # REST API endpoints
│   ├── products.php      # Product management
│   ├── transactions.php  # Transaction handling
│   ├── scan-product.php  # Barcode scanning
│   └── ...              # Other API files
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript files
└── .qodo/               # Development files
```

### Contributing
- Follow PSR coding standards for PHP
- Use ES6+ JavaScript features
- Maintain responsive design principles
- Add proper error handling
- Include API documentation

### License
This project is open source and available under the MIT License.

---

## Quick Start Checklist

- [ ] XAMPP installed and running
- [ ] POS files extracted to htdocs
- [ ] Visited setup.php and completed installation
- [ ] Accessed main system at localhost/POS
- [ ] Added first product with barcode
- [ ] Tested barcode scanning functionality
- [ ] Processed first transaction
- [ ] Reviewed dashboard analytics

**Need Help?** Check the troubleshooting section or review the API documentation for advanced usage.

---

*Built with PHP, MySQL, JavaScript, Bootstrap, and Chart.js*