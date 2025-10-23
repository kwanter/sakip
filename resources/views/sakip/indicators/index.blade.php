@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Indikator Kinerja</h1>
                    @can('create', App\Models\PerformanceIndicator::class)
                        <a href="{{ route('sakip.indicators.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-block">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Indikator
                        </a>
                    @endcan
                </div>

                <!-- Data Table Component -->
                @component('sakip.components.data-table', [
                    'id' => 'indicators-table',
                    'type' => 'indicator',
                    'apiUrl' => route('sakip.api.datatables.indicator'),
                    'searchable' => true,
                    'exportable' => true,
                    'selectable' => true,
                    'actions' => ['edit', 'delete', 'view']
                ])
                @endcomponent
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
            SAKIP_DATA_TABLE_INIT.initIndicatorTable('indicators-table');
        }
    });
</script>
@endpush
