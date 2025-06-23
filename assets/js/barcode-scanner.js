// Barcode Scanner Integration for POS System
class BarcodeScanner {
    constructor(posSystem) {
        this.posSystem = posSystem;
        this.isScanning = false;
        this.scannerModal = null;
        this.quaggaInitialized = false;
        this.usbScannerBuffer = '';
        this.usbScannerTimeout = null;
        
        this.init();
    }

    init() {
        this.setupUSBScanner();
        this.createScannerModal();
        this.updateScannerStatus();
    }

    setupUSBScanner() {
        // Listen for USB barcode scanner input
        document.addEventListener('keydown', (e) => {
            this.handleUSBScannerInput(e);
        });

        // Check if USB scanner is connected (simulated)
        this.checkUSBScannerConnection();
    }

    handleUSBScannerInput(e) {
        // USB barcode scanners typically send data quickly followed by Enter
        if (e.key === 'Enter' && this.usbScannerBuffer.length > 0) {
            const barcode = this.usbScannerBuffer.trim();
            if (barcode.length >= 8) { // Minimum barcode length
                this.processScan(barcode, 'usb');
            }
            this.usbScannerBuffer = '';
            return;
        }

        // Accumulate characters for potential barcode
        if (e.key.length === 1 && /[0-9A-Za-z]/.test(e.key)) {
            this.usbScannerBuffer += e.key;
            
            // Clear buffer after timeout (in case it's not a barcode scan)
            clearTimeout(this.usbScannerTimeout);
            this.usbScannerTimeout = setTimeout(() => {
                this.usbScannerBuffer = '';
            }, 100);
        }
    }

    checkUSBScannerConnection() {
        // Simulate USB scanner detection
        // In a real implementation, this would check for actual USB devices
        setTimeout(() => {
            this.updateScannerStatus(true);
        }, 1000);
    }

    updateScannerStatus(connected = true) {
        const statusElement = document.getElementById('scanner-status');
        if (statusElement) {
            if (connected) {
                statusElement.className = 'badge bg-success';
                statusElement.innerHTML = '<i class="fas fa-barcode me-1"></i>Scanner Ready';
            } else {
                statusElement.className = 'badge bg-warning';
                statusElement.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Scanner Disconnected';
            }
        }
    }

    createScannerModal() {
        const modalHTML = `
            <div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="scannerModalLabel">
                                <i class="fas fa-qrcode me-2"></i>Barcode Scanner
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="scanner-container" id="scanner-container">
                                        <div class="scanner-overlay"></div>
                                        <div class="text-center mt-3">
                                            <p class="text-muted">Position barcode within the frame</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Scanner Options</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Scanner Type</label>
                                                <select class="form-select" id="scanner-type">
                                                    <option value="camera">Camera Scanner</option>
                                                    <option value="usb" selected>USB Scanner</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Manual Entry</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="manual-barcode" placeholder="Enter barcode manually">
                                                    <button class="btn btn-primary" type="button" onclick="processManualEntry()">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn btn-success w-100" onclick="startCameraScanning()">
                                                    <i class="fas fa-camera me-2"></i>Start Camera
                                                </button>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn btn-warning w-100" onclick="stopScanning()">
                                                    <i class="fas fa-stop me-2"></i>Stop Scanning
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Recent Scans</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="recent-scans" class="recent-scans">
                                                <p class="text-muted text-center">No recent scans</p>
                                            </div>
                                        </div>
                                    </div>
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

        // Add modal to modals container
        const modalsContainer = document.getElementById('modals-container');
        if (modalsContainer) {
            modalsContainer.insertAdjacentHTML('beforeend', modalHTML);
        }

        this.scannerModal = new bootstrap.Modal(document.getElementById('scannerModal'));
    }

    showScannerModal() {
        if (this.scannerModal) {
            this.scannerModal.show();
        }
    }

    startCameraScanning() {
        if (!window.Quagga) {
            this.posSystem.showNotification('Camera scanner not available', 'error');
            return;
        }

        const scannerContainer = document.getElementById('scanner-container');
        if (!scannerContainer) return;

        if (this.isScanning) {
            this.stopScanning();
        }

        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: scannerContainer,
                constraints: {
                    width: 640,
                    height: 480,
                    facingMode: "environment"
                }
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: 2,
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "code_39_vin_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ]
            },
            locate: true
        }, (err) => {
            if (err) {
                console.error('Quagga initialization failed:', err);
                this.posSystem.showNotification('Camera initialization failed', 'error');
                return;
            }
            
            console.log("Quagga initialization finished. Ready to start");
            Quagga.start();
            this.isScanning = true;
            this.quaggaInitialized = true;
            
            // Listen for successful scans
            Quagga.onDetected((data) => {
                const barcode = data.codeResult.code;
                this.processScan(barcode, 'camera');
            });
        });
    }

    stopScanning() {
        if (this.quaggaInitialized && window.Quagga) {
            Quagga.stop();
            this.isScanning = false;
            this.quaggaInitialized = false;
        }
    }

    processManualEntry() {
        const manualInput = document.getElementById('manual-barcode');
        if (manualInput && manualInput.value.trim()) {
            const barcode = manualInput.value.trim();
            this.processScan(barcode, 'manual');
            manualInput.value = '';
        }
    }

    async processScan(barcode, scanType) {
        if (!barcode || barcode.length < 8) {
            this.posSystem.showNotification('Invalid barcode format', 'warning');
            return;
        }

        // Add to recent scans
        this.addToRecentScans(barcode, scanType);

        // Process the scan through the POS system
        await this.posSystem.scanProduct(barcode);

        // Play scan sound
        this.playScanSound();

        // If using camera, briefly pause scanning to prevent multiple scans
        if (scanType === 'camera' && this.isScanning) {
            this.stopScanning();
            setTimeout(() => {
                if (document.getElementById('scannerModal').classList.contains('show')) {
                    this.startCameraScanning();
                }
            }, 2000);
        }
    }

    addToRecentScans(barcode, scanType) {
        const recentScansContainer = document.getElementById('recent-scans');
        if (!recentScansContainer) return;

        const scanTime = new Date().toLocaleTimeString();
        const scanTypeIcon = {
            'camera': 'fa-camera',
            'usb': 'fa-usb',
            'manual': 'fa-keyboard'
        };

        const scanElement = document.createElement('div');
        scanElement.className = 'recent-scan-item mb-2 p-2 border rounded';
        scanElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas ${scanTypeIcon[scanType]} me-2 text-muted"></i>
                    <span class="barcode-display">${barcode}</span>
                </div>
                <small class="text-muted">${scanTime}</small>
            </div>
        `;

        // Remove "No recent scans" message if it exists
        const noScansMessage = recentScansContainer.querySelector('.text-muted');
        if (noScansMessage && noScansMessage.textContent.includes('No recent scans')) {
            noScansMessage.remove();
        }

        // Add new scan to the top
        recentScansContainer.insertBefore(scanElement, recentScansContainer.firstChild);

        // Keep only the last 5 scans
        const scanItems = recentScansContainer.querySelectorAll('.recent-scan-item');
        if (scanItems.length > 5) {
            scanItems[scanItems.length - 1].remove();
        }
    }

    playScanSound() {
        // Create and play a simple beep sound
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'square';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (error) {
            console.log('Audio not supported or blocked');
        }
    }

    // Test function to simulate barcode scanning
    simulateScan(barcode = '1234567890123') {
        this.processScan(barcode, 'manual');
    }
}

// Initialize barcode scanner when POS system is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait for POS system to be initialized
    setTimeout(() => {
        if (window.posSystem) {
            window.barcodeScanner = new BarcodeScanner(window.posSystem);
            
            // Override the POS system's scanner methods
            posSystem.setupBarcodeScanner = () => {
                // Already handled by BarcodeScanner class
            };
            
            posSystem.startBarcodeScanning = () => {
                barcodeScanner.showScannerModal();
            };
        }
    }, 100);
});

// Global functions for modal interactions
function startCameraScanning() {
    if (window.barcodeScanner) {
        window.barcodeScanner.startCameraScanning();
    } else {
        console.error('Barcode scanner not initialized');
        alert('Scanner not available. Please wait for system to load.');
    }
}

function stopScanning() {
    if (window.barcodeScanner) {
        window.barcodeScanner.stopScanning();
    } else {
        console.error('Barcode scanner not initialized');
    }
}

function processManualEntry() {
    if (window.barcodeScanner) {
        window.barcodeScanner.processManualEntry();
    } else {
        console.error('Barcode scanner not initialized');
        alert('Scanner not available. Please wait for system to load.');
    }
}

// Global function for opening scanner modal (used by transaction modal)
function openScannerForTransaction() {
    if (window.barcodeScanner) {
        barcodeScanner.showScannerModal();
    } else {
        console.error('Barcode scanner not initialized');
        if (window.posSystem) {
            posSystem.showNotification('Scanner not available. Please wait for system to load.', 'warning');
        } else {
            alert('Scanner not available. Please wait for system to load.');
        }
    }
}

// Global function for main header scan button
function startBarcodeScanning() {
    if (window.barcodeScanner) {
        barcodeScanner.showScannerModal();
    } else {
        console.error('Barcode scanner not initialized');
        if (window.posSystem) {
            posSystem.showNotification('Scanner not available. Please wait for system to load.', 'warning');
        } else {
            alert('Scanner not available. Please wait for system to load.');
        }
    }
}

// Function to simulate scanning for testing
function simulateScan(barcode) {
    if (window.barcodeScanner) {
        barcodeScanner.simulateScan(barcode);
    } else {
        console.error('Barcode scanner not initialized');
    }
}