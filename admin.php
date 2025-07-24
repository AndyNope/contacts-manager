<?php
require_once 'config.php';
requireLogin();

$contactManager = new ContactManager();
$contacts = $contactManager->getAllContacts();

$message = '';
$messageType = '';

// Kontakt hinzufügen/bearbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $data = [
            'vorname' => $_POST['vorname'] ?? '',
            'nachname' => $_POST['nachname'] ?? '',
            'telefon' => $_POST['telefon'] ?? '',
            'email' => $_POST['email'] ?? '',
            'position' => $_POST['position'] ?? '',
            'firma' => $_POST['firma'] ?? '',
            'adresse' => $_POST['adresse'] ?? '',
            'plz' => $_POST['plz'] ?? '',
            'ort' => $_POST['ort'] ?? '',
            'land' => $_POST['land'] ?? 'Schweiz',
            'website' => $_POST['website'] ?? '',
            'notizen' => $_POST['notizen'] ?? '',
            'foto_url' => $_POST['foto_url'] ?? ''
        ];
        
        if ($action === 'add') {
            if ($contactManager->addContact($data)) {
                $message = 'Kontakt erfolgreich hinzugefügt';
                $messageType = 'success';
            } else {
                $message = 'Fehler beim Hinzufügen des Kontakts';
                $messageType = 'danger';
            }
        } else {
            $id = $_POST['contact_id'] ?? 0;
            if ($contactManager->updateContact($id, $data)) {
                $message = 'Kontakt erfolgreich aktualisiert';
                $messageType = 'success';
            } else {
                $message = 'Fehler beim Aktualisieren des Kontakts';
                $messageType = 'danger';
            }
        }
        
        // Kontakte neu laden
        $contacts = $contactManager->getAllContacts();
    }
    
    if ($action === 'delete') {
        $id = $_POST['contact_id'] ?? 0;
        if ($contactManager->deleteContact($id)) {
            $message = 'Kontakt erfolgreich gelöscht';
            $messageType = 'success';
        } else {
            $message = 'Fehler beim Löschen des Kontakts';
            $messageType = 'danger';
        }
        
        // Kontakte neu laden
        $contacts = $contactManager->getAllContacts();
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kontaktverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Schütz Schlüsselservice Admin Design - Blaues Schema */
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
            --dark-color: #1f2937;
            --light-gray: #f8f9fa;
            --border-color: #e5e7eb;
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
        
        .contact-form {
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border: 2px solid rgba(30, 58, 138, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.1);
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
            color: var(--dark-color);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }
        
        .table th {
            border-top: none;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: bold;
        }
        
        .table tbody tr:hover {
            background-color: rgba(30, 58, 138, 0.05);
        }
        
        .contact-avatar {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2);
            color: white;
            border-radius: 50%;
            font-weight: bold;
            flex-shrink: 0; /* Verhindert Zusammendrücken */
            min-width: 40px; /* Mindestbreite */
            min-height: 40px; /* Mindesthöhe */
            object-fit: cover; /* Für Hintergrundbilder */
        }
        
        /* Responsive Avatar-Größen */
        @media (max-width: 576px) {
            .contact-avatar {
                min-width: 35px;
                min-height: 35px;
            }
            
            /* Kleinere Avatare in der Tabelle auf mobilen Geräten */
            .table .contact-avatar {
                width: 35px !important;
                height: 35px !important;
                font-size: 0.8rem !important;
            }
            
            /* Formular-Vorschau anpassen */
            #avatarPreview {
                width: 60px !important;
                height: 60px !important;
                font-size: 1.2rem !important;
            }
        }
        
        .card {
            border: 1px solid rgba(30, 58, 138, 0.1);
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.08);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--light-gray), #ffffff);
            border-bottom: 2px solid var(--primary-color);
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .security-icon {
            color: var(--accent-color);
            margin-right: 8px;
        }
        
        .alert-success {
            background-color: rgba(59, 130, 246, 0.1);
            border-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            .admin-header {
                padding: 1rem 0;
            }
            
            /* Mobile Header Anpassungen */
            .admin-header h2 {
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
            .admin-header h2 {
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
        .admin-header .navbar-brand h2 {
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
            .admin-header .navbar-brand h2 {
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
                                <h2 class="mb-0 text-white">
                                    <i class="bi bi-shield-lock security-icon"></i>
                                    Schütz Kontaktverwaltung - Administration
                                </h2>
                                <p class="mb-0 mt-1 text-white-50 small">
                                    <i class="bi bi-building me-2"></i>
                                    Schlüssel- und Schreinerservice GmbH | Für Ihre Sicherheit
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
                                        <a href="index" class="nav-link text-white">
                                            <i class="bi bi-eye me-1"></i> Kontakte anzeigen
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="admin_users.php" class="nav-link text-white">
                                            <i class="bi bi-people me-1"></i> Admin-Benutzer
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="admin_links.php" class="nav-link text-white">
                                            <i class="bi bi-link-45deg me-1"></i> Links verwalten
                                        </a>
                                    </li>
                                    <li class="nav-item ms-lg-2">
                                        <a href="?logout=1" class="nav-link btn btn-danger text-white px-3 rounded">
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

        <!-- Kontakt hinzufügen/bearbeiten Form -->
        <div class="contact-form">
            <h4 class="mb-3" style="color: var(--primary-color);">
                <i class="bi bi-person-plus-fill security-icon"></i>
                <span id="formTitle">Neuen Kontakt hinzufügen</span>
            </h4>
            <p class="text-muted mb-4">
                <i class="bi bi-info-circle me-2"></i>
                Verwalten Sie hier die Kontakte für Schütz Schlüssel- und Schreinerservice GmbH
            </p>
            
            <form method="POST" id="contactForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="contact_id" id="contactId" value="">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vorname" class="form-label">Vorname *</label>
                        <input type="text" class="form-control" id="vorname" name="vorname" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nachname" class="form-label">Nachname *</label>
                        <input type="text" class="form-control" id="nachname" name="nachname" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="telefon" class="form-label">Telefon</label>
                        <input type="tel" class="form-control" id="telefon" name="telefon">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">E-Mail</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="text" class="form-control" id="position" name="position">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="firma" class="form-label">Firma</label>
                        <input type="text" class="form-control" id="firma" name="firma">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text" class="form-control" id="adresse" name="adresse">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="plz" class="form-label">PLZ</label>
                        <input type="text" class="form-control" id="plz" name="plz">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="ort" class="form-label">Ort</label>
                        <input type="text" class="form-control" id="ort" name="ort">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="land" class="form-label">Land</label>
                        <input type="text" class="form-control" id="land" name="land" value="Schweiz">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="foto_url" class="form-label">Profilbild</label>
                        <div class="mb-2">
                            <input type="file" class="form-control" id="foto_file" name="foto_file" 
                                   accept="image/jpeg,image/png,image/gif,image/webp" onchange="uploadProfileImage(this)">
                            <div class="form-text">Bild hochladen (JPEG, PNG, GIF, WebP - max. 5MB)</div>
                        </div>
                        <div class="mb-2">
                            <label for="foto_url" class="form-label text-muted small">Oder URL/Pfad eingeben:</label>
                            <input type="text" class="form-control" id="foto_url" name="foto_url" placeholder="https://example.com/bild.jpg oder uploads/bild.jpg">
                            <div class="form-text">URL oder relativer Pfad zu einem Bild</div>
                        </div>
                        <div id="uploadProgress" class="progress mt-2" style="display: none; height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="uploadMessage" class="mt-2"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vorschau</label>
                        <div id="imagePreview" class="d-flex align-items-center">
                            <div class="contact-avatar me-3" id="avatarPreview" style="width: 80px; height: 80px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Bild wird hier angezeigt</small>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="removeImageBtn" 
                                        onclick="removeProfileImage()" style="display: none;">
                                    <i class="bi bi-trash"></i> Entfernen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notizen" class="form-label">Notizen</label>
                    <textarea class="form-control" id="notizen" name="notizen" rows="3"></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-2"></i>
                        <span id="submitText">Kontakt hinzufügen</span>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="bi bi-x-circle"></i> Abbrechen
                    </button>
                </div>
            </form>
        </div>

        <!-- Kontakte Tabelle -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check security-icon"></i>
                    Kontakte verwalten (<?= count($contacts) ?> Kontakte)
                    <small class="text-muted ms-3">
                        <i class="bi bi-building me-1"></i>
                        Schütz Schlüssel- und Schreinerservice GmbH
                    </small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="d-none d-md-table-cell">Position</th>
                                <th class="d-none d-lg-table-cell">Firma</th>
                                <th class="d-none d-md-table-cell">Kontakt</th>
                                <th width="150">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="contact-avatar me-3" style="width: 40px; height: 40px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;
                                                 <?php if (!empty($contact['foto_url'])): ?>
                                                     background-image: url('<?= htmlspecialchars($contact['foto_url']) ?>'); 
                                                     background-size: cover; 
                                                     background-position: center;
                                                 <?php endif; ?>">
                                                <?php if (empty($contact['foto_url'])): ?>
                                                    <?= strtoupper(substr($contact['vorname'], 0, 1) . substr($contact['nachname'], 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?></div>
                                                <div class="d-md-none small text-muted">
                                                    <?= htmlspecialchars($contact['position'] ?? '') ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?= htmlspecialchars($contact['position'] ?? '') ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <?= htmlspecialchars($contact['firma'] ?? '') ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <small>
                                            <?php if ($contact['telefon']): ?>
                                                <div><?= htmlspecialchars($contact['telefon']) ?></div>
                                            <?php endif; ?>
                                            <?php if ($contact['email']): ?>
                                                <div><?= htmlspecialchars($contact['email']) ?></div>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editContact(<?= htmlspecialchars(json_encode($contact)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteContact(<?= $contact['id'] ?>, '<?= htmlspecialchars($contact['vorname'] . ' ' . $contact['nachname']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Lösch-Bestätigungs-Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kontakt löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sind Sie sicher, dass Sie den Kontakt <strong id="deleteContactName"></strong> löschen möchten?</p>
                    <p class="text-muted small">Diese Aktion kann nicht rückgängig gemacht werden.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="contact_id" id="deleteContactId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Löschen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>
