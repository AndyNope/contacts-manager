<?php

class Company {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get company by slug
     */
    public function getBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT c.*, COUNT(ct.id) as contact_count 
            FROM companies c 
            LEFT JOIN contacts ct ON c.id = ct.company_id 
            WHERE c.slug = ? 
            GROUP BY c.id
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new company
     */
    public function create($data) {
        // Generate unique slug
        $slug = $this->generateSlug($data['name']);
        
        $stmt = $this->db->prepare("
            INSERT INTO companies (slug, name, subscription_tier) 
            VALUES (?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $slug,
            $data['name'],
            $data['subscription_tier'] ?? 'free'
        ]);
        
        if ($result) {
            return [
                'id' => $this->db->lastInsertId(),
                'slug' => $slug
            ];
        }
        
        return false;
    }
    
    /**
     * Update company settings
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = ['name', 'logo_url', 'domain', 'subscription_tier'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE companies 
            SET " . implode(', ', $fields) . ", updated_at = NOW() 
            WHERE id = ?
        ");
        
        return $stmt->execute($values);
    }
    
    /**
     * Check subscription limits
     */
    public function checkContactLimit($companyId) {
        $company = $this->getById($companyId);
        if (!$company) return false;
        
        $config = include __DIR__ . '/../../config/app.php';
        $limits = $config['subscription'][$company['subscription_tier']]['contacts_limit'];
        
        if ($limits === -1) return true; // Unlimited
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM contacts WHERE company_id = ?");
        $stmt->execute([$companyId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $current < $limits;
    }
    
    /**
     * Get company by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate unique slug from company name
     */
    private function generateSlug($name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug) {
        $stmt = $this->db->prepare("SELECT id FROM companies WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get company usage statistics
     */
    public function getUsageStats($companyId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(c.id) as total_contacts,
                SUM(c.profile_views) as total_profile_views,
                SUM(c.qr_code_scans) as total_qr_scans,
                COUNT(CASE WHEN c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as contacts_last_30_days
            FROM contacts c 
            WHERE c.company_id = ?
        ");
        $stmt->execute([$companyId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
