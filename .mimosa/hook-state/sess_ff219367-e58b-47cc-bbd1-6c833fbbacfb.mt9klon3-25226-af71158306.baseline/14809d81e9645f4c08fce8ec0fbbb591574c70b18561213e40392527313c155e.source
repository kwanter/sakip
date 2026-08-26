@extends('layouts.modern')

@section('title', 'Edit Data Kinerja')

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
                <h1 class="page-header-title">Edit Data Kinerja</h1>
                <p class="page-header-subtitle">Perbarui data kinerja yang sudah ada</p>
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
            <form action="{{ route('sakip.data-collection.update', $data) }}" method="POST" enctype="multipart/form-data" id="dataCollectionForm">
                @csrf
                @method('PUT')

                <!-- Section: Informasi Dasar -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Informasi Dasar</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="indicator_id" class="form-label">Indikator Kinerja</label>
                                <select name="indicator_id" id="indicator_id" required class="form-select" disabled>
                                    <option value="{{ $data->indicator_id }}">{{ $data->indicator->name }}</option>
                                </select>
                                <input type="hidden" name="indicator_id" value="{{ $data->indicator_id }}">
                                <div class="form-text">Indikator tidak dapat diubah setelah data dibuat</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="instansi_id" class="form-label">Instansi</label>
                                <select name="instansi_id" id="instansi_id" required class="form-select" disabled>
                                    <option value="{{ $data->instansi_id }}">{{ $data->instansi->name }}</option>
                                </select>
                                <input type="hidden" name="instansi_id" value="{{ $data->instansi_id }}">
                                <div class="form-text">Instansi tidak dapat diubah setelah data dibuat</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select name="year" id="year" required class="form-select @error('year') is-invalid @enderror">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                                        <option value="{{ $y }}" {{ old('year', $data->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
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
                                <select name="period" id="period" required class="form-select @error('period') is-invalid @enderror">
                                    <option value="">Pilih Periode</option>
                                    <option value="triwulan-1" {{ old('period', $data->period) == 'triwulan-1' ? 'selected' : '' }}>Triwulan I (Januari - Maret)</option>
                                    <option value="triwulan-2" {{ old('period', $data->period) == 'triwulan-2' ? 'selected' : '' }}>Triwulan II (April - Juni)</option>
                                    <option value="triwulan-3" {{ old('period', $data->period) == 'triwulan-3' ? 'selected' : '' }}>Triwulan III (Juli - September)</option>
                                    <option value="triwulan-4" {{ old('period', $data->period) == 'triwulan-4' ? 'selected' : '' }}>Triwulan IV (Oktober - Desember)</option>
                                    <option value="bulanan" {{ old('period', $data->period) == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="tahunan" {{ old('period', $data->period) == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
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
                                <input type="number" name="value" id="value" step="0.01" value="{{ old('value', $data->value) }}" required class="form-control @error('value') is-invalid @enderror" placeholder="Masukkan nilai capaian">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                                <input type="number" name="target" id="target" step="0.01" value="{{ old('target', $data->target) }}" required class="form-control @error('target') is-invalid @enderror" placeholder="Masukkan target kinerja">
                                @error('target')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="data_source" class="form-label">Sumber Data <span class="text-danger">*</span></label>
                                <input type="text" name="data_source" id="data_source" value="{{ old('data_source', $data->data_source) }}" required class="form-control @error('data_source') is-invalid @enderror" placeholder="Contoh: Sistem Informasi, Laporan">
                                @error('data_source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="collection_method" class="form-label">Metode Pengumpulan <span class="text-danger">*</span></label>
                                <select name="collection_method" id="collection_method" required class="form-select @error('collection_method') is-invalid @enderror">
                                    <option value="">Pilih Metode</option>
                                    <option value="manual" {{ old('collection_method', $data->collection_method) == 'manual' ? 'selected' : '' }}>Manual</option>
                                    <option value="automated" {{ old('collection_method', $data->collection_method) == 'automated' ? 'selected' : '' }}>Otomatis</option>
                                    <option value="survey" {{ old('collection_method', $data->collection_method) == 'survey' ? 'selected' : '' }}>Survey</option>
                                    <option value="interview" {{ old('collection_method', $data->collection_method) == 'interview' ? 'selected' : '' }}>Wawancara</option>
                                </select>
                                @error('collection_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="Tambahkan catatan atau keterangan tambahan">{{ old('notes', $data->notes) }}</textarea>
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

                    <!-- Existing Evidence -->
                    @if($data->evidence->count() > 0)
                    <div class="mb-3">
                        <h6 class="form-label">Dokumen yang sudah diunggah:</h6>
                        <div class="row">
                            @foreach($data->evidence as $evidence)
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="card mb-0">
                                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt text-muted me-2"></i>
                                            <div>
                                                <div class="small fw-bold">{{ $evidence->filename }}</div>
                                                <small class="text-muted">{{ number_format($evidence->file_size / 1024, 2) }} KB</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEvidence({{ $evidence->id }}, this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- File Upload -->
                    <label class="form-label">Unggah Dokumen Bukti Tambahan</label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="evidence_files[]" id="evidence_files" multiple class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <div class="upload-area-content">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <p class="upload-text">Drag & drop file di sini atau</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('evidence_files').click()">
                                <i class="fas fa-folder-open"></i>
                                <span class="ms-1">Pilih File</span>
                            </button>
                            <p class="upload-hint">PDF, Excel, Word, Gambar (Maks. 5MB per file)</p>
                        </div>
                    </div>

                    <!-- File List Preview -->
                    <div id="fileList" class="mt-3"></div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <div class="btn-group">
                        <button type="submit" name="status" value="draft" class="btn btn-secondary">
                            <i class="fas fa-save"></i>
                            <span class="ms-1">Simpan Draft</span>
                        </button>
                        <button type="submit" name="status" value="pending" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <span class="ms-1">Kirim Validasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
    document.getElementById('evidence_files').addEventListener('change', function() {
        const files = this.files;
        const fileList = document.getElementById('fileList');

        // Clear existing content using DOM methods
        while (fileList.firstChild) {
            fileList.removeChild(fileList.firstChild);
        }

        if (files.length > 0) {
            const listGroup = document.createElement('div');
            listGroup.className = 'list-group';

            for (let i = 0; i < files.length; i++) {
                const fileSize = (files[i].size / 1024 / 1024).toFixed(2);
                const listItem = createFileListItem(files[i].name, fileSize);
                listGroup.appendChild(listItem);
            }

            fileList.appendChild(listGroup);
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
        uploadArea.addEventListener(eventName, function() {
            uploadArea.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, function() {
            uploadArea.classList.remove('dragover');
        }, false);
    });

    uploadArea.addEventListener('drop', function(e) {
        const files = e.dataTransfer.files;
        document.getElementById('evidence_files').files = files;
        document.getElementById('evidence_files').dispatchEvent(new Event('change'));
    });

    function removeEvidence(evidenceId, button) {
        if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
            // Create hidden input to mark for deletion
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_evidence[]';
            input.value = evidenceId;
            document.getElementById('dataCollectionForm').appendChild(input);

            // Remove from UI
            button.closest('.col-md-6').remove();
        }
    }
</script>
@endpush
@endsection
