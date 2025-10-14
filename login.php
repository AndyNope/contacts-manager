<?php
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
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$error = '';
$success = '';

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Find user by email - first check if user exists
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'No account found with this email address.';
            } else {
                // Get company information
                $stmt = $db->prepare("
                    SELECT u.*, c.slug as company_slug, c.name as company_name, c.subscription_tier, c.subscription_status
                    FROM users u 
                    LEFT JOIN companies c ON u.company_id = c.id 
                    WHERE u.id = ?
                ");
                $stmt->execute([$user['id']]);
                $userWithCompany = $stmt->fetch();
                
                // Check password - use 'password' field which contains the correct hash
                $passwordHash = $user['password'];
                
                if (password_verify($password, $passwordHash)) {
                    // Check subscription status (allow if no company or if active)
                    if (!$userWithCompany['company_id'] || $userWithCompany['subscription_status'] === 'active') {
                        // Update last login
                        $updateStmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
                        $updateStmt->execute([$user['id']]);
                        
                        // Set session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['company_id'] = $userWithCompany['company_id'];
                        $_SESSION['company_slug'] = $userWithCompany['company_slug'] ?: 'private';
                        $_SESSION['user_role'] = $user['role'] ?: 'user';
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        
                        // Redirect to appropriate dashboard
                        $redirectUrl = $userWithCompany['company_slug'] ? '/' . $userWithCompany['company_slug'] : '/dashboard';
                        header('Location: ' . $redirectUrl);
                        exit;
                    } else {
                        $error = 'Your account subscription is not active. Please contact support.';
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - EasyContact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6b00b3;
            --secondary-color: #8b5cf6;
            --accent-color: #f59e0b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-logo img {
            height: 60px;
            width: 60px;
            margin-bottom: 10px;
        }

        .auth-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 12px 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(107, 0, 179, 0.25);
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider:before {
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
            padding: 0 15px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="auth-card">
                    <div class="auth-logo">
                        <img src="assets/images/easy-contact-logo.svg" alt="EasyContact Logo">
                        <h1 class="auth-title">Welcome Back</h1>
                        <p class="text-muted">Sign in to your EasyContact account</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember_me">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </form>

                    <!-- Loading and error displays -->
                    <div id="loginStatus" class="mt-3" style="display: none;"></div>

                    <div class="divider">
                        <span>or</span>
                    </div>

                    <div class="text-center">
                        <p class="mb-2">Don't have an account?</p>
                        <a href="register" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="/" class="text-muted text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Back to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('loginBtn');
        const statusDiv = document.getElementById('loginStatus');
        const form = this;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';
        statusDiv.style.display = 'none';
        
        try {
            // Prepare form data
            const formData = new FormData(form);
            
            // Send AJAX request to login API
            const response = await fetch('/api/login_simple.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                // Success - redirect
                statusDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>Login successful! Redirecting...
                    </div>
                `;
                statusDiv.style.display = 'block';
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = result.redirect_url || '/dashboard';
                }, 1000);
                
            } else {
                // Error
                throw new Error(result.message || 'Login failed');
            }
            
        } catch (error) {
            // Show error
            statusDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>${error.message}
                </div>
            `;
            statusDiv.style.display = 'block';
            
        } finally {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Sign In';
        }
    });
    
    // Enter key support
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && (e.target.id === 'email' || e.target.id === 'password')) {
            document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }
    });
    </script>
</body>
</html>
