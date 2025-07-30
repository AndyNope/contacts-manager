<?php
/**
 * PayPal Configuration for EasyContact
 * Updated with real PayPal Developer credentials
 */

class PayPalConfig {
    // PayPal Environment: 'sandbox' for testing, 'live' for production
    public $mode = 'sandbox';
    
    // PayPal API Credentials from PayPal Developer Dashboard
    public $clientId = 'AcSTDRJUGr0RAakIBQUKmSlE8KtLxV7PFHW6ozPvfm-h4LQpQeyyyEB5cwjrtEKF3B4vn-kcWRrH_5fT';
    public $clientSecret = 'EN7KnVUNhkUzKueUhNn4CBJCmH9L4-q2C-A7bI3sUxQTeLL7U7nMQItthabfIqaUxcuSv3NTpdcFM1gT';
    
    // PayPal API URLs
    public $baseUrl = 'https://api-m.sandbox.paypal.com';
    
    // Webhook Configuration (set after creating webhook in PayPal Developer Dashboard)
    // To get this: PayPal Developer Dashboard > Your App > Webhooks > Add Webhook
    // Webhook URL: https://your-domain.com/api/paypal_webhook.php
    public $webhookId = '7TS68256FT808511N';
    
    // Subscription Plan IDs (to be set after creating plans in PayPal)
    private $subscriptionPlans = [
        'basic' => 'P-9CW722475R9593909NCFCSLY',
        'premium' => 'P-03406900A22449008NCFCSMA'
    ];
    
    public function getSubscriptionPlans() {
        return $this->subscriptionPlans;
    }
    
    public function setPlanId($plan, $planId) {
        $this->subscriptionPlans[$plan] = $planId;
    }
    
    public function getReturnUrls() {
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        return [
            'return_url' => $baseUrl . '/api/subscription_success.php',
            'cancel_url' => $baseUrl . '/api/subscription_cancel.php'
        ];
    }
    
    public function getWebhookEvents() {
        return [
            'BILLING.SUBSCRIPTION.CREATED',
            'BILLING.SUBSCRIPTION.ACTIVATED', 
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.SUSPENDED',
            'BILLING.SUBSCRIPTION.EXPIRED',
            'PAYMENT.SALE.COMPLETED'
        ];
    }
}
?>
