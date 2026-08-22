# SAKIP - Sistem Akuntabilitas Kinerja Instansi Pemerintah

> **v1.1.0** — Security & Architecture Hardening (2026-08-22)
> [Changelog](#changelog)

A comprehensive web-based application for managing government performance accountability (SAKIP) in Indonesia. Built with Laravel, this system helps government institutions manage performance indicators, collect data, generate reports, and maintain accountability standards.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Usage Guide](#usage-guide)
- [Technical Architecture](#technical-architecture)
- [Security Features](#security-features)
- [API Documentation](#api-documentation)
- [Changelog](#changelog)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## Overview

SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah) is a performance accountability system designed specifically for Indonesian government institutions. The application provides a complete solution for:

- Managing organizational performance indicators
- Setting and tracking annual targets
- Collecting and validating performance data
- Conducting assessments against criteria
- Generating comprehensive reports
- Maintaining audit trails and compliance

## Features

### Performance Management
- **Performance Indicators**: Define and manage KPIs with measurement units and calculation formulas
- **Target Setting**: Set annual targets with quarterly breakdowns
- **Data Collection**: Submit, validate, and approve performance data
- **Achievement Tracking**: Automatic calculation of achievement percentages
- **Evidence Management**: Attach supporting documents to performance data

### Assessment & Evaluation
- **Assessment Criteria**: Define evaluation criteria with scoring methods
- **Criterion Management**: Manage assessment standards and benchmarks
- **Evidence Documentation**: Link documents to specific assessment criteria
- **Scoring System**: Automated scoring based on achievement levels

### Reporting
- **Report Templates**: Create customizable report templates
- **Automated Generation**: Generate reports with dynamic data
- **Multiple Formats**: Export to PDF, Excel, and other formats
- **Approval Workflow**: Multi-level approval process for reports
- **Historical Reports**: Maintain archive of previous reports

### User & Access Management
- **Role-Based Access Control**: Admin, Manager, Staff, and Auditor roles
- **Institution Assignment**: Assign users to specific institutions
- **Permission Management**: Granular control over feature access
- **Activity Logging**: Complete audit trail of user actions

### Security Features
- **Security Headers**: CSP (nonce-based), HSTS, X-Frame-Options, Referrer-Policy, Permissions-Policy
- **CSRF Protection**: Token-based protection for all forms and session-authenticated API calls
- **SQL Injection Prevention**: Parameterized queries via Eloquent ORM (all raw SQL uses bound params)
- **XSS Protection**: Blade output escaping; export cell values neutralized against spreadsheet formula injection
- **Authentication**: Secure login with password hashing (bcrypt), email verification, per-operation rate limiting
- **Authorization**: Spatie role/permission policies with institution (tenant) scoping; Super Admin role cannot be self-assigned
- **File Uploads**: `secure.file.upload` middleware — MIME + extension allowlist, 10 MB cap, archives blocked, evidence on private disk
- **Session Management**: Encrypted sessions, secure cookies, strict SameSite
- **Dependency Auditing**: `composer audit` runs in CI — 0 known advisories

## System Requirements

### Server Requirements
- PHP >= 8.3
- MySQL >= 8.0 or MariaDB >= 10.3
- Composer >= 2.0
- Node.js >= 16.x (for asset compilation)
- npm or yarn

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick (for image processing)
- cURL

### Recommended Server Configuration
- Memory Limit: 256M or higher
- Max Execution Time: 300 seconds
- Upload Max Filesize: 20M
- Post Max Size: 25M

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd sakip
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables

Edit `.env` file and configure:

```env
APP_NAME="SAKIP"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sakip
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@sakip.go.id"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Set Permissions

```bash
# Set proper permissions for storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Database Setup

### 1. Create Database

```bash
mysql -u root -p
CREATE DATABASE sakip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 2. Run Migrations

```bash
# Run all migrations
php artisan migrate

# Run with seeders (if available)
php artisan migrate --seed
```

### 3. Create Admin User

```bash
php artisan tinker
```

Then in tinker console:

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@sakip.go.id',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);

// Assign admin role (if using Spatie permissions)
$user->assignRole('admin');
```

## Configuration

### Security Headers

The application includes security middleware configured in `app/Http/Middleware/SecurityHeadersMiddleware.php`:

- Content Security Policy (CSP)
- HTTP Strict Transport Security (HSTS)
- X-Frame-Options
- X-Content-Type-Options
- Referrer-Policy
- Permissions-Policy

### Cache Configuration

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### Queue Configuration

For background job processing:

```bash
# Run queue worker
php artisan queue:work

# For production, use supervisor or systemd
```

## Usage Guide

### 1. Login and Dashboard

Access the application at `http://your-domain.com` and login with your credentials. The dashboard provides an overview of:
- Recent performance data
- Pending approvals
- Key metrics and statistics
- Quick access to main features

### 2. Managing Performance Indicators

**Create New Indicator:**
1. Navigate to SAKIP → Performance Indicators
2. Click "Create New Indicator"
3. Fill in:
   - Indicator code and name
   - Strategic objective
   - Measurement unit
   - Calculation formula
   - Data source
4. Set polarity (higher is better / lower is better)
5. Save the indicator

**Set Targets:**
1. Open a performance indicator
2. Click "Set Target"
3. Enter target value for the year
4. Optionally set quarterly breakdown
5. Submit for approval

### 3. Data Collection

**Submit Performance Data:**
1. Navigate to SAKIP → Data Collection
2. Click "Submit New Data"
3. Select performance indicator
4. Enter period (YYYY-MM)
5. Enter actual value achieved
6. Attach evidence documents
7. Add notes/explanation
8. Submit for validation

**Data Validation Workflow:**
- Draft → Submitted → Validated → Approved
- Each step requires appropriate user role
- Comments can be added at each stage

### 4. Assessments

**Create Assessment:**
1. Navigate to SAKIP → Assessments
2. Click "New Assessment"
3. Select institution and period
4. Choose assessment criteria
5. Link evidence documents
6. Calculate scores
7. Submit assessment

### 5. Report Generation

**Generate Report:**
1. Navigate to SAKIP → Reports
2. Click "Generate New Report"
3. Select report type
4. Choose template
5. Set date range and filters
6. Preview report
7. Generate and download

### 6. User Management (Admin Only)

**Create User:**
1. Navigate to Admin → Users
2. Click "Create User"
3. Enter user details:
   - Name and email
   - Password
   - Assign institution (optional)
   - Assign roles
4. Save user

**Institution Assignment:**
- Users can be assigned to specific institutions
- Leave empty for system-wide access
- Restricts data visibility based on institution

## Technical Architecture

### Framework & Core
- **Laravel 12.x**: PHP web application framework
- **MySQL 8.0**: Primary database
- **UUID Primary Keys**: All tables use UUIDs instead of auto-increment
- **Soft Deletes**: Logical deletion for data integrity

### Design Patterns
- **MVC Architecture**: Model-View-Controller pattern
- **Service Layer**: Business logic separated from controllers
- **Observer Pattern**: Model events and listeners
- **Factory Pattern**: Database seeders and factories

### Database Schema

**Key Tables:**
- `users`: User accounts with institution assignment
- `instansis`: Government institutions
- `performance_indicators`: KPI definitions
- `targets`: Annual targets for indicators
- `performance_data`: Actual performance records
- `assessments`: Assessment records
- `assessment_criteria`: Evaluation criteria
- `evidence_documents`: Supporting documents
- `reports`: Generated reports
- `report_templates`: Report templates

**Relationships:**
- Users belong to Institutions
- Performance Indicators have many Targets
- Performance Data belongs to Indicator
- Assessments have many Criteria
- Reports belong to Institutions

### Key Models

**User.php**
```php
protected $fillable = ['name', 'email', 'password', 'instansi_id'];
```

**PerformanceIndicator.php**
- Uses UUID primary key
- Soft deletes enabled
- Relationships: targets, performanceData
- Methods: getTargetForYear(), calculateAchievement()

**PerformanceData.php**
- Belongs to PerformanceIndicator
- Status workflow: draft → submitted → validated → approved
- Evidence attachment support
- Achievement calculation

**Assessment.php**
- Multiple criteria evaluation
- Scoring system
- Evidence linking

**Report.php**
- Template-based generation
- Approval workflow
- Multiple export formats

### Services

**AdminService**: User and role management
**SakipDataTableService**: DataTables server-side processing
**SakipExportService**: Data export to Excel/PDF
**TemplateService**: Report template processing

## Security Features

### 1. Input Validation
- All forms use Laravel Request Validation
- CSRF token protection on all POST requests
- Input sanitization and escaping

### 2. Authentication & Authorization
- Bcrypt password hashing
- Session-based authentication
- Role-based access control (Spatie Permissions)
- Route protection via middleware

### 3. Database Security
- Parameterized queries (Eloquent ORM)
- SQL injection prevention
- Foreign key constraints
- Transaction support

### 4. Security Headers
```php
Content-Security-Policy: default-src 'self'
Strict-Transport-Security: max-age=31536000
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
```

### 5. File Upload Security
- MIME type validation
- File size limits
- Secure storage location
- Filename sanitization

### 6. Audit Trail
- Activity logging for all critical actions
- User action timestamps
- IP address logging
- Change history tracking

## API Documentation

### Authentication

The application is session-based: users authenticate through the web login
form (`/login`) with CSRF protection. There is **no bearer-token API** —
API-style JSON endpoints reuse the authenticated web session and require a
verified email (`auth` + `verified` middleware).

### JSON Endpoints

AJAX/JSON endpoints live under `sakip/api/*` (`routes/web_sakip.php`) and
serve the dashboard UI. They are read-mostly and scoped to the caller's
institution (non-HQ users are pinned to their own `instansi_id`):

| Endpoint | Purpose |
|---|---|
| `GET sakip/api/dashboard-data` | Dashboard metrics, charts, activities |
| `GET sakip/api/performance-summary` | Performance summary for period |
| `GET sakip/api/achievement-trends` | Achievement trend series |
| `GET sakip/api/compliance-status` | Compliance status per institution |
| `GET sakip/api/indicator-comparison` | Indicator comparisons |
| `GET sakip/api/indicators/{indicator}/targets` | Targets for an indicator |
| `GET sakip/api/indicators/{indicator}/performance-data` | Performance data rows |
| `GET sakip/api/indicators/by-instansi/{instansi}` | Indicators (dropdown) |
| `GET sakip/api/sasaran-strategis/by-instansi/{instansi}` | Dropdown data |
| `GET sakip/api/program/by-sasaran-strategis/{sasaranStrategis}` | Dropdown data |
| `GET sakip/api/kegiatan/by-program/{program}` | Dropdown data |
| `GET sakip/api/assessment-analytics` | Assessment analytics |
| `GET sakip/api/report-analytics` | Report analytics |
| `GET sakip/api/audit-analytics` | Audit analytics |

All responses are JSON: `{"success": true, "data": ..., "timestamp": ...}`.

### Mutations

Data changes use standard server-rendered form routes (CSRF-protected):
`sakip/indicators/*`, `sakip/targets/*`, `sakip/data-collection/*`,
`sakip/assessments/*`, `sakip/reports/*`. File uploads on these routes are
guarded by `secure.file.upload` middleware (MIME + extension allowlist,
10 MB cap, archives blocked).

## Changelog

### v1.1.0 — 2026-08-22 — Security & Architecture Hardening

**Security**
- Removed dead parallel API stacks (api_sakip / api_v1) — the live API is the session-authenticated `sakip/api/*` group
- Blocked Super Admin self-escalation via role allowlist in `AdminService`
- Pinned non-HQ users to their own institution on all API endpoints (cross-tenant pivot fix)
- Evidence documents: server-pinned file_path, archive uploads (zip/rar) blocked, tenant-scoped reads/deletes, private-disk only
- Registered `AssessmentCriterionPolicy` (previously unpoliced) and implemented `ReportPolicy::approve`
- Removed data-corrupting `SanitizeInputMiddleware`; output escaping + bound queries remain the real defenses
- Whitelisted sort_by columns across 6 services (prevents 500s on invalid column names)
- Encrypted sessions + secure cookies in defaults; capped CSP-report log writes
- composer audit: 0 advisories (guzzlehttp/guzzle, league/commonmark updated)

**Build integrity**
- Restored export stack: phpoffice/phpspreadsheet + dompdf installed; formula-injection guard on all cell writes; export row cap (10 000 rows)
- Fixed 3 latent fatals: audit-log insert (missing action column), Cache::getRedis() crash on non-redis stores (6 services), evidence eager-loads referencing nonexistent relations

**Maintenance**
- Deleted unreferenced services (AuditService, EnhancedAuditService, ValidationOrchestrator), unused Repository layer, unmapped React/Inertia pages, dead Policies/Sakip stubs
- Collapsed duplicate performance-data/data-collection route group
- Repo hygiene: stray SQLite DB and compiled view cache gitignored

**Verification**: 77 tests passing, CI green (lint, PHP 8.3/8.4 tests, security audit, frontend build).

### v1.0.0 — Security Hardened Release

Initial tagged release with tenant-scoped authorization, CSP/HSTS headers, and baseline test suite.

## Troubleshooting

### Issue: Migration Error - Column Not Found

**Error:** `SQLSTATE[42S22]: Column not found`

**Solution:**
```bash
# Clear cached schema
php artisan config:clear
php artisan cache:clear

# Re-run migrations
php artisan migrate:fresh
```

### Issue: 500 Internal Server Error

**Check:**
1. PHP error logs: `storage/logs/laravel.log`
2. Web server error logs
3. Enable debug mode temporarily: `APP_DEBUG=true` in `.env`

**Common causes:**
- Missing PHP extensions
- Incorrect file permissions
- Database connection issues
- Missing environment variables

### Issue: Assets Not Loading

**Solution:**
```bash
# Recompile assets
npm run build

# Clear view cache
php artisan view:clear

# Check public storage link
php artisan storage:link
```

### Issue: Queue Jobs Not Processing

**Solution:**
```bash
# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue: Database Connection Refused

**Check:**
1. MySQL service is running
2. Database credentials in `.env` are correct
3. Database exists
4. User has proper permissions

```bash
# Test connection
mysql -h 127.0.0.1 -u username -p database_name
```

### Issue: Permission Denied Errors

**Solution:**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# For development (not recommended for production)
chmod -R 777 storage bootstrap/cache
```

### Issue: Session Expired Frequently

**Check `.env` settings:**
```env
SESSION_LIFETIME=120
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false  # true for HTTPS only
```

### Issue: Data Not Saving

**Common causes:**
1. Check validation rules in controllers
2. Check mass assignment protection in models (`$fillable` array)
3. Check database constraints
4. Review service layer methods

**Debug:**
```bash
# Enable query logging
DB::enableQueryLog();
// Your code here
dd(DB::getQueryLog());
```

### Performance Issues

**Optimization steps:**
```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enable OPcache in php.ini
opcache.enable=1
opcache.memory_consumption=256

# Optimize Composer autoloader
composer dump-autoload --optimize
```

## Contributing

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make changes and commit: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/your-feature`
5. Submit a pull request

### Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Write comments for complex logic
- Maintain consistent indentation (4 spaces)
- Run PHP CS Fixer before committing:
  ```bash
  ./vendor/bin/php-cs-fixer fix
  ```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TestName

# Generate coverage report
php artisan test --coverage
```

### Database Changes

- Always create migrations for schema changes
- Never modify existing migrations in production
- Test migrations both up and down
- Document complex migrations

### Pull Request Guidelines

- Describe what your PR does
- Reference any related issues
- Include screenshots for UI changes
- Ensure all tests pass
- Update documentation if needed

## License

This project is proprietary software developed for Indonesian government institutions. Unauthorized copying, distribution, or modification is prohibited.

For licensing inquiries, contact: [contact information]

---

## Support

For technical support or questions:
- Email: fendy24kwan@mahkamahagung.go.id

## Changelog

### Version 2.0.0 (Current)
- Migrated to UUID primary keys
- Added soft deletes to all tables
- Implemented user-institution assignment
- Enhanced security headers middleware
- Fixed multiple column reference errors
- Improved report generation system
- Added comprehensive audit trail

### Version 1.0.0
- Initial release
- Basic performance indicator management
- Target setting and tracking
- Assessment system
- Report generation

---

**Built with ❤️ for Indonesian Government Institutions**
