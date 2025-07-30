<?php
// Individual contact profile page
// URL: /:company/profile/:contact

$companyName = htmlspecialchars($company['name']);
$companySlug = htmlspecialchars($company['slug']);
$contactName = htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']);

// Generate QR code URL for this profile
$profileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $companySlug . "/profile/" . $contact['slug'];
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($profileUrl);

// Generate clean profile URL function
function generateCleanProfileUrl($firstName, $lastName) {
    global $companySlug;
    $name = $firstName . '-' . $lastName;
    $cleanName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $cleanName = preg_replace('/--+/', '-', $cleanName);
    $cleanName = trim($cleanName, '-');
    return "https://" . $_SERVER['HTTP_HOST'] . "/" . $companySlug . "/profile/" . $cleanName;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $contactName ?> - <?= $companyName ?></title>
    <meta name="description" content="Connect with <?= $contactName ?> from <?= $companyName ?>. Professional digital business card with contact information.">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="profile">
    <meta property="og:title" content="<?= $contactName ?> - <?= $companyName ?>">
    <meta property="og:description" content="Professional contact card for <?= $contactName ?><?= $contact['position'] ? ' - ' . htmlspecialchars($contact['position']) : '' ?>">
    <meta property="og:url" content="<?= $profileUrl ?>">
    <?php if ($contact['photo_url']): ?>
        <meta property="og:image" content="<?= htmlspecialchars($contact['photo_url']) ?>">
    <?php endif; ?>
    
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

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 20px;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .profile-position {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .profile-company {
            font-size: 1rem;
            opacity: 0.8;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-link:hover {
            color: white;
            text-decoration: underline;
        }

        .contact-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            border: 1px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .contact-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .contact-link {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .contact-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 15px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .action-btn.primary {
            background: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .action-btn.primary:hover {
            background: #d97706;
            border-color: #d97706;
        }

        .qr-section {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .qr-code {
            max-width: 200px;
            border-radius: 15px;
            margin-bottom: 15px;
        }

        .stats-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            border: 1px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .stat-item {
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .profile-name {
                font-size: 2rem;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <a href="/<?= $companySlug ?>" class="back-link">
                <i class="bi bi-arrow-left me-1"></i> Back to <?= $companyName ?> Team
            </a>
            
            <?php if ($contact['photo_url']): ?>
                <img src="<?= htmlspecialchars($contact['photo_url']) ?>" 
                     alt="<?= $contactName ?>" 
                     class="profile-photo">
            <?php else: ?>
                <div class="profile-photo d-flex align-items-center justify-content-center mx-auto" 
                     style="background: rgba(255, 255, 255, 0.2); color: white; font-size: 3rem; font-weight: 600;">
                    <?= strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            
            <h1 class="profile-name"><?= $contactName ?></h1>
            
            <?php if ($contact['position']): ?>
                <div class="profile-position"><?= htmlspecialchars($contact['position']) ?></div>
            <?php endif; ?>
            
            <?php if ($contact['department']): ?>
                <div class="profile-company"><?= htmlspecialchars($contact['department']) ?> • <?= $companyName ?></div>
            <?php else: ?>
                <div class="profile-company"><?= $companyName ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container" style="margin-top: -30px; position: relative; z-index: 10;">
        <div class="row g-4">
            <!-- Main Contact Information -->
            <div class="col-lg-8">
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="/api/business-card/<?= $contact['id'] ?>" target="_blank" class="action-btn primary">
                        <i class="bi bi-file-earmark-pdf"></i>
                        Download Business Card
                    </a>
                    
                    <a href="/api/vcard/<?= $contact['id'] ?>" class="action-btn">
                        <i class="bi bi-person-plus"></i>
                        Add to Contacts
                    </a>
                    
                    <a href="#" onclick="shareProfile()" class="action-btn">
                        <i class="bi bi-share"></i>
                        Share Profile
                    </a>
                </div>

                <!-- Contact Details -->
                <div class="contact-section">
                    <h3 class="mb-4" style="color: var(--primary-color);">
                        <i class="bi bi-person-lines-fill me-2"></i>
                        Contact Information
                    </h3>
                    
                    <?php if ($contact['email']): ?>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <div><strong>Email</strong></div>
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="contact-link">
                                    <?= htmlspecialchars($contact['email']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['phone']): ?>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <div><strong>Phone</strong></div>
                                <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" class="contact-link">
                                    <?= htmlspecialchars($contact['phone']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['website']): ?>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-globe"></i>
                            </div>
                            <div>
                                <div><strong>Website</strong></div>
                                <a href="<?= htmlspecialchars($contact['website']) ?>" target="_blank" class="contact-link">
                                    <?= htmlspecialchars($contact['website']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contact['address_line1'] || $contact['city']): ?>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <div><strong>Address</strong></div>
                                <div>
                                    <?php if ($contact['address_line1']): ?>
                                        <?= htmlspecialchars($contact['address_line1']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($contact['address_line2']): ?>
                                        <?= htmlspecialchars($contact['address_line2']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($contact['city']): ?>
                                        <?= htmlspecialchars($contact['postal_code'] . ' ' . $contact['city']) ?>
                                        <?= $contact['country'] ? ', ' . htmlspecialchars($contact['country']) : '' ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- QR Code -->
                <div class="qr-section">
                    <h5 style="color: var(--primary-color); margin-bottom: 20px;">
                        <i class="bi bi-qr-code me-2"></i>
                        Quick Connect
                    </h5>
                    <img src="<?= htmlspecialchars($qrCodeUrl) ?>" 
                         alt="QR Code for <?= $contactName ?>" 
                         class="qr-code">
                    <p class="small text-muted mb-0">
                        Scan to save this contact to your phone
                    </p>
                </div>

                <!-- Stats -->
                <div class="stats-section mt-4">
                    <h5 style="color: var(--primary-color); margin-bottom: 20px;">
                        <i class="bi bi-graph-up me-2"></i>
                        Profile Statistics
                    </h5>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($contact['profile_views']) ?></div>
                        <div class="stat-label">Profile Views</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($contact['qr_code_scans']) ?></div>
                        <div class="stat-label">QR Code Scans</div>
                    </div>
                    
                    <?php if ($contact['last_viewed_at']): ?>
                        <div class="stat-item">
                            <div class="stat-label">Last Viewed</div>
                            <div class="small text-muted">
                                <?= date('M j, Y', strtotime($contact['last_viewed_at'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Company Info -->
                <div class="contact-section mt-4">
                    <h5 style="color: var(--primary-color); margin-bottom: 20px;">
                        <img src="/assets/images/easy-contact-logo.svg" alt="EasyContact Logo" style="height: 20px; width: 20px; margin-right: 10px;">
                        Powered by EasyContact
                    </h5>
                    <p class="text-muted small mb-3">
                        Professional digital business cards for modern teams
                    </p>
                    <a href="https://easy-contact.com" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-globe me-1"></i>
                        Get Your Digital Cards
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function shareProfile() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= $contactName ?> - <?= $companyName ?>',
                    text: 'Connect with <?= $contactName ?><?= $contact['position'] ? ' - ' . htmlspecialchars($contact['position']) : '' ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Profile link copied to clipboard!');
                });
            }
        }

        // Track QR code scan when QR code is clicked
        document.querySelector('.qr-code').addEventListener('click', function() {
            fetch('/api/analytics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    contact_id: <?= $contact['id'] ?>,
                    event_type: 'qr_scan'
                })
            });
        });
    </script>
</body>
</html>
