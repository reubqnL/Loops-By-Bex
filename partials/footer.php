<?php
declare(strict_types=1);

$currentNav = $activeNav ?? '';
$instagramUrl = env_val('INSTAGRAM_URL', 'https://www.instagram.com/bex2_011/');
$etsyUrl = env_val('ETSY_SHOP_URL', '');
$contactEmail = env_val('CONTACT_EMAIL', 'hello@loopsbybex.co.uk');

$footerNavItems = [
    'home' => ['label' => 'Home', 'href' => '/'],
    'shop' => ['label' => 'Shop', 'href' => '/shop'],
    'about' => ['label' => 'About Me', 'href' => '/about'],
    'blog' => ['label' => 'Blog', 'href' => '/blog'],
    'contact' => ['label' => 'Contact', 'href' => '/contact'],
];
?>
<footer class="site-footer">
    <div class="site-container footer-inner">
        <div class="footer-social-group">
            <div class="social-links">
                <a href="<?php echo e($instagramUrl); ?>" target="_blank" rel="noopener noreferrer" class="social-circle" title="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="2.5" width="19" height="19" rx="5"></rect>
                        <circle cx="12" cy="12" r="4.2"></circle>
                        <circle cx="17.5" cy="6.5" r="1"></circle>
                    </svg>
                </a>
                <?php if ($etsyUrl !== ''): ?>
                    <a href="<?php echo e($etsyUrl); ?>" target="_blank" rel="noopener noreferrer" class="social-circle social-etsy" title="Etsy Shop">
                        <span>E</span>
                    </a>
                <?php else: ?>
                    <a href="/shop" class="social-circle social-etsy" title="Etsy Shop Coming Soon">
                        <span>E</span>
                    </a>
                <?php endif; ?>
                <a href="mailto:<?php echo e($contactEmail); ?>" class="social-circle" title="Email <?php echo e($contactEmail); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="4.5" width="19" height="15" rx="2.5"></rect>
                        <path d="m3.5 6.5 8.5 6.5 8.5-6.5"></path>
                    </svg>
                </a>
            </div>
            <span class="footer-divider"></span>
            <a href="mailto:<?php echo e($contactEmail); ?>" class="footer-email-link"><?php echo e($contactEmail); ?></a>
        </div>

        <nav class="footer-nav">
            <ul class="footer-nav-list">
                <?php foreach ($footerNavItems as $key => $item): ?>
                    <li>
                        <a href="<?php echo e($item['href']); ?>" class="footer-link<?php echo $currentNav === $key ? ' is-active' : ''; ?>">
                            <?php echo e($item['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="footer-legal-list">
                <li><a href="/privacy" class="footer-legal-link">Privacy Policy</a></li>
                <li><a href="/terms" class="footer-legal-link">Terms</a></li>
                <li><a href="/returns" class="footer-legal-link">Shipping &amp; Returns</a></li>
            </ul>
        </nav>

        <p class="footer-copy">&copy; <?php echo date('Y'); ?> Loops by Bex. All rights reserved.</p>
    </div>
</footer>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
