@extends('sakip.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">SAKIP Dashboard</h1>
                
                <!-- Dashboard Component -->
                <div id="sakip-dashboard" 
                     data-api-url="{{ route('sakip.api.dashboard') }}"
                     data-period="current_year"
                     data-instansi="{{ auth()->user()->instansi_id ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof SAKIP_DASHBOARD !== 'undefined') {
            SAKIP_DASHBOARD.init('sakip-dashboard');
        }
    });
</script>
@endpush