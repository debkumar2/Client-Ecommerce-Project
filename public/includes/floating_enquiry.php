<!-- GLOBAL FLOATING RIGHT ENQUIRY BUTTON & POPUP MODAL -->
<style>
    /* Floating Right Vertical Enquiry Button */
    .floating-enquiry-trigger {
        position: fixed !important;
        right: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        z-index: 99990 !important;
        background: linear-gradient(135deg, #1b3b2b 0%, #0f261c 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 14px 0 0 14px !important;
        padding: 16px 12px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
        cursor: pointer !important;
        box-shadow: -4px 8px 24px rgba(0, 0, 0, 0.35) !important;
        border-left: 4px solid #d4af37 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .floating-enquiry-trigger:hover {
        padding-right: 18px !important;
        background: linear-gradient(135deg, #254e39 0%, #173829 100%) !important;
        box-shadow: -6px 12px 32px rgba(27, 59, 43, 0.5) !important;
    }

    .enquiry-btn-icon {
        width: 22px !important;
        height: 22px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: rgba(212, 175, 55, 0.2) !important;
        color: #d4af37 !important;
        border-radius: 50% !important;
        padding: 4px !important;
    }

    .enquiry-btn-text {
        writing-mode: vertical-rl !important;
        text-transform: uppercase !important;
        letter-spacing: 0.15em !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        font-family: 'Open Sans', sans-serif !important;
        color: #ffffff !important;
    }

    /* Modal Popup Overlay */
    .enquiry-modal-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(8, 18, 13, 0.82) !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        z-index: 99999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transition: all 0.3s ease !important;
        padding: 20px !important;
        box-sizing: border-box !important;
    }

    .enquiry-modal-overlay.active {
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Modal Container matching exact screenshot design */
    .enquiry-modal-card {
        background: #0f291b !important; /* Rich Dark Forest Green as shown in image */
        color: #ffffff !important;
        border-radius: 24px !important;
        padding: 44px !important;
        max-width: 960px !important;
        width: 100% !important;
        position: relative !important;
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        transform: scale(0.92) translateY(20px);
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        max-height: 90vh !important;
        overflow-y: auto !important;
        box-sizing: border-box !important;
    }

    /* Reset transparent background for wrapper elements inside modal */
    .enquiry-modal-card div,
    .enquiry-modal-card form,
    .enquiry-modal-grid,
    .enquiry-modal-grid > div {
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }

    .enquiry-modal-overlay.active .enquiry-modal-card {
        transform: scale(1) translateY(0) !important;
    }

    .enquiry-modal-close {
        position: absolute !important;
        top: 20px !important;
        right: 24px !important;
        background: rgba(255, 255, 255, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        font-size: 22px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        line-height: 1 !important;
        z-index: 10 !important;
    }

    .enquiry-modal-close:hover {
        background: rgba(255, 255, 255, 0.25) !important;
        transform: rotate(90deg) !important;
    }

    .enquiry-modal-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 44px !important;
        align-items: start !important;
    }

    .enquiry-form-input {
        width: 100% !important;
        padding: 14px 18px !important;
        border-radius: 10px !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        background: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
        font-size: 14px !important;
        margin-bottom: 14px !important;
        box-sizing: border-box !important;
        outline: none !important;
        transition: border 0.2s ease, background 0.2s ease !important;
        font-family: 'Open Sans', sans-serif !important;
    }

    .enquiry-form-input::placeholder {
        color: #8aa694 !important;
    }

    .enquiry-form-input:focus {
        border-color: #d4af37 !important;
        background: rgba(255, 255, 255, 0.14) !important;
    }

    .enquiry-submit-btn {
        width: 100% !important;
        padding: 16px !important;
        border-radius: 30px !important;
        border: none !important;
        background: #d4af37 !important;
        color: #0f291b !important;
        font-family: 'Open Sans', sans-serif !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 6px 20px rgba(212, 175, 55, 0.25) !important;
    }

    .enquiry-submit-btn:hover {
        background: #e5bf45 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.35) !important;
    }

    .enquiry-modal-eyebrow {
        font-size: 11px;
        font-weight: 800;
        color: #d4af37;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        display: block;
        margin-bottom: 6px;
    }

    .enquiry-modal-title {
        font-family: 'Merriweather', serif;
        font-size: 2.1rem;
        color: #ffffff !important;
        margin: 0 0 16px 0;
        line-height: 1.25;
        font-weight: 700;
    }

    .enquiry-modal-desc {
        font-size: 14px;
        color: #a4bea9 !important;
        line-height: 1.65;
        margin: 0 0 24px 0;
    }

    .enquiry-contact-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        font-size: 13.5px;
        color: #e0ece3 !important;
    }

    @media (max-width: 768px) {
        .enquiry-modal-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        .enquiry-modal-card {
            padding: 32px 24px !important;
            max-height: 92vh !important;
        }
        .enquiry-modal-title {
            font-size: 1.6rem !important;
            margin-bottom: 12px !important;
        }
        .enquiry-modal-desc {
            font-size: 13px !important;
            margin-bottom: 16px !important;
        }
    }

    @media (max-width: 575px) {
        .floating-enquiry-trigger {
            padding: 10px 8px !important;
            gap: 6px !important;
            border-radius: 10px 0 0 10px !important;
        }
        .enquiry-btn-icon {
            width: 16px !important;
            height: 16px !important;
            padding: 3px !important;
        }
        .enquiry-btn-icon svg {
            width: 10px !important;
            height: 10px !important;
        }
        .enquiry-btn-text {
            font-size: 9px !important;
            letter-spacing: 0.1em !important;
        }
    }

    /* ---- 475px: Compact Mobile Form ---- */
    @media (max-width: 475px) {
        .enquiry-modal-overlay {
            padding: 10px !important;
        }
        .enquiry-modal-card {
            padding: 20px 14px 14px 14px !important;
            border-radius: 18px !important;
            max-height: 95vh !important;
        }
        .enquiry-modal-close {
            top: 10px !important;
            right: 10px !important;
            width: 28px !important;
            height: 28px !important;
            font-size: 18px !important;
        }
        .enquiry-modal-grid {
            gap: 12px !important;
        }
        .enquiry-modal-eyebrow {
            font-size: 9px !important;
            letter-spacing: 0.1em !important;
            margin-bottom: 2px !important;
        }
        .enquiry-modal-title {
            font-size: 1.2rem !important;
            line-height: 1.25 !important;
            margin-bottom: 6px !important;
        }
        .enquiry-modal-desc {
            font-size: 11px !important;
            line-height: 1.4 !important;
            margin-bottom: 10px !important;
        }
        .enquiry-contact-list {
            gap: 6px !important;
            font-size: 11px !important;
        }
        .enquiry-contact-list span {
            font-size: 11px !important;
        }
        .enquiry-form-input {
            padding: 9px 12px !important;
            margin-bottom: 8px !important;
            font-size: 12px !important;
            border-radius: 6px !important;
        }
        .enquiry-textarea {
            min-height: 50px !important;
            padding: 8px 12px !important;
            margin-bottom: 10px !important;
        }
        .enquiry-submit-btn {
            padding: 10px 14px !important;
            font-size: 11px !important;
            border-radius: 20px !important;
        }
    }
</style>

<!-- FLOATING TRIGGER BUTTON -->
<button type="button" id="floating-enquiry-btn" class="floating-enquiry-trigger" onclick="openEnquiryModal()" aria-label="Quick Bulk Enquiry">
    <div class="enquiry-btn-icon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
    </div>
    <span class="enquiry-btn-text">ENQUIRY NOW</span>
</button>

<!-- POPUP MODAL -->
<div id="enquiry-modal-overlay" class="enquiry-modal-overlay" onclick="closeEnquiryModalOnBackdrop(event)">
    <div class="enquiry-modal-card">
        <button type="button" class="enquiry-modal-close" onclick="closeEnquiryModal()" aria-label="Close modal">&times;</button>
        
        <div class="enquiry-modal-grid">
            <!-- Left Info Column -->
            <div style="color: #ffffff; background: transparent !important;">
                <span class="enquiry-modal-eyebrow">QUICK BULK ENQUIRY</span>
                
                <h2 class="enquiry-modal-title">
                    Request Wholesale Best Price
                </h2>
                
                <p class="enquiry-modal-desc">
                    Looking for bulk quantities of Harad Powder, Arjuna Bark, or Commercial Solar LED Lights? Send us your requirement and our export team will send you the best quote within 2 hours.
                </p>

                <div class="enquiry-contact-list">
                    <div style="display: flex; align-items: center; gap: 8px; background: transparent !important;">
                        <span style="color: #d4af37;">📞</span>
                        <span><strong style="color: #ffffff;">Direct Call / WhatsApp:</strong> +91 93300 51702</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; background: transparent !important;">
                        <span style="color: #d4af37;">✉️</span>
                        <span><strong style="color: #ffffff;">Official Email:</strong> dipak_200607@yahoo.co.in</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 8px; background: transparent !important;">
                        <span style="color: #d4af37; margin-top: 2px;">📍</span>
                        <span><strong style="color: #ffffff;">Address:</strong> Na Kalikapur Berhampore Murshidabad, Bara Bazar, Kolkata - 742102</span>
                    </div>
                </div>
            </div>

            <!-- Right Form Column -->
            <div style="background: transparent !important;">
                <form id="popup-enquiry-form" onsubmit="handlePopupEnquirySubmit(event)" style="background: transparent !important;">
                    <input type="text" name="full_name" class="enquiry-form-input" placeholder="Your Full Name *" required>
                    <input type="email" name="email" class="enquiry-form-input" placeholder="Your Email Address *" required>
                    <input type="tel" name="phone" class="enquiry-form-input" placeholder="Phone / WhatsApp Number *" required>
                    
                    <select name="product" class="enquiry-form-input" style="color: #ffffff !important; background: #0b1e14 !important;" required>
                        <option value="" disabled selected style="color: #888;">Select Product of Interest *</option>
                        <option value="Harad Powder (99% Pure)" style="color: #fff; background: #0f291b;">Harad Powder (99% Pure)</option>
                        <option value="Dried Arjuna Bark (Medicine Cut)" style="color: #fff; background: #0f291b;">Dried Arjuna Bark (Medicine Cut)</option>
                        <option value="Natural Reetha Soap Nuts" style="color: #fff; background: #0f291b;">Natural Reetha Soap Nuts</option>
                        <option value="Antibacterial Neem Leaves/Powder" style="color: #fff; background: #0f291b;">Antibacterial Neem Leaves/Powder</option>
                        <option value="Dried Organic Tulsi Leaves" style="color: #fff; background: #0f291b;">Dried Organic Tulsi Leaves</option>
                        <option value="Solar LED Street Lights" style="color: #fff; background: #0f291b;">Solar LED Street Lights</option>
                        <option value="Custom Bulk Export Order" style="color: #fff; background: #0f291b;">Custom Bulk Export Order</option>
                    </select>
                    
                    <textarea name="details" class="enquiry-form-input enquiry-textarea" rows="2" placeholder="Requirement Details (Quantity, Destination, etc.)" style="resize: vertical;"></textarea>
                    
                    <button type="submit" class="enquiry-submit-btn">SUBMIT BULK ENQUIRY</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openEnquiryModal() {
        document.getElementById('enquiry-modal-overlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEnquiryModal() {
        document.getElementById('enquiry-modal-overlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    function closeEnquiryModalOnBackdrop(event) {
        if (event.target.id === 'enquiry-modal-overlay') {
            closeEnquiryModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEnquiryModal();
        }
    });

    async function handlePopupEnquirySubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('.enquiry-submit-btn');
        const originalText = submitBtn ? submitBtn.textContent : 'SUBMIT BULK ENQUIRY';

        const formData = new FormData(form);
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'SUBMITTING...';
        }

        try {
            const response = await fetch('<?= url('api/submit_enquiry.php') ?>', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('Thank you ' + form.full_name.value + '! Your bulk enquiry has been received and stored in our database. Our sales export team will contact you shortly.');

                const name = form.full_name.value;
                const phone = form.phone.value;
                const product = form.product.value;
                const details = form.details.value;
                const waText = encodeURIComponent(`Hello Biswas Enterprise, I submitted a bulk enquiry for ${product}.\nName: ${name}\nPhone: ${phone}\nDetails: ${details}`);
                
                if (confirm('Would you also like to send this inquiry directly to WhatsApp (+91 93300 51702)?')) {
                    window.open(`https://api.whatsapp.com/send?phone=919330051702&text=${waText}`, '_blank');
                }

                form.reset();
                closeEnquiryModal();
            } else {
                alert('Error: ' + (result.message || 'Failed to submit enquiry.'));
            }
        } catch (error) {
            console.error('Enquiry Submission Error:', error);
            alert('Something went wrong. Please try again or contact us via WhatsApp.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    }
</script>

<!-- LIGHTWEIGHT GPU-ACCELERATED SMOOTH SCROLL ENHANCEMENT -->
<script src="https://cdn.jsdelivr.net/npm/smoothscroll-polyfill@0.4.4/dist/smoothscroll.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof smoothscroll !== 'undefined') {
            smoothscroll.polyfill();
        }
        
        // Smooth scroll for all internal anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId && targetId !== '#') {
                    const targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        e.preventDefault();
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    });
</script>


