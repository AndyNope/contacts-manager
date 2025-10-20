<?php
/**
 * Edit Contact
 * Form for company admins to edit existing team members
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

// Get contact ID from URL
$contactId = $this->segments[3] ?? null;
if (!$contactId || !is_numeric($contactId)) {
    header('Location: /dashboard/contacts');
    exit;
}

// Get contact record
$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND company_id = ?");
$stmt->execute([$contactId, $companyId]);
$contact = $stmt->fetch();

if (!$contact) {
    header('Location: /dashboard/contacts');
    exit;
}

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
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Validation
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Check if email already exists in company (excluding current contact)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM contacts WHERE email = ? AND company_id = ? AND id != ?");
            $stmt->execute([$email, $companyId, $contactId]);
            if ($stmt->fetch()) {
                throw new Exception('A contact with this email already exists in your company');
            }
        }
        
        // Generate slug if names changed
        $newSlug = $contact['slug'];
        if ($firstName !== $contact['first_name'] || $lastName !== $contact['last_name']) {
            $newSlug = strtolower($firstName . '-' . $lastName);
            $newSlug = preg_replace('/[^a-z0-9-]/', '-', $newSlug);
            $newSlug = preg_replace('/-+/', '-', $newSlug);
            $newSlug = trim($newSlug, '-');
            
            // Ensure slug is unique within company
            $originalSlug = $newSlug;
            $counter = 1;
            while (true) {
                $stmt = $pdo->prepare("SELECT id FROM contacts WHERE slug = ? AND company_id = ? AND id != ?");
                $stmt->execute([$newSlug, $companyId, $contactId]);
                if (!$stmt->fetch()) {
                    break;
                }
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        // Update contact
        $stmt = $pdo->prepare("
            UPDATE contacts SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, 
                department = ?, website = ?, address = ?, notes = ?, linkedin = ?, 
                twitter = ?, facebook = ?, instagram = ?, slug = ?, is_public = ?, 
                is_featured = ?, updated_at = NOW()
            WHERE id = ? AND company_id = ?
        ");
        
        $stmt->execute([
            $firstName, $lastName, $email, $phone, $position,
            $department, $website, $address, $notes, $linkedin, $twitter,
            $facebook, $instagram, $newSlug, $isPublic, $isFeatured,
            $contactId, $companyId
        ]);
        
        // Refresh contact data
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ? AND company_id = ?");
        $stmt->execute([$contactId, $companyId]);
        $contact = $stmt->fetch();
        
        $message = 'Contact updated successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        error_log('Edit contact error: ' . $e->getMessage());
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
    <title>Edit Contact - <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?> | EasyContact</title>
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
        
        .contact-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
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
                    <a class="nav-link active" href="/dashboard/contacts">
                        <i class="bi bi-people"></i>
                        Manage Contacts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard/contacts/add">
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
                    <div class="d-flex align-items-center">
                        <div class="contact-avatar me-3">
                            <?= strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="mb-1">Edit Contact</h4>
                            <small class="text-muted"><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></small>
                        </div>
                    </div>
                    <div>
                        <?php if ($contact['is_public'] && $contact['slug']): ?>
                            <a href="/<?= $_SESSION['company_slug'] ?>/profile/<?= htmlspecialchars($contact['slug']) ?>" 
                               target="_blank" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-eye me-2"></i>View Profile
                            </a>
                        <?php endif; ?>
                        <a href="/dashboard/contacts" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Contacts
                        </a>
                    </div>
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
                            <div class="col-md-6 mb-3">
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
                        </div>
                    </div>
                    
                    <!-- Work Information -->
                    <div class="form-section">
                        <h6><i class="bi bi-briefcase me-2"></i>Work Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="position" name="position" 
                                           value="<?= htmlspecialchars($contact['position'] ?? '') ?>">
                                    <label for="position">Job Title / Position</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="department" name="department" 
                                           value="<?= htmlspecialchars($contact['department'] ?? '') ?>">
                                    <label for="department">Department</label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="website" name="website" 
                                           value="<?= htmlspecialchars($contact['website'] ?? '') ?>">
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
                                    <textarea class="form-control" id="address" name="address" style="height: 100px"><?= htmlspecialchars($contact['address'] ?? '') ?></textarea>
                                    <label for="address">Address</label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-floating">
                                    <textarea class="form-control" id="notes" name="notes" style="height: 100px"><?= htmlspecialchars($contact['notes'] ?? '') ?></textarea>
                                    <label for="notes">Notes / Bio</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="form-section">
                        <h6><i class="bi bi-share me-2"></i>Social Media</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                           value="<?= htmlspecialchars($contact['linkedin'] ?? '') ?>">
                                    <label for="linkedin">LinkedIn URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="twitter" name="twitter" 
                                           value="<?= htmlspecialchars($contact['twitter'] ?? '') ?>">
                                    <label for="twitter">Twitter URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="facebook" name="facebook" 
                                           value="<?= htmlspecialchars($contact['facebook'] ?? '') ?>">
                                    <label for="facebook">Facebook URL</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-floating">
                                    <input type="url" class="form-control" id="instagram" name="instagram" 
                                           value="<?= htmlspecialchars($contact['instagram'] ?? '') ?>">
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
                                           <?= $contact['is_public'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_public">
                                        <strong>Public Profile</strong>
                                        <br><small class="text-muted">Show this contact on your company page</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                           <?= $contact['is_featured'] ? 'checked' : '' ?>>
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
                        <div>
                            <a href="/dashboard/contacts" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <a href="/dashboard/contacts/delete/<?= $contact['id'] ?>" 
                               class="btn btn-outline-danger ms-2"
                               onclick="return confirm('Are you sure you want to delete this contact? This action cannot be undone.')">
                                <i class="bi bi-trash me-2"></i>Delete Contact
                            </a>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Contact
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
    </script>
</body>
</html>