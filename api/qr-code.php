<?php
/**
 * QR Code Generator API
 * Generate QR codes for contact profiles
 */

// Function to generate QR code using external API
function generateQRCode($data, $size = 200) {
    // Using QR Server API (free service)
    $encodedData = urlencode($data);
    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
    return $qrUrl;
}

// Function to generate Google Charts QR code
function generateGoogleQR($data, $size = 200) {
    $encodedData = urlencode($data);
    $qrUrl = "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedData}";
    return $qrUrl;
}

try {
    // Database connection
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Get contact ID
    $contactId = $_GET['contact'] ?? null;
    $format = $_GET['format'] ?? 'url'; // url, image, download
    $size = (int)($_GET['size'] ?? 200);
    
    if (!$contactId) {
        http_response_code(400);
        die('Contact ID required');
    }
    
    // Get contact details
    $stmt = $pdo->prepare("
        SELECT c.*, comp.slug as company_slug, comp.name as company_name,
               u.profile_slug, u.is_private_profile
        FROM contacts c 
        LEFT JOIN companies comp ON c.company_id = comp.id
        LEFT JOIN users u ON c.created_by = u.id
        WHERE c.id = ? AND c.is_public = 1
    ");
    $stmt->execute([$contactId]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        http_response_code(404);
        die('Contact not found or not public');
    }
    
    // Generate profile URL
    if ($contact['is_private_profile']) {
        // Use UUID for private profiles for security
        if (!empty($contact['uuid'])) {
            $profileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/private/" . $contact['uuid'];
        } else {
            // Fallback to contact ID for backward compatibility (this should not happen after migration)
            $profileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/private/" . $contactId;
        }
    } else {
        $profileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/" . $contact['company_slug'] . "/profile/" . $contact['slug'];
    }
    
    // Handle different formats
    switch ($format) {
        case 'image':
            // Return QR code image directly
            $qrUrl = generateQRCode($profileUrl, $size);
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=3600');
            
            // Fetch and output the image
            $imageData = file_get_contents($qrUrl);
            if ($imageData) {
                echo $imageData;
            } else {
                http_response_code(500);
                die('Failed to generate QR code');
            }
            break;
            
        case 'download':
            // Force download of QR code
            $qrUrl = generateQRCode($profileUrl, $size);
            $imageData = file_get_contents($qrUrl);
            
            if ($imageData) {
                $filename = 'qr-code-' . $contact['slug'] . '.png';
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . strlen($imageData));
                echo $imageData;
            } else {
                http_response_code(500);
                die('Failed to generate QR code');
            }
            break;
            
        case 'svg':
            // Generate SVG QR code (if available)
            $encodedData = urlencode($profileUrl);
            $svgUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&format=svg&data={$encodedData}";
            
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=3600');
            
            $svgData = file_get_contents($svgUrl);
            if ($svgData) {
                echo $svgData;
            } else {
                http_response_code(500);
                die('Failed to generate SVG QR code');
            }
            break;
            
        case 'json':
            // Return JSON with QR code info
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'contact_id' => $contactId,
                'profile_url' => $profileUrl,
                'qr_code_url' => generateQRCode($profileUrl, $size),
                'qr_code_google' => generateGoogleQR($profileUrl, $size),
                'contact_name' => trim($contact['first_name'] . ' ' . $contact['last_name']),
                'company' => $contact['company_name'],
                'sizes' => [100, 150, 200, 300, 400, 500]
            ]);
            break;
            
        default: // 'url'
            // Redirect to the actual profile
            header('Location: ' . $profileUrl);
            exit;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    if ($_GET['format'] === 'json') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        die('Error: ' . $e->getMessage());
    }
}
?>