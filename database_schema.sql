-- EasyContact Database Schema
-- Create all required tables for the application
-- Database User: easycontact
-- Database Password: EzC0nt@ct2025!

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    subscription_plan ENUM('free', 'basic', 'premium') NOT NULL DEFAULT 'free',
    subscription_status ENUM('active', 'pending', 'cancelled', 'suspended', 'expired') NOT NULL DEFAULT 'pending',
    contact_limit INT NOT NULL DEFAULT 1,
    paypal_subscription_id VARCHAR(255) NULL,
    subscription_activated_at TIMESTAMP NULL,
    subscription_cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'admin',
    is_private_profile TINYINT(1) DEFAULT 0,
    profile_slug VARCHAR(255) NULL,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Create contacts table
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    position VARCHAR(255) NULL,
    company VARCHAR(255) NULL,
    website VARCHAR(255) NULL,
    address TEXT NULL,
    notes TEXT NULL,
    photo VARCHAR(255) NULL,
    slug VARCHAR(255) NULL,
    profile_views INT DEFAULT 0,
    last_viewed_at TIMESTAMP NULL,
    is_public TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    linkedin VARCHAR(255) NULL,
    twitter VARCHAR(255) NULL,
    facebook VARCHAR(255) NULL,
    instagram VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create analytics_events table for tracking
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    contact_id INT NULL,
    event_type VARCHAR(50) NOT NULL,
    user_agent TEXT NULL,
    ip_address VARCHAR(45) NULL,
    referrer TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
);

-- Create payment_logs table for PayPal tracking
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    paypal_subscription_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    status VARCHAR(50) NOT NULL,
    paypal_payment_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_companies_slug ON companies(slug);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_company ON users(company_id);
CREATE INDEX idx_users_profile_slug ON users(profile_slug);
CREATE INDEX idx_contacts_company ON contacts(company_id);
CREATE INDEX idx_contacts_slug ON contacts(slug);
CREATE INDEX idx_contacts_public ON contacts(is_public);
CREATE INDEX idx_analytics_company ON analytics_events(company_id);
CREATE INDEX idx_analytics_contact ON analytics_events(contact_id);

-- Insert sample data
INSERT IGNORE INTO companies 
(slug, name, subscription_plan, subscription_status, contact_limit) 
VALUES 
('private', 'Private Profiles', 'free', 'active', -1);

-- Show success message
SELECT 'Database schema created successfully!' as status;
