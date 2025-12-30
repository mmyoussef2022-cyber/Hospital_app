// Hospital Management System JavaScript

// Import Bootstrap
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Import Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Import Axios
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token Setup
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

// Hospital Management System Global Functions
window.HMS = {
    // Show success message
    showSuccess: function(message) {
        Swal.fire({
            icon: 'success',
            title: 'نجح!',
            text: message,
            confirmButtonColor: '#1877F2',
            confirmButtonText: 'موافق'
        });
    },

    // Show error message
    showError: function(message) {
        Swal.fire({
            icon: 'error',
            title: 'خطأ!',
            text: message,
            confirmButtonColor: '#F44336',
            confirmButtonText: 'موافق'
        });
    },

    // Show confirmation dialog
    confirm: function(message, callback) {
        Swal.fire({
            title: 'تأكيد',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1877F2',
            cancelButtonColor: '#F44336',
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    },

    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR'
        }).format(amount);
    },

    // Format date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('ar-SA').format(new Date(date));
    },

    // Toggle sidebar on mobile
    toggleSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    },

    // Initialize tooltips
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Initialize popovers
    initPopovers: function() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    },

    // Barcode scanner integration
    initBarcodeScanner: function(callback) {
        // This will be implemented when barcode scanning is added
        console.log('Barcode scanner initialized');
    },

    // Print function
    print: function(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>طباعة</title>
                        <style>
                            body { font-family: Arial, sans-serif; direction: rtl; }
                            @media print { body { margin: 0; } }
                        </style>
                    </head>
                    <body>
                        ${element.innerHTML}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    }
};

// Initialize components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    HMS.initTooltips();
    HMS.initPopovers();
    
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', HMS.toggleSidebar);
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Livewire hooks
document.addEventListener('livewire:load', function () {
    // Re-initialize components after Livewire updates
    Livewire.hook('message.processed', (message, component) => {
        HMS.initTooltips();
        HMS.initPopovers();
    });
});

// Service Worker for offline functionality (future enhancement)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(error) {
                console.log('ServiceWorker registration failed');
            });
    });
}