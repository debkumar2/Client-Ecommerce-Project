/**
 * SHOP TOOLBAR COMPONENT
 * Handles debounced live search, sorting logic, and view mode switching (Grid vs List).
 */

export function initShopToolbar(onSearchCallback, onSortCallback) {
    // 1. Search Input Debouncing & Clear Handler
    const searchInput = document.getElementById('shopSearchInput');
    const searchClear = document.getElementById('shopSearchClear');
    const searchSpinner = document.getElementById('shopSearchSpinner');
    let debounceTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();

            // Toggle Clear Button
            if (searchClear) {
                searchClear.style.display = query.length > 0 ? 'flex' : 'none';
            }

            // Show Spinner
            if (searchSpinner) searchSpinner.style.display = 'block';

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (searchSpinner) searchSpinner.style.display = 'none';
                if (typeof onSearchCallback === 'function') {
                    onSearchCallback(query);
                }
            }, 300);
        });

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchClear.style.display = 'none';
                if (searchSpinner) searchSpinner.style.display = 'none';
                if (typeof onSearchCallback === 'function') {
                    onSearchCallback('');
                }
            });
        }
    }

    // 2. Sort Dropdown Change Handler
    const sortSelect = document.getElementById('shopSortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            const sortOption = e.target.value;
            if (typeof onSortCallback === 'function') {
                onSortCallback(sortOption);
            }
        });
    }

    // 3. Grid vs List View Mode Toggle
    const gridViewBtn = document.getElementById('viewGridBtn');
    const listViewBtn = document.getElementById('viewListBtn');
    const productsGrid = document.getElementById('productsGrid');

    if (gridViewBtn && listViewBtn && productsGrid) {
        gridViewBtn.addEventListener('click', () => {
            productsGrid.classList.remove('products-list');
            gridViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        });

        listViewBtn.addEventListener('click', () => {
            productsGrid.classList.add('products-list');
            listViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
        });
    }
}
