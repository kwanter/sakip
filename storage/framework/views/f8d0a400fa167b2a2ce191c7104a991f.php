<?php $__env->startSection('title', 'Profil Saya'); ?>

<?php $__env->startSection('page-title', 'Profil Saya'); ?>

<?php $__env->startSection('content'); ?>
<?php ($user = $user ?? auth()->user()); ?>
<style>
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: var(--radius-xl);
        background: linear-gradient(135deg, var(--primary-400), var(--primary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }

    .info-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        padding: var(--space-lg);
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--space-md) 0;
        border-bottom: 1px solid var(--border-light);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .info-value {
        font-weight: 500;
        color: var(--text-primary);
    }
</style>

<!-- Profile Header -->
<div style="max-width: 800px; margin: 0 auto;">
    <div class="modern-card" style="margin-bottom: var(--space-lg);">
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: var(--space-lg);">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 var(--space-xs) 0; font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">
                        <?php echo e($user->name); ?>

                    </h2>
                    <p style="margin: 0; color: var(--text-secondary);"><?php echo e($user->email); ?></p>
                </div>
                <?php if(Route::has('settings.account')): ?>
                    <a href="<?php echo e(route('settings.account')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i>
                        <span style="margin-left: var(--space-sm);">Pengaturan Akun</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Info Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-lg);">
        <!-- Roles Card -->
        <div class="info-card">
            <div style="display: flex; align-items: center; gap: var(--space-md); margin-bottom: var(--space-md);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-50); display: flex; align-items: center; justify-content: center; color: var(--primary-600);">
                    <i class="fas fa-user-tag"></i>
                </div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Peran</h3>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-sm);">
                <?php ($roles = method_exists($user, 'roles') ? $user->roles->pluck('name')->all() : []); ?>
                <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <span class="badge badge-neutral"><?php echo e(ucfirst(str_replace('_', ' ', $role))); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span style="color: var(--text-tertiary);">Tidak ada peran</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Institution Card -->
        <div class="info-card">
            <div style="display: flex; align-items: center; gap: var(--space-md); margin-bottom: var(--space-md);">
                <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--info-light); display: flex; align-items: center; justify-content: center; color: var(--info);">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Institusi</h3>
            </div>
            <?php ($institution = $user->institution ?? $user->instansi ?? null); ?>
            <?php if($institution): ?>
                <div style="color: var(--text-primary);">
                    <?php echo e($institution->name ?? $institution->nama ?? '—'); ?>

                </div>
                <?php if(Route::has('institution.profile')): ?>
                    <a href="<?php echo e(route('institution.profile')); ?>" style="color: var(--primary-600); font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-xs); margin-top: var(--space-sm);">
                        Lihat profil institusi <i class="fas fa-arrow-right" style="font-size: 0.75rem;"></i>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <div style="color: var(--text-tertiary);">Belum terdaftar pada institusi</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Account Details Card -->
    <div class="modern-card" style="margin-top: var(--space-lg);">
        <div class="card-header">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 600;">Detail Akun</h3>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-value"><?php echo e($user->name); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email</span>
                <span class="info-value"><?php echo e($user->email); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Terdaftar Sejak</span>
                <span class="info-value"><?php echo e($user->created_at->format('d M Y')); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Terakhir Login</span>
                <span class="info-value"><?php echo e(optional($user->last_login_at)->format('d M Y, H:i') ?? '—'); ?></span>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.modern', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sakip/profile/show.blade.php ENDPATH**/ ?>