<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - EasyContact</title>
    <meta name="description" content="Privacy Policy for EasyContact digital business cards platform. GDPR compliant data protection practices.">
    
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

        .privacy-highlight {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
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
                        <a class="nav-link" href="terms">Terms</a>
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
                    <h1 class="display-4 fw-bold" style="color: var(--primary-color);">Privacy Policy</h1>
                    <p class="lead">Last updated: January 30, 2025</p>
                </div>

                <!-- GDPR Compliance Highlight -->
                <div class="privacy-highlight text-center">
                    <h3><i class="bi bi-shield-check me-2"></i>GDPR Compliant</h3>
                    <p class="mb-0">We comply with the General Data Protection Regulation (GDPR) and respect your data privacy rights.</p>
                </div>

                <!-- Introduction -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">1. Introduction</h2>
                    <p>EasyContact ("we", "our", or "us") is committed to protecting and respecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our digital business card and contact management platform.</p>
                    <p>This policy applies to all information collected through our website, mobile application, and any related services.</p>
                </div>

                <!-- Information We Collect -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">2. Information We Collect</h2>
                    
                    <h4>2.1 Personal Information You Provide</h4>
                    <ul>
                        <li><strong>Account Information:</strong> Name, email address, password, company name</li>
                        <li><strong>Profile Information:</strong> Job title, phone number, address, bio, profile photo</li>
                        <li><strong>Contact Information:</strong> Details of contacts you add to your account</li>
                        <li><strong>Payment Information:</strong> Credit card details, billing address (processed securely by third-party providers)</li>
                        <li><strong>Communication Data:</strong> Messages you send through our platform</li>
                    </ul>
                    
                    <h4>2.2 Automatically Collected Information</h4>
                    <ul>
                        <li><strong>Usage Data:</strong> How you interact with our services, features used, time spent</li>
                        <li><strong>Device Information:</strong> IP address, browser type, operating system, device identifiers</li>
                        <li><strong>Analytics Data:</strong> Profile views, QR code scans, contact downloads</li>
                        <li><strong>Cookies and Tracking:</strong> We use cookies to enhance your experience</li>
                    </ul>
                </div>

                <!-- How We Use Your Information -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">3. How We Use Your Information</h2>
                    <p>We use your information for the following purposes:</p>
                    <ul>
                        <li><strong>Service Delivery:</strong> To provide, maintain, and improve our services</li>
                        <li><strong>Account Management:</strong> To create and manage your account</li>
                        <li><strong>Communication:</strong> To send you updates, security alerts, and support messages</li>
                        <li><strong>Personalization:</strong> To customize your experience and recommend relevant features</li>
                        <li><strong>Analytics:</strong> To understand how our service is used and improve it</li>
                        <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights</li>
                        <li><strong>Marketing:</strong> To send you promotional materials (with your consent)</li>
                    </ul>
                </div>

                <!-- Legal Basis for Processing (GDPR) -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">4. Legal Basis for Processing (GDPR)</h2>
                    <p>Under GDPR, we process your personal data based on:</p>
                    <ul>
                        <li><strong>Contract:</strong> Processing necessary to perform our services</li>
                        <li><strong>Consent:</strong> Where you have given explicit consent</li>
                        <li><strong>Legitimate Interest:</strong> For business operations and service improvement</li>
                        <li><strong>Legal Obligation:</strong> To comply with applicable laws</li>
                    </ul>
                </div>

                <!-- Information Sharing -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">5. Information Sharing and Disclosure</h2>
                    <p>We do not sell, trade, or rent your personal information. We may share your information in the following circumstances:</p>
                    
                    <h4>5.1 Service Providers</h4>
                    <p>We may share information with trusted third-party service providers who assist us in operating our platform:</p>
                    <ul>
                        <li>Cloud hosting providers (AWS, Google Cloud)</li>
                        <li>Payment processors (Stripe, PayPal)</li>
                        <li>Email service providers</li>
                        <li>Analytics providers</li>
                    </ul>
                    
                    <h4>5.2 Business Transfers</h4>
                    <p>In the event of a merger, acquisition, or sale of assets, your information may be transferred as part of that transaction.</p>
                    
                    <h4>5.3 Legal Requirements</h4>
                    <p>We may disclose your information if required by law or to protect our rights and safety.</p>
                </div>

                <!-- Your Rights (GDPR) -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">6. Your Rights Under GDPR</h2>
                    <p>As a data subject under GDPR, you have the following rights:</p>
                    <ul>
                        <li><strong>Right of Access:</strong> Request a copy of your personal data</li>
                        <li><strong>Right of Rectification:</strong> Correct inaccurate or incomplete data</li>
                        <li><strong>Right of Erasure:</strong> Request deletion of your personal data</li>
                        <li><strong>Right to Restrict Processing:</strong> Limit how we use your data</li>
                        <li><strong>Right to Data Portability:</strong> Receive your data in a portable format</li>
                        <li><strong>Right to Object:</strong> Object to processing based on legitimate interests</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent at any time</li>
                    </ul>
                    <p>To exercise these rights, please contact us at <a href="mailto:privacy@easy-contact.com" style="color: var(--primary-color);">privacy@easy-contact.com</a></p>
                </div>

                <!-- Data Security -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">7. Data Security</h2>
                    <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
                    <ul>
                        <li><strong>Encryption:</strong> Data is encrypted in transit and at rest</li>
                        <li><strong>Access Controls:</strong> Limited access to personal data on a need-to-know basis</li>
                        <li><strong>Regular Audits:</strong> Security practices are regularly reviewed and updated</li>
                        <li><strong>Incident Response:</strong> Procedures in place for data breach notification</li>
                        <li><strong>Staff Training:</strong> Regular privacy and security training for employees</li>
                    </ul>
                </div>

                <!-- Data Retention -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">8. Data Retention</h2>
                    <p>We retain your personal information for as long as necessary to provide our services and comply with legal obligations:</p>
                    <ul>
                        <li><strong>Account Data:</strong> Retained while your account is active</li>
                        <li><strong>Contact Information:</strong> Retained as long as you use our service</li>
                        <li><strong>Analytics Data:</strong> Aggregated data may be retained for up to 3 years</li>
                        <li><strong>Legal Requirements:</strong> Some data may be retained longer for legal compliance</li>
                    </ul>
                    <p>You can request deletion of your account and data at any time.</p>
                </div>

                <!-- International Transfers -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">9. International Data Transfers</h2>
                    <p>Your information may be processed in countries outside the European Economic Area (EEA). We ensure adequate protection through:</p>
                    <ul>
                        <li>Standard Contractual Clauses (SCCs)</li>
                        <li>Adequacy decisions by the European Commission</li>
                        <li>Other appropriate safeguards as required by GDPR</li>
                    </ul>
                </div>

                <!-- Cookies and Tracking -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">10. Cookies and Tracking Technologies</h2>
                    <p>We use cookies and similar technologies to enhance your experience:</p>
                    
                    <h4>10.1 Types of Cookies</h4>
                    <ul>
                        <li><strong>Essential Cookies:</strong> Required for basic functionality</li>
                        <li><strong>Analytics Cookies:</strong> Help us understand usage patterns</li>
                        <li><strong>Preference Cookies:</strong> Remember your settings (like dark mode)</li>
                        <li><strong>Marketing Cookies:</strong> Used for targeted advertising (with consent)</li>
                    </ul>
                    
                    <p>You can control cookies through your browser settings or our cookie consent manager.</p>
                </div>

                <!-- Children's Privacy -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">11. Children's Privacy</h2>
                    <p>Our service is not intended for children under 18 years of age. We do not knowingly collect personal information from children under 18. If you become aware that a child has provided us with personal information, please contact us immediately.</p>
                </div>

                <!-- Changes to Privacy Policy -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">12. Changes to This Privacy Policy</h2>
                    <p>We may update this Privacy Policy from time to time. We will notify you of any changes by:</p>
                    <ul>
                        <li>Posting the new Privacy Policy on this page</li>
                        <li>Updating the "Last updated" date</li>
                        <li>Sending you an email notification for material changes</li>
                    </ul>
                    <p>Your continued use of the service after changes become effective constitutes acceptance of the new Privacy Policy.</p>
                </div>

                <!-- Contact Information -->
                <div class="content-section">
                    <h2 style="color: var(--primary-color);">13. Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>General Inquiries</h5>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-envelope me-2"></i><a href="mailto:privacy@easy-contact.com" style="color: var(--primary-color);">privacy@easy-contact.com</a></li>
                                <li><i class="bi bi-envelope me-2"></i><a href="mailto:support@easy-contact.com" style="color: var(--primary-color);">support@easy-contact.com</a></li>
                                <li><i class="bi bi-globe me-2"></i><a href="/" style="color: var(--primary-color);">EasyContact.com</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Data Protection Officer</h5>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-envelope me-2"></i><a href="mailto:dpo@easy-contact.com" style="color: var(--primary-color);">dpo@easy-contact.com</a></li>
                                <li><i class="bi bi-shield-check me-2"></i>GDPR Compliance</li>
                            </ul>
                        </div>
                    </div>
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
