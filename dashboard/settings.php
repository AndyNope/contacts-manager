<?php
/**
 * Company Settings
 * Settings page for company administrators
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

// Get company information
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch();

if (!$company) {
    header('Location: /dashboard');
    exit;
}

// Handle form submission
if ($_POST) {
    try {
        $companyName = trim($_POST['company_name'] ?? '');
        
        // Validation
        if (empty($companyName)) {
            throw new Exception('Company name is required');
        }
        
        // Update company information
        $stmt = $pdo->prepare("UPDATE companies SET name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$companyName, $companyId]);
        
        // Update session
        $_SESSION['company_name'] = $companyName;
        
        // Refresh company data
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch();
        
        $message = 'Company settings updated successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        error_log('Company settings error: ' . $e->getMessage());
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Get company statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contacts WHERE company_id = ?");
$stmt->execute([$companyId]);
$totalContacts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contacts WHERE company_id = ? AND is_public = 1");
$stmt->execute([$companyId]);
$publicContacts = $stmt->fetchColumn();

// Get admin users
$stmt = $pdo->prepare("
    SELECT first_name, last_name, email, created_at 
    FROM users 
    WHERE company_id = ? AND role = 'admin'
    ORDER BY created_at ASC
");
$stmt->execute([$companyId]);
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - <?= htmlspecialchars($_SESSION['company_name']) ?> | EasyContact</title>
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
            margin-bottom: 20px;
        }
        
        .content-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        
        .stat-item {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .badge-plan {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-free {
            background: #f3f4f6;
            color: #4b5563;
        }
        
        .badge-basic {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-premium {
            background: #fef3c7;
            color: #92400e;
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
                    <a class="nav-link" href="/dashboard/contacts/add">
                        <i class="bi bi-person-plus"></i>
                        Add Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/dashboard/settings">
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
        <!-- Company Information -->
        <div class="content-card">
            <div class="content-header">
                <h4 class="mb-1">Company Information</h4>
                <small class="text-muted">Update your company details</small>
            </div>
            
            <div class="p-4">
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?= htmlspecialchars($company['name']) ?>" required>
                                <label for="company_name">Company Name *</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="company_slug" 
                                       value="<?= htmlspecialchars($company['slug']) ?>" readonly>
                                <label for="company_slug">Company URL Slug</label>
                            </div>
                            <small class="text-muted">Your public page: <?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/<?= htmlspecialchars($company['slug']) ?></small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Subscription Information -->
        <div class="content-card">
            <div class="content-header">
                <h4 class="mb-1">Subscription Information</h4>
                <small class="text-muted">Your current plan and usage</small>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Current Plan</strong>
                </div>
                <div>
                    <span class="badge-plan badge-<?= strtolower($company['subscription_plan']) ?>">
                        <?= htmlspecialchars(ucfirst($company['subscription_plan'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Subscription Status</strong>
                </div>
                <div>
                    <span class="badge badge-<?= $company['subscription_status'] === 'active' ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars(ucfirst($company['subscription_status'])) ?>
                    </span>
                </div>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Contact Limit</strong>
                </div>
                <div>
                    <?= $totalContacts ?> / <?= $company['contact_limit'] === -1 ? 'Unlimited' : $company['contact_limit'] ?>
                </div>
            </div>
            
            <?php if ($company['subscription_activated_at']): ?>
            <div class="stat-item">
                <div>
                    <strong>Activated</strong>
                </div>
                <div>
                    <?= date('M j, Y', strtotime($company['subscription_activated_at'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Usage Statistics -->
        <div class="content-card">
            <div class="content-header">
                <h4 class="mb-1">Usage Statistics</h4>
                <small class="text-muted">Overview of your account activity</small>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Total Contacts</strong>
                    <br><small class="text-muted">All team members</small>
                </div>
                <div class="h4 text-primary mb-0"><?= $totalContacts ?></div>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Public Contacts</strong>
                    <br><small class="text-muted">Visible on company page</small>
                </div>
                <div class="h4 text-success mb-0"><?= $publicContacts ?></div>
            </div>
            
            <div class="stat-item">
                <div>
                    <strong>Private Contacts</strong>
                    <br><small class="text-muted">Hidden from public</small>
                </div>
                <div class="h4 text-warning mb-0"><?= $totalContacts - $publicContacts ?></div>
            </div>
        </div>
        
        <!-- Admin Users -->
        <div class="content-card">
            <div class="content-header">
                <h4 class="mb-1">Administrator Users</h4>
                <small class="text-muted">Users with admin access to this company</small>
            </div>
            
            <?php foreach ($admins as $admin): ?>
            <div class="stat-item">
                <div>
                    <strong><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></strong>
                    <br><small class="text-muted"><?= htmlspecialchars($admin['email']) ?></small>
                </div>
                <div>
                    <small class="text-muted">
                        Admin since<br>
                        <?= date('M j, Y', strtotime($admin['created_at'])) ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="content-card">
            <div class="content-header">
                <h4 class="mb-1">Quick Actions</h4>
                <small class="text-muted">Common tasks and links</small>
            </div>
            
            <div class="p-3">
                <div class="d-grid gap-2">
                    <a href="/dashboard/contacts/add" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add New Contact
                    </a>
                    <a href="/dashboard/contacts" class="btn btn-outline-primary">
                        <i class="bi bi-people me-2"></i>Manage All Contacts
                    </a>
                    <a href="/<?= htmlspecialchars($_SESSION['company_slug']) ?>" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-eye me-2"></i>View Public Company Page
                    </a>
                </div>
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