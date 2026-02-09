@extends('layouts.modern')

@section('title', 'Input Data Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">{{ isset($performanceData) ? 'Edit Data Kinerja' : 'Input Data Kinerja' }}</h1>
                <p class="page-header-subtitle">{{ isset($performanceData) ? 'Perbarui data kinerja yang sudah ada' : 'Masukkan data kinerja untuk periode yang ditentukan' }}</p>
            </div>
        </div>
    </div>

    <!-- Alert Section -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div>
            <strong>Terjadi kesalahan</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ isset($performanceData) ? route('sakip.data-collection.update', $performanceData) : route('sakip.data-collection.store') }}" enctype="multipart/form-data" id="dataCollectionForm">
                @csrf
                @if(isset($performanceData))
                    @method('PUT')
                @endif

                <!-- Section: Informasi Dasar -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Informasi Dasar</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="indicator_id" class="form-label">Indikator Kinerja <span class="text-danger">*</span></label>
                                <select id="indicator_id" name="indicator_id" required class="form-select @error('indicator_id') is-invalid @enderror">
                                    <option value="">Pilih Indikator</option>
                                    @foreach($indicators as $indicator)
                                        <option value="{{ $indicator->id }}" data-unit="{{ $indicator->measurement_unit }}" data-category="{{ $indicator->category }}" data-frequency="{{ $indicator->frequency }}" data-description="{{ $indicator->description }}"
                                            {{ old('indicator_id', $performanceData->indicator_id ?? '') == $indicator->id ? 'selected' : '' }}>
                                            {{ $indicator->code }} - {{ $indicator->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('indicator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="instansi_id" class="form-label">Instansi <span class="text-danger">*</span></label>
                                <select id="instansi_id" name="instansi_id" required class="form-select @error('instansi_id') is-invalid @enderror">
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
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select id="year" name="year" required class="form-select @error('year') is-invalid @enderror">
                                    <option value="">Pilih Tahun</option>
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}"
                                            {{ old('year', $performanceData->year ?? date('Y')) == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="period" class="form-label">Periode <span class="text-danger">*</span></label>
                                <select id="period" name="period" required class="form-select @error('period') is-invalid @enderror">
                                    <option value="">Pilih Periode</option>
                                    <option value="triwulan-1" {{ old('period', $performanceData->period ?? '') == 'triwulan-1' ? 'selected' : '' }}>Triwulan I (Januari - Maret)</option>
                                    <option value="triwulan-2" {{ old('period', $performanceData->period ?? '') == 'triwulan-2' ? 'selected' : '' }}>Triwulan II (April - Juni)</option>
                                    <option value="triwulan-3" {{ old('period', $performanceData->period ?? '') == 'triwulan-3' ? 'selected' : '' }}>Triwulan III (Juli - September)</option>
                                    <option value="triwulan-4" {{ old('period', $performanceData->period ?? '') == 'triwulan-4' ? 'selected' : '' }}>Triwulan IV (Oktober - Desember)</option>
                                    <option value="bulanan" {{ old('period', $performanceData->period ?? '') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="tahunan" {{ old('period', $performanceData->period ?? '') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </select>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Data Kinerja -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-chart-bar"></i>
                        <span>Data Kinerja</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="value" class="form-label">Nilai Capaian <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="value" name="value" required
                                    value="{{ old('value', $performanceData->actual ?? '') }}"
                                    class="form-control @error('value') is-invalid @enderror"
                                    placeholder="Masukkan nilai capaian">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="target" class="form-label">Target</label>
                                <input type="number" step="0.01" id="target" name="target"
                                    value="{{ old('target', $performanceData->target ?? '') }}"
                                    class="form-control @error('target') is-invalid @enderror"
                                    placeholder="Masukkan target kinerja">
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="data_source" class="form-label">Sumber Data <span class="text-danger">*</span></label>
                                <input type="text" id="data_source" name="data_source" required
                                    value="{{ old('data_source', $performanceData->data_source ?? '') }}"
                                    class="form-control @error('data_source') is-invalid @enderror"
                                    placeholder="Contoh: Sistem Informasi, Laporan, Survey">
                                @error('data_source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="collection_method" class="form-label">Metode Pengumpulan</label>
                                <select id="collection_method" name="collection_method" class="form-select @error('collection_method') is-invalid @enderror">
                                    <option value="">Pilih Metode</option>
                                    <option value="manual" {{ old('collection_method', $performanceData->collection_method ?? '') == 'manual' ? 'selected' : '' }}>Manual</option>
                                    <option value="automated" {{ old('collection_method', $performanceData->collection_method ?? '') == 'automated' ? 'selected' : '' }}>Otomatis</option>
                                    <option value="survey" {{ old('collection_method', $performanceData->collection_method ?? '') == 'survey' ? 'selected' : '' }}>Survey</option>
                                    <option value="interview" {{ old('collection_method', $performanceData->collection_method ?? '') == 'interview' ? 'selected' : '' }}>Wawancara</option>
                                </select>
                                @error('collection_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea id="notes" name="notes" rows="3"
                                    class="form-control @error('notes') is-invalid @enderror"
                                    placeholder="Tambahkan catatan atau keterangan tambahan">{{ old('notes', $performanceData->notes ?? '') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Dokumen Bukti -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-file-upload"></i>
                        <span>Dokumen Bukti</span>
                    </div>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" id="evidence_documents" name="evidence_documents[]" multiple
                            accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png" class="d-none">
                        <div class="upload-area-content">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Drag & drop file di sini atau</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('evidence_documents').click()">
                                <i class="fas fa-folder-open"></i>
                                <span class="ms-1">Pilih File</span>
                            </button>
                            <p class="upload-hint">PDF, Excel, Word, Gambar (Maks. 5MB per file)</p>
                        </div>
                    </div>

                    <!-- File List Preview -->
                    <div id="fileList" class="mt-3"></div>

                    <!-- Existing Evidence Documents -->
                    @if(isset($performanceData) && $performanceData->evidenceDocuments->count() > 0)
                    <div class="mt-3">
                        <h6 class="form-label">Dokumen Bukti Saat Ini:</h6>
                        <div class="list-group">
                            @foreach($performanceData->evidenceDocuments as $document)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-alt text-muted me-2"></i>
                                    <span>{{ $document->file_name }}</span>
                                </div>
                                <div>
                                    <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" onclick="removeEvidence({{ $document->id }})" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <div class="btn-group">
                        <button type="submit" name="action" value="draft" class="btn btn-secondary">
                            <i class="fas fa-save"></i>
                            <span class="ms-1">Simpan Draft</span>
                        </button>
                        <button type="submit" name="action" value="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <span class="ms-1">Ajukan Validasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Indicator Preview Card -->
    <div class="card mt-4" id="indicatorPreview" style="display: none;">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fas fa-eye me-2 text-primary"></i>
                Preview Indikator
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">Kode Indikator</small>
                    <p id="previewCode" class="mb-3 fw-bold"></p>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Satuan Pengukuran</small>
                    <p id="previewUnit" class="mb-3 fw-bold"></p>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Kategori</small>
                    <p id="previewCategory" class="mb-3"><span class="badge bg-primary"></span></p>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Frekuensi</small>
                    <p id="previewFrequency" class="mb-3"></p>
                </div>
                <div class="col-12">
                    <small class="text-muted">Deskripsi</small>
                    <p id="previewDescription" class="mb-0"></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Indicator preview functionality
    document.getElementById('indicator_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const indicatorId = this.value;

        if (indicatorId) {
            document.getElementById('indicatorPreview').style.display = 'block';
            document.getElementById('previewCode').textContent = selectedOption.text.split(' - ')[0];
            document.getElementById('previewUnit').textContent = selectedOption.dataset.unit || '-';
            document.getElementById('previewCategory').querySelector('.badge').textContent = selectedOption.dataset.category || '-';
            document.getElementById('previewFrequency').textContent = selectedOption.dataset.frequency || '-';
            document.getElementById('previewDescription').textContent = selectedOption.dataset.description || '-';
        } else {
            document.getElementById('indicatorPreview').style.display = 'none';
        }
    });

    // Get file icon based on extension
    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'fas fa-file-pdf text-danger',
            'xlsx': 'fas fa-file-excel text-success',
            'xls': 'fas fa-file-excel text-success',
            'docx': 'fas fa-file-word text-primary',
            'doc': 'fas fa-file-word text-primary',
            'jpg': 'fas fa-file-image text-info',
            'jpeg': 'fas fa-file-image text-info',
            'png': 'fas fa-file-image text-info'
        };
        return icons[ext] || 'fas fa-file text-muted';
    }

    // Create file list item using DOM methods
    function createFileListItem(fileName, fileSize) {
        const item = document.createElement('div');
        item.className = 'list-group-item';

        const dFlex = document.createElement('div');
        dFlex.className = 'd-flex align-items-center';

        const icon = document.createElement('i');
        icon.className = getFileIcon(fileName) + ' me-2 text-muted';

        const contentDiv = document.createElement('div');
        contentDiv.className = 'flex-grow-1';

        const nameDiv = document.createElement('div');
        nameDiv.className = 'fw-bold';
        nameDiv.textContent = fileName;

        const sizeSmall = document.createElement('small');
        sizeSmall.className = 'text-muted';
        sizeSmall.textContent = fileSize + ' MB';

        const badge = document.createElement('span');
        badge.className = 'badge bg-success';
        badge.textContent = 'Baru';

        contentDiv.appendChild(nameDiv);
        contentDiv.appendChild(sizeSmall);

        dFlex.appendChild(icon);
        dFlex.appendChild(contentDiv);
        dFlex.appendChild(badge);

        item.appendChild(dFlex);
        return item;
    }

    // File upload preview
    document.getElementById('evidence_documents').addEventListener('change', function() {
        const files = this.files;
        const fileList = document.getElementById('fileList');

        if (files.length > 0) {
            const listGroup = document.createElement('div');
            listGroup.className = 'list-group';

            for (let i = 0; i < files.length; i++) {
                const fileSize = (files[i].size / 1024 / 1024).toFixed(2);
                const listItem = createFileListItem(files[i].name, fileSize);
                listGroup.appendChild(listItem);
            }

            fileList.innerHTML = '';
            fileList.appendChild(listGroup);
        } else {
            fileList.innerHTML = '';
        }
    });

    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
    });

    uploadArea.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        document.getElementById('evidence_documents').files = files;
        document.getElementById('evidence_documents').dispatchEvent(new Event('change'));
    });

    // Remove evidence document
    function removeEvidence(documentId) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
            fetch('/sakip/evidence-documents/' + documentId, {
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

    // Trigger indicator preview if editing existing data
    @if(isset($performanceData) && $performanceData->indicator_id)
        document.getElementById('indicator_id').dispatchEvent(new Event('change'));
    @endif
</script>
@endpush
@endsection
