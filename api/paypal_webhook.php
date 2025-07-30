<?php
/**
 * PayPal Webhook Handler for EasyContact
 * Handles subscription status updates from PayPal
 */

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
    http_response_code(500);
    error_log('Database connection failed: ' . $e->getMessage());
    exit('Database connection failed');
}

// Get PayPal config
$paypalConfig = new PayPalConfig();

// Get webhook payload
$webhook_payload = file_get_contents('php://input');
$webhook_data = json_decode($webhook_payload, true);

// Log webhook for debugging (remove in production)
error_log('PayPal Webhook received: ' . $webhook_payload);

// Verify webhook signature (recommended for production)
function verifyWebhookSignature($webhook_payload, $webhook_headers, $webhook_id, $cert_id) {
    // Implementation for webhook signature verification
    // For now, we'll skip this but it's recommended for production
    return true;
}

// Process webhook events
if ($webhook_data) {
    $event_type = $webhook_data['event_type'] ?? '';
    $resource = $webhook_data['resource'] ?? [];
    
    try {
        switch ($event_type) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                handleSubscriptionCreated($pdo, $resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                handleSubscriptionActivated($pdo, $resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                handleSubscriptionCancelled($pdo, $resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                handleSubscriptionSuspended($pdo, $resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.EXPIRED':
                handleSubscriptionExpired($pdo, $resource);
                break;
                
            case 'PAYMENT.SALE.COMPLETED':
                handlePaymentCompleted($pdo, $resource);
                break;
                
            default:
                error_log('Unhandled webhook event: ' . $event_type);
        }
        
        // Return success response to PayPal
        http_response_code(200);
        echo 'OK';
        
    } catch (Exception $e) {
        error_log('Webhook processing error: ' . $e->getMessage());
        http_response_code(500);
        echo 'Error processing webhook';
    }
} else {
    http_response_code(400);
    echo 'Invalid webhook data';
}

function handleSubscriptionCreated($pdo, $resource) {
    $subscriptionId = $resource['id'] ?? '';
    $customId = $resource['custom_id'] ?? '';
    
    if ($subscriptionId && $customId) {
        // Update company with subscription ID
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET paypal_subscription_id = ?, 
                subscription_status = 'pending',
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$subscriptionId, $customId]);
        
        error_log("Subscription created: {$subscriptionId} for company: {$customId}");
    }
}

function handleSubscriptionActivated($pdo, $resource) {
    $subscriptionId = $resource['id'] ?? '';
    
    if ($subscriptionId) {
        // Activate subscription
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET subscription_status = 'active',
                subscription_activated_at = NOW(),
                updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        
        error_log("Subscription activated: {$subscriptionId}");
    }
}

function handleSubscriptionCancelled($pdo, $resource) {
    $subscriptionId = $resource['id'] ?? '';
    
    if ($subscriptionId) {
        // Cancel subscription
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET subscription_status = 'cancelled',
                subscription_cancelled_at = NOW(),
                updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        
        error_log("Subscription cancelled: {$subscriptionId}");
    }
}

function handleSubscriptionSuspended($pdo, $resource) {
    $subscriptionId = $resource['id'] ?? '';
    
    if ($subscriptionId) {
        // Suspend subscription
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET subscription_status = 'suspended',
                updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        
        error_log("Subscription suspended: {$subscriptionId}");
    }
}

function handleSubscriptionExpired($pdo, $resource) {
    $subscriptionId = $resource['id'] ?? '';
    
    if ($subscriptionId) {
        // Mark subscription as expired
        $stmt = $pdo->prepare("
            UPDATE companies 
            SET subscription_status = 'expired',
                updated_at = NOW()
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        
        error_log("Subscription expired: {$subscriptionId}");
    }
}

function handlePaymentCompleted($pdo, $resource) {
    $subscriptionId = $resource['billing_agreement_id'] ?? '';
    $amount = $resource['amount']['total'] ?? '';
    $currency = $resource['amount']['currency'] ?? '';
    
    if ($subscriptionId) {
        // Log successful payment
        $stmt = $pdo->prepare("
            INSERT INTO payment_logs 
            (company_id, paypal_subscription_id, amount, currency, status, paypal_payment_id, created_at) 
            SELECT id, ?, ?, ?, 'completed', ?, NOW()
            FROM companies 
            WHERE paypal_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId, $amount, $currency, $resource['id'] ?? '', $subscriptionId]);
        
        error_log("Payment completed: {$amount} {$currency} for subscription: {$subscriptionId}");
    }
}
?>
