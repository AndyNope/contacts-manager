<?php
require_once 'config.php';

echo "<h2>🔧 Automatische Login-Reparatur</h2>";
echo "<p>Schütz Schlüssel- und Schreinerservice GmbH</p>";
echo "<hr>";

// 1. Verschiedene Hash-Varianten testen
$passwords = ['admin123', 'admin', 'password'];
$hashes = [
    'Aktueller Hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Neuer Hash' => '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO',
    'Frischer Hash' => password_hash('admin123', PASSWORD_DEFAULT)
];

echo "<h3>1. Hash-Tests:</h3>";
foreach ($hashes as $name => $hash) {
    echo "<strong>{$name}:</strong><br>";
    echo "Hash: " . substr($hash, 0, 30) . "...<br>";
    
    foreach ($passwords as $pwd) {
        $result = password_verify($pwd, $hash) ? "✅" : "❌";
        echo "Password '{$pwd}': {$result}<br>";
    }
    echo "<br>";
}

// 2. Datenbank-Status prüfen
echo "<h3>2. Datenbank-Status:</h3>";
try {
    $db = new Database();
    $stmt = $db->prepare("SELECT username, password_hash FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        echo "✅ Admin-Benutzer gefunden<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Aktueller Hash: " . substr($user['password_hash'], 0, 30) . "...<br>";
        
        // Test mit aktuellem Hash
        if (password_verify('admin123', $user['password_hash'])) {
            echo "✅ <strong>Hash ist korrekt - Login sollte funktionieren!</strong><br>";
        } else {
            echo "❌ <strong>Hash ist falsch - wird automatisch repariert...</strong><br>";
            
            // Automatische Reparatur
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
            $updateStmt->bind_param("s", $newHash);
            
            if ($updateStmt->execute()) {
                echo "✅ <strong>Hash erfolgreich aktualisiert!</strong><br>";
                echo "Neuer Hash: " . substr($newHash, 0, 30) . "...<br>";
                
                // Verifikation
                if (password_verify('admin123', $newHash)) {
                    echo "✅ <strong>Verifikation erfolgreich! Login sollte jetzt funktionieren.</strong><br>";
                } else {
                    echo "❌ Verifikation fehlgeschlagen!<br>";
                }
            } else {
                echo "❌ Fehler beim Update!<br>";
            }
        }
    } else {
        echo "❌ Admin-Benutzer nicht gefunden! Wird erstellt...<br>";
        
        // Admin-Benutzer erstellen
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $insertStmt = $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES ('admin', ?)");
        $insertStmt->bind_param("s", $newHash);
        
        if ($insertStmt->execute()) {
            echo "✅ <strong>Admin-Benutzer erfolgreich erstellt!</strong><br>";
        } else {
            echo "❌ Fehler beim Erstellen!<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Datenbankfehler: " . $e->getMessage() . "<br>";
}

// 3. Login-Test
echo "<h3>3. Login-Test:</h3>";
echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
echo "<label>Benutzername:</label><br>";
echo "<input type='text' name='test_user' value='admin' style='margin: 5px 0; padding: 8px; width: 200px;'><br>";
echo "<label>Passwort:</label><br>";
echo "<input type='password' name='test_pass' value='admin123' style='margin: 5px 0; padding: 8px; width: 200px;'><br>";
echo "<input type='submit' name='test_login' value='Login testen' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; margin-top: 10px; cursor: pointer;'>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $testUser = $_POST['test_user'];
    $testPass = $_POST['test_pass'];
    
    echo "<h4>Login-Test Ergebnis:</h4>";
    
    try {
        $stmt = $db->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $testUser);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($testPass, $user['password_hash'])) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; color: #155724; margin: 10px 0;'>";
            echo "✅ <strong>LOGIN ERFOLGREICH!</strong><br>";
            echo "Sie können sich jetzt normal anmelden.<br>";
            echo "<a href='admin_login.php' style='color: #155724; font-weight: bold;'>→ Zum Admin-Login</a>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; color: #721c24; margin: 10px 0;'>";
            echo "❌ <strong>LOGIN FEHLGESCHLAGEN!</strong><br>";
            echo "Hash-Problem besteht weiterhin.<br>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; color: #721c24; margin: 10px 0;'>";
        echo "❌ Fehler: " . $e->getMessage();
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>4. Manuelle SQL-Befehle (als Backup):</h3>";
echo "<code>-- Passwort zurücksetzen:<br>";
echo "UPDATE admin_users SET password_hash = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE username = 'admin';</code><br><br>";

echo "<code>-- Oder neuen Benutzer erstellen:<br>";
echo "INSERT INTO admin_users (username, password_hash) VALUES ('schuetz', '" . password_hash('admin123', PASSWORD_DEFAULT) . "');</code><br>";
echo "<small>Login dann mit: <strong>schuetz / admin123</strong></small>";

echo "<hr>";
echo "<p><a href='admin_login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>🔐 Zum Login</a></p>";
?>
