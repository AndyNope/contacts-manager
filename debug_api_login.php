<?php
// Debug API Login
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 API Login Debug<br><br>";

// Test database connection
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
    echo "✅ Database connection successful<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test POST data
echo "<h3>POST Data:</h3>";
if ($_POST) {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "No POST data received<br>";
}

// Test email and password
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    echo "<h3>Processing Login:</h3>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Password length: " . strlen($password) . "<br>";
    
    try {
        // Find user by email
        $stmt = $pdo->prepare("
            SELECT u.*, c.slug as company_slug, c.name as company_name, c.subscription_plan, c.subscription_status 
            FROM users u 
            JOIN companies c ON u.company_id = c.id 
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ User found<br>";
            echo "Company: " . $user['company_name'] . "<br>";
            echo "Subscription: " . $user['subscription_status'] . "<br>";
            
            // Test password
            if (password_verify($password, $user['password'])) {
                echo "✅ Password correct<br>";
                echo "Would redirect to: /" . $user['company_slug'] . "<br>";
            } else {
                echo "❌ Password incorrect<br>";
            }
        } else {
            echo "❌ User not found<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Query error: " . $e->getMessage() . "<br>";
    }
}

// Show test form
echo '<h3>Test Form:</h3>';
echo '<form method="POST">';
echo '<input type="email" name="email" placeholder="Email" value="contact@andynope.com"><br><br>';
echo '<input type="password" name="password" placeholder="Password" value="Pixelgun3d!"><br><br>';
echo '<input type="submit" value="Test Login">';
echo '</form>';
?>