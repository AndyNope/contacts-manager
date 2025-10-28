<?php
/**
 * Multi-tenant Business Card API endpoint
 * Handles business card generation for the subscription model
 */

// Get database connection from router context
global $pdo;

// If not available, create our own connection
if (!$pdo) {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDatabaseConnection();
}

// Get contact ID and format from query parameters
$contactIdentifier = $_GET['contact'] ?? $_GET['id'] ?? null;
$format = $_GET['format'] ?? 'html'; // html, json

if (!$contactIdentifier) {
    http_response_code(400);
    die('Invalid or missing contact identifier');
}

try {
    // Get contact with company information - support both ID and UUID
    $contact = null;
    
    if (is_numeric($contactIdentifier)) {
        // Contact ID lookup
        $stmt = $pdo->prepare("
            SELECT c.*, co.name as company_name, co.slug as company_slug, co.brand_color, co.logo_url
            FROM contacts c 
            LEFT JOIN companies co ON c.company_id = co.id 
            WHERE c.id = ? AND c.is_public = TRUE
        ");
        $stmt->execute([$contactIdentifier]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $contactIdentifier)) {
        // UUID lookup
        $stmt = $pdo->prepare("
            SELECT c.*, co.name as company_name, co.slug as company_slug, co.brand_color, co.logo_url
            FROM contacts c 
            LEFT JOIN companies co ON c.company_id = co.id 
            WHERE c.uuid = ? AND c.is_public = TRUE
        ");
        $stmt->execute([$contactIdentifier]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$contact) {
        http_response_code(404);
        die('Contact not found or not public');
    }
    
    // Check access permissions
    if (!canAccessContactAPI($pdo, $contact)) {
        http_response_code(403);
        die('Access denied: You do not have permission to access this contact');
    }
    
    // Check if company subscription is active (except for private profiles)
    if ($contact['company_slug'] !== 'private') {
        $stmt = $pdo->prepare("SELECT subscription_status FROM companies WHERE id = ?");
        $stmt->execute([$contact['company_id']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$company || $company['subscription_status'] !== 'active') {
            http_response_code(403);
            die('Company subscription is not active');
        }
    }
    
    // Track business card generation
    trackBusinessCardGeneration($pdo, $contact['company_id'], $contact['id']);
    
    // Generate business card HTML
    $businessCardGenerator = new BusinessCardGenerator($contact);
    
    if ($format === 'preview') {
        // Return JSON for preview
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'html' => $businessCardGenerator->generateHTML(),
            'contact_name' => $contact['first_name'] . ' ' . $contact['last_name']
        ]);
    } elseif ($format === 'download') {
        // Generate downloadable PDF-ready HTML
        $html = $businessCardGenerator->generatePDFHTML();
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="business-card-' . sanitizeFileName($contact['first_name'] . '-' . $contact['last_name']) . '.html"');
        
        echo $html;
    } else {
        // Default HTML output
        $html = $businessCardGenerator->generateHTML();
        
        header('Content-Type: text/html; charset=utf-8');
        
        echo $html;
    }
    
} catch (Exception $e) {
    error_log('Business Card API Error: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error');
}

/**
 * Business Card Generator Class
 */
class BusinessCardGenerator {
    private $contact;
    private $qrCodeUrl;
    
    public function __construct($contact) {
        $this->contact = $contact;
        $this->qrCodeUrl = $this->generateQRCodeUrl();
    }
    
    private function generateQRCodeUrl() {
        // Create vCard data for QR code
        $vcard = "BEGIN:VCARD\nVERSION:3.0\n";
        $vcard .= "FN:" . $this->contact['first_name'] . " " . $this->contact['last_name'] . "\n";
        
        if (!empty($this->contact['phone'])) {
            $vcard .= "TEL:" . $this->contact['phone'] . "\n";
        }
        if (!empty($this->contact['email'])) {
            $vcard .= "EMAIL:" . $this->contact['email'] . "\n";
        }
        if (!empty($this->contact['company_name'])) {
            $vcard .= "ORG:" . $this->contact['company_name'] . "\n";
        }
        if (!empty($this->contact['job_title'])) {
            $vcard .= "TITLE:" . $this->contact['job_title'] . "\n";
        }
        
        $vcard .= "END:VCARD";
        
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&data=' . urlencode($vcard);
    }
    
    public function generateHTML() {
        $name = htmlspecialchars($this->contact['first_name'] . ' ' . $this->contact['last_name']);
        $jobTitle = htmlspecialchars($this->contact['job_title'] ?? '');
        $company = htmlspecialchars($this->contact['company_name'] ?? '');
        $phone = htmlspecialchars($this->contact['phone'] ?? '');
        $email = htmlspecialchars($this->contact['email'] ?? '');
        $website = htmlspecialchars($this->contact['website'] ?? '');
        
        // Brand color from company or default
        $brandColor = $this->contact['brand_color'] ?? '#1e3a8a';
        $accentColor = $this->contact['brand_color'] ? $this->lightenColor($brandColor, 20) : '#fbbf24';
        
        // Photo handling
        $photoHtml = '';
        if (!empty($this->contact['profile_photo'])) {
            $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $photoUrl = $baseUrl . '/' . ltrim($this->contact['profile_photo'], '/');
            $photoHtml = '<div class="profile-photo">
                <img src="' . $photoUrl . '" alt="' . $name . '" style="width: 15mm; height: 15mm; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.3);">
            </div>';
        }
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Card - ' . $name . '</title>
    <style>
        @page {
            size: 85mm 54mm;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", Arial, sans-serif;
            background: white;
        }
        
        .business-cards {
            display: flex;
            flex-direction: column;
        }
        
        .business-card {
            width: 85mm;
            height: 54mm;
            margin: 0;
            position: relative;
            overflow: hidden;
        }
        
        .front-side {
            background: linear-gradient(135deg, ' . $brandColor . ' 0%, ' . $this->darkenColor($brandColor, 10) . ' 100%);
            color: white;
            padding: 6mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        
        .profile-photo {
            position: absolute;
            top: 4mm;
            left: 4mm;
        }
        
        .name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 1mm;
            clear: both;
            ' . (!empty($this->contact['profile_photo']) ? 'margin-left: 20mm;' : '') . '
        }
        
        .job-title {
            font-size: 9px;
            margin-bottom: 1mm;
            opacity: 0.9;
            ' . (!empty($this->contact['profile_photo']) ? 'margin-left: 20mm;' : '') . '
        }
        
        .company {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 3mm;
            color: ' . $accentColor . ';
            ' . (!empty($this->contact['profile_photo']) ? 'margin-left: 20mm;' : '') . '
        }
        
        .contact-details {
            font-size: 7px;
            line-height: 1.4;
        }
        
        .contact-details div {
            margin-bottom: 0.8mm;
        }
        
        .logo-front {
            position: absolute;
            top: 3mm;
            right: 3mm;
            font-size: 8px;
            text-align: right;
            background: rgba(255,255,255,0.1);
            padding: 2mm;
            border-radius: 2mm;
        }
        
        .back-side {
            background: white;
            padding: 6mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .main-logo {
            text-align: center;
            color: ' . $brandColor . ';
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .qr-section {
            text-align: center;
            margin: 1mm 0;
        }
        
        .qr-code {
            width: 16mm;
            height: 16mm;
            border: 1px solid #e5e7eb;
        }
        
        .company-info {
            text-align: center;
            color: ' . $brandColor . ';
            font-size: 6px;
            line-height: 1.2;
        }
        
        .company-info div {
            margin-bottom: 0.5mm;
        }
        
        .services {
            font-size: 5px;
            color: #666;
            text-align: center;
            margin-top: 1mm;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact !important; }
            .business-card { page-break-after: always; }
            .business-card:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="business-cards">
        <!-- FRONT SIDE -->
        <div class="business-card">
            <div class="front-side">
                <div class="logo-front">
                    <div style="font-weight: bold; color: white;">' . strtoupper($this->contact['company_name'] ?? 'COMPANY') . '</div>
                    <div style="font-size: 4px; color: ' . $accentColor . ';">DIGITAL BUSINESS CARD</div>
                </div>
                
                ' . $photoHtml . '
                
                <div class="name">' . $name . '</div>
                ' . (!empty($jobTitle) ? '<div class="job-title">' . $jobTitle . '</div>' : '') . '
                ' . (!empty($company) ? '<div class="company">' . $company . '</div>' : '') . '
                
                <div class="contact-details">
                    ' . (!empty($phone) ? '<div>📞 ' . $phone . '</div>' : '') . '
                    ' . (!empty($email) ? '<div>✉️ ' . $email . '</div>' : '') . '
                    ' . (!empty($website) ? '<div>🌐 ' . $website . '</div>' : '') . '
                </div>
            </div>
        </div>
        
        <!-- BACK SIDE -->
        <div class="business-card">
            <div class="back-side">
                <div class="main-logo">
                    <div style="color: ' . $brandColor . '; font-size: 18px;">' . strtoupper($this->contact['company_name'] ?? 'COMPANY') . '</div>
                    <div style="color: ' . $accentColor . '; font-size: 8px;">DIGITAL CONTACT SOLUTIONS</div>
                </div>
                
                <div class="qr-section">
                    <img src="' . $this->qrCodeUrl . '" class="qr-code" alt="QR Code">
                    <div style="font-size: 5px; color: #666; margin-top: 0.5mm;">Scan to save contact</div>
                </div>
                
                <div class="company-info">
                    <div style="font-weight: bold; color: ' . $accentColor . '; margin-bottom: 0.5mm; font-size: 6px;">POWERED BY EASYCONTACT</div>
                    <div style="margin-bottom: 0.3mm;">Digital Business Solutions</div>
                    <div style="margin-bottom: 0.3mm;">www.easy-contact.com</div>
                </div>
                
                <div class="services">
                    <div>✓ Digital Business Cards • ✓ Contact Management • ✓ Analytics</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
    
    private function lightenColor($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = min(255, $r + ($percent * 255 / 100));
        $g = min(255, $g + ($percent * 255 / 100));
        $b = min(255, $b + ($percent * 255 / 100));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    private function darkenColor($hex, $percent) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, $r - ($percent * 255 / 100));
        $g = max(0, $g - ($percent * 255 / 100));
        $b = max(0, $b - ($percent * 255 / 100));
        
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    public function generatePDFHTML() {
        $name = htmlspecialchars($this->contact['first_name'] . ' ' . $this->contact['last_name']);
        $jobTitle = htmlspecialchars($this->contact['job_title'] ?? $this->contact['position'] ?? '');
        $company = htmlspecialchars($this->contact['company_name'] ?? '');
        $phone = htmlspecialchars($this->contact['phone'] ?? '');
        $email = htmlspecialchars($this->contact['email'] ?? '');
        $website = htmlspecialchars($this->contact['website'] ?? '');
        
        // Brand color from company or default
        $brandColor = $this->contact['brand_color'] ?? '#1e3a8a';
        
        // Generate profile URL for QR code
        $profileUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/' . $this->contact['company_slug'] . '/profile/' . $this->contact['slug'];
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($profileUrl);
        
        // Profile picture HTML
        $profilePictureHtml = '';
        $profilePicDiv = '';
        $contentMargin = 'margin-left: 0.8in;';
        
        if (!empty($this->contact['photo_url'])) {
            $profilePictureHtml = '
            .profile-pic {
                position: absolute;
                top: 0.15in;
                left: 0.15in;
                width: 0.6in;
                height: 0.6in;
                border-radius: 50%;
                overflow: hidden;
                border: 2px solid rgba(255,255,255,0.3);
                background: white;
            }
            
            .profile-pic img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }';
            
            $profilePicDiv = '<div class="profile-pic"><img src="' . htmlspecialchars($this->contact['photo_url']) . '" alt="' . $name . '"></div>';
        } else {
            $initials = strtoupper(substr($this->contact['first_name'], 0, 1) . substr($this->contact['last_name'], 0, 1));
            $profilePictureHtml = '
            .profile-pic {
                position: absolute;
                top: 0.15in;
                left: 0.15in;
                width: 0.6in;
                height: 0.6in;
                border-radius: 50%;
                background: rgba(255,255,255,0.2);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: bold;
                border: 2px solid rgba(255,255,255,0.3);
            }';
            
            $profilePicDiv = '<div class="profile-pic">' . $initials . '</div>';
        }
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Card - ' . $name . '</title>
    <style>
        @page {
            size: 3.5in 2in;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Helvetica Neue", Arial, sans-serif;
            width: 3.5in;
            height: 4in; /* Double height for both sides */
        }
        
        .business-card {
            width: 3.5in;
            height: 2in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            page-break-after: always;
        }
        
        /* Front side */
        .front {
            background: linear-gradient(135deg, ' . $brandColor . ' 0%, ' . $this->darkenColor($brandColor, 10) . ' 100%);
            color: white;
            padding: 0.3in;
        }
        
        /* Back side */
        .back {
            background: linear-gradient(135deg, ' . $this->darkenColor($brandColor, 10) . ' 0%, ' . $brandColor . ' 100%);
            color: white;
            padding: 0.3in;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .content {
            ' . $contentMargin . '
        }
        
        .name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            line-height: 1.1;
        }
        
        .position {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 2px;
        }
        
        .company {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        
        .contact-info {
            font-size: 9px;
            line-height: 1.3;
            opacity: 0.9;
        }
        
        .contact-info p {
            margin-bottom: 1px;
        }
        
        .logo {
            position: absolute;
            top: 0.2in;
            right: 0.2in;
            width: 0.4in;
            height: 0.4in;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }' . $profilePictureHtml . '
        
        /* QR Code side */
        .qr-section {
            text-align: center;
            flex: 1;
        }
        
        .qr-code {
            width: 1.2in;
            height: 1.2in;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
        }
        
        .qr-code img {
            width: 1in;
            height: 1in;
        }
        
        .qr-text {
            font-size: 8px;
            opacity: 0.9;
            text-align: center;
        }
        
        .back-info {
            flex: 1;
            text-align: right;
        }
        
        .back-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 4px;
            opacity: 0.9;
        }
        
        .back-tagline {
            font-size: 10px;
            opacity: 0.8;
        }
        
        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .business-card {
                page-break-after: always;
            }
        }
        
        /* Instructions for cutting */
        .instructions {
            width: 3.5in;
            padding: 0.2in;
            background: #f0f0f0;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px dashed #ccc;
        }
    </style>
</head>
<body>
    <!-- FRONT SIDE -->
    <div class="business-card front">
        ' . $profilePicDiv . '
        <div class="logo">EC</div>
        <div class="content">
            <h1 class="name">' . $name . '</h1>
            ' . ($jobTitle ? '<p class="position">' . $jobTitle . '</p>' : '') . '
            ' . ($company ? '<p class="company">' . $company . '</p>' : '') . '
            <div class="contact-info">
                ' . ($email ? '<p>' . $email . '</p>' : '') . '
                ' . ($phone ? '<p>' . $phone . '</p>' : '') . '
                ' . ($website ? '<p>' . str_replace(['http://', 'https://'], '', $website) . '</p>' : '') . '
            </div>
        </div>
    </div>
    
    <!-- BACK SIDE -->
    <div class="business-card back">
        <div class="qr-section">
            <div class="qr-code">
                <img src="' . $qrCodeUrl . '" alt="QR Code">
            </div>
            <div class="qr-text">
                Scan to view<br>digital profile
            </div>
        </div>
        
        <div class="back-info">
            <div class="back-logo">EasyContact</div>
            <div class="back-tagline">Professional Digital Cards</div>
        </div>
    </div>
    
    <!-- Cutting Instructions -->
    <div class="instructions">
        📏 Cut along the center line to separate front and back sides • Standard business card size: 3.5" × 2" (89mm × 51mm)
    </div>
</body>
</html>';
    }
}

/**
 * Track business card generation for analytics
 */
function trackBusinessCardGeneration($pdo, $companyId, $contactId) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO analytics_events (company_id, contact_id, event_type, user_agent, ip_address, referrer, created_at) 
            VALUES (?, ?, 'business_card_generated', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $companyId,
            $contactId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null
        ]);
        
    } catch (Exception $e) {
        // Log error but don't break the generation
        error_log("Business card generation tracking error: " . $e->getMessage());
    }
}

/**
 * Check if current user can access contact via API
 */
function canAccessContactAPI($pdo, $contact) {
    session_start();
    
    // Private profiles - allow access if contact is public
    if ($contact['company_slug'] === 'private') {
        return isset($contact['is_public']) && $contact['is_public'];
    }
    
    // For company contacts, check user permissions
    if (!isset($_SESSION['user_id'])) {
        // Not logged in - only allow if explicitly public
        return isset($contact['is_public']) && $contact['is_public'];
    }
    
    // Check if user belongs to the same company
    if (isset($_SESSION['company_id']) && $_SESSION['company_id'] == $contact['company_id']) {
        return true;
    }
    
    // Admin users might have broader access
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        // Still check if contact is public for admin users not in same company
        return isset($contact['is_public']) && $contact['is_public'];
    }
    
    // Default: only public contacts
    return isset($contact['is_public']) && $contact['is_public'];
}

/**
 * Sanitize filename for downloads
 */
function sanitizeFileName($filename) {
    $filename = preg_replace('/[^A-Za-z0-9\-_]/', '-', $filename);
    $filename = preg_replace('/-+/', '-', $filename);
    return trim($filename, '-');
}
?>