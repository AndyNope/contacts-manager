<?php
session_start();

// Database connection
require_once __DIR__ . '/../config/database.php';

try {
    $format = $_GET['format'] ?? 'preview'; // preview, download, download-qr
    $side = $_GET['side'] ?? 'front'; // front, back
    
    // Use the centralized database connection
    $pdo = getDatabaseConnection();
    
    // Get contact details - support both ID and UUID
    $contact = null;
    
    if (isset($_GET['contact'])) {
        if (is_numeric($_GET['contact'])) {
            // Contact ID lookup
            $contactId = (int)$_GET['contact'];
            $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $_GET['contact'])) {
            // UUID lookup
            $uuid = $_GET['contact'];
            $stmt = $pdo->prepare("SELECT * FROM contacts WHERE uuid = ?");
            $stmt->execute([$uuid]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    if (!$contact) {
        throw new Exception('Contact not found or invalid contact identifier');
    }
    
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
        // Generate actual PDF for download
        $html = generateTwoSidedBusinessCard($contact);
        $filename = sanitizeFileName($contact['first_name'] . '-' . $contact['last_name']);
        
        generatePDF($html, $filename);
        exit;
    } elseif ($format === 'download-qr') {
        // Generate PDF for QR code back side
        $html = generateQRBusinessCard($contact);
        $filename = sanitizeFileName($contact['first_name'] . '-' . $contact['last_name'] . '-qr');
        
        generatePDF($html, $filename);
        exit;
    } elseif ($format === 'html') {
        // For debugging - return HTML version
        header('Content-Type: text/html');
        echo generateTwoSidedBusinessCard($contact);
        exit;
    }
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Business Card PDF Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Error $e) {
    // Catch PHP fatal errors
    error_log("Business Card PDF Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
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
            padding: 0.2in;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.1in;
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
            flex: 0 0 1in; /* Fixed width for QR section */
            max-width: 1in;
        }
        
        .qr-code {
            width: 0.9in;
            height: 0.9in;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
        }
        
        .qr-code img {
            width: 0.8in;
            height: 0.8in;
        }
        
        .qr-text {
            font-size: 6px;
            opacity: 0.9;
            text-align: center;
            line-height: 1.1;
        }
        
        .back-info {
            flex: 1;
            text-align: right;
            padding-right: 0.1in;
            min-width: 0; /* Prevent flex overflow */
        }
        
        .back-logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 2px;
            opacity: 0.9;
            line-height: 1;
            word-wrap: break-word;
        }
        
        .back-tagline {
            font-size: 8px;
            opacity: 0.8;
            line-height: 1.2;
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

function sanitizeFileName($filename) {
    $filename = preg_replace('/[^A-Za-z0-9\-_]/', '-', $filename);
    $filename = preg_replace('/-+/', '-', $filename);
    return trim($filename, '-');
}

function generatePDF($html, $filename) {
    // Use simple FPDF-style PDF generation without external dependencies
    generateSimplePDF($html, $filename);
}

function generateSimplePDF($html, $filename) {
    // Create a print-ready page that auto-downloads as PDF via JavaScript
    $autoDownloadHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($filename) . '</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            z-index: 9999;
        }
        
        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin-right: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading no-print">
        <div class="spinner"></div>
        <div>Generating PDF... Please wait</div>
    </div>
    
    ' . $html . '
    
    <script>
        window.addEventListener("load", function() {
            // Hide loading screen
            document.querySelector(".loading").style.display = "none";
            
            // Auto-trigger print dialog after short delay
            setTimeout(function() {
                window.print();
                
                // After printing, try to close the window or redirect
                setTimeout(function() {
                    if (window.opener) {
                        window.close();
                    } else {
                        // If cant close, show success message
                        document.body.innerHTML = `
                            <div style="
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                height: 100vh;
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                color: white;
                                text-align: center;
                                font-family: Arial, sans-serif;
                            ">
                                <div>
                                    <h2>✅ PDF Generation Complete!</h2>
                                    <p>Your business card PDF should now be downloading.</p>
                                    <p><small>You can close this window.</small></p>
                                </div>
                            </div>
                        `;
                    }
                }, 2000);
            }, 800);
        });
    </script>
</body>
</html>';

    header('Content-Type: text/html; charset=UTF-8');
    echo $autoDownloadHtml;
}

function generatePrintableHTML($html, $filename) {
    $pdfHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Business Card - ' . htmlspecialchars($filename) . '</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .print-instructions {
                display: none !important;
            }
        }
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: white;
        }
        .print-instructions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .print-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .print-btn:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .step {
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            font-size: 14px;
        }
    </style>
    <script>
        function printCard() {
            window.print();
        }
        
        function downloadPDF() {
            // Try to trigger download via print
            if (window.print) {
                window.print();
            } else {
                alert("Please use Ctrl+P (Cmd+P on Mac) to print/save as PDF");
            }
        }
    </script>
</head>
<body>
    <div class="print-instructions">
        <h2>📄 Business Card Ready for PDF Export</h2>
        <p>Your business card is ready! Use the print function to save as PDF:</p>
        
        <div class="steps">
            <div class="step">1️⃣ Click Print below</div>
            <div class="step">2️⃣ Select "Save as PDF"</div>
            <div class="step">3️⃣ Choose filename & save</div>
        </div>
        
        <button class="print-btn" onclick="printCard()">
            🖨️ Print / Save as PDF
        </button>
        
        <p><small><strong>Tip:</strong> This will create a proper PDF file for professional printing!</small></p>
    </div>
    
    ' . $html . '
</body>
</html>';

    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $filename . '-printable.html"');
    
    echo $pdfHtml;
}
?>