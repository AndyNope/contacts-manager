<?php
/**
 * Company Dashboard Home
 * Main dashboard for company administrators
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

// Database connection is available from router context
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

// Get company statistics
$companyId = $_SESSION['company_id'];

// Total contacts
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contacts WHERE company_id = ?");
$stmt->execute([$companyId]);
$totalContacts = $stmt->fetchColumn();

// Public contacts
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contacts WHERE company_id = ? AND is_public = 1");
$stmt->execute([$companyId]);
$publicContacts = $stmt->fetchColumn();

// Total profile views (if column exists)
$profileViews = 0;
try {
    $stmt = $pdo->prepare("SELECT SUM(profile_views) as total FROM contacts WHERE company_id = ?");
    $stmt->execute([$companyId]);
    $profileViews = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Column might not exist
}

// Recent contacts
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, position, email, is_public, created_at 
    FROM contacts 
    WHERE company_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$companyId]);
$recentContacts = $stmt->fetchAll();

// Company info
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($_SESSION['company_name']) ?> | EasyContact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
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
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: #4b5563;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 0;
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
        
        .top-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .recent-contacts {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .contact-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
        
        .contact-info h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .contact-info small {
            color: #6b7280;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
        }
        
        .status-public {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-private {
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
                    <a class="nav-link active" href="/dashboard">
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
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4 class="mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h4>
                <small class="text-muted">Here's an overview of your company's contacts</small>
            </div>
            <div>
                <button class="btn btn-primary d-md-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $totalContacts ?></div>
                    <div class="stat-label">Total Contacts</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $publicContacts ?></div>
                    <div class="stat-label">Public Contacts</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?= number_format($profileViews) ?></div>
                    <div class="stat-label">Total Profile Views</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number"><?= htmlspecialchars($company['subscription_plan']) ?></div>
                    <div class="stat-label">Subscription Plan</div>
                </div>
            </div>
        </div>
        
        <!-- Recent Contacts -->
        <div class="row">
            <div class="col-lg-8">
                <div class="recent-contacts">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h5 class="mb-0">Recent Contacts</h5>
                        <a href="/dashboard/contacts" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    
                    <?php if (empty($recentContacts)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No contacts yet</h6>
                            <p class="text-muted mb-3">Start by adding your first team member</p>
                            <a href="/dashboard/contacts/add" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Add Contact
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentContacts as $contact): ?>
                            <div class="contact-item">
                                <div class="contact-info">
                                    <h6><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></h6>
                                    <small>
                                        <?= $contact['position'] ? htmlspecialchars($contact['position']) : 'No position' ?>
                                        <?php if ($contact['email']): ?>
                                            • <?= htmlspecialchars($contact['email']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-badge <?= $contact['is_public'] ? 'status-public' : 'status-private' ?>">
                                        <?= $contact['is_public'] ? 'Public' : 'Private' ?>
                                    </span>
                                    <a href="/dashboard/contacts/edit/<?= $contact['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="recent-contacts">
                    <div class="p-3 border-bottom">
                        <h5 class="mb-0">Quick Actions</h5>
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
                                <i class="bi bi-eye me-2"></i>View Public Page
                            </a>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="text-center">
                            <small class="text-muted">Company Slug</small>
                            <br>
                            <code><?= htmlspecialchars($_SESSION['company_slug']) ?></code>
                        </div>
                    </div>
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
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target) && 
                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    </script>
</body>
</html>