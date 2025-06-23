<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced POS System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-store me-2"></i>
                <span id="company-name">POS System</span>
            </a>
            
            <!-- Scanner Status -->
            <div class="scanner-status me-3">
                <span class="badge bg-success" id="scanner-status">
                    <i class="fas fa-barcode me-1"></i>
                    Scanner Ready
                </span>
            </div>

            <!-- Search Bar -->
            <div class="search-container me-3">
                <div class="input-group">
                    <input type="text" class="form-control" id="product-search" placeholder="Search products...">
                    <button class="btn btn-outline-light" type="button" id="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="navbar-nav flex-row">
                <button class="btn btn-success me-2" id="scan-btn">
                    <i class="fas fa-qrcode me-1"></i>
                    Scan
                </button>
                <button class="btn btn-info me-2" id="alerts-btn">
                    <i class="fas fa-bell me-1"></i>
                    <span class="badge bg-danger" id="alert-count">0</span>
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        Admin
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="showSettings()"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="admin/database-manager.php"><i class="fas fa-database me-2"></i>Database Manager</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="javascript:void(0)" onclick="showSection('dashboard')">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('products')">
                                <i class="fas fa-box me-2"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('transactions')">
                                <i class="fas fa-receipt me-2"></i>Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('customers')">
                                <i class="fas fa-users me-2"></i>Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('vendors')">
                                <i class="fas fa-truck me-2"></i>Vendors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('inventory')">
                                <i class="fas fa-warehouse me-2"></i>Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('archive')">
                                <i class="fas fa-archive me-2"></i>Archive
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="javascript:void(0)" onclick="showSection('reports')">
                                <i class="fas fa-chart-bar me-2"></i>Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Dashboard</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-products">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Sales</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="today-sales">$0.00</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Low Stock Items</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="low-stock-count">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Customers</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-customers">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Information Panel -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Current Product Scan</h6>
                                </div>
                                <div class="card-body">
                                    <div id="product-display" class="text-center">
                                        <div class="product-placeholder">
                                            <i class="fas fa-barcode fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Scan a product to display information</p>
                                        </div>
                                        <div id="product-info" style="display: none;">
                                            <h4 id="product-name"></h4>
                                            <div class="row mt-3">
                                                <div class="col-6">
                                                    <strong>Product Code:</strong>
                                                    <p id="product-code"></p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Barcode:</strong>
                                                    <p id="product-barcode"></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Price:</strong>
                                                    <p id="product-price"></p>
                                                </div>
                                                <div class="col-6">
                                                    <strong>Stock:</strong>
                                                    <p id="product-stock"></p>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <button class="btn btn-primary btn-sm me-2" onclick="editProduct()">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-warning btn-sm me-2" onclick="archiveProduct()">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="deleteProduct()">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <button class="btn btn-success w-100" onclick="showAddProductModal()">
                                                <i class="fas fa-plus"></i><br>Add Product
                                            </button>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <button class="btn btn-info w-100" onclick="showNewTransactionModal()">
                                                <i class="fas fa-cash-register"></i><br>New Sale
                                            </button>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <button class="btn btn-warning w-100" onclick="showInventoryAlerts()">
                                                <i class="fas fa-exclamation-triangle"></i><br>Stock Alerts
                                            </button>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <button class="btn btn-secondary w-100" onclick="exportData()">
                                                <i class="fas fa-download"></i><br>Export Data
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Dashboard -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="sales-period" id="week-sales" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary btn-sm" for="week-sales">Week</label>
                                        <input type="radio" class="btn-check" name="sales-period" id="month-sales" autocomplete="off">
                                        <label class="btn btn-outline-primary btn-sm" for="month-sales">Month</label>
                                        <input type="radio" class="btn-check" name="sales-period" id="year-sales" autocomplete="off">
                                        <label class="btn btn-outline-primary btn-sm" for="year-sales">Year</label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="salesChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Most Sold Products</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="topProductsChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Most Scanned Products</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="scannedProductsChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Transaction #</th>
                                                    <th>Amount</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recent-transactions">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No recent transactions</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Section -->
                <div id="products-section" class="content-section" style="display: none;">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">Product Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-sm btn-success" onclick="showAddProductModal()">
                                    <i class="fas fa-plus"></i> Add Product
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshProducts()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Product Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="category-filter">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="status-filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="stock-filter">
                                <option value="">All Stock Levels</option>
                                <option value="low">Low Stock</option>
                                <option value="out">Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="product-filter-search" placeholder="Search products...">
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="products-table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select-all-products"></th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Barcode</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="products-tbody">
                                        <tr>
                                            <td colspan="9" class="text-center">Loading products...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <nav>
                                <ul class="pagination justify-content-center" id="products-pagination">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Other sections will be loaded dynamically -->
                <div id="transactions-section" class="content-section" style="display: none;">
                    <h1 class="h2">Transaction History</h1>
                    <p>Transaction management interface will be loaded here.</p>
                </div>

                <div id="customers-section" class="content-section" style="display: none;">
                    <h1 class="h2">Customer Management</h1>
                    <p>Customer management interface will be loaded here.</p>
                </div>

                <div id="vendors-section" class="content-section" style="display: none;">
                    <h1 class="h2">Vendor Management</h1>
                    <p>Vendor management interface will be loaded here.</p>
                </div>

                <div id="inventory-section" class="content-section" style="display: none;">
                    <h1 class="h2">Inventory Management</h1>
                    <p>Inventory management interface will be loaded here.</p>
                </div>

                <div id="archive-section" class="content-section" style="display: none;">
                    <h1 class="h2">Archive Management</h1>
                    <p>Archive management interface will be loaded here.</p>
                </div>

                <div id="reports-section" class="content-section" style="display: none;">
                    <h1 class="h2">Reports & Analytics</h1>
                    <p>Reports and analytics interface will be loaded here.</p>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals will be added here -->
    <div id="modals-container">
        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addProductForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Product Name *</label>
                                        <input type="text" class="form-control" id="productName" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productCode" class="form-label">Product Code *</label>
                                        <input type="text" class="form-control" id="productCode" name="product_code" required>
                                        <div class="form-text">Unique identifier for the product</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productBarcode" class="form-label">Barcode *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="productBarcode" name="barcode" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="generateBarcode()">
                                                <i class="fas fa-magic"></i> Generate
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productQRCode" class="form-label">QR Code</label>
                                        <input type="text" class="form-control" id="productQRCode" name="qr_code">
                                        <div class="form-text">Optional QR code for the product</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productPrice" class="form-label">Selling Price *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productCostPrice" class="form-label">Cost Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="productCostPrice" name="cost_price" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="productStock" class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" id="productStock" name="stock_quantity" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="productMinStock" class="form-label">Min Stock Level</label>
                                        <input type="number" class="form-control" id="productMinStock" name="min_stock_level" min="0" value="10">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="productStatus" class="form-label">Status</label>
                                        <select class="form-select" id="productStatus" name="status">
                                            <option value="active">Active</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productCategory" class="form-label">Category</label>
                                        <select class="form-select" id="productCategory" name="category_id">
                                            <option value="">Select Category</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productVendor" class="form-label">Vendor</label>
                                        <select class="form-select" id="productVendor" name="vendor_id">
                                            <option value="">Select Vendor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="productDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="productImage" class="form-label">Product Image URL</label>
                                <input type="url" class="form-control" id="productImage" name="image_url" placeholder="https://example.com/image.jpg">
                                <div class="form-text">Enter a URL to an image for this product</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveProduct()">
                            <i class="fas fa-save me-2"></i>Save Product
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Product Modal -->
        <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editProductForm">
                            <input type="hidden" id="editProductId" name="id">
                            <!-- Same form fields as add product -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductName" class="form-label">Product Name *</label>
                                        <input type="text" class="form-control" id="editProductName" name="name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductCode" class="form-label">Product Code *</label>
                                        <input type="text" class="form-control" id="editProductCode" name="product_code" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductBarcode" class="form-label">Barcode *</label>
                                        <input type="text" class="form-control" id="editProductBarcode" name="barcode" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductQRCode" class="form-label">QR Code</label>
                                        <input type="text" class="form-control" id="editProductQRCode" name="qr_code">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductPrice" class="form-label">Selling Price *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="editProductPrice" name="price" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductCostPrice" class="form-label">Cost Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="editProductCostPrice" name="cost_price" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="editProductStock" class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" id="editProductStock" name="stock_quantity" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="editProductMinStock" class="form-label">Min Stock Level</label>
                                        <input type="number" class="form-control" id="editProductMinStock" name="min_stock_level" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="editProductStatus" class="form-label">Status</label>
                                        <select class="form-select" id="editProductStatus" name="status">
                                            <option value="active">Active</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductCategory" class="form-label">Category</label>
                                        <select class="form-select" id="editProductCategory" name="category_id">
                                            <option value="">Select Category</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editProductVendor" class="form-label">Vendor</label>
                                        <select class="form-select" id="editProductVendor" name="vendor_id">
                                            <option value="">Select Vendor</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editProductDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editProductDescription" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="editProductImage" class="form-label">Product Image URL</label>
                                <input type="url" class="form-control" id="editProductImage" name="image_url">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="updateProduct()">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Transaction Modal -->
        <div class="modal fade" id="newTransactionModal" tabindex="-1" aria-labelledby="newTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newTransactionModalLabel">
                            <i class="fas fa-cash-register me-2"></i>New Sale Transaction
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Product Search and Cart -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Add Products to Cart</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="transactionProductSearch" placeholder="Search products by name, code, or barcode...">
                                            <button class="btn btn-outline-secondary" type="button" onclick="searchTransactionProducts()">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-success" type="button" onclick="openScannerForTransaction()">
                                                <i class="fas fa-qrcode"></i> Scan
                                            </button>
                                        </div>
                                        <div id="transactionProductResults"></div>
                                    </div>
                                </div>

                                <!-- Shopping Cart -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Shopping Cart</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Price</th>
                                                        <th>Qty</th>
                                                        <th>Total</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="transactionCart">
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Cart is empty</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Transaction Summary -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Transaction Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="transactionCustomer" class="form-label">Customer</label>
                                            <select class="form-select" id="transactionCustomer">
                                                <option value="">Walk-in Customer</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="transactionPaymentMethod" class="form-label">Payment Method</label>
                                            <select class="form-select" id="transactionPaymentMethod" required>
                                                <option value="cash">Cash</option>
                                                <option value="card">Card</option>
                                                <option value="digital">Digital Payment</option>
                                                <option value="check">Check</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="transactionDiscount" class="form-label">Discount ($)</label>
                                            <input type="number" class="form-control" id="transactionDiscount" step="0.01" min="0" value="0" onchange="calculateTransactionTotal()">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="transactionTax" class="form-label">Tax Rate (%)</label>
                                            <input type="number" class="form-control" id="transactionTax" step="0.01" min="0" value="10" onchange="calculateTransactionTotal()">
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="transactionSubtotal">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount:</span>
                                            <span id="transactionDiscountAmount">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tax:</span>
                                            <span id="transactionTaxAmount">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <strong id="transactionTotal">$0.00</strong>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="transactionNotes" class="form-label">Notes</label>
                                            <textarea class="form-control" id="transactionNotes" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="processTransaction()">
                            <i class="fas fa-credit-card me-2"></i>Process Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Alerts Modal -->
        <div class="modal fade" id="inventoryAlertsModal" tabindex="-1" aria-labelledby="inventoryAlertsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inventoryAlertsModalLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Inventory Alerts
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button class="btn btn-sm btn-outline-primary" onclick="loadInventoryAlerts()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick="markAllAlertsRead()">
                                    <i class="fas fa-check me-1"></i>Mark All Read
                                </button>
                            </div>
                            <div>
                                <select class="form-select form-select-sm" id="alertsFilter" onchange="loadInventoryAlerts()">
                                    <option value="">All Alerts</option>
                                    <option value="low_stock">Low Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="overstock">Overstock</option>
                                </select>
                            </div>
                        </div>
                        <div id="alertsList">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading alerts...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="settingsModalLabel">
                            <i class="fas fa-cog me-2"></i>System Settings
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Settings Navigation -->
                            <div class="col-md-3">
                                <div class="nav flex-column nav-pills" id="settings-tab" role="tablist" aria-orientation="vertical">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">
                                        <i class="fas fa-building me-2"></i>General
                                    </button>
                                    <button class="nav-link" id="pos-tab" data-bs-toggle="pill" data-bs-target="#pos" type="button" role="tab">
                                        <i class="fas fa-cash-register me-2"></i>POS Settings
                                    </button>
                                    <button class="nav-link" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory" type="button" role="tab">
                                        <i class="fas fa-warehouse me-2"></i>Inventory
                                    </button>
                                    <button class="nav-link" id="scanner-tab" data-bs-toggle="pill" data-bs-target="#scanner" type="button" role="tab">
                                        <i class="fas fa-qrcode me-2"></i>Scanner
                                    </button>
                                    <button class="nav-link" id="receipt-tab" data-bs-toggle="pill" data-bs-target="#receipt" type="button" role="tab">
                                        <i class="fas fa-receipt me-2"></i>Receipt
                                    </button>
                                    <button class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">
                                        <i class="fas fa-users me-2"></i>Users
                                    </button>
                                    <button class="nav-link" id="backup-tab" data-bs-toggle="pill" data-bs-target="#backup" type="button" role="tab">
                                        <i class="fas fa-database me-2"></i>Backup
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Settings Content -->
                            <div class="col-md-9">
                                <div class="tab-content" id="settings-tabContent">
                                    <!-- General Settings -->
                                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                                        <h6 class="mb-3">Company Information</h6>
                                        <form id="generalSettingsForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="companyName" class="form-label">Company Name</label>
                                                        <input type="text" class="form-control" id="companyName" name="company_name">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="companyEmail" class="form-label">Company Email</label>
                                                        <input type="email" class="form-control" id="companyEmail" name="company_email">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="companyPhone" class="form-label">Company Phone</label>
                                                        <input type="tel" class="form-control" id="companyPhone" name="company_phone">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="timezone" class="form-label">Timezone</label>
                                                        <select class="form-select" id="timezone" name="timezone">
                                                            <option value="America/New_York">Eastern Time</option>
                                                            <option value="America/Chicago">Central Time</option>
                                                            <option value="America/Denver">Mountain Time</option>
                                                            <option value="America/Los_Angeles">Pacific Time</option>
                                                            <option value="UTC">UTC</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="companyAddress" class="form-label">Company Address</label>
                                                <textarea class="form-control" id="companyAddress" name="company_address" rows="3"></textarea>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- POS Settings -->
                                    <div class="tab-pane fade" id="pos" role="tabpanel">
                                        <h6 class="mb-3">Point of Sale Configuration</h6>
                                        <form id="posSettingsForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="currency" class="form-label">Currency</label>
                                                        <select class="form-select" id="currency" name="currency">
                                                            <option value="USD">US Dollar (USD)</option>
                                                            <option value="EUR">Euro (EUR)</option>
                                                            <option value="GBP">British Pound (GBP)</option>
                                                            <option value="CAD">Canadian Dollar (CAD)</option>
                                                            <option value="AUD">Australian Dollar (AUD)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="currencySymbol" class="form-label">Currency Symbol</label>
                                                        <input type="text" class="form-control" id="currencySymbol" name="currency_symbol" maxlength="5">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="taxRate" class="form-label">Default Tax Rate (%)</label>
                                                        <input type="number" class="form-control" id="taxRate" name="tax_rate" step="0.01" min="0" max="100">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="dateFormat" class="form-label">Date Format</label>
                                                        <select class="form-select" id="dateFormat" name="date_format">
                                                            <option value="Y-m-d">YYYY-MM-DD</option>
                                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                                            <option value="d-m-Y">DD-MM-YYYY</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="timeFormat" class="form-label">Time Format</label>
                                                        <select class="form-select" id="timeFormat" name="time_format">
                                                            <option value="H:i:s">24 Hour (HH:MM:SS)</option>
                                                            <option value="h:i:s A">12 Hour (HH:MM:SS AM/PM)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Inventory Settings -->
                                    <div class="tab-pane fade" id="inventory" role="tabpanel">
                                        <h6 class="mb-3">Inventory Management</h6>
                                        <form id="inventorySettingsForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="lowStockThreshold" class="form-label">Default Low Stock Threshold</label>
                                                        <input type="number" class="form-control" id="lowStockThreshold" name="low_stock_threshold" min="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="autoReorderLevel" class="form-label">Auto Reorder Level</label>
                                                        <input type="number" class="form-control" id="autoReorderLevel" name="auto_reorder_level" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="enableLowStockAlerts" name="enable_low_stock_alerts">
                                                    <label class="form-check-label" for="enableLowStockAlerts">
                                                        Enable Low Stock Alerts
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="enableAutoReorder" name="enable_auto_reorder">
                                                    <label class="form-check-label" for="enableAutoReorder">
                                                        Enable Automatic Reordering
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="trackExpiry" name="track_expiry">
                                                    <label class="form-check-label" for="trackExpiry">
                                                        Track Product Expiry Dates
                                                    </label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Scanner Settings -->
                                    <div class="tab-pane fade" id="scanner" role="tabpanel">
                                        <h6 class="mb-3">Barcode Scanner Configuration</h6>
                                        <form id="scannerSettingsForm">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="scannerEnabled" name="scanner_enabled">
                                                    <label class="form-check-label" for="scannerEnabled">
                                                        Enable Barcode Scanner Integration
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="scannerSound" name="scanner_sound">
                                                    <label class="form-check-label" for="scannerSound">
                                                        Enable Scanner Sound Effects
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cameraScanning" name="camera_scanning">
                                                    <label class="form-check-label" for="cameraScanning">
                                                        Enable Camera Scanning
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="scannerTimeout" class="form-label">Scanner Timeout (ms)</label>
                                                        <input type="number" class="form-control" id="scannerTimeout" name="scanner_timeout" min="50" max="1000" value="100">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="minBarcodeLength" class="form-label">Minimum Barcode Length</label>
                                                        <input type="number" class="form-control" id="minBarcodeLength" name="min_barcode_length" min="4" max="20" value="8">
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Receipt Settings -->
                                    <div class="tab-pane fade" id="receipt" role="tabpanel">
                                        <h6 class="mb-3">Receipt Configuration</h6>
                                        <form id="receiptSettingsForm">
                                            <div class="mb-3">
                                                <label for="receiptHeader" class="form-label">Receipt Header</label>
                                                <textarea class="form-control" id="receiptHeader" name="receipt_header" rows="3" placeholder="Welcome to our store!"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="receiptFooter" class="form-label">Receipt Footer</label>
                                                <textarea class="form-control" id="receiptFooter" name="receipt_footer" rows="3" placeholder="Thank you for your business!"></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="printReceipt" name="print_receipt">
                                                            <label class="form-check-label" for="printReceipt">
                                                                Auto Print Receipt
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="emailReceipt" name="email_receipt">
                                                            <label class="form-check-label" for="emailReceipt">
                                                                Email Receipt Option
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Users Settings -->
                                    <div class="tab-pane fade" id="users" role="tabpanel">
                                        <h6 class="mb-3">User Management</h6>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div></div>
                                            <button class="btn btn-primary btn-sm" onclick="showAddUserModal()">
                                                <i class="fas fa-plus me-1"></i>Add User
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Username</th>
                                                        <th>Full Name</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Status</th>
                                                        <th>Last Login</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="usersTableBody">
                                                    <tr>
                                                        <td colspan="7" class="text-center">Loading users...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Backup Settings -->
                                    <div class="tab-pane fade" id="backup" role="tabpanel">
                                        <h6 class="mb-3">Database Backup & Maintenance</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Backup Options</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="d-grid gap-2">
                                                            <button class="btn btn-success" onclick="createBackup()">
                                                                <i class="fas fa-download me-2"></i>Create Backup
                                                            </button>
                                                            <button class="btn btn-info" onclick="scheduleBackup()">
                                                                <i class="fas fa-clock me-2"></i>Schedule Backup
                                                            </button>
                                                            <button class="btn btn-warning" onclick="optimizeDatabase()">
                                                                <i class="fas fa-tools me-2"></i>Optimize Database
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">System Information</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="systemInfo">
                                                            <p><strong>Database Size:</strong> <span id="dbSize">Loading...</span></p>
                                                            <p><strong>Total Products:</strong> <span id="totalProducts">Loading...</span></p>
                                                            <p><strong>Total Transactions:</strong> <span id="totalTransactions">Loading...</span></p>
                                                            <p><strong>Last Backup:</strong> <span id="lastBackup">Never</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                            <i class="fas fa-save me-2"></i>Save All Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/barcode-scanner.js"></script>
    <script src="assets/js/charts.js"></script>
    <script src="assets/js/settings.js"></script>
</body>
</html>