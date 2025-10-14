<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - EasyContact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6b00b3;
            --secondary-color: #8b5cf6;
            --accent-color: #f59e0b;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--danger-color);
            margin-bottom: 20px;
        }

        .error-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .error-message {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }

        .security-info {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
        }

        .security-info h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .security-info ul {
            margin: 0;
            padding-left: 20px;
        }

        .security-info li {
            margin-bottom: 5px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="error-container">
                    <div class="error-icon">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    
                    <h1 class="error-title">Access Denied</h1>
                    
                    <p class="error-message">
                        <?= htmlspecialchars($defaultMessage) ?>
                        <?php if ($companyName && $companyName !== 'this resource'): ?>
                            <br><strong><?= htmlspecialchars($companyName) ?></strong> is a private company.
                        <?php endif; ?>
                    </p>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <!-- Not logged in -->
                            <a href="/login" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </a>
                            <a href="/register" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </a>
                        <?php else: ?>
                            <!-- Logged in but no access -->
                            <a href="<?= $_SESSION['company_slug'] ? '/' . $_SESSION['company_slug'] : '/' ?>" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Go to My Dashboard
                            </a>
                            <a href="/" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Homepage
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="security-info">
                        <h6><i class="bi bi-info-circle me-2"></i>Why am I seeing this?</h6>
                        <ul>
                            <li>You're trying to access a private company's contacts</li>
                            <li>You may need to be logged in with the correct account</li>
                            <li>Contact information is protected for privacy and security</li>
                            <li>Only authorized team members can access company data</li>
                        </ul>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Logged in as: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Unknown') ?></strong>
                                <?php if (isset($_SESSION['company_name'])): ?>
                                    (<?= htmlspecialchars($_SESSION['company_name']) ?>)
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>