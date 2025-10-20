<?php
/**
 * Delete Contact
 * Handle contact deletion for company admins
 */

// Ensure user is authenticated as company admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /company-login');
    exit;
}

// Database connection
global $pdo;
if (!$pdo) {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

$companyId = $_SESSION['company_id'];

// Get contact ID from URL
$contactId = $this->segments[3] ?? null;
if (!$contactId || !is_numeric($contactId)) {
    header('Location: /dashboard/contacts');
    exit;
}

// Verify contact belongs to this company
$stmt = $pdo->prepare("SELECT first_name, last_name FROM contacts WHERE id = ? AND company_id = ?");
$stmt->execute([$contactId, $companyId]);
$contact = $stmt->fetch();

if (!$contact) {
    header('Location: /dashboard/contacts');
    exit;
}

try {
    // Delete the contact
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ? AND company_id = ?");
    $stmt->execute([$contactId, $companyId]);
    
    // Redirect with success message
    header('Location: /dashboard/contacts?deleted=' . urlencode($contact['first_name'] . ' ' . $contact['last_name']));
    exit;
    
} catch (Exception $e) {
    error_log('Delete contact error: ' . $e->getMessage());
    header('Location: /dashboard/contacts?error=delete_failed');
    exit;
}
?>