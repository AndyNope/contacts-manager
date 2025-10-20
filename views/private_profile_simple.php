<?php
/**
 * Simple Private Profile View
 * Minimal version for testing private profile rendering
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic contact information
$contactId = $contact['id'] ?? 0;
$firstName = $contact['first_name'] ?? '';
$lastName = $contact['last_name'] ?? '';
$email = $contact['email'] ?? '';
$phone = $contact['phone'] ?? '';
$jobTitle = $contact['job_title'] ?? '';
$company = $contact['company_name'] ?? '';
$website = $contact['website'] ?? '';

$contactName = trim($firstName . ' ' . $lastName) ?: 'Unknown Contact';
$profileUrl = '/private/' . $contactId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($contactName) ?> - Private Profile</title>
    <meta name="description" content="Private professional profile for <?= htmlspecialchars($contactName) ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .avatar-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 20px;
        }
        
        .contact-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
        
        .btn-action {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Profile Card -->
                <div class="profile-card p-4 p-md-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="avatar-circle" 
                             <?php if (!empty($contact['photo'])): ?>
                                 style="background-image: url('<?= htmlspecialchars($contact['photo']) ?>'); background-size: cover; background-position: center; font-size: 0;"
                             <?php endif; ?>>
                            <?php if (empty($contact['photo'])): ?>
                                <?= strtoupper(substr($contactName, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h1 class="h2 mb-2"><?= htmlspecialchars($contactName) ?></h1>
                        <?php if ($contact['position'] ?? $jobTitle): ?>
                            <p class="text-muted mb-1"><?= htmlspecialchars($contact['position'] ?? $jobTitle) ?></p>
                        <?php endif; ?>
                        <?php if ($contact['company'] ?? $company): ?>
                            <p class="text-muted"><?= htmlspecialchars($contact['company'] ?? $company) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- QR Code Section -->
                    <div class="text-center mb-4">
                        <div class="d-inline-block p-3 bg-white rounded border">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $profileUrl) ?>" 
                                 alt="QR Code" class="img-fluid" style="max-width: 150px;">
                        </div>
                        <p class="small text-muted mt-2 mb-0">Scan to save contact</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mb-4">
                        <div class="d-flex flex-wrap justify-content-center">
                            <?php 
                            // Check if this is the profile owner viewing their own profile
                            $isOwner = isset($_SESSION['user_id']) && 
                                      isset($_SESSION['is_private_profile']) && 
                                      $_SESSION['is_private_profile'] && 
                                      $_SESSION['user_id'] == ($contact['created_by'] ?? 0);
                            ?>
                            
                            <?php if ($isOwner): ?>
                            <a href="/edit-profile.php" class="btn btn-warning btn-action">
                                <i class="bi bi-pencil-square me-2"></i>Edit Profile
                            </a>
                            <?php endif; ?>
                            
                            <a href="/api/vcard?contact=<?= $contactId ?>" 
                               class="btn btn-primary btn-action">
                                <i class="bi bi-download me-2"></i>Save Contact
                            </a>
                            
                            <button onclick="shareProfile()" class="btn btn-success btn-action">
                                <i class="bi bi-share me-2"></i>Share Profile
                            </button>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-4">
                        <?php if ($contact['email'] ?? $email): ?>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope me-3 text-primary"></i>
                                <div>
                                    <strong>Email</strong><br>
                                    <a href="mailto:<?= htmlspecialchars($contact['email'] ?? $email) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($contact['email'] ?? $email) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($contact['phone'] ?? $phone): ?>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-phone me-3 text-primary"></i>
                                <div>
                                    <strong>Phone</strong><br>
                                    <a href="tel:<?= htmlspecialchars($contact['phone'] ?? $phone) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($contact['phone'] ?? $phone) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($contact['website'] ?? $website): ?>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-globe me-3 text-primary"></i>
                                <div>
                                    <strong>Website</strong><br>
                                    <?php $siteUrl = $contact['website'] ?? $website; ?>
                                    <a href="<?= strpos($siteUrl, 'http') === 0 ? htmlspecialchars($siteUrl) : 'https://' . htmlspecialchars($siteUrl) ?>" 
                                       target="_blank" class="text-decoration-none">
                                        <?= htmlspecialchars($siteUrl) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    

                    
                    <!-- Contact Information -->
                    <div class="mb-4">
                        <?php if (!empty($contact['address'])): ?>
                        <div class="contact-item">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt me-3 text-primary"></i>
                                <div>
                                    <strong>Address</strong><br>
                                    <span><?= nl2br(htmlspecialchars($contact['address'])) ?></span>
                                    <br>
                                    <a href="https://www.google.com/maps?q=<?= urlencode($contact['address']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        View on Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Bio/Notes -->
                    <?php if (!empty($contact['notes'])): ?>
                    <div class="mb-4">
                        <h6 class="text-primary mb-2">About</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($contact['notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Social Media Links -->
                    <?php 
                    $socialLinks = array_filter([
                        'linkedin' => $contact['linkedin'] ?? '',
                        'twitter' => $contact['twitter'] ?? '',
                        'facebook' => $contact['facebook'] ?? '',
                        'instagram' => $contact['instagram'] ?? ''
                    ]);
                    ?>
                    <?php if (!empty($socialLinks)): ?>
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Connect</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($socialLinks as $platform => $url): ?>
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-<?= $platform ?> me-1"></i>
                                    <?= ucfirst($platform) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Privacy Notice -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-shield-check text-primary me-3 mt-1"></i>
                            <div>
                                <strong>Private Profile</strong>
                                <p class="mb-0 small text-muted">
                                    This is a private profile. Information is shared securely and only accessible via this link.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white-50 small">
                        Powered by <a href="/" class="text-white">EasyContact</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    

    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function shareProfile() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($contactName) ?> - Profile',
                    text: 'Check out <?= htmlspecialchars($firstName) ?>\'s professional profile',
                    url: window.location.href
                });
            } else {
                copyProfileLink();
            }
        }
        

        
        function copyProfileLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Profile link copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = window.location.href;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Profile link copied to clipboard!');
            });
        }
    </script>
</body>
</html>