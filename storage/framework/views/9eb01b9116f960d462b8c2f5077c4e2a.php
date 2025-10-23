<?php $__env->startSection('title', 'Detail Izin: ' . $permission->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key"></i> Detail Izin: <?php echo e($permission->name); ?>

        </h1>
        <div>
            <a href="<?php echo e(route('admin.permissions.edit', $permission)); ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Permission Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi Izin
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="font-weight-bold">Nama:</td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo e($permission->name); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Guard:</td>
                            <td><?php echo e($permission->guard_name ?? 'web'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">ID:</td>
                            <td><small><?php echo e($permission->id); ?></small></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Role:</td>
                            <td>
                                <span class="badge badge-warning">
                                    <?php echo e($permission->roles()->count()); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pengguna:</td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo e($permission->users()->count()); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td><?php echo e($permission->created_at?->format('d M Y H:i') ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui:</td>
                            <td><?php echo e($permission->updated_at?->format('d M Y H:i') ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role and User Lists -->
        <div class="col-lg-8">
            <!-- Roles Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shield-alt"></i> Role yang Memiliki Izin Ini (<?php echo e($permission->roles()->count()); ?>)
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom">Nama Role</th>
                                <th class="border-bottom text-center" style="width: 100px;">Pengguna</th>
                                <th class="border-bottom text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $permission->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary"><?php echo e($role->name); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-info"><?php echo e($role->users()->count()); ?></span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="<?php echo e(route('admin.roles.show', $role)); ?>"
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Izin ini belum ditambahkan ke role manapun
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users with this Permission -->
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-users"></i> Pengguna dengan Izin Ini (<?php echo e($permission->users()->count()); ?>)
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom">Nama Pengguna</th>
                                <th class="border-bottom">Email</th>
                                <th class="border-bottom text-center" style="width: 100px;">Role</th>
                                <th class="border-bottom text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $permission->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle"><?php echo e($user->name); ?></td>
                                    <td class="align-middle"><?php echo e($user->email); ?></td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-secondary">
                                            <?php echo e($user->roles()->count()); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="<?php echo e(route('admin.users.show', $user)); ?>"
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Tidak ada pengguna dengan izin ini
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/permissions/show.blade.php ENDPATH**/ ?>