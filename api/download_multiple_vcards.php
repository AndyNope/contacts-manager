<?php
require_once '../config.php';

if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    http_response_code(400);
    die('Keine Kontakt-IDs angegeben');
}

$contactIds = explode(',', $_GET['ids']);
$contactIds = array_filter($contactIds, 'is_numeric');

if (empty($contactIds)) {
    http_response_code(400);
    die('Ungültige Kontakt-IDs');
}

$contactManager = new ContactManager();
$vcards = [];

foreach ($contactIds as $id) {
    $contact = $contactManager->getContactById($id);
    if ($contact) {
        $vcards[] = $contactManager->generateVCard($contact);
    }
}

if (empty($vcards)) {
    http_response_code(404);
    die('Keine Kontakte gefunden');
}

// Alle vCards kombinieren
$combinedVcards = implode('', $vcards);

// Headers für Download setzen
$filename = 'kontakte_' . date('Y-m-d_H-i-s') . '.vcf';

header('Content-Type: text/vcard; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($combinedVcards));

echo $combinedVcards;
?>
