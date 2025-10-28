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
        $photo = trim($_POST['photo'] ?? '');
        
        // Background customization fields
        $backgroundType = $_POST['background_type'] ?? 'gradient';
        $backgroundValue = trim($_POST['background_value'] ?? '');
        $backgroundOverlay = isset($_POST['background_overlay']) ? 1 : 0;
        $backgroundOverlayOpacity = (float)($_POST['background_overlay_opacity'] ?? 0.3);
        
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
                photo = ?,
                background_type = ?,
                background_value = ?,
                background_overlay = ?,
                background_overlay_opacity = ?,
                updated_at = NOW()
            WHERE id = ? AND created_by = ?
        ");
        
        $stmt->execute([
            $firstName, $lastName, $email, $phone, $position, $company, 
            $website, $address, $notes, $linkedin, $twitter, $facebook, 
            $instagram, $photo, $backgroundType, $backgroundValue, 
            $backgroundOverlay, $backgroundOverlayOpacity, $contact['id'], $_SESSION['user_id']
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
        
        /* Professional Profile Photo Section */
        .profile-photo-section {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-title {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }
        
        .photo-upload-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .photo-preview-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .photo-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: bold;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            background-size: cover;
            background-position: center;
        }
        
        .photo-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        
        .photo-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-upload:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
        }
        
        .photo-url-section {
            width: 100%;
            max-width: 400px;
        }
        
        .upload-help {
            margin-top: 0.5rem;
            padding-left: 0.75rem;
        }
        
        .upload-progress {
            margin-top: 1rem;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .upload-progress .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .upload-message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .upload-message .text-success {
            color: #10b981 !important;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            display: inline-block;
        }
        
        .upload-message .text-danger {
            color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            display: inline-block;
        }
        
        /* Drag and Drop Styling */
        .photo-preview.drag-over {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: scale(1.05);
        }
        
        /* Background Customization Styles */
        .background-options {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .background-option-section {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .gradient-presets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .gradient-option {
            text-align: center;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .gradient-option:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .gradient-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .gradient-preview {
            width: 100%;
            height: 60px;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .preview-container {
            position: relative;
            height: 120px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .preview-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 2;
        }
        
        .preview-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        
        .preview-container.with-overlay::after {
            opacity: 1;
        }
        
        .form-control-color {
            width: 100%;
            height: 50px;
            border-radius: 8px !important;
        }
        
        .form-range {
            background: transparent;
        }
        
        .form-range::-webkit-slider-thumb {
            background: #667eea;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        
        .form-range::-moz-range-thumb {
            background: #667eea;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
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
                            
                            <!-- Profile Picture -->
                            <div class="col-12 mb-4">
                                <div class="profile-photo-section">
                                    <h6 class="section-title">
                                        <i class="bi bi-camera-fill me-2"></i>Profile Picture
                                    </h6>
                                    
                                    <div class="photo-upload-container">
                                        <div class="photo-preview-section">
                                            <div class="photo-preview" id="avatarPreview">
                                                <?php if (!empty($contact['photo'])): ?>
                                                    <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        updateImagePreview();
                                                    });
                                                    </script>
                                                <?php else: ?>
                                                    <i class="bi bi-person-fill"></i>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="photo-actions">
                                                <label for="photo_file" class="btn btn-primary btn-upload">
                                                    <i class="bi bi-upload me-2"></i>Upload Photo
                                                    <input type="file" id="photo_file" name="photo_file" 
                                                           accept="image/jpeg,image/png,image/gif,image/webp" 
                                                           onchange="uploadProfileImage(this)" hidden>
                                                </label>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        id="removeImageBtn" onclick="removeProfileImage()" 
                                                        style="display: none;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- URL field below upload section -->
                                    <div class="photo-url-section">
                                        <div class="form-floating">
                                            <input type="url" class="form-control" id="photo" name="photo" 
                                                   value="<?= htmlspecialchars($contact['photo'] ?? '') ?>"
                                                   placeholder="https://example.com/image.jpg" 
                                                   onchange="updateImagePreview()">
                                            <label for="photo">
                                                <i class="bi bi-link-45deg me-2"></i>Or paste image URL
                                            </label>
                                        </div>
                                        <div class="upload-help">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Supports JPEG, PNG, GIF, WebP • Max 5MB
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div id="uploadProgress" class="upload-progress" style="display: none;">
                                        <div class="progress-bar"></div>
                                    </div>
                                    <div id="uploadMessage" class="upload-message"></div>
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
                            
                            <!-- Background Customization Section -->
                            <div class="col-12 mb-3">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-palette me-2"></i>Profile Background Customization
                                </h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="background-options">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Background Type</label>
                                            <select class="form-select" id="background_type" name="background_type" onchange="updateBackgroundOptions()">
                                                <option value="gradient" <?= ($contact['background_type'] ?? 'gradient') === 'gradient' ? 'selected' : '' ?>>
                                                    🎨 Gradient (Default)
                                                </option>
                                                <option value="color" <?= ($contact['background_type'] ?? '') === 'color' ? 'selected' : '' ?>>
                                                    🎯 Solid Color
                                                </option>
                                                <option value="image" <?= ($contact['background_type'] ?? '') === 'image' ? 'selected' : '' ?>>
                                                    🖼️ Background Image
                                                </option>
                                                <option value="video" <?= ($contact['background_type'] ?? '') === 'video' ? 'selected' : '' ?>>
                                                    🎬 Video Background
                                                </option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="background_overlay" 
                                                       name="background_overlay" <?= ($contact['background_overlay'] ?? 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="background_overlay">
                                                    Add Dark Overlay (improves text readability)
                                                </label>
                                            </div>
                                            
                                            <div class="mt-2" id="overlay_opacity_section" style="<?= ($contact['background_overlay'] ?? 0) ? '' : 'display: none;' ?>">
                                                <label for="background_overlay_opacity" class="form-label">Overlay Opacity</label>
                                                <input type="range" class="form-range" id="background_overlay_opacity" 
                                                       name="background_overlay_opacity" min="0" max="0.8" step="0.1" 
                                                       value="<?= $contact['background_overlay_opacity'] ?? 0.3 ?>">
                                                <small class="text-muted">Current: <span id="opacity_value"><?= $contact['background_overlay_opacity'] ?? 0.3 ?></span></small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Gradient Options -->
                                    <div id="gradient_options" class="background-option-section" style="<?= ($contact['background_type'] ?? 'gradient') === 'gradient' ? '' : 'display: none;' ?>">
                                        <label class="form-label">Select Gradient</label>
                                        <div class="gradient-presets">
                                            <?php 
                                            $gradients = [
                                                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' => 'Purple Blue (Default)',
                                                'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' => 'Pink Coral',
                                                'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' => 'Sky Blue',
                                                'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' => 'Green Mint',
                                                'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' => 'Pink Yellow',
                                                'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' => 'Soft Pastel',
                                                'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)' => 'Rose Pink',
                                                'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)' => 'Purple Pink'
                                            ];
                                            foreach ($gradients as $gradient => $name): ?>
                                                <div class="gradient-option" onclick="selectGradient('<?= htmlspecialchars($gradient) ?>')">
                                                    <div class="gradient-preview" style="background: <?= $gradient ?>"></div>
                                                    <small><?= $name ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <input type="hidden" id="gradient_value" name="background_value" 
                                               value="<?= htmlspecialchars($contact['background_value'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') ?>">
                                    </div>
                                    
                                    <!-- Color Options -->
                                    <div id="color_options" class="background-option-section" style="<?= ($contact['background_type'] ?? '') === 'color' ? '' : 'display: none;' ?>">
                                        <div class="form-floating">
                                            <input type="color" class="form-control form-control-color" id="color_value" 
                                                   name="background_value" value="<?= htmlspecialchars($contact['background_value'] ?? '#667eea') ?>">
                                            <label for="color_value">Choose Background Color</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Image Options -->
                                    <div id="image_options" class="background-option-section" style="<?= ($contact['background_type'] ?? '') === 'image' ? '' : 'display: none;' ?>">
                                        <div class="form-floating">
                                            <input type="url" class="form-control" id="image_value" name="background_value" 
                                                   value="<?= htmlspecialchars($contact['background_value'] ?? '') ?>"
                                                   placeholder="https://example.com/background.jpg">
                                            <label for="image_value">Background Image URL</label>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Use high-quality images (1920x1080+) for best results. Supports JPG, PNG, WebP.
                                        </small>
                                    </div>
                                    
                                    <!-- Video Options -->
                                    <div id="video_options" class="background-option-section" style="<?= ($contact['background_type'] ?? '') === 'video' ? '' : 'display: none;' ?>">
                                        <div class="form-floating">
                                            <input type="url" class="form-control" id="video_value" name="background_value" 
                                                   value="<?= htmlspecialchars($contact['background_value'] ?? '') ?>"
                                                   placeholder="https://example.com/background.mp4">
                                            <label for="video_value">Background Video URL</label>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Use MP4 format for best compatibility. Keep file size under 10MB for fast loading.
                                        </small>
                                        <div class="alert alert-info mt-2">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            <strong>Pro Tip:</strong> Video backgrounds create stunning profiles but use more data. 
                                            Consider enabling the dark overlay for better text readability.
                                        </div>
                                    </div>
                                    
                                    <!-- Live Preview -->
                                    <div class="background-preview mt-3">
                                        <label class="form-label">Live Preview</label>
                                        <div class="preview-container" id="background_preview">
                                            <div class="preview-content">
                                                <h6><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></h6>
                                                <p><?= htmlspecialchars($contact['position'] ?? 'Your Position') ?></p>
                                            </div>
                                        </div>
                                    </div>
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
    
    <script>
    // Profile image upload functionality
    function uploadProfileImage(input) {
        const file = input.files[0];
        if (!file) return;
        
        const formData = new FormData();
        formData.append('profile_image', file);
        
        // Show progress
        const progressContainer = document.getElementById('uploadProgress');
        const progressBar = progressContainer.querySelector('.progress-bar');
        const messageDiv = document.getElementById('uploadMessage');
        
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        messageDiv.innerHTML = '';
        
        console.log('Starting upload for file:', file.name);
        
        // Upload via fetch
        fetch('/api/upload_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Upload response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Upload response data:', data);
            progressContainer.style.display = 'none';
            
            if (data.success) {
                // Set URL in the URL field
                document.getElementById('photo').value = data.url;
                
                // Update preview
                updateImagePreview();
                
                // Success message
                messageDiv.innerHTML = `<small class="text-success"><i class="bi bi-check-circle me-1"></i>${data.message}</small>`;
                
                // Reset file input
                input.value = '';
            } else {
                messageDiv.innerHTML = `<small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${data.error}</small>`;
            }
        })
        .catch(error => {
            console.error('Upload fetch error:', error);
            progressContainer.style.display = 'none';
            messageDiv.innerHTML = `<small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Upload error: ${error.message}</small>`;
        });
        
        // Fake progress (since fetch doesn't have real progress events)
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);
        
        // End progress when upload is done
        setTimeout(() => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
        }, 1000);
    }
    
    // Remove profile image
    function removeProfileImage() {
        document.getElementById('photo').value = '';
        document.getElementById('photo_file').value = '';
        document.getElementById('uploadMessage').innerHTML = '';
        updateImagePreview();
    }
    
    // Update image preview
    function updateImagePreview() {
        const photoUrl = document.getElementById('photo').value;
        const avatarPreview = document.getElementById('avatarPreview');
        const removeBtn = document.getElementById('removeImageBtn');
        
        if (photoUrl && photoUrl.trim() !== '') {
            // Check if URL is absolute or relative path
            let imageUrl = photoUrl;
            if (!photoUrl.startsWith('http://') && !photoUrl.startsWith('https://') && !photoUrl.startsWith('data:')) {
                // Relative path - use as relative path
                imageUrl = photoUrl;
            }
            
            // Show URL image
            avatarPreview.style.backgroundImage = `url(${imageUrl})`;
            avatarPreview.style.backgroundSize = 'cover';
            avatarPreview.style.backgroundPosition = 'center';
            avatarPreview.innerHTML = '';
            removeBtn.style.display = 'inline-block';
        } else {
            // Show initials
            avatarPreview.style.backgroundImage = 'none';
            avatarPreview.innerHTML = '<i class="bi bi-person"></i>';
            removeBtn.style.display = 'none';
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateImagePreview();
        
        // Update preview when URL field changes
        document.getElementById('photo').addEventListener('input', updateImagePreview);
        
        // Drag & Drop for image upload
        const fileInput = document.getElementById('photo_file');
        const imagePreview = document.getElementById('avatarPreview');
        
        // Drag Over
        imagePreview.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        // Drag Leave
        imagePreview.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });
        
        // Drop
        imagePreview.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    fileInput.files = files;
                    uploadProfileImage(fileInput);
                }
            }
        });
        
        // Initialize background customization
        updateBackgroundPreview();
        
        // Background overlay toggle
        document.getElementById('background_overlay').addEventListener('change', function() {
            const opacitySection = document.getElementById('overlay_opacity_section');
            opacitySection.style.display = this.checked ? 'block' : 'none';
            updateBackgroundPreview();
        });
        
        // Opacity slider
        document.getElementById('background_overlay_opacity').addEventListener('input', function() {
            document.getElementById('opacity_value').textContent = this.value;
            updateBackgroundPreview();
        });
        
        // Background value changes
        document.getElementById('color_value').addEventListener('change', updateBackgroundPreview);
        document.getElementById('image_value').addEventListener('input', updateBackgroundPreview);
        document.getElementById('video_value').addEventListener('input', updateBackgroundPreview);
    });
    
    // Background customization functions
    function updateBackgroundOptions() {
        const type = document.getElementById('background_type').value;
        
        // Hide all option sections
        document.querySelectorAll('.background-option-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected option section
        document.getElementById(type + '_options').style.display = 'block';
        
        updateBackgroundPreview();
    }
    
    function selectGradient(gradient) {
        document.getElementById('gradient_value').value = gradient;
        
        // Update selected state
        document.querySelectorAll('.gradient-option').forEach(option => {
            option.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        updateBackgroundPreview();
    }
    
    function updateBackgroundPreview() {
        const preview = document.getElementById('background_preview');
        const type = document.getElementById('background_type').value;
        const hasOverlay = document.getElementById('background_overlay').checked;
        const overlayOpacity = document.getElementById('background_overlay_opacity').value;
        
        let backgroundStyle = '';
        
        switch(type) {
            case 'gradient':
                const gradientValue = document.getElementById('gradient_value').value || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                backgroundStyle = `background: ${gradientValue}`;
                break;
                
            case 'color':
                const colorValue = document.getElementById('color_value').value || '#667eea';
                backgroundStyle = `background: ${colorValue}`;
                break;
                
            case 'image':
                const imageValue = document.getElementById('image_value').value;
                if (imageValue) {
                    backgroundStyle = `background: url('${imageValue}') center/cover no-repeat`;
                } else {
                    backgroundStyle = `background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)`;
                }
                break;
                
            case 'video':
                const videoValue = document.getElementById('video_value').value;
                if (videoValue) {
                    // For video preview, show a placeholder with video icon
                    backgroundStyle = `background: linear-gradient(135deg, #1a1a1a 0%, #333 100%)`;
                    preview.innerHTML = `
                        <div class="preview-content">
                            <i class="bi bi-play-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <h6>Video Background</h6>
                            <p>Video will play on live profile</p>
                        </div>
                        ${hasOverlay ? `<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,${overlayOpacity}); z-index: 1;"></div>` : ''}
                    `;
                    return;
                } else {
                    backgroundStyle = `background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)`;
                }
                break;
        }
        
        preview.style.cssText = backgroundStyle;
        
        // Add/remove overlay class
        if (hasOverlay && type !== 'video') {
            preview.classList.add('with-overlay');
            preview.style.setProperty('--overlay-opacity', overlayOpacity);
        } else {
            preview.classList.remove('with-overlay');
        }
        
        // Update overlay opacity CSS variable
        if (hasOverlay) {
            const afterElement = preview.querySelector('::after') || preview;
            preview.style.setProperty('--overlay-opacity', overlayOpacity);
        }
    }
    </script>
    
    <style>
    .preview-container.with-overlay::after {
        opacity: var(--overlay-opacity, 0.3);
    }
    </style>
</body>
</html>