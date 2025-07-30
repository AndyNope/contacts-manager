<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Plan - EasyContact</title>
    
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
            color: var(--text-muted);
            font-size: 1rem;
        }

        .paypal-button {
            background: #0070ba;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .paypal-button:hover {
            background: #005ea6;
            color: white;
        }

        .payment-method {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: var(--primary-color);
        }

        .payment-method.selected {
            border-color: var(--primary-color);
            background: rgba(107, 0, 179, 0.05);
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
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold" style="color: var(--primary-color);">Choose Your Plan</h1>
                    <p class="lead">Select the perfect plan for your business needs</p>
                </div>

                <!-- Pricing Plans -->
                <div class="row g-4 justify-content-center mb-5">
                    <!-- Basic Plan -->
                    <div class="col-lg-4 col-md-6">
                        <div class="pricing-card" id="basicPlan">
                            <h3 style="color: var(--primary-color);">Basic</h3>
                            <div class="price">€9.99<span class="price-period">/month</span></div>
                            <p class="mb-4" style="color: var(--text-muted);">Perfect for small teams</p>
                            
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>50 Contact Profiles</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Custom Branding</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Advanced Analytics</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>PDF Business Cards</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Email Support</li>
                            </ul>
                            
                            <button class="btn btn-outline-primary w-100" onclick="selectPlan('basic')">
                                Select Basic Plan
                            </button>
                        </div>
                    </div>
                    
                    <!-- Premium Plan -->
                    <div class="col-lg-4 col-md-6">
                        <div class="pricing-card featured" id="premiumPlan">
                            <div class="position-absolute top-0 start-50 translate-middle">
                                <span class="badge" style="background: var(--accent-color); color: var(--dark-color);">Recommended</span>
                            </div>
                            <h3 style="color: var(--primary-color);">Premium</h3>
                            <div class="price">€19.99<span class="price-period">/month</span></div>
                            <p class="mb-4" style="color: var(--text-muted);">For growing businesses</p>
                            
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Unlimited Contacts</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Custom Domain</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>White Label Solution</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>API Access</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Priority Support</li>
                                <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Advanced Analytics</li>
                            </ul>
                            
                            <button class="btn w-100 text-white" style="background: var(--accent-color);" onclick="selectPlan('premium')">
                                Select Premium Plan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div id="paymentSection" style="display: none;">
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <div class="card" style="background: var(--card-bg); border-color: var(--border-color);">
                                <div class="card-body p-4">
                                    <h4 class="text-center mb-4" style="color: var(--primary-color);">
                                        <i class="bi bi-credit-card me-2"></i>Choose Payment Method
                                    </h4>
                                    
                                    <!-- PayPal Option -->
                                    <div class="payment-method selected" onclick="selectPaymentMethod('paypal')" id="paypalMethod">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="paymentMethod" value="paypal" checked class="me-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <img src="https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-100px.png" alt="PayPal" style="height: 30px;" class="me-3">
                                                    <div>
                                                        <h6 class="mb-1">PayPal</h6>
                                                        <small class="text-muted">Pay securely with your PayPal account</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <i class="bi bi-check-circle text-success"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Credit Card Option (Future) -->
                                    <div class="payment-method" onclick="selectPaymentMethod('card')" id="cardMethod">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="paymentMethod" value="card" class="me-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-credit-card-2-front fs-3 me-3" style="color: var(--primary-color);"></i>
                                                    <div>
                                                        <h6 class="mb-1">Credit/Debit Card</h6>
                                                        <small class="text-muted">Pay with Visa, Mastercard, or other cards</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="badge bg-warning text-dark">Coming Soon</span>
                                        </div>
                                    </div>

                                    <!-- Selected Plan Summary -->
                                    <div class="mt-4 p-3" style="background: var(--bg-secondary); border-radius: 10px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><strong id="selectedPlanName">Premium Plan</strong></span>
                                            <span class="fs-5 fw-bold" style="color: var(--primary-color);" id="selectedPlanPrice">€19.99/month</span>
                                        </div>
                                        <small class="text-muted">Recurring monthly subscription • Cancel anytime</small>
                                    </div>

                                    <!-- Subscribe Button -->
                                    <div class="d-grid mt-4">
                                        <button class="btn paypal-button btn-lg" onclick="startSubscription()" id="subscribeBtn">
                                            <i class="bi bi-paypal me-2"></i>
                                            Subscribe with PayPal
                                        </button>
                                        <small class="text-center text-muted mt-2">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Secure payment processed by PayPal
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back to Home -->
                <div class="text-center mt-5">
                    <a href="/" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedPlan = null;
        let selectedPaymentMethod = 'paypal';
        
        function selectPlan(plan) {
            selectedPlan = plan;
            
            // Remove selection from all plans
            document.querySelectorAll('.pricing-card').forEach(card => {
                card.style.border = '2px solid var(--border-color)';
            });
            
            // Highlight selected plan
            const planCard = document.getElementById(plan + 'Plan');
            planCard.style.border = '2px solid var(--primary-color)';
            
            // Update payment section
            const planName = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
            const planPrice = plan === 'basic' ? '€9.99/month' : '€19.99/month';
            
            document.getElementById('selectedPlanName').textContent = planName;
            document.getElementById('selectedPlanPrice').textContent = planPrice;
            
            // Show payment section
            document.getElementById('paymentSection').style.display = 'block';
            
            // Scroll to payment section
            document.getElementById('paymentSection').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
        
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            // Remove selection from all methods
            document.querySelectorAll('.payment-method').forEach(method => {
                method.classList.remove('selected');
            });
            
            // Select the clicked method
            document.getElementById(method + 'Method').classList.add('selected');
            
            // Update subscribe button
            const subscribeBtn = document.getElementById('subscribeBtn');
            if (method === 'paypal') {
                subscribeBtn.innerHTML = '<i class="bi bi-paypal me-2"></i>Subscribe with PayPal';
                subscribeBtn.disabled = false;
            } else if (method === 'card') {
                subscribeBtn.innerHTML = '<i class="bi bi-credit-card me-2"></i>Coming Soon';
                subscribeBtn.disabled = true;
            }
        }
        
        function startSubscription() {
            if (!selectedPlan) {
                alert('Please select a plan first');
                return;
            }
            
            if (selectedPaymentMethod === 'card') {
                alert('Credit card payments are coming soon. Please use PayPal for now.');
                return;
            }
            
            // Show loading state
            const subscribeBtn = document.getElementById('subscribeBtn');
            subscribeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            subscribeBtn.disabled = true;
            
            // Redirect to PayPal subscription creation
            window.location.href = `api/create_subscription.php?plan=${selectedPlan}&method=${selectedPaymentMethod}`;
        }
        
        // Check for URL parameters (plan pre-selection)
        const urlParams = new URLSearchParams(window.location.search);
        const planParam = urlParams.get('plan');
        
        if (planParam && (planParam === 'basic' || planParam === 'premium')) {
            setTimeout(() => selectPlan(planParam), 500);
        }
    </script>
</body>
</html>
