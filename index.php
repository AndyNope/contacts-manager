<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = null;
$isPrivateProfile = false;

if ($isLoggedIn) {
    $currentUser = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Unknown User',
        'email' => $_SESSION['user_email'] ?? '',
        'company' => $_SESSION['company_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
    
    // Check if user has a private profile
    $isPrivateProfile = isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyContact - Professional Digital Business Cards & Contact Management</title>
    <meta name="description" content="Create professional digital business cards and manage your contacts with EasyContact. QR codes, custom branding, and analytics included.">
    
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

        /* User Avatar */
        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--accent-color);
            color: var(--dark-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .avatar-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--accent-color);
            color: var(--dark-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .welcome-back-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Dark Mode Toggle */
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .cta-button {
            background: var(--accent-color);
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: transform 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: var(--bg-secondary);
        }

        .feature-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            color: var(--text-color);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px var(--shadow);
        }

        .feature-card h4 {
            color: var(--primary-color) !important;
        }

        .feature-card p {
            color: var(--text-muted) !important;
        }

        .feature-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        /* Pricing Section */
        .pricing {
            padding: 80px 0;
            background: var(--bg-color);
        }

        .pricing-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            border: 2px solid var(--border-color);
            transition: transform 0.3s ease, border-color 0.3s ease;
            height: 100%;
            color: var(--text-color);
        }

        .pricing-card h3 {
            color: var(--primary-color) !important;
        }

        .pricing-card .text-muted {
            color: var(--text-muted) !important;
        }

        .pricing-card.featured {
            border-color: var(--accent-color);
            transform: scale(1.05);
            box-shadow: 0 20px 50px rgba(245, 158, 11, 0.2);
        }

        .pricing-card:hover {
            transform: translateY(-5px);
            border-color: var(--secondary-color);
        }

        .pricing-card.featured:hover {
            transform: scale(1.05) translateY(-5px);
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .price-period {
            color: #6b7280;
            font-size: 1rem;
        }

        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        /* Text Color Overrides for Dark Mode */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .lead {
            color: var(--text-color) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .theme-toggle {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            
            /* Fix mobile navbar alignment */
            .navbar-collapse {
                text-align: right;
            }
            
            .navbar-nav {
                align-items: flex-end;
            }
            
            .nav-item {
                text-align: right;
            }
            
            .dropdown-menu {
                right: 0;
                left: auto;
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
                    <?php if (!$isLoggedIn): ?>
                        <!-- Guest Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">Pricing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                        <li class="nav-item ms-3">
                            <a class="btn btn-outline-light" href="/login">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-warning" href="/register">
                                <i class="bi bi-person-plus me-1"></i>Sign Up
                            </a>
                        </li>

                    <!-- Theme toggle button -->
                    <li class="nav-item ms-3">
                        <button class="btn theme-toggle" onclick="toggleTheme()" id="themeToggle">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                            <span id="themeText">Dark</span>
                        </button>
                    </li>

                    <?php else: ?>
                        <!-- Authenticated Navigation -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <div class="me-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                                        </div>
                                        <div class="d-none d-lg-block">
                                            <div class="fw-semibold"><?= htmlspecialchars($currentUser['name']) ?></div>
                                            <div class="small text-light opacity-75"><?= htmlspecialchars($currentUser['company'] ?: 'Private Profile') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">
                                    <?= htmlspecialchars($currentUser['name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($currentUser['email']) ?></small>
                                </h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="/dashboard">
                                        <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                                    </a></li>
                                <?php elseif ($_SESSION['company_slug']): ?>
                                    <li><a class="dropdown-item" href="/<?= $_SESSION['company_slug'] ?>">
                                        <i class="bi bi-speedometer2 me-2"></i>Company Page
                                    </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= $isPrivateProfile ? '/edit-profile' : '/profile' ?>">
                                    <i class="bi bi-<?= $isPrivateProfile ? 'pencil-square' : 'person-circle' ?> me-2"></i><?= $isPrivateProfile ? 'Edit Profile' : 'My Profile' ?>
                                </a></li>
                                <li><a class="dropdown-item" href="/settings">
                                    <i class="bi bi-gear me-2"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/api/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <?php if ($isLoggedIn): ?>
                <!-- Personalized Hero for Logged In Users -->
                <div class="row align-items-center">
                    <div class="col-lg-8 mx-auto text-center">
                        <div class="welcome-back-card">
                            <div class="d-flex align-items-center justify-content-center mb-4">
                                <div class="avatar-large me-3">
                                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                                </div>
                                <div class="text-start">
                                    <h1 class="mb-1">Welcome back, <?= htmlspecialchars(explode(' ', $currentUser['name'])[0]) ?>!</h1>
                                    <p class="lead mb-1"><?= htmlspecialchars($currentUser['company'] ?: 'Private Profile') ?></p>
                                    <?php if (isset($_SESSION['subscription_status'])): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i><?= ucfirst($_SESSION['subscription_status']) ?> Plan
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="lead mb-4">Ready to manage your contacts and grow your network?</p>
                            
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="<?= $_SESSION['company_slug'] ? '/' . $_SESSION['company_slug'] : '/dashboard' ?>" class="btn cta-button text-dark">
                                    <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                                </a>
                                <?php if ($_SESSION['company_slug']): ?>
                                    <a href="/<?= $_SESSION['company_slug'] ?>" class="btn btn-outline-light">
                                        <i class="bi bi-building me-2"></i>View My Company
                                    </a>
                                <?php else: ?>
                                    <a href="/private/<?= $_SESSION['user_id'] ?>" class="btn btn-outline-light">
                                        <i class="bi bi-person-circle me-2"></i>View My Profile
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Default Hero for Guests -->
                <h1>Professional Digital Business Cards</h1>
                <p class="lead">Create stunning digital business cards with QR codes, custom branding, and powerful analytics. Perfect for modern professionals and teams.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="register" class="btn cta-button text-dark">
                        <i class="bi bi-rocket-takeoff me-2"></i>Start Free Trial
                    </a>
                    <a href="#features" class="btn btn-outline-light">
                        <i class="bi bi-play-circle me-2"></i>Learn More
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">Everything You Need</h2>
                <p class="lead">Powerful features to manage your professional contacts and digital presence</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">QR Code Business Cards</h4>
                        <p>Generate instant QR codes for easy contact sharing. Professional PDF business cards with your branding.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">Custom Branding</h4>
                        <p>Add your company logo, colors, and custom domain. White-label solution for agencies and enterprises.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">Analytics & Insights</h4>
                        <p>Track profile views, QR code scans, and contact downloads. Understand your networking performance.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">Team Management</h4>
                        <p>Manage multiple team members, set permissions, and maintain consistent branding across your organization.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">Multi-Language</h4>
                        <p>Support for English, German, and French. Perfect for international teams and global businesses.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 style="color: var(--primary-color);">Secure & Reliable</h4>
                        <p>Enterprise-grade security, GDPR compliant, and 99.9% uptime guarantee. Your data is safe with us.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold" style="color: var(--primary-color);">Simple, Transparent Pricing</h2>
                <p class="lead">Choose the perfect plan for your needs. Upgrade or downgrade anytime.</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <!-- Free Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <h3 style="color: var(--primary-color);">Free</h3>
                        <div class="price">€0<span class="price-period">/month</span></div>
                        <p class="mb-4" style="color: var(--text-muted);">Perfect for individuals</p>
                        
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>1 Contact Profile</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>QR Code Generation</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Basic Analytics</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Mobile Responsive</li>
                            <li class="mb-2"><i class="bi bi-x text-muted me-2"></i>Custom Branding</li>
                        </ul>
                        
                        <a href="register?plan=free" class="btn btn-outline-primary w-100">Get Started Free</a>
                    </div>
                </div>
                
                <!-- Basic Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card featured">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge" style="background: var(--accent-color); color: var(--dark-color);">Most Popular</span>
                        </div>
                        <h3 style="color: var(--primary-color);">Basic</h3>
                        <div class="price">€9.99<span class="price-period">/month</span></div>
                        <p class="mb-4" style="color: var(--text-muted);">For small teams</p>
                        
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>50 Contact Profiles</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Custom Branding</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Advanced Analytics</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>PDF Business Cards</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Email Support</li>
                        </ul>
                        
                        <a href="subscribe.php?plan=basic" class="btn w-100 text-white" style="background: var(--accent-color);">Subscribe to Basic</a>
                    </div>
                </div>
                
                <!-- Premium Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <h3 style="color: var(--primary-color);">Premium</h3>
                        <div class="price">€19.99<span class="price-period">/month</span></div>
                        <p class="mb-4" style="color: var(--text-muted);">For growing businesses</p>
                        
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Unlimited Contacts</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Custom Domain</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>White Label Solution</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>API Access</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Priority Support</li>
                        </ul>
                        
                        <a href="subscribe.php?plan=premium" class="btn btn-primary w-100">Subscribe to Premium</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <div class="container text-center text-white">
            <?php if ($isLoggedIn): ?>
                <!-- Personalized CTA for Logged In Users -->
                <h2 class="display-5 fw-bold mb-3">Keep Growing Your Network!</h2>
                <p class="lead mb-4">You're part of the EasyContact community. Make the most of your digital presence.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= $_SESSION['company_slug'] ? '/' . $_SESSION['company_slug'] : '/dashboard' ?>" class="btn cta-button text-dark btn-lg">
                        <i class="bi bi-speedometer2 me-2"></i>My Dashboard
                    </a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="/settings" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-gear me-2"></i>Account Settings
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Default CTA for Guests -->
                <h2 class="display-5 fw-bold mb-3">Ready to Get Started?</h2>
                <p class="lead mb-4">Join thousands of professionals using EasyContact for their digital business cards</p>
                <a href="subscribe.php" class="btn cta-button text-dark btn-lg">
                    <i class="bi bi-rocket-takeoff me-2"></i>Start Your Subscription
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-brand mb-3">
                        <img src="assets/images/easy-contact-logo-white.svg" alt="EasyContact Logo" style="height: 30px; width: 30px; margin-right: 10px;">
                        EasyContact
                    </div>
                    <p class="text-muted">Professional digital business cards and contact management for modern teams.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-muted"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Product</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li><a href="#pricing" class="text-muted text-decoration-none">Pricing</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">API</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Integrations</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Documentation</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Status</a></li>
                        <li><a href="mailto:support@easy-contact.com" class="text-muted text-decoration-none">Email Support</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="privacy" class="text-muted text-decoration-none">Privacy</a></li>
                        <li><a href="terms" class="text-muted text-decoration-none">Terms</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">GDPR</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cookies</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2025 EasyContact. All rights reserved.</p>
                    <small class="text-muted">Company admins: <a href="/company-login" class="text-decoration-none">Access your dashboard</a></small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">Made with ❤️ for professionals worldwide</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dark Mode Functionality
        function toggleTheme() {
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (html.getAttribute('data-theme') === 'dark') {
                // Switch to light mode
                html.removeAttribute('data-theme');
                themeIcon.className = 'bi bi-moon-fill';
                themeText.textContent = 'Dark';
                localStorage.setItem('theme', 'light');
            } else {
                // Switch to dark mode
                html.setAttribute('data-theme', 'dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeText.textContent = 'Light';
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const html = document.documentElement;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            // Check for saved theme preference or default to system preference
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
        
        // Listen for system theme changes
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
