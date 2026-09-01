/**
 * Utility Helpers
 */

export const formatCurrency = (amount, currency = 'INR') => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: currency,
        maximumFractionDigits: 0
    }).format(amount);
};

export const debounce = (func, wait = 300) => {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
};

export const showToast = (message, type = 'info') => {
    if (typeof window.showToastify === 'function') {
        window.showToastify(message, type);
        return;
    }
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:999999;display:flex;flex-direction:column;gap:10px;align-items:center;pointer-events:none;';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const bg = type === 'success' ? '#10b981' : (type === 'error' || type === 'danger' ? '#ef4444' : '#1b3b2b');
    const icon = type === 'success' ? '✓ ' : (type === 'error' || type === 'danger' ? '⚠️ ' : 'ℹ️ ');
    toast.style.cssText = `background:${bg};color:#ffffff;padding:13px 22px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 12px 32px rgba(0,0,0,0.18);transition:all 0.3s ease;pointer-events:auto;display:flex;align-items:center;gap:8px;`;
    toast.innerHTML = icon + message;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};
