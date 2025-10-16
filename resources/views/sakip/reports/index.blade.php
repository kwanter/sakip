@extends('sakip.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Laporan</h1>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Buat Laporan
                    </button>
                </div>
                
                <!-- Data Table Component -->
                @component('sakip.components.data-table', [
                    'id' => 'reports-table',
                    'type' => 'laporan',
                    'apiUrl' => route('sakip.api.datatables.laporan'),
                    'searchable' => true,
                    'exportable' => true,
                    'selectable' => true,
                    'actions' => ['edit', 'delete', 'view', 'download']
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
            SAKIP_DATA_TABLE_INIT.initLaporanTable('reports-table');
        }
    });
</script>
@endpush