<?php
session_start();

// Clear any pending subscription
unset($_SESSION['pending_subscription']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Cancelled - EasyContact</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6b00b3;
            --secondary-color: #8b5cf6;
            --accent-color: #f59e0b;
            --warning-color: #f59e0b;
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

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .cancel-card {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .cancel-icon {
            width: 120px;
            height: 120px;
            background: var(--warning-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="cancel-card">
                    <div class="cancel-icon">
                        <i class="bi bi-x-lg text-white" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="display-4 fw-bold mb-4" style="color: var(--warning-color);">
                        Subscription Cancelled
                    </h1>
                    
                    <p class="lead mb-4">
                        Your subscription process was cancelled. No charges have been made to your account.
                    </p>
                    
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>No worries!</strong> You can start a new subscription anytime. Your data and progress are not affected.
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center mb-4">
                        <a href="/subscribe.php" class="btn btn-lg text-white" style="background: var(--primary-color);">
                            <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                        </a>
                        <a href="/" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <i class="bi bi-chat-dots" style="font-size: 2rem; color: var(--primary-color);"></i>
                            <h6 class="mt-2">Need Help?</h6>
                            <small class="text-muted">Contact our support</small>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-question-circle" style="font-size: 2rem; color: var(--warning-color);"></i>
                            <h6 class="mt-2">Have Questions?</h6>
                            <small class="text-muted">Check our FAQ</small>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-shield-check" style="font-size: 2rem; color: #10b981;"></i>
                            <h6 class="mt-2">100% Secure</h6>
                            <small class="text-muted">Your data is safe</small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted mb-0">
                            <small>
                                Still interested? Our plans start at just €9.99/month with no setup fees.
                                <a href="/subscribe.php" style="color: var(--primary-color);">View plans</a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
