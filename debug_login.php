<?php
// Debug-Script für Login-Probleme
require_once 'config.php';

echo "<h2>Debug: Login-Problem diagnostizieren</h2>";
echo "<hr>";

// 1. Datenbankverbindung testen
echo "<h3>1. Datenbankverbindung:</h3>";
try {
    $db = new Database();
    echo "✅ Datenbankverbindung erfolgreich<br>";
} catch (Exception $e) {
    echo "❌ Datenbankverbindung fehlgeschlagen: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Admin-Benutzer prüfen
echo "<h3>2. Admin-Benutzer prüfen:</h3>";
try {
    $stmt = $db->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        echo "✅ Admin-Benutzer gefunden<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Password Hash: " . substr($user['password_hash'], 0, 20) . "...<br>";
        
        // Hash-Typ prüfen
        if (substr($user['password_hash'], 0, 4) === '$2y$') {
            echo "✅ Bcrypt-Hash erkannt<br>";
        } else {
            echo "❌ Kein Bcrypt-Hash! Aktueller Hash: " . $user['password_hash'] . "<br>";
            echo "→ Führen Sie generate_password_hash.php aus und aktualisieren Sie die Datenbank<br>";
        }
        
        // Passwort-Verifizierung testen
        echo "<h3>3. Passwort-Verifizierung testen:</h3>";
        $testPassword = 'admin123';
        if (password_verify($testPassword, $user['password_hash'])) {
            echo "✅ Passwort '{$testPassword}' ist korrekt<br>";
        } else {
            echo "❌ Passwort '{$testPassword}' ist falsch<br>";
            echo "→ Neuen Hash generieren und Datenbank aktualisieren<br>";
        }
        
    } else {
        echo "❌ Admin-Benutzer nicht gefunden<br>";
        echo "→ Führen Sie database_setup.sql aus<br>";
    }
} catch (Exception $e) {
    echo "❌ Fehler beim Prüfen des Admin-Benutzers: " . $e->getMessage() . "<br>";
}

// 3. Session-Funktionalität testen
echo "<h3>4. Session-Funktionalität:</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session ist aktiv<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ Session ist nicht aktiv<br>";
}

// 4. Korrektur-SQL ausgeben
echo "<h3>5. Korrektur-SQL:</h3>";
$newHash = '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO'; // Vorgenerierter Hash für admin123
echo "<strong>Neuer korrekter Hash für 'admin123':</strong><br>";
echo "<code>UPDATE admin_users SET password_hash = '{$newHash}' WHERE username = 'admin';</code><br><br>";
echo "<small>Führen Sie diesen SQL-Befehl in phpMyAdmin aus, um das Login zu reparieren.</small><br>";

// Test des neuen Hashes
echo "<h4>Hash-Verifikation Test:</h4>";
if (password_verify('admin123', $newHash)) {
    echo "✅ <strong>Neuer Hash ist korrekt!</strong><br>";
} else {
    echo "❌ Neuer Hash ist fehlerhaft!<br>";
}

echo "<hr>";
echo "<h3>Test-Login:</h3>";
echo "<form method='POST'>";
echo "Benutzername: <input type='text' name='test_username' value='admin'><br><br>";
echo "Passwort: <input type='password' name='test_password' value='admin123'><br><br>";
echo "<input type='submit' name='test_login' value='Login testen'>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    echo "<h4>Login-Test Ergebnis:</h4>";
    
    $stmt = $db->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        echo "✅ <strong>Login erfolgreich!</strong><br>";
        echo "Sie können sich jetzt normal anmelden.<br>";
    } else {
        echo "❌ <strong>Login fehlgeschlagen!</strong><br>";
        echo "Führen Sie die obigen Korrektur-Schritte aus.<br>";
    }
}
?>
