<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug: Starte index.php<br>";

try {
    require_once 'config.php';
    echo "Debug: config.php geladen<br>";
    
    $contactManager = new ContactManager();
    echo "Debug: ContactManager erstellt<br>";
    
    $contacts = $contactManager->getAllContacts();
    echo "Debug: Kontakte geladen: " . count($contacts) . "<br>";
    
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Digitale Kontaktverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Debug: Kontaktverwaltung funktioniert!</h1>
        <p>Anzahl Kontakte: <?= count($contacts) ?></p>
        <a href="index.php" class="btn btn-primary">Zur normalen Startseite</a>
        <a href="admin" class="btn btn-secondary">Zur Administration</a>
    </div>
</body>
</html>
