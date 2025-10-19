@extends('layouts.app')

@section('title', 'Impor Data Kinerja')

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
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Impor Data Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Impor data kinerja secara massal menggunakan file Excel</p>
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

        <!-- Import Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-6 mb-6">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Petunjuk Penggunaan</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Unduh template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format yang telah ditentukan</li>
                            <li>Pastikan kode indikator dan periode sudah benar</li>
                            <li>File yang diunggah harus berformat .xlsx atau .xls</li>
                            <li>Maksimal ukuran file 10MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download Template -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Unduh Template</h3>
            <p class="text-gray-600 mb-4">Gunakan template ini untuk memastikan format data sesuai dengan sistem.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('sakip.data-collection.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh Template Excel
                </a>

                <a href="{{ route('sakip.data-collection.sample') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Lihat Contoh Data
                </a>
            </div>
        </div>

        <!-- Import Form -->
        <form action="{{ route('sakip.data-collection.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Import Configuration -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Konfigurasi Impor</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
                        <select name="year" id="year" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
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
                </div>
            </div>

            <!-- File Upload -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Unggah File</h3>

                <div class="border-2 border-dashed border-gray-300 rounded-md p-6 text-center hover:border-gray-400 transition-colors cursor-pointer" id="drop-zone">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">Klik untuk memilih file atau drag and drop</p>
                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls (maksimal 10MB)</p>
                    <input type="file" name="file" id="file" required class="hidden" accept=".xlsx,.xls">
                </div>

                <!-- File Preview -->
                <div id="file-preview" class="mt-4 hidden">
                    <div class="border border-gray-200 rounded-md p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900" id="file-name"></p>
                                    <p class="text-xs text-gray-500" id="file-size"></p>
                                </div>
                            </div>
                            <button type="button" id="remove-file" class="text-red-600 hover:text-red-900">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('sakip.data-collection.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Impor Data
                </button>
            </div>
        </form>

        <!-- Preview Results -->
        @if(isset($previewData))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Preview Hasil Impor</h3>

            <div class="mb-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-50 border border-green-200 rounded-md p-3">
                        <p class="text-sm font-medium text-green-800">Data Valid</p>
                        <p class="text-2xl font-bold text-green-600">{{ $previewData['valid_count'] }}</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                        <p class="text-sm font-medium text-red-800">Data Error</p>
                        <p class="text-2xl font-bold text-red-600">{{ $previewData['error_count'] }}</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                        <p class="text-sm font-medium text-blue-800">Total Data</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $previewData['total_count'] }}</p>
                    </div>
                </div>
            </div>

            @if(count($previewData['errors']) > 0)
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Daftar Error</h4>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 max-h-60 overflow-y-auto">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach($previewData['errors'] as $error)
                            <li>Baris {{ $error['row'] }}: {{ $error['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if(count($previewData['valid_data']) > 0)
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Data Valid</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Baris</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Indikator</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($previewData['valid_data'] as $row)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['row'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['indicator_code'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['period'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['value'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['target'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <form action="{{ route('sakip.data-collection.import.confirm') }}" method="POST">
                @csrf
                <input type="hidden" name="import_id" value="{{ $previewData['import_id'] }}">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Konfirmasi Impor
                </button>
            </form>
            @endif
        </div>
        @endif
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const dropZone = document.getElementById('drop-zone');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFile = document.getElementById('remove-file');

    // Drag and drop functionality
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-blue-300');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-blue-300');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-blue-300');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Click to select file
    dropZone.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    // Remove file
    removeFile.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
    });

    function handleFile(file) {
        // Check file type
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        if (!allowedTypes.includes(file.type)) {
            alert('File harus berformat Excel (.xlsx atau .xls)');
            return;
        }

        // Check file size (10MB = 10 * 1024 * 1024 bytes)
        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file tidak boleh melebihi 10MB');
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        filePreview.classList.remove('hidden');

        // Set the file to input
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
    }
});
</script>
@stop
