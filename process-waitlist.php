<?php
require_once __DIR__ . '/includes/bootstrap.php';
enforce_site_mode('launching');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: /launching', true, 302);
    exit();
}

if (!verify_csrf_request()) {
    header('Location: /launching?status=error', true, 303);
    exit();
}

if (is_honeypot_triggered()) {
    regenerate_session_safely();
    $_SESSION['access_granted_at'] = time();
    header('Location: /launchingty', true, 303);
    exit();
}

if (!check_rate_limit($pdo, 'waitlist_signup', 5, 600)) {
    header('Location: /launching?status=rate_limited', true, 303);
    exit();
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));

if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /launching?status=invalid_email', true, 303);
    exit();
}

if (!($pdo instanceof PDO)) {
    error_log('waitlist signup failed: db not connected');
    header('Location: /launching?status=error', true, 303);
    exit();
}

try {
    $stmt = $pdo->prepare('INSERT INTO waitlist (email) VALUES (:email)');
    $stmt->execute(['email' => $email]);

    regenerate_session_safely();
    $_SESSION['access_granted_at'] = time();
    header('Location: /launchingty', true, 303);
    exit();
} catch (PDOException $e) {
    if ((string) $e->getCode() === '23000') {
        // send duplicate signups to thank you page too so people cant check whose email is in db
        regenerate_session_safely();
        $_SESSION['access_granted_at'] = time();
        header('Location: /launchingty', true, 303);
    } else {
        error_log('waitlist insert error: ' . $e->getMessage());
        header('Location: /launching?status=error', true, 303);
    }
    exit();
}
