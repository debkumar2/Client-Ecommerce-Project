<?php
/**
 * BRAND PRELOADER COMPONENT
 * Biswas Enterprise Official Brand Loader Include
 */
if (!isset($preloaderRendered)): 
    $preloaderRendered = true; 
?>
<div class="site-preloader" id="site-preloader" aria-hidden="false">
    <div class="preloader-content">
        <div class="preloader-logo-ring">
            <img src="<?= asset('image/logo.png') ?>" alt="Biswas Enterprise Loading..." class="preloader-brand-img">
        </div>
        <h2 class="preloader-brand-title">Biswas <span>Enterprise</span></h2>
        <div class="preloader-subtitle">Herbal & Renewable Energy Exporter</div>
        <div class="preloader-progress-track">
            <div class="preloader-progress-bar"></div>
        </div>
    </div>
</div>

<script>
(function() {
    function hidePreloader() {
        const loader = document.getElementById('site-preloader');
        if (loader && !loader.classList.contains('fade-out')) {
            loader.classList.add('fade-out');
            setTimeout(function() {
                loader.style.display = 'none';
            }, 500);
        }
    }

    // Hide loader as soon as window loads, or max 2.2 seconds fallback
    if (document.readyState === 'complete') {
        setTimeout(hidePreloader, 300);
    } else {
        window.addEventListener('load', function() {
            setTimeout(hidePreloader, 300);
        });
        setTimeout(hidePreloader, 2200); // Safety fallback timeout
    }
})();
</script>
<?php endif; ?>
