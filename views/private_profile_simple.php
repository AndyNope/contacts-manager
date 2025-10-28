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
            <?php 
            // Dynamic background based on contact settings
            $backgroundType = $contact['background_type'] ?? 'gradient';
            $backgroundValue = $contact['background_value'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            $hasOverlay = ($contact['background_overlay'] ?? 0) == 1;
            $overlayOpacity = $contact['background_overlay_opacity'] ?? 0.3;
            
            switch($backgroundType) {
                case 'color':
                    echo "background: {$backgroundValue};";
                    break;
                case 'image':
                    if (!empty($backgroundValue)) {
                        echo "background: url('{$backgroundValue}') center/cover no-repeat fixed;";
                    } else {
                        echo "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
                    }
                    break;
                case 'video':
                    echo "background: #000;"; // Fallback for video
                    break;
                case 'gradient':
                default:
                    echo "background: {$backgroundValue};";
                    break;
            }
            ?>
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
            position: relative;
        }
        
        <?php if ($backgroundType === 'video' && !empty($backgroundValue)): ?>
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }
        <?php endif; ?>
        
        <?php if ($hasOverlay): ?>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, <?= $overlayOpacity ?>);
            z-index: -1;
            pointer-events: none;
        }
        <?php endif; ?>
        
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
        
        .btn-purple {
            background-color: #7c3aed;
            border-color: #7c3aed;
            color: white;
        }
        
        .btn-purple:hover {
            background-color: #6d28d9;
            border-color: #6d28d9;
            color: white;
        }
        
        .btn-outline-purple {
            border-color: #7c3aed;
            color: #7c3aed;
        }
        
        .btn-outline-purple:hover {
            background-color: #7c3aed;
            border-color: #7c3aed;
            color: white;
        }
    </style>
</head>
<body>
    <?php if ($backgroundType === 'video' && !empty($backgroundValue)): ?>
        <video autoplay muted loop class="video-background">
            <source src="<?= htmlspecialchars($backgroundValue) ?>" type="video/mp4">
            <!-- Fallback for unsupported video -->
        </video>
    <?php endif; ?>
    
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

                    <!-- Business Card Section (Owner Only) -->
                    <?php 
                    // Check if this is the profile owner viewing their own profile
                    $isOwner = isset($_SESSION['user_id']) && 
                              isset($_SESSION['is_private_profile']) && 
                              $_SESSION['is_private_profile'] && 
                              $_SESSION['user_id'] == ($contact['created_by'] ?? 0);
                    ?>
                    
                    <?php if ($isOwner): ?>
                    <div class="alert alert-info text-center mb-4">
                        <h6 class="mb-3"><i class="bi bi-credit-card me-2"></i>Business Card Tools</h6>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                            <button onclick="previewBusinessCard()" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye me-1"></i>Preview Card
                            </button>
                            <a href="/api/business-card-pdf.php?contact=<?= $contactId ?>&format=download" 
                               target="_blank" class="btn btn-sm btn-outline-purple">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                            </a>
                        </div>
                        <small class="d-block mt-2 text-muted">
                            <i class="bi bi-info-circle me-1"></i>Perfect for professional printing • QR code links to your profile
                        </small>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="text-center mb-4">
                        <div class="d-flex flex-wrap justify-content-center">
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
    

    
    <!-- Business Card Preview Modal -->
    <div class="modal fade" id="businessCardModal" tabindex="-1" aria-labelledby="businessCardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="businessCardModalLabel">
                        <i class="bi bi-credit-card me-2"></i>Business Card Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="businessCardContainer" class="mb-4 p-4" style="background: #f8f9fa; border-radius: 10px;">
                        <!-- Business card preview will be loaded here -->
                    </div>
                    
                    <p class="text-muted mb-4">
                        Professional business card ready for printing (3.5" × 2")<br>
                        <small>Two-sided version includes QR code on back for digital profile access</small>
                    </p>
                    
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="/api/business-card-pdf.php?contact=<?= $contactId ?>&format=download" 
                           target="_blank" class="btn btn-purple">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                        </a>
                        <button onclick="copyCardInfo()" class="btn btn-secondary">
                            <i class="bi bi-clipboard me-1"></i>Copy Info
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="alert alert-info mb-0 w-100">
                        <small>
                            <i class="bi bi-lightbulb me-1"></i>
                            <strong>Pro Tip:</strong> Download includes both front side with your info and back side with QR code. Perfect for professional printing companies!
                        </small>
                    </div>
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
        
        function previewBusinessCard() {
            const modal = new bootstrap.Modal(document.getElementById('businessCardModal'));
            const container = document.getElementById('businessCardContainer');
            
            // Show loading
            container.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            modal.show();
            
            // Load business card preview
            const contactId = <?= $contactId ?>;
            
            // Create business card preview HTML
            setTimeout(() => {
                const profilePicHtml = <?php if (!empty($contact['photo'])): ?>
                    '<div style="position: absolute; top: 15px; left: 15px; width: 45px; height: 45px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,0.3);"><img src="<?= htmlspecialchars($contact['photo']) ?>" style="width: 100%; height: 100%; object-fit: cover;"></div>'
                <?php else: ?>
                    '<div style="position: absolute; top: 15px; left: 15px; width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;"><?= strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)) ?></div>'
                <?php endif; ?>;
                
                const profileUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
                const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(profileUrl)}`;
                
                container.innerHTML = `
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <!-- FRONT SIDE -->
                        <div>
                            <h6 style="text-align: center; margin-bottom: 8px; color: #6c757d; font-size: 12px;">Front Side</h6>
                            <div style="
                                width: 280px;
                                height: 160px;
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                border-radius: 10px;
                                color: white;
                                padding: 15px;
                                display: flex;
                                flex-direction: column;
                                justify-content: center;
                                position: relative;
                                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                                font-family: 'Helvetica Neue', Arial, sans-serif;
                            ">
                                <div style="position: absolute; top: 10px; right: 10px; width: 25px; height: 25px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">EC</div>
                                ${profilePicHtml.replace('45px', '35px').replace('18px', '14px').replace('15px', '10px')}
                                <div style="margin-left: 55px;">
                                    <h1 style="font-size: 15px; font-weight: bold; margin-bottom: 3px; line-height: 1.1;"><?= htmlspecialchars($contactName) ?></h1>
                                    <?php if ($contact['position'] ?? $jobTitle): ?>
                                    <p style="font-size: 11px; opacity: 0.9; margin-bottom: 2px;"><?= htmlspecialchars($contact['position'] ?? $jobTitle) ?></p>
                                    <?php endif; ?>
                                    <?php if ($contact['company'] ?? $company): ?>
                                    <p style="font-size: 9px; opacity: 0.8; margin-bottom: 6px;"><?= htmlspecialchars($contact['company'] ?? $company) ?></p>
                                    <?php endif; ?>
                                    <div style="font-size: 8px; line-height: 1.3; opacity: 0.9;">
                                        <?php if ($contact['email'] ?? $email): ?>
                                        <p style="margin-bottom: 1px;"><?= htmlspecialchars($contact['email'] ?? $email) ?></p>
                                        <?php endif; ?>
                                        <?php if ($contact['phone'] ?? $phone): ?>
                                        <p style="margin-bottom: 1px;"><?= htmlspecialchars($contact['phone'] ?? $phone) ?></p>
                                        <?php endif; ?>
                                        <?php if ($contact['website'] ?? $website): ?>
                                        <p style="margin-bottom: 1px;"><?= htmlspecialchars(str_replace(['http://', 'https://'], '', $contact['website'] ?? $website)) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- BACK SIDE -->
                        <div>
                            <h6 style="text-align: center; margin-bottom: 8px; color: #6c757d; font-size: 12px;">Back Side</h6>
                            <div style="
                                width: 280px;
                                height: 160px;
                                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
                                border-radius: 10px;
                                color: white;
                                padding: 15px;
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                position: relative;
                                box-shadow: 0 4px 15px rgba(0,0,0,0.15);
                                font-family: 'Helvetica Neue', Arial, sans-serif;
                            ">
                                <div style="text-align: center; flex: 1;">
                                    <div style="
                                        width: 90px;
                                        height: 90px;
                                        background: white;
                                        border-radius: 6px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin: 0 auto 6px;
                                        padding: 4px;
                                    ">
                                        <img src="${qrCodeUrl}" alt="QR Code" style="width: 82px; height: 82px; border-radius: 3px;">
                                    </div>
                                    <div style="font-size: 8px; opacity: 0.9; line-height: 1.2;">
                                        Scan to view<br>digital profile
                                    </div>
                                </div>
                                
                                <div style="text-align: right; flex: 1; padding-left: 12px;">
                                    <div style="font-size: 16px; font-weight: bold; margin-bottom: 3px; opacity: 0.95;">EasyContact</div>
                                    <div style="font-size: 9px; opacity: 0.8; margin-bottom: 6px;">Professional Digital Cards</div>
                                    <div style="font-size: 7px; opacity: 0.7; word-break: break-all; line-height: 1.2;">
                                        ${profileUrl}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 500);
        }
        
        function printBusinessCard() {
            const printWindow = window.open('/api/business-card-pdf.php?contact=<?= $contactId ?>&format=download', '_blank');
            if (printWindow) {
                printWindow.onload = function() {
                    printWindow.print();
                };
            }
        }
        
        function copyCardInfo() {
            const cardInfo = `<?= htmlspecialchars($contactName) ?>
<?= ($contact['position'] ?? $jobTitle) ? htmlspecialchars($contact['position'] ?? $jobTitle) . "\n" : '' ?><?= ($contact['company'] ?? $company) ? htmlspecialchars($contact['company'] ?? $company) . "\n" : '' ?><?= ($contact['email'] ?? $email) ? htmlspecialchars($contact['email'] ?? $email) . "\n" : '' ?><?= ($contact['phone'] ?? $phone) ? htmlspecialchars($contact['phone'] ?? $phone) . "\n" : '' ?><?= ($contact['website'] ?? $website) ? htmlspecialchars($contact['website'] ?? $website) . "\n" : '' ?>`;
            
            navigator.clipboard.writeText(cardInfo).then(() => {
                alert('Business card information copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = cardInfo;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Business card information copied to clipboard!');
            });
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