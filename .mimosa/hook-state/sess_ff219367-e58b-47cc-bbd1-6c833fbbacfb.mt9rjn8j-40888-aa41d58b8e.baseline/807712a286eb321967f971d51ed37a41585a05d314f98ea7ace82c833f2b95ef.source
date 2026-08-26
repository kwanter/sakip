<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Theme: set immediately to prevent flash -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <title><?php echo $__env->yieldContent('title', 'SAKIP'); ?> - Sistem Akuntabilitas Kinerja</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Modern Design System (extends Bootstrap) -->
    <link rel="stylesheet" href="<?php echo e(asset('css/modern-sakip.css')); ?>">
</head>
<body>
    <div class="app-container">
        <?php if(auth()->guard()->check()): ?>
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Modern Sidebar -->
        <aside class="modern-sidebar" id="sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <span class="sidebar-logo-text">SAKIP</span>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view-dashboard')): ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Utama</div>
                    <a href="<?php echo e(route('sakip.dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.dashboard') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-home"></i></span>
                        <span class="sidebar-link-text">Dashboard</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if(Auth::user()->hasAnyRole(['Super Admin', 'Executive', 'Government Official', 'Assessor'])): ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Data Master</div>
                    <a href="<?php echo e(route('sakip.instansi.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.instansi.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-building"></i></span>
                        <span class="sidebar-link-text">Instansi</span>
                    </a>
                    <a href="<?php echo e(route('sakip.sasaran-strategis.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.sasaran-strategis.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="sidebar-link-text">Sasaran Strategis</span>
                    </a>
                    <a href="<?php echo e(route('sakip.program.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.program.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="sidebar-link-text">Program</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-sakip')): ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Manajemen Kinerja</div>
                    <a href="<?php echo e(route('sakip.indicators.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.indicators.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="sidebar-link-text">Indikator Kinerja</span>
                    </a>
                    <a href="<?php echo e(route('sakip.data-collection.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.data-collection.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-database"></i></span>
                        <span class="sidebar-link-text">Pengumpulan Data</span>
                        <?php if(isset($pendingDataCount) && $pendingDataCount > 0): ?>
                        <span class="sidebar-link-badge"><?php echo e($pendingDataCount); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if(request()->routeIs('sakip.indicators.show')): ?>
                    <a href="<?php echo e(route('sakip.targets.index', request()->route('indicator'))); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.targets.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-bullseye"></i></span>
                        <span class="sidebar-link-text">Target Kinerja</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('sakip.assessments.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.assessments.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="sidebar-link-text">Penilaian</span>
                    </a>
                    <a href="<?php echo e(route('sakip.reports.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.reports.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-file-alt"></i></span>
                        <span class="sidebar-link-text">Laporan</span>
                    </a>
                    <a href="<?php echo e(route('sakip.audit.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('sakip.audit.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-history"></i></span>
                        <span class="sidebar-link-text">Audit Trail</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-users')): ?>
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Administrasi</div>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-tachometer-alt"></i></span>
                        <span class="sidebar-link-text">Admin Panel</span>
                    </a>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-users"></i></span>
                        <span class="sidebar-link-text">Pengguna</span>
                    </a>
                    <a href="<?php echo e(route('admin.roles.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-user-tag"></i></span>
                        <span class="sidebar-link-text">Role</span>
                    </a>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-permissions')): ?>
                    <a href="<?php echo e(route('admin.permissions.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.permissions.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-key"></i></span>
                        <span class="sidebar-link-text">Permission</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.audit-logs')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.audit-logs') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="sidebar-link-text">Audit Log</span>
                    </a>
                    <a href="<?php echo e(route('admin.settings.index')); ?>" class="sidebar-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
                        <span class="sidebar-link-icon"><i class="fas fa-cog"></i></span>
                        <span class="sidebar-link-text">Pengaturan</span>
                    </a>
                </div>
                <?php endif; ?>
            </nav>

            <!-- Footer -->
            <div class="sidebar-footer">
                <button class="sidebar-toggle-btn" id="sidebarCollapseBtn">
                    <i class="fas fa-chevron-left"></i>
                    <span class="btn-text">Ciutkan</span>
                </button>
            </div>
        </aside>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="modern-main" id="mainContent">
            <?php if(auth()->guard()->check()): ?>
            <!-- Header -->
            <header class="modern-header">
                <div class="header-left">
                    <button class="header-trigger" id="mobileMenuBtn" title="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="header-title"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h1>
                </div>

                <div class="header-right">
                    <!-- Search -->
                    <div class="header-search">
                        <i class="fas fa-search header-search-icon"></i>
                        <input type="text" class="header-search-input" placeholder="Cari indikator, laporan...">
                    </div>

                    <!-- Actions -->
                    <div class="header-actions">
                        <button class="header-action-btn" id="themeToggle" title="Tema" data-onclick="toggleTheme()">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                        <button class="header-action-btn" title="Notifikasi">
                            <i class="fas fa-bell"></i>
                            <span class="notification-dot"></span>
                        </button>
                        <button class="user-menu-trigger" id="userMenuBtn" data-onclick="const dd=document.getElementById('userDropdown'); dd.classList.toggle('show'); console.log('Dropdown clicked via data-onclick', dd);">
                            <div class="user-avatar"><?php echo e(substr(Auth::user()->name, 0, 2)); ?></div>
                            <div class="user-info">
                                <div class="user-name"><?php echo e(Auth::user()->name); ?></div>
                                <div class="user-role"><?php echo e(Auth::user()->roles->first()->display_name ?? Auth::user()->roles->first()->name ?? 'User'); ?></div>
                            </div>
                            <i class="fas fa-chevron-down" style="font-size: 0.75rem; color: var(--text-tertiary);"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- User Dropdown Menu -->
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <div class="user-avatar large"><?php echo e(substr(Auth::user()->name, 0, 2)); ?></div>
                    <div class="user-dropdown-info">
                        <div class="user-dropdown-name"><?php echo e(Auth::user()->name); ?></div>
                        <div class="user-dropdown-email"><?php echo e(Auth::user()->email); ?></div>
                    </div>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="<?php echo e(route('profile.show')); ?>" class="user-dropdown-item">
                    <i class="fas fa-user"></i>
                    Profil Saya
                </a>
                <a href="<?php echo e(route('settings.account')); ?>" class="user-dropdown-item">
                    <i class="fas fa-cog"></i>
                    Pengaturan Akun
                </a>
                <div class="user-dropdown-divider"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="user-dropdown-item user-dropdown-item-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Keluar
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="content-wrapper">
                <!-- Flash Messages -->
                <?php if(session('success')): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo e(session('success')); ?></div>
                    </div>
                    <button class="alert-close" data-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo e(session('error')); ?></div>
                    </div>
                    <button class="alert-close" data-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>

                <?php if(session('warning')): ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo e(session('warning')); ?></div>
                    </div>
                    <button class="alert-close" data-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-title">Terjadi Kesalahan</div>
                        <div class="alert-message">
                            <ul class="list-unstyled mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                    <button class="alert-close" data-dismiss="alert">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>

                <?php if(auth()->guard()->check()): ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php else: ?>
                    <?php if(request()->routeIs('login')): ?>
                        <?php echo $__env->yieldContent('content'); ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="modern-card" style="max-width: 400px; margin: 0 auto; padding: 2rem;">
                                <div class="sidebar-logo-icon" style="margin: 0 auto 1rem; width: 56px; height: 56px; font-size: 1.5rem;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h2 class="font-bold text-lg mb-2">Selamat Datang di SAKIP</h2>
                                <p class="text-secondary mb-4">Sistem Akuntabilitas Kinerja Instansi Pemerintah</p>
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Masuk
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom Scripts -->
    <script src="<?php echo e(asset('js/custom-scripts.js')); ?>"></script>

    <!-- Modern Layout UI Script -->
    <script>
    (function() {
        'use strict';

        // Initialize when DOM is ready
        function initApp() {
            console.log('[Modern UI] Initializing...');
            initSidebar();
            initThemeToggle();
            initUserDropdown();
            initAlerts();
        }

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApp);
        } else {
            // DOM is already ready
            initApp();
        }

        // ============================
        // SIDEBAR FUNCTIONALITY
        // ============================
        function initSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Desktop collapse
            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');

                    const icon = sidebarCollapseBtn.querySelector('i');
                    const text = sidebarCollapseBtn.querySelector('.btn-text');

                    if (sidebar.classList.contains('collapsed')) {
                        if (icon) {
                            icon.classList.remove('fa-chevron-left');
                            icon.classList.add('fa-chevron-right');
                        }
                        if (text) text.textContent = 'Kembangkan';
                    } else {
                        if (icon) {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-left');
                        }
                        if (text) text.textContent = 'Ciutkan';
                    }
                });
            }

            // Mobile menu
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.add('mobile-open');
                    sidebarOverlay.classList.add('show');
                });
            }

            // Close sidebar on overlay click
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('show');
                });
            }
        }

        // ============================
        // THEME TOGGLE
        // ============================
        function initThemeToggle() {
            const html = document.documentElement;

            // Load saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }

        // Global toggleTheme function for data-onclick handler
        window.toggleTheme = function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);

            console.log('[Modern UI] Theme toggled to:', newTheme);
        };

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        // ============================
        // USER DROPDOWN
        // ============================
        function initUserDropdown() {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');

            console.log('[Modern UI] initUserDropdown - userMenuBtn:', userMenuBtn, 'userDropdown:', userDropdown);

            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('[Modern UI] User menu button clicked');

                    const isOpen = userDropdown.classList.contains('show');
                    console.log('[Modern UI] Dropdown open state:', isOpen);

                    // Toggle this dropdown
                    userDropdown.classList.toggle('show', !isOpen);
                });

                // Close on outside click (but not when clicking the trigger button)
                document.addEventListener('click', function(e) {
                    if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });

                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && userDropdown.classList.contains('show')) {
                        userDropdown.classList.remove('show');
                    }
                });

                console.log('[Modern UI] User dropdown initialized successfully');
            } else {
                console.error('[Modern UI] User dropdown elements not found!', { userMenuBtn, userDropdown });
            }
        }

        // ============================
        // ALERT DISMISS
        // ============================
        function initAlerts() {
            document.querySelectorAll('[data-dismiss="alert"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const alert = this.closest('.alert');
                    if (alert) {
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 300);
                    }
                });
            });
        }
    })();
    </script>
</body>
</html>
<?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/layouts/modern.blade.php ENDPATH**/ ?>