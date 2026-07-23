<?php $__env->startSection('title', 'Tidak Diizinkan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-left-danger mb-4">
                <div class="card-header">
                    <i class="fas fa-ban"></i> Akses Ditolak (403)
                </div>
                <div class="card-body">
                    <p>Maaf, Anda tidak memiliki hak untuk mengakses halaman ini.</p>
                    <?php if(auth()->guard()->check()): ?>
                        <p>Jika Anda merasa ini kesalahan, hubungi administrator untuk mendapatkan akses yang sesuai.</p>
                    <?php else: ?>
                        <p>Silakan <a href="<?php echo e(route('login')); ?>">login</a> terlebih dahulu.</p>
                    <?php endif; ?>
                    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.modern', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/errors/403.blade.php ENDPATH**/ ?>