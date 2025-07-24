<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Kontakt-ID']);
    exit;
}

$contactManager = new ContactManager();
$contact = $contactManager->getContactById($_GET['id']);

if (!$contact) {
    http_response_code(404);
    echo json_encode(['error' => 'Kontakt nicht gefunden']);
    exit;
}

echo json_encode($contact);
?>
