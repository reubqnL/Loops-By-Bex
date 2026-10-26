<?php
// boilerplate

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // retrieve the email from the form submission and trim whitespace
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    // validate that it is a valid email address format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO waitlist (email) VALUES (:email)");
            $stmt->execute(['email' => $email]);

            header("Location: launchingty.php?status=success");
            exit();

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                header("Location: launchingty.php?status=already_registered");
            } else {
                header("Location: launchingty.php?status=error");
            }
            exit();
        }
    } else {
        header("Location: launchingty.php?status=invalid_email");
        exit();
    }
} else {
    header("Location: launching.php");
    exit();
}