<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login-Problem beheben - Schütz Kontaktverwaltung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .alert-success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="bi bi-tools"></i> Login-Problem beheben</h4>
                        <small>Schütz Schlüssel- und Schreinerservice GmbH</small>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Problem:</strong> Login mit admin/admin123 funktioniert nicht.
                        </div>

                        <h5>🔧 Schnelle Lösung:</h5>
                        <p>Führen Sie diesen SQL-Befehl in phpMyAdmin oder Ihrer Datenbank aus:</p>
                        
                        <div class="alert alert-success">
                            <code style="font-size: 1.1em;">
                                UPDATE admin_users SET password_hash = '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO' WHERE username = 'admin';
                            </code>
                        </div>

                        <hr>

                        <h5>📋 Alternative Lösungen:</h5>
                        
                        <div class="accordion" id="solutionAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#solution1">
                                        Lösung 1: SQL-Update in phpMyAdmin
                                    </button>
                                </h2>
                                <div id="solution1" class="accordion-collapse collapse show" data-bs-parent="#solutionAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Öffnen Sie phpMyAdmin</li>
                                            <li>Wählen Sie die Datenbank <code>kontaktverwaltung</code></li>
                                            <li>Klicken Sie auf "SQL"</li>
                                            <li>Fügen Sie den obigen SQL-Befehl ein</li>
                                            <li>Klicken Sie "OK"</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#solution2">
                                        Lösung 2: Komplette Neuinstallation
                                    </button>
                                </h2>
                                <div id="solution2" class="accordion-collapse collapse" data-bs-parent="#solutionAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Führen Sie <code>database_setup.sql</code> erneut aus</li>
                                            <li>Dies erstellt die Tabellen neu mit dem korrekten Hash</li>
                                            <li>⚠️ Achtung: Alle bestehenden Daten gehen verloren!</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#solution3">
                                        Lösung 3: Neuen Admin-Benutzer erstellen
                                    </button>
                                </h2>
                                <div id="solution3" class="accordion-collapse collapse" data-bs-parent="#solutionAccordion">
                                    <div class="accordion-body">
                                        <code>
                                            INSERT INTO admin_users (username, password_hash) VALUES ('schuetz', '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO');
                                        </code>
                                        <p class="mt-2">Login dann mit: <strong>schuetz / admin123</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>🧪 Hash-Test:</h5>
                        <?php
                        $testHash = '$2y$10$7HlpVy5YQKKvl7eMFyAGOuU5M/9iBxdGHvKGsWKjlI8VTvSJy4QLO';
                        if (password_verify('admin123', $testHash)) {
                            echo '<div class="alert alert-success">✅ <strong>Hash ist korrekt!</strong> Der obige SQL-Befehl wird das Problem lösen.</div>';
                        } else {
                            echo '<div class="alert alert-danger">❌ Hash-Fehler! Bitte kontaktieren Sie den Support.</div>';
                        }
                        ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="debug_login.php" class="btn btn-info">🔍 Erweiterte Diagnose</a>
                            <a href="admin_login.php" class="btn btn-primary">🔐 Zum Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
