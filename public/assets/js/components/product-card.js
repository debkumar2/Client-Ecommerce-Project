/**
 * Product Card Component JavaScript
 * Handles wishlist toggles and Add to Cart visual feedback.
 */

import { showToast } from '../core/utils.js';

export const initProductCards = () => {
    // Wishlist Button Handlers
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            btn.classList.toggle('active');
            const isWishlisted = btn.classList.contains('active');
            showToast(isWishlisted ? 'Added to your Wishlist' : 'Removed from your Wishlist', 'info');
        });
    });

    // Add to Cart Handlers
    document.querySelectorAll('.btn-add-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (btn.disabled) return;

            const card = btn.closest('.product-card');
            const title = card ? card.querySelector('.product-title').textContent.trim() : 'Product';

            // TODO: Connect to backend Cart API (POST /api/cart/add)
            
            // Visual feedback
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span>Added ✓</span>`;
            btn.style.backgroundColor = 'var(--primary)';
            btn.style.color = '#ffffff';

            // Update header cart badge
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                let count = parseInt(cartBadge.textContent || '0', 10);
                cartBadge.textContent = count + 1;
            }

            showToast(`${title} added to cart`, 'success');

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.backgroundColor = '';
                btn.style.color = '';
            }, 1800);
        });
    });
};
