<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .setup-body {
            padding: 2rem;
        }
        .step {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .step.completed {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .step.error {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .step.processing {
            border-color: #007bff;
            background-color: #d1ecf1;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        .step.completed .step-icon {
            background-color: #28a745;
            color: white;
        }
        .step.error .step-icon {
            background-color: #dc3545;
            color: white;
        }
        .step.processing .step-icon {
            background-color: #007bff;
            color: white;
        }
        .step-content h6 {
            margin: 0;
            font-weight: 600;
        }
        .step-content p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .log-output {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="setup-card">
                    <div class="setup-header">
                        <h2><i class="fas fa-cogs me-2"></i>POS System Setup</h2>
                        <p class="mb-0">Initialize your Point of Sale system database and configuration</p>
                    </div>
                    <div class="setup-body">
                        <div id="setup-steps">
                            <div class="step" id="step-1">
                                <div class="step-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="step-content">
                                    <h6>Database Connection</h6>
                                    <p>Testing database connection and creating database if needed</p>
                                </div>
                            </div>
                            
                            <div class="step" id="step-2">
                                <div class="step-icon">
                                    <i class="fas fa-table"></i>
                                </div>
                                <div class="step-content">
                                    <h6>Create Tables</h6>
                                    <p>Creating all required database tables and relationships</p>
                                </div>
                            </div>
                            
                            <div class="step" id="step-3">
                                <div class="step-icon">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <div class="step-content">
                                    <h6>Sample Data</h6>
                                    <p>Inserting default categories, users, and sample products</p>
                                </div>
                            </div>
                            
                            <div class="step" id="step-4">
                                <div class="step-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="step-content">
                                    <h6>Verification</h6>
                                    <p>Verifying installation and testing system functionality</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary btn-lg" id="start-setup" onclick="startSetup()">
                                    <i class="fas fa-play me-2"></i>Start Setup
                                </button>
                                <button class="btn btn-success btn-lg" id="continue-to-system" onclick="continueToSystem()" style="display: none;">
                                    <i class="fas fa-arrow-right me-2"></i>Continue to POS System
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4" id="log-container" style="display: none;">
                            <h6>Setup Log:</h6>
                            <div class="log-output" id="setup-log"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let setupLog = '';
        
        function addLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = `[${timestamp}] ${message}\n`;
            setupLog += logEntry;
            
            const logElement = document.getElementById('setup-log');
            if (logElement) {
                logElement.textContent = setupLog;
                logElement.scrollTop = logElement.scrollHeight;
            }
        }
        
        function updateStep(stepNumber, status, message = '') {
            const step = document.getElementById(`step-${stepNumber}`);
            const icon = step.querySelector('.step-icon i');
            
            // Remove all status classes
            step.classList.remove('completed', 'error', 'processing');
            
            switch (status) {
                case 'processing':
                    step.classList.add('processing');
                    icon.className = 'loading-spinner';
                    break;
                case 'completed':
                    step.classList.add('completed');
                    icon.className = 'fas fa-check';
                    break;
                case 'error':
                    step.classList.add('error');
                    icon.className = 'fas fa-times';
                    break;
            }
            
            if (message) {
                step.querySelector('.step-content p').textContent = message;
            }
        }
        
        async function startSetup() {
            const startButton = document.getElementById('start-setup');
            const logContainer = document.getElementById('log-container');
            
            startButton.disabled = true;
            startButton.innerHTML = '<div class="loading-spinner me-2"></div>Setting up...';
            logContainer.style.display = 'block';
            
            addLog('Starting POS System setup...');
            
            try {
                // Step 1: Database Connection and Creation
                updateStep(1, 'processing', 'Connecting to database...');
                addLog('Testing database connection...');
                
                const response = await fetch('config/init_database.php');
                const result = await response.text();
                
                if (response.ok) {
                    updateStep(1, 'completed', 'Database connected and created successfully');
                    addLog('Database setup completed successfully');
                    
                    // Step 2: Already handled by init_database.php
                    updateStep(2, 'completed', 'All tables created successfully');
                    addLog('Database tables created');
                    
                    // Step 3: Already handled by init_database.php
                    updateStep(3, 'completed', 'Sample data inserted successfully');
                    addLog('Sample data inserted');
                    
                    // Step 4: Verification
                    updateStep(4, 'processing', 'Verifying installation...');
                    addLog('Verifying system installation...');
                    
                    // Test API endpoints
                    const testResponse = await fetch('api/dashboard-stats.php');
                    const testData = await testResponse.json();
                    
                    if (testData.success) {
                        updateStep(4, 'completed', 'System verification successful');
                        addLog('System verification completed successfully');
                        addLog('Setup completed! You can now use the POS system.');
                        
                        // Show continue button
                        startButton.style.display = 'none';
                        document.getElementById('continue-to-system').style.display = 'block';
                    } else {
                        throw new Error('System verification failed');
                    }
                } else {
                    throw new Error('Database setup failed');
                }
                
            } catch (error) {
                addLog(`Error: ${error.message}`, 'error');
                updateStep(1, 'error', 'Setup failed - check log for details');
                
                startButton.disabled = false;
                startButton.innerHTML = '<i class="fas fa-redo me-2"></i>Retry Setup';
            }
        }
        
        function continueToSystem() {
            window.location.href = 'index.php';
        }
        
        // Check if system is already set up
        window.addEventListener('load', async () => {
            try {
                const response = await fetch('api/dashboard-stats.php');
                const data = await response.json();
                
                if (data.success) {
                    // System is already set up
                    document.getElementById('start-setup').innerHTML = '<i class="fas fa-check me-2"></i>System Already Set Up';
                    document.getElementById('continue-to-system').style.display = 'block';
                    
                    // Mark all steps as completed
                    for (let i = 1; i <= 4; i++) {
                        updateStep(i, 'completed');
                    }
                    
                    addLog('System is already set up and ready to use.');
                    document.getElementById('log-container').style.display = 'block';
                }
            } catch (error) {
                // System not set up yet, continue with normal setup
                console.log('System not set up yet');
            }
        });
    </script>
</body>
</html>