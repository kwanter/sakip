@extends('layouts.modern')

@section('title', 'Impor Data Kinerja')

@section('page-title', 'Impor Data Kinerja')

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
                <h1 class="page-header-title">Impor Data Kinerja</h1>
                <p class="page-header-subtitle">Impor data kinerja secara massal menggunakan file Excel</p>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Terdapat kesalahan:</div>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Import Instructions -->
    <div class="alert alert-info mb-4" role="alert">
        <i class="fas fa-info-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">Petunjuk Penggunaan</div>
            <ul class="mb-0 mt-2 ps-4">
                <li>Unduh template Excel terlebih dahulu</li>
                <li>Isi data sesuai format yang telah ditentukan</li>
                <li>Pastikan kode indikator dan periode sudah benar</li>
                <li>File yang diunggah harus berformat .xlsx atau .xls</li>
                <li>Maksimal ukuran file 10MB</li>
            </ul>
        </div>
    </div>

    <!-- Download Template -->
    <div class="modern-card mb-4">
        <div class="card-body">
            <h6 class="card-title">Unduh Template</h6>
            <p class="text-muted">Gunakan template ini untuk memastikan format data sesuai dengan sistem.</p>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('sakip.data-collection.template') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-download"></i>
                    <span class="ms-1">Unduh Template Excel</span>
                </a>

                <a href="{{ route('sakip.data-collection.sample') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-file-alt"></i>
                    <span class="ms-1">Lihat Contoh Data</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <form action="{{ route('sakip.data-collection.import') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Import Configuration -->
        <div class="modern-card mb-4">
            <div class="card-body">
                <h6 class="card-title">Konfigurasi Impor</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                            <select name="year" id="year" required class="form-select">
                                @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                            @error('year')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="instansi_id" class="form-label">Instansi <span class="text-danger">*</span></label>
                            <select name="instansi_id" id="instansi_id" required class="form-select">
                                <option value="">Pilih Instansi</option>
                                @foreach($instansis as $instansi)
                                    <option value="{{ $instansi->id }}">{{ $instansi->name }}</option>
                                @endforeach
                            </select>
                            @error('instansi_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Upload -->
        <div class="modern-card mb-4">
            <div class="card-body">
                <h6 class="card-title">Unggah File</h6>

                <div class="upload-area" id="drop-zone">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="upload-text">Klik untuk memilih file atau drag and drop</p>
                    <p class="upload-hint">Format: .xlsx, .xls (maksimal 10MB)</p>
                    <input type="file" name="file" id="file" required class="d-none" accept=".xlsx,.xls">
                </div>

                <!-- File Preview -->
                <div id="file-preview" class="mt-3 d-none">
                    <div class="p-3 rounded border">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-excel fa-2x text-success me-3"></i>
                                <div>
                                    <p class="mb-0 fw-bold" id="file-name"></p>
                                    <small class="text-muted" id="file-size"></small>
                                </div>
                            </div>
                            <button type="button" id="remove-file" class="btn btn-outline-danger btn-sm" title="Hapus file">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
                <span class="ms-1">Batal</span>
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-import"></i>
                <span class="ms-1">Impor Data</span>
            </button>
        </div>
    </form>

    <!-- Preview Results -->
    @if(isset($previewData))
    <div class="modern-card mt-4">
        <div class="card-body">
            <h6 class="card-title">Preview Hasil Impor</h6>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value text-success">{{ $previewData['valid_count'] }}</div>
                        <div class="stat-label">Data Valid</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value text-danger">{{ $previewData['error_count'] }}</div>
                        <div class="stat-label">Data Error</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value">{{ $previewData['total_count'] }}</div>
                        <div class="stat-label">Total Data</div>
                    </div>
                </div>
            </div>

            @if(count($previewData['errors']) > 0)
            <div class="alert alert-danger mb-4" role="alert">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div class="alert-content">
                    <div class="alert-title">Daftar Error</div>
                    <ul class="mb-0 mt-2">
                        @foreach($previewData['errors'] as $error)
                            <li>Baris {{ $error['row'] }}: {{ $error['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if(count($previewData['valid_data']) > 0)
            <h6 class="card-title">Data Valid</h6>
            <div class="modern-table-container mb-4">
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Baris</th>
                                <th>Kode Indikator</th>
                                <th>Periode</th>
                                <th>Nilai</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewData['valid_data'] as $row)
                            <tr>
                                <td>{{ $row['row'] }}</td>
                                <td>{{ $row['indicator_code'] }}</td>
                                <td>{{ $row['period'] }}</td>
                                <td>{{ $row['value'] }}</td>
                                <td>{{ $row['target'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <form action="{{ route('sakip.data-collection.import.confirm') }}" method="POST">
                @csrf
                <input type="hidden" name="import_id" value="{{ $previewData['import_id'] }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    <span class="ms-1">Konfirmasi Impor</span>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
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
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');

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
        filePreview.classList.add('d-none');
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
        filePreview.classList.remove('d-none');

        // Set the file to input
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
    }
});
</script>
@endpush
