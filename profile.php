<?php
// Fehlerbehandlung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Für Debug-Zwecke
if (isset($_GET['debug'])) {
    echo "<h3>Debug Informationen:</h3>";
    echo "GET-Parameter: " . print_r($_GET, true) . "<br>";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'nicht gesetzt') . "<br>";
}

try {
    require_once 'config.php';

    $contactManager = new ContactManager();
    $contact = null;
    $error = '';
    $debug_info = '';

    // Debug-Modus aktivieren falls GET-Parameter gesetzt
    $debug = isset($_GET['debug']) ? true : false;

    if ($debug) {
        $debug_info .= "Debug-Modus aktiviert<br>";
        $debug_info .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'nicht gesetzt') . "<br>";
        $debug_info .= "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'nicht gesetzt') . "<br>";
    }

    // Name aus verschiedenen Quellen extrahieren
    $profileName = '';

    // 1. Versuche GET-Parameter 'name'
    if (isset($_GET['name']) && !empty($_GET['name'])) {
        $profileName = $_GET['name'];
        if ($debug) $debug_info .= "Name aus GET-Parameter: " . htmlspecialchars($profileName) . "<br>";
    }

    // 2. Falls kein GET-Parameter, versuche aus URL zu extrahieren
    if (empty($profileName) && isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $path = parse_url($uri, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Suche nach einem Segment mit Bindestrich (Profil-Name)
        foreach ($segments as $segment) {
            if (strpos($segment, '-') !== false) {
                $profileName = $segment;
                if ($debug) $debug_info .= "Name aus URL-Segment: " . htmlspecialchars($profileName) . "<br>";
                break;
            }
        }
    }

    if ($profileName) {
        // Sichere URL-Decode für Umlaute
        try {
            $decodedName = urldecode($profileName);
            if ($decodedName !== false) {
                $profileName = $decodedName;
            }
        } catch (Exception $e) {
            // Falls URL-Decode fehlschlägt, verwende Original-Name
            if ($debug) $debug_info .= "URL-Decode Fehler: " . $e->getMessage() . "<br>";
        }
        
        if ($debug) $debug_info .= "Name nach URL-Decode: " . htmlspecialchars($profileName) . "<br>";
        
        $nameParts = explode('-', $profileName);
        if (count($nameParts) >= 2) {
            $vorname = trim($nameParts[0]);
            $nachname = trim(implode('-', array_slice($nameParts, 1))); // Falls Nachname Bindestriche enthält
            
            if ($debug) {
                $debug_info .= "Vorname: " . htmlspecialchars($vorname) . "<br>";
                $debug_info .= "Nachname: " . htmlspecialchars($nachname) . "<br>";
            }
            
            // Kontakt suchen - case-insensitive und trimmed mit Umlaut-Behandlung
            $contacts = $contactManager->getAllContacts();
            if ($debug) $debug_info .= "Anzahl Kontakte: " . count($contacts) . "<br>";
            
            // Deutsche Umlaute für Vergleich vorbereiten
            $umlaut_map = array(
                'ä' => 'ae',
                'Ä' => 'ae',
                'ö' => 'oe', 
                'Ö' => 'oe',
                'ü' => 'ue',
                'Ü' => 'ue',
                'ß' => 'ss'
            );
            
            foreach ($contacts as $c) {
                // Original-Namen für Debug
                $contactVorname = trim(strtolower($c['vorname']));
                $contactNachname = trim(strtolower($c['nachname']));
                
                // ASCII-Versionen für Vergleich generieren (wie in der URL)
                $contactVornameAscii = strtr($contactVorname, $umlaut_map);
                $contactNachnameAscii = strtr($contactNachname, $umlaut_map);
                
                $searchVorname = trim(strtolower($vorname));
                $searchNachname = trim(strtolower($nachname));
                
                if ($debug) {
                    $debug_info .= "Vergleiche: '" . $contactVornameAscii . "' === '" . $searchVorname . "' && '" . $contactNachnameAscii . "' === '" . $searchNachname . "'<br>";
                    $debug_info .= "Original: '" . $contactVorname . "' → ASCII: '" . $contactVornameAscii . "'<br>";
                    $debug_info .= "Original: '" . $contactNachname . "' → ASCII: '" . $contactNachnameAscii . "'<br>";
                }
                
                // Vergleiche ASCII-Versionen (wie sie in der URL erscheinen)
                if ($contactVornameAscii === $searchVorname && $contactNachnameAscii === $searchNachname) {
                    $contact = $c;
                    if ($debug) $debug_info .= "✅ Kontakt gefunden!<br>";
                    break;
                }
            }
            
            if (!$contact && $debug) {
                $debug_info .= "❌ Kontakt nicht gefunden. Verfügbare Kontakte:<br>";
                foreach ($contacts as $c) {
                    $debug_info .= "- " . htmlspecialchars($c['vorname'] . ' ' . $c['nachname']) . " → URL: " . generateProfileUrl($c['vorname'], $c['nachname']) . "<br>";
                }
            }
        } else if ($debug) {
            $debug_info .= "❌ Name enthält keinen Bindestrich<br>";
        }
    } else if ($debug) {
        $debug_info .= "❌ Kein Profil-Name gefunden<br>";
    }

    if (!$contact) {
        $error = 'Kontakt nicht gefunden';
        if ($debug) {
            $error = 'Debug-Info:<br>' . $debug_info . '<br>Kontakt nicht gefunden';
        }
    }

} catch (Exception $e) {
    $error = "Systemfehler: " . $e->getMessage();
    if ($debug) {
        $error .= "<br><br>Debug-Info:<br>" . $debug_info;
        $error .= "<br><br>Stack Trace:<br>" . $e->getTraceAsString();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $contact ? htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) : 'Kontakt nicht gefunden' ?> - Schütz Kontakte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Schütz Schlüsselservice Design - Blaues Schema */
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
            --dark-color: #1f2937;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.3);
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 2rem;
            object-fit: cover;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.1);
            border: 2px solid rgba(30, 58, 138, 0.1);
            overflow: hidden;
            margin-top: 2rem; /* Geändert von -4rem zu 2rem */
            position: relative;
            z-index: 10;
        }
        
        .contact-info-item {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 1px solid rgba(30, 58, 138, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .contact-info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
        }
        
        .contact-info-item .icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--accent-color), #f59e0b);
            border: none;
            color: var(--dark-color);
            padding: 12px 30px;
            border-radius: 8px;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 8px;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .security-badge {
            background: var(--accent-color);
            color: var(--dark-color);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .company-logo {
            color: var(--accent-color);
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        /* QR-Code Styling */
        .qr-code-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.1);
            margin-top: 1rem;
        }
        
        .qr-code-container h6 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: bold;
        }
        
        .qr-code-image {
            max-width: 200px;
            height: auto;
            border: 1px solid rgba(30, 58, 138, 0.1);
            border-radius: 10px;
            background: white;
            padding: 10px;
        }
        
        @media (max-width: 768px) {
            .qr-code-container {
                padding: 1rem;
                margin-top: 0.5rem;
            }
            
            .qr-code-image {
                max-width: 150px;
            }
        }
        
        @media (max-width: 768px) {
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 2.5rem;
                margin: 0 auto 1.5rem;
            }
            
            .profile-header {
                padding: 1.5rem 0;
            }
            
            .contact-info-item {
                padding: 1rem;
                margin-bottom: 0.8rem;
            }
            
            .contact-info-item .icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            /* Mobile Layout: Avatar oben zentriert, dann Card mit Daten */
            .mobile-profile-layout {
                display: block !important;
            }
            
            .mobile-avatar-section {
                text-align: center;
                margin-bottom: 2rem;
                padding: 1rem;
            }
            
            .mobile-content-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(30, 58, 138, 0.1);
                margin: 0 1rem;
                padding: 1.5rem;
            }
            
            .profile-card {
                margin-top: -2rem;
                margin-left: 0;
                margin-right: 0;
                box-shadow: none;
                background: transparent;
            }
            
            /* Aktionen-Sidebar auf Mobile als Card unten */
            .mobile-actions-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(30, 58, 138, 0.1);
                margin: 1rem;
                padding: 1.5rem;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php if ($error): ?>
        <!-- Fehlerseite -->
        <div class="profile-header">
            <div class="container position-relative text-center">
                <div class="profile-avatar">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h1 class="mb-3">Kontakt nicht gefunden</h1>
                <p class="lead">Der angeforderte Kontakt konnte nicht gefunden werden.</p>
                <div class="mt-4">
                    <a href="index" class="btn btn-outline-light me-2">
                        <i class="bi bi-arrow-left me-2"></i>Zurück zur Kontaktliste
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Profil Header -->
        <div class="profile-header">
            <div class="container position-relative">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <div class="profile-avatar" 
                             <?php if (!empty($contact['foto_url'])): ?>
                                 style="background-image: url('<?= htmlspecialchars($contact['foto_url']) ?>'); background-size: cover; background-position: center;"
                             <?php endif; ?>>
                            <?php if (empty($contact['foto_url'])): ?>
                                <?= strtoupper(substr($contact['vorname'], 0, 1) . substr($contact['nachname'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="mb-2"><?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?></h1>
                        
                        <?php if (!empty($contact['position'])): ?>
                            <p class="lead mb-1"><?= htmlspecialchars($contact['position']) ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($contact['firma'])): ?>
                            <p class="text-white-50 mb-3">
                                <i class="bi bi-building company-logo"></i>
                                <?= htmlspecialchars($contact['firma']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                            <span class="security-badge">
                                <i class="bi bi-shield-check me-1"></i>
                                Schütz Kontakt
                            </span>
                            <?php if (!empty($contact['land'])): ?>
                                <span class="security-badge">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($contact['land']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <a href="/" class="btn btn-outline-light me-2">
                            <i class="bi bi-arrow-left me-2"></i>Alle Kontakte
                        </a>
                        <a href="tel:+41525601440" class="btn btn-warning">
                            <i class="bi bi-telephone-fill me-2"></i>+41 52 560 14 40
                        </a>
                    </div>
                </div>
                
                <!-- QR-Code für Profile-URL -->
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-center">
                        <div class="qr-code-container">
                            <h6>
                                <i class="bi bi-qr-code me-2"></i>
                                Profil teilen
                            </h6>
                            <?php 
                                $currentCleanUrl = generateCleanProfileUrl($contact['vorname'], $contact['nachname']);
                                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($currentCleanUrl);
                            ?>
                            <img src="<?= htmlspecialchars($qrCodeUrl) ?>" 
                                 alt="QR-Code für <?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?>" 
                                 class="qr-code-image">
                            <p class="small text-muted mt-2 mb-0">
                                QR-Code scannen um dieses Profil zu öffnen
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Content -->
        <div class="container">
            <!-- Desktop Layout -->
            <div class="profile-card d-none d-lg-block">
                <div class="row g-0">
                    <!-- Kontaktinformationen -->
                    <div class="col-lg-8">
                        <div class="p-4">
                            <h3 class="mb-4" style="color: var(--primary-color);">
                                <i class="bi bi-person-vcard me-2" style="color: var(--accent-color);"></i>
                                Kontaktinformationen
                            </h3>
                            
                            <div class="row">
                                <!-- Telefon -->
                                <?php if (!empty($contact['telefon'])): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-info-item">
                                            <div class="d-flex align-items-center">
                                                <div class="icon">
                                                    <i class="bi bi-telephone-fill"></i>
                                                </div>
                                                <div>
                                                    <strong>Telefon</strong>
                                                    <div><?= htmlspecialchars($contact['telefon']) ?></div>
                                                    <a href="tel:<?= htmlspecialchars($contact['telefon']) ?>" class="btn btn-outline-primary btn-sm mt-2">
                                                        <i class="bi bi-telephone me-1"></i>Anrufen
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- E-Mail -->
                                <?php if (!empty($contact['email'])): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-info-item">
                                            <div class="d-flex align-items-center">
                                                <div class="icon">
                                                    <i class="bi bi-envelope-fill"></i>
                                                </div>
                                                <div>
                                                    <strong>E-Mail</strong>
                                                    <div><?= htmlspecialchars($contact['email']) ?></div>
                                                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="btn btn-outline-primary btn-sm mt-2">
                                                        <i class="bi bi-envelope me-1"></i>E-Mail senden
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Adresse -->
                                <?php if (!empty($contact['adresse']) || !empty($contact['plz']) || !empty($contact['ort'])): ?>
                                    <div class="col-md-12 mb-3">
                                        <div class="contact-info-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                </div>
                                                <div>
                                                    <strong>Adresse</strong>
                                                    <div>
                                                        <?php if (!empty($contact['adresse'])): ?>
                                                            <div><?= htmlspecialchars($contact['adresse']) ?></div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($contact['plz']) || !empty($contact['ort'])): ?>
                                                            <div>
                                                                <?= htmlspecialchars(trim($contact['plz'] . ' ' . $contact['ort'])) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($contact['land'])): ?>
                                                            <div><?= htmlspecialchars($contact['land']) ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($contact['adresse'])): ?>
                                                        <a href="https://maps.google.com?q=<?= urlencode($contact['adresse'] . ', ' . $contact['plz'] . ' ' . $contact['ort'] . ', ' . $contact['land']) ?>" 
                                                           target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                                            <i class="bi bi-map me-1"></i>Auf Karte anzeigen
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Website -->
                                <?php if (!empty($contact['website'])): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="contact-info-item">
                                            <div class="d-flex align-items-center">
                                                <div class="icon">
                                                    <i class="bi bi-globe"></i>
                                                </div>
                                                <div>
                                                    <strong>Website</strong>
                                                    <div><?= htmlspecialchars($contact['website']) ?></div>
                                                    <a href="<?= htmlspecialchars($contact['website']) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Besuchen
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Notizen -->
                            <?php if (!empty($contact['notizen'])): ?>
                                <div class="mt-4">
                                    <h4 style="color: var(--primary-color);">
                                        <i class="bi bi-journal-text me-2" style="color: var(--accent-color);"></i>
                                        Notizen
                                    </h4>
                                    <div class="contact-info-item">
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($contact['notizen'])) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Aktionen -->
                    <div class="col-lg-4">
                        <div class="p-4 bg-light h-100">
                            <h3 class="mb-4" style="color: var(--primary-color);">
                                <i class="bi bi-gear-fill me-2" style="color: var(--accent-color);"></i>
                                Aktionen
                            </h3>
                            
                            <!-- vCard Download -->
                            <div class="d-grid gap-3">
                                <button onclick="downloadVCard(<?= $contact['id'] ?>)" class="btn btn-success btn-lg">
                                    <i class="bi bi-download me-2"></i>
                                    vCard herunterladen
                                </button>
                                
                                <div class="border-top pt-3">
                                    <h5 class="mb-3" style="color: var(--primary-color);">Schnellaktionen</h5>
                                    
                                    <?php if (!empty($contact['telefon'])): ?>
                                        <a href="tel:<?= htmlspecialchars($contact['telefon']) ?>" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-telephone-fill me-2"></i>Anrufen
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contact['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-envelope-fill me-2"></i>E-Mail schreiben
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contact['website'])): ?>
                                        <a href="<?= htmlspecialchars($contact['website']) ?>" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-globe me-2"></i>Website besuchen
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="border-top pt-3">
                                    <h5 class="mb-3" style="color: var(--primary-color);">
                                        <i class="bi bi-building me-2" style="color: var(--accent-color);"></i>
                                        Schütz Service
                                    </h5>
                                    <p class="small text-muted mb-3">
                                        Schlüssel- und Schreinerservice GmbH<br>
                                        Ihr Partner für Sicherheit
                                    </p>
                                    <a href="tel:+41525601440" class="btn btn-warning w-100">
                                        <i class="bi bi-telephone-fill me-2"></i>
                                        24/7 Notdienst
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Layout -->
            <div class="d-lg-none">
                <!-- Mobile Avatar Section -->
                <div class="mobile-avatar-section">
                    <div class="profile-avatar" 
                         <?php if (!empty($contact['foto_url'])): ?>
                             style="background-image: url('<?= htmlspecialchars($contact['foto_url']) ?>'); background-size: cover; background-position: center;"
                         <?php endif; ?>>
                        <?php if (empty($contact['foto_url'])): ?>
                            <?= strtoupper(substr($contact['vorname'], 0, 1) . substr($contact['nachname'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    
                    <h1 class="mb-2" style="color: var(--primary-color);"><?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?></h1>
                    
                    <?php if (!empty($contact['position'])): ?>
                        <p class="lead mb-1" style="color: var(--secondary-color);"><?= htmlspecialchars($contact['position']) ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact['firma'])): ?>
                        <p class="text-muted mb-3">
                            <i class="bi bi-building company-logo"></i>
                            <?= htmlspecialchars($contact['firma']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Kontaktdaten Card -->
                <div class="mobile-content-card">
                    <h4 class="mb-3" style="color: var(--primary-color);">
                        <i class="bi bi-person-vcard me-2" style="color: var(--accent-color);"></i>
                        Kontaktinformationen
                    </h4>
                    
                    <!-- Telefon -->
                    <?php if (!empty($contact['telefon'])): ?>
                        <div class="contact-info-item">
                            <div class="d-flex align-items-center">
                                <div class="icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <div>
                                    <strong>Telefon</strong>
                                    <div><?= htmlspecialchars($contact['telefon']) ?></div>
                                    <a href="tel:<?= htmlspecialchars($contact['telefon']) ?>" class="btn btn-outline-primary btn-sm mt-2">
                                        <i class="bi bi-telephone me-1"></i>Anrufen
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- E-Mail -->
                    <?php if (!empty($contact['email'])): ?>
                        <div class="contact-info-item">
                            <div class="d-flex align-items-center">
                                <div class="icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <div>
                                    <strong>E-Mail</strong>
                                    <div><?= htmlspecialchars($contact['email']) ?></div>
                                    <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="btn btn-outline-primary btn-sm mt-2">
                                        <i class="bi bi-envelope me-1"></i>E-Mail senden
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Adresse -->
                    <?php if (!empty($contact['adresse']) || !empty($contact['plz']) || !empty($contact['ort'])): ?>
                        <div class="contact-info-item">
                            <div class="d-flex align-items-start">
                                <div class="icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <strong>Adresse</strong>
                                    <div>
                                        <?php if (!empty($contact['adresse'])): ?>
                                            <div><?= htmlspecialchars($contact['adresse']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($contact['plz']) || !empty($contact['ort'])): ?>
                                            <div>
                                                <?= htmlspecialchars(trim($contact['plz'] . ' ' . $contact['ort'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($contact['land'])): ?>
                                            <div><?= htmlspecialchars($contact['land']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($contact['adresse'])): ?>
                                        <a href="https://maps.google.com?q=<?= urlencode($contact['adresse'] . ', ' . $contact['plz'] . ' ' . $contact['ort'] . ', ' . $contact['land']) ?>" 
                                           target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="bi bi-map me-1"></i>Auf Karte anzeigen
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Website -->
                    <?php if (!empty($contact['website'])): ?>
                        <div class="contact-info-item">
                            <div class="d-flex align-items-center">
                                <div class="icon">
                                    <i class="bi bi-globe"></i>
                                </div>
                                <div>
                                    <strong>Website</strong>
                                    <div><?= htmlspecialchars($contact['website']) ?></div>
                                    <a href="<?= htmlspecialchars($contact['website']) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>Besuchen
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Notizen -->
                    <?php if (!empty($contact['notizen'])): ?>
                        <div class="mt-3">
                            <h5 style="color: var(--primary-color);">
                                <i class="bi bi-journal-text me-2" style="color: var(--accent-color);"></i>
                                Notizen
                            </h5>
                            <div class="contact-info-item">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($contact['notizen'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Aktionen Card -->
                <div class="mobile-actions-card">
                    <h4 class="mb-3" style="color: var(--primary-color);">
                        <i class="bi bi-gear-fill me-2" style="color: var(--accent-color);"></i>
                        Aktionen
                    </h4>
                    
                    <!-- vCard Download -->
                    <div class="d-grid gap-2 mb-3">
                        <button onclick="downloadVCard(<?= $contact['id'] ?>)" class="btn btn-success btn-lg">
                            <i class="bi bi-download me-2"></i>
                            vCard herunterladen
                        </button>
                    </div>
                    
                    <!-- Schnellaktionen -->
                    <div class="border-top pt-3 mb-3">
                        <h5 class="mb-3" style="color: var(--primary-color);">Schnellaktionen</h5>
                        
                        <div class="d-grid gap-2">
                            <?php if (!empty($contact['telefon'])): ?>
                                <a href="tel:<?= htmlspecialchars($contact['telefon']) ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-telephone-fill me-2"></i>Anrufen
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($contact['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-envelope-fill me-2"></i>E-Mail schreiben
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($contact['website'])): ?>
                                <a href="<?= htmlspecialchars($contact['website']) ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-globe me-2"></i>Website besuchen
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- QR-Code Sektion -->
                    <div class="border-top pt-3 mb-3">
                        <h5 class="mb-3" style="color: var(--primary-color);">
                            <i class="bi bi-qr-code me-2" style="color: var(--accent-color);"></i>
                            Profil teilen
                        </h5>
                        <div class="text-center">
                            <?php 
                                $currentCleanUrl = generateCleanProfileUrl($contact['vorname'], $contact['nachname']);
                                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($currentCleanUrl);
                            ?>
                            <img src="<?= htmlspecialchars($qrCodeUrl) ?>" 
                                 alt="QR-Code für <?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?>" 
                                 class="qr-code-image" style="max-width: 150px;">
                            <p class="small text-muted mt-2 mb-0">
                                QR-Code scannen um dieses Profil zu öffnen
                            </p>
                        </div>
                    </div>
                    
                    <!-- Schütz Service -->
                    <div class="border-top pt-3">
                        <h5 class="mb-3" style="color: var(--primary-color);">
                            <i class="bi bi-building me-2" style="color: var(--accent-color);"></i>
                            Schütz Service
                        </h5>
                        <p class="small text-muted mb-3">
                            Schlüssel- und Schreinerservice GmbH<br>
                            Ihr Partner für Sicherheit
                        </p>
                        <a href="tel:+41525601440" class="btn btn-warning w-100">
                            <i class="bi bi-telephone-fill me-2"></i>
                            24/7 Notdienst
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Abstand -->
        <div style="height: 4rem;"></div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadVCard(contactId) {
            window.location.href = `/api/download_vcard.php?id=${contactId}`;
        }
    </script>
</body>
</html>
