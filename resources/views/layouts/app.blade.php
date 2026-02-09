<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Theme: set immediately to prevent flash -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <title>@yield('title', 'SAKIP') - Sistem Akuntabilitas Kinerja Instansi Pemerintah</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom-styles.css') }}">

    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        @auth
                <nav class="sidebar bg-dark text-white" id="sidebar">
                    <div class="sidebar-header">
                        <a href="{{ route('sakip.dashboard') }}" class="sidebar-brand">
                            <i class="fas fa-chart-line me-2"></i>
                            <span>SAKIP</span>
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <!-- Desktop Collapse Button -->
                            <button class="sidebar-toggle d-lg-flex" id="sidebarCollapseBtn" title="Toggle Sidebar">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <!-- Mobile Close Button -->
                            <button class="sidebar-toggle d-lg-none" id="sidebarCloseBtn" title="Close Sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <ul class="nav flex-column">
                        @can('view-dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.dashboard') ? 'active' : '' }}" href="{{ route('sakip.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        @endcan

                        <!-- Master Data Menu -->
                        @if(Auth::user()->hasAnyRole(['Super Admin', 'Executive', 'Government Official', 'Assessor']))
                        <div class="sidebar-section-title">Master Data</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.instansi.*') ? 'active' : '' }}" href="{{ route('sakip.instansi.index') }}">
                                <i class="fas fa-building"></i>
                                <span>Instansi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.sasaran-strategis.*') ? 'active' : '' }}" href="{{ route('sakip.sasaran-strategis.index') }}">
                                <i class="fas fa-bullseye"></i>
                                <span>Sasaran Strategis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.program.*') ? 'active' : '' }}" href="{{ route('sakip.program.index') }}">
                                <i class="fas fa-tasks"></i>
                                <span>Program</span>
                            </a>
                        </li>
                        @endif

                        @can('manage-sakip')
                        <div class="sidebar-section-title">Manajemen Kinerja</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.indicators.*') ? 'active' : '' }}" href="{{ route('sakip.indicators.index') }}">
                                <i class="fas fa-chart-line"></i>
                                <span>Indikator Kinerja</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.performance-data.*') ? 'active' : '' }}" href="{{ route('sakip.performance-data.index') }}">
                                <i class="fas fa-database"></i>
                                <span>Pengumpulan Data</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.assessments.*') ? 'active' : '' }}" href="{{ route('sakip.assessments.index') }}">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Penilaian</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.reports.*') ? 'active' : '' }}" href="{{ route('sakip.reports.index') }}">
                                <i class="fas fa-file-alt"></i>
                                <span>Laporan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.audit.*') ? 'active' : '' }}" href="{{ route('sakip.audit.index') }}">
                                <i class="fas fa-history"></i>
                                <span>Audit Trail</span>
                            </a>
                        </li>
                        @endcan

                        <hr class="sidebar-divider">

                        <!-- Admin Menu -->
                        @can('manage-users')
                        <div class="sidebar-section-title">Administrasi</div>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-user-shield"></i>
                                <span>Admin Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users-cog"></i>
                                <span>User Management</span>
                            </a>
                        </li>
                        @endcan
                        @can('manage-roles')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                <i class="fas fa-user-tag"></i>
                                <span>Role Management</span>
                            </a>
                        </li>
                        @endcan
                        @can('manage-permissions')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">
                                <i class="fas fa-key"></i>
                                <span>Permission Management</span>
                            </a>
                        </li>
                        @endcan
                        @can('manage-settings')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                                <i class="fas fa-history"></i>
                                <span>Audit Logs</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cogs"></i>
                                <span>System Settings</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </nav>
        @endauth

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light border-bottom">
                <div class="container-fluid">
                    <!-- Mobile Sidebar Toggle -->
                    <button class="sidebar-toggle-btn d-md-none me-3" id="sidebarToggleBtn" title="Toggle Menu">
                        <i class="fas fa-bars"></i>
                    </button>

                    <button class="navbar-toggler d-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item me-3">
                                <button class="theme-toggle" id="themeToggleBtn" title="Toggle Dark Mode" data-onclick="toggleTheme()">
                                    <i class="fas fa-moon" id="theme-icon"></i>
                                </button>
                            </li>
                            @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-onclick="const dd=document.getElementById('navbarDropdownMenu'); dd.classList.toggle('show'); console.log('App dropdown clicked', dd);">
                                    <i class="fas fa-user-circle"></i>
                                    <span>{{ Auth::user()->name }}</span>
                                    @foreach(Auth::user()->roles as $role)
                                        <span class="badge badge-secondary ms-1">{{ $role->display_name ?? $role->name }}</span>
                                    @endforeach
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" id="navbarDropdownMenu">
                                    <li class="px-3 py-2">
                                        <div class="small fw-semibold">{{ Auth::user()->name }}</div>
                                        <div class="small text-muted">{{ Auth::user()->email }}</div>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('profile.show') }}">
                                            <i class="fas fa-user me-2"></i> Profil Saya
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('settings.account') }}">
                                            <i class="fas fa-cog me-2"></i> Pengaturan Akun
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Keluar</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            @endauth
                            @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @auth
                    @yield('content')
                @else
                    @if (request()->routeIs('login'))
                        @yield('content')
                    @else
                        <div class="container py-5">
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <div class="card shadow-sm">
                                        <div class="card-body text-center">
                                            <h5 class="card-title mb-3">Selamat datang di SAKIP</h5>
                                            <p class="card-text">Silakan masuk untuk mengakses menu dan fitur aplikasi.</p>
                                            <a href="{{ route('login') }}" class="btn btn-primary">
                                                <i class="fas fa-sign-in-alt"></i> Masuk
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom Scripts -->
    <script src="{{ asset('js/custom-scripts.js') }}"></script>
    <script src="{{ asset('js/helpers.js') }}"></script>

    <!-- Dropdown Fallback Script -->
    <script>
    (function() {
        'use strict';

        // Fallback dropdown handler in case Bootstrap doesn't work
        function initDropdownFallback() {
            const dropdownToggle = document.querySelector('[data-bs-toggle="dropdown"]');
            const dropdownMenu = document.querySelector('.dropdown-menu');

            if (dropdownToggle && dropdownMenu) {
                console.log('[App Layout] Initializing dropdown fallback...');

                // Remove default Bootstrap behavior and use custom handler
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('[App Layout] Dropdown toggle clicked');

                    const isOpen = dropdownMenu.classList.contains('show');
                    dropdownMenu.classList.toggle('show', !isOpen);

                    // Update aria attribute
                    dropdownToggle.setAttribute('aria-expanded', !isOpen);
                });

                // Close on outside click
                document.addEventListener('click', function(e) {
                    if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    }
                });

                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    }
                });

                console.log('[App Layout] Dropdown fallback initialized');
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDropdownFallback);
        } else {
            initDropdownFallback();
        }
    })();
    </script>

    @auth
    @stack('scripts')
    @endauth
</body>
</html>
