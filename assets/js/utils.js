/**
 * Utility Functions for Better UX
 * College Face Recognition Attendance System
 */

// ============================================
// Toast Notification System
// ============================================

class ToastNotification {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        toast.style.cssText = `
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid ${colors[type]};
            animation: slideInRight 0.3s ease;
            pointer-events: auto;
            cursor: pointer;
            min-width: 300px;
        `;

        toast.innerHTML = `
            <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 1.2rem;"></i>
            <span style="flex: 1; color: #1f2937; font-weight: 500;">${message}</span>
            <i class="fas fa-times" style="color: #9ca3af; cursor: pointer; font-size: 0.9rem;"></i>
        `;

        // Close button
        const closeBtn = toast.querySelector('.fa-times');
        closeBtn.addEventListener('click', () => this.remove(toast));

        // Auto remove
        const timeout = setTimeout(() => this.remove(toast), duration);

        // Pause on hover
        toast.addEventListener('mouseenter', () => clearTimeout(timeout));
        toast.addEventListener('mouseleave', () => {
            setTimeout(() => this.remove(toast), duration);
        });

        this.container.appendChild(toast);
        return toast;
    }

    remove(toast) {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Global toast instance
const toast = new ToastNotification();

// Replace alert with toast
window.showToast = function(message, type = 'info') {
    toast.show(message, type);
};

// ============================================
// Loading States
// ============================================

function showLoading(element, message = 'Loading...') {
    if (!element) return;
    
    const loader = document.createElement('div');
    loader.className = 'loading-overlay';
    loader.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <p style="margin-top: 1rem; color: var(--primary-color); font-weight: 500;">${message}</p>
        </div>
    `;
    element.style.position = 'relative';
    element.appendChild(loader);
    return loader;
}

function hideLoading(loader) {
    if (loader && loader.parentNode) {
        loader.parentNode.removeChild(loader);
    }
}

// ============================================
// Debounce Function
// ============================================

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================
// Form Validation
// ============================================

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[+]?[(]?[0-9]{1,4}[)]?[-\s.]?[(]?[0-9]{1,4}[)]?[-\s.]?[0-9]{1,9}$/;
    return re.test(phone);
}

function showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.cssText = `
        color: var(--danger-color);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    `;
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    field.classList.add('error');
    field.style.borderColor = 'var(--danger-color)';
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    field.style.borderColor = '';
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// ============================================
// Empty State Component
// ============================================

function createEmptyState(icon, title, message, actionText = null, actionCallback = null) {
    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'empty-state';
    emptyDiv.style.cssText = `
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
    `;
    
    let actionBtn = '';
    if (actionText && actionCallback) {
        actionBtn = `<button class="btn btn-primary" onclick="${actionCallback}" style="margin-top: 1rem;">${actionText}</button>`;
    }
    
    emptyDiv.innerHTML = `
        <i class="fas ${icon}" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
        <h3 style="color: #374151; margin-bottom: 0.5rem;">${title}</h3>
        <p style="color: #6b7280;">${message}</p>
        ${actionBtn}
    `;
    
    return emptyDiv;
}

// ============================================
// Keyboard Shortcuts
// ============================================

const shortcuts = {};

function registerShortcut(key, callback, description = '') {
    shortcuts[key.toLowerCase()] = { callback, description };
}

document.addEventListener('keydown', (e) => {
    // Check for Ctrl/Cmd + key combinations
    if ((e.ctrlKey || e.metaKey) && shortcuts[e.key.toLowerCase()]) {
        e.preventDefault();
        shortcuts[e.key.toLowerCase()].callback();
    }
    
    // Check for Escape key
    if (e.key === 'Escape') {
        // Close any open modals
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// ============================================
// Tooltip System
// ============================================

function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        const tooltipText = element.getAttribute('data-tooltip');
        
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: #1f2937;
                color: white;
                padding: 0.5rem 0.75rem;
                border-radius: 6px;
                font-size: 0.875rem;
                white-space: nowrap;
                z-index: 10001;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            setTimeout(() => tooltip.style.opacity = '1', 10);
            
            element._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', () => {
            if (element._tooltip) {
                element._tooltip.style.opacity = '0';
                setTimeout(() => {
                    if (element._tooltip.parentNode) {
                        element._tooltip.parentNode.removeChild(element._tooltip);
                    }
                }, 200);
            }
        });
    });
}

// Initialize tooltips on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTooltips);
} else {
    initTooltips();
}

// ============================================
// Auto-save Form Data
// ============================================

function enableAutoSave(formId, storageKey) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    // Load saved data
    const savedData = localStorage.getItem(storageKey);
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = data[key];
                }
            });
        } catch (e) {
            console.error('Failed to load saved form data:', e);
        }
    }
    
    // Save on change
    form.addEventListener('input', debounce(() => {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        localStorage.setItem(storageKey, JSON.stringify(data));
    }, 1000));
    
    // Clear on successful submit
    form.addEventListener('submit', () => {
        setTimeout(() => {
            localStorage.removeItem(storageKey);
        }, 1000);
    });
}

// ============================================
// Export Functions
// ============================================

function exportToCSV(data, filename) {
    if (!data || data.length === 0) {
        toast.warning('No data to export');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => 
            headers.map(header => {
                const value = row[header] || '';
                return `"${String(value).replace(/"/g, '""')}"`;
            }).join(',')
        )
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toast.success('Data exported successfully!');
}

// ============================================
// Format Helpers
// ============================================

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatTime(timeString) {
    if (!timeString) return '-';
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minutes} ${ampm}`;
}

function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString();
}

// ============================================
// Confirmation Dialog
// ============================================

function confirmAction(message, onConfirm, onCancel = null) {
    const confirmed = window.confirm(message);
    if (confirmed && onConfirm) {
        onConfirm();
    } else if (!confirmed && onCancel) {
        onCancel();
    }
    return confirmed;
}

// Export for use in other files
window.ToastNotification = ToastNotification;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.debounce = debounce;
window.validateEmail = validateEmail;
window.validatePhone = validatePhone;
window.showFieldError = showFieldError;
window.clearFieldError = clearFieldError;
window.createEmptyState = createEmptyState;
window.registerShortcut = registerShortcut;
window.enableAutoSave = enableAutoSave;
window.exportToCSV = exportToCSV;
window.formatDate = formatDate;
window.formatTime = formatTime;
window.formatNumber = formatNumber;
window.confirmAction = confirmAction;

