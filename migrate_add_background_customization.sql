-- Add background customization fields to contacts table
-- Run this SQL script on your hosting database to add background customization support

ALTER TABLE contacts 
ADD COLUMN background_type ENUM('gradient', 'color', 'image', 'video') DEFAULT 'gradient' AFTER instagram,
ADD COLUMN background_value TEXT NULL AFTER background_type,
ADD COLUMN background_overlay TINYINT(1) DEFAULT 0 AFTER background_value,
ADD COLUMN background_overlay_opacity DECIMAL(3,2) DEFAULT 0.3 AFTER background_overlay;

-- Update existing records to use gradient background (current default)
UPDATE contacts 
SET background_type = 'gradient', 
    background_value = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
WHERE background_type IS NULL;