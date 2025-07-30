<?php
session_start();
require_once '../config/paypal.php';

// Database connection
try {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Check if we have a pending subscription
if (!isset($_SESSION['pending_subscription'])) {
    header('Location: /subscribe.php?error=no_pending_subscription');
    exit;
}

$subscriptionToken = $_GET['subscription_id'] ?? null;
$pendingSubscription = $_SESSION['pending_subscription'];

if (!$subscriptionToken) {
    header('Location: /subscribe.php?error=missing_subscription_token');
    exit;
}

try {
    // Get PayPal access token
    $paypalConfig = new PayPalConfig();
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $paypalConfig->baseUrl . '/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_USERPWD => $paypalConfig->clientId . ':' . $paypalConfig->clientSecret,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get PayPal access token');
    }
    
    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'];
    
    // Get subscription details from PayPal
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $paypalConfig->baseUrl . '/v1/billing/subscriptions/' . $subscriptionToken,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('Failed to get subscription details from PayPal');
    }
    
    $subscription = json_decode($response, true);
    
    // Check if subscription is active
    if ($subscription['status'] !== 'ACTIVE') {
        throw new Exception('Subscription is not active: ' . $subscription['status']);
    }
    
    // Store subscription in database
    $stmt = $pdo->prepare("
        INSERT INTO paypal_subscriptions 
        (subscription_id, plan_type, status, subscriber_email, payer_id, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $subscriberEmail = $subscription['subscriber']['email_address'] ?? '';
    $payerId = $subscription['subscriber']['payer_id'] ?? '';
    
    $stmt->execute([
        $subscription['id'],
        $pendingSubscription['plan'],
        $subscription['status'],
        $subscriberEmail,
        $payerId
    ]);
    
    // Update session with success info
    $_SESSION['subscription_success'] = [
        'subscription_id' => $subscription['id'],
        'plan' => $pendingSubscription['plan'],
        'status' => $subscription['status'],
        'subscriber_email' => $subscriberEmail
    ];
    
    // Clear pending subscription
    unset($_SESSION['pending_subscription']);
    
} catch (Exception $e) {
    error_log('PayPal Subscription Success Error: ' . $e->getMessage());
    header('Location: /subscribe.php?error=subscription_processing_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Successful - EasyContact</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6b00b3;
            --secondary-color: #8b5cf6;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --dark-color: #1f2937;
            
            /* Light Mode Variables */
            --bg-color: #ffffff;
            --bg-secondary: #f8fafc;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .plan-badge {
            background: var(--accent-color);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="success-card">
                    <div class="success-icon">
                        <i class="bi bi-check-lg text-white" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="display-4 fw-bold mb-4" style="color: var(--success-color);">
                        Subscription Successful!
                    </h1>
                    
                    <div class="plan-badge">
                        <?php echo ucfirst($_SESSION['subscription_success']['plan']); ?> Plan
                    </div>
                    
                    <p class="lead mb-4">
                        Welcome to EasyContact! Your subscription has been activated successfully.
                    </p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border-radius: 10px;">
                                <strong>Subscription ID</strong><br>
                                <small class="text-muted"><?php echo $_SESSION['subscription_success']['subscription_id']; ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border-radius: 10px;">
                                <strong>Email</strong><br>
                                <small class="text-muted"><?php echo $_SESSION['subscription_success']['subscriber_email']; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Next Steps:</strong> Create your company account to start building your contact profiles and business cards.
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="/register" class="btn btn-lg text-white" style="background: var(--primary-color);">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </a>
                        <a href="/" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="bi bi-shield-check" style="font-size: 2rem; color: var(--success-color);"></i>
                            <h6 class="mt-2">Secure Payment</h6>
                            <small class="text-muted">Protected by PayPal</small>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-arrow-repeat" style="font-size: 2rem; color: var(--primary-color);"></i>
                            <h6 class="mt-2">Easy Management</h6>
                            <small class="text-muted">Cancel anytime</small>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-headset" style="font-size: 2rem; color: var(--accent-color);"></i>
                            <h6 class="mt-2">24/7 Support</h6>
                            <small class="text-muted">We're here to help</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
