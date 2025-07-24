<?php
// Admin-Einstellungen laden
$settings = [];
$settingsFile = 'admin_settings.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
}
$auftragsformular_url = $settings['auftragsformular_url'] ?? 'https://astonishing-airedale-2b7.notion.site/2247f6e6d71a801495bec9f26b908df1';
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

    <!-- Einstellungen-Link -->
    <div class="container mt-4 mb-3">
        <div class="d-flex justify-content-end">
            <a href="/admin.php" class="btn btn-outline-primary">
                <i class="bi bi-gear"></i> Einstellungen
            </a>
        </div>
    </div>

    <div class="container">
        <div class="form-container text-center py-5">
            <h3 class="mb-4" style="color: var(--primary-color);">
                <i class="bi bi-person-fill security-icon"></i>
                Auftragsanfrage
            </h3>
            <p class="lead mb-4">Das Auftragsformular wird extern bereitgestellt. Klicken Sie auf den Button, um das Formular zu öffnen:</p>
            <a href="<?= htmlspecialchars($auftragsformular_url) ?>" target="_blank" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-up-right me-2"></i>Zum Auftragsformular
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
