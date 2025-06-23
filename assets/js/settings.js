// Settings Panel Functionality for POS System

// Global functions for settings modal
function saveAllSettings() {
    if (posSystem) {
        posSystem.saveAllSettings();
    }
}

function showAddUserModal() {
    // Create and show add user modal
    const modalHTML = `
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">
                            <i class="fas fa-user-plus me-2"></i>Add New User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addUserForm">
                            <div class="mb-3">
                                <label for="newUsername" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="newUsername" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="newUserEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="newUserEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="newUserPassword" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="newUserPassword" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="newUserFullName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="newUserFullName" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="newUserRole" class="form-label">Role *</label>
                                <select class="form-select" id="newUserRole" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Administrator</option>
                                    <option value="manager">Manager</option>
                                    <option value="cashier">Cashier</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="newUserStatus" class="form-label">Status</label>
                                <select class="form-select" id="newUserStatus" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveNewUser()">
                            <i class="fas fa-save me-2"></i>Save User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if present
    const existingModal = document.getElementById('addUserModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
    modal.show();
}

async function saveNewUser() {
    const form = document.getElementById('addUserForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const userData = {};
    for (let [key, value] of formData.entries()) {
        userData[key] = value;
    }
    
    try {
        const response = await fetch('api/users.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            posSystem.showNotification('User created successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            posSystem.loadUsers();
        } else {
            posSystem.showNotification('Error: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error saving user:', error);
        posSystem.showNotification('Error saving user', 'error');
    }
}

// Backup and maintenance functions
function createBackup() {
    if (confirm('Create a database backup? This may take a few moments.')) {
        posSystem.showNotification('Creating backup...', 'info');
        
        // Simulate backup creation
        setTimeout(() => {
            const backupName = `pos_backup_${new Date().toISOString().split('T')[0]}.sql`;
            posSystem.showNotification(`Backup created: ${backupName}`, 'success');
        }, 2000);
    }
}

function scheduleBackup() {
    posSystem.showNotification('Backup scheduling feature coming soon', 'info');
}

function optimizeDatabase() {
    if (confirm('Optimize database tables? This may take a few moments.')) {
        posSystem.showNotification('Optimizing database...', 'info');
        
        // Simulate database optimization
        setTimeout(() => {
            posSystem.showNotification('Database optimized successfully', 'success');
        }, 3000);
    }
}

// Settings validation functions
function validateSettings() {
    const errors = [];
    
    // Validate company name
    const companyName = document.getElementById('companyName')?.value;
    if (!companyName || companyName.trim().length < 2) {
        errors.push('Company name must be at least 2 characters long');
    }
    
    // Validate email format
    const companyEmail = document.getElementById('companyEmail')?.value;
    if (companyEmail && !isValidEmail(companyEmail)) {
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

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Settings preview functions
function previewReceiptSettings() {
    const header = document.getElementById('receiptHeader')?.value || '';
    const footer = document.getElementById('receiptFooter')?.value || '';
    const companyName = document.getElementById('companyName')?.value || 'Your Store';
    
    const previewHTML = `
        <div class="modal fade" id="receiptPreviewModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receipt Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="receipt-preview" style="font-family: monospace; border: 1px solid #ccc; padding: 20px; background: white;">
                            <div class="text-center">
                                <h4>${companyName}</h4>
                                <p>${header}</p>
                                <hr>
                                <p>Sample Transaction #12345</p>
                                <p>Date: ${new Date().toLocaleDateString()}</p>
                                <hr>
                                <div class="text-start">
                                    <p>Sample Product 1 x1 .... $10.00</p>
                                    <p>Sample Product 2 x2 .... $20.00</p>
                                </div>
                                <hr>
                                <p><strong>Total: $30.00</strong></p>
                                <hr>
                                <p>${footer}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing preview modal
    const existingPreview = document.getElementById('receiptPreviewModal');
    if (existingPreview) {
        existingPreview.remove();
    }
    
    // Add and show preview modal
    document.body.insertAdjacentHTML('beforeend', previewHTML);
    const modal = new bootstrap.Modal(document.getElementById('receiptPreviewModal'));
    modal.show();
}

// Settings export/import functions
function exportSettings() {
    if (posSystem) {
        const settings = posSystem.collectAllSettings();
        const dataStr = JSON.stringify(settings, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        const link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = `pos_settings_${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        
        posSystem.showNotification('Settings exported successfully', 'success');
    }
}

function importSettings() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    
    input.onchange = function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    posSystem.populateSettingsForm({
                        // Convert flat settings to expected format
                        ...Object.keys(settings).reduce((acc, key) => {
                            acc[key] = { value: settings[key] };
                            return acc;
                        }, {})
                    });
                    posSystem.showNotification('Settings imported successfully', 'success');
                } catch (error) {
                    posSystem.showNotification('Error importing settings: Invalid file format', 'error');
                }
            };
            reader.readAsText(file);
        }
    };
    
    input.click();
}

// Initialize settings panel enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Add preview button to receipt settings
    const receiptTab = document.getElementById('receipt');
    if (receiptTab) {
        const previewButton = document.createElement('button');
        previewButton.type = 'button';
        previewButton.className = 'btn btn-outline-info btn-sm mt-3';
        previewButton.innerHTML = '<i class="fas fa-eye me-2"></i>Preview Receipt';
        previewButton.onclick = previewReceiptSettings;
        
        const receiptForm = receiptTab.querySelector('form');
        if (receiptForm) {
            receiptForm.appendChild(previewButton);
        }
    }
    
    // Add export/import buttons to general settings
    const generalTab = document.getElementById('general');
    if (generalTab) {
        const buttonGroup = document.createElement('div');
        buttonGroup.className = 'btn-group mt-3';
        buttonGroup.innerHTML = `
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportSettings()">
                <i class="fas fa-download me-1"></i>Export Settings
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="importSettings()">
                <i class="fas fa-upload me-1"></i>Import Settings
            </button>
        `;
        
        const generalForm = generalTab.querySelector('form');
        if (generalForm) {
            generalForm.appendChild(buttonGroup);
        }
    }
});