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
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
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
    // First, find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
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
    
    // Get company information if exists
    $company = null;
    if ($user['company_id']) {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$user['company_id']]);
        $company = $stmt->fetch();
    }
    
    // Check subscription status if company exists
    if ($company && in_array($company['subscription_status'], ['cancelled', 'suspended'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Your account subscription is not active. Please contact support.']);
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['is_private_profile'] = $user['is_private_profile'] ?? false;
    
    if ($company) {
        $_SESSION['company_slug'] = $company['slug'];
        $_SESSION['company_name'] = $company['name'];
        $_SESSION['subscription_plan'] = $company['subscription_tier'] ?? $company['subscription_plan'] ?? 'basic';
        $_SESSION['subscription_status'] = $company['subscription_status'];
    }
    
    // Determine redirect URL
    $redirectUrl = '/dashboard'; // default
    if ($company && $company['slug']) {
        $redirectUrl = '/' . $company['slug'];
    } elseif ($user['is_private_profile']) {
        // Get UUID from user's contact record for private profiles
        $stmt = $pdo->prepare("
            SELECT uuid FROM contacts 
            WHERE created_by = ? AND company_id = (SELECT id FROM companies WHERE slug = 'private')
            LIMIT 1
        ");
        $stmt->execute([$user['id']]);
        $contactRecord = $stmt->fetch();
        
        if ($contactRecord && !empty($contactRecord['uuid'])) {
            $redirectUrl = '/private/' . $contactRecord['uuid'];
        } elseif (!empty($user['profile_slug'])) {
            $redirectUrl = '/private/' . $user['profile_slug'];
        } else {
            $redirectUrl = '/private/' . $user['id']; // fallback to user ID
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
            'company' => $company ? $company['name'] : null
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Login API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}
?>