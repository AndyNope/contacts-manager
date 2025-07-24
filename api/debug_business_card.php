<?php
// Einfache Fehlerdiagnose
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../config.php';
    echo "Config loaded successfully\n";
    
    if (!isset($_GET['id'])) {
        echo "No ID provided\n";
        exit;
    }
    
    $contactManager = new ContactManager();
    echo "ContactManager created\n";
    
    $contact = $contactManager->getContactById($_GET['id']);
    echo "Contact retrieved: " . print_r($contact, true) . "\n";
    
    if (!$contact) {
        echo "Contact not found\n";
        exit;
    }
    
    echo "Success: Contact found!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
