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
        
        .user-type-selector {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .user-type-selector:hover {
            border-color: #8b5cf6;
            background-color: #faf5ff;
        }

        .user-type-selector.selected {
            border-color: #8b5cf6;
            background-color: #f3e8ff;
            box-shadow: 0 0 0 1px #8b5cf6;
        }

        .plan-selector {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .plan-selector:hover {
            border-color: #8b5cf6;
            background-color: #faf5ff;
        }

        .plan-selector.selected {
            border-color: #8b5cf6;
            background-color: #f3e8ff;
            box-shadow: 0 0 0 1px #8b5cf6;
        }

        .password-strength {
            margin-top: 8px;
        }

        .strength-weak { background-color: #ef4444; }
        .strength-fair { background-color: #f59e0b; }
        .strength-good { background-color: #10b981; }
        .strength-strong { background-color: #059669; }        .plan-price {
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
            <form method="POST" action="/api/register.php" id="registerForm">
                <!-- User Type Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">What type of account do you want?</label>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="user-type-selector selected" onclick="selectUserType('individual')" id="individual-type">
                            <div class="text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-user text-3xl text-primary-500"></i>
                                </div>
                                <h6 class="font-semibold text-gray-800 mb-2">Individual</h6>
                                <small class="text-gray-500">Personal digital business card</small>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        <i class="fas fa-check mr-1"></i> FREE
                                    </span>
                                </div>
                            </div>
                            <input type="radio" name="user_type" value="individual" checked style="display: none;">
                        </div>
                        
                        <div class="user-type-selector" onclick="selectUserType('company')" id="company-type">
                            <div class="text-center p-4">
                                <div class="mb-3">
                                    <i class="fas fa-building text-3xl text-primary-500"></i>
                                </div>
                                <h6 class="font-semibold text-gray-800 mb-2">Company</h6>
                                <small class="text-gray-500">Team contact management</small>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-primary-100 text-primary-800 rounded-full">
                                        <i class="fas fa-star mr-1"></i> PREMIUM
                                    </span>
                                </div>
                            </div>
                            <input type="radio" name="user_type" value="company" style="display: none;">
                        </div>
                    </div>
                </div>

                <!-- Plan Selection (shown for company users) -->
                <div class="mb-6" id="planSelection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Choose Your Company Plan</label>
                    
                    <div class="plan-selector" onclick="selectPlan('basic')" id="basic-plan">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold text-gray-800">Basic</h6>
                                <small class="text-gray-500">Up to 50 contacts, company branding</small>
                            </div>
                            <div class="plan-price">€9.99/month</div>
                        </div>
                        <input type="radio" name="plan" value="basic" style="display: none;">
                    </div>
                    
                    <div class="plan-selector" onclick="selectPlan('premium')" id="premium-plan">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold text-gray-800">Premium</h6>
                                <small class="text-gray-500">Unlimited contacts, white-label, analytics</small>
                            </div>
                            <div class="plan-price">€29.99/month</div>
                        </div>
                        <input type="radio" name="plan" value="premium" style="display: none;">
                    </div>
                    
                    <input type="hidden" name="plan" value="free" id="hidden-plan">
                </div>

                <!-- Company Information (shown for company users) -->
                <div id="companyFields" class="mb-6" style="display: none;">
                    <div class="mb-4">
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="company_name" 
                               name="company_name" 
                               value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>"
                               placeholder="Your Company Name">
                        <small class="text-gray-500 mt-1 block">
                            This will be your company's public URL: easycontact.com/your-company-name
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
                               minlength="8"
                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                               title="Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character">
                        <div class="password-strength mt-2 hidden" id="passwordStrength">
                            <div class="text-xs text-gray-600 mb-1">Password strength:</div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%" id="strengthBar"></div>
                            </div>
                            <div class="text-xs mt-1" id="strengthText">Weak</div>
                        </div>
                        <small class="text-gray-500 mt-1 block">
                            Must contain: uppercase, lowercase, number, and special character (@$!%*?&)
                        </small>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required>
                        <div class="mt-2 hidden" id="passwordMatch">
                            <div class="flex items-center text-sm">
                                <i class="fas fa-check-circle text-green-500 mr-2 hidden" id="matchIcon"></i>
                                <i class="fas fa-times-circle text-red-500 mr-2 hidden" id="noMatchIcon"></i>
                                <span id="matchText" class="text-gray-500">Passwords must match</span>
                            </div>
                        </div>
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
        function selectUserType(type) {
            // Remove selected class from all user types
            document.querySelectorAll('.user-type-selector').forEach(el => el.classList.remove('selected'));
            
            // Add selected class to clicked type
            event.currentTarget.classList.add('selected');
            
            // Check the radio button
            document.querySelector(`input[name="user_type"][value="${type}"]`).checked = true;
            
            // Show/hide fields based on user type
            const planSelection = document.getElementById('planSelection');
            const companyFields = document.getElementById('companyFields');
            const companyNameInput = document.getElementById('company_name');
            const hiddenPlan = document.getElementById('hidden-plan');
            
            if (type === 'individual') {
                // Individual user - hide company fields and plan selection
                planSelection.style.display = 'none';
                companyFields.style.display = 'none';
                companyNameInput.required = false;
                hiddenPlan.value = 'free';
                
                // Clear any selected plans
                document.querySelectorAll('.plan-selector').forEach(el => el.classList.remove('selected'));
                document.querySelectorAll('input[name="plan"]').forEach(el => {
                    if (el.id !== 'hidden-plan') el.checked = false;
                });
            } else {
                // Company user - show company fields and plan selection
                planSelection.style.display = 'block';
                companyFields.style.display = 'block';
                companyNameInput.required = true;
                
                // Select basic plan by default for companies
                selectPlan('basic');
            }
        }

        function selectPlan(plan) {
            // Remove selected class from all plans
            document.querySelectorAll('.plan-selector').forEach(el => el.classList.remove('selected'));
            
            // Add selected class to clicked plan
            const planElement = document.getElementById(plan + '-plan');
            if (planElement) {
                planElement.classList.add('selected');
            }
            
            // Check the radio button
            document.querySelectorAll('input[name="plan"]').forEach(el => {
                el.checked = el.value === plan;
            });
        }

        // Password strength validation
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (!password) {
                strengthIndicator.classList.add('hidden');
                return;
            }
            
            strengthIndicator.classList.remove('hidden');
            
            let score = 0;
            let feedback = [];
            
            // Check length
            if (password.length >= 8) score += 1;
            else feedback.push('at least 8 characters');
            
            // Check for lowercase
            if (/[a-z]/.test(password)) score += 1;
            else feedback.push('lowercase letter');
            
            // Check for uppercase
            if (/[A-Z]/.test(password)) score += 1;
            else feedback.push('uppercase letter');
            
            // Check for numbers
            if (/\d/.test(password)) score += 1;
            else feedback.push('number');
            
            // Check for special characters
            if (/[@$!%*?&]/.test(password)) score += 1;
            else feedback.push('special character');
            
            // Update strength bar and text
            let width, color, text;
            
            if (score <= 1) {
                width = '20%';
                color = '#ef4444';
                text = 'Very Weak';
            } else if (score === 2) {
                width = '40%';
                color = '#f59e0b';
                text = 'Weak';
            } else if (score === 3) {
                width = '60%';
                color = '#eab308';
                text = 'Fair';
            } else if (score === 4) {
                width = '80%';
                color = '#10b981';
                text = 'Good';
            } else {
                width = '100%';
                color = '#059669';
                text = 'Strong';
            }
            
            strengthBar.style.width = width;
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.style.color = color;
            
            return score >= 4;
        }

        // Password confirmation validation
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const passwordMatch = document.getElementById('passwordMatch');
            const matchIcon = document.getElementById('matchIcon');
            const noMatchIcon = document.getElementById('noMatchIcon');
            const matchText = document.getElementById('matchText');
            
            if (!confirmPassword) {
                passwordMatch.classList.add('hidden');
                return;
            }
            
            passwordMatch.classList.remove('hidden');
            
            if (password === confirmPassword && password.length > 0) {
                matchIcon.classList.remove('hidden');
                noMatchIcon.classList.add('hidden');
                matchText.textContent = 'Passwords match';
                matchText.className = 'text-green-600';
                document.getElementById('confirm_password').setCustomValidity('');
            } else {
                matchIcon.classList.add('hidden');
                noMatchIcon.classList.remove('hidden');
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-red-600';
                document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize individual user type by default
            selectUserType('individual');
            
            // Password strength checking
            document.getElementById('password').addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });
            
            // Password confirmation checking
            document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
            
            // Form validation on submit
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const userType = document.querySelector('input[name="user_type"]:checked').value;
                const companyName = document.getElementById('company_name').value;
                
                // Check password strength
                if (!checkPasswordStrength(password)) {
                    e.preventDefault();
                    alert('Please choose a stronger password that meets all requirements.');
                    return false;
                }
                
                // Check company name for company users
                if (userType === 'company' && !companyName.trim()) {
                    e.preventDefault();
                    alert('Please enter a company name.');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>
