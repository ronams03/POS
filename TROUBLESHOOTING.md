# POS System Troubleshooting Guide

## Quick Fix Steps

If your POS system is not functioning, follow these steps in order:

### 1. Check XAMPP Services
- Open XAMPP Control Panel
- Make sure **Apache** and **MySQL** services are running (green status)
- If not running, click "Start" for both services

### 2. Run Auto-Fix Script
- Open your browser and go to: `http://localhost/POS/fix-system.php`
- This will automatically detect and fix common issues

### 3. Run Diagnostics
- Go to: `http://localhost/POS/diagnose.php`
- This will show you exactly what's wrong with your system

### 4. Test Basic Functionality
- Go to: `http://localhost/POS/test-basic.html`
- This will test if the core system components are working

### 5. Run Setup Wizard
- Go to: `http://localhost/POS/setup.php`
- This will guide you through the complete setup process

## Common Issues and Solutions

### Issue: Blank Page or "Page Not Found"
**Solution:**
- Check if XAMPP Apache is running
- Make sure you're accessing `http://localhost/POS/` (not just `localhost`)
- Check if the POS folder is in the correct XAMPP htdocs directory

### Issue: Database Connection Errors
**Solution:**
- Check if XAMPP MySQL is running
- Run the auto-fix script: `http://localhost/POS/fix-system.php`
- Default MySQL settings: Host=localhost, Username=root, Password=(empty)

### Issue: "Table doesn't exist" Errors
**Solution:**
- Run database initialization: `http://localhost/POS/config/init_database.php`
- Or use the setup wizard: `http://localhost/POS/setup.php`

### Issue: JavaScript Errors or Non-Responsive Interface
**Solution:**
- Clear your browser cache (Ctrl+F5)
- Check browser console for errors (F12 → Console tab)
- Try the basic test page: `http://localhost/POS/test-basic.html`

### Issue: API Errors (Data Not Loading)
**Solution:**
- Test individual APIs using the diagnostic tool
- Check if database tables have data
- Verify file permissions on the api/ folder

## Default Login Credentials

After running the setup or auto-fix:
- **Username:** admin
- **Password:** admin123

## File Structure Check

Your POS folder should contain:
```
POS/
├── index.php (main system)
├── setup.php (setup wizard)
├── diagnose.php (diagnostic tool)
├── fix-system.php (auto-fix tool)
├── test-basic.html (basic test)
├── config/
│   ├── database.php
│   └── init_database.php
├── api/ (all API files)
├── assets/
│   ├── css/style.css
│   └── js/main.js
└── admin/ (admin tools)
```

## Step-by-Step Recovery Process

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

2. **Run Auto-Fix**
   - Go to `http://localhost/POS/fix-system.php`
   - Wait for all checks to complete

3. **Verify Setup**
   - Go to `http://localhost/POS/test-basic.html`
   - Test all functionality

4. **Access Main System**
   - Go to `http://localhost/POS/index.php`
   - Login with admin/admin123

## Getting Help

If you're still experiencing issues:

1. Run the diagnostic tool and note any error messages
2. Check the browser console (F12) for JavaScript errors
3. Verify XAMPP error logs in the XAMPP control panel
4. Make sure your system meets the requirements:
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Modern web browser with JavaScript enabled

## Manual Database Setup

If automatic setup fails, you can manually create the database:

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `pos_system`
3. Run the initialization script: `http://localhost/POS/config/init_database.php`

## Contact Information

For additional support, check the system logs and error messages provided by the diagnostic tools.