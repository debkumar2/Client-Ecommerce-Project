/**
 * FILTER SIDEBAR & MOBILE FILTER DRAWER COMPONENT
 * Handles slide-in drawer open/close animation, price slider range updates, and filter resets.
 */

export function initFilterSidebar(onFilterChangeCallback) {
    // 1. Mobile Filter Drawer Elements
    const mobileFilterTrigger = document.getElementById('openMobileFilter');
    const mobileFilterDrawer = document.getElementById('mobileFilterDrawer');
    const closeDrawerBtn = document.getElementById('closeMobileFilter');
    const applyDrawerBtn = document.getElementById('applyMobileFilter');

    // Create Backdrop Overlay dynamically if not present
    let drawerOverlay = document.getElementById('drawerOverlay');
    if (!drawerOverlay) {
        drawerOverlay = document.createElement('div');
        drawerOverlay.id = 'drawerOverlay';
        drawerOverlay.style.cssText = 'position:fixed;inset:0;background:rgba(10,36,18,0.5);backdrop-filter:blur(4px);z-index:2000;opacity:0;visibility:hidden;transition:all 0.3s ease;';
        document.body.appendChild(drawerOverlay);
    }

    if (mobileFilterTrigger && mobileFilterDrawer) {
        mobileFilterTrigger.addEventListener('click', openDrawer);
        if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
        if (applyDrawerBtn) applyDrawerBtn.addEventListener('click', () => {
            closeDrawer();
            if (typeof onFilterChangeCallback === 'function') onFilterChangeCallback();
        });
        drawerOverlay.addEventListener('click', closeDrawer);
    }

    function openDrawer() {
        if (!mobileFilterDrawer) return;
        mobileFilterDrawer.classList.add('open');
        drawerOverlay.style.opacity = '1';
        drawerOverlay.style.visibility = 'visible';
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        if (!mobileFilterDrawer) return;
        mobileFilterDrawer.classList.remove('open');
        drawerOverlay.style.opacity = '0';
        drawerOverlay.style.visibility = 'hidden';
        document.body.style.overflow = '';
    }

    // 2. Price Range Slider Synchronization
    const priceSliders = document.querySelectorAll('.price-range-slider');
    priceSliders.forEach(slider => {
        const container = slider.closest('.price-slider-wrapper');
        if (!container) return;

        const maxValDisplay = container.querySelector('.price-max-val');
        const maxInput = container.querySelector('.price-max-input');

        slider.addEventListener('input', (e) => {
            const val = e.target.value;
            if (maxValDisplay) maxValDisplay.textContent = `₹${val}`;
            if (maxInput) maxInput.value = val;
            if (typeof onFilterChangeCallback === 'function') onFilterChangeCallback();
        });

        if (maxInput) {
            maxInput.addEventListener('change', (e) => {
                const val = Math.min(Math.max(e.target.value, 0), slider.max);
                slider.value = val;
                if (maxValDisplay) maxValDisplay.textContent = `₹${val}`;
                if (typeof onFilterChangeCallback === 'function') onFilterChangeCallback();
            });
        }
    });

    // 3. Category & Checkbox Filter Event Listeners
    const filterInputs = document.querySelectorAll('.filter-item input');
    filterInputs.forEach(input => {
        input.addEventListener('change', () => {
            if (typeof onFilterChangeCallback === 'function') {
                onFilterChangeCallback();
            }
        });
    });

    // 4. Clear All Filters Buttons
    const clearFilterBtns = document.querySelectorAll('.btn-clear-filters');
    clearFilterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();

            // Uncheck all checkboxes and radio inputs
            filterInputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                }
            });

            // Reset Price Sliders
            priceSliders.forEach(slider => {
                slider.value = slider.max;
                const container = slider.closest('.price-slider-wrapper');
                if (container) {
                    const maxValDisplay = container.querySelector('.price-max-val');
                    const maxInput = container.querySelector('.price-max-input');
                    if (maxValDisplay) maxValDisplay.textContent = `₹${slider.max}`;
                    if (maxInput) maxInput.value = slider.max;
                }
            });

            // Reset Search Input if present
            const searchInput = document.getElementById('shopSearchInput');
            if (searchInput) {
                searchInput.value = '';
                const clearBtn = document.getElementById('shopSearchClear');
                if (clearBtn) clearBtn.style.display = 'none';
            }

            if (typeof onFilterChangeCallback === 'function') {
                onFilterChangeCallback();
            }
        });
    });
}
