<?php
session_start();
require_once __DIR__ . '/../config/database.php';

try {
    if (!isset($_GET['contact']) || !is_numeric($_GET['contact'])) {
        throw new Exception('Invalid contact ID');
    }
    
    $contactId = (int)$_GET['contact'];
    $format = $_GET['format'] ?? 'preview'; // preview, download, download-qr
    $side = $_GET['side'] ?? 'front'; // front, back
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get contact details
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$contactId]);
    $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$contact) {
        throw new Exception('Contact not found');
    }
    
    // Check if user has access to this contact
    $hasAccess = false;
    
    // Check if it's the user's own profile
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $contact['user_id']) {
        $hasAccess = true;
    }
    
    // Check if it's a company contact and user has company access
    if (!$hasAccess && isset($_SESSION['company_id']) && $contact['company_id'] == $_SESSION['company_id']) {
        $hasAccess = true;
    }
    
    if (!$hasAccess) {
        throw new Exception('Access denied');
    }
    
    // Generate business card HTML
    $businessCardHtml = generateBusinessCardHtml($contact);
    
    if ($format === 'preview') {
        // Return JSON for preview
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'html' => generateBusinessCardHtml($contact),
            'contact_name' => $contact['first_name'] . ' ' . $contact['last_name']
        ]);
    } elseif ($format === 'download') {
        // For download, return both sides HTML
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="business-card-' . sanitizeFileName($contact['first_name'] . '-' . $contact['last_name']) . '.html"');
        
        echo generateTwoSidedBusinessCard($contact);
        exit;
    } elseif ($format === 'download-qr') {
        // For QR code back side download
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="business-card-qr-' . sanitizeFileName($contact['first_name'] . '-' . $contact['last_name']) . '.html"');
        
        echo generateQRBusinessCard($contact);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function generateBusinessCardHtml($contact) {
    $name = htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']);
    $position = htmlspecialchars($contact['position'] ?? '');
    $company = htmlspecialchars($contact['company'] ?? '');
    $email = htmlspecialchars($contact['email'] ?? '');
    $phone = htmlspecialchars($contact['phone'] ?? '');
    $website = htmlspecialchars($contact['website'] ?? '');
    $address = htmlspecialchars($contact['address'] ?? '');
    
    $photoHtml = '';
    if (!empty($contact['photo'])) {
        $photoHtml = '<div class="photo" style="position: absolute; top: 15px; left: 15px; width: 45px; height: 45px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,0.3);">
            <img src="' . htmlspecialchars($contact['photo']) . '" alt="' . $name . '" style="width: 100%; height: 100%; object-fit: cover;">
        </div>';
    } else {
        $initials = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
        $photoHtml = '<div class="photo-placeholder" style="position: absolute; top: 15px; left: 15px; width: 45px; height: 45px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
            <span>' . $initials . '</span>
        </div>';
    }
    
    return '
    <div class="business-card-front">
        ' . $photoHtml . '
        <div class="content" style="margin-left: 70px;">
            <h1 class="name">' . $name . '</h1>
            ' . ($position ? '<p class="position">' . $position . '</p>' : '') . '
            ' . ($company ? '<p class="company">' . $company . '</p>' : '') . '
            <div class="contact-info">
                ' . ($email ? '<p class="email"><i class="icon">✉</i> ' . $email . '</p>' : '') . '
                ' . ($phone ? '<p class="phone"><i class="icon">📞</i> ' . $phone . '</p>' : '') . '
                ' . ($website ? '<p class="website"><i class="icon">🌐</i> ' . $website . '</p>' : '') . '
            </div>
        </div>
    </div>';
}

function generateTwoSidedBusinessCard($contact) {
    $name = htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']);
    $position = htmlspecialchars($contact['position'] ?? '');
    $company = htmlspecialchars($contact['company'] ?? '');
    $email = htmlspecialchars($contact['email'] ?? '');
    $phone = htmlspecialchars($contact['phone'] ?? '');
    $website = htmlspecialchars($contact['website'] ?? '');
    $address = htmlspecialchars($contact['address'] ?? '');
    
    // Generate profile URL for QR code
    $profileUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/private/' . ($contact['uuid'] ?? $contact['id']);
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($profileUrl);
    
    // Profile picture HTML
    $profilePictureHtml = '';
    if (!empty($contact['photo'])) {
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
        
        $profilePicDiv = '<div class="profile-pic"><img src="' . htmlspecialchars($contact['photo']) . '" alt="' . $name . '"></div>';
        $contentMargin = 'margin-left: 0.8in;';
    } else {
        $initials = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
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
        $contentMargin = 'margin-left: 0.8in;';
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.3in;
        }
        
        /* Back side */
        .back {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
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
            ' . ($position ? '<p class="position">' . $position . '</p>' : '') . '
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

function generateQRBusinessCard($contact) {
    $name = htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']);
    
    // Generate profile URL for QR code
    $profileUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/private/' . ($contact['uuid'] ?? $contact['id']);
    $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($profileUrl);
    
    // Profile picture HTML
    $profilePictureHtml = '';
    $profilePicDiv = '';
    if (!empty($contact['photo'])) {
        $profilePictureHtml = '
        .profile-pic {
            width: 0.5in;
            height: 0.5in;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.4);
            background: white;
            margin-bottom: 4px;
        }
        
        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }';
        
        $profilePicDiv = '<div class="profile-pic"><img src="' . htmlspecialchars($contact['photo']) . '" alt="' . $name . '"></div>';
    } else {
        $initials = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
        $profilePictureHtml = '
        .profile-pic {
            width: 0.5in;
            height: 0.5in;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
            border: 2px solid rgba(255,255,255,0.4);
            margin-bottom: 4px;
        }';
        
        $profilePicDiv = '<div class="profile-pic">' . $initials . '</div>';
    }
    
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Business Card - ' . $name . '</title>
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
            height: 2in;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.3in;
        }
        
        .qr-section {
            text-align: center;
            flex: 1;
        }
        
        .qr-code {
            width: 1.3in;
            height: 1.3in;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            padding: 0.05in;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }
        
        .qr-text {
            font-size: 9px;
            opacity: 0.9;
            text-align: center;
            line-height: 1.2;
        }
        
        .info-section {
            flex: 1;
            text-align: center;
            padding-left: 0.2in;
        }
        
        .brand-logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
            opacity: 0.95;
        }
        
        .tagline {
            font-size: 10px;
            opacity: 0.8;
            margin-bottom: 10px;
        }' . $profilePictureHtml . '
        
        .profile-name {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .profile-url {
            font-size: 7px;
            opacity: 0.7;
            word-break: break-all;
        }
        
        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="qr-section">
        <div class="qr-code">
            <img src="' . $qrCodeUrl . '" alt="QR Code">
        </div>
        <div class="qr-text">
            Scan to view<br>digital profile
        </div>
    </div>
    
    <div class="info-section">
        <div class="brand-logo">EasyContact</div>
        <div class="tagline">Professional Digital Cards</div>
        ' . $profilePicDiv . '
        <div class="profile-name">' . $name . '</div>
        <div class="profile-url">' . $profileUrl . '</div>
    </div>
</body>
</html>';
}
    $name = htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']);
    $position = htmlspecialchars($contact['position'] ?? '');
    $company = htmlspecialchars($contact['company'] ?? '');
    $email = htmlspecialchars($contact['email'] ?? '');
    $phone = htmlspecialchars($contact['phone'] ?? '');
    $website = htmlspecialchars($contact['website'] ?? '');
    $address = htmlspecialchars($contact['address'] ?? '');
    
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
            height: 2in;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .business-card {
            width: 100%;
            height: 100%;
            padding: 0.3in;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
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
        }
        
        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="business-card">
        <div class="logo">EC</div>
        <h1 class="name">' . $name . '</h1>
        ' . ($position ? '<p class="position">' . $position . '</p>' : '') . '
        ' . ($company ? '<p class="company">' . $company . '</p>' : '') . '
        <div class="contact-info">
            ' . ($email ? '<p>' . $email . '</p>' : '') . '
            ' . ($phone ? '<p>' . $phone . '</p>' : '') . '
            ' . ($website ? '<p>' . str_replace(['http://', 'https://'], '', $website) . '</p>' : '') . '
        </div>
    </div>
</body>
</html>';
}

function sanitizeFileName($filename) {
    $filename = preg_replace('/[^A-Za-z0-9\-_]/', '-', $filename);
    $filename = preg_replace('/-+/', '-', $filename);
    return trim($filename, '-');
}
?>