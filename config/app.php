<?php
// Multi-tenant configuration
return [
    'app' => [
        'name' => 'EasyContact',
        'domain' => 'easy-contact.com',
        'default_company' => 'easycontact',
        'version' => '2.0.0'
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'dbname' => $_ENV['DB_NAME'] ?? 'easycontact',
        'username' => $_ENV['DB_USER'] ?? 'easycontact',
        'password' => $_ENV['DB_PASS'] ?? 'EzC0nt@ct2025!',
        'charset' => 'utf8mb4'
    ],
    
    'subscription' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'contacts_limit' => 1,
            'features' => ['basic_profile', 'qr_code']
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 9.99,
            'contacts_limit' => 50,
            'features' => ['basic_profile', 'qr_code', 'custom_branding', 'analytics']
        ],
        'premium' => [
            'name' => 'Premium', 
            'price' => 19.99,
            'contacts_limit' => -1, // unlimited
            'features' => ['basic_profile', 'qr_code', 'custom_branding', 'analytics', 'api_access', 'white_label']
        ]
    ],
    
    'languages' => [
        'default' => 'en',
        'supported' => ['en', 'de', 'fr'],
        'fallback' => 'en'
    ],
    
    'payment' => [
        'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
        'stripe_secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
        'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''
    ]
];
?>
