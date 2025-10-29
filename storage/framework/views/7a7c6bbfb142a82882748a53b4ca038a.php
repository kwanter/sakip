<?php $__env->startSection('title', 'Dashboard SAKIP'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4" data-sakip-dashboard>
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Dashboard SAKIP <?php echo e($currentYear ?? ''); ?></h1>
            <?php if(session('error')): ?>
                <div class="alert alert-danger mt-2"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Indikator</span>
                        <strong><?php echo e($dashboardData['total_indicators'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Data</span>
                        <strong><?php echo e($dashboardData['total_data_points'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Penilaian</span>
                        <strong><?php echo e($dashboardData['total_assessments'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Laporan</span>
                        <strong><?php echo e($dashboardData['total_reports'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Aktivitas Terbaru</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = ($recentActivities ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="list-group-item"><?php echo e($activity['description'] ?? 'Aktivitas'); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-muted">Belum ada aktivitas terbaru.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Peringatan</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = ($alerts ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="list-group-item">
                                <span class="badge bg-<?php echo e($alert['type'] ?? 'info'); ?>">&nbsp;</span>
                                <?php echo e($alert['message'] ?? 'Peringatan'); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-muted">Tidak ada peringatan saat ini.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header">Aksi Cepat</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php $__empty_1 = true; $__currentLoopData = ($quickActions ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <a href="<?php echo e($action['link'] ?? '#'); ?>" class="btn btn-primary btn-sm"><?php echo e($action['label'] ?? 'Aksi'); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted">Tidak ada aksi cepat tersedia.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/sakip/dashboard/index.blade.php ENDPATH**/ ?>