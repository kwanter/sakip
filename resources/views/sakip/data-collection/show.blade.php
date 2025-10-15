@extends('sakip.layouts.app')

@section('title', 'Detail Data Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.data-collection.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Detail Data Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Informasi lengkap data kinerja</p>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="mb-6 flex items-center space-x-3">
            <a href="{{ route('sakip.data-collection.edit', $data) }}" class="inline-flex items-center px-3 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            
            @if($data->status == 'pending')
            <form action="{{ route('sakip.data-collection.validate', $data) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Validasi
                </button>
            </form>
            
            <form action="{{ route('sakip.data-collection.reject', $data) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Tolak
                </button>
            </form>
            @endif
            
            <form action="{{ route('sakip.data-collection.destroy', $data) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Indikator Kinerja</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $data->indicator->name }}</p>
                    <p class="text-sm text-gray-600">{{ $data->indicator->code }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Instansi</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $data->instansi->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Periode</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $data->period }} {{ $data->year }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status Validasi</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $data->status == 'validated' ? 'bg-green-100 text-green-800' : ($data->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($data->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Performance Data -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Data Kinerja</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nilai Kinerja</label>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($data->value, 2) }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Target</label>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($data->target, 2) }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Pencapaian</label>
                    <p class="text-2xl font-bold {{ ($data->target > 0 ? ($data->value / $data->target * 100) : 0) >= 100 ? 'text-green-600' : 'text-red-600' }}">
                        @if($data->target > 0)
                            {{ number_format(($data->value / $data->target) * 100, 1) }}%
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Sumber Data</label>
                    <p class="text-gray-900">{{ $data->data_source }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Metode Pengumpulan</label>
                    <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $data->collection_method)) }}</p>
                </div>
            </div>
            
            @if($data->notes)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                <p class="text-gray-900">{{ $data->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Evidence Documents -->
        @if($data->evidence->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dokumen Bukti</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($data->evidence as $evidence)
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex items-center mb-3">
                        <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $evidence->filename }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($evidence->file_size / 1024, 2) }} KB</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ Storage::url($evidence->file_path) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-900">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat
                        </a>
                        <a href="{{ Storage::url($evidence->file_path) }}" download class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-900">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Validation Information -->
        @if($data->validated_at)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Validasi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Divalidasi oleh</label>
                    <p class="text-gray-900">{{ $data->validatedBy->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Validasi</label>
                    <p class="text-gray-900">{{ $data->validated_at->format('d F Y H:i') }}</p>
                </div>
            </div>
            
            @if($data->validation_notes)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-500 mb-1">Catatan Validasi</label>
                <p class="text-gray-900">{{ $data->validation_notes }}</p>
            </div>
            @endif
        </div>
        @endif

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sistem</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat oleh</label>
                    <p class="text-gray-900">{{ $data->createdBy->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Dibuat</label>
                    <p class="text-gray-900">{{ $data->created_at->format('d F Y H:i') }}</p>
                </div>
                
                @if($data->updated_at != $data->created_at)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Diperbarui oleh</label>
                    <p class="text-gray-900">{{ $data->updatedBy ? $data->updatedBy->name : '-' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Diperbarui</label>
                    <p class="text-gray-900">{{ $data->updated_at->format('d F Y H:i') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop