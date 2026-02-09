<?php $__env->startSection('title', 'Daftar Instansi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Daftar Instansi</h1>
                <p class="page-header-subtitle">Kelola data instansi pemerintahan</p>
            </div>
            <div class="page-header-actions">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Instansi::class)): ?>
                <a href="<?php echo e(route('sakip.instansi.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Instansi</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('sakip.instansi.index')); ?>" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, kode, atau kepala instansi..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?php echo e(request('status') == 'aktif' ? 'selected' : ''); ?>>Aktif</option>
                        <option value="nonaktif" <?php echo e(request('status') == 'nonaktif' ? 'selected' : ''); ?>>Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-search"></i>
                        <span class="ms-1">Cari</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-body">
            <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle alert-icon"></i>
                <span><?php echo e(session('success')); ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Instansi</th>
                            <th>Kepala Instansi</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $instansis ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $instansi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($instansis->firstItem() + $key); ?></td>
                            <td><span class="badge bg-light text-dark"><?php echo e($instansi->kode_instansi); ?></span></td>
                            <td><strong><?php echo e($instansi->nama_instansi); ?></strong></td>
                            <td><?php echo e($instansi->kepala_instansi ?? '-'); ?></td>
                            <td><?php echo e($instansi->telepon ?? '-'); ?></td>
                            <td>
                                <?php if($instansi->status == 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('sakip.instansi.show', $instansi)); ?>" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $instansi)): ?>
                                    <a href="<?php echo e(route('sakip.instansi.edit', $instansi)); ?>" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $instansi)): ?>
                                    <form action="<?php echo e(route('sakip.instansi.destroy', $instansi)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus instansi ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-building text-muted"></i>
                                    <p class="mb-0">Tidak ada data instansi</p>
                                    <small class="text-muted">Silakan tambahkan data instansi baru</small>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(isset($instansis) && $instansis->hasPages()): ?>
            <div class="mt-3">
                <?php echo e($instansis->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.modern', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/sakip/instansi/index.blade.php ENDPATH**/ ?>