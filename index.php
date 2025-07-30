<?php 
require_once 'config.php';
$contactManager = new ContactManager();
$contacts = $contactManager->getAllContacts();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digitale Kontaktverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Schütz Schlüsselservice Corporate Design - Korrigierte Farben */
        :root {
            --primary-color: #1e3a8a; /* Schütz-Blau */
            --secondary-color: #3b82f6; /* Helleres Blau */
            --accent-color: #fbbf24; /* Gelb/Gold für Akzente */
            --dark-color: #1f2937;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
        }
        
        body {
            background-color: #f9fafb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand, .header-title {
            color: var(--primary-color) !important;
            font-weight: bold;
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
        
        .btn-success {
            background: linear-gradient(135deg, var(--accent-color), #f59e0b);
            border: none;
            color: #1f2937;
            font-weight: bold;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #f59e0b, var(--accent-color));
            color: #1f2937;
        }
        
        .contact-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background: white;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.15);
            border-color: var(--secondary-color);
        }
        
        .contact-avatar {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2);
            border-radius: 50%;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0; /* Verhindert Zusammendrücken */
            min-width: 50px; /* Mindestbreite */
            min-height: 50px; /* Mindesthöhe */
            object-fit: cover; /* Für Hintergrundbilder */
        }
        
        /* Responsive Avatar-Größen */
        @media (max-width: 768px) {
            .contact-avatar {
                width: 50px !important;
                height: 50px !important;
                font-size: 1rem !important;
                border-width: 2px;
            }
        }
        
        @media (max-width: 576px) {
            .contact-avatar {
                width: 45px !important;
                height: 45px !important;
                font-size: 0.9rem !important;
                min-width: 45px;
                min-height: 45px;
            }
        }
        
        .sticky-download-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            box-shadow: 0 2px 10px rgba(30, 58, 138, 0.3);
        }
        
        .search-container {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .security-badge {
            background: var(--accent-color);
            color: var(--dark-color);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .company-info {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        /* Mobile Company Info Anpassungen */
        @media (max-width: 768px) {
            .company-info {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .company-info .row {
                text-align: center;
            }
            
            .company-info h5 {
                font-size: 1.1rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            .company-info p {
                font-size: 0.9rem !important;
                margin-bottom: 1rem !important;
            }
            
            .company-info .col-md-4 {
                text-align: center !important;
                margin-top: 0.5rem;
            }
            
            .company-info small {
                font-size: 0.8rem !important;
                display: block;
            }
            
            /* Mobile Service Buttons */
            .premium-card {
                padding: 1.5rem;
                margin: 1rem 0;
            }
            
            .service-btn {
                padding: 1rem 0.5rem;
                min-height: 100px;
                font-size: 0.85rem;
            }
            
            .service-btn i {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .service-btn .btn-title {
                font-size: 0.9rem;
            }
            
            .service-btn .btn-subtitle {
                font-size: 0.7rem;
            }
            
            .service-buttons-container {
                margin-top: 1.5rem;
                padding-top: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .contact-card {
                margin-bottom: 1rem;
            }
            .header-section {
                padding: 15px 0;
            }
            
            /* Mobile Header Anpassungen */
            .header-section h1 {
                font-size: 1.5rem !important;
                line-height: 1.3;
            }
            
            .header-section p {
                font-size: 0.9rem !important;
            }
            
            .security-badge {
                display: block;
                margin: 5px 0 0 0 !important;
                font-size: 0.7rem !important;
                padding: 3px 8px !important;
                width: fit-content;
            }
            
            /* Mobile Buttons im Header */
            .header-section .btn {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.8rem !important;
                margin-bottom: 0.5rem;
            }
            
            /* Mobile Header Layout */
            .header-section .row {
                text-align: center !important;
            }
            
            .header-section .col-md-4 {
                text-align: center !important;
                margin-top: 1rem;
            }
            
            /* Mobile Checkbox positionierung */
            .mobile-checkbox-container {
                position: absolute;
                top: 15px;
                right: 15px;
                z-index: 10;
            }
            
            /* Mobile Layout Anpassungen */
            .mobile-layout {
                position: relative;
                padding-top: 10px;
            }
            
            /* Avatar für Mobile optimiert */
            .mobile-layout .contact-avatar {
                display: block;
                margin: 0 auto 1rem auto;
            }
            
            /* Button-Anpassungen für Mobile */
            .mobile-layout .d-grid {
                gap: 0.5rem;
            }
            
            .mobile-layout .btn-group {
                margin-top: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .header-section h1 {
                font-size: 1.3rem !important;
            }
            
            .header-section .btn {
                font-size: 0.75rem !important;
                padding: 0.3rem 0.6rem !important;
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .header-section .col-md-4 .btn:last-child {
                margin-bottom: 0;
            }
            
            .security-badge {
                font-size: 0.65rem !important;
                padding: 2px 6px !important;
            }
        }
        
        /* Sicherheits-Icons und Animationen */
        .security-icon {
            color: var(--accent-color);
            margin-right: 8px;
            margin-left: 8px;
        }
        
        .contact-info a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .contact-info a:hover {
            color: var(--accent-color);
        }
        
        /* Premium Look für Schütz */
        .premium-card {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 1px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 8px 30px rgba(30, 58, 138, 0.12);
            border-radius: 20px;
            padding: 2.5rem;
            margin: 2rem 0;
            overflow: hidden;
            position: relative;
        }
        
        .premium-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
            pointer-events: none;
        }
        
        .service-buttons-container {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .service-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 1.5rem 1rem;
            border-radius: 15px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            height: 100%;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .service-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .service-btn i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            opacity: 0.9;
        }
        
        .service-btn .btn-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }
        
        .service-btn .btn-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            line-height: 1.1;
        }
        
        /* Verhindert Text-Overflow in Kontakt-Karten */
        .min-w-0 {
            min-width: 0;
        }
        
        .contact-info {
            overflow: hidden;
        }
        
        .contact-info h5, .contact-info p {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header im Schütz Corporate Design -->
        <div class="header-section">
            <div class="container-fluid position-relative">
                <div class="row align-items-center">
                    <div class="col-md-8 col-12 text-center text-md-start">
                        <h1 class="mb-0 text-white">
                            <i class="bi bi-shield-check security-icon"></i>
                            Schütz Kontaktverwaltung
                            <span class="security-badge">Für Ihre Sicherheit</span>
                        </h1>
                        <p class="mb-0 mt-2 text-white-50">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Winterthur & Bülach | 24/7 Service
                        </p>
                    </div>
                    <div class="col-md-4 text-end d-none d-md-block">
                        <a href="admin" class="btn btn-outline-light me-2">
                            <i class="bi bi-gear"></i> Administration
                        </a>
                        <a href="tel:+41525601440" class="btn btn-warning">
                            <i class="bi bi-telephone-fill"></i> +41 52 560 14 40
                        </a>
                    </div>
                    <!-- Mobile Header Buttons -->
                    <div class="col-12 d-md-none mt-3">
                        <div class="d-grid gap-2">
                            <a href="tel:+41525601440" class="btn btn-warning">
                                <i class="bi bi-telephone-fill"></i> +41 52 560 14 40
                            </a>
                            <a href="admin" class="btn btn-outline-light">
                                <i class="bi bi-gear"></i> Administration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Firmeninfo -->
        <div class="company-info">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h5 class="mb-1" style="color: var(--primary-color);">
                        <i class="bi bi-building security-icon"></i>
                        Schütz Schlüssel- und Schreinerservice GmbH
                    </h5>
                    <p class="mb-0 text-muted">
                        Ihr Spezialist für Schlüssel- und Schreinerservice in der Region Winterthur. 
                        Inhabergeführter Familienbetrieb mit 24/7 Notfalldienst.
                    </p>
                </div>
                <div class="col-md-4 col-12 text-end d-none d-md-block">
                    <small class="text-muted">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        5.0 Google Bewertung (92 Rezensionen)
                    </small>
                </div>
                <!-- Mobile Google Bewertung -->
                <div class="col-12 d-md-none mt-2 text-center">
                    <small class="text-muted">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        5.0 Google Bewertung (92 Rezensionen)
                    </small>
                </div>
            </div>
        </div>

        <!-- Schütz Service Funktionen -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="premium-card" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-3">
                                <i class="bi bi-tools security-icon"></i>
                                Unsere Services & Informationen
                            </h4>
                            <p class="mb-0 opacity-75">
                                Entdecken Sie unser vollständiges Leistungsspektrum und erfahren Sie mehr über uns
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button class="btn btn-warning btn-lg fw-bold" onclick="window.open('https://schuetz-schluesselservice.ch/kontakt', '_blank')">
                                    <i class="bi bi-clipboard-check me-2"></i>
                                    Kontakt aufnehmen
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Service Buttons Grid -->
                    <div class="service-buttons-container">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <button class="service-btn w-100" onclick="showPresentationModal()">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    <div class="btn-title">Präsentation</div>
                                    <div class="btn-subtitle">PDF ansehen</div>
                                </button>
                            </div>
                            <div class="col-md-3 col-6">
                                <button class="service-btn w-100" onclick="showGoogleReviews()">
                                    <i class="bi bi-star-fill"></i>
                                    <div class="btn-title">Bewertungen</div>
                                    <div class="btn-subtitle">5.0 ★ Google</div>
                                </button>
                            </div>
                            <div class="col-md-3 col-6">
                                <button class="service-btn w-100" onclick="openNavigation()">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <div class="btn-title">Navigation</div>
                                    <div class="btn-subtitle">Route zu uns</div>
                                </button>
                            </div>
                            <div class="col-md-3 col-6">
                                <button class="service-btn w-100" onclick="openWebsite()">
                                    <i class="bi bi-globe"></i>
                                    <div class="btn-title">Webseite</div>
                                    <div class="btn-subtitle">Mehr erfahren</div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Such- und Filter-Bereich -->
        <div class="search-container premium-card">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold" style="color: var(--primary-color);">
                        <i class="bi bi-search security-icon"></i>
                        Kontakte durchsuchen
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--primary-color); color: white; border: none;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Nach Name, Position oder Firma suchen..."
                               style="border-color: var(--primary-color);">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold" style="color: var(--primary-color);">
                        <i class="bi bi-check-square security-icon"></i>
                        Auswahl-Optionen
                    </label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="selectAll()">
                            <i class="bi bi-check-square"></i> Alle auswählen
                        </button>
                        <button class="btn btn-outline-secondary" onclick="deselectAll()">
                            <i class="bi bi-square"></i> Abwählen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download-Leiste -->
        <div class="sticky-download-bar" id="downloadBar" style="display: none;">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <span id="selectedCount" class="text-muted">0 Kontakte ausgewählt</span>
                    <div class="btn-group">
                        <button class="btn btn-success" onclick="downloadSelected()">
                            <i class="bi bi-download"></i> Ausgewählte herunterladen
                        </button>
                        <button class="btn btn-outline-secondary" onclick="deselectAll()">
                            <i class="bi bi-x"></i> Abbrechen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kontakte Grid -->
        <div class="row" id="contactsContainer">
            <?php foreach ($contacts as $contact): ?>
                <div class="col-lg-4 col-md-6 mb-4 contact-item" 
                     data-name="<?= strtolower($contact['vorname'] . ' ' . $contact['nachname']) ?>"
                     data-position="<?= strtolower($contact['position'] ?? '') ?>"
                     data-company="<?= strtolower($contact['firma'] ?? '') ?>">
                    <div class="card contact-card premium-card h-100">
                        <div class="card-body">
                            <!-- Desktop Layout -->
                            <div class="d-flex align-items-start flex-nowrap d-none d-md-flex">
                                <div class="form-check me-2 mt-1 flex-shrink-0">
                                    <input class="form-check-input contact-checkbox" type="checkbox" 
                                           value="<?= $contact['id'] ?>" onchange="updateDownloadBar()"
                                           style="border-color: var(--primary-color);">
                                </div>
                                
                                <div class="contact-avatar me-3 flex-shrink-0" style="width: 65px; height: 65px; font-size: 1.3rem; 
                                     <?php if (!empty($contact['foto_url'])): ?>
                                         background-image: url('<?= htmlspecialchars($contact['foto_url']) ?>'); 
                                         background-size: cover; 
                                         background-position: center;
                                     <?php endif; ?>">
                                    <?php if (empty($contact['foto_url'])): ?>
                                        <?= strtoupper(substr($contact['vorname'], 0, 1) . substr($contact['nachname'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-grow-1 min-w-0 contact-info">
                                    <h5 class="card-title mb-1" style="color: var(--primary-color);">
                                        <i class="bi bi-person-badge security-icon" style="font-size: 0.9rem;"></i>
                                        <a href="<?= generateCleanProfileUrl($contact['vorname'], $contact['nachname']) ?>" 
                                           class="text-decoration-none" style="color: var(--primary-color);">
                                            <?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if (!empty($contact['position'])): ?>
                                        <p class="text-muted mb-1 small">
                                            <i class="bi bi-briefcase security-icon"></i> 
                                            <?= htmlspecialchars($contact['position']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contact['firma'])): ?>
                                        <p class="text-muted mb-2 small fw-bold">
                                            <i class="bi bi-building security-icon"></i> 
                                            <?= htmlspecialchars($contact['firma']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="contact-info">
                                        <?php if (!empty($contact['telefon'])): ?>
                                            <div class="mb-1">
                                                <small>
                                                    <i class="bi bi-telephone-fill security-icon"></i>
                                                    <a href="tel:<?= $contact['telefon'] ?>" class="text-decoration-none fw-bold">
                                                        <?= htmlspecialchars($contact['telefon']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($contact['email'])): ?>
                                            <div class="mb-2">
                                                <small>
                                                    <i class="bi bi-envelope-fill security-icon"></i>
                                                    <a href="mailto:<?= $contact['email'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($contact['email']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm w-100">
                                        <a href="<?= generateCleanProfileUrl($contact['vorname'], $contact['nachname']) ?>" class="btn btn-primary">
                                            <i class="bi bi-person-circle"></i> Profil
                                        </a>
                                        <button class="btn btn-outline-primary" onclick="showContactDetails(<?= $contact['id'] ?>)">
                                            <i class="bi bi-eye-fill"></i> Details
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="downloadBusinessCard(<?= $contact['id'] ?>)" title="PDF-Visitenkarte">
                                            <i class="bi bi-credit-card"></i> PDF
                                        </button>
                                        <button class="btn btn-success" onclick="downloadContact(<?= $contact['id'] ?>)">
                                            <i class="bi bi-download"></i> vCard
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mobile Layout -->
                            <div class="d-md-none mobile-layout">
                                <!-- Checkbox oben rechts -->
                                <div class="mobile-checkbox-container">
                                    <input class="form-check-input contact-checkbox" type="checkbox" 
                                           value="<?= $contact['id'] ?>" onchange="updateDownloadBar()"
                                           style="border-color: var(--primary-color);">
                                </div>
                                
                                <!-- Avatar zentriert -->
                                <div class="contact-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 1.8rem; 
                                     <?php if (!empty($contact['foto_url'])): ?>
                                         background-image: url('<?= htmlspecialchars($contact['foto_url']) ?>'); 
                                         background-size: cover; 
                                         background-position: center;
                                     <?php endif; ?>">
                                    <?php if (empty($contact['foto_url'])): ?>
                                        <?= strtoupper(substr($contact['vorname'], 0, 1) . substr($contact['nachname'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Name und Info zentriert -->
                                <div class="text-center">
                                    <h5 class="card-title mb-2" style="color: var(--primary-color);">
                                        <a href="<?= generateCleanProfileUrl($contact['vorname'], $contact['nachname']) ?>" 
                                           class="text-decoration-none" style="color: var(--primary-color);">
                                            <?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if (!empty($contact['position'])): ?>
                                        <p class="text-muted mb-1 small">
                                            <i class="bi bi-briefcase security-icon"></i> 
                                            <?= htmlspecialchars($contact['position']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contact['firma'])): ?>
                                        <p class="text-muted mb-2 small fw-bold">
                                            <i class="bi bi-building security-icon"></i> 
                                            <?= htmlspecialchars($contact['firma']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Kontaktdaten -->
                                    <div class="contact-info mb-3">
                                        <?php if (!empty($contact['telefon'])): ?>
                                            <div class="mb-1">
                                                <small>
                                                    <i class="bi bi-telephone-fill security-icon"></i>
                                                    <a href="tel:<?= $contact['telefon'] ?>" class="text-decoration-none fw-bold">
                                                        <?= htmlspecialchars($contact['telefon']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($contact['email'])): ?>
                                            <div class="mb-2">
                                                <small>
                                                    <i class="bi bi-envelope-fill security-icon"></i>
                                                    <a href="mailto:<?= $contact['email'] ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($contact['email']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Buttons -->
                                    <div class="d-grid gap-2">
                                        <a href="<?= generateCleanProfileUrl($contact['vorname'], $contact['nachname']) ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-person-circle"></i> Profil ansehen
                                        </a>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="showContactDetails(<?= $contact['id'] ?>)">
                                                <i class="bi bi-eye-fill"></i> Details
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="downloadBusinessCard(<?= $contact['id'] ?>)" title="PDF-Visitenkarte">
                                                <i class="bi bi-credit-card"></i> PDF
                                            </button>
                                            <button class="btn btn-success" onclick="downloadContact(<?= $contact['id'] ?>)">
                                                <i class="bi bi-download"></i> vCard
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Keine Ergebnisse -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-search display-4 text-muted"></i>
            <h3 class="text-muted mt-3">Keine Kontakte gefunden</h3>
            <p class="text-muted">Versuchen Sie andere Suchbegriffe</p>
        </div>
    </div>

    <!-- Kontakt Details Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kontakt Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contactModalBody">
                    <!-- Wird dynamisch gefüllt -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="downloadModalContact">
                        <i class="bi bi-download"></i> vCard herunterladen
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Service & Social Media Links -->
    <div class="container my-4">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="https://g.page/r/CXxXxXxXxXxXxXEBM/review" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-star-fill"></i> Google Bewertung
            </a>
            <a href="https://goo.gl/maps/xxxxx" target="_blank" class="btn btn-outline-success">
                <i class="bi bi-geo-alt-fill"></i> Navigation
            </a>
            <a href="https://schuetz-schluesselservice.ch" target="_blank" class="btn btn-outline-dark">
                <i class="bi bi-globe2"></i> Webseite
            </a>
            <a href="mailto:info@schuetz-schluesselservice.ch" class="btn btn-outline-secondary">
                <i class="bi bi-envelope-fill"></i> E-Mail
            </a>
        </div>
    </div>
    <!-- PDF Präsentations Modal -->
    <div class="modal fade" id="presentationModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Schütz Schlüsselservice - Firmenpräsentation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="assets/SchuetzPDF.pdf" width="100%" height="600px" frameborder="0">
                        <p>Ihr Browser unterstützt keine PDF-Anzeige. 
                           <a href="assets/SchuetzPDF.pdf" target="_blank">PDF herunterladen</a>
                        </p>
                    </iframe>
                </div>
                <div class="modal-footer">
                    <a href="assets/SchuetzPDF.pdf" download class="btn btn-primary">
                        <i class="bi bi-download me-2"></i>PDF herunterladen
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Google Reviews Modal -->
    <div class="modal fade" id="reviewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-star-fill me-2"></i>
                        Google Bewertungen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="display-1 text-warning">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h3 style="color: var(--primary-color);">5.0 von 5 Sternen</h3>
                        <p class="text-muted">Basierend auf 92 Google Rezensionen</p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Kundenzufriedenheit steht bei uns an erster Stelle!</strong><br>
                        Über 90 zufriedene Kunden haben uns auf Google bewertet.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong>Maria S.</strong>
                                        <div class="text-warning small">★★★★★</div>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        "Sehr schneller und zuverlässiger Service. Freundliches Personal und faire Preise."
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong>Peter M.</strong>
                                        <div class="text-warning small">★★★★★</div>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        "Professionelle Arbeit, pünktlich und kompetent. Kann ich nur weiterempfehlen!"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="https://g.page/r/CXxXxXxXxXxXxXEBM/review" target="_blank" class="btn btn-primary">
                        <i class="bi bi-star me-2"></i>Bewertung abgeben
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/contacts.js"></script>
    <script>
        // Service Funktionen
        function showPresentationModal() {
            new bootstrap.Modal(document.getElementById('presentationModal')).show();
        }
        
        function showGoogleReviews() {
            new bootstrap.Modal(document.getElementById('reviewsModal')).show();
        }
        
        function openNavigation() {
            // Google Maps Navigation zu Schütz Schlüsselservice
            const address = "Schütz Schlüssel- und Schreinerservice GmbH, Winterthur";
            const encodedAddress = encodeURIComponent(address);
            window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodedAddress}`, '_blank');
        }
        
        function openWebsite() {
            window.open('https://schuetz-schluesselservice.ch', '_blank');
        }
    </script>
</body>
</html>
