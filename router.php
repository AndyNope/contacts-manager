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
        
        // Private profile routes: /private/profile/:username
        if (count($this->segments) === 3 && $this->segments[0] === 'private' && $this->segments[1] === 'profile') {
            $userSlug = $this->segments[2];
            $this->showPrivateProfile($userSlug);
            return;
        }
        
        // Private profile routes: /private/profile/:username
        if ($this->segments[0] === 'private' && 
            isset($this->segments[1]) && $this->segments[1] === 'profile' &&
            isset($this->segments[2])) {
            $this->showPrivateProfile($this->segments[2]);
            return;
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
        
        // Get contact with this slug in private company
        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? AND c.slug = ?
        ");
        $stmt->execute([$privateCompany['id'], $userSlug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $contact = $this->getContactBySlug($company['id'], $contactSlug);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        // Track profile view
        $this->trackAnalytics($company['id'], $contact['id'], 'profile_view');
        
        // Update profile view count
        $stmt = $this->db->prepare("UPDATE contacts SET profile_views = profile_views + 1, last_viewed_at = NOW() WHERE id = ?");
        $stmt->execute([$contact['id']]);
        
        // Include contact profile view
        include 'views/contact_profile.php';
    }
    
    private function showPrivateProfile($userSlug) {
        $contact = $this->getPrivateProfile($userSlug);
        
        if (!$contact) {
            $this->show404();
            return;
        }
        
        // For private profiles, set a minimal company context
        $_SESSION['current_company'] = [
            'id' => 'private',
            'name' => 'Private Profiles',
            'slug' => 'private'
        ];
        
        // Track profile view
        $this->trackAnalytics('private', $contact['id'], 'profile_view');
        
        // Update profile view count
        $stmt = $this->db->prepare("UPDATE contacts SET profile_views = profile_views + 1, last_viewed_at = NOW() WHERE id = ?");
        $stmt->execute([$contact['id']]);
        
        // Include private profile view
        include 'views/private_profile.php';
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
