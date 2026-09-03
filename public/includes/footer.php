<?php
/**
 * PROMINENT & CLEAN FOOTER COMPONENT
 * Biswas Enterprise Official Footer Include
 */
$founderName = $companyDetails['founder'] ?? 'Mr. Dipak Biswas';
$gstNo = $companyDetails['gst'] ?? '19AGXPB1978M1ZI';
$companyEmail = $companyDetails['email'] ?? 'dipak_200607@yahoo.co.in';
$companyPhone = $companyDetails['phone'] ?? '+91 93300 51702';
$companyWhatsapp = $companyDetails['whatsapp'] ?? '+919330051702';
$companyLocation = $companyDetails['location'] ?? 'Na Kalikapur Berhampore Murshidabad, Bara Bazar, Kolkata, West Bengal - 742102';

// Render Brand Preloader Component
require_once __DIR__ . '/loader.php';
?>
<footer class="site-footer" role="contentinfo">

    <!-- 1. TOP TRUST BADGES BAR -->
    <div class="footer-trust-bar">
        <div class="container">
            <div class="footer-trust-grid">
                
                <div class="footer-trust-item">
                    <div class="footer-trust-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div>
                        <div class="footer-trust-title">100% Authentic Sourcing</div>
                        <div class="footer-trust-desc">Shade-cured herbal raw materials & botanical powders.</div>
                    </div>
                </div>

                <div class="footer-trust-item">
                    <div class="footer-trust-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </div>
                    <div>
                        <div class="footer-trust-title">Global & Pan-India Shipping</div>
                        <div class="footer-trust-desc">Wholesale export & domestic supply from Kolkata port.</div>
                    </div>
                </div>

                <div class="footer-trust-item">
                    <div class="footer-trust-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    </div>
                    <div>
                        <div class="footer-trust-title">Solar & Botanical Catalog</div>
                        <div class="footer-trust-desc">Commercial integrated solar street lights & PV systems.</div>
                    </div>
                </div>

                <div class="footer-trust-item">
                    <div class="footer-trust-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div>
                        <div class="footer-trust-title">Government Registered</div>
                        <div class="footer-trust-desc">Official GST registered supplier (<?= htmlspecialchars($gstNo) ?>).</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 2. MAIN FOOTER BODY -->
    <div class="footer-main-body">
        <div class="container">
            <div class="footer-grid">

                <!-- Brand Column -->
                <div class="footer-brand">
                    <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                        <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                    </a>
                    <p class="footer-desc">
                        Prominent Exporter, Supplier & Wholesale Trader of Harad Powder, Arjuna Bark, Neem Leaf, Reetha Soap Nuts, and Commercial Solar Solutions based in Kolkata, India.
                    </p>
                    <div class="footer-gst-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>GSTIN: <?= htmlspecialchars($gstNo) ?></span>
                    </div>
                </div>

                <!-- Products Column -->
                <div>
                    <h4 class="footer-heading">OUR PRODUCTS</h4>
                    <ul class="footer-links">
                        <li><a href="<?= url('shop?category=arjuna-bark') ?>">▸ Arjuna Bark Collection</a></li>
                        <li><a href="<?= url('shop?category=herbs-powder') ?>">▸ Harad Powder & Powders</a></li>
                        <li><a href="<?= url('shop?category=dried-herbs') ?>">▸ Neem Leaves & Reetha Shells</a></li>
                        <li><a href="<?= url('shop?category=renewable-energy') ?>">▸ Solar LED Street Lights</a></li>
                        <li><a href="<?= url('shop?category=renewable-energy') ?>">▸ Solar Batteries & PV Panels</a></li>
                    </ul>
                </div>

                <!-- Quick Navigation -->
                <div>
                    <h4 class="footer-heading">QUICK LINKS</h4>
                    <ul class="footer-links">
                        <li><a href="<?= url() ?>">▸ Home</a></li>
                        <li><a href="<?= url('about') ?>">▸ About Biswas Enterprise</a></li>
                        <li><a href="<?= url('shop') ?>">▸ Full Shop Catalog</a></li>
                        <li><a href="<?= url('contact') ?>">▸ Contact & Location</a></li>
                        <li><a href="<?= url('blog') ?>">▸ Blog Journal</a></li>
                    </ul>
                </div>

                <!-- Direct Wholesale Contact Card -->
                <div>
                    <h4 class="footer-heading">HEADQUARTERS</h4>
                    <div class="footer-contact-card">
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span>Bara Bazar, Kolkata, West Bengal - 742102</span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <span><?= htmlspecialchars($companyPhone) ?></span>
                        </div>
                        <div class="footer-contact-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span><?= htmlspecialchars($companyEmail) ?></span>
                        </div>
                        <a href="https://wa.me/<?= str_replace(['+', ' '], '', $companyWhatsapp) ?>?text=Hello%20Biswas%20Enterprise,%20I%20want%20a%20bulk%20quote" target="_blank" rel="noopener" class="footer-cta-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                            <span>WHATSAPP BULK QUOTE</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. FOOTER BOTTOM -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-flex">
                <div>
                    &copy; <?= date('Y') ?> <strong>Biswas Enterprise</strong>. All Rights Reserved. Exporter & Wholesale Supplier from Kolkata, West Bengal.
                </div>
                <div>
                    <a href="#" onclick="window.scrollTo({top:0, behavior:'smooth'}); return false;" class="footer-back-to-top">
                        ↑ BACK TO TOP
                    </a>
                </div>
            </div>
        </div>
    </div>

</footer>

<!-- Mobile Navigation Drawer Component -->
<?php if (!isset($mobileDrawerRendered)): $mobileDrawerRendered = true; ?>
<div class="mobile-drawer" id="mobile-drawer" aria-hidden="true">
    <div class="drawer-header">
        <a href="<?= url() ?>" class="drawer-logo" aria-label="Biswas Enterprise Home">
            <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
        </a>
        <button type="button" class="drawer-close" id="drawer-close" aria-label="Close menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    <div class="drawer-body">
        <nav class="drawer-navigation" aria-label="Mobile Navigation">
            <ul>
                <li><a href="<?= url() ?>" class="drawer-link">Home</a></li>
                <li><a href="<?= url('shop') ?>" class="drawer-link">Shop</a></li>
                <li><a href="<?= url('about') ?>" class="drawer-link">About Us</a></li>
                <li><a href="<?= url('contact') ?>" class="drawer-link">Contact</a></li>
                <li><a href="<?= url('blog') ?>" class="drawer-link">Blog</a></li>
            </ul>
        </nav>
    </div>
</div>
<div class="drawer-overlay" id="drawer-overlay" aria-hidden="true"></div>

<script>
(function() {
    function setupDrawer() {
        const toggle = document.getElementById('mobile-toggle');
        const drawer = document.getElementById('mobile-drawer');
        const overlay = document.getElementById('drawer-overlay');
        const closeBtn = document.getElementById('drawer-close');

        if (drawer && overlay) {
            // Ensure menu starts closed on page load
            drawer.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';

            function closeMenu() {
                drawer.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            function openMenu() {
                drawer.classList.add('open');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openMenu();
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeMenu();
                });
            }

            overlay.addEventListener('click', closeMenu);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && drawer.classList.contains('open')) {
                    closeMenu();
                }
            });

            // Close on drawer link click
            const links = drawer.querySelectorAll('a');
            links.forEach(function(link) {
                link.addEventListener('click', closeMenu);
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupDrawer);
    } else {
        setupDrawer();
    }
})();
</script>
<?php endif; ?>
