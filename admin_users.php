<?php
require_once 'config.php';
requireLogin();

$message = '';
$messageType = '';

// Aktuellen User holen
$currentUserId = $_SESSION['admin_user_id'] ?? 0;
$currentUsername = $_SESSION['admin_username'] ?? '';

// Database-Objekt erstellen
$database = new Database();
$db = $database->getConnection();

// Admin-Benutzer aus Datenbank laden
function getAdminUsers() {
    global $db;
    $query = "SELECT id, username, created_at FROM admin_users ORDER BY username";
    $result = $db->query($query);
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
    return $admins;
}

// Admin hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            if (strlen($password) < 8) {
                $message = 'Das Passwort muss mindestens 8 Zeichen lang sein';
                $messageType = 'danger';
            } else {
                $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ?");
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $message = 'Benutzername existiert bereits.';
                    $messageType = 'danger';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
                    $stmt->bind_param('ss', $username, $hash);
                    
                    if ($stmt->execute()) {
                        $message = 'Neuer Admin wurde angelegt.';
                        $messageType = 'success';
                    } else {
                        $message = 'Fehler beim Anlegen des Admins: ' . $stmt->error;
                        $messageType = 'danger';
                    }
                }
            }
        } else {
            $message = 'Bitte Benutzername und Passwort angeben.';
            $messageType = 'danger';
        }
    }
    
    // Passwort ändern
    if ($action === 'changepw') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if ($old && $new) {
            if ($new !== $confirm) {
                $message = 'Die neuen Passwörter stimmen nicht überein';
                $messageType = 'danger';
            } else if (strlen($new) < 8) {
                $message = 'Das neue Passwort muss mindestens 8 Zeichen lang sein';
                $messageType = 'danger';
            } else {
                $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
                $stmt->bind_param('i', $currentUserId);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user && password_verify($old, $user['password_hash'])) {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $stmt->bind_param('si', $hash, $currentUserId);
                    
                    if ($stmt->execute()) {
                        $message = 'Passwort erfolgreich geändert.';
                        $messageType = 'success';
                    } else {
                        $message = 'Fehler beim Ändern des Passworts: ' . $stmt->error;
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Altes Passwort ist falsch.';
                    $messageType = 'danger';
                }
            }
        } else {
            $message = 'Bitte beide Passwörter angeben.';
            $messageType = 'danger';
        }
    }
    
    // Admin löschen
    if ($action === 'delete') {
        $id = intval($_POST['user_id'] ?? 0);
        if ($id && $id != $currentUserId) {
            $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                $message = 'Admin gelöscht.';
                $messageType = 'success';
            } else {
                $message = 'Fehler beim Löschen: ' . $stmt->error;
                $messageType = 'danger';
            }
        } else {
            $message = 'Sie können sich nicht selbst löschen.';
            $messageType = 'danger';
        }
    }
    
    // Admin bearbeiten
    if ($action === 'edit') {
        $id = intval($_POST['user_id'] ?? 0);
        $username = trim($_POST['edit_username'] ?? '');
        
        if (empty($username)) {
            $message = 'Benutzername darf nicht leer sein';
            $messageType = 'danger';
        } else {
            // Prüfen, ob Benutzername bereits existiert (außer bei diesem Benutzer)
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = ? AND id != ?");
            $stmt->bind_param('si', $username, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $message = 'Ein Benutzer mit diesem Benutzernamen existiert bereits';
                $messageType = 'danger';
            } else {
                $stmt = $db->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                $stmt->bind_param('si', $username, $id);
                
                if ($stmt->execute()) {
                    // Wenn der eigene Benutzer bearbeitet wurde, Session aktualisieren
                    if ($id == $currentUserId) {
                        $_SESSION['admin_username'] = $username;
                        $currentUsername = $username;
                    }
                    $message = 'Admin-Benutzer erfolgreich aktualisiert';
                    $messageType = 'success';
                } else {
                    $message = 'Fehler beim Aktualisieren des Admin-Benutzers: ' . $stmt->error;
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Alle Admins laden
$admins = getAdminUsers();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Benutzer verwalten - Schütz Schlüsselservice</title>
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
        
        .table th {
            background: rgba(30, 58, 138, 0.05);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .table tbody tr:hover {
            background-color: rgba(30, 58, 138, 0.02);
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
                                    <i class="bi bi-people-fill security-icon"></i>
                                    Admin-Benutzer verwalten
                                </h1>
                                <p class="mb-0 mt-1 text-white-50 small">
                                    <i class="bi bi-shield-lock me-2"></i>
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
        <div class="row">
            <!-- Passwort ändern -->
            <div class="col-lg-6 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-key-fill security-icon"></i>
                            Passwort ändern
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="changepw">
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Aktuelles Passwort</label>
                                <input type="password" class="form-control" id="old_password" name="old_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Neues Passwort</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <div class="form-text">Mindestens 8 Zeichen</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Neues Passwort bestätigen</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="8" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Passwort ändern
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Neuen Admin-Benutzer hinzufügen -->
            <div class="col-lg-6 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus-fill security-icon"></i>
                            Neuen Admin-Benutzer hinzufügen
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Passwort</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       minlength="8" required>
                                <div class="form-text">Mindestens 8 Zeichen</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Benutzer hinzufügen
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Admin-Benutzer verwalten -->
            <div class="col-12">
                <div class="card admin-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-people security-icon"></i>
                            Admin-Benutzer (<?= count($admins) ?>)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Benutzername</th>
                                        <th>Erstellt am</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($admin['username']) ?>
                                                <?php if ($admin['id'] == $currentUserId): ?>
                                                    <span class="badge bg-info ms-2">Aktueller Benutzer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= isset($admin['created_at']) ? htmlspecialchars(date('d.m.Y H:i', strtotime($admin['created_at']))) : '-' ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editUser(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username']) ?>')">
                                                    <i class="bi bi-pencil"></i> Bearbeiten
                                                </button>
                                                <?php if ($admin['id'] != $currentUserId): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteUser(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username']) ?>')">
                                                        <i class="bi bi-trash"></i> Löschen
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($admins)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Keine Admin-Benutzer gefunden.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lösch-Bestätigungs-Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Admin-Benutzer löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sind Sie sicher, dass Sie den Admin-Benutzer <strong id="deleteUserName"></strong> löschen möchten?</p>
                    <p class="text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Achtung: Diese Aktion kann nicht rückgängig gemacht werden!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Löschen bestätigen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bearbeiten-Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Admin-Benutzer bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editForm">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Benutzername</label>
                            <input type="text" class="form-control" id="edit_username" name="edit_username" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" form="editForm" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Speichern
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal-Funktionen für Löschen und Bearbeiten
        function deleteUser(id, username) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = username;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        function editUser(id, username) {
            document.getElementById('editUserId').value = id;
            document.getElementById('edit_username').value = username;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
