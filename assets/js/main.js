// Main JavaScript for POS System
class POSSystem {
    constructor() {
        this.currentProduct = null;
        this.currentSection = 'dashboard';
        this.products = [];
        this.transactions = [];
        this.customers = [];
        this.vendors = [];
        this.categories = [];
        this.alerts = [];
        this.transactionCart = [];
        
        this.init();
    }

    init() {
        console.log('Initializing POS System...');
        this.setupEventListeners();
        this.loadInitialData();
        this.startAutoRefresh();
    }

    setupEventListeners() {
        console.log('Setting up event listeners...');
        
        // Navigation - Use direct event listeners instead of onclick attributes
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const href = link.getAttribute('href');
                if (href && href.includes('showSection')) {
                    const section = href.match(/showSection\('(.+)'\)/)?.[1];
                    if (section) {
                        this.showSection(section);
                    }
                }
            });
        });

        // Search functionality
        const productSearch = document.getElementById('product-search');
        if (productSearch) {
            productSearch.addEventListener('input', (e) => {
                this.searchProducts(e.target.value);
            });
        }

        const searchBtn = document.getElementById('search-btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                const query = document.getElementById('product-search')?.value || '';
                this.searchProducts(query);
            });
        }

        // Scan button
        const scanBtn = document.getElementById('scan-btn');
        if (scanBtn) {
            scanBtn.addEventListener('click', () => {
                this.startBarcodeScanning();
            });
        }

        // Alerts button
        const alertsBtn = document.getElementById('alerts-btn');
        if (alertsBtn) {
            alertsBtn.addEventListener('click', () => {
                this.showAlerts();
            });
        }

        // Quick action buttons
        this.setupQuickActionButtons();

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });
    }

    setupQuickActionButtons() {
        // Add Product button
        document.querySelectorAll('[onclick*="showAddProductModal"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.showAddProductModal());
        });

        // New Sale button
        document.querySelectorAll('[onclick*="showNewTransactionModal"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.showNewTransactionModal());
        });

        // Stock Alerts button
        document.querySelectorAll('[onclick*="showInventoryAlerts"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.showInventoryAlerts());
        });

        // Export Data button
        document.querySelectorAll('[onclick*="exportData"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.exportData());
        });

        // Refresh Dashboard button
        document.querySelectorAll('[onclick*="refreshDashboard"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.refreshDashboard());
        });

        // Settings button
        document.querySelectorAll('[onclick*="showSettings"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.showSettings());
        });

        // Logout button
        document.querySelectorAll('[onclick*="logout"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.logout());
        });

        // Save All Settings button
        document.querySelectorAll('[onclick*="saveAllSettings"]').forEach(btn => {
            btn.removeAttribute('onclick');
            btn.addEventListener('click', () => this.saveAllSettings());
        });
    }

    async loadInitialData() {
        console.log('Loading initial data...');
        try {
            await Promise.all([
                this.loadProducts(),
                this.loadCategories(),
                this.loadCustomers(),
                this.loadVendors(),
                this.loadTransactions(),
                this.loadAlerts(),
                this.loadDashboardStats()
            ]);
            
            this.showNotification('System loaded successfully', 'success');
            console.log('Initial data loaded successfully');
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showNotification('Error loading system data', 'error');
        }
    }

    async loadProducts() {
        try {
            const response = await fetch('api/products.php');
            const data = await response.json();
            
            if (data.success) {
                this.products = data.products || [];
                this.updateProductsTable();
                console.log(`Loaded ${this.products.length} products`);
            } else {
                console.error('Failed to load products:', data.message);
            }
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    async loadCategories() {
        try {
            const response = await fetch('api/categories.php');
            const data = await response.json();
            
            if (data.success) {
                this.categories = data.categories || [];
                console.log(`Loaded ${this.categories.length} categories`);
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadCustomers() {
        try {
            const response = await fetch('api/customers.php');
            const data = await response.json();
            
            if (data.success) {
                this.customers = data.customers || [];
                console.log(`Loaded ${this.customers.length} customers`);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    async loadVendors() {
        try {
            const response = await fetch('api/vendors.php');
            const data = await response.json();
            
            if (data.success) {
                this.vendors = data.vendors || [];
                console.log(`Loaded ${this.vendors.length} vendors`);
            }
        } catch (error) {
            console.error('Error loading vendors:', error);
        }
    }

    async loadTransactions() {
        try {
            const response = await fetch('api/transactions.php');
            const data = await response.json();
            
            if (data.success) {
                this.transactions = data.transactions || [];
                this.updateRecentTransactions();
                console.log(`Loaded ${this.transactions.length} transactions`);
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
        }
    }

    async loadAlerts() {
        try {
            const response = await fetch('api/alerts.php');
            const data = await response.json();
            
            if (data.success) {
                this.alerts = data.alerts || [];
                this.updateAlertCount();
                console.log(`Loaded ${this.alerts.length} alerts`);
            }
        } catch (error) {
            console.error('Error loading alerts:', error);
        }
    }

    async loadDashboardStats() {
        try {
            const response = await fetch('api/dashboard-stats.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDashboardStats(data.stats);
                console.log('Dashboard stats loaded');
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    showSection(sectionName) {
        console.log(`Showing section: ${sectionName}`);
        
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show selected section
        const targetSection = document.getElementById(`${sectionName}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
            targetSection.classList.add('fade-in');
        }

        // Update navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });

        const activeLink = document.querySelector(`.sidebar .nav-link[href*="showSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;
    }

    updateProductsTable() {
        const tbody = document.getElementById('products-tbody');
        if (!tbody) return;

        if (this.products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No products found</td></tr>';
            return;
        }

        tbody.innerHTML = this.products.map(product => `
            <tr>
                <td><input type="checkbox" class="product-checkbox" value="${product.id}"></td>
                <td>
                    ${product.image_url ? 
                        `<img src="${product.image_url}" class="product-image" alt="${product.name}">` :
                        `<div class="product-image-placeholder"><i class="fas fa-image"></i></div>`
                    }
                </td>
                <td>
                    <strong>${product.name}</strong>
                    <br><small class="text-muted">${product.description || 'No description'}</small>
                </td>
                <td><span class="badge bg-secondary">${product.product_code}</span></td>
                <td><span class="barcode-display">${product.barcode}</span></td>
                <td><strong>$${parseFloat(product.price).toFixed(2)}</strong></td>
                <td><span class="${this.getStockLevelClass(product.stock_quantity, product.min_stock_level)}">${product.stock_quantity}</span></td>
                <td><span class="badge status-${product.status}">${product.status.charAt(0).toUpperCase() + product.status.slice(1)}</span></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" data-action="edit" data-id="${product.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-warning" data-action="archive" data-id="${product.id}" title="Archive">
                            <i class="fas fa-archive"></i>
                        </button>
                        <button class="btn btn-outline-danger" data-action="delete" data-id="${product.id}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // Add event listeners to action buttons
        tbody.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.target.closest('button').dataset.action;
                const id = e.target.closest('button').dataset.id;
                
                switch (action) {
                    case 'edit':
                        this.editProduct(id);
                        break;
                    case 'archive':
                        this.archiveProduct(id);
                        break;
                    case 'delete':
                        this.deleteProduct(id);
                        break;
                }
            });
        });
    }

    getStockLevelClass(stock, minLevel) {
        if (stock === 0) return 'stock-out';
        if (stock <= minLevel) return 'stock-low';
        if (stock <= minLevel * 2) return 'stock-medium';
        return 'stock-high';
    }

    updateDashboardStats(stats) {
        const elements = {
            'total-products': stats.total_products || 0,
            'today-sales': `$${parseFloat(stats.today_sales || 0).toFixed(2)}`,
            'low-stock-count': stats.low_stock_count || 0,
            'total-customers': stats.total_customers || 0
        };

        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    updateRecentTransactions() {
        const tbody = document.getElementById('recent-transactions');
        if (!tbody) return;

        if (this.transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No recent transactions</td></tr>';
            return;
        }

        const recentTransactions = this.transactions.slice(0, 5);
        tbody.innerHTML = recentTransactions.map(transaction => `
            <tr>
                <td>${transaction.transaction_number}</td>
                <td>$${parseFloat(transaction.total_amount).toFixed(2)}</td>
                <td>${new Date(transaction.transaction_date).toLocaleTimeString()}</td>
            </tr>
        `).join('');
    }

    updateAlertCount() {
        const alertCount = document.getElementById('alert-count');
        if (alertCount) {
            const unreadAlerts = this.alerts.filter(alert => !alert.is_read).length;
            alertCount.textContent = unreadAlerts;
            alertCount.style.display = unreadAlerts > 0 ? 'inline' : 'none';
        }
    }

    showNotification(message, type = 'info') {
        console.log(`Notification: ${message} (${type})`);
        
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '1055';
            document.body.appendChild(toastContainer);
        }
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        
        // Show toast using Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        } else {
            // Fallback if Bootstrap is not loaded
            toast.style.display = 'block';
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    }

    handleKeyboardShortcuts(e) {
        // Ctrl+S for scan
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            this.startBarcodeScanning();
        }
        
        // Ctrl+F for search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('product-search');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getInstance(openModal)?.hide();
            }
        }
    }

    startAutoRefresh() {
        // Refresh dashboard stats every 30 seconds
        setInterval(() => {
            if (this.currentSection === 'dashboard') {
                this.loadDashboardStats();
            }
        }, 30000);

        // Check for new alerts every 60 seconds
        setInterval(() => {
            this.loadAlerts();
        }, 60000);
    }

    // Product management methods
    async editProduct(productId) {
        console.log(`Editing product: ${productId}`);
        this.showNotification('Edit product functionality coming soon', 'info');
    }

    async archiveProduct(productId) {
        if (confirm('Are you sure you want to archive this product?')) {
            console.log(`Archiving product: ${productId}`);
            this.showNotification('Archive product functionality coming soon', 'info');
        }
    }

    async deleteProduct(productId) {
        if (confirm('Are you sure you want to permanently delete this product? This action cannot be undone.')) {
            console.log(`Deleting product: ${productId}`);
            this.showNotification('Delete product functionality coming soon', 'info');
        }
    }

    showAddProductModal() {
        console.log('Showing add product modal');
        const modal = document.getElementById('addProductModal');
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            this.showNotification('Add product modal not found', 'error');
        }
    }

    showNewTransactionModal() {
        console.log('Showing new transaction modal');
        const modal = document.getElementById('newTransactionModal');
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            this.showNotification('New transaction modal not found', 'error');
        }
    }

    showInventoryAlerts() {
        console.log('Showing inventory alerts');
        const modal = document.getElementById('inventoryAlertsModal');
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            this.showNotification('Inventory alerts modal not found', 'error');
        }
    }

    showAlerts() {
        this.showInventoryAlerts();
    }

    async exportData() {
        console.log('Exporting data');
        this.showNotification('Data export functionality coming soon', 'info');
    }

    showSettings() {
        console.log('Showing settings');
        const modal = document.getElementById('settingsModal');
        if (modal && typeof bootstrap !== 'undefined') {
            // Load settings when modal is shown
            this.loadSettings();
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        } else {
            this.showNotification('Settings modal not found', 'error');
        }
    }

    refreshDashboard() {
        console.log('Refreshing dashboard');
        this.loadDashboardStats();
        this.loadTransactions();
        this.showNotification('Dashboard refreshed', 'success');
    }

    async searchProducts(query) {
        console.log(`Searching products: ${query}`);
        if (!query.trim()) {
            this.updateProductsTable();
            return;
        }
        // Search functionality to be implemented
        this.showNotification('Search functionality coming soon', 'info');
    }

    startBarcodeScanning() {
        console.log('Starting barcode scanning');
        this.showNotification('Barcode scanning functionality coming soon', 'info');
    }

    logout() {
        if (confirm('Are you sure you want to logout?')) {
            console.log('Logging out');
            // For now, just reload the page
            window.location.reload();
        }
    }

    // Settings management methods
    async saveAllSettings() {
        console.log('Saving all settings...');
        
        try {
            // Validate settings first
            const validationErrors = this.validateSettings();
            if (validationErrors.length > 0) {
                this.showNotification('Please fix the following errors:\n' + validationErrors.join('\n'), 'error');
                return;
            }

            const settings = this.collectAllSettings();
            
            const response = await fetch('api/settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ settings: settings })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Settings saved successfully!', 'success');
                // Close the settings modal
                const settingsModal = document.getElementById('settingsModal');
                if (settingsModal && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(settingsModal)?.hide();
                }
            } else {
                this.showNotification('Error saving settings: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error saving settings:', error);
            this.showNotification('Error saving settings: ' + error.message, 'error');
        }
    }

    collectAllSettings() {
        const settings = {};
        
        // General settings
        settings.company_name = document.getElementById('companyName')?.value || '';
        settings.company_email = document.getElementById('companyEmail')?.value || '';
        settings.company_phone = document.getElementById('companyPhone')?.value || '';
        settings.company_address = document.getElementById('companyAddress')?.value || '';
        settings.timezone = document.getElementById('timezone')?.value || 'America/New_York';

        // POS settings
        settings.currency = document.getElementById('currency')?.value || 'USD';
        settings.currency_symbol = document.getElementById('currencySymbol')?.value || '$';
        settings.tax_rate = document.getElementById('taxRate')?.value || '10';
        settings.date_format = document.getElementById('dateFormat')?.value || 'Y-m-d';
        settings.time_format = document.getElementById('timeFormat')?.value || 'H:i:s';

        // Inventory settings
        settings.low_stock_threshold = document.getElementById('lowStockThreshold')?.value || '10';
        settings.auto_reorder_level = document.getElementById('autoReorderLevel')?.value || '5';
        settings.enable_low_stock_alerts = document.getElementById('enableLowStockAlerts')?.checked ? '1' : '0';
        settings.enable_auto_reorder = document.getElementById('enableAutoReorder')?.checked ? '1' : '0';
        settings.track_expiry = document.getElementById('trackExpiry')?.checked ? '1' : '0';

        // Scanner settings
        settings.scanner_enabled = document.getElementById('scannerEnabled')?.checked ? '1' : '0';
        settings.scanner_sound = document.getElementById('scannerSound')?.checked ? '1' : '0';
        settings.camera_scanning = document.getElementById('cameraScanning')?.checked ? '1' : '0';
        settings.scanner_timeout = document.getElementById('scannerTimeout')?.value || '100';
        settings.min_barcode_length = document.getElementById('minBarcodeLength')?.value || '8';

        // Receipt settings
        settings.receipt_header = document.getElementById('receiptHeader')?.value || '';
        settings.receipt_footer = document.getElementById('receiptFooter')?.value || 'Thank you for your business!';
        settings.print_receipt = document.getElementById('printReceipt')?.checked ? '1' : '0';
        settings.email_receipt = document.getElementById('emailReceipt')?.checked ? '1' : '0';

        return settings;
    }

    validateSettings() {
        const errors = [];
        
        // Validate company name
        const companyName = document.getElementById('companyName')?.value;
        if (!companyName || companyName.trim().length < 2) {
            errors.push('Company name must be at least 2 characters long');
        }
        
        // Validate email format if provided
        const companyEmail = document.getElementById('companyEmail')?.value;
        if (companyEmail && !this.isValidEmail(companyEmail)) {
            errors.push('Please enter a valid company email address');
        }
        
        // Validate tax rate
        const taxRate = parseFloat(document.getElementById('taxRate')?.value);
        if (isNaN(taxRate) || taxRate < 0 || taxRate > 100) {
            errors.push('Tax rate must be between 0 and 100');
        }
        
        // Validate low stock threshold
        const lowStockThreshold = parseInt(document.getElementById('lowStockThreshold')?.value);
        if (isNaN(lowStockThreshold) || lowStockThreshold < 0) {
            errors.push('Low stock threshold must be a positive number');
        }
        
        return errors;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    async loadSettings() {
        try {
            const response = await fetch('api/settings.php');
            const data = await response.json();
            
            if (data.success) {
                this.populateSettingsForm(data.settings);
                console.log('Settings loaded successfully');
            } else {
                this.showNotification('Error loading settings: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            this.showNotification('Error loading settings', 'error');
        }
    }

    populateSettingsForm(settings) {
        // General settings
        this.setFormValue('companyName', settings.company_name?.value);
        this.setFormValue('companyEmail', settings.company_email?.value);
        this.setFormValue('companyPhone', settings.company_phone?.value);
        this.setFormValue('companyAddress', settings.company_address?.value);
        this.setFormValue('timezone', settings.timezone?.value);

        // POS settings
        this.setFormValue('currency', settings.currency?.value);
        this.setFormValue('currencySymbol', settings.currency_symbol?.value);
        this.setFormValue('taxRate', settings.tax_rate?.value);
        this.setFormValue('dateFormat', settings.date_format?.value);
        this.setFormValue('timeFormat', settings.time_format?.value);

        // Inventory settings
        this.setFormValue('lowStockThreshold', settings.low_stock_threshold?.value);
        this.setFormValue('autoReorderLevel', settings.auto_reorder_level?.value);
        this.setCheckboxValue('enableLowStockAlerts', settings.enable_low_stock_alerts?.value);
        this.setCheckboxValue('enableAutoReorder', settings.enable_auto_reorder?.value);
        this.setCheckboxValue('trackExpiry', settings.track_expiry?.value);

        // Scanner settings
        this.setCheckboxValue('scannerEnabled', settings.scanner_enabled?.value);
        this.setCheckboxValue('scannerSound', settings.scanner_sound?.value);
        this.setCheckboxValue('cameraScanning', settings.camera_scanning?.value);
        this.setFormValue('scannerTimeout', settings.scanner_timeout?.value);
        this.setFormValue('minBarcodeLength', settings.min_barcode_length?.value);

        // Receipt settings
        this.setFormValue('receiptHeader', settings.receipt_header?.value);
        this.setFormValue('receiptFooter', settings.receipt_footer?.value);
        this.setCheckboxValue('printReceipt', settings.print_receipt?.value);
        this.setCheckboxValue('emailReceipt', settings.email_receipt?.value);
    }

    setFormValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element && value !== undefined) {
            element.value = value;
        }
    }

    setCheckboxValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element && value !== undefined) {
            element.checked = value === '1' || value === true;
        }
    }
}

// Global functions for backward compatibility
function showSection(sectionName) {
    if (window.posSystem) {
        window.posSystem.showSection(sectionName);
    }
}

function refreshDashboard() {
    if (window.posSystem) {
        window.posSystem.refreshDashboard();
    }
}

function showSettings() {
    if (window.posSystem) {
        window.posSystem.showSettings();
    }
}

function logout() {
    if (window.posSystem) {
        window.posSystem.logout();
    }
}

function showAddProductModal() {
    if (window.posSystem) {
        window.posSystem.showAddProductModal();
    }
}

function showNewTransactionModal() {
    if (window.posSystem) {
        window.posSystem.showNewTransactionModal();
    }
}

function showInventoryAlerts() {
    if (window.posSystem) {
        window.posSystem.showInventoryAlerts();
    }
}

function exportData() {
    if (window.posSystem) {
        window.posSystem.exportData();
    }
}

function saveAllSettings() {
    if (window.posSystem) {
        window.posSystem.saveAllSettings();
    }
}

// Initialize the POS system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing POS System...');
    try {
        window.posSystem = new POSSystem();
        console.log('POS System initialized successfully');
    } catch (error) {
        console.error('Error initializing POS System:', error);
    }
});

// Error handling for uncaught errors
window.addEventListener('error', (e) => {
    console.error('JavaScript Error:', e.error);
});

console.log('POS System JavaScript loaded');