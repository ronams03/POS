// Charts and Analytics for POS System
class POSCharts {
    constructor(posSystem) {
        this.posSystem = posSystem;
        this.charts = {};
        this.chartColors = {
            primary: '#007bff',
            success: '#28a745',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#17a2b8',
            light: '#f8f9fa',
            dark: '#343a40'
        };
        
        this.init();
    }

    init() {
        this.setupChartDefaults();
        setTimeout(() => {
            this.initializeAllCharts();
        }, 1000);
    }

    setupChartDefaults() {
        if (window.Chart) {
            Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.color = '#5a5c69';
            Chart.defaults.plugins.legend.display = true;
            Chart.defaults.plugins.legend.position = 'top';
        }
    }

    initializeAllCharts() {
        this.initializeSalesChart();
        this.initializeTopProductsChart();
        this.initializeScannedProductsChart();
    }

    initializeSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        this.charts.sales = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.getDateLabels('week'),
                datasets: [{
                    label: 'Sales ($)',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    borderColor: this.chartColors.primary,
                    backgroundColor: this.hexToRgba(this.chartColors.primary, 0.1),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: this.chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: this.chartColors.primary,
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return 'Sales: $' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#858796'
                        }
                    },
                    y: {
                        grid: {
                            color: '#eaecf4',
                            borderDash: [2]
                        },
                        ticks: {
                            color: '#858796',
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        this.loadSalesData('week');
    }

    initializeTopProductsChart() {
        const ctx = document.getElementById('topProductsChart');
        if (!ctx) return;

        this.charts.topProducts = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Loading...'],
                datasets: [{
                    data: [1],
                    backgroundColor: [this.chartColors.light],
                    borderColor: [this.chartColors.primary],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                return label + ': ' + value + ' sold';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

        this.loadTopProductsData();
    }

    initializeScannedProductsChart() {
        const ctx = document.getElementById('scannedProductsChart');
        if (!ctx) return;

        this.charts.scannedProducts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Loading...'],
                datasets: [{
                    label: 'Scans',
                    data: [0],
                    backgroundColor: this.hexToRgba(this.chartColors.info, 0.8),
                    borderColor: this.chartColors.info,
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' scans';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#858796'
                        }
                    },
                    y: {
                        grid: {
                            color: '#eaecf4',
                            borderDash: [2]
                        },
                        ticks: {
                            color: '#858796',
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }
                }
            }
        });

        this.loadScannedProductsData();
    }

    async loadSalesData(period = 'week') {
        try {
            const response = await fetch(`api/sales-data.php?period=${period}`);
            const data = await response.json();
            
            if (data.success && this.charts.sales) {
                this.charts.sales.data.labels = this.getDateLabels(period);
                this.charts.sales.data.datasets[0].data = data.sales || [];
                this.charts.sales.update();
            }
        } catch (error) {
            console.error('Error loading sales data:', error);
            this.showChartError('salesChart', 'Failed to load sales data');
        }
    }

    async loadTopProductsData() {
        try {
            const response = await fetch('api/top-products.php');
            const data = await response.json();
            
            if (data.success && this.charts.topProducts && data.products.length > 0) {
                const colors = this.generateColors(data.products.length);
                
                this.charts.topProducts.data.labels = data.products.map(p => p.name);
                this.charts.topProducts.data.datasets[0].data = data.products.map(p => p.total_sold);
                this.charts.topProducts.data.datasets[0].backgroundColor = colors.background;
                this.charts.topProducts.data.datasets[0].borderColor = colors.border;
                this.charts.topProducts.update();
            }
        } catch (error) {
            console.error('Error loading top products data:', error);
            this.showChartError('topProductsChart', 'Failed to load product data');
        }
    }

    async loadScannedProductsData() {
        try {
            const response = await fetch('api/scanned-products.php');
            const data = await response.json();
            
            if (data.success && this.charts.scannedProducts && data.products.length > 0) {
                this.charts.scannedProducts.data.labels = data.products.map(p => p.name);
                this.charts.scannedProducts.data.datasets[0].data = data.products.map(p => p.scan_count);
                this.charts.scannedProducts.update();
            }
        } catch (error) {
            console.error('Error loading scanned products data:', error);
            this.showChartError('scannedProductsChart', 'Failed to load scan data');
        }
    }

    getDateLabels(period) {
        const labels = [];
        const now = new Date();
        
        switch (period) {
            case 'week':
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(now);
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('en-US', { weekday: 'short' }));
                }
                break;
                
            case 'month':
                for (let i = 29; i >= 0; i--) {
                    const date = new Date(now);
                    date.setDate(date.getDate() - i);
                    labels.push(date.getDate().toString());
                }
                break;
                
            case 'year':
                for (let i = 11; i >= 0; i--) {
                    const date = new Date(now);
                    date.setMonth(date.getMonth() - i);
                    labels.push(date.toLocaleDateString('en-US', { month: 'short' }));
                }
                break;
                
            default:
                return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        }
        
        return labels;
    }

    generateColors(count) {
        const baseColors = [
            this.chartColors.primary,
            this.chartColors.success,
            this.chartColors.warning,
            this.chartColors.danger,
            this.chartColors.info
        ];
        
        const background = [];
        const border = [];
        
        for (let i = 0; i < count; i++) {
            const color = baseColors[i % baseColors.length];
            background.push(this.hexToRgba(color, 0.8));
            border.push(color);
        }
        
        return { background, border };
    }

    hexToRgba(hex, alpha = 1) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        if (result) {
            const r = parseInt(result[1], 16);
            const g = parseInt(result[2], 16);
            const b = parseInt(result[3], 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
        return hex;
    }

    showChartError(chartId, message) {
        const canvas = document.getElementById(chartId);
        if (canvas) {
            const container = canvas.parentElement;
            container.innerHTML = `
                <div class="text-center text-muted p-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="posCharts.retryChart('${chartId}')">
                        <i class="fas fa-sync-alt me-1"></i>Retry
                    </button>
                </div>
            `;
        }
    }

    retryChart(chartId) {
        // Recreate the canvas element
        const container = document.getElementById(chartId)?.parentElement;
        if (container) {
            container.innerHTML = `<canvas id="${chartId}" width="400" height="200"></canvas>`;
            
            // Reinitialize the specific chart
            switch (chartId) {
                case 'salesChart':
                    this.initializeSalesChart();
                    break;
                case 'topProductsChart':
                    this.initializeTopProductsChart();
                    break;
                case 'scannedProductsChart':
                    this.initializeScannedProductsChart();
                    break;
            }
        }
    }

    updateSalesChart() {
        const selectedPeriod = document.querySelector('input[name="sales-period"]:checked')?.id;
        let period = 'week';
        
        if (selectedPeriod === 'month-sales') period = 'month';
        else if (selectedPeriod === 'year-sales') period = 'year';
        
        this.loadSalesData(period);
    }

    refreshAllCharts() {
        this.loadSalesData();
        this.loadTopProductsData();
        this.loadScannedProductsData();
    }

    destroyAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }

    // Export chart as image
    exportChart(chartId, filename) {
        const chart = this.charts[chartId];
        if (chart) {
            const url = chart.toBase64Image();
            const link = document.createElement('a');
            link.download = filename || `${chartId}.png`;
            link.href = url;
            link.click();
        }
    }

    // Create a comprehensive analytics report
    async generateAnalyticsReport() {
        try {
            const [salesResponse, productsResponse, scansResponse] = await Promise.all([
                fetch('api/sales-data.php?period=month'),
                fetch('api/top-products.php'),
                fetch('api/scanned-products.php')
            ]);

            const salesData = await salesResponse.json();
            const productsData = await productsResponse.json();
            const scansData = await scansResponse.json();

            const report = {
                generated: new Date().toISOString(),
                sales: salesData.success ? salesData : null,
                topProducts: productsData.success ? productsData : null,
                scannedProducts: scansData.success ? scansData : null,
                summary: {
                    totalSales: salesData.success ? salesData.sales.reduce((a, b) => a + b, 0) : 0,
                    topProduct: productsData.success && productsData.products.length > 0 ? productsData.products[0].name : 'N/A',
                    mostScannedProduct: scansData.success && scansData.products.length > 0 ? scansData.products[0].name : 'N/A'
                }
            };

            return report;
        } catch (error) {
            console.error('Error generating analytics report:', error);
            return null;
        }
    }
}

// Initialize charts when POS system is ready
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (window.posSystem) {
            window.posCharts = new POSCharts(window.posSystem);
            
            // Override POS system chart methods
            posSystem.initializeCharts = () => {
                // Already handled by POSCharts class
            };
            
            posSystem.updateCharts = () => {
                if (window.posCharts) {
                    posCharts.refreshAllCharts();
                }
            };
            
            posSystem.updateSalesChart = () => {
                if (window.posCharts) {
                    posCharts.updateSalesChart();
                }
            };
        }
    }, 500);
});

// Global functions for chart interactions
function updateSalesChart() {
    if (window.posCharts) {
        window.posCharts.updateSalesChart();
    }
}

function exportChart(chartId, filename) {
    if (window.posCharts) {
        window.posCharts.exportChart(chartId, filename);
    }
}

function refreshCharts() {
    if (window.posCharts) {
        window.posCharts.refreshAllCharts();
    }
}