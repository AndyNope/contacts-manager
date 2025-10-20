<?php
/**
 * EasyContact Multi-Tenant Router
 * Handles URL routing for company-specific pages
 */

session_start();

// Database connection
try {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

class Router {
    private $db;
    private $path;
    private $segments;
    
    public function __construct($db) {
        $this->db = $db;
        $this->path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $this->segments = array_filter(explode('/', $this->path));
    }
    
    public function route() {
        // Remove 'ams' from path if present (for development)
        if (!empty($this->segments) && $this->segments[0] === 'ams') {
            array_shift($this->segments);
        }
        
        // Homepage - EasyContact marketing site
        if (empty($this->segments) || $this->path === '' || $this->path === 'index.php') {
            $this->showHomepage();
            return;
        }
        
        // Auth routes
        if ($this->segments[0] === 'login') {
            $this->showLogin();
            return;
        }
        
        if ($this->segments[0] === 'register') {
            $this->showRegister();
            return;
        }
        
        // Legal pages
        if ($this->segments[0] === 'terms') {
            $this->showTerms();
            return;
        }
        
        if ($this->segments[0] === 'privacy') {
            $this->showPrivacy();
            return;
        }
        
        // Edit profile route
        if ($this->segments[0] === 'edit-profile') {
            include 'edit-profile.php';
            return;
        }
        
        // Company admin dashboard routes
        if ($this->segments[0] === 'dashboard') {
            $this->handleCompanyDashboard();
            return;
        }
        
        // Company admin login route
        if ($this->segments[0] === 'company-login') {
            $this->showCompanyLogin();
            return;
        }
        
        // Subscription routes
        if ($this->segments[0] === 'subscribe') {
            $this->showSubscribe();
            return;
        }
        
        // Admin routes
        if ($this->segments[0] === 'admin') {
            if (isset($this->segments[1])) {
                switch ($this->segments[1]) {
                    case 'login':
                        $this->showAdminLogin();
                        return;
                    case 'dashboard':
                        $this->showAdminDashboard();
                        return;
                }
            }
            $this->show404();
            return;
        }
        
        // API routes
        if ($this->segments[0] === 'api') {
            $this->handleApiRoute();
            return;
        }
        
        // Private profile routes
        if ($this->segments[0] === 'private') {
            if (!isset($this->segments[1])) {
                // Handle /private without UUID - redirect to user's private profile if logged in
                if (isset($_SESSION['user_id']) && isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile']) {
                    // Get user's UUID from their contact record
                    $userUUID = $this->getUserUUIDById($_SESSION['user_id']);
                    if ($userUUID) {
                        header('Location: /private/' . $userUUID, true, 302);
                    } else {
                        header('Location: /login', true, 302);
                    }
                    exit;
                } else {
                    // Not a private profile user or not logged in
                    header('Location: /login', true, 302);
                    exit;
                }
            }
            
            $identifier = $this->segments[1];
            
            // Check if identifier looks like a UUID (with hyphens) or old numeric ID or slug
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identifier)) {
                // UUID format
                $this->showPrivateProfileByUUID($identifier);
            } elseif (is_numeric($identifier)) {
                // Legacy numeric ID - redirect to UUID
                $this->redirectToUUIDProfile($identifier);
            } else {
                // Username slug
                $this->showPrivateProfileBySlug($identifier);
            }
            return;
        }
        
        // Legacy support: /private/profile/:username (redirect to new format)
        if (count($this->segments) === 3 && $this->segments[0] === 'private' && $this->segments[1] === 'profile') {
            $userSlug = $this->segments[2];
            
            // Find user ID by profile_slug to redirect to /private/{id}
            $userId = $this->getUserIdByProfileSlug($userSlug);
            if ($userId) {
                header('Location: /private/' . $userId, true, 301);
            } else {
                // If not found by profile_slug, try regular slug redirect
                header('Location: /private/' . $userSlug, true, 301);
            }
            exit;
        }
        
        // Company routes: /:company or /:company/profile/:contact
        if (count($this->segments) >= 1) {
            $companySlug = $this->segments[0];
            
            // Check if company exists
            $company = $this->getCompanyBySlug($companySlug);
            if (!$company) {
                $this->show404();
                return;
            }
            
            // Set company context
            $_SESSION['current_company'] = $company;
            
            if (count($this->segments) === 1) {
                // Show company contact list: /:company
                $this->showCompanyContacts($company);
            } elseif (count($this->segments) === 3 && $this->segments[1] === 'profile') {
                // Show contact profile: /:company/profile/:contact
                $contactSlug = $this->segments[2];
                $this->showContactProfile($company, $contactSlug);
            } else {
                $this->show404();
            }
            return;
        }
        
        $this->show404();
    }
    
    private function getCompanyBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE slug = ? AND subscription_status = 'active'");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getContactBySlug($companyId, $slug) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? AND c.slug = ? AND c.is_public = TRUE
        ");
        $stmt->execute([$companyId, $slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getPrivateProfile($userSlug) {
        // Get private company
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE slug = 'private'");
        $stmt->execute();
        $privateCompany = $stmt->fetch();
        
        if (!$privateCompany) {
            return null;
        }
        
        // Try to find contact by slug in contacts table first
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? AND c.slug = ?
        ");
        $stmt->execute([$privateCompany['id'], $userSlug]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If not found by contact slug, try by user profile_slug
        if (!$contact) {
            $stmt = $this->db->prepare("
                SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
                FROM contacts c 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.company_id = ? AND u.profile_slug = ?
            ");
            $stmt->execute([$privateCompany['id'], $userSlug]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $contact;
    }
    
    private function getUserIdByProfileSlug($profileSlug) {
        // Find user ID by profile_slug for legacy URL redirects
        $stmt = $this->db->prepare("
            SELECT u.id 
            FROM users u 
            LEFT JOIN companies c ON u.company_id = c.id 
            WHERE u.profile_slug = ? AND (u.is_private_profile = 1 OR c.slug = 'private')
        ");
        $stmt->execute([$profileSlug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }
    
    private function getPrivateProfileById($contactId) {
        // Get private company
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE slug = 'private'");
        $stmt->execute();
        $privateCompany = $stmt->fetch();
        
        if (!$privateCompany) {
            return null;
        }
        
        // Get contact by ID in private company
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? AND c.id = ? AND c.is_public = TRUE
        ");
        $stmt->execute([$privateCompany['id'], $contactId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getPrivateProfileBySlug($userSlug) {
        return $this->getPrivateProfile($userSlug);
    }
    
    private function getPrivateProfileByUUID($uuid) {
        // Get private company
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE slug = 'private'");
        $stmt->execute();
        $privateCompany = $stmt->fetch();
        
        if (!$privateCompany) {
            return null;
        }
        
        // Get contact by UUID in private company
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? AND c.uuid = ? AND c.is_public = TRUE
        ");
        $stmt->execute([$privateCompany['id'], $uuid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getUserUUIDById($userId) {
        // Get private company
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE slug = 'private'");
        $stmt->execute();
        $privateCompany = $stmt->fetch();
        
        if (!$privateCompany) {
            return null;
        }
        
        // Get UUID for user's contact record
        $stmt = $this->db->prepare("
            SELECT uuid FROM contacts 
            WHERE company_id = ? AND created_by = ? 
            LIMIT 1
        ");
        $stmt->execute([$privateCompany['id'], $userId]);
        $result = $stmt->fetch();
        return $result ? $result['uuid'] : null;
    }
    
    private function showHomepage() {
        // For direct access, use the index.php content
        // Since we moved the homepage content to index.php, we can just return
        // The index.php will be served directly by the web server
        return;
    }
    
    private function showLogin() {
        include 'login.php';
    }
    
    private function showRegister() {
        include 'register.php';
    }
    
    private function showTerms() {
        include 'terms.php';
    }
    
    private function showPrivacy() {
        include 'privacy.php';
    }
    
    private function showSubscribe() {
        include 'subscribe.php';
    }
    
    private function showCompanyContacts($company) {
        // Check access permissions
        if (!$this->canAccessCompany($company)) {
            $this->showAccessDenied($company);
            return;
        }
        
        // Get company contacts
        $stmt = $this->db->prepare("
            SELECT * FROM contacts 
            WHERE company_id = ? AND is_public = TRUE 
            ORDER BY is_featured DESC, sort_order ASC, first_name ASC
        ");
        $stmt->execute([$company['id']]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Include company-specific contact list view
        include 'views/company_contacts.php';
    }
    
    private function showContactProfile($company, $contactSlug) {
        // Check access permissions
        if (!$this->canAccessCompany($company)) {
            $this->showAccessDenied($company);
            return;
        }
        
        $contact = $this->getContactBySlug($company['id'], $contactSlug);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        // Additional contact-level permission check
        if (!$this->canViewContact($contact)) {
            $this->showAccessDenied($company, 'This contact is not publicly accessible.');
            return;
        }
        
        // Track profile view
        $this->trackAnalytics($company['id'], $contact['id'], 'profile_view');
        
        // Update profile view count
        try {
            $stmt = $this->db->prepare("UPDATE contacts SET profile_views = profile_views + 1, last_viewed_at = NOW() WHERE id = ?");
            $stmt->execute([$contact['id']]);
        } catch (Exception $e) {
            error_log('Profile view tracking error: ' . $e->getMessage());
        }
        
        // Include contact profile view
        include 'views/contact_profile.php';
    }
    
    private function showPrivateProfile($userSlug) {
        $contact = $this->getPrivateProfile($userSlug);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        $this->renderPrivateProfile($contact);
    }
    
    private function showPrivateProfileById($contactId) {
        $contact = $this->getPrivateProfileById($contactId);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        $this->renderPrivateProfile($contact);
    }
    
    private function showPrivateProfileBySlug($userSlug) {
        $contact = $this->getPrivateProfileBySlug($userSlug);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        $this->renderPrivateProfile($contact);
    }
    
    private function showPrivateProfileByUUID($uuid) {
        $contact = $this->getPrivateProfileByUUID($uuid);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        $this->renderPrivateProfile($contact);
    }
    
    private function redirectToUUIDProfile($contactId) {
        // Get UUID for the numeric contact ID and redirect
        $contact = $this->getPrivateProfileById($contactId);
        
        if (!$contact || !$contact['uuid']) {
            $this->show404();
            return;
        }
        
        header('Location: /private/' . $contact['uuid'], true, 301);
        exit;
    }
    
    private function renderPrivateProfile($contact) {
        // For private profiles, set a minimal company context
        $_SESSION['current_company'] = [
            'id' => 'private',
            'name' => 'Private Profiles',
            'slug' => 'private'
        ];
        
        // Track profile view
        $this->trackAnalytics('private', $contact['id'], 'profile_view');
        
        // Update profile view count (safely)
        try {
            $stmt = $this->db->prepare("UPDATE contacts SET profile_views = profile_views + 1, last_viewed_at = NOW() WHERE id = ?");
            $stmt->execute([$contact['id']]);
        } catch (Exception $e) {
            // Handle cases where these columns might not exist
            error_log('Profile view tracking error: ' . $e->getMessage());
        }
        
        // Include private profile view
        include 'views/private_profile_simple.php';
    }
    
    private function showAdminLogin() {
        include 'admin/login.php';
    }
    
    private function showAdminDashboard() {
        // Check admin authentication
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        include 'admin/dashboard.php';
    }
    
    private function showCompanyLogin() {
        include 'company-login.php';
    }
    
    private function handleCompanyDashboard() {
        // Check if user is logged in as company admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['company_id'])) {
            header('Location: /company-login');
            exit;
        }
        
        // Check if user has admin role for their company
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /company-login?error=insufficient_permissions');
            exit;
        }
        
        // Block private users from accessing company dashboard
        if (isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile']) {
            header('Location: /edit-profile.php');
            exit;
        }
        
        // Handle dashboard sub-routes
        if (isset($this->segments[1])) {
            switch ($this->segments[1]) {
                case 'contacts':
                    if (isset($this->segments[2])) {
                        switch ($this->segments[2]) {
                            case 'add':
                                $this->showAddContact();
                                return;
                            case 'edit':
                                if (isset($this->segments[3])) {
                                    $this->showEditContact($this->segments[3]);
                                } else {
                                    $this->show404();
                                }
                                return;
                            case 'delete':
                                if (isset($this->segments[3])) {
                                    $this->handleDeleteContact($this->segments[3]);
                                } else {
                                    $this->show404();
                                }
                                return;
                            default:
                                $this->showContactsList();
                        }
                    } else {
                        $this->showContactsList();
                    }
                    return;
                case 'settings':
                    $this->showCompanySettings();
                    return;
                default:
                    $this->showDashboardHome();
            }
        } else {
            $this->showDashboardHome();
        }
    }
    
    private function showDashboardHome() {
        include 'dashboard/index.php';
    }
    
    private function showContactsList() {
        include 'dashboard/contacts.php';
    }
    
    private function showAddContact() {
        include 'dashboard/add-contact.php';
    }
    
    private function showEditContact($contactId) {
        include 'dashboard/edit-contact.php';
    }
    
    private function handleDeleteContact($contactId) {
        include 'dashboard/delete-contact.php';
    }
    
    private function showCompanySettings() {
        include 'dashboard/settings.php';
    }
    
    private function handleApiRoute() {
        if (count($this->segments) < 2) {
            $this->show404();
            return;
        }
        
        $apiEndpoint = $this->segments[1];
        
        switch ($apiEndpoint) {
            case 'business-card':
                include 'api/generate_business_card.php';
                break;
            case 'analytics':
                include 'api/analytics.php';
                break;
            case 'vcard':
                include 'api/vcard.php';
                break;
            default:
                $this->show404();
        }
    }
    
    private function trackAnalytics($companyId, $contactId, $eventType) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO analytics_events (company_id, contact_id, event_type, user_agent, ip_address, referrer) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $companyId,
                $contactId,
                $eventType,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_REFERER'] ?? null
            ]);
        } catch (Exception $e) {
            // Log error but don't break the flow
            error_log("Analytics tracking error: " . $e->getMessage());
        }
    }
    
    private function show404() {
        http_response_code(404);
        include 'views/404.php';
    }
    
    /**
     * Check if current user can access a company
     */
    private function canAccessCompany($company) {
        // Allow access to private profiles company
        if ($company['slug'] === 'private') {
            return true;
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if user belongs to this company
        if (isset($_SESSION['company_id']) && $_SESSION['company_id'] == $company['id']) {
            return true;
        }
        
        // Check if company allows public access (new feature)
        if (isset($company['allow_public_access']) && $company['allow_public_access']) {
            return true;
        }
        
        // Default: deny access
        return false;
    }
    
    /**
     * Check if current user can view a specific contact
     */
    private function canViewContact($contact) {
        // Public contacts are viewable
        if (isset($contact['is_public']) && $contact['is_public']) {
            return true;
        }
        
        // If user is not logged in, only public contacts
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Users can view contacts from their own company
        if (isset($_SESSION['company_id']) && $_SESSION['company_id'] == $contact['company_id']) {
            return true;
        }
        
        // Users can view their own contacts
        if (isset($contact['created_by']) && $_SESSION['user_id'] == $contact['created_by']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Show access denied page
     */
    private function showAccessDenied($company = null, $message = null) {
        http_response_code(403);
        
        $defaultMessage = $message ?: 'You do not have permission to access this company\'s contacts.';
        $companyName = $company ? $company['name'] : 'this resource';
        
        include 'views/access_denied.php';
    }
}

// Initialize router
try {
    $router = new Router($pdo);
    $router->route();
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    include 'views/500.php';
}
?>
