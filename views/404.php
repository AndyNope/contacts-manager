<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - EasyContact</title>
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .error-container {
            text-align: center;
            padding: 60px 20px;
        }

        .error-icon {
            font-size: 8rem;
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .error-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .error-message {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 40px;
        }

        .btn-home {
            background: var(--accent-color);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            background: #d97706;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            
            <h1 class="error-title">404 - Page Not Found</h1>
            
            <p class="error-message">
                Sorry, the page you're looking for doesn't exist.<br>
                The company or contact you're trying to reach might have been moved or deleted.
            </p>
            
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="/" class="btn-home">
                    <i class="bi bi-house me-2"></i>
                    Back to EasyContact
                </a>
                
                <a href="javascript:history.back()" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Go Back
                </a>
            </div>
            
            <div class="mt-5">
                <p class="small text-muted">
                    Need help? <a href="mailto:support@easy-contact.com" class="text-decoration-none">Contact our support team</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
