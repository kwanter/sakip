<?php $__env->startSection('title', 'Detail Instansi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building"></i> Detail Instansi
        </h1>
        <div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $instansi)): ?>
            <a href="<?php echo e(route('sakip.instansi.edit', $instansi)); ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('sakip.instansi.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Instansi -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Instansi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Kode Instansi</th>
                            <td>: <?php echo e($instansi->kode_instansi); ?></td>
                        </tr>
                        <tr>
                            <th>Nama Instansi</th>
                            <td>: <?php echo e($instansi->nama_instansi); ?></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td>: <?php echo e($instansi->alamat ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Telepon</th>
                            <td>: <?php echo e($instansi->telepon ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: <?php echo e($instansi->email ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Website</th>
                            <td>:
                                <?php if($instansi->website): ?>
                                    <a href="<?php echo e($instansi->website); ?>" target="_blank"><?php echo e($instansi->website); ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Kepala Instansi</th>
                            <td>: <?php echo e($instansi->kepala_instansi ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>NIP Kepala</th>
                            <td>: <?php echo e($instansi->nip_kepala ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                <?php if($instansi->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>: <?php echo e($instansi->created_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                        <tr>
                            <th>Diupdate Pada</th>
                            <td>: <?php echo e($instansi->updated_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Sasaran Strategis</small>
                        <h4><?php echo e($instansi->sasaranStrategis->count()); ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Program</small>
                        <h4><?php echo e($instansi->programs->count()); ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Indikator Kinerja</small>
                        <h4><?php echo e($instansi->performanceIndicators->count()); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sasaran Strategis Terkait -->
    <?php if($instansi->sasaranStrategis->count() > 0): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sasaran Strategis Terkait</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Strategis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $instansi->sasaranStrategis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $sasaran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key + 1); ?></td>
                            <td><?php echo e($sasaran->kode_sasaran_strategis); ?></td>
                            <td><?php echo e($sasaran->nama_strategis); ?></td>
                            <td>
                                <?php if($sasaran->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('sakip.sasaran-strategis.show', $sasaran)); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Program Terkait -->
    <?php if($instansi->programs->count() > 0): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Program Terkait</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Program</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $instansi->programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key + 1); ?></td>
                            <td><?php echo e($program->kode_program); ?></td>
                            <td><?php echo e($program->nama_program); ?></td>
                            <td><?php echo e($program->tahun); ?></td>
                            <td>
                                <?php if($program->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif($program->status == 'selesai'): ?>
                                    <span class="badge badge-info">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('sakip.program.show', $program)); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/sakip/instansi/show.blade.php ENDPATH**/ ?>