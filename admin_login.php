<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $db = new Database();
            $stmt = $db->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $username;
                header('Location: admin');
                exit;
            } else {
                $error = 'Ungültige Anmeldedaten. Bitte überprüfen Sie Benutzername und Passwort.';
            }
        } catch (Exception $e) {
            $error = 'Datenbankfehler: ' . $e->getMessage();
        }
    } else {
        $error = 'Bitte füllen Sie alle Felder aus';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Kontaktverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #fbbf24;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.3);
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .security-badge {
            background: var(--accent-color);
            color: #1f2937;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.25);
        }
        
        .input-group-text {
            background: var(--primary-color);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="bi bi-shield-lock display-3 mb-3"></i>
                        <h3>Schütz Admin Login</h3>
                        <p class="mb-2">Schlüssel- und Schreinerservice GmbH</p>
                        <div class="security-badge">Für Ihre Sicherheit</div>
                        <p class="mb-0 mt-2 small">
                            <i class="bi bi-geo-alt me-1"></i>
                            Winterthur & Bülach
                        </p>
                    </div>
                    
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                                
                                <?php if (strpos($error, 'Ungültige Anmeldedaten') !== false): ?>
                                    <hr>
                                    <small>
                                        <strong>Login-Problem?</strong><br>
                                        <a href="quick_fix.php" class="alert-link">
                                            <i class="bi bi-tools me-1"></i>
                                            Automatische Reparatur starten
                                        </a>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Passwort</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-shield-check me-2"></i>
                                Sicher anmelden
                            </button>
                        </form>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a href="index" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Zurück zur Kontaktliste
                            </a>
                        </div>
                        
                        <!-- Standard-Login-Hinweis entfernt -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
