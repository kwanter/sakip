@extends('layouts.app')

@section('title', 'Input Data Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.data-collection.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Input Data Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Masukkan data kinerja untuk periode yang ditentukan</p>
        </div>

        <!-- Alert Section -->
        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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

        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ isset($performanceData) ? route('sakip.data-collection.update', $performanceData) : route('sakip.data-collection.store') }}" enctype="multipart/form-data">
                @csrf
                @if(isset($performanceData))
                    @method('PUT')
                @endif

                <div class="px-6 py-6">
                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="indicator_id" class="block text-sm font-medium text-gray-700 mb-2">Indikator Kinerja *</label>
                                <select id="indicator_id" name="indicator_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('indicator_id') border-red-300 @enderror">
                                    <option value="">Pilih Indikator</option>
                                    @foreach($indicators as $indicator)
                                        <option value="{{ $indicator->id }}"
                                            {{ old('indicator_id', $performanceData->indicator_id ?? '') == $indicator->id ? 'selected' : '' }}>
                                            {{ $indicator->code }} - {{ $indicator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('indicator_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="instansi_id" class="block text-sm font-medium text-gray-700 mb-2">Instansi *</label>
                                <select id="instansi_id" name="instansi_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('instansi_id') border-red-300 @enderror">
                                    <option value="">Pilih Instansi</option>
                                    @if(isset($instansis))
                                        @foreach($instansis as $instansi)
                                            <option value="{{ $instansi->id }}"
                                                {{ old('instansi_id', $performanceData->instansi_id ?? '') == $instansi->id ? 'selected' : '' }}>
                                                {{ $instansi->nama_instansi }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('instansi_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun *</label>
                                <select id="year" name="year" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('year') border-red-300 @enderror">
                                    <option value="">Pilih Tahun</option>
                                    @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                                        <option value="{{ $year }}"
                                            {{ old('year', $performanceData->year ?? date('Y')) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('year')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Periode *</label>
                                <select id="period" name="period" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('period') border-red-300 @enderror">
                                    <option value="">Pilih Periode</option>
                                    <option value="triwulan-1" {{ old('period', $performanceData->period ?? '') == 'triwulan-1' ? 'selected' : '' }}>Triwulan I (Januari - Maret)</option>
                                    <option value="triwulan-2" {{ old('period', $performanceData->period ?? '') == 'triwulan-2' ? 'selected' : '' }}>Triwulan II (April - Juni)</option>
                                    <option value="triwulan-3" {{ old('period', $performanceData->period ?? '') == 'triwulan-3' ? 'selected' : '' }}>Triwulan III (Juli - September)</option>
                                    <option value="triwulan-4" {{ old('period', $performanceData->period ?? '') == 'triwulan-4' ? 'selected' : '' }}>Triwulan IV (Oktober - Desember)</option>
                                    <option value="bulanan" {{ old('period', $performanceData->period ?? '') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="tahunan" {{ old('period', $performanceData->period ?? '') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                                @error('period')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Performance Data -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Data Kinerja</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Nilai Kinerja *</label>
                                <input type="number" step="0.01" id="value" name="value" required
                                    value="{{ old('value', $performanceData->value ?? '') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('value') border-red-300 @enderror"
                                    placeholder="Masukkan nilai kinerja">
                                @error('value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="target" class="block text-sm font-medium text-gray-700 mb-2">Target</label>
                                <input type="number" step="0.01" id="target" name="target"
                                    value="{{ old('target', $performanceData->target ?? '') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('target') border-red-300 @enderror"
                                    placeholder="Masukkan target kinerja">
                                @error('target')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="data_source" class="block text-sm font-medium text-gray-700 mb-2">Sumber Data *</label>
                                <input type="text" id="data_source" name="data_source" required
                                    value="{{ old('data_source', $performanceData->data_source ?? '') }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('data_source') border-red-300 @enderror"
                                    placeholder="Contoh: Sistem Informasi, Laporan, Survey">
                                @error('data_source')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="collection_method" class="block text-sm font-medium text-gray-700 mb-2">Metode Pengumpulan</label>
                                <select id="collection_method" name="collection_method" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('collection_method') border-red-300 @enderror">
                                    <option value="">Pilih Metode</option>
                                    <option value="manual" {{ old('collection_method', $performanceData->collection_method ?? '') == 'manual' ? 'selected' : '' }}>Manual</option>
                                    <option value="automated" {{ old('collection_method', $performanceData->collection_method ?? '') == 'automated' ? 'selected' : '' }}>Otomatis</option>
                                    <option value="survey" {{ old('collection_method', $performanceData->collection_method ?? '') == 'survey' ? 'selected' : '' }}>Survey</option>
                                    <option value="interview" {{ old('collection_method', $performanceData->collection_method ?? '') == 'interview' ? 'selected' : '' }}>Wawancara</option>
                                </select>
                                @error('collection_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror"
                                placeholder="Tambahkan catatan atau keterangan tambahan">{{ old('notes', $performanceData->notes ?? '') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Evidence Documents -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Dokumen Bukti</h3>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-600 mb-2">Upload dokumen bukti (PDF, Excel, Word, gambar)</p>
                            <p class="text-xs text-gray-500 mb-4">Maksimal 5MB per file</p>
                            <input type="file" id="evidence_documents" name="evidence_documents[]" multiple
                                accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png"
                                class="hidden">
                            <button type="button" onclick="document.getElementById('evidence_documents').click()"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Pilih File
                            </button>
                        </div>

                        <!-- Existing Evidence Documents -->
                        @if(isset($performanceData) && $performanceData->evidenceDocuments->count() > 0)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Dokumen Bukti Saat Ini:</h4>
                            <div class="space-y-2">
                                @foreach($performanceData->evidenceDocuments as $document)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-sm text-gray-700">{{ $document->file_name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900 text-sm">Lihat</a>
                                        <button type="button" onclick="removeEvidence({{ $document->id }})" class="text-red-600 hover:text-red-900 text-sm">Hapus</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <a href="{{ route('sakip.data-collection.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                    <div class="flex items-center space-x-3">
                        <button type="submit" name="action" value="draft" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Simpan sebagai Draft
                        </button>
                        <button type="submit" name="action" value="submit" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ajukan Validasi
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Indicator Preview -->
        <div id="indicatorPreview" class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6" style="display: none;">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Preview Indikator</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Kode Indikator</h4>
                    <p id="previewCode" class="text-sm text-gray-900 mt-1"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Satuan Pengukuran</h4>
                    <p id="previewUnit" class="text-sm text-gray-900 mt-1"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Kategori</h4>
                    <p id="previewCategory" class="text-sm text-gray-900 mt-1"></p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Frekuensi</h4>
                    <p id="previewFrequency" class="text-sm text-gray-900 mt-1"></p>
                </div>
                <div class="md:col-span-2">
                    <h4 class="text-sm font-medium text-gray-500">Deskripsi</h4>
                    <p id="previewDescription" class="text-sm text-gray-900 mt-1"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Indicator preview functionality
    document.getElementById('indicator_id').addEventListener('change', function() {
        const indicatorId = this.value;
        if (indicatorId) {
            fetch(`/sakip/api/indicators/${indicatorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('indicatorPreview').style.display = 'block';
                        document.getElementById('previewCode').textContent = data.data.code;
                        document.getElementById('previewUnit').textContent = data.data.measurement_unit;
                        document.getElementById('previewCategory').textContent = data.data.category;
                        document.getElementById('previewFrequency').textContent = data.data.frequency;
                        document.getElementById('previewDescription').textContent = data.data.description;
                    }
                })
                .catch(error => {
                    console.error('Error fetching indicator data:', error);
                    document.getElementById('indicatorPreview').style.display = 'none';
                });
        } else {
            document.getElementById('indicatorPreview').style.display = 'none';
        }
    });

    // File upload preview
    document.getElementById('evidence_documents').addEventListener('change', function() {
        const files = this.files;
        if (files.length > 0) {
            let fileList = '<div class="mt-4"><h4 class="text-sm font-medium text-gray-700 mb-2">File yang akan diupload:</h4><ul class="space-y-1">';
            for (let i = 0; i < files.length; i++) {
                const fileSize = (files[i].size / 1024 / 1024).toFixed(2); // Convert to MB
                fileList += `<li class="text-sm text-gray-600">${files[i].name} (${fileSize} MB)</li>`;
            }
            fileList += '</ul></div>';

            // Insert or update the file list
            const existingList = document.getElementById('fileList');
            if (existingList) {
                existingList.innerHTML = fileList;
            } else {
                const fileListDiv = document.createElement('div');
                fileListDiv.id = 'fileList';
                fileListDiv.innerHTML = fileList;
                this.parentElement.parentElement.appendChild(fileListDiv);
            }
        }
    });

    // Remove evidence document
    function removeEvidence(documentId) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
            fetch(`/sakip/evidence-documents/${documentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus dokumen: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menghapus dokumen');
            });
        }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['indicator_id', 'instansi_id', 'year', 'period', 'value', 'data_source'];
        let isValid = true;

        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('border-red-300');
                isValid = false;
            } else {
                element.classList.remove('border-red-300');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi (ditandai dengan *).');
        }
    });

    // Trigger indicator preview if editing existing data
    @if(isset($performanceData) && $performanceData->indicator_id)
        document.getElementById('indicator_id').dispatchEvent(new Event('change'));
    @endif
</script>
@endpush
@endsection
