<?php
/**
 * Multi-tenant Business Card API endpoint
 * Handles business card generation for the subscription model
 */

// Get database connection from router context
global $pdo;

// If not available, create our own connection
if (!$pdo) {
    try {
        $host = 'localhost';
        $user = 'easycontact';
        $pass = 'EzC0nt@ct2025!';
        $dbname = 'easycontact';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        die('Database connection failed');
    }
}

// Get contact ID from query parameters
$contactId = $_GET['contact'] ?? $_GET['id'] ?? null;

if (!$contactId || !is_numeric($contactId)) {
    http_response_code(400);
    die('Invalid or missing contact ID');
}

try {
    // Get contact with company information
    $stmt = $pdo->prepare("
        SELECT c.*, co.name as company_name, co.slug as company_slug, co.brand_color, co.logo_url
        FROM contacts c 
        LEFT JOIN companies co ON c.company_id = co.id 
        WHERE c.id = ? AND c.is_public = TRUE
    ");
    $stmt->execute([$contactId]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
    $html = $businessCardGenerator->generateHTML();
    
    // Set headers for HTML output
    header('Content-Type: text/html; charset=utf-8');
    
    echo $html;
    
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
?>