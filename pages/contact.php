<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
enforce_site_mode('storefront');

$pageTitle = 'Contact';
$activeNav = 'contact';
$contactEmail = env_val('CONTACT_EMAIL', 'hello@loopsbybex.co.uk');
$instagramUrl = env_val('INSTAGRAM_URL', 'https://www.instagram.com/bex2_011/');

$errors = [];
$submitted = false;
$values = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'subject' => trim((string) ($_POST['subject'] ?? '')),
    'message' => trim((string) ($_POST['message'] ?? '')),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($values['name'] === '' || mb_strlen($values['name']) > 120) {
        $errors['name'] = 'Please enter your name.';
    }
    if ($values['email'] === '' || strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($values['subject'] === '' || mb_strlen($values['subject']) > 160) {
        $errors['subject'] = 'Please enter a short subject.';
    }
    if ($values['message'] === '' || mb_strlen($values['message']) > 3000) {
        $errors['message'] = 'Please enter a message (up to 3,000 characters).';
    }

    if (empty($errors)) {
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)'
                );
                $stmt->execute($values);
            } catch (PDOException $e) {
                error_log('[LoopsByBex Contact] Failed saving message: ' . $e->getMessage());
            }
        }
        $submitted = true;
        $values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="site-main" id="main-content">
    <div class="site-container">
        <div class="page-header-band">
            <div>
                <h1>Get in Touch</h1>
                <p>Questions about an item, custom crochet colours, or an existing order? Send a note below.</p>
            </div>
        </div>

        <div class="two-col-panel">
            <section class="panel-card">
                <?php if ($submitted): ?>
                    <div class="flash-banner flash-success" role="status" aria-live="polite" style="margin-bottom: 1.25rem;">
                        Thank you for your message! Bex will get back to you as soon as possible.
                    </div>
                <?php endif; ?>

                <h2>Send a Message</h2>
                <form action="/contact" method="POST" novalidate aria-label="Contact Loops By Bex">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="contact_name">Your Name <span aria-hidden="true">*</span></label>
                            <input type="text" id="contact_name" name="name" required maxlength="120" autocomplete="name"
                                   value="<?php echo e($values['name']); ?>"
                                   <?php echo isset($errors['name']) ? 'aria-invalid="true" aria-describedby="err-contact-name"' : ''; ?>>
                            <?php if (isset($errors['name'])): ?>
                                <small id="err-contact-name" role="alert" style="color: #8f1d35;"><?php echo e($errors['name']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label for="contact_email">Email Address <span aria-hidden="true">*</span></label>
                            <input type="email" id="contact_email" name="email" required maxlength="254" autocomplete="email"
                                   value="<?php echo e($values['email']); ?>"
                                   <?php echo isset($errors['email']) ? 'aria-invalid="true" aria-describedby="err-contact-email"' : ''; ?>>
                            <?php if (isset($errors['email'])): ?>
                                <small id="err-contact-email" role="alert" style="color: #8f1d35;"><?php echo e($errors['email']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field field-full">
                            <label for="contact_subject">Subject <span aria-hidden="true">*</span></label>
                            <input type="text" id="contact_subject" name="subject" required maxlength="160"
                                   value="<?php echo e($values['subject']); ?>"
                                   <?php echo isset($errors['subject']) ? 'aria-invalid="true" aria-describedby="err-contact-subject"' : ''; ?>>
                            <?php if (isset($errors['subject'])): ?>
                                <small id="err-contact-subject" role="alert" style="color: #8f1d35;"><?php echo e($errors['subject']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-field field-full">
                            <label for="contact_message">Message <span aria-hidden="true">*</span></label>
                            <textarea id="contact_message" name="message" required maxlength="3000"
                                      <?php echo isset($errors['message']) ? 'aria-invalid="true" aria-describedby="err-contact-message"' : ''; ?>><?php echo e($values['message']); ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <small id="err-contact-message" role="alert" style="color: #8f1d35;"><?php echo e($errors['message']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 1.1rem;">Send Message &rarr;</button>
                </form>
            </section>

            <aside class="panel-card">
                <h2>Direct Contact</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Prefer to email directly or follow new makes on social media?
                </p>
                <div class="summary-row">
                    <span>Email</span>
                    <a href="mailto:<?php echo e($contactEmail); ?>" class="text-arrow-link"><?php echo e($contactEmail); ?></a>
                </div>
                <div class="summary-row">
                    <span>Instagram</span>
                    <a href="<?php echo e($instagramUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-arrow-link">@bex2_011 &rarr;</a>
                </div>
                <div class="summary-row">
                    <span>Response Time</span>
                    <span>Within 1–2 working days</span>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
