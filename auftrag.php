<?php
require_once 'config.php';

// Admin-Einstellungen laden
$settings = [];
$settingsFile = 'admin_settings.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Standard-Werte falls nicht konfiguriert
$emailTo = $settings['email_to'] ?? 'info@schuetz-schluesselservice.ch';
$emailSubject = $settings['email_subject'] ?? 'Neue Auftragsanfrage - Schütz Schlüsselservice';
$formEnabled = $settings['form_enabled'] ?? true;

$success = false;
$error = '';

Neue Auftragsanfrage von der Website

=== KUNDENDATEN ===
Name: $name
E-Mail: $email
Telefon: $telefon
Adresse: $adresse
PLZ: $plz
Ort: $ort" . (!empty($firma) ? "\nFirma: $firma" : "") . "

=== AUFTRAGSDETAILS ===
Auftragstyp: $auftragType
Dringlichkeit: $dringlichkeit
Terminwunsch: " . (!empty($terminwunsch) ? date('d.m.Y H:i', strtotime($terminwunsch)) : 'Nicht angegeben') . "

=== TECHNISCHE DETAILS ===
Türtyp/Material: " . (!empty($tuerTyp) ? $tuerTyp : 'Nicht angegeben') . "
Schlosstyp: " . (!empty($schlossTyp) ? $schlossTyp : 'Nicht angegeben') . "
Anzahl Schlüssel: " . (!empty($anzahlSchluessel) ? $anzahlSchluessel : 'Nicht zutreffend') . "
Zugang: " . (!empty($zugang) ? $zugang : 'Nicht angegeben') . "

=== ZUSÄTZLICHE INFORMATIONEN ===
Besondere Umstände: " . (!empty($besondereUmstaende) ? $besondereUmstaende : 'Keine') . "
Bevorzugte Kontaktzeit: " . (!empty($kontaktZeit) ? $kontaktZeit : 'Jederzeit') . "

=== BESCHREIBUNG ===
$beschreibung

Gesendet über das Auftragsformular von " . $_SERVER['HTTP_HOST'] . "
Zeitpunkt: " . date('d.m.Y H:i:s') . "
";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $formEnabled) {
    // Auftraggeber
    $auftraggeber_name = trim($_POST['auftraggeber_name'] ?? '');
    $auftraggeber_firma = trim($_POST['auftraggeber_firma'] ?? '');
    $auftraggeber_tel = trim($_POST['auftraggeber_tel'] ?? '');
    $auftraggeber_email = trim($_POST['auftraggeber_email'] ?? '');
    // Kontakt vor Ort
    $kontakt_name = trim($_POST['kontakt_name'] ?? '');
    $kontakt_firma = trim($_POST['kontakt_firma'] ?? '');
    $kontakt_tel = trim($_POST['kontakt_tel'] ?? '');
    $kontakt_email = trim($_POST['kontakt_email'] ?? '');
    // Auftragsadresse
    $adresse_strasse = trim($_POST['adresse_strasse'] ?? '');
    $adresse_plz = trim($_POST['adresse_plz'] ?? '');
    $adresse_ort = trim($_POST['adresse_ort'] ?? '');
    // Rechnungsadresse
    $rechnung_name = trim($_POST['rechnung_name'] ?? '');
    $rechnung_firma = trim($_POST['rechnung_firma'] ?? '');
    $rechnung_tel = trim($_POST['rechnung_tel'] ?? '');
    $rechnung_email = trim($_POST['rechnung_email'] ?? '');
    // Bestätigung
    $bestaetigung_email = trim($_POST['bestaetigung_email'] ?? '');
    // Beschreibung
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    // Dateien
    $dateien = $_FILES['dateien'] ?? null;

    // Validierung
    if (empty($beschreibung) || empty($auftraggeber_name) || empty($auftraggeber_tel) || empty($auftraggeber_email) || empty($kontakt_name) || empty($kontakt_tel) || empty($kontakt_email) || empty($adresse_strasse) || empty($adresse_plz) || empty($adresse_ort) || empty($rechnung_name) || empty($rechnung_tel) || empty($rechnung_email) || empty($bestaetigung_email)) {
        $error = 'Bitte füllen Sie alle Pflichtfelder aus.';
    } elseif (!filter_var($auftraggeber_email, FILTER_VALIDATE_EMAIL) || !filter_var($kontakt_email, FILTER_VALIDATE_EMAIL) || !filter_var($rechnung_email, FILTER_VALIDATE_EMAIL) || !filter_var($bestaetigung_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte geben Sie gültige E-Mail-Adressen ein.';
    } else {
        // E-Mail senden
        $emailBody = "Neue Auftragsanfrage von der Website\n\n" .
            "=== Worum geht es? ===\n$beschreibung\n\n" .
            "=== Auftraggeber ===\nName: $auftraggeber_name\nFirma: $auftraggeber_firma\nTelefon: $auftraggeber_tel\nE-Mail: $auftraggeber_email\n\n" .
            "=== Kontakt vor Ort ===\nName: $kontakt_name\nFirma: $kontakt_firma\nTelefon: $kontakt_tel\nE-Mail: $kontakt_email\n\n" .
            "=== Auftragsadresse ===\nStraße & Nr.: $adresse_strasse\nPLZ: $adresse_plz\nOrt: $adresse_ort\n\n" .
            "=== Rechnungsadresse ===\nName: $rechnung_name\nFirma: $rechnung_firma\nTelefon: $rechnung_tel\nE-Mail: $rechnung_email\n\n" .
            "=== Bestätigungs-E-Mail ===\n$bestaetigung_email\n\n" .
            "---\nGesendet über das Auftragsformular von " . $_SERVER['HTTP_HOST'] . "\nZeitpunkt: " . date('d.m.Y H:i:s') . "\n";

        $headers = "From: $auftraggeber_email\r\n";
        $headers .= "Reply-To: $auftraggeber_email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Dateiupload-Info (echter Versand als Anhang erfordert mehr Logik)
        $fileInfo = '';
        if ($dateien && isset($dateien['name']) && is_array($dateien['name'])) {
            $fileCount = count($dateien['name']);
            $fileInfo .= "\nDateien/Medien hochgeladen: $fileCount\n";
            for ($i = 0; $i < $fileCount; $i++) {
                if (!empty($dateien['name'][$i])) {
                    $fileInfo .= "- " . $dateien['name'][$i] . " (" . $dateien['size'][$i] . " Bytes)\n";
                }
            }
        }
        $emailBody .= $fileInfo;

        if (mail($emailTo, $emailSubject, $emailBody, $headers)) {
            $success = true;
        } else {
            $error = 'E-Mail konnte nicht gesendet werden. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auftragsformular - Schütz Schlüsselservice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Schütz Corporate Design */
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
            --dark-color: #1f2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ef4444, #f87171);
            color: white;
        }
        
        .security-icon {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }
        
        .required {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="bi bi-clipboard-check security-icon"></i>
                        Auftragsformular
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        <i class="bi bi-building me-2"></i>
                        Schütz Schlüssel- und Schreinerservice GmbH
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/" class="btn btn-outline-light me-2">
                        <i class="bi bi-house-door"></i> Zur Kontaktliste
                    </a>
                    <a href="tel:+41525601440" class="btn btn-warning">
                        <i class="bi bi-telephone-fill"></i> Notdienst
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (!$formEnabled): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Das Auftragsformular ist derzeit deaktiviert. Bitte kontaktieren Sie uns direkt unter +41 52 560 14 40.
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Vielen Dank! Ihre Auftragsanfrage wurde erfolgreich gesendet. Wir melden uns schnellstmöglich bei Ihnen.
            </div>
            <div class="text-center">
                <a href="/" class="btn btn-primary">Zurück zur Kontaktliste</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <h3 class="mb-4" style="color: var(--primary-color);">
                    <i class="bi bi-person-fill security-icon"></i>
                    Ihre Auftragsanfrage
                </h3>
                
                <form method="POST">
                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Worum geht es?</legend>
                        <div class="mb-3">
                            <label for="beschreibung" class="form-label">Beschreibung *</label>
                            <textarea class="form-control" id="beschreibung" name="beschreibung" rows="4" required><?=htmlspecialchars($_POST['beschreibung'] ?? '')?></textarea>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Auftraggeber</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="auftraggeber_name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="auftraggeber_name" name="auftraggeber_name" required value="<?=htmlspecialchars($_POST['auftraggeber_name'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="auftraggeber_firma" class="form-label">Firma</label>
                                <input type="text" class="form-control" id="auftraggeber_firma" name="auftraggeber_firma" value="<?=htmlspecialchars($_POST['auftraggeber_firma'] ?? '')?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="auftraggeber_tel" class="form-label">Telefon *</label>
                                <input type="text" class="form-control" id="auftraggeber_tel" name="auftraggeber_tel" required value="<?=htmlspecialchars($_POST['auftraggeber_tel'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="auftraggeber_email" class="form-label">E-Mail *</label>
                                <input type="email" class="form-control" id="auftraggeber_email" name="auftraggeber_email" required value="<?=htmlspecialchars($_POST['auftraggeber_email'] ?? '')?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Kontakt vor Ort</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kontakt_name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="kontakt_name" name="kontakt_name" required value="<?=htmlspecialchars($_POST['kontakt_name'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kontakt_firma" class="form-label">Firma</label>
                                <input type="text" class="form-control" id="kontakt_firma" name="kontakt_firma" value="<?=htmlspecialchars($_POST['kontakt_firma'] ?? '')?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kontakt_tel" class="form-label">Telefon *</label>
                                <input type="text" class="form-control" id="kontakt_tel" name="kontakt_tel" required value="<?=htmlspecialchars($_POST['kontakt_tel'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kontakt_email" class="form-label">E-Mail *</label>
                                <input type="email" class="form-control" id="kontakt_email" name="kontakt_email" required value="<?=htmlspecialchars($_POST['kontakt_email'] ?? '')?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Auftragsadresse</legend>
                        <div class="mb-3">
                            <label for="adresse_strasse" class="form-label">Straße & Nr. *</label>
                            <input type="text" class="form-control" id="adresse_strasse" name="adresse_strasse" required value="<?=htmlspecialchars($_POST['adresse_strasse'] ?? '')?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adresse_plz" class="form-label">PLZ *</label>
                                <input type="text" class="form-control" id="adresse_plz" name="adresse_plz" required value="<?=htmlspecialchars($_POST['adresse_plz'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adresse_ort" class="form-label">Ort *</label>
                                <input type="text" class="form-control" id="adresse_ort" name="adresse_ort" required value="<?=htmlspecialchars($_POST['adresse_ort'] ?? '')?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Rechnungsadresse</legend>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rechnung_name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="rechnung_name" name="rechnung_name" required value="<?=htmlspecialchars($_POST['rechnung_name'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rechnung_firma" class="form-label">Firma</label>
                                <input type="text" class="form-control" id="rechnung_firma" name="rechnung_firma" value="<?=htmlspecialchars($_POST['rechnung_firma'] ?? '')?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rechnung_tel" class="form-label">Telefon *</label>
                                <input type="text" class="form-control" id="rechnung_tel" name="rechnung_tel" required value="<?=htmlspecialchars($_POST['rechnung_tel'] ?? '')?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rechnung_email" class="form-label">E-Mail *</label>
                                <input type="email" class="form-control" id="rechnung_email" name="rechnung_email" required value="<?=htmlspecialchars($_POST['rechnung_email'] ?? '')?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Bestätigungs-E-Mail</legend>
                        <div class="mb-3">
                            <label for="bestaetigung_email" class="form-label">E-Mail für Bestätigung *</label>
                            <input type="email" class="form-control" id="bestaetigung_email" name="bestaetigung_email" required value="<?=htmlspecialchars($_POST['bestaetigung_email'] ?? '')?>">
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 border rounded p-3">
                        <legend class="w-auto px-2">Dateien/Medien</legend>
                        <div class="mb-3">
                            <label for="dateien" class="form-label">Dateien/Medien (optional, max. 10 Dateien, 100 MB gesamt)</label>
                            <input type="file" class="form-control" id="dateien" name="dateien[]" multiple accept="image/*,application/pdf">
                        </div>
                    </fieldset>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/" class="btn btn-outline-secondary me-md-2">
                            <i class="bi bi-arrow-left me-2"></i>Abbrechen
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Absenden
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
