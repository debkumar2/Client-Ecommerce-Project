/**
 * Newsletter Component JavaScript
 * Handles newsletter subscription form validation and submission.
 */

import { subscribeNewsletter } from '../core/api.js';
import { showToast } from '../core/utils.js';

export const initNewsletter = () => {
    const form = document.getElementById('newsletter-form');
    if (!form) return;

    const input = form.querySelector('.newsletter-input');
    const feedback = document.getElementById('newsletter-feedback');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = input ? input.value.trim() : '';

        // Basic Email Validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            if (feedback) {
                feedback.className = 'newsletter-feedback error';
                feedback.textContent = 'Please enter a valid email address.';
            }
            showToast('Please enter a valid email address', 'error');
            return;
        }

        // Disable input while submitting
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const res = await subscribeNewsletter(email);

        if (submitBtn) submitBtn.disabled = false;

        if (res && res.success) {
            if (feedback) {
                feedback.className = 'newsletter-feedback success';
                feedback.textContent = res.message || 'Thank you for subscribing!';
            }
            showToast('Thank you for subscribing!', 'success');
            if (input) input.value = '';
        } else {
            if (feedback) {
                feedback.className = 'newsletter-feedback error';
                feedback.textContent = 'Something went wrong. Please try again.';
            }
            showToast('Subscription failed. Please try again.', 'error');
        }
    });
};
