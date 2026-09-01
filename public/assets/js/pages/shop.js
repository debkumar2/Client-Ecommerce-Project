/**
 * SHOP PAGE MASTER JS MODULE
 * Coordinates search, filtering, sorting, product card interactivity, and empty states.
 */

import { initProductCards } from '../components/product-card.js';
import { initFilterSidebar } from '../components/filter-sidebar.js';
import { initShopToolbar } from '../components/shop-toolbar.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Product Cards (Wishlist, Quick View, Add to Cart)
    initProductCards();

    // Cache DOM Elements
    const productsGrid = document.getElementById('productsGrid');
    const allCards = productsGrid ? Array.from(productsGrid.querySelectorAll('.product-card')) : [];
    const showingCountEl = document.getElementById('showingCount');
    const totalCountEl = document.getElementById('totalCount');
    const emptyState = document.getElementById('shopEmptyState');

    // Parse URL query parameters for pre-selected category filter
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = (urlParams.get('category') || '').toLowerCase().trim();

    if (categoryParam) {
        const catInputs = document.querySelectorAll('.filter-category-input');
        catInputs.forEach(input => {
            const val = input.value.toLowerCase().trim();
            const slug = (input.dataset.slug || '').toLowerCase().trim();
            const valHyphenated = val.replace(/\s+/g, '-');

            if (val === categoryParam || slug === categoryParam || valHyphenated === categoryParam || categoryParam.includes(slug) || slug.includes(categoryParam)) {
                input.checked = true;
            }
        });
    }

    // Master Filter & Sort Execution Function
    function applyFiltersAndSort() {
        if (!productsGrid || allCards.length === 0) return;

        // 1. Get Search Query
        const searchInput = document.getElementById('shopSearchInput');
        const searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';

        // 2. Get Selected Categories
        const categoryInputs = Array.from(document.querySelectorAll('.filter-category-input:checked'));
        const selectedCategories = categoryInputs.map(input => ({
            name: input.value.toLowerCase().trim(),
            slug: (input.dataset.slug || '').toLowerCase().trim()
        }));

        // 3. Get Max Price
        const priceSlider = document.querySelector('.price-range-slider');
        const maxPrice = priceSlider ? parseFloat(priceSlider.value) : 100000;

        // 4. Get Availability Filters
        const inStockOnly = document.getElementById('filterInStock')?.checked || false;
        const outOfStockOnly = document.getElementById('filterOutOfStock')?.checked || false;

        // 5. Get Rating Filter
        const ratingRadio = document.querySelector('input[name="rating-filter"]:checked');
        const minRating = ratingRadio ? parseFloat(ratingRadio.value) : 0;

        // 6. Get Selected Brands
        const brandInputs = Array.from(document.querySelectorAll('.filter-brand-input:checked'));
        const selectedBrands = brandInputs.map(input => input.value.toLowerCase().trim());

        // Filter Cards
        let visibleCards = allCards.filter(card => {
            const title = (card.dataset.title || '').toLowerCase().trim();
            const category = (card.dataset.category || '').toLowerCase().trim();
            const price = parseFloat(card.dataset.rawPrice || '0');
            const cardRating = parseFloat(card.dataset.rating || '5');
            const isOutOfStock = card.classList.contains('is-out-of-stock');
            const brand = (card.dataset.brand || '').toLowerCase().trim();

            // Search filter
            if (searchQuery && !title.includes(searchQuery) && !category.includes(searchQuery)) {
                return false;
            }

            // Category filter
            if (selectedCategories.length > 0) {
                const cardCat = category;
                const cardCatClean = cardCat.replace(/[^a-z0-9]/g, '');
                
                const isCatMatched = selectedCategories.some(sc => {
                    const scNameClean = sc.name.replace(/[^a-z0-9]/g, '');
                    const scSlugClean = sc.slug.replace(/[^a-z0-9]/g, '');
                    
                    return cardCat === sc.name || 
                           cardCat.includes(sc.name) || 
                           sc.name.includes(cardCat) ||
                           (scNameClean && cardCatClean.includes(scNameClean)) ||
                           (scSlugClean && cardCatClean.includes(scSlugClean));
                });

                if (!isCatMatched) {
                    return false;
                }
            }

            // Price filter
            if (price > maxPrice) {
                return false;
            }

            // Availability filter
            if (inStockOnly && isOutOfStock) return false;
            if (outOfStockOnly && !isOutOfStock) return false;

            // Rating filter
            if (minRating > 0 && cardRating < minRating) {
                return false;
            }

            // Brand filter
            if (selectedBrands.length > 0) {
                const isBrandMatched = selectedBrands.some(sb => brand.includes(sb) || sb.includes(brand));
                if (!isBrandMatched) {
                    return false;
                }
            }

            return true;
        });

        // 7. Sort Cards
        const sortSelect = document.getElementById('shopSortSelect');
        const sortVal = sortSelect ? sortSelect.value : 'featured';

        visibleCards.sort((a, b) => {
            const priceA = parseFloat(a.dataset.rawPrice || '0');
            const priceB = parseFloat(b.dataset.rawPrice || '0');
            const ratingA = parseFloat(a.dataset.rating || '0');
            const ratingB = parseFloat(b.dataset.rating || '0');

            if (sortVal === 'price-low') return priceA - priceB;
            if (sortVal === 'price-high') return priceB - priceA;
            if (sortVal === 'rating') return ratingB - ratingA;
            return 0; // default featured
        });

        // Update DOM Visibility
        allCards.forEach(card => card.style.display = 'none');
        visibleCards.forEach(card => {
            card.style.display = 'flex';
            productsGrid.appendChild(card); // Re-append for sorted order
        });

        // Update Counts
        if (showingCountEl) showingCountEl.textContent = visibleCards.length;
        if (totalCountEl) totalCountEl.textContent = allCards.length;

        // Toggle Empty State UI
        if (emptyState) {
            if (visibleCards.length === 0) {
                emptyState.style.display = 'block';
                productsGrid.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                productsGrid.style.display = 'grid';
            }
        }
    }

    // Initialize Filter Sidebar with change callback
    initFilterSidebar(() => {
        applyFiltersAndSort();
    });

    // Initialize Shop Toolbar with search & sort callbacks
    initShopToolbar(
        (query) => applyFiltersAndSort(),
        (sortOption) => applyFiltersAndSort()
    );

    // Also listen directly on rating radio inputs
    document.querySelectorAll('input[name="rating-filter"]').forEach(radio => {
        radio.addEventListener('change', () => applyFiltersAndSort());
    });

    // Run initial filter check on load
    applyFiltersAndSort();
});
