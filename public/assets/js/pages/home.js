/**
 * Home Page JavaScript Controller
 */

import { initHeader } from '../components/header.js';
import { initProductCards } from '../components/product-card.js';
import { initNewsletter } from '../components/newsletter.js';

export const initHomePage = () => {
    initHeader();
    initProductCards();
    initNewsletter();

    // IntersectionObserver for Scroll Reveal
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        revealElements.forEach(el => observer.observe(el));
    } else {
        // Fallback for reduced motion or legacy browsers
        document.querySelectorAll('.reveal-on-scroll').forEach(el => el.classList.add('is-visible'));
    }
};
