<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug: Profil-URL Diagnose</h1>";

// Server-Info
echo "<h2>Server-Informationen:</h2>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'nicht gesetzt') . "<br>";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'nicht gesetzt') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'nicht gesetzt') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'nicht gesetzt') . "<br>";

// URL Parameter
echo "<h2>URL Parameter:</h2>";
echo "GET Parameter 'name': " . ($_GET['name'] ?? 'nicht gesetzt') . "<br>";
if (isset($_GET['name'])) {
    echo "URL-decoded: " . urldecode($_GET['name']) . "<br>";
}

// .htaccess Test
echo "<h2>.htaccess Test:</h2>";
if (function_exists('apache_get_modules')) {
    if (in_array('mod_rewrite', apache_get_modules())) {
        echo "✅ mod_rewrite ist aktiviert<br>";
    } else {
        echo "❌ mod_rewrite ist NICHT aktiviert<br>";
    }
} else {
    echo "⚠️ Kann mod_rewrite Status nicht prüfen<br>";
}

// Teste Kontakte
try {
    require_once 'config.php';
    
    $contactManager = new ContactManager();
    $contacts = $contactManager->getAllContacts();
    
    echo "<h2>Verfügbare Kontakte und URLs:</h2>";
    
    foreach ($contacts as $contact) {
        $profileUrl = generateProfileUrl($contact['vorname'], $contact['nachname']);
        echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9;'>";
        echo "<strong>" . htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) . "</strong><br>";
        echo "Generierte URL: <code>" . $profileUrl . "</code><br>";
        echo "Vollständiger Link: <a href='" . $profileUrl . "' target='_blank'>" . $profileUrl . "</a><br>";
        echo "Direkter Link: <a href='profile.php?name=" . urlencode($profileUrl) . "' target='_blank'>profile.php?name=" . urlencode($profileUrl) . "</a>";
        echo "</div>";
    }
    
    // Test der Profil-Seite selbst
    if (isset($_GET['test_name'])) {
        echo "<h2>Test des Profil-Ladeprozesses:</h2>";
        $testName = $_GET['test_name'];
        echo "Test-Name: " . htmlspecialchars($testName) . "<br>";
        
        $nameParts = explode('-', $testName);
        if (count($nameParts) >= 2) {
            $vorname = $nameParts[0];
            $nachname = implode('-', array_slice($nameParts, 1));
            
            echo "Vorname: " . htmlspecialchars($vorname) . "<br>";
            echo "Nachname: " . htmlspecialchars($nachname) . "<br>";
            
            $found = false;
            foreach ($contacts as $c) {
                $contactVorname = trim(strtolower($c['vorname']));
                $contactNachname = trim(strtolower($c['nachname']));
                $searchVorname = trim(strtolower($vorname));
                $searchNachname = trim(strtolower($nachname));
                
                if ($contactVorname === $searchVorname && $contactNachname === $searchNachname) {
                    echo "✅ Kontakt gefunden: " . htmlspecialchars($c['vorname'] . ' ' . $c['nachname']) . "<br>";
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo "❌ Kontakt nicht gefunden<br>";
                echo "Verfügbare Kontakte zum Vergleich:<br>";
                foreach ($contacts as $c) {
                    echo "- '" . htmlspecialchars(strtolower($c['vorname'])) . "' + '" . htmlspecialchars(strtolower($c['nachname'])) . "'<br>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Fehler:</h2>";
    echo htmlspecialchars($e->getMessage());
}

echo "<h2>Direkte Tests:</h2>";
echo "<ul>";
echo "<li><a href='?test_name=Maria-Glutz'>Test: Maria-Glutz</a></li>";
echo "<li><a href='profile.php?name=Maria-Glutz'>Direkt: profile.php?name=Maria-Glutz</a></li>";
echo "<li><a href='profile.php'>profile.php ohne Parameter</a></li>";
echo "</ul>";

echo "<h2>Alle Server-Variablen:</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
