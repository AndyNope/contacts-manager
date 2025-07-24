<?php
require_once 'config.php';
requireLogin();

$message = '';
$messageType = '';

// Settings-Datei
$settingsFile = 'admin_settings.json';

// Aktuelle Settings laden
function loadSettings() {
    global $settingsFile;
    if (file_exists($settingsFile)) {
        return json_decode(file_get_contents($settingsFile), true) ?: [];
    }
    return [];
}

// Settings speichern
function saveSettings($settings) {
    global $settingsFile;
    return file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Default Settings
$defaultSettings = [
    'phone' => '+41 52 560 14 40',
    'email' => 'info@schuetz-schluesselservice.ch',
    'website_url' => 'https://schuetz-schluesselservice.ch',
    'google_review_url' => '',
    'maps_url' => '',
    'instagram_url' => '',
    'facebook_url' => '',
    'linkedin_url' => '',
    'whatsapp_url' => ''
];

// Aktuelle Settings laden
$settings = array_merge($defaultSettings, loadSettings());

// POST-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_links') {
        $newSettings = [];
        
        // Alle Link-Felder verarbeiten
        foreach ($defaultSettings as $key => $defaultValue) {
            $newSettings[$key] = trim($_POST[$key] ?? '');
        }
        
        if (saveSettings($newSettings)) {
            $settings = $newSettings;
            $message = 'Links und Einstellungen erfolgreich gespeichert!';
            $messageType = 'success';
        } else {
            $message = 'Fehler beim Speichern der Einstellungen';
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links verwalten - Schütz Schlüsselservice</title>
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
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .admin-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .security-icon {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }
        
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1.25rem 1.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }
        
        .form-label {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }
        
        .link-preview {
            background: rgba(30, 58, 138, 0.05);
            border: 1px solid rgba(30, 58, 138, 0.1);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .link-preview a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .link-preview a:hover {
            color: var(--secondary-color);
        }
        
        /* Mobile Responsive Anpassungen */
        @media (max-width: 768px) {
            .admin-header {
                padding: 15px 0;
            }
            
            .admin-header h1 {
                font-size: 1.5rem !important;
                line-height: 1.3;
            }
            
            .admin-header p {
                font-size: 0.9rem !important;
            }
            
            .admin-header .btn {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.8rem !important;
                margin-bottom: 0.5rem;
            }
            
            .admin-header .row {
                text-align: center !important;
            }
            
            .admin-header .col-md-4 {
                text-align: center !important;
                margin-top: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .admin-header h1 {
                font-size: 1.3rem !important;
            }
            
            .admin-header .btn {
                font-size: 0.75rem !important;
                padding: 0.3rem 0.6rem !important;
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .admin-header .col-md-4 .btn:last-child {
                margin-bottom: 0;
            }
        }
        
        /* Verbessertes Button-Design */
        .admin-header .btn-outline-light {
            border: 1px solid rgba(255, 255, 255, 0.6);
            color: white;
            transition: all 0.3s ease;
        }
        
        .admin-header .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: white;
            color: white;
            transform: translateY(-1px);
        }
        
        .admin-header .btn-warning {
            background: var(--accent-color);
            border: none;
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .admin-header .btn-warning:hover {
            background: #f59e0b;
            color: var(--dark-color);
            transform: translateY(-1px);
        }
        
        .admin-header .btn-danger {
            background: #dc3545;
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .admin-header .btn-danger:hover {
            background: #c82333;
            color: white;
            transform: translateY(-1px);
        }
        
        /* Navbar Styling */
        .admin-header .navbar-brand h1 {
            font-size: 1.5rem;
        }
        
        .admin-header .navbar-brand p {
            font-size: 0.9rem;
        }
        
        .admin-header .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.25rem 0.5rem;
        }
        
        .admin-header .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .admin-header .nav-link {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            margin: 0.25rem 0.25rem;
            transition: all 0.3s ease;
        }
        
        .admin-header .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-1px);
        }
        
        .admin-header .nav-link.btn {
            margin: 0.25rem 0.25rem;
        }
        
        /* Mobile Navbar Anpassungen */
        @media (max-width: 991px) {
            .admin-header .navbar-brand h1 {
                font-size: 1.3rem;
                line-height: 1.3;
            }
            
            .admin-header .navbar-brand p {
                font-size: 0.8rem;
            }
            
            .admin-header .navbar-collapse {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .admin-header .nav-link {
                margin: 0.25rem 0;
                padding: 0.75rem 1rem;
                border-radius: 8px;
                background: rgba(255, 255, 255, 0.1);
            }
            
            .admin-header .nav-link.btn {
                display: block;
                text-align: center;
                margin: 0.5rem 0;
            }
        }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-12">
                    <nav class="navbar navbar-expand-lg navbar-dark">
                        <div class="container-fluid p-0">
                            <!-- Brand/Title -->
                            <div class="navbar-brand mb-0 me-4">
                                <h1 class="mb-0 text-white">
                                    <i class="bi bi-link-45deg security-icon"></i>
                                    Links & Einstellungen verwalten
                                </h1>
                                <p class="mb-0 mt-1 text-white-50 small">
                                    <i class="bi bi-gear me-2"></i>
                                    Schütz Schlüssel- und Schreinerservice GmbH
                                </p>
                            </div>
                            
                            <!-- Mobile Hamburger Button -->
                            <button class="navbar-toggler border-0 shadow-none" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#adminNavbar" 
                                    aria-controls="adminNavbar" aria-expanded="false" 
                                    aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                            
                            <!-- Collapsible Navigation -->
                            <div class="collapse navbar-collapse" id="adminNavbar">
                                <ul class="navbar-nav ms-auto align-items-lg-center">
                                    <li class="nav-item">
                                        <a href="admin.php" class="nav-link text-white">
                                            <i class="bi bi-gear me-1"></i> Administration
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="index.php" class="nav-link text-white">
                                            <i class="bi bi-house-door me-1"></i> Kontaktliste
                                        </a>
                                    </li>
                                    <li class="nav-item ms-lg-2">
                                        <a href="admin.php?logout=1" class="nav-link btn btn-danger text-white px-3 rounded">
                                            <i class="bi bi-box-arrow-right me-1"></i> Abmelden
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Nachrichten -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Links verwalten -->
        <div class="row">
            <div class="col-12">
                <div class="card admin-card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-link-45deg me-2"></i>
                            Links & Kontaktdaten verwalten
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="save_links">
                            
                            <div class="row">
                                <!-- Kontaktdaten -->
                                <div class="col-lg-6 mb-4">
                                    <h5 class="mb-3" style="color: var(--primary-color);">
                                        <i class="bi bi-telephone-fill me-2"></i>
                                        Kontaktdaten
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="bi bi-telephone-fill me-1"></i>
                                            Telefonnummer
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($settings['phone']) ?>"
                                               placeholder="+41 52 560 14 40">
                                        <?php if (!empty($settings['phone'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="tel:<?= htmlspecialchars($settings['phone']) ?>"><?= htmlspecialchars($settings['phone']) ?></a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope-fill me-1"></i>
                                            E-Mail-Adresse
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($settings['email']) ?>"
                                               placeholder="info@schuetz-schluesselservice.ch">
                                        <?php if (!empty($settings['email'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="mailto:<?= htmlspecialchars($settings['email']) ?>"><?= htmlspecialchars($settings['email']) ?></a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="website_url" class="form-label">
                                            <i class="bi bi-globe me-1"></i>
                                            Webseite URL
                                        </label>
                                        <input type="url" class="form-control" id="website_url" name="website_url" 
                                               value="<?= htmlspecialchars($settings['website_url']) ?>"
                                               placeholder="https://schuetz-schluesselservice.ch">
                                        <?php if (!empty($settings['website_url'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="<?= htmlspecialchars($settings['website_url']) ?>" target="_blank"><?= htmlspecialchars($settings['website_url']) ?></a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Geschäfts-Links -->
                                <div class="col-lg-6 mb-4">
                                    <h5 class="mb-3" style="color: var(--primary-color);">
                                        <i class="bi bi-building me-2"></i>
                                        Geschäfts-Links
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="google_review_url" class="form-label">
                                            <i class="bi bi-star-fill me-1"></i>
                                            Google Bewertungs-URL
                                        </label>
                                        <input type="url" class="form-control" id="google_review_url" name="google_review_url" 
                                               value="<?= htmlspecialchars($settings['google_review_url']) ?>"
                                               placeholder="https://g.page/r/...">
                                        <?php if (!empty($settings['google_review_url'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="<?= htmlspecialchars($settings['google_review_url']) ?>" target="_blank">Google Bewertung</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="maps_url" class="form-label">
                                            <i class="bi bi-geo-alt-fill me-1"></i>
                                            Google Maps URL
                                        </label>
                                        <input type="url" class="form-control" id="maps_url" name="maps_url" 
                                               value="<?= htmlspecialchars($settings['maps_url']) ?>"
                                               placeholder="https://goo.gl/maps/...">
                                        <?php if (!empty($settings['maps_url'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="<?= htmlspecialchars($settings['maps_url']) ?>" target="_blank">Google Maps</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="whatsapp_url" class="form-label">
                                            <i class="bi bi-whatsapp me-1"></i>
                                            WhatsApp URL
                                        </label>
                                        <input type="url" class="form-control" id="whatsapp_url" name="whatsapp_url" 
                                               value="<?= htmlspecialchars($settings['whatsapp_url']) ?>"
                                               placeholder="https://wa.me/41525601440">
                                        <?php if (!empty($settings['whatsapp_url'])): ?>
                                            <div class="link-preview">
                                                <small>
                                                    <i class="bi bi-eye me-1"></i>
                                                    Vorschau: <a href="<?= htmlspecialchars($settings['whatsapp_url']) ?>" target="_blank">WhatsApp</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Social Media -->
                                <div class="col-12 mb-4">
                                    <h5 class="mb-3" style="color: var(--primary-color);">
                                        <i class="bi bi-share me-2"></i>
                                        Social Media Links
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="instagram_url" class="form-label">
                                                <i class="bi bi-instagram me-1"></i>
                                                Instagram URL
                                            </label>
                                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                                   value="<?= htmlspecialchars($settings['instagram_url']) ?>"
                                                   placeholder="https://instagram.com/schuetz...">
                                            <?php if (!empty($settings['instagram_url'])): ?>
                                                <div class="link-preview">
                                                    <small>
                                                        <i class="bi bi-eye me-1"></i>
                                                        <a href="<?= htmlspecialchars($settings['instagram_url']) ?>" target="_blank">Instagram</a>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="facebook_url" class="form-label">
                                                <i class="bi bi-facebook me-1"></i>
                                                Facebook URL
                                            </label>
                                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                                   value="<?= htmlspecialchars($settings['facebook_url']) ?>"
                                                   placeholder="https://facebook.com/schuetz...">
                                            <?php if (!empty($settings['facebook_url'])): ?>
                                                <div class="link-preview">
                                                    <small>
                                                        <i class="bi bi-eye me-1"></i>
                                                        <a href="<?= htmlspecialchars($settings['facebook_url']) ?>" target="_blank">Facebook</a>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 mb-3">
                                            <label for="linkedin_url" class="form-label">
                                                <i class="bi bi-linkedin me-1"></i>
                                                LinkedIn URL
                                            </label>
                                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                                   value="<?= htmlspecialchars($settings['linkedin_url']) ?>"
                                                   placeholder="https://linkedin.com/company/schuetz...">
                                            <?php if (!empty($settings['linkedin_url'])): ?>
                                                <div class="link-preview">
                                                    <small>
                                                        <i class="bi bi-eye me-1"></i>
                                                        <a href="<?= htmlspecialchars($settings['linkedin_url']) ?>" target="_blank">LinkedIn</a>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Speichern Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Alle Einstellungen speichern
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hilfe-Bereich -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Hilfe & Informationen
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 style="color: var(--primary-color);">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    Tipps für URLs
                                </h6>
                                <ul class="small text-muted">
                                    <li>URLs sollten mit <code>https://</code> beginnen</li>
                                    <li>WhatsApp: Format <code>https://wa.me/41525601440</code></li>
                                    <li>Google Maps: Kurz-URL verwenden für bessere Darstellung</li>
                                    <li>Leere Felder werden nicht auf der Website angezeigt</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 style="color: var(--primary-color);">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Sicherheitshinweise
                                </h6>
                                <ul class="small text-muted">
                                    <li>Überprüfen Sie alle URLs vor dem Speichern</li>
                                    <li>Nur offizielle Firmen-Accounts verwenden</li>
                                    <li>Änderungen sind sofort auf der Website sichtbar</li>
                                    <li>Backup der alten Links wird automatisch erstellt</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
