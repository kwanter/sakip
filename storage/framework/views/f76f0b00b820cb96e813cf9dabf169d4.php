<?php $__env->startSection('title', 'Detail Program'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks"></i> Detail Program
        </h1>
        <div>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $program)): ?>
            <a href="<?php echo e(route('sakip.program.edit', $program)); ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo e(route('sakip.program.index')); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Program -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Program</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="250">Kode Program</th>
                            <td>: <?php echo e($program->kode_program); ?></td>
                        </tr>
                        <tr>
                            <th>Nama Program</th>
                            <td>: <?php echo e($program->nama_program); ?></td>
                        </tr>
                        <tr>
                            <th>Instansi</th>
                            <td>:
                                <a href="<?php echo e(route('sakip.instansi.show', $program->instansi)); ?>">
                                    <?php echo e($program->instansi->nama_instansi); ?>

                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Sasaran Strategis</th>
                            <td>:
                                <a href="<?php echo e(route('sakip.sasaran-strategis.show', $program->sasaranStrategis)); ?>">
                                    <?php echo e($program->sasaranStrategis->nama_strategis); ?>

                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>: <?php echo e($program->deskripsi ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Anggaran</th>
                            <td>: <strong>Rp <?php echo e(number_format($program->anggaran, 0, ',', '.')); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Tahun</th>
                            <td>: <?php echo e($program->tahun); ?></td>
                        </tr>
                        <tr>
                            <th>Penanggung Jawab</th>
                            <td>: <?php echo e($program->penanggung_jawab ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>:
                                <?php if($program->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif($program->status == 'selesai'): ?>
                                    <span class="badge badge-info">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat Pada</th>
                            <td>: <?php echo e($program->created_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                        <tr>
                            <th>Diupdate Pada</th>
                            <td>: <?php echo e($program->updated_at->format('d/m/Y H:i')); ?></td>
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
                        <small class="text-muted">Total Kegiatan</small>
                        <h4><?php echo e($program->kegiatans->count()); ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Kegiatan Aktif</small>
                        <h4><?php echo e($program->kegiatans->where('status', 'aktif')->count()); ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Indikator Kinerja</small>
                        <h4><?php echo e($program->performanceIndicators?->count() ?? 0); ?></h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Realisasi Anggaran</small>
                        <h4>
                            <?php
                                $realisasi = $program->kegiatans->sum('anggaran_realisasi');
                                $persentase = $program->anggaran > 0 ? ($realisasi / $program->anggaran) * 100 : 0;
                            ?>
                            <?php echo e(number_format($persentase, 1)); ?>%
                        </h4>
                        <small class="text-muted">Rp <?php echo e(number_format($realisasi, 0, ',', '.')); ?></small>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Progress</h6>
                </div>
                <div class="card-body">
                    <?php
                        $totalKegiatan = $program->kegiatans->count();
                        $kegiatanSelesai = $program->kegiatans->where('status', 'selesai')->count();
                        $progressPersentase = $totalKegiatan > 0 ? ($kegiatanSelesai / $totalKegiatan) * 100 : 0;
                    ?>
                    <div class="mb-2">
                        <small>Kegiatan Selesai: <?php echo e($kegiatanSelesai); ?>/<?php echo e($totalKegiatan); ?></small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?php echo e($progressPersentase >= 75 ? 'bg-success' : ($progressPersentase >= 50 ? 'bg-info' : 'bg-warning')); ?>"
                             role="progressbar" style="width: <?php echo e($progressPersentase); ?>%"
                             aria-valuenow="<?php echo e($progressPersentase); ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo e(number_format($progressPersentase, 0)); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kegiatan Terkait -->
    <?php if($program->kegiatans->count() > 0): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Kegiatan Terkait</h6>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Kegiatan::class)): ?>
            <a href="<?php echo e(route('sakip.kegiatan.create', ['program_id' => $program->id])); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Kegiatan</th>
                            <th>Nama Kegiatan</th>
                            <th>Anggaran</th>
                            <th>Realisasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $program->kegiatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $kegiatan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key + 1); ?></td>
                            <td><?php echo e($kegiatan->kode_kegiatan); ?></td>
                            <td><?php echo e($kegiatan->nama_kegiatan); ?></td>
                            <td>Rp <?php echo e(number_format($kegiatan->anggaran, 0, ',', '.')); ?></td>
                            <td>Rp <?php echo e(number_format($kegiatan->anggaran_realisasi ?? 0, 0, ',', '.')); ?></td>
                            <td>
                                <?php if($kegiatan->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif($kegiatan->status == 'selesai'): ?>
                                    <span class="badge badge-info">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('sakip.kegiatan.show', $kegiatan)); ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total:</th>
                            <th>Rp <?php echo e(number_format($program->kegiatans->sum('anggaran'), 0, ',', '.')); ?></th>
                            <th>Rp <?php echo e(number_format($program->kegiatans->sum('anggaran_realisasi'), 0, ',', '.')); ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Kegiatan Terkait</h6>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Kegiatan::class)): ?>
            <a href="<?php echo e(route('sakip.kegiatan.create', ['program_id' => $program->id])); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kegiatan
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <p class="text-center text-muted mb-0">Belum ada kegiatan terkait dengan program ini.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Indikator Kinerja Terkait -->
    <?php if(($program->performanceIndicators?->count() ?? 0) > 0): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Indikator Kinerja Program</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Indikator</th>
                            <th>Satuan</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $program->performanceIndicators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key + 1); ?></td>
                            <td><?php echo e($indicator->nama_indikator); ?></td>
                            <td><?php echo e($indicator->satuan); ?></td>
                            <td><?php echo e($indicator->targets->first()->target ?? '-'); ?></td>
                            <td>
                                <?php if($indicator->status == 'aktif'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('sakip.indicators.show', $indicator)); ?>" class="btn btn-info btn-sm">
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/sakip/program/show.blade.php ENDPATH**/ ?>