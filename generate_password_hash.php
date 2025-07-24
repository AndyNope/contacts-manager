<?php
// Hilfsskript zum Generieren eines Admin-Passwort-Hashes
// Führen Sie dieses Script aus, um einen neuen Passwort-Hash zu generieren

$password = 'admin123'; // Ändern Sie hier das gewünschte Passwort

echo "Passwort: " . $password . "\n";
echo "Bcrypt Hash: " . password_hash($password, PASSWORD_DEFAULT) . "\n";
echo "\n";

// Für direkte Datenbank-Aktualisierung:
echo "SQL-Update-Befehl:\n";
echo "UPDATE admin_users SET password_hash = '" . password_hash($password, PASSWORD_DEFAULT) . "' WHERE username = 'admin';\n";
echo "\n";

// Test verschiedener Passwörter:
$testPasswords = ['admin123', 'admin', 'password', '123456'];

echo "Hash-Generierung für verschiedene Test-Passwörter:\n";
foreach ($testPasswords as $testPass) {
    echo "Passwort '{$testPass}': " . password_hash($testPass, PASSWORD_DEFAULT) . "\n";
}
?>
