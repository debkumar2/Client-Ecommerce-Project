/**
 * Header Component JavaScript
 * Handles mobile drawer toggle, search modal toggle, and sticky header behavior.
 */

export const initHeader = () => {
    const mobileToggle = document.getElementById('mobile-toggle');
    const drawerClose = document.getElementById('drawer-close');
    const mobileDrawer = document.getElementById('mobile-drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const searchToggle = document.getElementById('search-toggle');
    const searchModal = document.getElementById('search-modal');

    // Mobile Drawer Toggle
    if (mobileToggle && mobileDrawer && drawerOverlay) {
        const openDrawer = () => {
            mobileDrawer.classList.add('open');
            drawerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            mobileDrawer.classList.remove('open');
            drawerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        mobileToggle.addEventListener('click', openDrawer);
        if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
        drawerOverlay.addEventListener('click', closeDrawer);
        
        // Escape key listener
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileDrawer.classList.contains('open')) {
                closeDrawer();
            }
        });
    }

    // Search Bar Toggle
    if (searchToggle && searchModal) {
        searchToggle.addEventListener('click', (e) => {
            e.preventDefault();
            searchModal.classList.toggle('active');
            if (searchModal.classList.contains('active')) {
                const searchInput = searchModal.querySelector('input');
                if (searchInput) searchInput.focus();
            }
        });
    }

    // Dropdown Navigation Menu Toggle
    const dropdownToggles = document.querySelectorAll('.has-dropdown');
    dropdownToggles.forEach(dropdown => {
        const toggleBtn = dropdown.querySelector('.dropdown-toggle');
        if (toggleBtn) {
            dropdown.addEventListener('mouseenter', () => {
                dropdown.classList.add('active');
            });
            dropdown.addEventListener('mouseleave', () => {
                dropdown.classList.remove('active');
            });
            toggleBtn.addEventListener('click', (e) => {
                // If on mobile or touch device, toggle active state
                if (window.innerWidth <= 1024) {
                    e.preventDefault();
                    dropdown.classList.toggle('active');
                }
            });
        }
    });
};
