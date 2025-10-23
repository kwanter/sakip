<?php $__env->startSection('title', 'Manajemen Role'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shield-alt"></i> Manajemen Role
        </h1>
        <a href="<?php echo e(route('admin.roles.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Role
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

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" action="<?php echo e(route('admin.roles.index')); ?>" class="form-inline">
                <div class="form-group mr-3 mb-2 flex-grow-1">
                    <input type="text" name="search" class="form-control w-100"
                           placeholder="Cari role..."
                           value="<?php echo e(request('search')); ?>">
                </div>
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-search"></i> Cari
                </button>
                <?php if(request('search')): ?>
                    <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-secondary mb-2 ml-2">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Roles Table Card -->
    <div class="card shadow">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Role (<?php echo e($roles->total()); ?> Total)
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom">
                            <strong>Nama Role</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 120px;">
                            <strong>Pengguna</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 120px;">
                            <strong>Izin</strong>
                        </th>
                        <th class="border-bottom text-center" style="width: 150px;">
                            <strong>Aksi</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="align-middle">
                                <div class="mb-0">
                                    <strong><?php echo e($role->name); ?></strong>
                                    <?php if($role->guard_name): ?>
                                        <br>
                                        <small class="text-muted">Guard: <?php echo e($role->guard_name); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info">
                                    <?php echo e($role->users_count ?? 0); ?>

                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-secondary">
                                    <?php echo e($role->permissions_count ?? 0); ?>

                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('admin.roles.show', $role)); ?>"
                                       class="btn btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.roles.edit', $role)); ?>"
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if(!in_array($role->name, ['Super Admin', 'admin', 'super-admin'])): ?>
                                        <form action="<?php echo e(route('admin.roles.destroy', $role)); ?>"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada role ditemukan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-center">
                <?php echo e($roles->links()); ?>

            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi Role
                    </h6>
                </div>
                <div class="card-body small">
                    <p>
                        Role adalah kelompok izin yang dapat diberikan kepada pengguna.
                        Setiap role dapat memiliki satu atau lebih izin yang mendefinisikan
                        akses pengguna terhadap fitur sistem.
                    </p>
                    <ul class="mb-0">
                        <li>Klik tombol "Lihat Detail" untuk melihat izin role</li>
                        <li>Klik tombol "Edit" untuk mengubah role dan izinnya</li>
                        <li>Klik tombol "Hapus" untuk menghapus role (tidak dapat menghapus role default)</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-question-circle"></i> Pertanyaan Umum
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Apa perbedaan Role dan Permission?</strong><br>
                        Role adalah kumpulan permission. Permission adalah aksi spesifik yang dapat dilakukan pengguna.
                    </p>
                    <p class="mb-0">
                        <strong>Bagaimana cara memberikan role kepada pengguna?</strong><br>
                        Kunjungi halaman Manajemen Pengguna, pilih pengguna, lalu assign role di bagian "Role".
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/roles/index.blade.php ENDPATH**/ ?>