-- SQL script to add private profile support to EasyContact database

-- Add private profile columns to users table
ALTER TABLE users 
ADD COLUMN is_private_profile TINYINT(1) DEFAULT 0 AFTER role,
ADD COLUMN profile_slug VARCHAR(255) NULL AFTER is_private_profile;

-- Add slug column to contacts table if not exists
ALTER TABLE contacts 
ADD COLUMN slug VARCHAR(255) NULL AFTER email;

-- Add profile views tracking
ALTER TABLE contacts 
ADD COLUMN profile_views INT DEFAULT 0 AFTER slug,
ADD COLUMN last_viewed_at TIMESTAMP NULL AFTER profile_views;

-- Create index for faster lookups
CREATE INDEX idx_contacts_slug ON contacts(slug);
CREATE INDEX idx_users_profile_slug ON users(profile_slug);

-- Create the private company if it doesn't exist
INSERT IGNORE INTO companies 
(slug, name, subscription_plan, subscription_status, contact_limit, created_at, updated_at) 
VALUES ('private', 'Private Profiles', 'free', 'active', -1, NOW(), NOW());

-- Update existing contacts to have slugs if they don't
UPDATE contacts 
SET slug = LOWER(CONCAT(
    REPLACE(REPLACE(REPLACE(first_name, ' ', '-'), '.', ''), '/', ''), 
    '-', 
    REPLACE(REPLACE(REPLACE(last_name, ' ', '-'), '.', ''), '/', '')
))
WHERE slug IS NULL OR slug = '';

-- Ensure unique slugs by adding numbers to duplicates
-- This is a simplified approach - in a real scenario you'd want a more robust duplicate handling
UPDATE contacts c1
JOIN (
    SELECT company_id, slug, COUNT(*) as cnt
    FROM contacts 
    WHERE slug IS NOT NULL AND slug != ''
    GROUP BY company_id, slug
    HAVING cnt > 1
) c2 ON c1.company_id = c2.company_id AND c1.slug = c2.slug
SET c1.slug = CONCAT(c1.slug, '-', c1.id)
WHERE c1.id NOT IN (
    SELECT MIN(id) 
    FROM contacts 
    WHERE company_id = c1.company_id AND slug = c1.slug
);

-- Add database schema completion notice
SELECT 'Private profile database schema setup completed!' as status;
