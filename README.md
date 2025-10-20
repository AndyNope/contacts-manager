# EasyContact - Professional Contact Management Platform

A modern, secure contact management system with multi-tenant architecture, featuring both private profiles and company team management.

## 🚀 Features

### ✨ **Core Functionality**
- **Multi-Tenant Architecture** - Each company has its own instance
- **Private Profiles** - Secure personal profiles with UUID-based URLs
- **Company Profiles** - Branded company pages with team management
- **Admin Dashboard** - Complete contact management interface
- **PayPal Integration** - Automated subscription management
- **Responsive Design** - Mobile-first with dark/light mode support
- **QR Code Generation** - Instant contact sharing
- **vCard Export** - Standard contact format downloads

### 🔒 **Security & Privacy**
- **UUID-based URLs** - Cryptographically secure private profile URLs
- **Role-based Access Control** - Admin/user permissions
- **Secure Authentication** - Password hashing with PHP
- **Privacy Protection** - GDPR-compliant design
- **Access Control** - Company-specific data isolation

### � **Business Features**
- **Subscription Management** - Free, Basic, and Premium plans
- **Contact Analytics** - Profile views and engagement tracking
- **Bulk Operations** - Efficient team member management
- **Public/Private Toggle** - Granular visibility controls
- **Featured Contacts** - Highlight key team members

## 🛠️ Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite enabled
- Web hosting with PHP support

### 1. Database Setup

```sql
-- Create database and user
CREATE DATABASE easycontact;
CREATE USER 'easycontact'@'localhost' IDENTIFIED BY 'EzC0nt@ct2025!';
GRANT ALL PRIVILEGES ON easycontact.* TO 'easycontact'@'localhost';
FLUSH PRIVILEGES;
mysql -u easycontact -p easycontact < database/schema.sql
mysql -u easycontact -p easycontact < setup_private_profiles.sql
```

### 2. Import Database Schema

```bash
mysql -u easycontact -p easycontact < database_schema.sql
```

### 3. Run UUID Migration (First Time Only)

Access `https://yoursite.com/migrate_add_uuid.php` to add UUID security to existing installations. **Delete this file after running.**

### 4. Configure Web Server

Ensure `.htaccess` is enabled and mod_rewrite is active. The included `.htaccess` file handles all URL routing.

## 🌐 URL Structure

### Public Pages
- `/` - Homepage/Marketing
- `/login` - User login
- `/register` - Registration
- `/company-login` - Company admin login
- `/terms` - Terms of Service
- `/privacy` - Privacy Policy

### Private Profiles (Free Plan)
- `/private/[uuid]` - Secure private user profiles
- Example: `/private/f566aa33-2186-4430-9f12-436adaeff8bf`

### Company Profiles (Basic/Premium)
- `/[company-slug]` - Company contact listing
- `/[company-slug]/profile/[contact-slug]` - Individual contact
- `/dashboard` - Company admin dashboard

### API Endpoints
- `/api/register.php` - Registration
- `/api/login_simple.php` - Authentication  
- `/api/vcard.php` - vCard downloads
- `/api/qr-code.php` - QR code generation
- `/api/paypal_webhook.php` - PayPal webhooks

## 👥 User Roles & Access

### Private Profile Users (Free)
- Create and manage personal contact profile
- Secure UUID-based profile URLs
- QR code and vCard generation
- Profile editing capabilities

### Company Admins (Basic/Premium)
- Full company dashboard access
- Add/edit/delete team members
- Manage company settings
- Bulk operations on contacts
- View analytics and statistics

### Public Users
- View public company pages
- Access individual contact profiles
- Download vCards and view QR codes

## � Security Features

### UUID-Based Private Profiles
- **Before**: `/private/3` (enumerable, predictable)
- **After**: `/private/f566aa33-2186-4430-9f12-436adaeff8bf` (cryptographically secure)
- Automatic migration from old ID-based URLs
- Backward compatibility with legacy links

### Access Controls
- Company data isolation
- Role-based permissions
- Session management
- Secure password hashing

## 💳 Subscription Plans

| Feature | Free | Basic | Premium |
|---------|------|-------|---------|
| Private Profile | ✅ | ✅ | ✅ |
| Company Page | ❌ | ✅ | ✅ |
| Team Members | 1 | 10 | Unlimited |
| Custom Branding | ❌ | ❌ | ✅ |
| Analytics | Basic | Advanced | Full |
| Support | Community | Email | Priority |

## � Deployment

### Production Checklist
- [ ] Set up SSL certificate (HTTPS required)
- [ ] Configure database with strong credentials
- [ ] Update PayPal configuration for live environment
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Remove migration files after running
- [ ] Test all functionality thoroughly

### Environment Configuration
Update database credentials in all PHP files:
- Default credentials are set for development
- Change `$host`, `$user`, `$pass`, `$dbname` for production

## 📱 Mobile Support

- Fully responsive design
- Mobile-optimized navigation
- Touch-friendly interfaces
- Progressive Web App features

## 🎨 Customization

### Themes
- Light/Dark mode toggle
- CSS custom properties for easy theming
- Bootstrap 5.3.0 integration

### Branding
- Company logos and colors
- Custom domain support (Premium)
- White-label options

## 📊 Analytics

- Profile view tracking
- QR code scan metrics
- User engagement statistics
- Company dashboard insights

## 🔧 Maintenance

### Regular Tasks
- Monitor subscription statuses
- Clean up expired sessions
- Review security logs
- Update dependencies

### Backup Strategy
- Database backups (daily recommended)
- File system backups
- Configuration backup

## 🤝 Contributing

This is a commercial project. For support or customization requests, please contact the development team.

## 📄 License

Proprietary software. All rights reserved.

## 📞 Support

- **Technical Issues**: Create an issue in the repository
- **Business Inquiries**: Contact via company website
- **Security Concerns**: Report privately to security team

---

**EasyContact** - Making professional networking effortless and secure.
