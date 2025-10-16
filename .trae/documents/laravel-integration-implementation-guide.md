# Laravel Integration Implementation Guide

## 1. Prerequisites and Setup

### 1.1 System Requirements
- PHP 8.2 or higher
- Composer 2.0+
- Node.js 18+ and npm
- SQLite or PostgreSQL database
- Redis (optional, for caching)

### 1.2 Initial Setup
```bash
# Clone the repository
git clone <repository-url>
cd sakip

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

# Build assets
npm run build
```

### 1.3 Environment Configuration
```env
# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Application Settings
APP_NAME="SAKIP System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Cache Configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@sakip.local
MAIL_FROM_NAME="${APP_NAME}"
```

## 2. Frontend Integration

### 2.1 Vite Configuration Update
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sakip/data-tables.js',
                'resources/js/sakip/dashboard.js',
                'resources/js/sakip/assessment.js',
                'resources/js/sakip/report.js',
                'resources/js/sakip/data-collection.js',
                'resources/js/sakip/audit-trail.js',
                'resources/js/sakip/notification.js',
                'resources/js/sakip/helpers.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'sakip-vendor': ['react', 'react-dom'],
                    'sakip-components': [
                        'resources/js/sakip/data-tables.js',
                        'resources/js/sakip/dashboard.js',
                        'resources/js/sakip/assessment.js'
                    ]
                }
            }
        }
    }
});
```

### 2.2 Main Application JavaScript
```javascript
// resources/js/app.js
import './bootstrap';
import '../css/app.css';

// Import SAKIP modules
import SAKIP_DATA_TABLES from './sakip/data-tables.js';
import SAKIP_DASHBOARD from './sakip/dashboard.js';
import SAKIP_ASSESSMENT from './sakip/assessment.js';
import SAKIP_REPORT from './sakip/report.js';
import SAKIP_DATA_COLLECTION from './sakip/data-collection.js';
import SAKIP_AUDIT_TRAIL from './sakip/audit-trail.js';
import SAKIP_NOTIFICATION from './sakip/notification.js';
import SAKIP_HELPERS from './sakip/helpers.js';

// Make SAKIP modules globally available
window.SAKIP_DATA_TABLES = SAKIP_DATA_TABLES;
window.SAKIP_DASHBOARD = SAKIP_DASHBOARD;
window.SAKIP_ASSESSMENT = SAKIP_ASSESSMENT;
window.SAKIP_REPORT = SAKIP_REPORT;
window.SAKIP_DATA_COLLECTION = SAKIP_DATA_COLLECTION;
window.SAKIP_AUDIT_TRAIL = SAKIP_AUDIT_TRAIL;
window.SAKIP_NOTIFICATION = SAKIP_NOTIFICATION;
window.SAKIP_HELPERS = SAKIP_HELPERS;

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        window.SAKIP_HELPERS.setCsrfToken(csrfToken);
    }

    // Initialize theme support
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);

    // Initialize notification system
    window.SAKIP_NOTIFICATION.init();

    // Initialize tooltips and popovers
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
```

### 2.3 CSS Integration
```css
/* resources/css/app.css */
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

/* SAKIP Custom Styles */
@import '../css/sakip-styles.css';

/* Government styling */
@import '../css/sakip-gov-styles.css';

/* Component styles */
@import '../css/sakip-components.css';

/* Responsive styles */
@import '../css/sakip-responsive.css';

/* Accessibility styles */
@import '../css/sakip-accessibility.css';

/* Print styles */
@import '../css/sakip-print.css';
```

## 3. Backend Integration

### 3.1 Service Provider Configuration
```php
// app/Providers/AppServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SakipDashboardService;
use App\Services\SakipExportService;
use App\Services\SakipValidationService;
use App\Services\SakipNotificationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register SAKIP services
        $this->app->singleton(SakipDashboardService::class, function ($app) {
            return new SakipDashboardService(
                $app->make(PerformanceIndicatorService::class),
                $app->make(PerformanceDataService::class),
                $app->make(AssessmentService::class)
            );
        });

        $this->app->singleton(SakipExportService::class, function ($app) {
            return new SakipExportService(
                $app->make(ReportGenerationService::class),
                $app->make(PerformanceDataService::class)
            );
        });

        $this->app->singleton(SakipValidationService::class, function ($app) {
            return new SakipValidationService(
                $app->make(DataValidationService::class),
                $app->make(ComplianceService::class)
            );
        });

        $this->app->singleton(SakipNotificationService::class, function ($app) {
            return new SakipNotificationService(
                $app->make(NotificationService::class),
                $app->make(AuditService::class)
            );
        });
    }

    public function boot(): void
    {
        // Configure pagination
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Configure model bindings
        \Illuminate\Database\Eloquent\Model::unguard();
    }
}
```

### 3.2 Route Configuration
```php
// routes/api_sakip.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sakip\PerformanceIndicatorController;
use App\Http\Controllers\Sakip\DataCollectionController;
use App\Http\Controllers\Sakip\AssessmentController;
use App\Http\Controllers\Sakip\ReportController;
use App\Http\Controllers\Sakip\SakipDashboardController;

Route::prefix('sakip')->middleware(['auth', 'verified'])->group(function () {
    // API endpoints for data tables
    Route::get('/indicators/data', [PerformanceIndicatorController::class, 'getDataTableData'])
        ->name('sakip.api.indicators.data');
    
    Route::get('/performance-data/data', [DataCollectionController::class, 'getDataTableData'])
        ->name('sakip.api.performance-data.data');
    
    Route::get('/assessments/data', [AssessmentController::class, 'getDataTableData'])
        ->name('sakip.api.assessments.data');
    
    Route::get('/reports/data', [ReportController::class, 'getDataTableData'])
        ->name('sakip.api.reports.data');
    
    // Export endpoints
    Route::post('/export/csv', [ReportController::class, 'exportCsv'])
        ->name('sakip.api.export.csv');
    
    Route::post('/export/excel', [ReportController::class, 'exportExcel'])
        ->name('sakip.api.export.excel');
    
    Route::post('/export/pdf', [ReportController::class, 'exportPdf'])
        ->name('sakip.api.export.pdf');
});
```

### 3.3 Controller Integration
```php
// app/Http/Controllers/Sakip/PerformanceIndicatorController.php
<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Services\PerformanceIndicatorService;
use App\Http\Resources\PerformanceIndicatorResource;
use Illuminate\Http\Request;

class PerformanceIndicatorController extends Controller
{
    protected $indicatorService;

    public function __construct(PerformanceIndicatorService $indicatorService)
    {
        $this->indicatorService = $indicatorService;
    }

    public function getDataTableData(Request $request)
    {
        try {
            $data = $this->indicatorService->getDataTableData($request->all());
            
            return response()->json([
                'success' => true,
                'data' => PerformanceIndicatorResource::collection($data['data']),
                'meta' => [
                    'total' => $data['total'],
                    'per_page' => $data['per_page'],
                    'current_page' => $data['current_page'],
                    'last_page' => $data['last_page'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to fetch data',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
```

## 4. Data Table Integration

### 4.1 JavaScript Integration Helper
```javascript
// resources/js/sakip/integration-helpers.js
export class SakipIntegrationHelper {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || window.location.origin;
        this.csrfToken = options.csrfToken || this.getCsrfToken();
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    async fetchDataTableData(endpoint, params = {}) {
        try {
            const url = new URL(endpoint, this.baseUrl);
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    url.searchParams.append(key, params[key]);
                }
            });

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: this.defaultHeaders,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error?.message || 'Unknown error');
            }

            return data;
        } catch (error) {
            console.error('Data table fetch error:', error);
            throw error;
        }
    }

    async exportData(endpoint, format, data = {}) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    ...this.defaultHeaders,
                    'Accept': this.getAcceptHeader(format)
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.blob();
        } catch (error) {
            console.error('Export error:', error);
            throw error;
        }
    }

    getAcceptHeader(format) {
        const formats = {
            'csv': 'text/csv',
            'excel': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf': 'application/pdf',
            'json': 'application/json'
        };
        return formats[format] || 'application/json';
    }
}
```

### 4.2 Data Table Initialization
```javascript
// resources/js/sakip/table-initializer.js
import { SakipIntegrationHelper } from './integration-helpers.js';

export class SakipTableInitializer {
    constructor() {
        this.helper = new SakipIntegrationHelper();
        this.tables = new Map();
    }

    initializeTable(tableId, config) {
        const tableElement = document.getElementById(tableId);
        if (!tableElement) {
            console.error(`Table element with ID '${tableId}' not found`);
            return;
        }

        const tableConfig = {
            ...config,
            dataSource: {
                type: 'api',
                endpoint: config.endpoint,
                fetchFunction: (params) => this.helper.fetchDataTableData(config.endpoint, params)
            }
        };

        const dataTable = new SAKIP_DATA_TABLES.DataTable(tableConfig);
        this.tables.set(tableId, dataTable);

        // Render table
        tableElement.innerHTML = '';
        tableElement.appendChild(dataTable.render());

        // Set up export functionality
        if (config.enableExport) {
            this.setupExportHandlers(tableId, config);
        }

        return dataTable;
    }

    setupExportHandlers(tableId, config) {
        const table = this.tables.get(tableId);
        if (!table) return;

        // CSV Export
        table.on('exportCsv', (data) => {
            this.helper.exportData(config.exportEndpoints.csv, 'csv', data)
                .then(blob => this.downloadBlob(blob, `${tableId}-export.csv`))
                .catch(error => SAKIP_NOTIFICATION.error('Export failed: ' + error.message));
        });

        // Excel Export
        table.on('exportExcel', (data) => {
            this.helper.exportData(config.exportEndpoints.excel, 'excel', data)
                .then(blob => this.downloadBlob(blob, `${tableId}-export.xlsx`))
                .catch(error => SAKIP_NOTIFICATION.error('Export failed: ' + error.message));
        });

        // PDF Export
        table.on('exportPdf', (data) => {
            this.helper.exportData(config.exportEndpoints.pdf, 'pdf', data)
                .then(blob => this.downloadBlob(blob, `${tableId}-export.pdf`))
                .catch(error => SAKIP_NOTIFICATION.error('Export failed: ' + error.message));
        });
    }

    downloadBlob(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.SAKIP_TABLE_INITIALIZER = new SakipTableInitializer();
});
```

## 5. Testing Integration

### 5.1 Unit Test Example
```php
// tests/Unit/Services/SakipDashboardServiceTest.php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SakipDashboardService;
use App\Services\PerformanceIndicatorService;
use App\Services\PerformanceDataService;
use App\Services\AssessmentService;
use Mockery;

class SakipDashboardServiceTest extends TestCase
{
    protected $dashboardService;
    protected $indicatorServiceMock;
    protected $dataServiceMock;
    protected $assessmentServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicatorServiceMock = Mockery::mock(PerformanceIndicatorService::class);
        $this->dataServiceMock = Mockery::mock(PerformanceDataService::class);
        $this->assessmentServiceMock = Mockery::mock(AssessmentService::class);

        $this->dashboardService = new SakipDashboardService(
            $this->indicatorServiceMock,
            $this->dataServiceMock,
            $this->assessmentServiceMock
        );
    }

    public function test_get_dashboard_data_returns_correct_structure()
    {
        // Arrange
        $this->indicatorServiceMock
            ->shouldReceive('getActiveIndicatorsCount')
            ->once()
            ->andReturn(10);

        $this->dataServiceMock
            ->shouldReceive('getDataEntryStatus')
            ->once()
            ->andReturn(['completed' => 8, 'pending' => 2]);

        $this->assessmentServiceMock
            ->shouldReceive('getAssessmentStatus')
            ->once()
            ->andReturn(['approved' => 5, 'pending' => 3, 'rejected' => 2]);

        // Act
        $result = $this->dashboardService->getDashboardData();

        // Assert
        $this->assertArrayHasKey('indicators', $result);
        $this->assertArrayHasKey('data_entry', $result);
        $this->assertArrayHasKey('assessments', $result);
        $this->assertEquals(10, $result['indicators']['total']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
```

### 5.2 Feature Test Example
```php
// tests/Feature/SakipDataTableApiTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PerformanceIndicator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SakipDataTableApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_fetch_indicators_data_table_data()
    {
        // Arrange
        PerformanceIndicator::factory()->count(15)->create();

        // Act
        $response = $this->getJson('/api/sakip/indicators/data');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'measurement_unit',
                        'created_at'
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page'
                ]
            ]);
    }

    public function test_data_table_respects_pagination_parameters()
    {
        // Arrange
        PerformanceIndicator::factory()->count(25)->create();

        // Act
        $response = $this->getJson('/api/sakip/indicators/data?page=2&per_page=10');

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 10);
    }
}
```

## 6. Performance Optimization

### 6.1 Database Query Optimization
```php
// app/Services/PerformanceIndicatorService.php
public function getDataTableData(array $params = [])
{
    $query = PerformanceIndicator::with([
        'targets',
        'performanceData',
        'responsibleUser',
        'activity.program.institution'
    ]);

    // Apply filters
    if (!empty($params['search'])) {
        $query->where(function ($q) use ($params) {
            $q->where('name', 'like', '%' . $params['search'] . '%')
              ->orWhere('description', 'like', '%' . $params['search'] . '%');
        });
    }

    // Apply sorting
    if (!empty($params['sort_by'])) {
        $direction = $params['sort_direction'] ?? 'asc';
        $query->orderBy($params['sort_by'], $direction);
    }

    // Apply pagination
    $perPage = $params['per_page'] ?? 10;
    return $query->paginate($perPage);
}
```

### 6.2 Cache Implementation
```php
// app/Services/SakipDashboardService.php
use Illuminate\Support\Facades\Cache;

class SakipDashboardService
{
    protected $cacheTtl = 300; // 5 minutes

    public function getDashboardData()
    {
        return Cache::remember('sakip_dashboard_data', $this->cacheTtl, function () {
            return [
                'indicators' => $this->getIndicatorsSummary(),
                'data_entry' => $this->getDataEntryStatus(),
                'assessments' => $this->getAssessmentStatus(),
                'compliance' => $this->getComplianceStatus(),
            ];
        });
    }

    public function clearDashboardCache()
    {
        Cache::forget('sakip_dashboard_data');
    }
}
```

## 7. Security Implementation

### 7.1 Policy Implementation
```php
// app/Policies/PerformanceIndicatorPolicy.php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PerformanceIndicator;

class PerformanceIndicatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('performance-indicators.view');
    }

    public function view(User $user, PerformanceIndicator $indicator): bool
    {
        return $user->hasPermission('performance-indicators.view') ||
               $user->id === $indicator->responsible_user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('performance-indicators.create');
    }

    public function update(User $user, PerformanceIndicator $indicator): bool
    {
        return $user->hasPermission('performance-indicators.update') ||
               $user->id === $indicator->responsible_user_id;
    }

    public function delete(User $user, PerformanceIndicator $indicator): bool
    {
        return $user->hasPermission('performance-indicators.delete');
    }
}
```

### 7.2 Middleware Configuration
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    // ... existing middleware
    'sakip.access' => \App\Http\Middleware\SakipAccessMiddleware::class,
    'sakip.audit' => \App\Http\Middleware\SakipAuditMiddleware::class,
    'sakip.throttle' => \App\Http\Middleware\SakipThrottleMiddleware::class,
];
```

## 8. Deployment Checklist

### 8.1 Pre-deployment
- [ ] Run all tests: `php artisan test`
- [ ] Check code standards: `./vendor/bin/pint`
- [ ] Optimize autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Clear all caches: `php artisan optimize:clear`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed production data: `php artisan db:seed --class=ProductionSeeder`
- [ ] Build assets: `npm run build`
- [ ] Set up cron jobs for scheduled tasks
- [ ] Configure queue workers
- [ ] Set up monitoring and logging

### 8.2 Post-deployment
- [ ] Verify all routes are accessible
- [ ] Test authentication and authorization
- [ ] Check data table functionality
- [ ] Verify export features work correctly
- [ ] Test notification system
- [ ] Monitor application logs
- [ ] Check performance metrics
- [ ] Verify backup procedures
- [ ] Test disaster recovery procedures

This implementation guide provides a comprehensive step-by-step approach to integrating all existing SAKIP components with the Laravel application while maintaining best practices and ensuring optimal performance.