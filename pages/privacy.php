<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Privacy Policy';
$activeNav = '';
$contactEmail = env_val('CONTACT_EMAIL', 'hello@loopsbybex.co.uk');
$isLaunchingOnly = strtolower(env_val('APP_LAUNCHING_MODE', 'preview')) === 'launching';

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <article class="prose-card">
            <h1>Privacy Policy</h1>
            <p style="font-size: 0.88rem; color: var(--text-muted);">Last updated: October 2026</p>

            <h2>1. Data Controller</h2>
            <p>
                Loops By Bex operates this website in the United Kingdom and acts as the data controller for personal information collected through our waitlist, contact form, and online shop.
            </p>

            <h2>2. Information We Collect &amp; Lawful Basis</h2>
            <ul>
                <li><strong>Waitlist Signups (Consent):</strong> Your email address, collected solely to notify you when Loops By Bex launches and share occasional studio updates.</li>
                <li><strong>Shop Orders (Contract &amp; Legal Obligation):</strong> Your name, email address, UK delivery address, and order items so we can fulfil your order and maintain statutory UK accounting records.</li>
                <li><strong>Payments (PCI-DSS SAQ-A):</strong> Payment card numbers are processed directly by our accredited third-party payment provider (Stripe Hosted Checkout) and never pass through or get stored on Loops By Bex servers.</li>
                <li><strong>Contact Enquiries (Legitimate Interests):</strong> Your name, email address, and message content so we can reply to your question.</li>
            </ul>

            <h2>3. Essential Cookies &amp; Third-Party Services</h2>
            <p>
                We use a strictly necessary PHP session cookie to keep track of items in your shopping cart and protect forms against cross-site request forgery (CSRF). Typography assets are loaded via Google Fonts, which receives standard browser request headers (including IP address) to deliver font files.
            </p>

            <h2>4. Data Retention &amp; Security</h2>
            <p>
                Waitlist email addresses are retained only until launch notifications are completed or until you unsubscribe. Order records are retained for 6 years to comply with UK tax law. Access to our database is restricted using authenticated, parameterised queries over HTTPS.
            </p>

            <h2>5. Your UK GDPR Rights</h2>
            <p>
                Under UK data protection law, you have the right to request access to, correction of, or deletion of your personal data at any time by emailing
                <a href="mailto:<?php echo e($contactEmail); ?>?subject=Privacy%20Request"><?php echo e($contactEmail); ?></a>.
                You also have the right to lodge a complaint with the UK Information Commissioner's Office (<a href="https://ico.org.uk" target="_blank" rel="noopener noreferrer">ico.org.uk</a>).
            </p>

            <p style="margin-top: 1.75rem;">
                <a href="<?php echo $isLaunchingOnly ? '/launching' : '/'; ?>">&larr; Back to Home</a>
            </p>
        </article>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
