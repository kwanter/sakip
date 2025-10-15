@extends('sakip.layouts.app')

@section('title', 'Edit Data Kinerja')

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
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Edit Data Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Perbarui data kinerja indikator</p>
        </div>

        <!-- Alert Notifications -->
        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Data Collection Form -->
        <form action="{{ route('sakip.data-collection.update', $data) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="indicator_id" class="block text-sm font-medium text-gray-700 mb-2">Indikator Kinerja *</label>
                        <select name="indicator_id" id="indicator_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                            <option value="{{ $data->indicator_id }}">{{ $data->indicator->name }}</option>
                        </select>
                        <input type="hidden" name="indicator_id" value="{{ $data->indicator_id }}">
                    </div>
                    
                    <div>
                        <label for="instansi_id" class="block text-sm font-medium text-gray-700 mb-2">Instansi *</label>
                        <select name="instansi_id" id="instansi_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                            <option value="{{ $data->instansi_id }}">{{ $data->instansi->name }}</option>
                        </select>
                        <input type="hidden" name="instansi_id" value="{{ $data->instansi_id }}">
                    </div>
                    
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
                        <select name="year" id="year" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}" {{ old('year', $data->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                        @error('year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periode *</label>
                        <select name="period" id="period" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Periode</option>
                            <option value="Januari" {{ old('period', $data->period) == 'Januari' ? 'selected' : '' }}>Januari</option>
                            <option value="Februari" {{ old('period', $data->period) == 'Februari' ? 'selected' : '' }}>Februari</option>
                            <option value="Maret" {{ old('period', $data->period) == 'Maret' ? 'selected' : '' }}>Maret</option>
                            <option value="April" {{ old('period', $data->period) == 'April' ? 'selected' : '' }}>April</option>
                            <option value="Mei" {{ old('period', $data->period) == 'Mei' ? 'selected' : '' }}>Mei</option>
                            <option value="Juni" {{ old('period', $data->period) == 'Juni' ? 'selected' : '' }}>Juni</option>
                            <option value="Juli" {{ old('period', $data->period) == 'Juli' ? 'selected' : '' }}>Juli</option>
                            <option value="Agustus" {{ old('period', $data->period) == 'Agustus' ? 'selected' : '' }}>Agustus</option>
                            <option value="September" {{ old('period', $data->period) == 'September' ? 'selected' : '' }}>September</option>
                            <option value="Oktober" {{ old('period', $data->period) == 'Oktober' ? 'selected' : '' }}>Oktober</option>
                            <option value="November" {{ old('period', $data->period) == 'November' ? 'selected' : '' }}>November</option>
                            <option value="Desember" {{ old('period', $data->period) == 'Desember' ? 'selected' : '' }}>Desember</option>
                            <option value="Triwulan I" {{ old('period', $data->period) == 'Triwulan I' ? 'selected' : '' }}>Triwulan I</option>
                            <option value="Triwulan II" {{ old('period', $data->period) == 'Triwulan II' ? 'selected' : '' }}>Triwulan II</option>
                            <option value="Triwulan III" {{ old('period', $data->period) == 'Triwulan III' ? 'selected' : '' }}>Triwulan III</option>
                            <option value="Triwulan IV" {{ old('period', $data->period) == 'Triwulan IV' ? 'selected' : '' }}>Triwulan IV</option>
                            <option value="Semester I" {{ old('period', $data->period) == 'Semester I' ? 'selected' : '' }}>Semester I</option>
                            <option value="Semester II" {{ old('period', $data->period) == 'Semester II' ? 'selected' : '' }}>Semester II</option>
                        </select>
                        @error('period')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Performance Data -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Kinerja</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Nilai Kinerja *</label>
                        <input type="number" name="value" id="value" step="0.01" value="{{ old('value', $data->value) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan nilai kinerja">
                        @error('value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="target" class="block text-sm font-medium text-gray-700 mb-2">Target *</label>
                        <input type="number" name="target" id="target" step="0.01" value="{{ old('target', $data->target) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan target">
                        @error('target')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="data_source" class="block text-sm font-medium text-gray-700 mb-2">Sumber Data *</label>
                        <input type="text" name="data_source" id="data_source" value="{{ old('data_source', $data->data_source) }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Laporan Bulanan, Sistem Informasi">
                        @error('data_source')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="collection_method" class="block text-sm font-medium text-gray-700 mb-2">Metode Pengumpulan *</label>
                        <select name="collection_method" id="collection_method" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Metode</option>
                            <option value="survei" {{ old('collection_method', $data->collection_method) == 'survei' ? 'selected' : '' }}>Survei</option>
                            <option value="wawancara" {{ old('collection_method', $data->collection_method) == 'wawancara' ? 'selected' : '' }}>Wawancara</option>
                            <option value="observasi" {{ old('collection_method', $data->collection_method) == 'observasi' ? 'selected' : '' }}>Observasi</option>
                            <option value="dokumentasi" {{ old('collection_method', $data->collection_method) == 'dokumentasi' ? 'selected' : '' }}>Dokumentasi</option>
                            <option value="sistem_informasi" {{ old('collection_method', $data->collection_method) == 'sistem_informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                        </select>
                        @error('collection_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan catatan tambahan">{{ old('notes', $data->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Evidence Documents -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dokumen Bukti</h3>
                
                <!-- Existing Evidence -->
                @if($data->evidence->count() > 0)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Dokumen yang sudah diunggah:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($data->evidence as $evidence)
                        <div class="border border-gray-200 rounded-md p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $evidence->filename }}</p>
                                        <p class="text-xs text-gray-500">{{ number_format($evidence->file_size / 1024, 2) }} KB</p>
                                    </div>
                                </div>
                                <button type="button" class="text-red-600 hover:text-red-900" onclick="removeEvidence({{ $evidence->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- File Upload -->
                <div>
                    <label for="evidence_files" class="block text-sm font-medium text-gray-700 mb-2">Unggah Dokumen Bukti</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-md p-6 text-center hover:border-gray-400 transition-colors">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Klik untuk memilih file atau drag and drop</p>
                        <input type="file" name="evidence_files[]" id="evidence_files" multiple class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </div>
                    
                    <!-- File Preview -->
                    <div id="file-preview" class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
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
                
                <div class="flex items-center space-x-3">
                    <button type="submit" name="status" value="draft" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        Simpan sebagai Draft
                    </button>
                    
                    <button type="submit" name="status" value="pending" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Kirim untuk Validasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('evidence_files');
    const filePreview = document.getElementById('file-preview');
    
    // File upload preview
    fileInput.addEventListener('change', function(e) {
        filePreview.innerHTML = '';
        
        Array.from(e.target.files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'border border-gray-200 rounded-md p-3';
            fileItem.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${file.name}</p>
                            <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-900" onclick="removeFile(${index})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;
            filePreview.appendChild(fileItem);
        });
    });
    
    // Indicator preview
    document.getElementById('indicator_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const target = selectedOption.getAttribute('data-target');
        const unit = selectedOption.getAttribute('data-unit');
        
        if (target && unit) {
            document.getElementById('indicator-preview').innerHTML = `
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <p class="text-sm text-blue-800">Target: ${target} ${unit}</p>
                </div>
            `;
        }
    });
});

function removeFile(index) {
    const fileInput = document.getElementById('evidence_files');
    const files = Array.from(fileInput.files);
    files.splice(index, 1);
    
    // Create new FileList
    const dt = new DataTransfer();
    files.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
    
    // Trigger change event to update preview
    fileInput.dispatchEvent(new Event('change'));
}

function removeEvidence(evidenceId) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
        // Create hidden input to mark for deletion
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_evidence[]';
        input.value = evidenceId;
        document.querySelector('form').appendChild(input);
        
        // Remove from UI
        event.target.closest('.border').remove();
    }
}
</script>
@stop