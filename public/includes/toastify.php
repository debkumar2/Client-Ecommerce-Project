<?php
/**
 * Toastify Top-Center Notification Component
 * Biswas Enterprise E-Commerce
 */
?>
<!-- Toastify CSS & JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<style>
/* Toastify Top Center Custom Styling */
.toastify {
    padding: 12px 20px !important;
    color: #ffffff !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25) !important;
    border-radius: 50px !important; /* Premium Pill Shape */
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    top: 20px !important;
    z-index: 999999 !important;
    width: max-content !important;
    max-width: calc(100vw - 32px) !important;
    box-sizing: border-box !important;
    transform: none !important;
    letter-spacing: 0.2px !important;
}

.toastify.toastify-top.toastify-center {
    left: 0 !important;
    right: 0 !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

.toastify-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: 1px solid #047857 !important;
}

.toastify-error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    border: 1px solid #b91c1c !important;
}

.toastify-info {
    background: linear-gradient(135deg, #1b3b2b 0%, #2a523c 100%) !important;
    border: 1px solid #10261b !important;
}

.toastify-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    border: 1px solid #b45309 !important;
}

.toastify .toast-close {
    opacity: 0.85 !important;
    padding-left: 12px !important;
    font-size: 16px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    color: #ffffff !important;
    margin-left: auto !important;
}

.toastify .toast-close:hover {
    opacity: 1 !important;
}
</style>

<script>
function showToastify(message, type = 'info') {
    if (!message) return;
    let bgClass = 'toastify-info';
    let icon = 'ℹ️ ';
    
    if (type === 'error' || type === 'danger') {
        bgClass = 'toastify-error';
        icon = '⚠️ ';
    } else if (type === 'success') {
        bgClass = 'toastify-success';
        icon = '✓ ';
    } else if (type === 'warning') {
        bgClass = 'toastify-warning';
        icon = '🔔 ';
    }

    if (typeof Toastify === 'function') {
        Toastify({
            text: icon + message,
            duration: 4000,
            close: true,
            gravity: "top",
            position: "center",
            stopOnFocus: true,
            className: bgClass
        }).showToast();
    } else {
        let container = document.getElementById('global-toast-fallback-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'global-toast-fallback-container';
            container.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:999999;display:flex;flex-direction:column;gap:10px;align-items:center;pointer-events:none;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = bgClass;
        toast.style.cssText = `background:${type==='error'?'#ef4444':'#10b981'};color:#fff;padding:12px 20px;border-radius:50px;font-size:13.5px;font-weight:600;box-shadow:0 10px 30px rgba(0,0,0,0.25);pointer-events:auto;display:flex;align-items:center;gap:10px;width:max-content;`;
        toast.innerHTML = icon + message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
}
</script>
