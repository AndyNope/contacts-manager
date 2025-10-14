<?php
// Debug script to help troubleshoot login issues
session_start();

// Database connection
try {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    die('❌ Database connection failed: ' . $e->getMessage());
}

echo "<h2>Debug Login for contact@andynope.com</h2>";

$email = 'contact@andynope.com';

try {
    // Check if user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "❌ User not found in database<br>";
        exit;
    }
    
    echo "✅ User found in database<br>";
    echo "📊 User data:<br>";
    echo "<pre>";
    foreach ($user as $key => $value) {
        if ($key === 'password_hash') {
            echo "$key: " . substr($value, 0, 20) . "...<br>";
        } else {
            echo "$key: $value<br>";
        }
    }
    echo "</pre>";
    
    // Check company
    if (isset($user['company_id'])) {
        $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$user['company_id']]);
        $company = $stmt->fetch();
        
        if ($company) {
            echo "✅ Company found: " . $company['name'] . "<br>";
            echo "📊 Company subscription status: " . ($company['subscription_status'] ?? 'unknown') . "<br>";
        } else {
            echo "❌ Company not found<br>";
        }
    }
    
    // Test password verification
    $testPassword = 'Pixelgun3d!';
    echo "<h3>Password Test</h3>";
    echo "Testing password: '$testPassword'<br>";
    echo "Stored hash: " . substr($user['password_hash'], 0, 30) . "...<br>";
    
    if (password_verify($testPassword, $user['password_hash'])) {
        echo "✅ Password verification successful<br>";
    } else {
        echo "❌ Password verification failed<br>";
        
        // Test with manual hash
        $manualHash = '$2y$10$43j3FiZtPOz4eT8LsQe.uu0ELDssPsDnMGX6J6Aajw97tZ9eA261C';
        echo "Testing with manual hash: " . substr($manualHash, 0, 30) . "...<br>";
        
        if (password_verify($testPassword, $manualHash)) {
            echo "✅ Manual hash verification successful<br>";
        } else {
            echo "❌ Manual hash verification failed<br>";
        }
    }
    
    // Generate new hash for comparison
    $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
    echo "New hash generated: " . substr($newHash, 0, 30) . "...<br>";
    
    if (password_verify($testPassword, $newHash)) {
        echo "✅ New hash verification successful<br>";
    } else {
        echo "❌ New hash verification failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h3>Database Schema Check</h3>";
try {
    $stmt = $db->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "📊 Users table structure:<br>";
    echo "<pre>";
    foreach ($columns as $column) {
        echo $column['Field'] . " (" . $column['Type'] . ")<br>";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ Schema check error: " . $e->getMessage() . "<br>";
}
?>