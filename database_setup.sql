-- Datenbankstruktur für Kontaktverwaltung
CREATE DATABASE IF NOT EXISTS kontaktverwaltung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE kontaktverwaltung;

-- Tabelle für Kontakte
CREATE TABLE IF NOT EXISTS kontakte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vorname VARCHAR(100) NOT NULL,
    nachname VARCHAR(100) NOT NULL,
    telefon VARCHAR(20),
    email VARCHAR(150),
    position VARCHAR(100),
    firma VARCHAR(150),
    adresse TEXT,
    plz VARCHAR(10),
    ort VARCHAR(100),
    land VARCHAR(50) DEFAULT 'Schweiz',
    website VARCHAR(200),
    notizen TEXT,
    foto_url VARCHAR(300),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin-Tabelle für Benutzer (falls benötigt)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Standard Admin-Benutzer hinzufügen (Passwort: admin123)
INSERT INTO admin_users (username, password_hash) VALUES 
('admin', '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO');

-- Beispiel-Kontakte hinzufügen (Schütz Schlüsselservice Team & Partner)
INSERT INTO kontakte (vorname, nachname, telefon, email, position, firma, adresse, plz, ort, website, notizen) VALUES
('Lukas', 'Schütz', '+41 52 560 14 40', 'lukas@schuetz-schluesselservice.ch', 'Geschäftsführer & Inhaber', 'Schütz Schlüssel- und Schreinerservice GmbH', 'Schützenstrasse 3', '8400', 'Winterthur', 'https://www.schuetz-schluesselservice.ch', 'Inhabergeführter Familienbetrieb, 24/7 Notfalldienst'),
('Sandra', 'Schütz', '+41 52 560 14 41', 'sandra@schuetz-schluesselservice.ch', 'Administration & Buchhaltung', 'Schütz Schlüssel- und Schreinerservice GmbH', 'Schützenstrasse 3', '8400', 'Winterthur', 'https://www.schuetz-schluesselservice.ch', 'Bürozeiten: Mo-Fr 08:00-17:00'),
('Marco', 'Müller', '+41 52 560 14 42', 'marco@schuetz-schluesselservice.ch', 'Schreinermeister', 'Schütz Schlüssel- und Schreinerservice GmbH', 'Schützenstrasse 3', '8400', 'Winterthur', 'https://www.schuetz-schluesselservice.ch', 'Spezialist für Türreparaturen und Notfallschreiner'),
('Thomas', 'Weber', '+41 52 560 14 43', 'thomas@schuetz-schluesselservice.ch', 'Schlüsseltechniker', 'Schütz Schlüssel- und Schreinerservice GmbH', 'Südstrasse 1', '8180', 'Bülach', 'https://www.schuetz-schluesselservice.ch', 'Filiale Bülach, Spezialist für Sicherheitstechnik'),
('Andrea', 'Keller', '+41 52 560 14 44', 'andrea@schuetz-schluesselservice.ch', 'Glasnotdienst-Koordinatorin', 'Schütz Schlüssel- und Schreinerservice GmbH', 'Schützenstrasse 3', '8400', 'Winterthur', 'https://www.schuetz-schluesselservice.ch', '24/7 Glasnotdienst, Einbruchschäden'),
('Peter', 'Dormakaba', '+41 44 818 90 11', 'peter.mueller@dormakaba.com', 'Verkaufsberater', 'Dormakaba Schweiz AG', 'Hofwisenstrasse 24', '8153', 'Rümlang', 'https://www.dormakaba.com/ch-de', 'Fachpartner - Zutrittslösungen und Sicherheitstechnik'),
('Maria', 'Glutz', '+41 61 515 51 51', 'maria.schneider@glutz.com', 'Key Account Manager', 'Glutz AG', 'Segetzstrasse 13', '4502', 'Solothurn', 'https://glutz.com/ch/de', 'Fachpartner - Schliesssysteme und Sicherheitslösungen'),
('Stefan', 'Hörmann', '+41 56 675 70 00', 'stefan.klaus@hoermann.ch', 'Technischer Berater', 'Hörmann Schweiz AG', 'Industriestrasse 1', '5314', 'Kleindöttingen', 'https://www.hoermann.ch', 'Fachpartner - Garagentore und Türsysteme');
