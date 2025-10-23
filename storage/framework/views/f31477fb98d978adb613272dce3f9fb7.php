<?php $__env->startSection('title', 'Edit Izin: ' . $permission->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Izin: <?php echo e($permission->name); ?>

        </h1>
        <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-key"></i> Informasi Izin
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h6>
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('admin.permissions.update', $permission)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <!-- Permission Name -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label font-weight-bold">
                                Nama Izin <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="name"
                                   name="name"
                                   value="<?php echo e(old('name', $permission->name)); ?>"
                                   required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="form-text text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <small class="form-text text-muted">
                                Gunakan format: <code>action-resource</code>
                            </small>
                        </div>

                        <!-- Guard Name -->
                        <div class="form-group mb-3">
                            <label for="guard_name" class="form-label font-weight-bold">
                                Guard Name
                            </label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['guard_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="guard_name"
                                   name="guard_name"
                                   value="<?php echo e(old('guard_name', $permission->guard_name)); ?>">
                            <?php $__errorArgs = ['guard_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="form-text text-danger"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Perbarui Izin
                            </button>
                            <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info and Danger Zone -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi Izin
                    </h6>
                </div>
                <div class="card-body small">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">ID:</td>
                            <td><?php echo e($permission->id); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Guard:</td>
                            <td><?php echo e($permission->guard_name ?? 'web'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Role:</td>
                            <td>
                                <span class="badge badge-warning">
                                    <?php echo e($permission->roles()->count()); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pengguna:</td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo e($permission->users()->count()); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td><?php echo e($permission->created_at?->format('d M Y') ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui:</td>
                            <td><?php echo e($permission->updated_at?->format('d M Y H:i') ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-link"></i> Terkait Dengan
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="font-weight-bold mb-2">Role yang Menggunakan Izin Ini:</p>
                    <?php $__empty_1 = true; $__currentLoopData = $permission->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('admin.roles.show', $role)); ?>"
                           class="badge badge-warning mr-1 mb-1">
                            <?php echo e($role->name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0">Belum digunakan oleh role manapun</p>
                    <?php endif; ?>

                    <p class="font-weight-bold mb-2 mt-3">Pengguna Langsung:</p>
                    <?php if($permission->users()->count() > 0): ?>
                        <small class="text-muted">
                            <?php echo e($permission->users()->count()); ?> pengguna memiliki izin ini
                        </small>
                    <?php else: ?>
                        <p class="text-muted mb-0">Tidak ada pengguna langsung</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-3">
                        <i class="fas fa-warning"></i> Menghapus izin akan mempengaruhi semua role dan pengguna yang memiliki izin ini.
                    </p>
                    <form action="<?php echo e(route('admin.permissions.destroy', $permission)); ?>" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin ini? Ini akan mempengaruhi ' + <?php echo e($permission->roles()->count()); ?> + ' role dan ' + <?php echo e($permission->users()->count()); ?> + ' pengguna.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Hapus Izin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/permissions/edit.blade.php ENDPATH**/ ?>