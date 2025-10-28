# 🚀 EasyContact Hosting Setup Guide

## 📋 Database Configuration

**IMPORTANT:** Before uploading to your hosting, you need to update the database configuration:

### Step 1: Update Database Credentials

Edit `/config/database.php` and replace these values with your hosting database details:

```php
// === HOSTING DATABASE CONFIGURATION ===
$host = 'localhost'; // Your hosting database host (usually 'localhost' or provided by hosting)
$dbname = 'your_actual_database_name'; // Replace with your hosting database name
$user = 'your_actual_db_username'; // Replace with your hosting database username  
$pass = 'your_actual_db_password'; // Replace with your hosting database password
```

### Step 2: Database Setup

1. **Import the database schema** from `database_schema.sql` to your hosting database
2. **Create the database tables** using your hosting control panel or phpMyAdmin
3. **Verify the connection** by accessing `/api/business-card-pdf.php?contact=1&format=preview`

## 🔧 Common Hosting Database Settings

### cPanel/Shared Hosting:
- **Host:** Usually `localhost`
- **Database Name:** Usually prefixed like `username_dbname`
- **Username:** Usually prefixed like `username_dbuser`
- **Password:** Set during database user creation

### VPS/Dedicated Server:
- **Host:** `localhost` or specific IP
- **Database Name:** Your chosen name
- **Username:** Your created MySQL user
- **Password:** Your set password

## ⚠️ Troubleshooting

### Error: "No such file or directory"
This means the database host is incorrect. Check:
1. Database host name (localhost vs. specific hostname)
2. Database name spelling
3. Username and password accuracy

### Error: "Access denied"
This means credentials are wrong. Verify:
1. Database username
2. Database password  
3. User has correct permissions

### Error: "Database does not exist"
This means:
1. Database name is incorrect
2. Database hasn't been created yet
3. Import the schema from `database_schema.sql`

## 📁 Files Updated for Hosting

✅ `/config/database.php` - Centralized database configuration
✅ `/api/business-card-pdf.php` - Fixed database connection  
✅ `/api/business-card.php` - Fixed database connection

## 🧪 Testing After Upload

1. Update database credentials in `/config/database.php`
2. Test API endpoint: `https://your-domain.com/api/business-card-pdf.php?contact=1&format=preview`
3. Test registration: `https://your-domain.com/register`
4. Test profile access: `https://your-domain.com/private/your-slug`

## 📞 If You Need Help

If you encounter issues, check:
1. Hosting error logs for detailed error messages
2. phpMyAdmin to verify database tables exist
3. File permissions (755 for directories, 644 for files)