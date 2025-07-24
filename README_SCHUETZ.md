# Schütz Kontaktverwaltung 🔐

Professionelle Kontaktverwaltung für **Schütz Schlüssel- und Schreinerservice GmbH** - Für Ihre Sicherheit.

## Über Schütz Schlüssel- und Schreinerservice GmbH

Inhabergeführter Familienbetrieb aus Winterthur mit **24/7 Notfalldienst** für:
- 🔑 **Schlüsselnotdienst** - Türöffnungen und Schlüsselservice
- 🪟 **Glasnotdienst** - Glasbruch und Einbruchschäden  
- 🔨 **Notfallschreiner** - Türreparaturen und Sicherung

**Standorte:**
- **Hauptsitz:** Schützenstrasse 3, 8400 Winterthur
- **Filiale:** Südstrasse 1, 8180 Bülach
- **Notfall:** +41 52 560 14 40 (24/7)

## System-Features

### 🛡️ **Sicherheitsorientiertes Design**
- Corporate Design von Schütz Schlüsselservice
- Professionelle Farbgebung (Grün für Sicherheit, Orange für Akzente)
- Responsive Design für alle Geräte
- SSL-verschlüsselte Datenübertragung

### 📋 **Kontaktverwaltung**
- **Team-Kontakte:** Geschäftsführung, Techniker, Administration
- **Fachpartner:** Dormakaba, Glutz, Hörmann und weitere
- **Kunden:** Kundenkontakte für Service und Wartung
- **Notfall-Kontakte:** 24/7 erreichbare Ansprechpartner

### 📱 **vCard-Export für Smartphones**
- Direkt importierbar in iPhone und Android
- Schweizer Telefonnummern (+41 Format)
- Vollständige Firmen- und Adressdaten
- Notizen für Spezialisierungen

## Installation für kontakte.schuetz-schluesselservice.ch

### 1. Domain-Setup
```
Subdomain: kontakte.schuetz-schluesselservice.ch
SSL-Zertifikat: Automatisch über Let's Encrypt
```

### 2. Datenbank einrichten
```sql
-- MariaDB/MySQL Datenbank erstellen
mysql -u root -p < database_setup.sql
```

### 3. Webserver-Konfiguration
```apache
<VirtualHost *:443>
    ServerName kontakte.schuetz-schluesselservice.ch
    DocumentRoot /var/www/schuetz-kontakte
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/schuetz-schluesselservice.ch.crt
    SSLCertificateKeyFile /etc/ssl/private/schuetz-schluesselservice.ch.key
</VirtualHost>
```

### 4. Standard-Login
```
URL: https://kontakte.schuetz-schluesselservice.ch/admin.php
Benutzername: admin
Passwort: admin123
```

**⚠️ Wichtig:** Passwort nach Installation ändern!

## Schütz-spezifische Funktionen

### **Team-Kontakte verwalten**
- **Lukas Schütz** - Geschäftsführer & Inhaber
- **Sandra Schütz** - Administration & Buchhaltung  
- **Marco Müller** - Schreinermeister
- **Thomas Weber** - Schlüsseltechniker (Filiale Bülach)
- **Andrea Keller** - Glasnotdienst-Koordinatorin

### **Fachpartner-Netzwerk**
- **Dormakaba** - Zutrittslösungen
- **Glutz** - Schliesssysteme
- **Hörmann** - Garagentore und Türsysteme
- Weitere Partner gemäß Website

### **24/7 Notfall-Integration**
- Direkter Tel-Link: +41 52 560 14 40
- Standort-spezifische Kontakte
- Notfall-Kategorien (Schlüssel, Glas, Schreiner)

## Corporate Design

### **Farben**
- **Primär:** #1e3a8a (Schütz-Blau)
- **Sekundär:** #3b82f6 (Hellblau)  
- **Akzent:** #fbbf24 (Gelb/Gold für Akzente)

### **Typografie**
- Professionelle, lesbare Schriftarten
- Klare Hierarchien für bessere Übersicht
- Mobile-optimierte Darstellung

### **Icons & Symbole**
- Sicherheits-Icons (Schloss, Schild, etc.)
- Branchen-spezifische Symbole
- Konsistente Verwendung

## Sicherheit & Datenschutz

### **Technische Sicherheit**
- bcrypt Passwort-Hashing
- SQL-Injection Schutz
- XSS-Schutz durch HTML-Escaping
- Session-basierte Authentifizierung

### **DSGVO-Konformität**
- Datenminimierung
- Recht auf Löschung
- Export-Funktion (vCard)
- Sichere Datenübertragung

## Support & Wartung

### **Bei Problemen**
1. **Debug-Tool verwenden:** `/debug_login.php`
2. **Passwort zurücksetzen:** `/generate_password_hash.php`
3. **Logs prüfen:** PHP Error Logs
4. **Backup erstellen:** Regelmäßige Datenbank-Backups

### **Kontakt für technischen Support**
- **E-Mail:** info@schuetz-schluesselservice.ch
- **Telefon:** +41 52 560 14 40
- **Notfall:** 24/7 erreichbar

## Updates & Erweiterungen

### **Geplante Features**
- [ ] QR-Code Generation für Kontakte
- [ ] WhatsApp Business Integration
- [ ] Termin-Buchungssystem
- [ ] Mehrsprachigkeit (DE/FR/IT)

### **Wartungsintervalle**
- **Täglich:** Automatische Backups
- **Wöchentlich:** Security Updates
- **Monatlich:** Performance-Optimierung
- **Jährlich:** Design-Updates

---

**© 2025 Schütz Schlüssel- und Schreinerservice GmbH**  
*Für Ihre Sicherheit - 24 Stunden am Tag, 365 Tage im Jahr*

🏆 **Google Bewertung: 5.0 Sterne** (92 Rezensionen)  
🇨🇭 **Fachpartner Dormakaba** | Inhabergeführter Familienbetrieb
