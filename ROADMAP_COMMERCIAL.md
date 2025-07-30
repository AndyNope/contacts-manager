# EasyContact.com - Commercial Roadmap 🚀

## Vision
Multi-tenant contact management platform with company profiles and subscription model.

## New Structure

### Domain & URLs
- **Main Domain**: `easy-contact.com`
- **Company Profiles**: `easy-contact.com/:company/profile/:contact-name`
- **Admin Dashboard**: `easy-contact.com/:company/admin`
- **Landing Page**: `easy-contact.com`

### Multi-Language Support
- **Default**: English 🇺🇸
- **Additional**: German 🇩🇪, French 🇫🇷
- **Implementation**: i18n system with language switcher

### Company Structure
- **Default Company**: BideBliss
- **Free Tier**: 1 private contact under BideBliss
- **Paid Tier**: Unlimited contacts for companies with subscription

## Phase 1: Core Multi-Tenant Architecture

### Database Changes
```sql
-- Companies table
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    domain VARCHAR(100),
    subscription_tier ENUM('free', 'basic', 'premium') DEFAULT 'free',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update contacts table
ALTER TABLE kontakte ADD COLUMN company_id INT,
ADD FOREIGN KEY (company_id) REFERENCES companies(id);

-- Users/Admins table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') DEFAULT 'editor',
    FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

### File Structure
```
/
├── config/
│   ├── database.php
│   ├── i18n.php
│   └── routes.php
├── src/
│   ├── Controllers/
│   │   ├── CompanyController.php
│   │   ├── ContactController.php
│   │   └── AuthController.php
│   ├── Models/
│   │   ├── Company.php
│   │   ├── Contact.php
│   │   └── User.php
│   └── Services/
│       ├── SubscriptionService.php
│       └── BusinessCardService.php
├── public/
│   ├── :company/
│   │   ├── profile/:contact-name
│   │   └── admin/
│   └── assets/
└── lang/
    ├── en.json
    ├── de.json
    └── fr.json
```

## Phase 2: Subscription Model

### Pricing Tiers
- **Free (BideBliss)**: 1 contact, basic features
- **Basic**: €9.99/month, 50 contacts, custom domain
- **Premium**: €19.99/month, unlimited contacts, white-label

### Features by Tier
| Feature | Free | Basic | Premium |
|---------|------|-------|---------|
| Contacts | 1 | 50 | Unlimited |
| Business Cards | ✓ | ✓ | ✓ |
| Custom Branding | ❌ | ✓ | ✓ |
| API Access | ❌ | ❌ | ✓ |
| Analytics | ❌ | Basic | Advanced |

## Phase 3: Advanced Features

### Business Card Enhancements
- Company-specific branding
- Multiple templates
- QR code analytics
- Bulk generation

### Analytics Dashboard
- Contact views
- QR code scans
- Geographic data
- Export reports

## Implementation Priority

1. **Week 1-2**: Multi-tenant database structure
2. **Week 3-4**: Company registration & auth system
3. **Week 5-6**: Multi-language implementation
4. **Week 7-8**: URL routing (:company/:contact-name)
5. **Week 9-10**: Subscription integration
6. **Week 11-12**: Enhanced business cards

## Technical Stack

### Backend
- **PHP 8.1+** with OOP structure
- **MySQL/MariaDB** for data persistence
- **JWT** for authentication
- **Stripe** for payments

### Frontend
- **Vanilla JS** with modern ES6+
- **Bootstrap 5** for responsive design
- **i18next** for internationalization
- **Chart.js** for analytics

### DevOps
- **Docker** for containerization
- **GitHub Actions** for CI/CD
- **Cloudflare** for CDN and security

## Revenue Model

### Subscription Revenue
- Free tier: €0 (lead generation)
- Basic tier: €9.99/month × estimated 1000 users = €9,990/month
- Premium tier: €19.99/month × estimated 200 users = €3,998/month
- **Total projected**: €13,988/month

### Additional Revenue Streams
- White-label licensing
- API usage fees
- Premium templates
- Custom integrations

## Next Steps

1. Set up development environment
2. Create multi-tenant database schema
3. Implement company registration flow
4. Build URL routing system
5. Add multi-language support
