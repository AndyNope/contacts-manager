<?php
/**
 * Add New Contact
 * Form for company admins to add new team members
 */

// Strict access control - only company admins allowed
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['company_id']) || 
    !isset($_SESSION['user_role']) || 
    $_SESSION['user_role'] !== 'admin' ||
    (isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile'])) {
    
    // Redirect private users to their profile
    if (isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile']) {
        header('Location: /edit-profile.php');
        exit;
    }
    
    // Redirect others to company login
    header('Location: /company-login');
    exit;
}

// Database connection
global $pdo;
if (!$pdo) {
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
}

$message = '';
$messageType = '';
$companyId = $_SESSION['company_id'];

// Handle form submission
if ($_POST) {
    try {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $twitter = trim($_POST['twitter'] ?? '');
        $facebook = trim($_POST['facebook'] ?? '');
        $instagram = trim($_POST['instagram'] ?? '');
        $photo = trim($_POST['photo'] ?? '');
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Validation
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Check if email already exists in company
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM contacts WHERE email = ? AND company_id = ?");
            $stmt->execute([$email, $companyId]);
            if ($stmt->fetch()) {
                throw new Exception('A contact with this email already exists in your company');
            }
        }
        
        // Generate slug
        $slug = strtolower($firstName . '-' . $lastName);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure slug is unique within company
        $originalSlug = $slug;
        $counter = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM contacts WHERE slug = ? AND company_id = ?");
            $stmt->execute([$slug, $companyId]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Generate UUID
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        // Insert contact
        $stmt = $pdo->prepare("
            INSERT INTO contacts (
                company_id, first_name, last_name, email, phone, position, 
                department, website, address, notes, linkedin, twitter, 
                facebook, instagram, photo, slug, uuid, is_public, is_featured, created_by, 
                created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");
        
        $stmt->execute([
            $companyId, $firstName, $lastName, $email, $phone, $position,
            $department, $website, $address, $notes, $linkedin, $twitter,
            $facebook, $instagram, $photo, $slug, $uuid, $isPublic, $isFeatured, $_SESSION['user_id']
        ]);
        
        $contactId = $pdo->lastInsertId();
        
        $message = 'Contact added successfully!';
        $messageType = 'success';
        
        // Clear form data
        $_POST = [];
        
    } catch (Exception $e) {
        error_log('Add contact error: ' . $e->getMessage());
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Contact - <?= htmlspecialchars($_SESSION['company_name']) ?> | EasyContact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: var(--primary-color);
            color: white;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-link {
            color: #4b5563;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: #f3f4f6;
            color: var(--primary-color);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .content-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h6 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        /* Professional Profile Photo Section */
        .profile-photo-section {
            background: rgba(30, 58, 138, 0.02);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(30, 58, 138, 0.1);
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            border: none;
            padding: 0;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: bold;
            border: 4px solid rgba(30, 58, 138, 0.2);
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
            transition: all 0.3s ease;
            background-size: cover;
            background-position: center;
        }
        
        .photo-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.3);
        }
        
        .photo-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 58, 138, 0.3);
        }
        
        .btn-upload:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 58, 138, 0.4);
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
            background: rgba(30, 58, 138, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .upload-progress .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
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
            border-color: var(--primary-color);
            background: rgba(30, 58, 138, 0.1);
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-1"><?= htmlspecialchars($_SESSION['company_name']) ?></h5>
            <small class="opacity-75">Admin Dashboard</small>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard/contacts">
                        <i class="bi bi-people"></i>
                        Manage Contacts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/dashboard/contacts/add">
                        <i class="bi bi-person-plus"></i>
                        Add Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard/settings">
                        <i class="bi bi-gear"></i>
                        Company Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/<?= htmlspecialchars($_SESSION['company_slug']) ?>" target="_blank">
                        <i class="bi bi-eye"></i>
                        View Public Page
                    </a>
                </li>
            </ul>
            
            <hr class="my-3">
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/">
                        <i class="bi bi-house"></i>
                        EasyContact Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/api/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="content-card">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">Add New Contact</h4>
                        <small class="text-muted">Add a new team member to your company</small>
                    </div>
                    <a href="/dashboard/contacts" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Contacts
                    </a>
                </div>
            </div>
            
            <div class="p-4">
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6><i class="bi bi-person me-2"></i>Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                                    <label for="first_name">First Name *</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                                    <label for="last_name">Last Name *</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                                    <label for="phone">Phone Number</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Work Information -->
                    <div class="form-section">
                        <h6><i class="bi bi-briefcase me-2"></i>Work Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="position" name="position" 
                                           value="<?= htmlspecialchars($_POST['position'] ?? '') ?>">
                                    <label for="position">Job Title / Position</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="department" name="department" 
                                           value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
                                    <label for="department">Department</label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                                    <label for="website">Personal Website</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Details -->
                    <div class="form-section">
                        <h6><i class="bi bi-geo-alt me-2"></i>Contact Details</h6>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address" style="height: 100px"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" name="notes" style="height: 100px"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                    <label for="notes">Notes / Bio</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Picture -->
                    <div class="form-section">
                        <h6><i class="bi bi-person-circle me-2"></i>Profile Picture</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="photo_file" class="form-label">Upload Image</label>
                                <input type="file" class="form-control" id="photo_file" name="photo_file" 
                                       accept="image/jpeg,image/png,image/gif,image/webp" onchange="uploadProfileImage(this)">
                                <div class="form-text">Upload image (JPEG, PNG, GIF, WebP - max. 5MB)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="photo" name="photo" 
                                           value="<?= htmlspecialchars($_POST['photo'] ?? '') ?>"
                                           placeholder="https://example.com/image.jpg" onchange="updateImagePreview()">
                                    <label for="photo">Or enter image URL</label>
                                </div>
                            </div>
                        </div>
                        <div id="uploadProgress" class="progress mt-2" style="display: none; height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="uploadMessage" class="mt-2"></div>
                        
                        <!-- Preview -->
                        <div class="mt-3">
                            <label class="form-label">Preview</label>
                            <div id="imagePreview" class="d-flex align-items-center">
                                <div class="contact-avatar me-3" id="avatarPreview" style="width: 60px; height: 60px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Image preview will appear here</small>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="removeImageBtn" 
                                            onclick="removeProfileImage()" style="display: none;">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Picture -->
                    <div class="form-section">
                        <div class="profile-photo-section">
                            <h6 class="section-title">
                                <i class="bi bi-camera-fill me-2"></i>Profile Picture
                            </h6>
                            
                            <div class="photo-upload-container">
                                <div class="photo-preview-section">
                                    <div class="photo-preview" id="avatarPreview">
                                        <i class="bi bi-person-fill"></i>
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
                                           value="<?= htmlspecialchars($_POST['photo'] ?? '') ?>"
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
                    
                    <!-- Social Media -->
                    <div class="form-section">
                        <h6><i class="bi bi-share me-2"></i>Social Media</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                           value="<?= htmlspecialchars($_POST['linkedin'] ?? '') ?>">
                                    <label for="linkedin">LinkedIn URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="twitter" name="twitter" 
                                           value="<?= htmlspecialchars($_POST['twitter'] ?? '') ?>">
                                    <label for="twitter">Twitter URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="facebook" name="facebook" 
                                           value="<?= htmlspecialchars($_POST['facebook'] ?? '') ?>">
                                    <label for="facebook">Facebook URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="instagram" name="instagram" 
                                           value="<?= htmlspecialchars($_POST['instagram'] ?? '') ?>">
                                    <label for="instagram">Instagram URL</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Visibility Settings -->
                    <div class="form-section">
                        <h6><i class="bi bi-eye me-2"></i>Visibility Settings</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_public" name="is_public" 
                                           <?= isset($_POST['is_public']) ? 'checked' : 'checked' ?>>
                                    <label class="form-check-label" for="is_public">
                                        <strong>Public Profile</strong>
                                        <br><small class="text-muted">Show this contact on your company page</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <strong>Featured Contact</strong>
                                        <br><small class="text-muted">Highlight this contact (appears first)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="/dashboard/contacts" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Add Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
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
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            
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
                const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase();
                avatarPreview.innerHTML = initials || '<i class="bi bi-person"></i>';
                removeBtn.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateImagePreview();
            
            // Update preview when fields change
            document.getElementById('photo').addEventListener('input', updateImagePreview);
            document.getElementById('first_name').addEventListener('input', updateImagePreview);
            document.getElementById('last_name').addEventListener('input', updateImagePreview);
            
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
        });
    </script>
</body>
</html>