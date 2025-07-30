<?php
// Company-specific contact listing page
// URL: /:company/

$companyName = htmlspecialchars($company['name']);
$companySlug = htmlspecialchars($company['slug']);
$logoUrl = $company['logo_url'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $companyName ?> - Team Contacts</title>
    <meta name="description" content="Connect with the <?= $companyName ?> team. Professional digital business cards and contact information.">
    
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
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .company-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 40px 0;
        }

        .contact-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(30, 58, 138, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.15);
            text-decoration: none;
        }

        .contact-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 4px solid var(--accent-color);
        }

        .contact-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .contact-position {
            color: #6b7280;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.9rem;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #4b5563;
        }

        .featured-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: white;
            text-decoration: underline;
        }

        .footer {
            background: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <a href="/" class="back-link">
                <i class="bi bi-arrow-left me-1"></i> Back to EasyContact
            </a>
            
            <?php if ($logoUrl): ?>
                <div>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= $companyName ?> Logo" class="company-logo">
                </div>
            <?php endif; ?>
            
            <h1 class="display-4 fw-bold"><?= $companyName ?></h1>
            <p class="lead opacity-90">Meet our team and connect with us professionally</p>
            
            <?php if (count($contacts) > 0): ?>
                <p class="small opacity-75">
                    <i class="bi bi-people me-1"></i> 
                    <?= count($contacts) ?> team member<?= count($contacts) !== 1 ? 's' : '' ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contacts Grid -->
    <div class="container">
        <?php if (empty($contacts)): ?>
            <div class="empty-state">
                <i class="bi bi-people display-1 mb-4 opacity-50"></i>
                <h3>No Team Members Yet</h3>
                <p>This company hasn't added any team contacts yet.</p>
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Back to EasyContact
                </a>
            </div>
        <?php else: ?>
            <div class="contact-grid">
                <?php foreach ($contacts as $contact): ?>
                    <a href="/<?= $companySlug ?>/profile/<?= htmlspecialchars($contact['slug']) ?>" 
                       class="contact-card text-decoration-none position-relative">
                        
                        <?php if ($contact['is_featured']): ?>
                            <div class="featured-badge">
                                <i class="bi bi-star-fill"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($contact['photo_url']): ?>
                            <img src="<?= htmlspecialchars($contact['photo_url']) ?>" 
                                 alt="<?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>" 
                                 class="contact-photo">
                        <?php else: ?>
                            <div class="contact-photo d-flex align-items-center justify-content-center" 
                                 style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; font-size: 2rem; font-weight: 600;">
                                <?= strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contact-name">
                            <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                        </div>
                        
                        <?php if ($contact['position']): ?>
                            <div class="contact-position">
                                <?= htmlspecialchars($contact['position']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contact-info">
                            <?php if ($contact['email']): ?>
                                <div class="contact-info-item">
                                    <i class="bi bi-envelope"></i>
                                    <span><?= htmlspecialchars($contact['email']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($contact['phone']): ?>
                                <div class="contact-info-item">
                                    <i class="bi bi-telephone"></i>
                                    <span><?= htmlspecialchars($contact['phone']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($contact['city'] && $contact['country']): ?>
                                <div class="contact-info-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?= htmlspecialchars($contact['city'] . ', ' . $contact['country']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-eye me-1"></i>
                                <?= number_format($contact['profile_views']) ?> views
                            </small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start">
                    <p class="mb-0">
                        Powered by <strong style="color: var(--accent-color);">
                            <img src="/assets/images/easy-contact-logo-white.svg" alt="EasyContact Logo" style="height: 16px; width: 16px; margin-right: 5px;">
                            EasyContact
                        </strong>
                        - Professional Digital Business Cards
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="https://easy-contact.com" target="_blank" class="text-white text-decoration-none">
                        <i class="bi bi-globe me-1"></i> Get Your Digital Business Cards
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
