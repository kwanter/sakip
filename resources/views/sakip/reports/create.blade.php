@extends('sakip.layouts.app')

@section('title', 'Buat Laporan SAKIP')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.reports.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Buat Laporan SAKIP</h1>
            </div>
            <p class="mt-2 text-gray-600">Buat laporan kinerja berdasarkan data yang telah dikumpulkan</p>
        </div>

        <!-- Report Form -->
        <form action="{{ route('sakip.reports.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul Laporan *</label>
                        <input type="text" name="title" id="title" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan judul laporan">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan *</label>
                        <select name="type" id="type" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Jenis Laporan</option>
                            <option value="triwulan">Laporan Triwulan</option>
                            <option value="tahunan">Laporan Tahunan</option>
                            <option value="akuntabilitas">Laporan Akuntabilitas Kinerja</option>
                            <option value="evaluasi">Laporan Evaluasi Kinerja</option>
                            <option value="kinerja">Laporan Kinerja SKPD</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periode *</label>
                        <select name="period" id="period" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Periode</option>
                            <option value="triwulan-1">Triwulan I</option>
                            <option value="triwulan-2">Triwulan II</option>
                            <option value="triwulan-3">Triwulan III</option>
                            <option value="triwulan-4">Triwulan IV</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                        @error('period')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
                        <select name="year" id="year" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        @error('year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="instansi_id" class="block text-sm font-medium text-gray-700 mb-2">Instansi *</label>
                        <select name="instansi_id" id="instansi_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Instansi</option>
                            @foreach($instansis as $instansi)
                                <option value="{{ $instansi->id }}">{{ $instansi->name }}</option>
                            @endforeach
                        </select>
                        @error('instansi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Template Laporan</label>
                        <select name="template_id" id="template_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Template (Opsional)</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Isi Laporan</h3>
                
                <div class="space-y-6">
                    <div>
                        <label for="executive_summary" class="block text-sm font-medium text-gray-700 mb-2">Ringkasan Eksekutif</label>
                        <textarea name="executive_summary" id="executive_summary" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan ringkasan eksekutif laporan..."></textarea>
                        @error('executive_summary')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Isi Laporan</label>
                        <textarea name="content" id="content" rows="8" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan isi lengkap laporan..."></textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="conclusions" class="block text-sm font-medium text-gray-700 mb-2">Kesimpulan</label>
                        <textarea name="conclusions" id="conclusions" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan kesimpulan laporan..."></textarea>
                        @error('conclusions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi</label>
                        <textarea name="recommendations" id="recommendations" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan rekomendasi..."></textarea>
                        @error('recommendations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('sakip.reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
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
@stop

@section('scripts')
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
@stop