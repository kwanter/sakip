# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Development
```bash
# Full development stack (server, queue, logs, vite)
composer dev

# Individual services
php artisan serve
php artisan queue:work
php artisan pail

# Frontend
npm run dev     # Development
npm run build   # Production build
```

### Testing & Quality
```bash
composer test           # Run all tests
php artisan test --filter=TestName  # Specific test
./vendor/bin/pint       # Fix code style (Pint - Laravel Pint)
./vendor/bin/pint --test  # Check style without fixing
```

### Docker (via Makefile)
```bash
make up          # Start containers
make down        # Stop containers
make shell       # Open shell in app container
make migrate     # Run migrations
make fresh       # Migrate + seed
make cache       # Clear all caches
make optimize    # Optimize for production
make test        # Run tests
make backup      # Backup database
```

### Cache & Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear  # Clear all caches
```

## Architecture Overview

SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah) is a Laravel 12.x application for managing Indonesian government performance accountability. The codebase follows a clean architecture with clear separation of concerns.

### Design Patterns

**Service Layer Pattern** - Core business logic resides in `/app/Services`. Controllers delegate to services:
- `SakipExportService` - Orchestrates exports (delegates to format-specific services)
- `SakipValidationService` - Data validation rules
- `ReportCalculationService` - Report generation logic
- `DataValidationService` - Input validation
- `DropdownCacheService` - Cached dropdown data
- `PerformanceCalculationService` - Achievement calculations

**Strategy Pattern** - Export functionality:
- `/app/Services/Export/` contains `ExcelExportService`, `CsvExportService`, `PdfExportService`, `JsonExportService`
- `SakipExportService` orchestrates and delegates to appropriate format handler

### Domain Model

**Hierarchy:** Instansi (Institutions) → SasaranStrategis (Strategic Objectives) → Program → Kegiatan (Activities) → PerformanceIndicators

**Core Entities:**
- `PerformanceIndicator` - KPI definitions with measurement units, formulas, polarity (maximize/minimize)
- `Target` - Annual targets with quarterly breakdowns
- `PerformanceData` - Actual values with workflow (draft → submitted → validated → approved)
- `Assessment` - Evaluations with scoring and evidence linking
- `EvidenceDocument` - Supporting file attachments
- `AuditLog` - Complete audit trail for all actions

### Key Technical Details

**UUID Primary Keys** - All tables use UUIDs instead of auto-increment IDs. When creating migrations, use `$table->uuid('id')->primary();`

**Soft Deletes** - All models use soft deletes for data integrity. Include `deleted_at` in migrations and `use SoftDeletes` in models.

**Institution-Based Scoping** - Non-super-admin users are scoped to their institution via the `InstansiScope` global scope (`/app/Models/Scopes/InstansiScope.php`). Applied to models with a direct `instansi_id` column: Program, SasaranStrategis, PerformanceData, Report, AuditLog, PerformanceIndicator. Use `Model::withoutGlobalScope(InstansiScope::class)` for admin reports.

**Performance Thresholds** - Defined in `config/sakip.php`:
- Excellent: 100%, Good: 80%, Satisfactory: 60%
- Max achievement capped at 200%

**File Upload** - Evidence documents validated in `config/sakip.php`:
- Max 10MB
- Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG

**Dashboard Caching** - Role-based dashboards cached for 5 minutes (configurable)

### Role-Based Architecture

**Dashboard Routing** - Users are redirected based on role:
- Super Admin / Admin → `/admin/dashboard`
- Other roles → `/sakip/dashboard`

**Roles** (via Spatie Laravel Permission):
- Super Admin - Full system access
- Admin - Administrative functions
- Assessor - Assessment and evaluation
- Auditor - Audit and compliance
- Data Collector - Data entry

### Security Implementation

**Middleware:**
- `SecurityHeadersMiddleware` - CSP, HSTS, X-Frame-Options
- `SecureFileUploadMiddleware` - File upload validation

**Policies** - Located in `/app/Policies/`, authorize actions on models

### Constants

Define system-wide values in `/app/Constants/`:
- `Status` - General status constants
- `ReportStatus` - Report workflow states
- `AssessmentStatus` - Assessment states
- `SystemRoles` - Role definitions
- `ValidationRules` - Reusable validation rules
- `Pagination` - Pagination defaults

### Frontend

- **Blade Templates** - Server-side rendered in `/resources/views/`
- **Bootstrap 5 + jQuery + DataTables** via CDN; `custom-scripts.js`, `helpers.js`, `admin-settings.js` in `public/js/`
- **Vite** bundles five vanilla JS modules (`data-tables`, `dashboard`, `notification`, `helpers`, `data-table-init`) + chart.js + Tailwind 4
- **No React/Inertia** — removed in 2026 architecture cleanup

### Report Generation

Reports use template-based generation:
1. Template defined in `ReportTemplate` model
2. Data fetched via services
3. Format handled by export services (Excel, PDF, JSON)
4. Approval workflow before finalization

### API Routes

AJAX endpoints in `routes/web_sakip.php` under `/sakip/api/` prefix (session auth, not Sanctum), for:
- Performance indicators listing
- Performance data queries
- Assessment analytics
- Report analytics
- Audit analytics
- Cascade dropdowns (sasaran-strategis, program, kegiatan by parent)

No `routes/api.php` CRUD API. Authentication is session-based via `auth` + `verified` middleware.

## Important Notes

- Always use UUID for new table primary keys
- Add soft deletes to any new model that tracks data
- Include the `InstansiScope` global scope on models with a direct `instansi_id`
- Use the service layer for business logic, keep controllers thin
- All PerformanceData transitions must go through proper workflow states
- Audit logging is automatic via `AuditLog` model - ensure important actions create log entries
- Dashboard data is cached - clear cache when updating related data
