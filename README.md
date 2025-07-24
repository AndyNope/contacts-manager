# Digitale Kontaktverwaltung 🇨🇭

Eine responsive Webanwendung zur Verwaltung und zum Download von digitalen Kontakten im vCard-Format, speziell angepasst für Schweizer Unternehmen.

## Features

- 📱 **Responsive Design** - Funktioniert auf Desktop, Tablet und Smartphone
- 👥 **Kontaktliste** - Übersichtliche Darstellung aller Kontakte
- 🔍 **Suche & Filter** - Schnelle Suche nach Name, Position oder Firma
- 📋 **Kontakt-Details** - Vollständige Informationen in einem Modal
- ⬇️ **vCard-Download** - Einzelne oder mehrere Kontakte gleichzeitig herunterladen
- ✅ **Mehrfachauswahl** - Alle oder ausgewählte Kontakte auf einmal herunterladen
- 🔐 **Admin-Bereich** - Kontakte hinzufügen, bearbeiten und löschen
- 🎨 **Moderne UI** - Bootstrap 5 mit benutzerfreundlichem Design
- 🇨🇭 **Schweiz-spezifisch** - Telefonnummern, Adressen und Beispieldaten für die Schweiz

## Systemanforderungen

- **Webserver** mit PHP 7.4 oder höher
- **MariaDB/MySQL** Datenbank
- **Browser** mit JavaScript-Unterstützung

## Installation

### 1. Datenbank einrichten

1. Erstellen Sie die MariaDB-Datenbank mit dem bereitgestellten SQL-Script:
   ```sql
   mysql -u kontaktverwaltung -p < database_setup.sql
   ```

2. Oder führen Sie das SQL-Script manuell in phpMyAdmin aus.

### 2. Dateien hochladen

1. Laden Sie alle Dateien in Ihr Webserver-Verzeichnis hoch
2. Stellen Sie sicher, dass die Ordnerstruktur korrekt ist

### 3. Login-Problem beheben (falls nötig)

Falls das Admin-Login nicht funktioniert:

1. Öffnen Sie `debug_login.php` in Ihrem Browser
2. Führen Sie die Diagnose durch
3. Oder verwenden Sie `generate_password_hash.php` um einen neuen Hash zu erstellen
4. Aktualisieren Sie die Datenbank mit dem neuen Hash

### 4. Admin-Zugang

**Standard-Login:**
- Benutzername: `admin`
- Passwort: `admin123`

**Sicherheitshinweis:** Ändern Sie das Admin-Passwort nach der Installation!

## Schweiz-spezifische Anpassungen

### Telefonnummern
- Format: `+41 XX XXX XX XX`
- Automatische Formatierung für Schweizer Nummern
- Unterstützung für Mobile und Festnetz

### Adressen
- Standard-Land: Schweiz
- Schweizer PLZ-Format (4-stellig)
- Typische Schweizer Städte in Beispieldaten

### Beispiel-Kontakte
Die Datenbank enthält Beispielkontakte von Schweizer Unternehmen aus verschiedenen Regionen:
- Zürich (SwissTech AG)
- Bern (Alpen Marketing GmbH) 
- Lausanne (Romandie Tech SA)
- Basel (Basel Design Studio)
- Lugano (Ticino Business Sagl)

## Verwendung

### Für Benutzer

1. **Kontakte anzeigen**: Öffnen Sie `index.php` in Ihrem Browser
2. **Suchen**: Verwenden Sie das Suchfeld, um Kontakte zu finden
3. **Details anzeigen**: Klicken Sie auf "Details" bei einem Kontakt
4. **Einzeldownload**: Klicken Sie auf "vCard" bei einem Kontakt
5. **Mehrfachdownload**: 
   - Wählen Sie gewünschte Kontakte mit den Checkboxen aus
   - Klicken Sie auf "Ausgewählte herunterladen"

### Für Administratoren

1. **Anmeldung**: Gehen Sie zu `admin.php` oder klicken Sie auf "Administration"
2. **Kontakt hinzufügen**: Füllen Sie das Formular aus und klicken Sie "Hinzufügen"
3. **Kontakt bearbeiten**: Klicken Sie auf das Bearbeiten-Symbol in der Tabelle
4. **Kontakt löschen**: Klicken Sie auf das Löschen-Symbol und bestätigen Sie

## vCard-Format für Schweizer Kontakte

Die heruntergeladenen Kontakte sind im standardisierten vCard 3.0 Format und enthalten:

- Name (Vor- und Nachname)
- Schweizer Telefonnummer (+41 Format)
- E-Mail-Adresse (.ch Domains)
- Position/Titel
- Schweizer Firma/Organisation
- Schweizer Adresse (PLZ, Ort, Schweiz)
- Website (.ch Domains)
- Notizen

## Smartphone-Integration

Die vCard-Dateien können direkt in die Kontakte-App des Smartphones importiert werden:

- **iPhone**: Öffnen Sie die .vcf-Datei und tippen Sie auf "Kontakt hinzufügen"
- **Android**: Öffnen Sie die .vcf-Datei mit der Kontakte-App

## Fehlerbehebung

### Login-Probleme

Wenn das Admin-Login nicht funktioniert:

1. **Debug-Tool verwenden**: Öffnen Sie `debug_login.php`
2. **Passwort-Hash prüfen**: Verwenden Sie `generate_password_hash.php`
3. **Datenbank aktualisieren**: Führen Sie den generierten SQL-Befehl aus

### Häufige Probleme

1. **"Connection failed"**: Überprüfen Sie die Datenbankverbindung in `config.php`
2. **"File not found"**: Stellen Sie sicher, dass alle Dateien hochgeladen wurden
3. **vCard-Download funktioniert nicht**: Überprüfen Sie die PHP-Berechtigung für Header-Ausgabe
4. **Schweizer Telefonnummern falsch formatiert**: Prüfen Sie das JavaScript in `admin.js`

## Datenschutz & DSGVO

- **Datenminimierung**: Nur notwendige Kontaktdaten werden gespeichert
- **Löschfunktion**: Kontakte können vollständig gelöscht werden
- **Sichere Speicherung**: Passwörter werden gehasht gespeichert
- **Export-Funktion**: Benutzer können ihre Daten jederzeit exportieren

## Version

Version 1.1 - Schweiz-Edition mit verbessertem Login und lokalen Anpassungen
# contacts-manager
