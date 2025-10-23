<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Indikator Kinerja</h1>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\PerformanceIndicator::class)): ?>
                        <a href="<?php echo e(route('sakip.indicators.create')); ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Indikator
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Data Table Component -->
                <?php $__env->startComponent('sakip.components.data-table', [
                    'id' => 'indicators-table',
                    'type' => 'indicator',
                    'apiUrl' => route('sakip.api.datatables.indicator'),
                    'searchable' => true,
                    'exportable' => true,
                    'selectable' => true,
                    'actions' => ['edit', 'delete', 'view']
                ]); ?>
                <?php echo $__env->renderComponent(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
            SAKIP_DATA_TABLE_INIT.initIndicatorTable('indicators-table');
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/sakip/indicators/index.blade.php ENDPATH**/ ?>