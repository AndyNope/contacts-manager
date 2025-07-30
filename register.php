<?php
session_start();

$error = '';
$success = '';
$selectedPlan = $_GET['plan'] ?? 'free';

// Handle error/success messages from API
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'validation':
            $error = $_GET['message'] ?? 'Please check your input and try again.';
            break;
        case 'email_exists':
            $error = 'An account with this email already exists.';
            break;
        case 'registration_failed':
            $error = $_GET['message'] ?? 'Registration failed. Please try again.';
            break;
        default:
            $error = 'An error occurred. Please try again.';
    }
}

if (isset($_GET['success'])) {
    $success = 'Account created successfully! Please check your email for verification.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EasyContact</title>
    <meta name="description" content="Create your EasyContact account and start managing professional contacts">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .plan-selector {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .plan-selector:hover {
            border-color: #c4b5fd;
            background: rgba(139, 92, 246, 0.02);
        }
        
        .plan-selector.selected {
            border-color: #8b5cf6;
            background: rgba(139, 92, 246, 0.05);
        }
        
        .plan-price {
            font-weight: 700;
            color: #8b5cf6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <div class="auth-card p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-primary-500 text-white p-3 rounded-full">
                        <i class="fas fa-address-book text-2xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Your Account</h1>
                <p class="text-gray-600">Start managing your professional contacts today</p>
            </div>

            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="/api/register_debug.php" id="registerForm">
                <!-- Plan Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Choose Your Plan</label>
                    
                    <div class="plan-selector <?= $selectedPlan === 'free' ? 'selected' : '' ?>" onclick="selectPlan('free')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold text-gray-800">Free</h6>
                                <small class="text-gray-500">Private profile, perfect for individuals</small>
                            </div>
                            <div class="plan-price">€0/month</div>
                        </div>
                        <input type="radio" name="plan" value="free" <?= $selectedPlan === 'free' ? 'checked' : '' ?> style="display: none;">
                    </div>
                    
                    <div class="plan-selector <?= $selectedPlan === 'basic' ? 'selected' : '' ?>" onclick="selectPlan('basic')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold text-gray-800">Basic</h6>
                                <small class="text-gray-500">50 contacts, company branding</small>
                            </div>
                            <div class="plan-price">€9.99/month</div>
                        </div>
                        <input type="radio" name="plan" value="basic" <?= $selectedPlan === 'basic' ? 'checked' : '' ?> style="display: none;">
                    </div>
                    
                    <div class="plan-selector <?= $selectedPlan === 'premium' ? 'selected' : '' ?>" onclick="selectPlan('premium')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold text-gray-800">Premium</h6>
                                <small class="text-gray-500">Unlimited contacts, white-label</small>
                            </div>
                            <div class="plan-price">€29.99/month</div>
                        </div>
                        <input type="radio" name="plan" value="premium" <?= $selectedPlan === 'premium' ? 'checked' : '' ?> style="display: none;">
                    </div>
                </div>

                <!-- Company Information -->
                <div id="companyFields" class="mb-6">
                    <div class="mb-4">
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name 
                            <small class="text-gray-500 font-normal">(Optional for Free Plan)</small>
                        </label>
                        <input type="text" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="company_name" 
                               name="company_name" 
                               value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
                               placeholder="Leave empty for private profile">
                        <small class="text-gray-500 mt-1 block">
                            Free plan: Leave empty for a private profile like /private/profile/your-name
                        </small>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input type="text" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="first_name" 
                               name="first_name" 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" 
                               required>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input type="text" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="last_name" 
                               name="last_name" 
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" 
                               required>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="8">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" 
                               class="mt-1 mr-3 text-primary-500 border-gray-300 rounded focus:ring-primary-500" 
                               id="agree_terms" 
                               name="agree_terms" 
                               required>
                        <span class="text-sm text-gray-700">
                            I agree to the <a href="/terms" target="_blank" class="text-primary-600 hover:text-primary-700 underline">Terms of Service</a> and <a href="/privacy" target="_blank" class="text-primary-600 hover:text-primary-700 underline">Privacy Policy</a>
                        </span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full bg-primary-600 text-white py-3 px-6 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </form>

            <!-- Footer Links -->
            <div class="text-center mt-8">
                <p class="text-gray-600 mb-4">Already have an account?</p>
                <a href="/login" class="w-full inline-block bg-gray-100 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </a>
            </div>

            <div class="text-center mt-6">
                <a href="/" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Homepage
                </a>
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
            
            // Show/hide company fields based on plan
            const companyFields = document.getElementById('companyFields');
            const companyNameInput = document.getElementById('company_name');
            
            if (plan === 'free') {
                // For free plan, company name is optional
                companyNameInput.required = false;
                companyFields.style.display = 'block';
            } else {
                // For paid plans, company name is required
                companyNameInput.required = true;
                companyFields.style.display = 'block';
            }
        }

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

        // Initialize plan selection on page load
        document.addEventListener('DOMContentLoaded', function() {
            const selectedPlan = document.querySelector('input[name="plan"]:checked').value;
            selectPlan(selectedPlan);
        });
    </script>
</body>
</html>
