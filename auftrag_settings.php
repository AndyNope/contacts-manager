<?php
session_start();
require_once 'config.php';

// Admin-Authentifizierung prüfen
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login');
    exit;
}

$success = false;
$error = '';

// Einstellungen laden
$settingsFile = 'admin_settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Standard-Werte
$settings = array_merge([
    'email_to' => 'info@schuetz-schluesselservice.ch',
    'email_subject' => 'Neue Auftragsanfrage - Schütz Schlüsselservice',
    'form_enabled' => true,
    'notification_email' => '',
    'auto_reply_enabled' => false,
    'auto_reply_text' => 'Vielen Dank für Ihre Anfrage. Wir melden uns schnellstmöglich bei Ihnen.'
], $settings);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newSettings = [
        'email_to' => trim($_POST['email_to'] ?? ''),
        'email_subject' => trim($_POST['email_subject'] ?? ''),
        'form_enabled' => isset($_POST['form_enabled']),
        'notification_email' => trim($_POST['notification_email'] ?? ''),
        'auto_reply_enabled' => isset($_POST['auto_reply_enabled']),
        'auto_reply_text' => trim($_POST['auto_reply_text'] ?? '')
    ];
    
    // Validierung
    if (!filter_var($newSettings['email_to'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } elseif (empty($newSettings['email_subject'])) {
        $error = 'Bitte geben Sie einen E-Mail-Betreff ein.';
    } else {
        // Einstellungen speichern
        if (file_put_contents($settingsFile, json_encode($newSettings, JSON_PRETTY_PRINT))) {
            $settings = $newSettings;
            $success = true;
        } else {
            $error = 'Einstellungen konnten nicht gespeichert werden.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auftragsformular Einstellungen - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Schütz Corporate Design */
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .settings-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.1);
            padding: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 10px 25px;
            font-weight: bold;
            border-radius: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .security-icon {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .status-active {
            background: #10b981;
            color: white;
        }
        
        .status-inactive {
            background: #ef4444;
            color: white;
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
                        <i class="bi bi-gear-fill security-icon"></i>
                        Auftragsformular Einstellungen
                    </h1>
                    <p class="mb-0 mt-1 opacity-75">Konfiguration des Auftragsformulars</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="admin" class="btn btn-outline-light me-2">
                        <i class="bi bi-arrow-left"></i> Admin
                    </a>
                    <a href="auftrag" class="btn btn-warning" target="_blank">
                        <i class="bi bi-eye"></i> Formular ansehen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Status Overview -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-toggle-on me-2" style="color: var(--primary-color);"></i>
                            Formular Status
                        </h5>
                        <span class="status-badge <?= $settings['form_enabled'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $settings['form_enabled'] ? '✓ Aktiv' : '✗ Deaktiviert' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-envelope me-2" style="color: var(--primary-color);"></i>
                            E-Mail Ziel
                        </h5>
                        <p class="mb-0"><?= htmlspecialchars($settings['email_to']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Einstellungen wurden erfolgreich gespeichert.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <form method="POST">
                <!-- Basis Einstellungen -->
                <h4 class="mb-3" style="color: var(--primary-color);">
                    <i class="bi bi-sliders security-icon"></i>
                    Basis Einstellungen
                </h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">E-Mail Empfänger</label>
                        <input type="email" class="form-control" name="email_to" required 
                               value="<?= htmlspecialchars($settings['email_to']) ?>"
                               placeholder="info@schuetz-schluesselservice.ch">
                        <div class="form-text">An diese E-Mail werden die Auftragsanfragen gesendet</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">E-Mail Betreff</label>
                        <input type="text" class="form-control" name="email_subject" required 
                               value="<?= htmlspecialchars($settings['email_subject']) ?>"
                               placeholder="Neue Auftragsanfrage">
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="form_enabled" name="form_enabled" 
                               <?= $settings['form_enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="form_enabled">
                            Auftragsformular aktiviert
                        </label>
                        <div class="form-text">Wenn deaktiviert, wird eine Nachricht angezeigt, dass das Formular nicht verfügbar ist</div>
                    </div>
                </div>

                <!-- Erweiterte Einstellungen -->
                <hr class="my-4">
                <h4 class="mb-3" style="color: var(--primary-color);">
                    <i class="bi bi-gear security-icon"></i>
                    Erweiterte Einstellungen
                </h4>

                <div class="mb-3">
                    <label class="form-label fw-bold">Zusätzliche Benachrichtigung</label>
                    <input type="email" class="form-control" name="notification_email" 
                           value="<?= htmlspecialchars($settings['notification_email']) ?>"
                           placeholder="optional@email.ch">
                    <div class="form-text">Optional: Zusätzliche E-Mail-Adresse die bei neuen Anfragen benachrichtigt wird</div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto_reply_enabled" name="auto_reply_enabled" 
                               <?= $settings['auto_reply_enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="auto_reply_enabled">
                            Automatische Antwort an Kunden
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Text für automatische Antwort</label>
                    <textarea class="form-control" name="auto_reply_text" rows="3"
                              placeholder="Text der an den Kunden als Bestätigung gesendet wird"><?= htmlspecialchars($settings['auto_reply_text']) ?></textarea>
                </div>

                <!-- URL Info -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Formular URL:</strong> 
                    <a href="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>/auftrag" 
                       target="_blank" class="text-decoration-none fw-bold">
                        <?= $_SERVER['HTTP_HOST'] ?>/auftrag
                    </a>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="admin" class="btn btn-outline-secondary me-md-2">
                        <i class="bi bi-arrow-left me-2"></i>Zurück
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Einstellungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
