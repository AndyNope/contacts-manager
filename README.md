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
- 🎴 **PDF-Visitenkarten** - Professionelle Visitenkarten mit QR-Codes generieren
- 📱 **QR-Code Integration** - vCard-Daten als QR-Code für einfaches Scannen
- 🎨 **Corporate Design** - Anpassbares Branding und Farbschema
- 🖨️ **Print-Optimierung** - Perfekt für professionellen Druck (85mm x 54mm)

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
5. **Visitenkarte erstellen**: 
   - Klicken Sie auf "Vorschau" um die Visitenkarte anzuzeigen
   - Klicken Sie auf "PDF" um die Visitenkarte herunterzuladen

## PDF-Visitenkarten Feature 🎴

### Funktionen
- **Doppelseitige Visitenkarten** - Vorderseite mit Kontaktdaten, Rückseite mit QR-Code
- **QR-Code Integration** - Automatische vCard-Generierung für einfaches Scannen
- **Corporate Design** - Professionelles Branding mit Firmenlogo und Farbschema
- **Print-Ready Format** - Optimiert für Standard-Visitenkarten (85mm x 54mm)
- **Foto-Integration** - Kontaktfotos werden automatisch eingebunden
- **Responsive Preview** - Vorschau-Funktion vor dem Download

### Design-Merkmale
- **Vorderseite**: Blauer Farbverlauf mit Kontaktfoto, Name, Position und Kontaktdaten
- **Rückseite**: Weißer Hintergrund mit Firmenlogo, QR-Code und Service-Informationen
- **Typografie**: Optimierte Schriftgrößen für professionelle Lesbarkeit
- **Print-Sicherheit**: Ausreichende Ränder für Schnitt-Toleranzen

### Verwendung
1. **Admin-Bereich**: Visitenkarten-Buttons bei jedem Kontakt
2. **Vorschau**: Klicken Sie "Vorschau" für Browser-Anzeige
3. **Download**: Klicken Sie "PDF" für HTML-Download (konvertierbar zu PDF)
4. **Hauptseite**: PDF-Download-Button auch in der Kontaktliste verfügbar

### Anpassung
Die Visitenkarten können in `api/generate_business_card.php` angepasst werden:
- Farben und Branding
- Logo und Firmendaten
- Layout und Abstände
- Service-Beschreibungen

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

Version 1.2 - Schweiz-Edition mit PDF-Visitenkarten und verbessertem Login

## Lizenz

MIT License

Copyright (c) 2025 Andy Bui

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Autor

**Andy Bui** - *Initial work and development*

## Beitrag leisten

1. Forken Sie das Repository
2. Erstellen Sie einen Feature-Branch (`git checkout -b feature/AmazingFeature`)
3. Committen Sie Ihre Änderungen (`git commit -m 'Add some AmazingFeature'`)
4. Pushen Sie den Branch (`git push origin feature/AmazingFeature`)
5. Öffnen Sie einen Pull Request

# contacts-manager
