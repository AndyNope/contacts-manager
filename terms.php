<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - EasyContact</title>
    <meta name="description" content="Terms of Service and conditions for using EasyContact professional digital business cards platform.">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6b00b3;
            --secondary-color: #8b5cf6;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --dark-color: #1f2937;
            
            /* Light Mode Variables */
            --bg-color: #ffffff;
            --bg-secondary: #f8fafc;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] {
            /* Dark Mode Variables */
            --bg-color: #0f172a;
            --bg-secondary: #1e293b;
            --text-color: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --card-bg: #1e293b;
            --shadow: rgba(0, 0, 0, 0.3);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .content-section {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px var(--shadow);
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        @media (max-width: 768px) {
            .content-section {
                padding: 25px;
            }
            
            .theme-toggle {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="/" style="color: var(--accent-color);">
                <img src="assets/images/easy-contact-logo-white.svg" alt="EasyContact Logo" style="height: 40px; width: 40px; margin-right: 10px;">
                EasyContact
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="privacy">Privacy</a>
                    </li>
                    <li class="nav-item ms-3">
                        <button class="btn theme-toggle" onclick="toggleTheme()" id="themeToggle">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                            <span id="themeText">Dark</span>
                        </button>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light" href="login">Sign In</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn" style="background: var(--accent-color); color: var(--dark-color);" href="register">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold" style="color: var(--primary-color);">Terms of Service</h1>
                    <p class="lead">Last updated: January 30, 2025</p>
                </div>

                <!-- Introduction -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">1. Introduction</h2>
                    <p>Welcome to EasyContact. These Terms of Service ("Terms") govern your use of our digital business card and contact management platform ("Service") operated by EasyContact ("us", "we", or "our").</p>
                    <p>By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of these terms, then you may not access the Service.</p>
                </div>

                <!-- Accounts -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">2. Accounts and Registration</h2>
                    <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for all activities under your account.</p>
                    <ul>
                        <li>You must be at least 18 years old to use this Service</li>
                        <li>You are responsible for maintaining the confidentiality of your account</li>
                        <li>You must notify us immediately of any unauthorized use of your account</li>
                        <li>We reserve the right to refuse service or terminate accounts at our discretion</li>
                    </ul>
                </div>

                <!-- Acceptable Use -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">3. Acceptable Use</h2>
                    <p>You may use our Service only for lawful purposes and in accordance with these Terms. You agree not to use the Service:</p>
                    <ul>
                        <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                        <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                        <li>To transmit, or procure the sending of, any advertising or promotional material without our prior written consent</li>
                        <li>To impersonate or attempt to impersonate the Company, a Company employee, another user, or any other person or entity</li>
                        <li>In any way that infringes upon the rights of others, or in any way is illegal, threatening, fraudulent, or harmful</li>
                    </ul>
                </div>

                <!-- Subscription Plans -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">4. Subscription Plans and Billing</h2>
                    <h4>4.1 Plan Types</h4>
                    <ul>
                        <li><strong>Free Plan:</strong> Limited features with 1 contact profile</li>
                        <li><strong>Basic Plan:</strong> €9.99/month for up to 50 contact profiles</li>
                        <li><strong>Premium Plan:</strong> €19.99/month for unlimited contacts and advanced features</li>
                    </ul>
                    
                    <h4>4.2 Billing</h4>
                    <p>Subscription fees are billed in advance on a monthly basis. All fees are non-refundable except as required by law or as specifically permitted in these Terms.</p>
                    
                    <h4>4.3 Cancellation</h4>
                    <p>You may cancel your subscription at any time. Cancellation will take effect at the end of your current billing period.</p>
                </div>

                <!-- Intellectual Property -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">5. Intellectual Property Rights</h2>
                    <p>The Service and its original content, features, and functionality are and will remain the exclusive property of EasyContact and its licensors. The Service is protected by copyright, trademark, and other laws.</p>
                    
                    <h4>5.1 Your Content</h4>
                    <p>You retain all rights to the content you upload to our Service, including your contact information, photos, and business details. By uploading content, you grant us a license to use, store, and display this content as necessary to provide the Service.</p>
                </div>

                <!-- Privacy -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">6. Privacy and Data Protection</h2>
                    <p>Your privacy is important to us. Please review our Privacy Policy, which also governs your use of the Service, to understand our practices.</p>
                    <p>We comply with GDPR and other applicable data protection laws. You have the right to access, update, or delete your personal information at any time.</p>
                </div>

                <!-- Termination -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">7. Termination</h2>
                    <p>We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever, including but not limited to a breach of the Terms.</p>
                    <p>If you wish to terminate your account, you may simply discontinue using the Service or contact us to request account deletion.</p>
                </div>

                <!-- Limitation of Liability -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">8. Limitation of Liability</h2>
                    <p>In no event shall EasyContact, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>
                </div>

                <!-- Governing Law -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">9. Governing Law</h2>
                    <p>These Terms shall be interpreted and governed by the laws of Germany, without regard to its conflict of law provisions. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.</p>
                </div>

                <!-- Changes to Terms -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">10. Changes to Terms</h2>
                    <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect.</p>
                </div>

                <!-- Contact Information -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">11. Contact Information</h2>
                    <p>If you have any questions about these Terms of Service, please contact us:</p>
                    <ul>
                        <li>Email: <a href="mailto:legal@easy-contact.com" style="color: var(--primary-color);">legal@easy-contact.com</a></li>
                        <li>Support: <a href="mailto:support@easy-contact.com" style="color: var(--primary-color);">support@easy-contact.com</a></li>
                        <li>Website: <a href="/" style="color: var(--primary-color);">EasyContact.com</a></li>
                    </ul>
                </div>

                <!-- Back to Home -->
                <div class="text-center mt-5 mb-5">
                    <a href="/" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-4" style="background: var(--dark-color); color: white;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 EasyContact. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/" class="text-muted text-decoration-none me-3">Home</a>
                    <a href="privacy" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dark Mode Functionality (same as index.php)
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (html.getAttribute('data-theme') === 'dark') {
                html.removeAttribute('data-theme');
                themeIcon.className = 'bi bi-moon-fill';
                themeText.textContent = 'Dark';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Light';
                localStorage.setItem('theme', 'dark');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Light';
            } else {
                html.removeAttribute('data-theme');
                themeIcon.className = 'bi bi-moon-fill';
                themeText.textContent = 'Dark';
            }
        });
        
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (!localStorage.getItem('theme')) {
                const html = document.documentElement;
                const themeIcon = document.getElementById('themeIcon');
                const themeText = document.getElementById('themeText');
                
                if (e.matches) {
                    html.setAttribute('data-theme', 'dark');
                    themeIcon.className = 'bi bi-sun-fill';
                    themeText.textContent = 'Light';
                } else {
                    html.removeAttribute('data-theme');
                    themeIcon.className = 'bi bi-moon-fill';
                    themeText.textContent = 'Dark';
                }
            }
        });
    </script>
</body>
</html>
