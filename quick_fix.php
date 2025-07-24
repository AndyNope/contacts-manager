<?php
// Einfache automatische Login-Reparatur
require_once 'config.php';

$fixed = false;
$message = "";

try {
    $db = new Database();
    
    // Neuen Hash generieren
    $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Prüfen ob admin existiert
    $checkStmt = $db->prepare("SELECT id FROM admin_users WHERE username = 'admin'");
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_assoc();
    
    if ($exists) {
        // Update bestehenden Benutzer
        $updateStmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
        $updateStmt->bind_param("s", $correctHash);
        
        if ($updateStmt->execute()) {
            $message = "✅ Login wurde repariert! Verwenden Sie: admin / admin123";
            $fixed = true;
        } else {
            $message = "❌ Update fehlgeschlagen";
        }
    } else {
        // Neuen Admin erstellen
        $insertStmt = $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES ('admin', ?)");
        $insertStmt->bind_param("s", $correctHash);
        
        if ($insertStmt->execute()) {
            $message = "✅ Admin-Benutzer wurde erstellt! Verwenden Sie: admin / admin123";
            $fixed = true;
        } else {
            $message = "❌ Erstellung fehlgeschlagen";
        }
    }
    
    // Test-Login durchführen
    if ($fixed) {
        $testStmt = $db->prepare("SELECT password_hash FROM admin_users WHERE username = 'admin'");
        $testStmt->execute();
        $testUser = $testStmt->get_result()->fetch_assoc();
        
        if (password_verify('admin123', $testUser['password_hash'])) {
            $message .= "<br>✅ Verifikation erfolgreich!";
        } else {
            $message .= "<br>❌ Verifikation fehlgeschlagen";
            $fixed = false;
        }
    }
    
} catch (Exception $e) {
    $message = "❌ Fehler: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login repariert - Schütz Kontaktverwaltung</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        .header { color: #2c5530; border-bottom: 3px solid #2c5530; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="header">🔧 Login-Reparatur</h1>
        <p><strong>Schütz Schlüssel- und Schreinerservice GmbH</strong></p>
        
        <div class="<?= $fixed ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
        
        <?php if ($fixed): ?>
            <h3>🎉 Problem gelöst!</h3>
            <p>Das Login-System wurde automatisch repariert. Sie können sich jetzt anmelden mit:</p>
            <ul>
                <li><strong>Benutzername:</strong> admin</li>
                <li><strong>Passwort:</strong> admin123</li>
            </ul>
            
            <a href="admin_login.php" class="btn btn-success">🔐 Jetzt einloggen</a>
            <a href="index.php" class="btn">📋 Kontakte anzeigen</a>
            
        <?php else: ?>
            <h3>❌ Problem nicht gelöst</h3>
            <p>Die automatische Reparatur hat nicht funktioniert. Bitte verwenden Sie eine manuelle Lösung:</p>
            
            <h4>Manuelle SQL-Reparatur:</h4>
            <code style="background: #f8f9fa; padding: 10px; display: block; margin: 10px 0; border-radius: 4px;">
                UPDATE admin_users SET password_hash = '<?= password_hash('admin123', PASSWORD_DEFAULT) ?>' WHERE username = 'admin';
            </code>
            
            <a href="auto_fix_login.php" class="btn">🔧 Erweiterte Reparatur</a>
            <a href="debug_login.php" class="btn">🔍 Diagnose</a>
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        <small style="color: #666;">
            Bei weiteren Problemen kontaktieren Sie: +41 52 560 14 40
        </small>
    </div>
</body>
</html>
