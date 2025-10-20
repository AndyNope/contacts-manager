<?php
session_start();

// Set JSON response header early
header('Content-Type: application/json');

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
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
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
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No account found with this email address.']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }
    
    // Check if account is active
    if ($user['subscription_status'] === 'cancelled' || $user['subscription_status'] === 'suspended') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Your account subscription is not active. Please contact support.']);
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
        
        // Store token in database (check if column exists first)
        try {
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
        } catch (Exception $e) {
            // Ignore if remember_token column doesn't exist
            error_log('Remember token update failed: ' . $e->getMessage());
        }
    }
    
    // Update last login (check if column exists first)
    try {
        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    } catch (Exception $e) {
        // Ignore if last_login_at column doesn't exist
        error_log('Last login update failed: ' . $e->getMessage());
    }
    
    // Determine redirect URL based on user type
    if ($user['company_slug'] && $user['company_slug'] !== 'private') {
        // Company user - redirect to company dashboard
        $redirectUrl = '/' . $user['company_slug'];
    } else {
        // Private profile user - check if they have is_private_profile flag
        // For private users, redirect to their specific profile
        $stmt = $pdo->prepare("SELECT is_private_profile FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userProfile && $userProfile['is_private_profile']) {
            $redirectUrl = '/private/' . $user['id'];
        } else {
            $redirectUrl = '/dashboard';
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect_url' => $redirectUrl,
        'user' => [
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'company' => $user['company_name']
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
    exit;
}
?>
