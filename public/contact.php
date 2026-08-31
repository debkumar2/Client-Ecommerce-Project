<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Biswas Enterprise Kolkata - Export Enquiries Welcome</title>
    <meta name="description" content="Contact Biswas Enterprise for bulk export enquiries. Reach Mr. Dipak Biswas at Na Kalikapur Berhampore Murshidabad, Kolkata, West Bengal - 742102.">
    <link rel="canonical" href="<?= url('contact') ?>">
    <meta property="og:title" content="Contact Us | Biswas Enterprise">
    <meta property="og:description" content="Reach out to Biswas Enterprise for herbal product and renewable energy enquiries.">
    <meta property="og:url" content="<?= url('contact') ?>">
    <meta property="og:type" content="website">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "mainEntity": {
        "@type": "Organization",
        "name": "Biswas Enterprise",
        "contactPoint": {
          "@type": "ContactPoint",
          "contactType": "customer support",
          "email": "dipak_200607@yahoo.co.in",
          "availableLanguage": ["English", "Hindi", "Bengali"]
        },
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "Na Kalikapur Berhampore Murshidabad, Bara Bazar",
          "addressLocality": "Kolkata",
          "addressRegion": "West Bengal",
          "postalCode": "742102",
          "addressCountry": "IN"
        }
      }
    }
    </script>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body>

    <!-- ANNOUNCEMENT BAR -->
    <div class="announcement-bar" role="region" aria-label="Announcement">
        <div class="container">
            <div class="announcement-content">
                <span class="announcement-item">ESTABLISHED 2023 • KOLKATA, WEST BENGAL</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">GST NO: 19AGXPB1978M1ZI</span>
                <span class="announcement-separator">•</span>
                <span class="announcement-item">BULK &amp; EXPORT ENQUIRIES WELCOME</span>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="site-header" role="banner">
        <div class="container">
            <div class="header-inner">
                <button class="mobile-toggle" id="mobile-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <a href="<?= url() ?>" class="site-logo" aria-label="Biswas Enterprise Home">
                    <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                </a>
                <nav class="main-navigation" role="navigation" aria-label="Main menu">
                    <ul>
                        <li><a href="<?= url() ?>" class="nav-link">Home</a></li>
                        <li><a href="<?= url('shop') ?>" class="nav-link">Shop</a></li>
                        <li><a href="<?= url('about') ?>" class="nav-link">About</a></li>
                        <li><a href="<?= url('contact') ?>" class="nav-link active">Contact</a></li>
                        <li><a href="<?= url('blog') ?>" class="nav-link">Blog</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <a href="<?= url('wishlist') ?>" class="icon-btn" aria-label="View Wishlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </a>
                    <a href="<?= url('account') ?>" class="icon-btn" aria-label="My Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </a>
                    <a href="<?= url('cart') ?>" class="icon-btn" aria-label="View Cart">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span class="cart-badge">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- MOBILE DRAWER -->
    <div class="drawer-overlay" id="drawer-overlay"></div>
    <div class="mobile-drawer" id="mobile-drawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation">
        <div class="drawer-header">
            <a href="<?= url() ?>" class="site-logo"><img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img"></a>
            <button class="drawer-close" id="drawer-close" aria-label="Close menu">&times;</button>
        </div>
        <nav class="mobile-nav-links">
            <a href="<?= url() ?>">Home</a>
            <a href="<?= url('shop') ?>">Shop</a>
            <a href="<?= url('about') ?>">About Us</a>
            <a href="<?= url('contact') ?>">Contact</a>
            <a href="<?= url('blog') ?>">Blog Journal</a>
        </nav>
    </div>

    <main id="main-content">

        <!-- HERO -->
        <section class="contact-hero">
            <div class="container">
                <div class="contact-hero-inner">
                    <span class="contact-hero-label">Get In Touch</span>
                    <h1>Contact Biswas Enterprise</h1>
                    <p class="contact-hero-lead">We welcome bulk orders, export enquiries, and partnership opportunities from importers, distributors and wholesalers worldwide.</p>
                    <div class="contact-hero-chips">
                        <span class="contact-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            dipak_200607@yahoo.co.in
                        </span>
                        <span class="contact-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Kolkata, West Bengal - 742102
                        </span>
                        <span class="contact-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            +91 9330051702
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- MAIN CONTACT SECTION -->
        <section class="contact-main-section">
            <div class="container">
                <div class="contact-grid">

                    <!-- LEFT: INFO PANEL -->
                    <div class="contact-info-panel">
                        <div>
                            <h2 class="contact-info-title">We'd Love to Hear From You</h2>
                            <p class="contact-info-subtitle">Reach out for bulk supply, export queries, partnerships, or any product information. Our team responds promptly.</p>
                        </div>

                        <div class="contact-detail-card">
                            <div class="contact-detail-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="contact-detail-text">
                                <h4>Contact Person</h4>
                                <p>Mr. Dipak Biswas</p>
                                <p style="font-size:0.82rem;color:var(--muted)">Proprietor, Biswas Enterprise</p>
                            </div>
                        </div>

                        <div class="contact-detail-card">
                            <div class="contact-detail-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <div class="contact-detail-text">
                                <h4>Office Address</h4>
                                <p>Na Kalikapur Berhampore Murshidabad,<br>Bara Bazar, Kolkata,<br>West Bengal, India – 742102</p>
                            </div>
                        </div>

                        <div class="contact-detail-card">
                            <div class="contact-detail-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </div>
                            <div class="contact-detail-text">
                                <h4>Email Address</h4>
                                <a href="mailto:dipak_200607@yahoo.co.in">dipak_200607@yahoo.co.in</a>
                            </div>
                        </div>

                        <div class="contact-detail-card">
                            <div class="contact-detail-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                            </div>
                            <div class="contact-detail-text">
                                <h4>Web Presence</h4>
                                <a href="https://www.biswas-enterprise.co.in" target="_blank" rel="noopener">www.biswas-enterprise.co.in</a>
                            </div>
                        </div>

                        <div class="gst-badge-block">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <div>
                                <span>GST Registered Business</span>
                                <small>GST NO: 19AGXPB1978M1ZI</small>
                            </div>
                        </div>

                        <a href="https://api.whatsapp.com/send?phone=919330051702&text=Hello%21+I+found+your+website+and+am+interested+in+your+products." target="_blank" rel="noopener" class="whatsapp-cta" aria-label="Chat on WhatsApp">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            Chat on WhatsApp — +91 9330051702
                        </a>
                    </div>

                    <!-- RIGHT: ENQUIRY FORM -->
                    <div class="contact-form-panel">
                        <h2 class="contact-form-heading">Send an Enquiry</h2>
                        <p class="contact-form-subhead">Fill in the form and we will get back to you within 24 hours.</p>

                        <form id="contact-enquiry-form" novalidate>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact-name">Full Name <span class="req">*</span></label>
                                    <input type="text" id="contact-name" name="name" placeholder="Mr. / Ms. Your Name" required autocomplete="name">
                                </div>
                                <div class="form-group">
                                    <label for="contact-company">Company / Organisation</label>
                                    <input type="text" id="contact-company" name="company" placeholder="Your Business Name" autocomplete="organization">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact-email">Email Address <span class="req">*</span></label>
                                    <input type="email" id="contact-email" name="email" placeholder="you@example.com" required autocomplete="email">
                                </div>
                                <div class="form-group">
                                    <label for="contact-phone">Phone / WhatsApp</label>
                                    <input type="tel" id="contact-phone" name="phone" placeholder="+91 98765 43210" autocomplete="tel">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="contact-subject">Enquiry Type <span class="req">*</span></label>
                                <select id="contact-subject" name="subject" required>
                                    <option value="" disabled selected>Select an enquiry type…</option>
                                    <option value="Bulk Order / Export Enquiry">Bulk Order / Export Enquiry</option>
                                    <option value="Product Information – Arjuna Bark">Product Information – Arjuna Bark</option>
                                    <option value="Product Information – Dried Herbs">Product Information – Dried Herbs</option>
                                    <option value="Product Information – Herbs Powder">Product Information – Herbs Powder</option>
                                    <option value="Product Information – Renewable Energy">Product Information – Renewable Energy</option>
                                    <option value="Partnership / Distribution">Partnership / Distribution</option>
                                    <option value="Pricing & Quotation">Pricing &amp; Quotation</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="contact-country">Country</label>
                                <input type="text" id="contact-country" name="country" placeholder="India / USA / UAE…" autocomplete="country-name">
                            </div>

                            <div class="form-group">
                                <label for="contact-message">Your Message <span class="req">*</span></label>
                                <textarea id="contact-message" name="message" placeholder="Please describe your requirement, quantity needed, preferred product grades or any specific details…" required></textarea>
                            </div>

                            <button type="submit" class="form-submit-btn" id="contact-submit-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                Send Enquiry
                            </button>

                            <div class="form-alert" id="contact-form-success" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                Thank you! Your enquiry has been sent successfully. We'll respond within 24 hours.
                            </div>
                            <div class="form-alert" id="contact-form-error" role="alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                Please fill in all required fields correctly.
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </section>

        <!-- MAP SECTION -->
        <section class="contact-map-section">
            <div class="container">
                <div class="contact-map-header">
                    <span class="section-label">Our Location</span>
                    <h2>Find Us on the Map</h2>
                </div>
                <div class="contact-map-wrapper">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3667.85!2d88.2467!3d24.1099!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39fbf04d4c000001%3A0x1234567890abcdef!2sBerhampore%2C%20Murshidabad%2C%20West%20Bengal%20742101!5e0!3m2!1sen!2sin!4v1690000000000"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Biswas Enterprise Location – Berhampore, Murshidabad, West Bengal">
                    </iframe>
                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col footer-brand-col">
                    <a href="<?= url() ?>" class="footer-logo">
                        <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise" class="brand-logo-img">
                    </a>
                    <p class="footer-desc">Globally oriented trading and export company established in 2023, headquartered in Kolkata, West Bengal, India.</p>
                    <div class="footer-gst-info"><strong>GST NO:</strong> 19AGXPB1978M1ZI</div>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?= url() ?>">Home</a></li>
                        <li><a href="<?= url('shop') ?>">Shop All Products</a></li>
                        <li><a href="<?= url('about') ?>">About Us</a></li>
                        <li><a href="<?= url('contact') ?>">Contact Us</a></li>
                        <li><a href="<?= url('blog') ?>">Latest Blog</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Categories</h3>
                    <ul class="footer-links">
                        <li><a href="<?= url('shop?category=arjuna-bark') ?>">Arjuna Bark</a></li>
                        <li><a href="<?= url('shop?category=dried-herbs') ?>">Dried Herbs</a></li>
                        <li><a href="<?= url('shop?category=herbs-powder') ?>">Herbs Powder</a></li>
                        <li><a href="<?= url('shop?category=renewable-energy') ?>">Renewable Energy</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3 class="footer-title">Headquarters</h3>
                    <p class="footer-address">Na Kalikapur Berhampore Murshidabad,<br>Bara Bazar, Kolkata, West Bengal – 742102, India</p>
                    <p class="footer-contact-item"><strong>Email:</strong> <a href="mailto:dipak_200607@yahoo.co.in">dipak_200607@yahoo.co.in</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Biswas Enterprise. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script type="module">
        import { initHeader } from '<?= asset('js/components/header.js') ?>';
        document.addEventListener('DOMContentLoaded', () => {
            initHeader();

            const form = document.getElementById('contact-enquiry-form');
            const btn  = document.getElementById('contact-submit-btn');
            const ok   = document.getElementById('contact-form-success');
            const err  = document.getElementById('contact-form-error');

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                ok.style.display = 'none';
                err.style.display = 'none';

                const name    = form.querySelector('#contact-name').value.trim();
                const email   = form.querySelector('#contact-email').value.trim();
                const subject = form.querySelector('#contact-subject').value;
                const message = form.querySelector('#contact-message').value.trim();
                const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!name || !email || !subject || !message || !emailRx.test(email)) {
                    err.className = 'form-alert error';
                    err.style.display = 'flex';
                    return;
                }

                btn.disabled = true;
                btn.textContent = 'Sending…';

                // Simulate async send (replace with real AJAX/fetch to API endpoint)
                setTimeout(() => {
                    ok.className = 'form-alert success';
                    ok.style.display = 'flex';
                    form.reset();
                    btn.disabled = false;
                    btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Enquiry`;
                }, 1200);
            });
        });
    </script>
</body>
</html>
