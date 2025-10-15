@php
    $user = auth()->user();
    $userRole = $user->role ?? 'guest';
    $currentRoute = Route::currentRouteName();
    $currentPath = request()->path();
    
    // Breadcrumb generation
    $breadcrumbs = [];
    $pathSegments = explode('/', trim($currentPath, '/'));
    $currentUrl = '';
    
    foreach ($pathSegments as $segment) {
        $currentUrl .= '/' . $segment;
        $title = ucfirst(str_replace('-', ' ', $segment));
        
        // Special handling for SAKIP routes
        if ($segment === 'sakip') {
            $title = 'SAKIP';
        } elseif (Str::startsWith($currentRoute, 'sakip.')) {
            // Map route names to more readable titles
            $routeMap = [
                'indicators' => 'Indikator Kinerja',
                'data-collection' => 'Pengumpulan Data',
                'assessments' => 'Penilaian',
                'reports' => 'Laporan',
                'audit' => 'Audit Trail',
                'dashboard' => 'Dashboard',
                'create' => 'Buat Baru',
                'edit' => 'Edit',
                'show' => 'Detail',
                'index' => 'Daftar',
                'generate' => 'Generate',
                'preview' => 'Preview',
                'templates' => 'Template',
                'bulk-import' => 'Impor Massal',
                'evidence-upload' => 'Unggah Dokumen',
                'review' => 'Review',
                'executive' => 'Executive',
                'data-collector' => 'Data Collector',
                'assessor' => 'Assessor',
                'auditor' => 'Auditor'
            ];
            
            if (isset($routeMap[strtolower($segment)])) {
                $title = $routeMap[strtolower($segment)];
            }
        }
        
        $breadcrumbs[] = [
            'title' => $title,
            'url' => $currentUrl,
            'active' => end($pathSegments) === $segment
        ];
    }
    
    // Page title
    $pageTitle = 'SAKIP';
    if (count($breadcrumbs) > 1) {
        $pageTitle = end($breadcrumbs)['title'] . ' - SAKIP';
    }
    
    // Quick actions based on current page
    $quickActions = [];
    if (Str::startsWith($currentRoute, 'sakip.indicators')) {
        $quickActions = [
            [
                'title' => 'Tambah Indikator',
                'route' => 'sakip.indicators.create',
                'icon' => 'fas fa-plus',
                'permission' => 'create', App\Models\Sakip\PerformanceIndicator::class
            ],
            [
                'title' => 'Impor Indikator',
                'route' => 'sakip.indicators.import',
                'icon' => 'fas fa-upload',
                'permission' => 'import', App\Models\Sakip\PerformanceIndicator::class
            ]
        ];
    } elseif (Str::startsWith($currentRoute, 'sakip.data-collection')) {
        $quickActions = [
            [
                'title' => 'Input Data',
                'route' => 'sakip.data-collection.create',
                'icon' => 'fas fa-plus',
                'permission' => 'create', App\Models\Sakip\PerformanceData::class
            ],
            [
                'title' => 'Impor Massal',
                'route' => 'sakip.data-collection.bulk-import',
                'icon' => 'fas fa-upload',
                'permission' => 'import', App\Models\Sakip\PerformanceData::class
            ]
        ];
    } elseif (Str::startsWith($currentRoute, 'sakip.assessments')) {
        $quickActions = [
            [
                'title' => 'Buat Penilaian',
                'route' => 'sakip.assessments.create',
                'icon' => 'fas fa-plus',
                'permission' => 'create', App\Models\Sakip\Assessment::class
            ]
        ];
    } elseif (Str::startsWith($currentRoute, 'sakip.reports')) {
        $quickActions = [
            [
                'title' => 'Generate Laporan',
                'route' => 'sakip.reports.generate',
                'icon' => 'fas fa-file-export',
                'permission' => 'generate', App\Models\Sakip\Report::class
            ]
        ];
    }
@endphp

<header class="sakip-header" role="banner" aria-label="Header SAKIP">
    <div class="header-container">
        <!-- Header Top Section -->
        <div class="header-top">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-nav" aria-label="Breadcrumb">
                <ol class="breadcrumb-list">
                    @foreach($breadcrumbs as $index => $breadcrumb)
                        <li class="breadcrumb-item {{ $breadcrumb['active'] ? 'active' : '' }}">
                            @if(!$breadcrumb['active'] && $index > 0)
                                <a href="{{ $breadcrumb['url'] }}" class="breadcrumb-link">
                                    {{ $breadcrumb['title'] }}
                                </a>
                            @else
                                <span class="breadcrumb-text">{{ $breadcrumb['title'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
            
            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Search Bar -->
                <div class="header-search">
                    <form action="{{ route('sakip.search') }}" method="GET" class="search-form">
                        <div class="search-input-group">
                            <input type="text" 
                                   name="q" 
                                   class="search-input" 
                                   placeholder="Cari indikator, data, atau laporan..."
                                   value="{{ request('q') }}"
                                   aria-label="Pencarian SAKIP">
                            <button type="submit" class="search-button" aria-label="Cari">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Quick Actions Dropdown -->
                @if(count($quickActions) > 0)
                    <div class="quick-actions-dropdown">
                        <button class="quick-actions-toggle" 
                                data-dropdown="quickActions"
                                aria-expanded="false"
                                aria-haspopup="true">
                            <i class="fas fa-bolt"></i>
                            <span>Aksi Cepat</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="quick-actions-menu" id="quickActions" role="menu">
                            @foreach($quickActions as $action)
                                @can($action['permission'])
                                    <a href="{{ route($action['route']) }}" class="quick-action-item">
                                        <i class="{{ $action['icon'] }}"></i>
                                        <span>{{ $action['title'] }}</span>
                                    </a>
                                @endcan
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <!-- View Options -->
                <div class="view-options">
                    <button class="view-option-btn" id="printBtn" title="Cetak Halaman" aria-label="Cetak halaman">
                        <i class="fas fa-print"></i>
                    </button>
                    <button class="view-option-btn" id="fullscreenBtn" title="Layar Penuh" aria-label="Layar penuh">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button class="view-option-btn" id="refreshBtn" title="Segarkan" aria-label="Segarkan halaman">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                
                <!-- Help Button -->
                <a href="{{ route('help') }}" class="help-button" title="Bantuan" aria-label="Bantuan">
                    <i class="fas fa-question-circle"></i>
                </a>
            </div>
        </div>
        
        <!-- Header Bottom Section -->
        <div class="header-bottom">
            <!-- Page Title and Description -->
            <div class="page-info">
                <h1 class="page-title">{{ $pageTitle }}</h1>
                @if(isset($pageDescription))
                    <p class="page-description">{{ $pageDescription }}</p>
                @endif
            </div>
            
            <!-- Page Actions -->
            <div class="page-actions">
                @yield('page-actions')
            </div>
        </div>
        
        <!-- Status Bar -->
        <div class="status-bar">
            <div class="status-info">
                <span class="status-item">
                    <i class="fas fa-calendar"></i>
                    <span id="currentDate">{{ now()->format('l, d F Y') }}</span>
                </span>
                <span class="status-item">
                    <i class="fas fa-clock"></i>
                    <span id="currentTime">{{ now()->format('H:i') }}</span>
                </span>
                @if($user->institution)
                    <span class="status-item">
                        <i class="fas fa-building"></i>
                        <span>{{ $user->institution->name }}</span>
                    </span>
                @endif
            </div>
            
            <div class="status-indicators">
                <span class="status-indicator online" title="Koneksi Online">
                    <i class="fas fa-circle"></i>
                    <span>Online</span>
                </span>
                <span class="status-indicator" id="syncStatus" title="Status Sinkronisasi">
                    <i class="fas fa-sync-alt"></i>
                    <span>Tersinkron</span>
                </span>
            </div>
        </div>
    </div>
</header>

<style>
.sakip-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: sticky;
    top: 70px; /* Below navigation */
    z-index: 998;
}

.header-container {
    padding: 1rem 1.5rem;
}

.header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

/* Breadcrumb Styles */
.breadcrumb-nav {
    flex: 1;
    min-width: 200px;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.breadcrumb-item:not(:last-child)::after {
    content: '/';
    margin: 0 0.5rem;
    color: #d1d5db;
}

.breadcrumb-link {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-link:hover {
    color: var(--sakip-primary);
}

.breadcrumb-text {
    color: var(--sakip-dark);
    font-weight: 500;
}

.breadcrumb-item.active .breadcrumb-text {
    color: var(--sakip-primary);
}

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Search Styles */
.header-search {
    position: relative;
}

.search-form {
    margin: 0;
}

.search-input-group {
    display: flex;
    align-items: center;
    position: relative;
}

.search-input {
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    width: 250px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--sakip-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    width: 300px;
}

.search-button {
    position: absolute;
    right: 0.5rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s ease;
}

.search-button:hover {
    color: var(--sakip-primary);
}

/* Quick Actions Dropdown */
.quick-actions-dropdown {
    position: relative;
}

.quick-actions-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--sakip-primary);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quick-actions-toggle:hover {
    background: var(--sakip-primary-dark);
}

.quick-actions-toggle i:last-child {
    font-size: 0.75rem;
    transition: transform 0.3s ease;
}

.quick-actions-toggle[aria-expanded="true"] i:last-child {
    transform: rotate(180deg);
}

.quick-actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
    margin-top: 0.5rem;
}

.quick-actions-toggle[aria-expanded="true"] + .quick-actions-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.quick-action-item:last-child {
    border-bottom: none;
}

.quick-action-item:hover {
    background: #f8fafc;
    color: var(--sakip-primary);
}

.quick-action-item i {
    width: 16px;
    text-align: center;
}

/* View Options */
.view-options {
    display: flex;
    gap: 0.25rem;
}

.view-option-btn {
    background: none;
    border: 1px solid #d1d5db;
    color: #6b7280;
    padding: 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.view-option-btn:hover {
    border-color: var(--sakip-primary);
    color: var(--sakip-primary);
}

.view-option-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Help Button */
.help-button {
    background: none;
    border: 1px solid #d1d5db;
    color: #6b7280;
    padding: 0.5rem;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 1rem;
}

.help-button:hover {
    border-color: var(--sakip-primary);
    color: var(--sakip-primary);
}

/* Header Bottom Section */
.header-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-info {
    flex: 1;
    min-width: 200px;
}

.page-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--sakip-dark);
    line-height: 1.2;
}

.page-description {
    margin: 0.25rem 0 0 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.page-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Status Bar */
.status-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.75rem;
    color: #6b7280;
    flex-wrap: wrap;
    gap: 1rem;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-item i {
    font-size: 0.875rem;
}

.status-indicators {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-indicator.online i {
    color: #10b981;
}

.status-indicator i {
    font-size: 0.5rem;
}

#syncStatus i {
    animation: spin 2s linear infinite;
    color: #6b7280;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .header-container {
        padding: 1rem;
    }
    
    .header-top {
        flex-direction: column;
        align-items: stretch;
    }
    
    .breadcrumb-nav {
        order: 2;
    }
    
    .header-actions {
        order: 1;
        justify-content: space-between;
    }
    
    .search-input {
        width: 150px;
    }
    
    .search-input:focus {
        width: 180px;
    }
    
    .header-bottom {
        flex-direction: column;
        align-items: stretch;
    }
    
    .page-actions {
        justify-content: flex-end;
    }
    
    .status-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    
    .status-info,
    .status-indicators {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .quick-actions-toggle span {
        display: none;
    }
    
    .view-options {
        display: none;
    }
    
    .help-button {
        display: none;
    }
}

/* Print Styles */
@media print {
    .sakip-header {
        position: static;
        box-shadow: none;
        border-bottom: 1px solid #000;
    }
    
    .header-actions,
    .status-bar {
        display: none;
    }
    
    .header-bottom {
        border-top: none;
        margin-top: 0;
        padding-top: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('currentTime').textContent = timeString;
    }
    
    // Update time every minute
    updateTime();
    setInterval(updateTime, 60000);
    
    // Quick actions dropdown
    const quickActionsToggle = document.querySelector('.quick-actions-toggle');
    const quickActionsMenu = document.getElementById('quickActions');
    
    if (quickActionsToggle && quickActionsMenu) {
        quickActionsToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.quick-actions-dropdown')) {
                quickActionsToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Print functionality
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Fullscreen functionality
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    this.innerHTML = '<i class="fas fa-compress"></i>';
                    this.title = 'Keluar dari layar penuh';
                });
            } else {
                document.exitFullscreen().then(() => {
                    this.innerHTML = '<i class="fas fa-expand"></i>';
                    this.title = 'Layar penuh';
                });
            }
        });
        
        // Update icon when exiting fullscreen via ESC
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
                fullscreenBtn.title = 'Layar penuh';
            }
        });
    }
    
    // Refresh functionality
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Add loading animation
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
    }
    
    // Search form submission
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
    
    // Sync status simulation
    function updateSyncStatus() {
        const syncStatus = document.getElementById('syncStatus');
        if (syncStatus) {
            // Simulate sync status check
            const isOnline = navigator.onLine;
            if (isOnline) {
                syncStatus.innerHTML = '<i class="fas fa-sync-alt"></i><span>Tersinkron</span>';
                syncStatus.className = 'status-indicator';
            } else {
                syncStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Offline</span>';
                syncStatus.className = 'status-indicator offline';
            }
        }
    }
    
    // Update sync status periodically
    updateSyncStatus();
    setInterval(updateSyncStatus, 30000); // Check every 30 seconds
    
    // Online/offline event listeners
    window.addEventListener('online', updateSyncStatus);
    window.addEventListener('offline', updateSyncStatus);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            if (printBtn) {
                printBtn.click();
            }
        }
        
        // F5 or Ctrl+R for refresh
        if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
            e.preventDefault();
            if (refreshBtn) {
                refreshBtn.click();
            }
        }
        
        // / for search focus
        if (e.key === '/' && !e.ctrlKey && !e.altKey && !e.metaKey) {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
});
</script>