<?php $__env->startSection('title', 'Buat Laporan SAKIP'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="<?php echo e(route('sakip.reports.index')); ?>" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Buat Laporan SAKIP</h1>
            </div>
            <p class="mt-2 text-gray-600">Buat laporan kinerja berdasarkan data yang telah dikumpulkan</p>
        </div>

        <!-- Report Form -->
        <form action="<?php echo e(route('sakip.reports.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Laporan *</label>
                        <input type="text" name="title" id="title" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan judul laporan">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan *</label>
                        <select name="report_type" id="report_type" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Jenis Laporan</option>
                            <option value="monthly">Laporan Bulanan</option>
                            <option value="quarterly">Laporan Triwulan</option>
                            <option value="semester">Laporan Semester</option>
                            <option value="annual">Laporan Tahunan</option>
                            <option value="custom">Laporan Custom</option>
                        </select>
                        <?php $__errorArgs = ['report_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Kategori Laporan *</label>
                        <select name="category" id="category" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <option value="performance">Laporan Kinerja</option>
                            <option value="assessment">Laporan Penilaian</option>
                            <option value="compliance">Laporan Kepatuhan</option>
                            <option value="summary">Laporan Ringkasan</option>
                        </select>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periode *</label>
                        <select name="period" id="period" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Periode</option>
                            <?php $__currentLoopData = $availablePeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availablePeriod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($availablePeriod['value']); ?>"><?php echo e($availablePeriod['label']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['period'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Template Laporan</label>
                        <select name="template_id" id="template_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Template (Opsional)</option>
                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($template->id); ?>"><?php echo e($template->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <!-- Performance Indicators Selection -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Indikator Kinerja *</h3>

                <div class="space-y-4">
                    <p class="text-sm text-gray-600">Pilih indikator kinerja yang akan dimasukkan dalam laporan (minimal 1)</p>

                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md p-4">
                        <?php if($indicators->isEmpty()): ?>
                            <p class="text-sm text-gray-500 italic">Tidak ada indikator kinerja tersedia</p>
                        <?php else: ?>
                            <?php $__currentLoopData = $indicators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start mb-3">
                                    <input type="checkbox" name="indicators[]" value="<?php echo e($indicator->id); ?>" id="indicator_<?php echo e($indicator->id); ?>" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="indicator_<?php echo e($indicator->id); ?>" class="ml-3 text-sm">
                                        <span class="font-medium text-gray-900"><?php echo e($indicator->code); ?></span> -
                                        <span class="text-gray-700"><?php echo e($indicator->name); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>

                    <?php $__errorArgs = ['indicators'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <!-- Report Options -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Opsi Laporan</h3>

                <div class="space-y-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Laporan</label>
                        <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan deskripsi laporan (opsional)"></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="include_assessments" id="include_assessments" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="include_assessments" class="ml-2 text-sm text-gray-700">Sertakan Penilaian</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="include_benchmarks" id="include_benchmarks" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="include_benchmarks" class="ml-2 text-sm text-gray-700">Sertakan Benchmark</label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="include_recommendations" id="include_recommendations" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="include_recommendations" class="ml-2 text-sm text-gray-700">Sertakan Rekomendasi</label>
                        </div>
                    </div>

                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Format Output *</label>
                        <select name="format" id="format" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Format</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="word">Word</option>
                        </select>
                        <?php $__errorArgs = ['format'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <a href="<?php echo e(route('sakip.reports.index')); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>

                <div class="flex items-center space-x-3">
                    <button type="button" id="preview-btn" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Pratinjau
                    </button>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Buat Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Pratinjau Laporan</h3>
                    <button type="button" id="close-preview" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="preview-content">
                    <!-- Preview content will be populated by JavaScript -->
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button type="button" id="close-preview-bottom" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-btn');
    const previewModal = document.getElementById('preview-modal');
    const closePreview = document.getElementById('close-preview');
    const closePreviewBottom = document.getElementById('close-preview-bottom');
    const previewContent = document.getElementById('preview-content');

    previewBtn.addEventListener('click', function() {
        const title = document.getElementById('title').value;
        const type = document.getElementById('type').value;
        const period = document.getElementById('period').value;
        const year = document.getElementById('year').value;
        const instansi = document.getElementById('instansi_id').selectedOptions[0]?.text;
        const executiveSummary = document.getElementById('executive_summary').value;
        const content = document.getElementById('content').value;
        const conclusions = document.getElementById('conclusions').value;
        const recommendations = document.getElementById('recommendations').value;

        previewContent.innerHTML = `
            <div class="space-y-6">
                <div class="text-center border-b border-gray-200 pb-4">
                    <h2 class="text-2xl font-bold text-gray-900">${title || '[Judul Laporan]'}</h2>
                    <p class="text-gray-600 mt-2">${type ? type.charAt(0).toUpperCase() + type.slice(1) : '[Jenis Laporan]'} - ${period ? period.charAt(0).toUpperCase() + period.slice(1) : '[Periode]'} ${year || '[Tahun]'}</p>
                    <p class="text-gray-600">${instansi || '[Instansi]'}</p>
                </div>

                ${executiveSummary ? `
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Ringkasan Eksekutif</h3>
                    <div class="text-gray-700">${executiveSummary.replace(/\n/g, '<br>')}</div>
                </div>
                ` : ''}

                ${content ? `
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Isi Laporan</h3>
                    <div class="text-gray-700">${content.replace(/\n/g, '<br>')}</div>
                </div>
                ` : ''}

                ${conclusions ? `
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Kesimpulan</h3>
                    <div class="text-gray-700">${conclusions.replace(/\n/g, '<br>')}</div>
                </div>
                ` : ''}

                ${recommendations ? `
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rekomendasi</h3>
                    <div class="text-gray-700">${recommendations.replace(/\n/g, '<br>')}</div>
                </div>
                ` : ''}
            </div>
        `;

        previewModal.classList.remove('hidden');
    });

    closePreview.addEventListener('click', function() {
        previewModal.classList.add('hidden');
    });

    closePreviewBottom.addEventListener('click', function() {
        previewModal.classList.add('hidden');
    });

    previewModal.addEventListener('click', function(e) {
        if (e.target === previewModal) {
            previewModal.classList.add('hidden');
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/sakip/reports/create.blade.php ENDPATH**/ ?>