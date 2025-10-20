<?php
/**
 * Company Contacts Management
 * CRUD interface for managing company team members
 */

// Ensure user is authenticated as company admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id']) || $_SESSION['user_role'] !== 'admin') {
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

// Handle success/error messages from URL parameters
if (isset($_GET['deleted'])) {
    $message = 'Contact "' . htmlspecialchars($_GET['deleted']) . '" has been deleted successfully';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'delete_failed':
            $message = 'Failed to delete contact. Please try again.';
            $messageType = 'danger';
            break;
    }
}

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action']) && isset($_POST['selected_contacts'])) {
    $selectedContacts = $_POST['selected_contacts'];
    $bulkAction = $_POST['bulk_action'];
    
    if (!empty($selectedContacts) && in_array($bulkAction, ['delete', 'make_public', 'make_private'])) {
        try {
            $placeholders = str_repeat('?,', count($selectedContacts) - 1) . '?';
            $params = array_merge($selectedContacts, [$companyId]);
            
            switch ($bulkAction) {
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id IN ($placeholders) AND company_id = ?");
                    $stmt->execute($params);
                    $message = count($selectedContacts) . ' contacts deleted successfully';
                    $messageType = 'success';
                    break;
                    
                case 'make_public':
                    $stmt = $pdo->prepare("UPDATE contacts SET is_public = 1 WHERE id IN ($placeholders) AND company_id = ?");
                    $stmt->execute($params);
                    $message = count($selectedContacts) . ' contacts made public';
                    $messageType = 'success';
                    break;
                    
                case 'make_private':
                    $stmt = $pdo->prepare("UPDATE contacts SET is_public = 0 WHERE id IN ($placeholders) AND company_id = ?");
                    $stmt->execute($params);
                    $message = count($selectedContacts) . ' contacts made private';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            error_log('Bulk action error: ' . $e->getMessage());
            $message = 'Bulk action failed. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Get contacts with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$whereClause = "WHERE company_id = ?";
$params = [$companyId];

if (!empty($search)) {
    $whereClause .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR position LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if ($statusFilter !== 'all') {
    $whereClause .= " AND is_public = ?";
    $params[] = ($statusFilter === 'public') ? 1 : 0;
}

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contacts $whereClause");
$countStmt->execute($params);
$totalContacts = $countStmt->fetchColumn();
$totalPages = ceil($totalContacts / $limit);

// Get contacts
$stmt = $pdo->prepare("
    SELECT * FROM contacts 
    $whereClause 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$contacts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts - <?= htmlspecialchars($_SESSION['company_name']) ?> | EasyContact</title>
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
        
        .contact-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
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
    <!-- Sidebar (same as dashboard) -->
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
                    <div>
                        <h4 class="mb-1">Manage Contacts</h4>
                        <small class="text-muted"><?= $totalContacts ?> total contacts</small>
                    </div>
                    <a href="/dashboard/contacts/add" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add Contact
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
            <div class="p-3">
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Filters and Search -->
            <div class="p-3 border-bottom">
                <form method="GET" action="" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search contacts..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Contacts</option>
                            <option value="public" <?= $statusFilter === 'public' ? 'selected' : '' ?>>Public Only</option>
                            <option value="private" <?= $statusFilter === 'private' ? 'selected' : '' ?>>Private Only</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                    <?php if ($search || $statusFilter !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="/dashboard/contacts" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (empty($contacts)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-4 text-muted"></i>
                    <h5 class="mt-3 text-muted">No contacts found</h5>
                    <p class="text-muted mb-3">
                        <?= $search ? 'Try adjusting your search terms' : 'Start by adding your first team member' ?>
                    </p>
                    <a href="/dashboard/contacts/add" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add Contact
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <!-- Bulk Actions -->
                    <div class="p-3 border-bottom">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <select class="form-select" name="bulk_action">
                                    <option value="">Bulk Actions</option>
                                    <option value="make_public">Make Public</option>
                                    <option value="make_private">Make Private</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('Are you sure?')">
                                    Apply
                                </button>
                            </div>
                            <div class="col-md-7 text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleSelectAll()">
                                    Select All
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contacts Table -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="40px">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Contact</th>
                                    <th>Position</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="120px">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input contact-checkbox" 
                                               name="selected_contacts[]" value="<?= $contact['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="contact-avatar me-3">
                                                <?= strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                                                </div>
                                                <?php if ($contact['phone']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($contact['phone']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= $contact['position'] ? htmlspecialchars($contact['position']) : '<span class="text-muted">No position</span>' ?>
                                    </td>
                                    <td>
                                        <?= $contact['email'] ? htmlspecialchars($contact['email']) : '<span class="text-muted">No email</span>' ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $contact['is_public'] ? 'status-public' : 'status-private' ?>">
                                            <?= $contact['is_public'] ? 'Public' : 'Private' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($contact['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/dashboard/contacts/edit/<?= $contact['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($contact['is_public'] && $contact['slug']): ?>
                                                <a href="/<?= $_SESSION['company_slug'] ?>/profile/<?= htmlspecialchars($contact['slug']) ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-secondary" title="View Profile">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="/dashboard/contacts/delete/<?= $contact['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this contact?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="p-3 border-top">
                    <nav aria-label="Contacts pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.contact-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
        
        document.getElementById('selectAll').addEventListener('change', toggleSelectAll);
        
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
    </script>
</body>
</html>