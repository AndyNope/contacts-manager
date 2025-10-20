<?php
/**
 * Multi-tenant vCard API endpoint
 * Handles vCard downloads for the subscription model
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
        SELECT c.*, co.name as company_name, co.slug as company_slug
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
        die('Access denied: You do not have permission to download this contact');
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
    
    // Generate vCard content
    $vcard = generateVCard($contact);
    
    // Track download analytics
    trackVCardDownload($pdo, $contact['company_id'], $contact['id']);
    
    // Set headers for vCard download
    $filename = sanitizeFilename($contact['first_name'] . '_' . $contact['last_name']) . '.vcf';
    
    header('Content-Type: text/vcard; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($vcard));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    
    echo $vcard;
    
} catch (Exception $e) {
    error_log('vCard API Error: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error');
}

/**
 * Generate vCard content from contact data
 */
function generateVCard($contact) {
    $vcard = "BEGIN:VCARD\r\n";
    $vcard .= "VERSION:3.0\r\n";
    
    // Full name
    $fullName = trim($contact['first_name'] . ' ' . $contact['last_name']);
    $vcard .= "FN:" . escapeVCardValue($fullName) . "\r\n";
    
    // Structured name (Last;First;;;)
    $vcard .= "N:" . escapeVCardValue($contact['last_name']) . ";" . escapeVCardValue($contact['first_name']) . ";;;\r\n";
    
    // Phone number
    if (!empty($contact['phone'])) {
        $vcard .= "TEL;TYPE=WORK,VOICE:" . escapeVCardValue($contact['phone']) . "\r\n";
    }
    
    // Email
    if (!empty($contact['email'])) {
        $vcard .= "EMAIL;TYPE=WORK:" . escapeVCardValue($contact['email']) . "\r\n";
    }
    
    // Job title
    if (!empty($contact['job_title'])) {
        $vcard .= "TITLE:" . escapeVCardValue($contact['job_title']) . "\r\n";
    }
    
    // Organization
    if (!empty($contact['company_name'])) {
        $vcard .= "ORG:" . escapeVCardValue($contact['company_name']) . "\r\n";
    }
    
    // Address
    if (!empty($contact['street']) || !empty($contact['city']) || !empty($contact['postal_code']) || !empty($contact['country'])) {
        $address = "ADR;TYPE=WORK:;;" . 
                  escapeVCardValue($contact['street'] ?? '') . ";" . 
                  escapeVCardValue($contact['city'] ?? '') . ";;" . 
                  escapeVCardValue($contact['postal_code'] ?? '') . ";" . 
                  escapeVCardValue($contact['country'] ?? 'Switzerland');
        $vcard .= $address . "\r\n";
    }
    
    // Website
    if (!empty($contact['website'])) {
        $website = $contact['website'];
        if (!preg_match('/^https?:\/\//', $website)) {
            $website = 'https://' . $website;
        }
        $vcard .= "URL:" . escapeVCardValue($website) . "\r\n";
    }
    
    // Notes
    if (!empty($contact['bio'])) {
        $vcard .= "NOTE:" . escapeVCardValue($contact['bio']) . "\r\n";
    }
    
    // Profile URL
    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    
    if (!empty($contact['company_slug'])) {
        if ($contact['company_slug'] === 'private') {
            // For private profiles, use UUID-based URL for security
            if (!empty($contact['uuid'])) {
                $profileUrl = $baseUrl . '/private/' . $contact['uuid'];
            }
        } else if (!empty($contact['slug'])) {
            // For company profiles, use the existing format: /{company_slug}/profile/{contact_slug}
            $profileUrl = $baseUrl . '/' . $contact['company_slug'] . '/profile/' . $contact['slug'];
        }
        
        if (isset($profileUrl)) {
            $vcard .= "URL;TYPE=PROFILE:" . escapeVCardValue($profileUrl) . "\r\n";
        }
    }
    
    // Photo URL (if available)
    if (!empty($contact['profile_photo'])) {
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $photoUrl = $baseUrl . '/' . ltrim($contact['profile_photo'], '/');
        $vcard .= "PHOTO;VALUE=URL:" . escapeVCardValue($photoUrl) . "\r\n";
    }
    
    $vcard .= "END:VCARD\r\n";
    
    return $vcard;
}

/**
 * Escape special characters in vCard values
 */
function escapeVCardValue($value) {
    if (empty($value)) {
        return '';
    }
    
    // Escape special characters
    $value = str_replace(['\\', ',', ';', "\n", "\r"], ['\\\\', '\\,', '\\;', '\\n', '\\n'], $value);
    
    return $value;
}

/**
 * Sanitize filename for download
 */
function sanitizeFilename($filename) {
    // Remove or replace invalid characters
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename); // Remove multiple underscores
    $filename = trim($filename, '_');
    
    return $filename ?: 'contact';
}

/**
 * Track vCard download for analytics
 */
function trackVCardDownload($pdo, $companyId, $contactId) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO analytics_events (company_id, contact_id, event_type, user_agent, ip_address, referrer, created_at) 
            VALUES (?, ?, 'vcard_download', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $companyId,
            $contactId,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_REFERER'] ?? null
        ]);
        
        // Update download counter
        $stmt = $pdo->prepare("UPDATE contacts SET vcard_downloads = vcard_downloads + 1 WHERE id = ?");
        $stmt->execute([$contactId]);
        
    } catch (Exception $e) {
        // Log error but don't break the download
        error_log("vCard download tracking error: " . $e->getMessage());
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