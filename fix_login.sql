-- Passwort-Hash für admin123 korrigieren
-- Führen Sie dieses SQL-Script aus, wenn das Login nicht funktioniert

-- Aktualisiere den Passwort-Hash für den admin Benutzer
UPDATE admin_users SET password_hash = '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO' WHERE username = 'admin';

-- Alternativ: Benutzer komplett neu erstellen (falls Update nicht funktioniert)
-- DELETE FROM admin_users WHERE username = 'admin';
-- INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO');

-- Test-Query: Benutzer anzeigen (zur Überprüfung)
-- SELECT id, username, password_hash FROM admin_users WHERE username = 'admin';
