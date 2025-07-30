-- EasyContact.com Multi-Tenant Database Schema
-- Version 2.0 - Commercial Edition

-- Companies table (Multi-tenant support)
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL COMMENT 'URL-friendly company identifier',
    name VARCHAR(100) NOT NULL COMMENT 'Company display name',
    logo_url VARCHAR(255) NULL COMMENT 'Company logo URL',
    domain VARCHAR(100) NULL COMMENT 'Custom domain (premium feature)',
    subscription_tier ENUM('free', 'basic', 'premium') DEFAULT 'free',
    subscription_status ENUM('active', 'canceled', 'past_due') DEFAULT 'active',
    stripe_customer_id VARCHAR(100) NULL,
    trial_ends_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_subscription (subscription_tier, subscription_status)
);

-- Users table (Multi-user support per company)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('owner', 'admin', 'editor', 'viewer') DEFAULT 'editor',
    language ENUM('en', 'de', 'fr') DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    last_login_at TIMESTAMP NULL,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_email (company_id, email),
    INDEX idx_role (role)
);

-- Enhanced contacts table with company association
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    created_by_user_id INT NOT NULL,
    
    -- Personal Information
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(100) NULL,
    department VARCHAR(100) NULL,
    
    -- Contact Information  
    phone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    
    -- Address Information
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(2) DEFAULT 'CH' COMMENT 'ISO country code',
    
    -- Media
    photo_url VARCHAR(255) NULL,
    
    -- SEO & Sharing
    slug VARCHAR(100) NOT NULL COMMENT 'URL-friendly contact identifier',
    meta_title VARCHAR(60) NULL,
    meta_description VARCHAR(160) NULL,
    
    -- Visibility & Settings
    is_public BOOLEAN DEFAULT TRUE COMMENT 'Public profile visibility',
    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Featured on company page',
    sort_order INT DEFAULT 0,
    
    -- QR Code & Analytics
    qr_code_scans INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_company_slug (company_id, slug),
    INDEX idx_company_public (company_id, is_public),
    INDEX idx_featured (is_featured, sort_order),
    INDEX idx_created_by (created_by_user_id)
);

-- Business card templates (Premium feature)
CREATE TABLE business_card_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NULL COMMENT 'NULL for system templates',
    name VARCHAR(100) NOT NULL,
    template_data JSON NOT NULL COMMENT 'Template configuration',
    is_system_template BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_active (company_id, is_active)
);

-- Analytics table for tracking
CREATE TABLE analytics_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    contact_id INT NULL,
    event_type ENUM('profile_view', 'qr_scan', 'vcard_download', 'business_card_download') NOT NULL,
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    referrer VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    
    INDEX idx_company_date (company_id, created_at),
    INDEX idx_contact_event (contact_id, event_type),
    INDEX idx_event_date (event_type, created_at)
);

-- Subscription plans (for reference)
CREATE TABLE subscription_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    contacts_limit INT DEFAULT -1 COMMENT '-1 for unlimited',
    features JSON NOT NULL,
    stripe_price_id_monthly VARCHAR(100) NULL,
    stripe_price_id_yearly VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default subscription plans
INSERT INTO subscription_plans (name, slug, price_monthly, price_yearly, contacts_limit, features) VALUES
('Free', 'free', 0.00, 0.00, 1, '["basic_profile", "qr_code"]'),
('Basic', 'basic', 9.99, 99.90, 50, '["basic_profile", "qr_code", "custom_branding", "analytics"]'),
('Premium', 'premium', 19.99, 199.90, -1, '["basic_profile", "qr_code", "custom_branding", "analytics", "api_access", "white_label", "custom_templates"]');

-- Insert default company (EasyContact)
INSERT INTO companies (slug, name, subscription_tier) VALUES 
('easycontact', 'EasyContact', 'premium');

-- Insert default admin user for EasyContact
INSERT INTO users (company_id, email, password_hash, first_name, last_name, role) VALUES 
(1, 'admin@easy-contact.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'EasyContact', 'owner');

-- Create indexes for performance
CREATE INDEX idx_contacts_company_slug ON contacts(company_id, slug);
CREATE INDEX idx_analytics_company_date ON analytics_events(company_id, created_at DESC);
CREATE INDEX idx_users_company_role ON users(company_id, role);
