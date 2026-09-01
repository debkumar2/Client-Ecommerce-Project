/**
 * PRODUCT CARD COMPONENT LOGIC
 * Manages Wishlist UI toggles, Quick View modal population, and Add to Cart visual states.
 */

export function initProductCards() {
    // 1. Wishlist Button Interactivity (Instant LocalStorage + MySQL DB Sync)
    const wishlistBtns = document.querySelectorAll('.btn-wishlist');

    // Helper: Get stored local wishlist IDs
    function getLocalWishlist() {
        try {
            return JSON.parse(localStorage.getItem('biswas_wishlist_ids') || '[]');
        } catch (e) {
            return [];
        }
    }

    // Helper: Save local wishlist IDs
    function setLocalWishlist(ids) {
        try {
            localStorage.setItem('biswas_wishlist_ids', JSON.stringify(ids.map(String)));
        } catch (e) {}
    }

    // Helper: Highlight heart icon for given button
    function setHeartState(btn, isActive) {
        const svg = btn.querySelector('svg');
        if (isActive) {
            btn.classList.add('active');
            if (svg) svg.setAttribute('fill', 'currentColor');
            btn.style.color = '#e53e3e';
        } else {
            btn.classList.remove('active');
            if (svg) svg.setAttribute('fill', 'none');
            btn.style.color = '';
        }
    }

    // Phase 1: Immediately highlight hearts from LocalStorage on page render
    const localIds = getLocalWishlist();
    wishlistBtns.forEach(btn => {
        const card = btn.closest('.product-card');
        const productId = card ? (card.dataset.id || btn.dataset.id) : btn.dataset.id;
        if (productId && localIds.includes(String(productId))) {
            setHeartState(btn, true);
        }
    });

    if (localIds.length > 0) {
        updateWishlistHeaderBadge(localIds.length);
    }

    // Phase 2: Sync with MySQL DB via API endpoint
    const apiUrl = '/Client-Ecommerce-Project/public/api/wishlist.php';
    fetch(apiUrl)
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.items)) {
                const serverItemIds = data.items.map(String);
                setLocalWishlist(serverItemIds);

                wishlistBtns.forEach(btn => {
                    const card = btn.closest('.product-card');
                    const productId = card ? (card.dataset.id || btn.dataset.id) : btn.dataset.id;
                    if (productId) {
                        setHeartState(btn, serverItemIds.includes(String(productId)));
                    }
                });
                updateWishlistHeaderBadge(data.count);
            }
        })
        .catch(() => {});

    // Phase 3: Handle click toggles
    wishlistBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const card = btn.closest('.product-card');
            const productId = card ? (card.dataset.id || btn.dataset.id) : btn.dataset.id;
            if (!productId) return;

            btn.disabled = true;

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle', product_id: productId })
                });

                const result = await response.json();

                if (result.require_login) {
                    if (typeof window.showToastify === 'function') {
                        window.showToastify(result.message, 'warning');
                    } else {
                        alert(result.message);
                    }
                    setTimeout(() => {
                        window.location.href = '/Client-Ecommerce-Project/public/login.php';
                    }, 1200);
                    return;
                }

                if (result.success) {
                    setHeartState(btn, result.is_in_wishlist);

                    // Update LocalStorage array
                    let currentLocal = getLocalWishlist();
                    if (result.is_in_wishlist) {
                        if (!currentLocal.includes(String(productId))) currentLocal.push(String(productId));
                    } else {
                        currentLocal = currentLocal.filter(id => String(id) !== String(productId));
                    }
                    setLocalWishlist(currentLocal);

                    if (typeof window.showToastify === 'function') {
                        window.showToastify(result.message, result.is_in_wishlist ? 'success' : 'info');
                    }

                    updateWishlistHeaderBadge(result.count);
                } else {
                    if (typeof window.showToastify === 'function') {
                        window.showToastify(result.message || 'Error updating wishlist.', 'error');
                    }
                }
            } catch (err) {
                console.error('Wishlist error:', err);
            } finally {
                btn.disabled = false;
            }
        });
    });

    function updateWishlistHeaderBadge(count) {
        const badges = document.querySelectorAll('.wishlist-badge');
        badges.forEach(b => {
            b.textContent = count;
            b.style.display = count > 0 ? 'inline-flex' : 'none';
        });
    }

    // 2. Quick View Button Trigger
    const quickViewBtns = document.querySelectorAll('.btn-quick-view');
    const modalBackdrop = document.getElementById('quickViewModal');

    if (quickViewBtns.length > 0 && modalBackdrop) {
        quickViewBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const card = btn.closest('.product-card');
                if (!card) return;

                // Extract data attributes
                const title = card.dataset.title || 'Ayurvedic Herbal Product';
                const category = card.dataset.category || 'Herbal';
                const price = card.dataset.price || '₹0';
                const originalPrice = card.dataset.originalPrice || '';
                const image = card.dataset.image || '';
                const description = card.dataset.description || 'Authentic herbal formulation crafted using traditional Biswas Enterprise methods for optimal health and vitality.';
                const stock = card.dataset.stock || 'In Stock';

                // Populate Modal Elements
                const modalImg = modalBackdrop.querySelector('#quickViewImg');
                const modalTitle = modalBackdrop.querySelector('#quickViewTitle');
                const modalCategory = modalBackdrop.querySelector('#quickViewCategory');
                const modalPrice = modalBackdrop.querySelector('#quickViewPrice');
                const modalOrigPrice = modalBackdrop.querySelector('#quickViewOrigPrice');
                const modalDesc = modalBackdrop.querySelector('#quickViewDesc');
                const modalStock = modalBackdrop.querySelector('#quickViewStock');

                if (modalImg) modalImg.src = image;
                if (modalTitle) modalTitle.textContent = title;
                if (modalCategory) modalCategory.textContent = category;
                if (modalPrice) modalPrice.textContent = price;
                if (modalOrigPrice) {
                    modalOrigPrice.textContent = originalPrice;
                    modalOrigPrice.style.display = originalPrice ? 'inline' : 'none';
                }
                if (modalDesc) modalDesc.textContent = description;
                if (modalStock) modalStock.textContent = stock;

                // Populate Product Details Specifications Table
                const specsGrid = modalBackdrop.querySelector('#quickViewSpecsGrid');
                if (specsGrid) {
                    let specsObj = {};
                    try {
                        specsObj = card.dataset.specs ? JSON.parse(card.dataset.specs) : {};
                    } catch (e) {
                        specsObj = {};
                    }

                    if (!specsObj || Object.keys(specsObj).length === 0) {
                        specsObj = {
                            "Packaging Type": "Plastic Bags, Jute Bags",
                            "Texture": "Rough",
                            "Usage": "Herbal Medicine, Ayurvedic Remedies",
                            "Benefit": "Heart Health, Blood Pressure Regulation",
                            "Storage": "Cool, Dry Place",
                            "Shelf Life": "1-2 Years",
                            "Processing Method": "Sun-dried"
                        };
                    }

                    specsGrid.innerHTML = Object.entries(specsObj).map(([key, val]) => `
                        <div class="spec-row-item">
                            <span class="spec-label-title">${key}</span>
                            <span class="spec-value-text">${val}</span>
                        </div>
                    `).join('');
                }

                // Handle 'Yes! I am interested' CTA click -> Open Quick Quote / Enquiry Modal
                const btnInterested = modalBackdrop.querySelector('#btnInterested');
                if (btnInterested) {
                    btnInterested.onclick = (e) => {
                        e.preventDefault();
                        closeQuickViewModal();
                        openEnquiryModal({
                            title: title,
                            image: image,
                            price: price,
                            rawPrice: card.dataset.rawPrice,
                            category: category
                        });
                    };
                }

                // Open Modal
                modalBackdrop.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close Modal Handlers
        const closeBtn = modalBackdrop.querySelector('#closeQuickView');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeQuickViewModal);
        }

        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) {
                closeQuickViewModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalBackdrop.classList.contains('active')) {
                closeQuickViewModal();
                closeEnquiryModal();
            }
        });
    }

    function closeQuickViewModal() {
        if (!modalBackdrop) return;
        modalBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    // --- ENQUIRY MODAL ("Get a Quick Quote") LOGIC ---
    const enquiryModal = document.getElementById('enquiryModal');
    const closeEnquiryBtn = document.getElementById('closeEnquiryModal');
    const quickQuoteForm = document.getElementById('quickQuoteForm');

    function openEnquiryModal(productData) {
        if (!enquiryModal) return;

        const titleEl = enquiryModal.querySelector('#enquiryProductTitle');
        const imgEl = enquiryModal.querySelector('#enquiryProductImg');
        const priceDisplayEl = enquiryModal.querySelector('#enquiryPriceDisplay');
        const moqDisplayEl = enquiryModal.querySelector('#enquiryMoqDisplay');
        const unitInput = enquiryModal.querySelector('#enquiryUnit');

        if (titleEl) titleEl.textContent = productData.title;
        if (imgEl) imgEl.src = productData.image;

        const rawPrice = parseFloat(productData.rawPrice || '500');
        const lowPrice = Math.round(rawPrice * 0.95);
        const highPrice = Math.round(rawPrice * 1.15);

        let defaultUnit = 'Kilogram';
        let defaultMoq = '50 Kilogram';

        if (productData.category && productData.category.toLowerCase().includes('renewable')) {
            defaultUnit = 'Set / Piece';
            defaultMoq = '5 Sets';
        } else if (productData.category && productData.category.toLowerCase().includes('wellness')) {
            defaultUnit = 'Bottles';
            defaultMoq = '20 Bottles';
        }

        if (priceDisplayEl) priceDisplayEl.textContent = `₹ ${lowPrice}.00 - ${highPrice}.00 / ${defaultUnit}`;
        if (moqDisplayEl) moqDisplayEl.textContent = defaultMoq;
        if (unitInput) unitInput.value = defaultUnit;

        enquiryModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEnquiryModal() {
        if (!enquiryModal) return;
        enquiryModal.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (closeEnquiryBtn) {
        closeEnquiryBtn.addEventListener('click', closeEnquiryModal);
    }

    if (enquiryModal) {
        enquiryModal.addEventListener('click', (e) => {
            if (e.target === enquiryModal) closeEnquiryModal();
        });
    }

    // Toggle unit input edit focus
    const btnEditUnit = document.getElementById('btnEditUnit');
    if (btnEditUnit) {
        btnEditUnit.addEventListener('click', () => {
            const unitInput = document.getElementById('enquiryUnit');
            if (unitInput) {
                unitInput.focus();
                unitInput.select();
            }
        });
    }

    if (quickQuoteForm) {
        quickQuoteForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = quickQuoteForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Sending Enquiry...</span>';

            setTimeout(() => {
                showToast('Enquiry Sent Successfully! Our sales representative will contact your mobile number shortly.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                quickQuoteForm.reset();
                closeEnquiryModal();
            }, 800);
        });
    }

    // 3. Add to Cart Interactivity
    const addCartBtns = document.querySelectorAll('.btn-add-cart');
    addCartBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (btn.disabled) return;

            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="spinner-icon" style="width:16px;height:16px;animation:spin 0.6s linear infinite" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"></path>
                </svg>
                <span>Adding...</span>
            `;

            setTimeout(() => {
                btn.innerHTML = `
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Added!</span>
                `;
                btn.style.backgroundColor = 'var(--secondary)';
                btn.style.borderColor = 'var(--secondary)';
                btn.style.color = 'var(--surface)';

                // Update Header Cart Count Badge visually
                const cartBadge = document.querySelector('.cart-badge');
                if (cartBadge) {
                    let currentCount = parseInt(cartBadge.textContent || '0', 10);
                    cartBadge.textContent = currentCount + 1;
                }

                showToast('Item added to cart!');

                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    btn.style.backgroundColor = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                }, 2000);
            }, 600);
        });
    });
}

// Simple Toast Notification Helper
function showToast(message, type = 'success') {
    if (typeof window.showToastify === 'function') {
        window.showToastify(message, type);
        return;
    }
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:999999;display:flex;flex-direction:column;gap:10px;align-items:center;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = 'background:#10b981;color:#ffffff;padding:13px 22px;border-radius:12px;font-size:14px;font-weight:600;box-shadow:0 12px 32px rgba(0,0,0,0.18);transition:all 0.3s ease;pointer-events:auto;display:flex;align-items:center;gap:8px;';
    toast.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
