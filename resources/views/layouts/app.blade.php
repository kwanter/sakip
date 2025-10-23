<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;

            /* Light theme variables */
            --bg-color: #f8f9fc;
            --card-bg: #ffffff;
            --text-color: #2c2c2c;
            --secondary-color: #666666;
            --border-color: #e3e6f0;
            --navbar-bg: #ffffff;
            --sidebar-bg: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --card-bg: #2d2d2d;
            --text-color: #e0e0e0;
            --secondary-color: #b0b0b0;
            --border-color: #404040;
            --navbar-bg: #2d2d2d;
            --sidebar-bg: linear-gradient(180deg, #2c3e50 10%, #1a252f 100%);
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: background 0.3s ease;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 0.35rem;
            margin: 0.25rem 0;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1rem;
            text-align: center;
        }

        .navbar {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card {
            background-color: var(--card-bg);
            color: var(--text-color);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: 1px solid var(--border-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }

        .border-left-success {
            border-left: 0.25rem solid var(--success-color) !important;
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color) !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }

        .icon-circle {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .bg-success {
            background-color: var(--success-color) !important;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text-color);
            background-color: var(--card-bg);
        }

        .table td {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .form-control {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .form-control:focus {
            background-color: var(--card-bg);
            color: var(--text-color);
        }

        .dropdown-menu {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        .dropdown-item {
            color: var(--text-color);
        }

        .dropdown-item:hover {
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .theme-toggle {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease;
        }

        .theme-toggle:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        [data-theme="dark"] .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .badge {
            font-size: 0.75em;
        }

        .badge-secondary {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        .badge-info {
            background-color: var(--info-color) !important;
            color: #fff !important;
        }

        .badge-success {
            background-color: var(--success-color) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .badge-secondary {
            background-color: #495057 !important;
            color: #e0e0e0 !important;
        }

        [data-theme="dark"] .badge-info {
            background-color: #17a2b8 !important;
            color: #fff !important;
        }

        [data-theme="dark"] .badge-success {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        .badge-primary {
            background-color: var(--primary-color) !important;
            color: #fff !important;
        }

        .badge-warning {
            background-color: var(--warning-color) !important;
            color: #212529 !important;
        }

        [data-theme="dark"] .badge-primary {
            background-color: #0d6efd !important;
            color: #fff !important;
        }

        [data-theme="dark"] .badge-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .badge-danger {
            background-color: var(--danger-color) !important;
            color: #fff !important;
        }

        [data-theme="dark"] .badge-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .alert {
            border: none;
            border-radius: 0.35rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .page-link {
            color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Tailwind + Bootstrap Compatibility */
        .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }

        /* Ensure Tailwind utilities work with Bootstrap */
        .max-w-7xl {
            max-width: 80rem !important;
        }

        .mx-auto {
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .py-12 {
            padding-top: 3rem !important;
            padding-bottom: 3rem !important;
        }

        .px-6 {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        .sm\:px-6 {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        .lg\:px-8 {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }

        /* Fix grid display */
        .grid {
            display: grid !important;
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        }

        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }

        .gap-6 {
            gap: 1.5rem !important;
        }

        .gap-4 {
            gap: 1rem !important;
        }

        /* Fix flex utilities */
        .flex {
            display: flex !important;
        }

        .items-center {
            align-items: center !important;
        }

        .justify-between {
            justify-content: space-between !important;
        }

        .space-x-3 > * + * {
            margin-left: 0.75rem !important;
        }

        /* Fix text utilities */
        .text-3xl {
            font-size: 1.875rem !important;
            line-height: 2.25rem !important;
        }

        .text-gray-900 {
            color: #111827 !important;
        }

        .text-gray-600 {
            color: #4b5563 !important;
        }

        .font-bold {
            font-weight: 700 !important;
        }

        /* Fix spacing utilities */
        .mb-4 {
            margin-bottom: 1rem !important;
        }

        .mb-6 {
            margin-bottom: 1.5rem !important;
        }

        .mt-2 {
            margin-top: 0.5rem !important;
        }

        /* Fix rounded utilities */
        .rounded-md {
            border-radius: 0.375rem !important;
        }

        .rounded-lg {
            border-radius: 0.5rem !important;
        }

        .sm\:rounded-lg {
            border-radius: 0.5rem !important;
        }

        /* Fix shadow utilities */
        .shadow-sm {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }

        /* Fix overflow utilities */
        .overflow-hidden {
            overflow: hidden !important;
        }

        /* Fix background utilities */
        .bg-white {
            background-color: #ffffff !important;
        }

        .bg-green-100 {
            background-color: #d1fae5 !important;
        }

        .bg-red-100 {
            background-color: #fee2e2 !important;
        }

        /* Fix border utilities */
        .border {
            border-width: 1px !important;
        }

        .border-green-400 {
            border-color: #4ade80 !important;
        }

        .border-red-400 {
            border-color: #f87171 !important;
        }

        .border-transparent {
            border-color: transparent !important;
        }

        /* Fix button utilities */
        .inline-flex {
            display: inline-flex !important;
        }

        .uppercase {
            text-transform: uppercase !important;
        }

        .tracking-widest {
            letter-spacing: 0.1em !important;
        }

        /* Fix responsive padding */
        @media (min-width: 640px) {
            .sm\:inline {
                display: inline !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        @auth
                <nav class="sidebar bg-dark text-white">
                    <ul class="nav flex-column">
                        @can('view-dashboard')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.dashboard') ? 'active' : '' }}" href="{{ route('sakip.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        @endcan

                        <!-- Master Data Menu -->
                        @if(Auth::user()->hasAnyRole(['Super Admin', 'Executive', 'Government Official', 'Assessor']))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.instansi.*') ? 'active' : '' }}" href="{{ route('sakip.instansi.index') }}">
                                <i class="fas fa-building"></i>
                                Instansi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.sasaran-strategis.*') ? 'active' : '' }}" href="{{ route('sakip.sasaran-strategis.index') }}">
                                <i class="fas fa-bullseye"></i>
                                Sasaran Strategis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.program.*') ? 'active' : '' }}" href="{{ route('sakip.program.index') }}">
                                <i class="fas fa-tasks"></i>
                                Program
                            </a>
                        </li>
                        @endif

                        @can('manage-sakip')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.indicators.*') ? 'active' : '' }}" href="{{ route('sakip.indicators.index') }}">
                                <i class="fas fa-chart-line"></i>
                                Indikator Kinerja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.performance-data.*') ? 'active' : '' }}" href="{{ route('sakip.performance-data.index') }}">
                                <i class="fas fa-database"></i>
                                Pengumpulan Data
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.assessments.*') ? 'active' : '' }}" href="{{ route('sakip.assessments.index') }}">
                                <i class="fas fa-clipboard-check"></i>
                                Penilaian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.reports.*') ? 'active' : '' }}" href="{{ route('sakip.reports.index') }}">
                                <i class="fas fa-file-alt"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sakip.audit.*') ? 'active' : '' }}" href="{{ route('sakip.audit.index') }}">
                                <i class="fas fa-history"></i>
                                Audit Trail
                            </a>
                        </li>
                        @endcan

                        <hr class="my-3" style="border-color: rgba(255, 255, 255, 0.2);">

                        <!-- Admin Menu -->
                        @can('manage-users')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-user-shield"></i>
                                Admin Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users-cog"></i>
                                User Management
                            </a>
                        </li>
                        @endcan
                        @can('manage-roles')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                                <i class="fas fa-user-tag"></i>
                                Role Management
                            </a>
                        </li>
                        @endcan
                        @can('manage-permissions')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}">
                                <i class="fas fa-key"></i>
                                Permission Management
                            </a>
                        </li>
                        @endcan
                        @can('manage-settings')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                                <i class="fas fa-history"></i>
                                Audit Logs
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cogs"></i>
                                System Settings
                            </a>
                        </li>
                        @endcan
                    </ul>
                </nav>
        @endauth

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light border-bottom" style="background-color: var(--navbar-bg);">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item me-3">
                                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode">
                                    <i class="fas fa-moon" id="theme-icon"></i>
                                </button>
                            </li>
                            @auth
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" style="color: var(--text-color);">
                                    <i class="fas fa-user-circle"></i>
                                    {{ Auth::user()->name }}
                                    @foreach(Auth::user()->roles as $role)
                                        <span class="badge badge-secondary ms-1">{{ $role->display_name ?? $role->name }}</span>
                                    @endforeach
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="px-3 py-2">
                                        <div class="small text-muted">{{ Auth::user()->email }}</div>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            @endauth
                            @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}" style="color: var(--text-color);">
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
    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Update icon
            const themeIcon = document.getElementById('theme-icon');
            if (newTheme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        }

        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            // Update icon based on current theme
            const themeIcon = document.getElementById('theme-icon');
            if (savedTheme === 'dark') {
                themeIcon.className = 'fas fa-sun';
            } else {
                themeIcon.className = 'fas fa-moon';
            }
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Confirm delete actions
        $('.btn-delete').on('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    </script>
    <script src="{{ asset('js/helpers.js') }}"></script>

    @auth
    @stack('scripts')
    @endauth
</body>
</html>
