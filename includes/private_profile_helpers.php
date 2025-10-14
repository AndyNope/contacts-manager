<?php
/**
 * Helper functions for private profile URLs and utilities
 */

/**
 * Generate a clean private profile URL
 * @param array $contact Contact data
 * @return string Private profile URL
 */
function generatePrivateProfileUrl($contact) {
    // Prefer numeric ID for cleaner URLs
    if (!empty($contact['id'])) {
        return '/private/' . $contact['id'];
    }
    
    // Fallback to slug
    if (!empty($contact['slug'])) {
        return '/private/' . $contact['slug'];
    }
    
    // Last resort: profile_slug
    if (!empty($contact['profile_slug'])) {
        return '/private/' . $contact['profile_slug'];
    }
    
    return '/private/unknown';
}

/**
 * Check if a contact is a private profile
 * @param array $contact Contact data
 * @return bool True if private profile
 */
function isPrivateProfile($contact) {
    return isset($contact['is_private_profile']) && $contact['is_private_profile'] == 1;
}

/**
 * Generate vCard download URL for private profiles
 * @param int $contactId Contact ID
 * @return string vCard download URL
 */
function generatePrivateVCardUrl($contactId) {
    return '/api/vcard?contact=' . $contactId;
}

/**
 * Generate business card URL for private profiles
 * @param int $contactId Contact ID
 * @return string Business card URL
 */
function generatePrivateBusinessCardUrl($contactId) {
    return '/api/business-card?contact=' . $contactId;
}

/**
 * Get the display name for a contact
 * @param array $contact Contact data
 * @return string Display name
 */
function getContactDisplayName($contact) {
    $firstName = $contact['first_name'] ?? '';
    $lastName = $contact['last_name'] ?? '';
    
    return trim($firstName . ' ' . $lastName) ?: 'Unknown Contact';
}

/**
 * Format phone number for display
 * @param string $phone Raw phone number
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone) {
    if (empty($phone)) {
        return '';
    }
    
    // Swiss phone number formatting
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    if (strpos($phone, '+41') === 0) {
        // Swiss number: +41 XX XXX XX XX
        $cleaned = substr($phone, 3);
        if (strlen($cleaned) === 9) {
            return '+41 ' . substr($cleaned, 0, 2) . ' ' . substr($cleaned, 2, 3) . ' ' . substr($cleaned, 5, 2) . ' ' . substr($cleaned, 7, 2);
        }
    }
    
    return $phone;
}

/**
 * Sanitize and format website URL
 * @param string $website Raw website URL
 * @return string Formatted website URL
 */
function formatWebsiteUrl($website) {
    if (empty($website)) {
        return '';
    }
    
    if (!preg_match('/^https?:\/\//', $website)) {
        $website = 'https://' . $website;
    }
    
    return $website;
}

/**
 * Generate a QR code URL for contact data
 * @param array $contact Contact data
 * @return string QR code URL
 */
function generateContactQRCode($contact) {
    $vcard = "BEGIN:VCARD\nVERSION:3.0\n";
    $vcard .= "FN:" . getContactDisplayName($contact) . "\n";
    
    if (!empty($contact['phone'])) {
        $vcard .= "TEL:" . $contact['phone'] . "\n";
    }
    if (!empty($contact['email'])) {
        $vcard .= "EMAIL:" . $contact['email'] . "\n";
    }
    if (!empty($contact['job_title'])) {
        $vcard .= "TITLE:" . $contact['job_title'] . "\n";
    }
    
    $vcard .= "END:VCARD";
    
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&data=' . urlencode($vcard);
}

/**
 * Check if the current user has permission to view a contact
 * @param array $contact Contact data
 * @param array $user Current user data
 * @return bool True if user can view contact
 */
function canViewContact($contact, $user = null) {
    // Public contacts are always viewable
    if (isset($contact['is_public']) && $contact['is_public']) {
        return true;
    }
    
    // If no user is logged in, only public contacts
    if (!$user) {
        return false;
    }
    
    // Users can always view their own contacts
    if ($user['id'] == $contact['created_by']) {
        return true;
    }
    
    // Admin users can view all contacts in their company
    if ($user['role'] === 'admin' && $user['company_id'] == $contact['company_id']) {
        return true;
    }
    
    return false;
}
?>