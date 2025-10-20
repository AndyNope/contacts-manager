<?php
/**
 * Edit Private Profile
 * Allow private profile users to edit their contact information
 */

session_start();

// Check if user is logged in and is a private profile user
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_private_profile']) || !$_SESSION['is_private_profile']) {
    header('Location: /login');
    exit;
}

// Database connection
$host = 'localhost';
$user = 'easycontact';
$pass = 'EzC0nt@ct2025!';
$dbname = 'easycontact';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$message = '';
$messageType = '';

// Get private company
$stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = 'private'");
$stmt->execute();
$privateCompany = $stmt->fetch();

if (!$privateCompany) {
    die('Private company not found');
}

// Get user's contact record
$stmt = $pdo->prepare("
    SELECT * FROM contacts 
    WHERE company_id = ? AND created_by = ?
");
$stmt->execute([$privateCompany['id'], $_SESSION['user_id']]);
$contact = $stmt->fetch();

if (!$contact) {
    die('Contact record not found');
}

// Handle form submission
if ($_POST) {
    try {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');
        $facebook = trim($_POST['facebook'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        
        // Validation
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Update contact record
        $stmt = $pdo->prepare("
            UPDATE contacts SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                position = ?, 
                company = ?, 
                website = ?, 
                address = ?, 
                notes = ?, 
                linkedin = ?, 
                twitter = ?, 
                facebook = ?, 
                instagram = ?,
                updated_at = NOW()
            WHERE id = ? AND created_by = ?
        ");
        
        $stmt->execute([
            $firstName, $lastName, $email, $phone, $position, $company, 
            $website, $address, $notes, $linkedin, $twitter, $facebook, 
            $instagram, $contact['id'], $_SESSION['user_id']
        ]);
        
        // Also update user table email if it changed
        if ($email && $email !== $_SESSION['user_email']) {
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            $_SESSION['user_email'] = $email;
        }
        
        // Update session name if it changed
        $fullName = trim($firstName . ' ' . $lastName);
        if ($fullName !== $_SESSION['user_name']) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $fullName;
        }
        
        // Refresh contact data
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
        $stmt->execute([$contact['id']]);
        $contact = $stmt->fetch();
        
        $message = 'Profile updated successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EasyContact</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        
        .edit-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .form-floating > .form-control {
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
        }
        
        .btn-outline-secondary {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-white mb-2">Edit Your Profile</h1>
                    <p class="text-white-50">Update your contact information</p>
                </div>
                
                <!-- Edit Form -->
                <div class="edit-card p-4 p-md-5">
                    <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= htmlspecialchars($contact['first_name']) ?>" required>
                                    <label for="first_name">First Name *</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= htmlspecialchars($contact['last_name']) ?>" required>
                                    <label for="last_name">Last Name *</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($contact['email'] ?? '') ?>">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($contact['phone'] ?? '') ?>">
                                    <label for="phone">Phone Number</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="position" name="position" 
                                           value="<?= htmlspecialchars($contact['position'] ?? '') ?>">
                                    <label for="position">Job Title / Position</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="company" name="company" 
                                           value="<?= htmlspecialchars($contact['company'] ?? '') ?>">
                                    <label for="company">Company</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="<?= htmlspecialchars($contact['website'] ?? '') ?>">
                                    <label for="website">Website</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address" style="height: 80px"><?= htmlspecialchars($contact['address'] ?? '') ?></textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" name="notes" style="height: 100px"><?= htmlspecialchars($contact['notes'] ?? '') ?></textarea>
                                    <label for="notes">Bio / Notes</label>
                                </div>
                            </div>
                            
                            <!-- Social Media -->
                            <div class="col-12 mb-3">
                                <h6 class="text-muted mb-3">Social Media Links</h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                           value="<?= htmlspecialchars($contact['linkedin'] ?? '') ?>">
                                    <label for="linkedin"><i class="bi bi-linkedin me-2"></i>LinkedIn</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="twitter" name="twitter" 
                                           value="<?= htmlspecialchars($contact['twitter'] ?? '') ?>">
                                    <label for="twitter"><i class="bi bi-twitter me-2"></i>Twitter</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="facebook" name="facebook" 
                                           value="<?= htmlspecialchars($contact['facebook'] ?? '') ?>">
                                    <label for="facebook"><i class="bi bi-facebook me-2"></i>Facebook</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="instagram" name="instagram" 
                                           value="<?= htmlspecialchars($contact['instagram'] ?? '') ?>">
                                    <label for="instagram"><i class="bi bi-instagram me-2"></i>Instagram</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                            
                            <a href="/private/<?= $contact['id'] ?>" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-eye me-2"></i>View Profile
                            </a>
                            
                            <a href="/" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-house me-2"></i>Home
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>