<?php
require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Ungültige Kontakt-ID');
}

$contactManager = new ContactManager();
$contact = $contactManager->getContactById($_GET['id']);

if (!$contact) {
    http_response_code(404);
    die('Kontakt nicht gefunden');
}

// vCard generieren
$vcard = $contactManager->generateVCard($contact);

// Headers für Download setzen
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $contact['vorname'] . '_' . $contact['nachname']) . '.vcf';

header('Content-Type: text/vcard; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($vcard));

echo $vcard;
?>
