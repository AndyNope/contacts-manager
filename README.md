# EasyContact - Professional Contact Management Platform

Ein modernes, multi-tenant SaaS-System für professionelle Kontaktverwaltung mit PayPal-Integration und privaten Profilen.

## 🚀 Features

### ✨ **Kern-Funktionalitäten**
- **Multi-Tenant-Architektur** - Jedes Unternehmen hat seine eigene Instanz
- **Private Profile** - Sichere persönliche Profile unter `/private/profile/username`
- **Unternehmensprofile** - Branded Company Pages unter `/company-name`
- **PayPal-Integration** - Automatische Subscription-Verwaltung
- **Responsive Design** - Mobile-first mit Dark/Light Mode
- **Analytics & Tracking** - Detaillierte Profil-Statistiken

### 🔒 **Sicherheit & Datenschutz**
- **Sichere Authentifizierung** - Password-Hashing mit PHP
- **Private Profile-URLs** - Nur über direkten Link zugänglich
- **GDPR-konform** - Privacy Policy und Terms of Service
- **Webhook-Verifizierung** - Sichere PayPal-Kommunikation

### 💳 **Subscription-Pläne**
- **Free Plan** - Private Profile, 1 Kontakt
- **Basic Plan** - €9.99/Monat, 50 Kontakte, Company Branding
- **Premium Plan** - €29.99/Monat, Unlimited Kontakte, Advanced Features

## 🛠 Installation & Setup

### Systemanforderungen
- PHP 7.4+ mit PDO MySQL
- MySQL/MariaDB 5.7+
- Apache/Nginx mit mod_rewrite
- PayPal Developer Account

### 1. Repository klonen
```bash
git clone https://github.com/AndyNope/contacts-manager.git
cd contacts-manager
```

### 2. Datenbank einrichten
```sql
-- Datenbank erstellen
CREATE DATABASE kontaktverwaltung;

-- Benutzer erstellen
CREATE USER 'kontaktverwaltung'@'localhost' IDENTIFIED BY 'Kontakt&Verwaltung';
GRANT ALL PRIVILEGES ON kontaktverwaltung.* TO 'kontaktverwaltung'@'localhost';
FLUSH PRIVILEGES;

-- Schema importieren
mysql -u kontaktverwaltung -p kontaktverwaltung < database/schema.sql
mysql -u kontaktverwaltung -p kontaktverwaltung < setup_private_profiles.sql
```

### 3. PayPal-Integration konfigurieren

#### PayPal Subscription Plans erstellen:
```bash
php setup_paypal_db.php
```

#### PayPal Webhook einrichten:
1. **PayPal Developer Dashboard** öffnen: https://developer.paypal.com
2. **Deine App auswählen**
3. **"Webhooks" → "Add Webhook"**
4. **Webhook URL**: `https://deine-domain.com/api/paypal_webhook.php`
5. **Events auswählen**:
   - `BILLING.SUBSCRIPTION.CREATED`
   - `BILLING.SUBSCRIPTION.ACTIVATED`
   - `BILLING.SUBSCRIPTION.CANCELLED`
   - `BILLING.SUBSCRIPTION.SUSPENDED`
   - `BILLING.SUBSCRIPTION.EXPIRED`
   - `PAYMENT.SALE.COMPLETED`
6. **Webhook-ID** in `config/paypal.php` eintragen

### 4. Web Server konfigurieren

#### Apache (.htaccess bereits vorhanden):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ router.php [QSA,L]
```

#### Nginx:
```nginx
location / {
    try_files $uri $uri/ /router.php?$query_string;
}
```

## 🔧 Konfiguration

### Datenbank-Verbindung
Die Datenbankverbindung ist bereits in allen Dateien konfiguriert:
- **Host**: localhost
- **Database**: kontaktverwaltung
- **User**: kontaktverwaltung
- **Password**: Kontakt&Verwaltung

### PayPal-Konfiguration
In `config/paypal.php`:
- ✅ **Client ID**: Bereits konfiguriert
- ✅ **Client Secret**: Bereits konfiguriert
- ✅ **Subscription Plans**: Basic & Premium bereits erstellt
- ⚠️ **Webhook ID**: Nach Webhook-Erstellung eintragen

## 🌐 URL-Struktur

### Öffentliche Seiten
- `/` - Homepage/Marketing
- `/login` - Benutzer-Login
- `/register` - Registrierung
- `/terms` - Nutzungsbedingungen
- `/privacy` - Datenschutz

### Private Profile (Free Plan)
- `/private/profile/john-doe` - Private Benutzerprofile
- Sicher, nur über direkten Link zugänglich
- Ideal für Freelancer und Einzelpersonen

### Unternehmensprofile (Basic/Premium)
- `/company-name` - Unternehmens-Kontaktliste
- `/company-name/profile/contact-name` - Einzelkontakt
- Company Branding und Custom Design

### API-Endpunkte
- `/api/register.php` - Registrierung
- `/api/login.php` - Authentifizierung
- `/api/paypal_webhook.php` - PayPal Webhooks
- `/api/subscription_success.php` - Subscription Bestätigung

## 📊 Datenbank-Schema

### Haupttabellen
- **companies** - Unternehmen/Mandanten
- **users** - Benutzer mit Rollen
- **contacts** - Kontaktprofile
- **analytics_events** - Tracking & Analytics
- **payment_logs** - PayPal-Transaktionen

### Private Profile Schema
```sql
-- Zusätzliche Spalten für Private Profile
ALTER TABLE users ADD COLUMN is_private_profile TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN profile_slug VARCHAR(255) NULL;
ALTER TABLE contacts ADD COLUMN slug VARCHAR(255) NULL;
ALTER TABLE contacts ADD COLUMN profile_views INT DEFAULT 0;
```

## 🔄 Deployment

### Produktionsumgebung
1. **PayPal auf Live umstellen**:
   ```php
   // In config/paypal.php
   public $mode = 'live';
   public $baseUrl = 'https://api-m.paypal.com';
   ```

2. **SSL-Zertifikat** installieren (erforderlich für PayPal)

3. **Webhook-URL** auf Live-Domain aktualisieren

4. **Error Logging** konfigurieren:
   ```php
   // Webhook-Debugging in Produktion deaktivieren
   // error_log('PayPal Webhook received: ' . $webhook_payload);
   ```

## 🧪 Testing

### Lokale Entwicklung
```bash
# PHP Development Server
php -S localhost:8000 router.php

# Mit Apache/Nginx testen
# Stelle sicher, dass mod_rewrite aktiviert ist
```

### PayPal Sandbox Testing
- Verwende PayPal Sandbox-Accounts für Tests
- Teste alle Subscription-Flows
- Prüfe Webhook-Funktionalität mit ngrok

## 🔧 Fehlerbehebung

### Häufige Probleme

#### 1. Datenbank-Verbindungsfehler
```
Lösung: Prüfe Credentials in allen PHP-Dateien
Bereits gefixt: Alle Dateien verwenden kontaktverwaltung/Kontakt&Verwaltung
```

#### 2. PayPal Webhook funktioniert nicht
```
- Prüfe Webhook-URL in PayPal Dashboard
- Stelle sicher, dass SSL aktiviert ist
- Kontrolliere Error-Logs für Details
```

#### 3. Private Profile URLs funktionieren nicht
```
- Prüfe mod_rewrite Konfiguration
- Stelle sicher, dass .htaccess gelesen wird
- Führe setup_private_profiles.sql aus
```

#### 4. Terms/Privacy Links broken
```
Bereits gefixt: Links zeigen auf /terms und /privacy
```

## 📁 Dateistruktur

```
ams/
├── config/
│   └── paypal.php              # PayPal-Konfiguration
├── api/
│   ├── register.php            # Registrierung + Private Profiles
│   ├── login.php               # Authentifizierung
│   ├── paypal_webhook.php      # PayPal Webhook Handler
│   └── subscription_success.php # Subscription Bestätigung
├── views/
│   ├── private_profile.php     # Private Profil-Ansicht
│   ├── contact_profile.php     # Unternehmens-Kontakt
│   └── 404.php                 # Fehlerseite
├── router.php                  # Multi-Tenant URL-Routing
├── index.php                   # Homepage
├── login.php                   # Login-Seite
├── register.php                # Registrierung
├── setup_paypal_db.php         # PayPal Setup-Script
└── setup_private_profiles.sql  # Private Profile Schema
```

## 🔐 Sicherheitshinweise

### Produktions-Checklist
- [ ] SSL-Zertifikat installiert
- [ ] PayPal Webhook-Signatur-Verifizierung aktiviert
- [ ] Error-Logs aus Webhook-Handler entfernt
- [ ] Datenbank-Backups konfiguriert
- [ ] Rate-Limiting für API-Endpunkte
- [ ] Security Headers konfiguriert

### Private Profile Sicherheit
- URLs sind nur über direkten Link zugänglich
- Keine öffentliche Auflistung von Private Profiles
- Sichere Slug-Generierung verhindert Raten
- Analytics-Tracking für Sicherheitsmonitoring

## 🆘 Support

### PayPal-Integration
- **Dokumentation**: https://developer.paypal.com/docs/subscriptions/
- **Sandbox-Testing**: https://developer.paypal.com/developer/accounts/
- **Webhook-Guide**: https://developer.paypal.com/docs/api/webhooks/

### Entwicklung
- **Repository**: https://github.com/AndyNope/contacts-manager
- **Branch**: commercial
- **Issues**: GitHub Issues für Bug-Reports

---

## ✅ Status

### Completed ✅
- [x] PayPal-Integration mit echten Credentials
- [x] Private Profile System implementiert
- [x] Datenbank-Credentials gefixt
- [x] Terms/Privacy Links gefixt
- [x] Free Plan wiederhergestellt
- [x] Multi-Tenant Routing System
- [x] Webhook Handler erstellt

### In Progress 🔄
- [ ] Webhook-ID in PayPal Dashboard erstellen
- [ ] SSL-Zertifikat für Webhook-Testing
- [ ] Produktions-Deployment

### Planned 📋
- [ ] Company-Branding für Premium Plans
- [ ] Advanced Analytics Dashboard
- [ ] Email-Benachrichtigungen
- [ ] API-Rate-Limiting

**Last Updated**: 30. Juli 2025
**Version**: 2.0.0 (Commercial with Private Profiles)
