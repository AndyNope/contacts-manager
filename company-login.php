<?php
/**
 * Company Admin Login
 * Login system for company administrators to access their dashboard
 */

session_start();

// Redirect if already logged in as company admin
if (isset($_SESSION['user_id']) && isset($_SESSION['company_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: /dashboard');
    exit;
}

// Database connection
$host = 'localhost';
$user = 'easycontact';
$pass = 'EzC0nt@ct2025!';
$dbname = 'easycontact';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$error = '';
$success = '';

// Handle login form submission
if ($_POST && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            // Find user with admin role
            $stmt = $pdo->prepare("
                SELECT u.*, c.name as company_name, c.slug as company_slug 
                FROM users u 
                JOIN companies c ON u.company_id = c.id 
                WHERE u.email = ? AND u.role = 'admin' AND c.subscription_status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['company_name'] = $user['company_name'];
                $_SESSION['company_slug'] = $user['company_slug'];
                $_SESSION['is_private_profile'] = false;
                
                header('Location: /dashboard');
                exit;
            } else {
                $error = 'Invalid email or password, or you are not a company administrator';
            }
        } catch (Exception $e) {
            error_log('Company login error: ' . $e->getMessage());
            $error = 'Login failed. Please try again.';
        }
    }
}

// Handle error messages from URL parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'insufficient_permissions':
            $error = 'You do not have administrator permissions for this company';
            break;
        case 'session_expired':
            $error = 'Your session has expired. Please log in again.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Admin Login - EasyContact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #f59e0b;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }
        
        .login-form {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .btn-login:hover {
            background: var(--secondary-color);
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }
        
        .divider span {
            background: white;
            padding: 0 20px;
            color: #6b7280;
            font-size: 14px;
        }
        
        .links {
            text-align: center;
        }
        
        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-building display-4 mb-3"></i>
                <h2 class="mb-2">Company Admin</h2>
                <p class="mb-0 opacity-75">Access your company dashboard</p>
            </div>
            
            <div class="login-form">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="name@company.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        <label for="email">
                            <i class="bi bi-envelope me-2"></i>Company Email Address
                        </label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required>
                        <label for="password">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Access Dashboard
                    </button>
                </form>
                
                <div class="divider">
                    <span>Need help?</span>
                </div>
                
                <div class="links">
                    <a href="/">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Homepage
                    </a>
                    <span class="mx-2">•</span>
                    <a href="/login">Regular User Login</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>