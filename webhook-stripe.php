<?php
// stripe webhook endpoint - marks pending order as paid once signature + amount match
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/payments.php';

function webhook_env(string $key, string $default = ''): string
{
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    $val = getenv($key);
    return ($val !== false && $val !== '') ? (string) $val : $default;
}

configure_runtime_security(webhook_env('APP_ENV', 'production'));
header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$webhookSecret = webhook_env('STRIPE_WEBHOOK_SECRET', '');
if ($webhookSecret === '' || !($pdo instanceof PDO)) {
    http_response_code(503);
    echo json_encode(['error' => 'Webhook not configured']);
    exit();
}

$rawBody = file_get_contents('php://input');
$sigHeader = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

if (!is_string($rawBody) || !verify_stripe_webhook_signature($rawBody, $sigHeader, $webhookSecret, 300)) {
    error_log('invalid stripe webhook signature');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

$event = json_decode($rawBody, true);
if (!is_array($event) || empty($event['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Malformed event']);
    exit();
}

if ($event['type'] === 'checkout.session.completed') {
    $sessionObj = $event['data']['object'] ?? [];
    $orderNumber = trim((string) ($sessionObj['client_reference_id'] ?? $sessionObj['metadata']['order_number'] ?? ''));
    $sessionId = trim((string) ($sessionObj['id'] ?? ''));
    $amountTotal = isset($sessionObj['amount_total']) ? (int) $sessionObj['amount_total'] : -1;
    $currency = strtoupper((string) ($sessionObj['currency'] ?? ''));
    $paymentStatus = (string) ($sessionObj['payment_status'] ?? '');

    if ($orderNumber !== '' && $paymentStatus === 'paid' && $currency === 'GBP' && $amountTotal >= 0) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE orders
                 SET payment_status = :paid_status, payment_reference = :session_id
                 WHERE order_number = :order_number
                   AND total_pence = :amount_total
                   AND currency = :currency
                   AND payment_status = :pending_status'
            );
            $stmt->execute([
                'paid_status' => 'paid',
                'session_id' => $sessionId,
                'order_number' => $orderNumber,
                'amount_total' => $amountTotal,
                'currency' => 'GBP',
                'pending_status' => 'pending',
            ]);
        } catch (PDOException $e) {
            error_log('webhook order update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal error']);
            exit();
        }
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
