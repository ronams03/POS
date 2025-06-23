<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .admin-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 2rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        .sql-output {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
        }
        .table-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 1.5rem;
            background-color: #fff5f5;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        .upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-database me-3"></i>Database Manager</h1>
                    <p class="mb-0">Manage your POS system database, upload SQL files, and perform maintenance tasks</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Database Status -->
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i>Database Status
                    </div>
                    <div class="card-body">
                        <div id="db-status">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Checking database status...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-upload me-2"></i>Upload SQL File
                    </div>
                    <div class="card-body">
                        <div class="upload-area" id="upload-area">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Drop SQL file here or click to browse</h5>
                            <p class="text-muted">Supports .sql files up to 50MB</p>
                            <input type="file" id="sql-file" accept=".sql" style="display: none;">
                            <button class="btn btn-primary" onclick="document.getElementById('sql-file').click()">
                                <i class="fas fa-folder-open me-2"></i>Browse Files
                            </button>
                        </div>
                        
                        <div id="file-info" style="display: none;" class="mt-3">
                            <div class="alert alert-info">
                                <strong>Selected File:</strong> <span id="file-name"></span><br>
                                <strong>Size:</strong> <span id="file-size"></span><br>
                                <strong>Type:</strong> <span id="file-type"></span>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button class="btn btn-success" onclick="uploadSQLFile()">
                                    <i class="fas fa-upload me-2"></i>Upload & Execute
                                </button>
                                <button class="btn btn-secondary" onclick="clearFileSelection()">
                                    <i class="fas fa-times me-2"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-tools me-2"></i>Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="downloadSQLFile()">
                                <i class="fas fa-download me-2"></i>Download Database SQL
                            </button>
                            <button class="btn btn-info" onclick="runDatabaseSetup()">
                                <i class="fas fa-cogs me-2"></i>Run Database Setup
                            </button>
                            <button class="btn btn-warning" onclick="optimizeDatabase()">
                                <i class="fas fa-tachometer-alt me-2"></i>Optimize Database
                            </button>
                            <button class="btn btn-success" onclick="backupDatabase()">
                                <i class="fas fa-save me-2"></i>Create Backup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-table me-2"></i>Database Tables
                    </div>
                    <div class="card-body">
                        <div id="tables-list">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading tables...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Executor -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-terminal me-2"></i>SQL Query Executor
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="sql-query" class="form-label">SQL Query:</label>
                            <textarea class="form-control" id="sql-query" rows="6" placeholder="Enter your SQL query here..."></textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button class="btn btn-primary" onclick="executeSQLQuery()">
                                <i class="fas fa-play me-2"></i>Execute Query
                            </button>
                            <button class="btn btn-secondary" onclick="clearSQLQuery()">
                                <i class="fas fa-eraser me-2"></i>Clear
                            </button>
                        </div>
                        
                        <div id="sql-result" style="display: none;" class="mt-3">
                            <h6>Query Result:</h6>
                            <div class="sql-output" id="sql-output"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="row">
            <div class="col-12">
                <div class="danger-zone">
                    <h5 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                    <p class="text-muted">These actions are irreversible. Please proceed with caution.</p>
                    <div class="d-grid gap-2 d-md-flex">
                        <button class="btn btn-outline-danger" onclick="resetDatabase()">
                            <i class="fas fa-redo me-2"></i>Reset Database
                        </button>
                        <button class="btn btn-outline-danger" onclick="dropAllTables()">
                            <i class="fas fa-trash me-2"></i>Drop All Tables
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            checkDatabaseStatus();
            loadTablesList();
            setupFileUpload();
        });

        // File upload setup
        function setupFileUpload() {
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('sql-file');

            // Drag and drop events
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0]);
                }
            });

            // File input change
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleFileSelection(e.target.files[0]);
                }
            });
        }

        function handleFileSelection(file) {
            if (!file.name.toLowerCase().endsWith('.sql')) {
                alert('Please select a valid SQL file (.sql extension)');
                return;
            }

            if (file.size > 50 * 1024 * 1024) { // 50MB limit
                alert('File size exceeds 50MB limit');
                return;
            }

            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = formatFileSize(file.size);
            document.getElementById('file-type').textContent = file.type || 'application/sql';
            document.getElementById('file-info').style.display = 'block';
        }

        function clearFileSelection() {
            document.getElementById('sql-file').value = '';
            document.getElementById('file-info').style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        async function checkDatabaseStatus() {
            try {
                const response = await fetch('../api/database-status.php');
                const data = await response.json();
                
                let statusHtml = '';
                if (data.success) {
                    statusHtml = `
                        <div class="text-success mb-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <h6 class="mt-2">Database Connected</h6>
                        </div>
                        <ul class="list-unstyled">
                            <li><strong>Database:</strong> ${data.database}</li>
                            <li><strong>Tables:</strong> ${data.table_count}</li>
                            <li><strong>Version:</strong> ${data.version}</li>
                            <li><strong>Size:</strong> ${data.size}</li>
                        </ul>
                    `;
                } else {
                    statusHtml = `
                        <div class="text-danger mb-3">
                            <i class="fas fa-times-circle fa-2x"></i>
                            <h6 class="mt-2">Database Error</h6>
                        </div>
                        <p class="text-muted">${data.message}</p>
                    `;
                }
                
                document.getElementById('db-status').innerHTML = statusHtml;
            } catch (error) {
                document.getElementById('db-status').innerHTML = `
                    <div class="text-warning mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                        <h6 class="mt-2">Connection Error</h6>
                    </div>
                    <p class="text-muted">Unable to connect to database</p>
                `;
            }
        }

        async function loadTablesList() {
            try {
                const response = await fetch('../api/database-tables.php');
                const data = await response.json();
                
                let tablesHtml = '';
                if (data.success && data.tables.length > 0) {
                    tablesHtml = '<div class="list-group list-group-flush">';
                    data.tables.forEach(table => {
                        tablesHtml += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-table me-2"></i>${table.name}</span>
                                <span class="badge bg-primary rounded-pill">${table.rows}</span>
                            </div>
                        `;
                    });
                    tablesHtml += '</div>';
                } else {
                    tablesHtml = '<p class="text-muted">No tables found</p>';
                }
                
                document.getElementById('tables-list').innerHTML = tablesHtml;
            } catch (error) {
                document.getElementById('tables-list').innerHTML = '<p class="text-danger">Error loading tables</p>';
            }
        }

        async function uploadSQLFile() {
            const fileInput = document.getElementById('sql-file');
            if (!fileInput.files.length) {
                alert('Please select a SQL file first');
                return;
            }

            const formData = new FormData();
            formData.append('sql_file', fileInput.files[0]);

            try {
                showLoading('Uploading and executing SQL file...');
                
                const response = await fetch('../api/upload-sql.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    showResult('SQL file executed successfully!', 'success');
                    clearFileSelection();
                    checkDatabaseStatus();
                    loadTablesList();
                } else {
                    showResult('Error: ' + data.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showResult('Upload failed: ' + error.message, 'error');
            }
        }

        async function executeSQLQuery() {
            const query = document.getElementById('sql-query').value.trim();
            if (!query) {
                alert('Please enter a SQL query');
                return;
            }

            try {
                const response = await fetch('../api/execute-sql.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ query: query })
                });
                
                const data = await response.json();
                
                document.getElementById('sql-result').style.display = 'block';
                
                if (data.success) {
                    let output = 'Query executed successfully!\n\n';
                    if (data.result && data.result.length > 0) {
                        output += 'Results:\n';
                        output += JSON.stringify(data.result, null, 2);
                    } else {
                        output += 'Query executed. Rows affected: ' + (data.affected_rows || 0);
                    }
                    document.getElementById('sql-output').textContent = output;
                } else {
                    document.getElementById('sql-output').textContent = 'Error: ' + data.message;
                }
            } catch (error) {
                document.getElementById('sql-result').style.display = 'block';
                document.getElementById('sql-output').textContent = 'Error: ' + error.message;
            }
        }

        function clearSQLQuery() {
            document.getElementById('sql-query').value = '';
            document.getElementById('sql-result').style.display = 'none';
        }

        function downloadSQLFile() {
            window.open('../database/pos_system.sql', '_blank');
        }

        async function runDatabaseSetup() {
            if (confirm('This will run the database setup. Continue?')) {
                try {
                    showLoading('Running database setup...');
                    const response = await fetch('../config/init_database.php');
                    const result = await response.text();
                    hideLoading();
                    
                    showResult('Database setup completed!', 'success');
                    checkDatabaseStatus();
                    loadTablesList();
                } catch (error) {
                    hideLoading();
                    showResult('Setup failed: ' + error.message, 'error');
                }
            }
        }

        async function optimizeDatabase() {
            if (confirm('This will optimize all database tables. Continue?')) {
                try {
                    showLoading('Optimizing database...');
                    const response = await fetch('../api/optimize-database.php', { method: 'POST' });
                    const data = await response.json();
                    hideLoading();
                    
                    if (data.success) {
                        showResult('Database optimized successfully!', 'success');
                    } else {
                        showResult('Optimization failed: ' + data.message, 'error');
                    }
                } catch (error) {
                    hideLoading();
                    showResult('Optimization failed: ' + error.message, 'error');
                }
            }
        }

        async function backupDatabase() {
            try {
                showLoading('Creating database backup...');
                const response = await fetch('../api/backup-database.php', { method: 'POST' });
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    showResult('Backup created successfully!', 'success');
                    // Optionally download the backup file
                    if (data.backup_file) {
                        window.open(data.backup_file, '_blank');
                    }
                } else {
                    showResult('Backup failed: ' + data.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showResult('Backup failed: ' + error.message, 'error');
            }
        }

        function resetDatabase() {
            if (confirm('WARNING: This will reset the entire database and all data will be lost! Are you sure?')) {
                if (confirm('This action cannot be undone. Type "RESET" to confirm:') && 
                    prompt('Type "RESET" to confirm:') === 'RESET') {
                    // Implement reset functionality
                    runDatabaseSetup();
                }
            }
        }

        function dropAllTables() {
            if (confirm('WARNING: This will drop all tables and all data will be lost! Are you sure?')) {
                if (prompt('Type "DROP ALL TABLES" to confirm:') === 'DROP ALL TABLES') {
                    // Implement drop tables functionality
                    alert('This feature is disabled for safety. Use SQL executor if needed.');
                }
            }
        }

        function showLoading(message) {
            // Create loading overlay
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.innerHTML = `
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>${message}</p>
                    </div>
                </div>
            `;
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                z-index: 9999;
                color: white;
            `;
            document.body.appendChild(overlay);
        }

        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        }

        function showResult(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.container').insertBefore(alert, document.querySelector('.container').firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>