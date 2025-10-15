@php
    $user = auth()->user();
    $userRole = $user->role ?? 'guest';
    $userInstitution = $user->institution ?? null;
    
    // Navigation items based on role
    $navItems = [
        'dashboard' => [
            'title' => 'Dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'route' => 'sakip.dashboard',
            'roles' => ['superadmin', 'admin', 'executive', 'data_collector', 'assessor', 'auditor', 'government_agency']
        ],
        'indicators' => [
            'title' => 'Indikator Kinerja',
            'icon' => 'fas fa-chart-line',
            'route' => 'sakip.indicators.index',
            'roles' => ['superadmin', 'admin', 'executive', 'data_collector', 'assessor', 'government_agency']
        ],
        'data-collection' => [
            'title' => 'Pengumpulan Data',
            'icon' => 'fas fa-database',
            'route' => 'sakip.data-collection.index',
            'roles' => ['superadmin', 'admin', 'data_collector', 'government_agency']
        ],
        'assessments' => [
            'title' => 'Penilaian',
            'icon' => 'fas fa-clipboard-check',
            'route' => 'sakip.assessments.index',
            'roles' => ['superadmin', 'admin', 'assessor', 'executive']
        ],
        'reports' => [
            'title' => 'Laporan',
            'icon' => 'fas fa-file-alt',
            'route' => 'sakip.reports.index',
            'roles' => ['superadmin', 'admin', 'executive', 'auditor', 'government_agency']
        ],
        'audit' => [
            'title' => 'Audit Trail',
            'icon' => 'fas fa-history',
            'route' => 'sakip.audit.index',
            'roles' => ['superadmin', 'admin', 'auditor']
        ],
        'settings' => [
            'title' => 'Pengaturan',
            'icon' => 'fas fa-cog',
            'route' => 'sakip.settings',
            'roles' => ['superadmin', 'admin']
        ]
    ];
    
    $currentRoute = Route::currentRouteName();
@endphp

<nav class="sakip-navigation" role="navigation" aria-label="Navigasi SAKIP">
    <div class="nav-container">
        <!-- Logo Section -->
        <div class="nav-brand">
            <a href="{{ route('sakip.dashboard') }}" class="brand-link">
                <div class="brand-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <div class="brand-text">
                    <span class="brand-title">SAKIP</span>
                    <span class="brand-subtitle">Sistem Akuntabilitas Kinerja</span>
                </div>
            </a>
        </div>

        <!-- Mobile Toggle Button -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
            <span class="toggle-bar"></span>
        </button>

        <!-- Navigation Menu -->
        <div class="nav-menu" id="navMenu">
            <!-- Primary Navigation -->
            <ul class="nav-list nav-primary">
                @foreach($navItems as $key => $item)
                    @if(in_array($userRole, $item['roles']))
                        <li class="nav-item">
                            <a href="{{ route($item['route']) }}" 
                               class="nav-link {{ Str::startsWith($currentRoute, $key) ? 'active' : '' }}"
                               aria-current="{{ Str::startsWith($currentRoute, $key) ? 'page' : '' }}">
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['title'] }}</span>
                                @if(isset($item['badge']))
                                    <span class="nav-badge">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>

            <!-- Secondary Navigation -->
            <ul class="nav-list nav-secondary">
                <!-- Quick Actions -->
                <li class="nav-item dropdown" id="quickActionsDropdown">
                    <button class="nav-link dropdown-toggle" 
                            data-dropdown="quickActions"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <i class="fas fa-bolt"></i>
                        <span>Aksi Cepat</span>
                    </button>
                    <div class="dropdown-menu" id="quickActions" role="menu">
                        @can('create', App\Models\Sakip\PerformanceIndicator::class)
                            <a href="{{ route('sakip.indicators.create') }}" class="dropdown-item">
                                <i class="fas fa-plus"></i>
                                Tambah Indikator
                            </a>
                        @endcan
                        @can('create', App\Models\Sakip\PerformanceData::class)
                            <a href="{{ route('sakip.data-collection.create') }}" class="dropdown-item">
                                <i class="fas fa-upload"></i>
                                Unggah Data
                            </a>
                        @endcan
                        @can('create', App\Models\Sakip\Assessment::class)
                            <a href="{{ route('sakip.assessments.create') }}" class="dropdown-item">
                                <i class="fas fa-star"></i>
                                Buat Penilaian
                            </a>
                        @endcan
                        @can('generate', App\Models\Sakip\Report::class)
                            <a href="{{ route('sakip.reports.generate') }}" class="dropdown-item">
                                <i class="fas fa-file-export"></i>
                                Generate Laporan
                            </a>
                        @endcan
                    </div>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown" id="notificationsDropdown">
                    <button class="nav-link dropdown-toggle notification-toggle"
                            data-dropdown="notifications"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count" id="notificationCount" style="display: none;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" id="notifications" role="menu">
                        <div class="dropdown-header">
                            <h6>Notifikasi</h6>
                            <a href="#" class="mark-all-read">Tandai semua sudah dibaca</a>
                        </div>
                        <div class="dropdown-body" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>Tidak ada notifikasi baru</p>
                            </div>
                        </div>
                        <div class="dropdown-footer">
                            <a href="#" class="view-all-notifications">Lihat semua</a>
                        </div>
                    </div>
                </li>

                <!-- User Menu -->
                <li class="nav-item dropdown" id="userDropdown">
                    <button class="nav-link dropdown-toggle user-toggle"
                            data-dropdown="userMenu"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ $user->name ?? 'User' }}</span>
                            <span class="user-role">{{ ucfirst(str_replace('_', ' ', $userRole)) }}</span>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" id="userMenu" role="menu">
                        <div class="dropdown-header">
                            <h6>{{ $user->name ?? 'User' }}</h6>
                            <p>{{ $user->email ?? '' }}</p>
                            @if($userInstitution)
                                <small>{{ $userInstitution->name ?? '' }}</small>
                            @endif
                        </div>
                        <div class="dropdown-body">
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Profil Saya
                            </a>
                            <a href="{{ route('settings.account') }}" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                Pengaturan Akun
                            </a>
                            @if($userRole === 'government_agency')
                                <a href="{{ route('institution.profile') }}" class="dropdown-item">
                                    <i class="fas fa-building"></i>
                                    Profil Institusi
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                Keluar
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Logout Form -->
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
.sakip-navigation {
    background: var(--sakip-primary);
    border-bottom: 3px solid var(--sakip-secondary);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 70px;
}

.nav-brand {
    flex-shrink: 0;
}

.brand-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: white;
    transition: opacity 0.3s ease;
}

.brand-link:hover {
    opacity: 0.9;
}

.brand-logo {
    font-size: 2rem;
    margin-right: 0.75rem;
    color: var(--sakip-accent);
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-title {
    font-weight: 700;
    font-size: 1.25rem;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 0.75rem;
    opacity: 0.9;
    line-height: 1;
}

.nav-toggle {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.toggle-bar {
    display: block;
    width: 25px;
    height: 3px;
    background: white;
    margin: 5px 0;
    transition: 0.3s;
}

.nav-menu {
    display: flex;
    align-items: center;
    flex: 1;
    justify-content: space-between;
}

.nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
}

.nav-primary {
    flex: 1;
    justify-content: center;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 6px;
    margin: 0 0.25rem;
    position: relative;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

.nav-link.active {
    background: var(--sakip-secondary);
    color: white;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--sakip-accent);
}

.nav-link i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.nav-badge {
    background: var(--sakip-danger);
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 10px;
    margin-left: 0.5rem;
    font-weight: 600;
}

/* Dropdown Styles */
.dropdown-toggle {
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    color: inherit;
}

.dropdown-toggle::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 0.5rem;
    transition: transform 0.3s ease;
}

.dropdown-toggle[aria-expanded="true"]::after {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    min-width: 220px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
}

.dropdown-menu-right {
    right: 0;
    left: auto;
}

.dropdown-toggle[aria-expanded="true"] + .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
    border-radius: 8px 8px 0 0;
}

.dropdown-header h6 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: var(--sakip-dark);
}

.dropdown-header p {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.dropdown-header small {
    display: block;
    margin-top: 0.25rem;
    color: #9ca3af;
}

.dropdown-body {
    max-height: 300px;
    overflow-y: auto;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--sakip-dark);
    text-decoration: none;
    transition: background-color 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: var(--sakip-primary);
}

.dropdown-item i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.dropdown-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f8fafc;
    border-radius: 0 0 8px 8px;
}

/* Notification Styles */
.notification-toggle {
    position: relative;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--sakip-danger);
    color: white;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 10px;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

.notification-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;
}

.notification-empty i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.mark-all-read {
    font-size: 0.75rem;
    color: var(--sakip-primary);
    text-decoration: none;
}

.view-all-notifications {
    color: var(--sakip-primary);
    text-decoration: none;
    font-weight: 500;
}

/* User Menu Styles */
.user-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--sakip-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.user-name {
    font-weight: 600;
    font-size: 0.875rem;
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    opacity: 0.9;
    line-height: 1;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .nav-toggle {
        display: block;
    }
    
    .nav-menu {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background: var(--sakip-primary);
        flex-direction: column;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
    }
    
    .nav-menu.active {
        transform: translateX(0);
    }
    
    .nav-list {
        flex-direction: column;
        width: 100%;
    }
    
    .nav-primary {
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 1rem;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-link {
        justify-content: flex-start;
        margin: 0.25rem 0;
        width: 100%;
    }
    
    .dropdown-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        border: 1px solid rgba(255,255,255,0.1);
        background: rgba(255,255,255,0.05);
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .dropdown-menu-right {
        right: auto;
        left: 0;
    }
    
    .user-toggle {
        justify-content: flex-start;
        width: 100%;
    }
    
    .brand-subtitle {
        display: none;
    }
}

/* Print Styles */
@media print {
    .sakip-navigation {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            navMenu.classList.toggle('active');
        });
    }
    
    // Dropdown functionality
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other dropdowns
            dropdownToggles.forEach(t => {
                if (t !== this) {
                    t.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Toggle current dropdown
            this.setAttribute('aria-expanded', !isExpanded);
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdownToggles.forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdownToggles.forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
            });
        }
    });
    
    // Load notifications (placeholder function)
    function loadNotifications() {
        // This would typically make an AJAX call to fetch notifications
        // For now, we'll just show the empty state
        console.log('Loading notifications...');
    }
    
    // Initialize notifications
    loadNotifications();
});
</script>