<?php
// Direct PDO connection for database setup
try {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    // First connect without database to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "🔗 Database '$dbname' created or verified\n";
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "🔗 Database connection established\n";
    
    // Create companies table first
    $sql = "CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        subdomain VARCHAR(100) UNIQUE NOT NULL,
        domain VARCHAR(255) NULL,
        logo_url VARCHAR(500) NULL,
        theme_color VARCHAR(7) DEFAULT '#6b00b3',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Companies table created successfully\n";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        role ENUM('admin', 'user') DEFAULT 'user',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "✅ Users table created successfully\n";
    
    // Create contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_id INT NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(50),
        position VARCHAR(200),
        department VARCHAR(200),
        company VARCHAR(200),
        website VARCHAR(500),
        address TEXT,
        photo_url VARCHAR(500),
        qr_code_url VARCHAR(500),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "✅ Contacts table created successfully\n";
    // Create PayPal subscriptions table
    $sql = "CREATE TABLE IF NOT EXISTS paypal_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subscription_id VARCHAR(255) UNIQUE NOT NULL,
        plan_type ENUM('basic', 'premium') NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'PENDING',
        subscriber_email VARCHAR(255),
        payer_id VARCHAR(100),
        company_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql);
    echo "✅ PayPal subscriptions table created successfully\n";
    
    // Create PayPal webhooks log table
    $sql = "CREATE TABLE IF NOT EXISTS paypal_webhook_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        webhook_id VARCHAR(255),
        event_type VARCHAR(100) NOT NULL,
        subscription_id VARCHAR(255),
        event_data JSON,
        processed BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_subscription_id (subscription_id),
        INDEX idx_event_type (event_type),
        INDEX idx_processed (processed)
    )";
    
    $pdo->exec($sql);
    echo "✅ PayPal webhook logs table created successfully\n";
    
    // Create payment history table
    $sql = "CREATE TABLE IF NOT EXISTS payment_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subscription_id VARCHAR(255),
        payment_id VARCHAR(255),
        amount DECIMAL(10,2),
        currency VARCHAR(3) DEFAULT 'EUR',
        status VARCHAR(50),
        payment_date TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_subscription_id (subscription_id),
        INDEX idx_payment_date (payment_date)
    )";
    
    $pdo->exec($sql);
    echo "✅ Payment history table created successfully\n";
    
    // Add subscription fields to companies table if they don't exist
    $checkColumn = $pdo->query("SHOW COLUMNS FROM companies LIKE 'subscription_plan'");
    if ($checkColumn->rowCount() == 0) {
        $sql = "ALTER TABLE companies 
                ADD COLUMN subscription_plan ENUM('free', 'basic', 'premium') DEFAULT 'free',
                ADD COLUMN subscription_status VARCHAR(50) DEFAULT 'inactive',
                ADD COLUMN subscription_id VARCHAR(255) NULL,
                ADD COLUMN subscription_expires_at TIMESTAMP NULL,
                ADD COLUMN contact_limit INT DEFAULT 1";
        
        $pdo->exec($sql);
        echo "✅ Company subscription fields added successfully\n";
    } else {
        echo "ℹ️  Company subscription fields already exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 PayPal subscription database setup completed!\n";
echo "\nNext steps:\n";
echo "1. Configure your PayPal Developer Account credentials in config/paypal.php\n";
echo "2. Create subscription plans in your PayPal Developer Dashboard\n";
echo "3. Update the plan IDs in config/paypal.php\n";
echo "4. Test the subscription flow\n";
?>
