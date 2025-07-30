<?php
session_start();

// Database connection
try {
    $host = 'localhost';
    $user = 'kontaktverwaltung';
    $pass = 'Kontakt&Verwaltung';
    $dbname = 'kontaktverwaltung';
    
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
$selectedPlan = $_GET['plan'] ?? 'free';

if ($_POST) {
    $companyName = trim($_POST['company_name']);
    $companySlug = trim($_POST['company_slug']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $plan = $_POST['plan'];
    
    // Validation
    if (empty($companyName) || empty($companySlug) || empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $companySlug)) {
        $error = 'Company URL can only contain lowercase letters, numbers, and hyphens.';
    } else {
        try {
            // Check if company slug already exists
            $stmt = $db->prepare("SELECT id FROM companies WHERE slug = ?");
            $stmt->execute([$companySlug]);
            if ($stmt->fetch()) {
                $error = 'This company URL is already taken. Please choose another one.';
            } else {
                // Check if email already exists
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'An account with this email already exists.';
                } else {
                    // Create company
                    $db->beginTransaction();
                    
                    $stmt = $db->prepare("
                        INSERT INTO companies (slug, name, subscription_tier, subscription_status) 
                        VALUES (?, ?, ?, 'active')
                    ");
                    $stmt->execute([$companySlug, $companyName, $plan]);
                    $companyId = $db->lastInsertId();
                    
                    // Create user
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        INSERT INTO users (company_id, email, password_hash, first_name, last_name, role, email_verified_at) 
                        VALUES (?, ?, ?, ?, ?, 'owner', NOW())
                    ");
                    $stmt->execute([$companyId, $email, $passwordHash, $firstName, $lastName]);
                    $userId = $db->lastInsertId();
                    
                    // Create default contact for the owner
                    $contactSlug = strtolower($firstName . '-' . $lastName);
                    $contactSlug = preg_replace('/[^a-z0-9-]/', '-', $contactSlug);
                    $contactSlug = preg_replace('/-+/', '-', $contactSlug);
                    $contactSlug = trim($contactSlug, '-');
                    
                    $stmt = $db->prepare("
                        INSERT INTO contacts (company_id, created_by_user_id, first_name, last_name, email, slug, is_public, is_featured) 
                        VALUES (?, ?, ?, ?, ?, ?, TRUE, TRUE)
                    ");
                    $stmt->execute([$companyId, $userId, $firstName, $lastName, $email, $contactSlug]);
                    
                    $db->commit();
                    
                    // Set session and redirect
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['company_id'] = $companyId;
                    $_SESSION['company_slug'] = $companySlug;
                    $_SESSION['user_role'] = 'owner';
                    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                    
                    // Redirect to company dashboard
                    header('Location: /' . $companySlug . '?welcome=1');
                    exit;
                }
            }
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Registration failed. Please try again.';
        }
    }
}

// Generate slug suggestion from company name
function generateSlug($name) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - EasyContact</title>
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
            padding: 20px 0;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
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

        .plan-selector {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .plan-selector:hover {
            border-color: var(--primary-color);
        }

        .plan-selector.selected {
            border-color: var(--primary-color);
            background: rgba(107, 0, 179, 0.05);
        }

        .plan-price {
            font-weight: 700;
            color: var(--primary-color);
        }

        .url-preview {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            margin-top: 5px;
            font-family: monospace;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-card">
                    <div class="auth-logo">
                        <img src="assets/images/easy-contact-logo.svg" alt="EasyContact Logo">
                        <h1 class="auth-title">Create Your Account</h1>
                        <p class="text-muted">Start managing your professional contacts today</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="registerForm">
                        <!-- Plan Selection -->
                        <div class="mb-4">
                            <label class="form-label">Choose Your Plan</label>
                            
                            <div class="plan-selector <?= $selectedPlan === 'free' ? 'selected' : '' ?>" onclick="selectPlan('free')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Free</h6>
                                        <small class="text-muted">1 contact, basic features</small>
                                    </div>
                                    <div class="plan-price">€0/month</div>
                                </div>
                                <input type="radio" name="plan" value="free" <?= $selectedPlan === 'free' ? 'checked' : '' ?> style="display: none;">
                            </div>
                            
                            <div class="plan-selector <?= $selectedPlan === 'basic' ? 'selected' : '' ?>" onclick="selectPlan('basic')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Basic</h6>
                                        <small class="text-muted">50 contacts, custom branding</small>
                                    </div>
                                    <div class="plan-price">€9.99/month</div>
                                </div>
                                <input type="radio" name="plan" value="basic" <?= $selectedPlan === 'basic' ? 'checked' : '' ?> style="display: none;">
                            </div>
                            
                            <div class="plan-selector <?= $selectedPlan === 'premium' ? 'selected' : '' ?>" onclick="selectPlan('premium')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Premium</h6>
                                        <small class="text-muted">Unlimited contacts, white-label</small>
                                    </div>
                                    <div class="plan-price">€19.99/month</div>
                                </div>
                                <input type="radio" name="plan" value="premium" <?= $selectedPlan === 'premium' ? 'checked' : '' ?> style="display: none;">
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_slug" class="form-label">Company URL</label>
                                <input type="text" class="form-control" id="company_slug" name="company_slug" 
                                       value="<?= htmlspecialchars($_POST['company_slug'] ?? '') ?>" required 
                                       pattern="[a-z0-9-]+" title="Only lowercase letters, numbers, and hyphens">
                                <div class="url-preview" id="urlPreview">
                                    easy-contact.com/<span id="slugPreview">your-company</span>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agree_terms" required>
                            <label class="form-check-label" for="agree_terms">
                                I agree to the <a href="/terms" target="_blank" class="text-decoration-none">Terms of Service</a> and <a href="/privacy" target="_blank" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-2">Already have an account?</p>
                        <a href="login" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
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
        function selectPlan(plan) {
            // Remove selected class from all plans
            document.querySelectorAll('.plan-selector').forEach(el => el.classList.remove('selected'));
            
            // Add selected class to clicked plan
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.querySelector(`input[value="${plan}"]`).checked = true;
        }

        // Auto-generate slug from company name
        document.getElementById('company_name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .replace(/^-|-$/g, '');
            
            document.getElementById('company_slug').value = slug;
            document.getElementById('slugPreview').textContent = slug || 'your-company';
        });

        // Update preview when slug is manually changed
        document.getElementById('company_slug').addEventListener('input', function() {
            document.getElementById('slugPreview').textContent = this.value || 'your-company';
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
