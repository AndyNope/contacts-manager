<?php
require_once '../config.php';
require_once '../config/paypal.php';

class PayPalSubscriptionCreator {
    private $paypalConfig;
    private $accessToken;
    
    public function __construct() {
        $this->paypalConfig = new PayPalConfig();
    }
    
    private function getAccessToken() {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->paypalConfig->baseUrl . '/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->paypalConfig->clientId . ':' . $this->paypalConfig->clientSecret,
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
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'];
            return true;
        }
        
        return false;
    }
    
    public function createSubscription($planId, $returnUrl, $cancelUrl) {
        if (!$this->getAccessToken()) {
            throw new Exception('Failed to get PayPal access token');
        }
        
        $subscriptionData = [
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => 'EasyContact',
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl
            ]
        ];
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->paypalConfig->baseUrl . '/v1/billing/subscriptions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken,
                'Accept: application/json',
                'PayPal-Request-Id: ' . uniqid(),
                'Prefer: return=representation'
            ],
            CURLOPT_POSTFIELDS => json_encode($subscriptionData),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 201) {
            return json_decode($response, true);
        }
        
        throw new Exception('Failed to create subscription: ' . $response);
    }
}

// Main logic
try {
    $plan = $_GET['plan'] ?? null;
    $method = $_GET['method'] ?? 'paypal';
    
    if (!$plan || !in_array($plan, ['basic', 'premium'])) {
        header('Location: ../subscribe.php?error=invalid_plan');
        exit;
    }
    
    if ($method !== 'paypal') {
        header('Location: ../subscribe.php?error=payment_method_not_supported');
        exit;
    }
    
    // Get PayPal plan ID
    $paypalConfig = new PayPalConfig();
    $plans = $paypalConfig->getSubscriptionPlans();
    
    if (!isset($plans[$plan])) {
        header('Location: ../subscribe.php?error=plan_not_found');
        exit;
    }
    
    $planId = $plans[$plan];
    
    // Create return and cancel URLs
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $returnUrl = $baseUrl . '/api/subscription_success.php';
    $cancelUrl = $baseUrl . '/api/subscription_cancel.php';
    
    // Create subscription
    $subscriptionCreator = new PayPalSubscriptionCreator();
    $subscription = $subscriptionCreator->createSubscription($planId, $returnUrl, $cancelUrl);
    
    // Store subscription info in session for later use
    session_start();
    $_SESSION['pending_subscription'] = [
        'subscription_id' => $subscription['id'],
        'plan' => $plan,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Find approval URL
    $approvalUrl = null;
    foreach ($subscription['links'] as $link) {
        if ($link['rel'] === 'approve') {
            $approvalUrl = $link['href'];
            break;
        }
    }
    
    if ($approvalUrl) {
        // Redirect to PayPal for approval
        header('Location: ' . $approvalUrl);
        exit;
    } else {
        throw new Exception('No approval URL found in subscription response');
    }
    
} catch (Exception $e) {
    error_log('PayPal Subscription Creation Error: ' . $e->getMessage());
    header('Location: ../subscribe.php?error=subscription_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>
