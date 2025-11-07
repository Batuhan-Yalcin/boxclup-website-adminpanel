// Admin Panel JavaScript

// Mobile Menu Toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    sidebar.classList.toggle('active');
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar overlay functionality
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', () => {
            toggleSidebar();
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.mobile-menu-toggle');
        if (window.innerWidth <= 968 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !toggleBtn && !toggleBtn.contains(e.target)) {
                toggleSidebar();
            }
        }
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// Form validation
const forms = document.querySelectorAll('form');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = '#dc3545';
                
                setTimeout(() => {
                    field.style.borderColor = '';
                }, 3000);
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Lütfen tüm zorunlu alanları doldurun!');
        }
    });
});

// Confirm delete actions
document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm('Bu işlemi gerçekleştirmek istediğinize emin misiniz?')) {
            e.preventDefault();
        }
    });
});

// Table row click (for messages)
document.querySelectorAll('.data-table tbody tr').forEach(row => {
    row.addEventListener('click', function(e) {
        // Don't trigger if clicking on action buttons
        if (!e.target.closest('.action-buttons')) {
            // Could add expand/collapse functionality here
        }
    });
});

// Auto-save draft (optional feature)
let autoSaveTimer;
const contentForm = document.querySelector('.content-form');
if (contentForm) {
    const inputs = contentForm.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Save to localStorage as draft
                const formData = new FormData(contentForm);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });
                localStorage.setItem('content_draft', JSON.stringify(data));
                
                // Show notification
                showNotification('Taslak kaydedildi', 'success');
            }, 2000);
        });
    });
    
    // Load draft on page load
    const draft = localStorage.getItem('content_draft');
    if (draft) {
        try {
            const data = JSON.parse(draft);
            Object.keys(data).forEach(key => {
                const input = contentForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = data[key];
                }
            });
        } catch (e) {
            console.error('Draft yüklenirken hata:', e);
        }
    }
}

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'info'}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'fadeInUp 0.3s ease';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Search functionality for tables
function initTableSearch() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Ara...';
        searchInput.className = 'form-control';
        searchInput.style.marginBottom = '15px';
        searchInput.style.maxWidth = '300px';
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        const container = table.closest('.table-container');
        if (container) {
            container.insertBefore(searchInput, table);
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Initialize table search
    initTableSearch();
});

// Export data (optional)
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename || 'export.csv';
    link.click();
}

