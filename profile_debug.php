<?php
// Einfache Profil-Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Profil-Debug</h1>";

try {
    require_once 'config.php';
    
    $contactManager = new ContactManager();
    $contacts = $contactManager->getAllContacts();
    
    echo "<h2>Alle verfügbaren Kontakte:</h2>";
    
    foreach ($contacts as $contact) {
        $cleanUrl = generateProfileUrl($contact['vorname'], $contact['nachname']);
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<strong>" . htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) . "</strong><br>";
        echo "Clean URL: " . htmlspecialchars($cleanUrl) . "<br>";
        echo "Profile Link: <a href='profile.php?name=" . urlencode($cleanUrl) . "'>profile.php?name=" . htmlspecialchars($cleanUrl) . "</a><br>";
        
        // Test verschiedener URL-Formate
        $urlEncoded = urlencode($cleanUrl);
        echo "URL-Encoded: " . htmlspecialchars($urlEncoded) . "<br>";
        echo "URL-Decoded: " . htmlspecialchars(urldecode($urlEncoded)) . "<br>";
        
        if (stripos($contact['nachname'], 'schütz') !== false || stripos($contact['nachname'], 'schutz') !== false) {
            echo "<strong style='color: red;'>⚠️ Umlaut-kritischer Name!</strong><br>";
            echo "Originaler Nachname: " . htmlspecialchars($contact['nachname']) . "<br>";
            echo "In URL: " . htmlspecialchars(str_replace('ü', 'u', strtolower($contact['nachname']))) . "<br>";
        }
        echo "</div>";
    }
    
    echo "<h2>Aktuelle URL-Parameter:</h2>";
    echo "REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'nicht gesetzt') . "<br>";
    echo "QUERY_STRING: " . htmlspecialchars($_SERVER['QUERY_STRING'] ?? 'nicht gesetzt') . "<br>";
    echo "GET-Parameter: " . print_r($_GET, true) . "<br>";
    
    if (isset($_GET['name'])) {
        $testName = $_GET['name'];
        echo "<h2>Test für Name: " . htmlspecialchars($testName) . "</h2>";
        echo "URL-Decoded: " . htmlspecialchars(urldecode($testName)) . "<br>";
        
        $parts = explode('-', urldecode($testName));
        if (count($parts) >= 2) {
            $vorname = trim($parts[0]);
            $nachname = trim(implode('-', array_slice($parts, 1)));
            echo "Vorname: '" . htmlspecialchars($vorname) . "'<br>";
            echo "Nachname: '" . htmlspecialchars($nachname) . "'<br>";
            
            // Suche Kontakt
            $found = false;
            foreach ($contacts as $c) {
                if (strtolower(trim($c['vorname'])) === strtolower($vorname) && 
                    strtolower(trim($c['nachname'])) === strtolower($nachname)) {
                    echo "<strong style='color: green;'>✅ Kontakt gefunden: " . htmlspecialchars($c['vorname'] . ' ' . $c['nachname']) . "</strong><br>";
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                echo "<strong style='color: red;'>❌ Kontakt nicht gefunden</strong><br>";
                echo "Mögliche Matches:<br>";
                foreach ($contacts as $c) {
                    $similarity1 = similar_text(strtolower($vorname), strtolower($c['vorname']));
                    $similarity2 = similar_text(strtolower($nachname), strtolower($c['nachname']));
                    if ($similarity1 > 2 || $similarity2 > 2) {
                        echo "- " . htmlspecialchars($c['vorname'] . ' ' . $c['nachname']) . " (Ähnlichkeit: V=$similarity1, N=$similarity2)<br>";
                    }
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Fehler:</h2>";
    echo htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Stack Trace:</strong><br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>Test-Links:</h2>";
echo "<a href='profile_debug.php?name=lukas-schutz'>Test: lukas-schutz</a><br>";
echo "<a href='profile_debug.php?name=" . urlencode('lukas-schütz') . "'>Test: lukas-schütz (URL-encoded)</a><br>";
echo "<a href='profile.php?name=lukas-schutz&debug=1'>Profile mit Debug: lukas-schutz</a><br>";
?>
