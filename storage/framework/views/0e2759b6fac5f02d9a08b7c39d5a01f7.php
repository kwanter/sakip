<?php $__env->startSection('title', 'Manajemen Izin'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key"></i> Manajemen Izin (Permission)
        </h1>
        <a href="<?php echo e(route('admin.permissions.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Izin
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" action="<?php echo e(route('admin.permissions.index')); ?>" class="form-inline">
                <div class="form-group mr-3 mb-2 flex-grow-1">
                    <input type="text" name="search" class="form-control w-100"
                           placeholder="Cari izin..."
                           value="<?php echo e(request('search')); ?>">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if(request('search')): ?>
                    <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Permissions Table Card -->
    <div class="card shadow">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Izin (<?php echo e($permissions->total()); ?> Total)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom">
                            <strong>Nama Izin</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 120px;">
                            <strong>Guard</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 100px;">
                            <strong>Role</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 100px;">
                            <strong>Pengguna</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 150px;">
                            <strong>Aksi</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="align-middle">
                                <div class="mb-0">
                                    <strong><?php echo e($permission->name); ?></strong>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <small class="text-muted"><?php echo e($permission->guard_name ?? 'web'); ?></small>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-warning">
                                    <?php echo e($permission->roles_count ?? 0); ?>

                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info">
                                    <?php echo e($permission->users_count ?? 0); ?>

                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('admin.permissions.show', $permission)); ?>"
                                       class="btn btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.permissions.edit', $permission)); ?>"
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.permissions.destroy', $permission)); ?>"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin ini?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada izin ditemukan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-center">
                <?php echo e($permissions->links()); ?>

            </div>
        </div>
    </div>

    <!-- Info Panels -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Apa itu Izin?
                    </h6>
                </div>
                <div class="card-body small">
                    <p>
                        Izin (Permission) adalah aksi spesifik yang dapat dilakukan pengguna dalam sistem.
                        Izin dikelompokkan menjadi Role, dan Role diberikan kepada pengguna.
                    </p>
                    <p class="mb-0">
                        <strong>Contoh:</strong>
                        <ul class="mb-0">
                            <li><code>create-indicator</code> - Membuat indikator baru</li>
                            <li><code>edit-indicator</code> - Mengedit indikator</li>
                            <li><code>delete-indicator</code> - Menghapus indikator</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-question-circle"></i> Cara Menggunakan
                    </h6>
                </div>
                <div class="card-body small">
                    <ol class="pl-3">
                        <li>Klik "Tambah Izin" untuk membuat izin baru</li>
                        <li>Berikan nama izin yang deskriptif (gunakan format: <code>action-resource</code>)</li>
                        <li>Tambahkan izin ke Role di halaman Manajemen Role</li>
                        <li>Role kemudian diberikan kepada pengguna</li>
                    </ol>
                    <p class="mt-2 mb-0">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Tips:</strong> Gunakan format konsisten seperti <code>view-dashboard</code>,
                        <code>create-indicator</code>, <code>approve-target</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/permissions/index.blade.php ENDPATH**/ ?>