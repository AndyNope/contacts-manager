<?php 
require_once 'config.php';

$contactManager = new ContactManager();
$contacts = $contactManager->getAllContacts();

echo "<h1>Debug: Profil-URLs</h1>";
echo "<h2>Generierte URLs für alle Kontakte:</h2>";

foreach ($contacts as $contact) {
    $profileUrl = generateProfileUrl($contact['vorname'], $contact['nachname']);
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>" . htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) . "</strong><br>";
    echo "URL: <a href='" . $profileUrl . "'>" . $profileUrl . "</a><br>";
    echo "Vollständiger Link: <a href='https://kontakte.schuetz-schluesselservice.ch/" . $profileUrl . "' target='_blank'>Testen</a>";
    echo "</div>";
}

echo "<h2>URL-Test:</h2>";
echo "<p>Aktueller Parameter 'name': " . ($_GET['name'] ?? 'nicht gesetzt') . "</p>";
if (isset($_GET['name'])) {
    echo "<p>URL-decoded: " . urldecode($_GET['name']) . "</p>";
}
?>

<h2>Manuelle Tests:</h2>
<p>Testen Sie diese URLs:</p>
<ul>
    <li><a href="Maria-Glutz">Maria-Glutz</a></li>
    <li><a href="maria-glutz">maria-glutz</a></li>
    <li><a href="profile.php?name=Maria-Glutz">profile.php?name=Maria-Glutz (direkt)</a></li>
</ul>
