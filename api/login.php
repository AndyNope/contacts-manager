<?php
session_start();

// Database connection
try {
    $host = 'localhost';
    $user = 'kontaktverwaltung';
    $pass = 'Kontakt&Verwaltung';
    $dbname = 'kontaktverwaltung';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']);

// Validation
if (empty($email) || empty($password)) {
    header('Location: /login?error=invalid_credentials');
    exit;
}

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
    
    if (!$user) {
        header('Location: /login?error=account_not_found');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        header('Location: /login?error=invalid_credentials');
        exit;
    }
    
    // Check if account is active
    if ($user['subscription_status'] === 'cancelled' || $user['subscription_status'] === 'suspended') {
        header('Location: /login?error=account_inactive');
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['company_slug'] = $user['company_slug'];
    $_SESSION['company_name'] = $user['company_name'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['subscription_plan'] = $user['subscription_plan'];
    $_SESSION['subscription_status'] = $user['subscription_status'];
    
    // Set remember me cookie if requested
    if ($rememberMe) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 days
        
        // Store token in database (you might want to create a remember_tokens table)
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Redirect to company dashboard
    header('Location: /' . $user['company_slug']);
    exit;
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    header('Location: /login?error=login_failed');
    exit;
}
?>
