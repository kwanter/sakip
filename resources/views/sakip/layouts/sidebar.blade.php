@php
    $user = auth()->user();
    $userRole = $user->role ?? 'guest';
    $currentRoute = Route::currentRouteName();
    $currentPath = request()->path();
    
    // Sidebar menu items based on role and context
    $sidebarMenus = [
        'dashboard' => [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'route' => 'sakip.dashboard',
            'roles' => ['superadmin', 'admin', 'executive', 'data_collector', 'assessor', 'auditor', 'government_agency'],
            'items' => [
                [
                    'title' => 'Executive Summary',
                    'route' => 'sakip.dashboard.executive',
                    'roles' => ['superadmin', 'admin', 'executive', 'auditor']
                ],
                [
                    'title' => 'Data Collector',
                    'route' => 'sakip.dashboard.data-collector',
                    'roles' => ['superadmin', 'admin', 'data_collector']
                ],
                [
                    'title' => 'Assessor Panel',
                    'route' => 'sakip.dashboard.assessor',
                    'roles' => ['superadmin', 'admin', 'assessor']
                ],
                [
                    'title' => 'Auditor View',
                    'route' => 'sakip.dashboard.auditor',
                    'roles' => ['superadmin', 'admin', 'auditor']
                ]
            ]
        ],
        'indicators' => [
            'title' => 'Indikator Kinerja',
            'icon' => 'fas fa-chart-line',
            'route' => 'sakip.indicators.index',
            'roles' => ['superadmin', 'admin', 'executive', 'data_collector', 'assessor', 'government_agency'],
            'items' => [
                [
                    'title' => 'Daftar Indikator',
                    'route' => 'sakip.indicators.index',
                    'roles' => ['superadmin', 'admin', 'executive', 'data_collector', 'assessor', 'government_agency']
                ],
                [
                    'title' => 'Tambah Indikator',
                    'route' => 'sakip.indicators.create',
                    'roles' => ['superadmin', 'admin', 'executive']
                ],
                [
                    'title' => 'Kategori Indikator',
                    'route' => 'sakip.indicators.categories',
                    'roles' => ['superadmin', 'admin']
                ]
            ]
        ],
        'data-collection' => [
            'title' => 'Pengumpulan Data',
            'icon' => 'fas fa-database',
            'route' => 'sakip.data-collection.index',
            'roles' => ['superadmin', 'admin', 'data_collector', 'government_agency'],
            'items' => [
                [
                    'title' => 'Dashboard Data',
                    'route' => 'sakip.data-collection.index',
                    'roles' => ['superadmin', 'admin', 'data_collector', 'government_agency']
                ],
                [
                    'title' => 'Input Data',
                    'route' => 'sakip.data-collection.create',
                    'roles' => ['superadmin', 'admin', 'data_collector', 'government_agency']
                ],
                [
                    'title' => 'Impor Massal',
                    'route' => 'sakip.data-collection.bulk-import',
                    'roles' => ['superadmin', 'admin', 'data_collector']
                ],
                [
                    'title' => 'Dokumen Pendukung',
                    'route' => 'sakip.data-collection.evidence',
                    'roles' => ['superadmin', 'admin', 'data_collector', 'government_agency']
                ]
            ]
        ],
        'assessments' => [
            'title' => 'Penilaian Kinerja',
            'icon' => 'fas fa-clipboard-check',
            'route' => 'sakip.assessments.index',
            'roles' => ['superadmin', 'admin', 'assessor', 'executive'],
            'items' => [
                [
                    'title' => 'Antrian Penilaian',
                    'route' => 'sakip.assessments.index',
                    'roles' => ['superadmin', 'admin', 'assessor']
                ],
                [
                    'title' => 'Buat Penilaian',
                    'route' => 'sakip.assessments.create',
                    'roles' => ['superadmin', 'admin', 'assessor']
                ],
                [
                    'title' => 'Riwayat Penilaian',
                    'route' => 'sakip.assessments.history',
                    'roles' => ['superadmin', 'admin', 'assessor', 'executive']
                ]
            ]
        ],
        'reports' => [
            'title' => 'Laporan',
            'icon' => 'fas fa-file-alt',
            'route' => 'sakip.reports.index',
            'roles' => ['superadmin', 'admin', 'executive', 'auditor', 'government_agency'],
            'items' => [
                [
                    'title' => 'Dashboard Laporan',
                    'route' => 'sakip.reports.index',
                    'roles' => ['superadmin', 'admin', 'executive', 'auditor', 'government_agency']
                ],
                [
                    'title' => 'Generate Laporan',
                    'route' => 'sakip.reports.generate',
                    'roles' => ['superadmin', 'admin', 'executive']
                ],
                [
                    'title' => 'Template Laporan',
                    'route' => 'sakip.reports.templates',
                    'roles' => ['superadmin', 'admin']
                ],
                [
                    'title' => 'Laporan Tersedia',
                    'route' => 'sakip.reports.available',
                    'roles' => ['superadmin', 'admin', 'executive', 'auditor', 'government_agency']
                ]
            ]
        ],
        'audit' => [
            'title' => 'Audit Trail',
            'icon' => 'fas fa-history',
            'route' => 'sakip.audit.index',
            'roles' => ['superadmin', 'admin', 'auditor'],
            'items' => [
                [
                    'title' => 'Dashboard Audit',
                    'route' => 'sakip.audit.index',
                    'roles' => ['superadmin', 'admin', 'auditor']
                ],
                [
                    'title' => 'Log Aktivitas',
                    'route' => 'sakip.audit.logs',
                    'roles' => ['superadmin', 'admin', 'auditor']
                ],
                [
                    'title' => 'Laporan Audit',
                    'route' => 'sakip.audit.reports',
                    'roles' => ['superadmin', 'admin', 'auditor']
                ]
            ]
        ],
        'settings' => [
            'title' => 'Pengaturan',
            'icon' => 'fas fa-cog',
            'route' => 'sakip.settings',
            'roles' => ['superadmin', 'admin'],
            'items' => [
                [
                    'title' => 'Umum',
                    'route' => 'sakip.settings.general',
                    'roles' => ['superadmin', 'admin']
                ],
                [
                    'title' => 'Pengguna',
                    'route' => 'sakip.settings.users',
                    'roles' => ['superadmin', 'admin']
                ],
                [
                    'title' => 'Peran & Izin',
                    'route' => 'sakip.settings.roles',
                    'roles' => ['superadmin', 'admin']
                ],
                [
                    'title' => 'Institusi',
                    'route' => 'sakip.settings.institutions',
                    'roles' => ['superadmin', 'admin']
                ]
            ]
        ]
    ];
    
    // Get current module for highlighting
    $currentModule = '';
    foreach ($sidebarMenus as $key => $menu) {
        if (Str::startsWith($currentRoute, 'sakip.' . $key)) {
            $currentModule = $key;
            break;
        }
    }
    
    // Quick stats for sidebar header
    $quickStats = [
        'total_indicators' => \App\Models\Sakip\PerformanceIndicator::count(),
        'pending_data' => \App\Models\Sakip\PerformanceData::where('status', 'pending')->count(),
        'pending_assessments' => \App\Models\Sakip\Assessment::where('status', 'pending')->count(),
        'total_reports' => \App\Models\Sakip\Report::count(),
    ];
@endphp

<aside class="sakip-sidebar" id="sakipSidebar" role="complementary" aria-label="Sidebar SAKIP">
    <div class="sidebar-container">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h6 class="user-name">{{ $user->name ?? 'User' }}</h6>
                    <p class="user-role">{{ ucfirst(str_replace('_', ' ', $userRole)) }}</p>
                    @if($user->institution)
                        <small class="user-institution">{{ $user->institution->name ?? '' }}</small>
                    @endif
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-item">
                    <span class="stat-value">{{ $quickStats['total_indicators'] }}</span>
                    <span class="stat-label">Indikator</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $quickStats['pending_data'] }}</span>
                    <span class="stat-label">Data Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $quickStats['pending_assessments'] }}</span>
                    <span class="stat-label">Penilaian</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">{{ $quickStats['total_reports'] }}</span>
                    <span class="stat-label">Laporan</span>
                </div>
            </div>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav" role="navigation" aria-label="Navigasi sidebar">
            @foreach($sidebarMenus as $key => $menu)
                @if(in_array($userRole, $menu['roles']))
                    <div class="nav-section">
                        <div class="nav-section-header">
                            <a href="{{ route($menu['route']) }}" 
                               class="nav-section-title {{ $currentModule === $key ? 'active' : '' }}"
                               aria-expanded="{{ $currentModule === $key ? 'true' : 'false' }}"
                               data-toggle="collapse"
                               data-target="#{{ $key }}Menu">
                                <i class="{{ $menu['icon'] }}"></i>
                                <span>{{ $menu['title'] }}</span>
                                <i class="fas fa-chevron-down nav-arrow"></i>
                            </a>
                        </div>
                        
                        <div class="nav-section-menu collapse {{ $currentModule === $key ? 'show' : '' }}" 
                             id="{{ $key }}Menu">
                            <ul class="nav-list">
                                @foreach($menu['items'] as $item)
                                    @if(in_array($userRole, $item['roles']))
                                        <li class="nav-item">
                                            <a href="{{ route($item['route']) }}" 
                                               class="nav-link {{ $currentRoute === $item['route'] ? 'active' : '' }}"
                                               aria-current="{{ $currentRoute === $item['route'] ? 'page' : '' }}">
                                                <span>{{ $item['title'] }}</span>
                                                @if(isset($item['badge']))
                                                    <span class="nav-badge">{{ $item['badge'] }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-actions">
                <a href="{{ route('help') }}" class="action-link" title="Bantuan">
                    <i class="fas fa-question-circle"></i>
                    <span>Bantuan</span>
                </a>
                <a href="{{ route('feedback') }}" class="action-link" title="Masukan">
                    <i class="fas fa-comment-dots"></i>
                    <span>Masukan</span>
                </a>
                <a href="#" class="action-link" onclick="toggleSidebar()" title="Sembunyikan Sidebar">
                    <i class="fas fa-chevron-left"></i>
                    <span>Sembunyikan</span>
                </a>
            </div>
            
            <div class="sidebar-version">
                <small>SAKIP v1.0.0</small>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Toggle Button (for mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true">
        <i class="fas fa-bars"></i>
    </button>
</aside>

<style>
.sakip-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: white;
    border-right: 1px solid #e5e7eb;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 999;
    transition: transform 0.3s ease;
}

.sidebar-container {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, var(--sakip-primary-light), var(--sakip-primary));
    color: white;
}

.user-info {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.5rem;
}

.user-details {
    flex: 1;
}

.user-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
}

.user-role {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.9;
}

.user-institution {
    display: block;
    margin-top: 0.25rem;
    opacity: 0.8;
}

.quick-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.stat-item {
    text-align: center;
    padding: 0.5rem;
    background: rgba(255,255,255,0.1);
    border-radius: 6px;
}

.stat-value {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    display: block;
    font-size: 0.75rem;
    opacity: 0.9;
    margin-top: 0.25rem;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.nav-section {
    margin-bottom: 0.5rem;
}

.nav-section-header {
    padding: 0 1rem;
}

.nav-section-title {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--sakip-dark);
    text-decoration: none;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    background: none;
    border: none;
    width: 100%;
    text-align: left;
}

.nav-section-title:hover {
    background: #f8fafc;
    color: var(--sakip-primary);
}

.nav-section-title.active {
    background: var(--sakip-primary-light);
    color: var(--sakip-primary);
}

.nav-section-title i:first-child {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

.nav-arrow {
    margin-left: auto;
    transition: transform 0.3s ease;
    font-size: 0.875rem;
}

.nav-section-title[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}

.nav-section-menu {
    padding: 0.5rem 0;
}

.nav-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    margin: 0;
}

.nav-link {
    display: block;
    padding: 0.5rem 1rem 0.5rem 3.5rem;
    color: #6b7280;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    border-radius: 4px;
    margin: 0 1rem;
}

.nav-link:hover {
    color: var(--sakip-primary);
    background: #f8fafc;
}

.nav-link.active {
    color: var(--sakip-primary);
    background: var(--sakip-primary-light);
    font-weight: 500;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--sakip-primary);
}

.nav-badge {
    background: var(--sakip-danger);
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 10px;
    margin-left: auto;
    font-weight: 600;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f8fafc;
}

.sidebar-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.action-link {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    color: #6b7280;
    text-decoration: none;
    font-size: 0.875rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.action-link:hover {
    color: var(--sakip-primary);
    background: white;
}

.action-link i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

.sidebar-version {
    text-align: center;
    color: #9ca3af;
    font-size: 0.75rem;
}

.sidebar-toggle {
    display: none;
    position: fixed;
    top: 50%;
    left: 280px;
    transform: translateY(-50%);
    background: var(--sakip-primary);
    color: white;
    border: none;
    border-radius: 0 6px 6px 0;
    padding: 1rem 0.5rem;
    cursor: pointer;
    z-index: 998;
    transition: left 0.3s ease;
}

.sidebar-toggle:hover {
    background: var(--sakip-primary-dark);
}

/* Collapsed State */
.sakip-sidebar.collapsed {
    transform: translateX(-280px);
}

.sakip-sidebar.collapsed + .sidebar-toggle {
    left: 0;
}

.sakip-sidebar.collapsed + .sidebar-toggle i {
    transform: rotate(180deg);
}

/* Main Content Adjustment */
.main-content {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
}

.main-content.expanded {
    margin-left: 0;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .sakip-sidebar {
        transform: translateX(-280px);
    }
    
    .sakip-sidebar.mobile-open {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: block;
        left: 0;
    }
    
    .main-content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .quick-stats {
        grid-template-columns: 1fr;
    }
    
    .sidebar-header {
        padding: 1rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
    }
    
    .user-avatar {
        margin-right: 0;
        margin-bottom: 0.75rem;
    }
}

/* Print Styles */
@media print {
    .sakip-sidebar {
        display: none;
    }
    
    .main-content {
        margin-left: 0;
    }
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sakipSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    
    function toggleSidebar() {
        if (window.innerWidth <= 1024) {
            // Mobile behavior
            sidebar.classList.toggle('mobile-open');
        } else {
            // Desktop behavior
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('expanded');
            }
            
            // Update toggle button icon
            const icon = sidebarToggle.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'fas fa-chevron-right';
            } else {
                icon.className = 'fas fa-bars';
            }
        }
        
        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Collapse functionality for menu sections
    const sectionTitles = document.querySelectorAll('.nav-section-title');
    
    sectionTitles.forEach(title => {
        title.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target');
            const targetMenu = document.querySelector(targetId);
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Toggle current section
            this.setAttribute('aria-expanded', !isExpanded);
            targetMenu.classList.toggle('show');
            
            // Save expanded state to localStorage
            const sectionKey = 'sidebarSection_' + targetId.replace('#', '');
            localStorage.setItem(sectionKey, !isExpanded);
        });
    });
    
    // Load saved states
    function loadSavedStates() {
        // Load sidebar collapsed state
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && window.innerWidth > 1024) {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
            const icon = sidebarToggle.querySelector('i');
            icon.className = 'fas fa-chevron-right';
        }
        
        // Load section expanded states
        sectionTitles.forEach(title => {
            const targetId = title.getAttribute('data-target');
            const sectionKey = 'sidebarSection_' + targetId.replace('#', '');
            const isSectionExpanded = localStorage.getItem(sectionKey) === 'true';
            
            if (isSectionExpanded) {
                title.setAttribute('aria-expanded', 'true');
                document.querySelector(targetId).classList.add('show');
            }
        });
    }
    
    // Handle window resize
    function handleResize() {
        if (window.innerWidth > 1024) {
            // Desktop view
            sidebar.classList.remove('mobile-open');
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('expanded');
                }
            }
        } else {
            // Mobile view
            sidebar.classList.remove('collapsed');
            if (mainContent) {
                mainContent.classList.remove('expanded');
            }
        }
    }
    
    // Initialize
    loadSavedStates();
    handleResize();
    
    // Add resize listener
    window.addEventListener('resize', handleResize);
    
    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) && 
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
});
</script>