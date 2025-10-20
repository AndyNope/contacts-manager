<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Composer is not available, so we'll use a simple PDF approach with TCPDF or similar
// For now, let's create a simple HTML-to-PDF solution using DomPDF equivalent or basic HTML

header('Content-Type: application/json');

try {
    if (!isset($_GET['contact']) || !is_numeric($_GET['contact'])) {
        throw new Exception('Invalid contact ID');
    }
    
    $contactId = (int)$_GET['contact'];
    $format = $_GET['format'] ?? 'preview'; // preview, download
    
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
        // Return HTML for preview
        echo json_encode([
            'success' => true,
            'html' => $businessCardHtml,
            'contact_name' => $contact['first_name'] . ' ' . $contact['last_name']
        ]);
    } elseif ($format === 'download') {
        // For download, we'll use a simple HTML approach since we don't have PDF libraries
        // In production, you'd want to use libraries like TCPDF, DomPDF, or wkhtmltopdf
        
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="business-card-' . sanitizeFileName($contact['first_name'] . '-' . $contact['last_name']) . '.html"');
        
        echo generatePrintableBusinessCard($contact);
        exit;
    }
    
} catch (Exception $e) {
    http_response_code(400);
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
        $photoHtml = '<div class="photo">
            <img src="' . htmlspecialchars($contact['photo']) . '" alt="' . $name . '">
        </div>';
    } else {
        $initials = strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1));
        $photoHtml = '<div class="photo-placeholder">
            <span>' . $initials . '</span>
        </div>';
    }
    
    return '
    <div class="business-card-front">
        ' . $photoHtml . '
        <div class="content">
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

function generatePrintableBusinessCard($contact) {
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