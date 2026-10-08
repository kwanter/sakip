@extends('layouts.modern')

@section('title', 'Edit Indikator Kinerja')

@section('page-title', 'Edit Indikator Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Edit Indikator Kinerja</h1>
                <p class="page-header-subtitle">Perbarui detail indikator kinerja</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('sakip.indicators.update', $indicator) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Informasi Dasar -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Informasi Dasar Indikator
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Instansi -->
                        <div class="form-group mb-3">
                            <label for="instansi_id" class="form-label fw-bold">
                                Instansi <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('instansi_id') is-invalid @enderror"
                                    id="instansi_id" name="instansi_id" required>
                                <option value="">-- Pilih Instansi --</option>
                                @foreach($instansis as $inst)
                                    <option value="{{ $inst->id }}"
                                            {{ old('instansi_id', $indicator->instansi_id) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instansi_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Kode Indikator -->
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label fw-bold">
                                        Kode Indikator <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $indicator->code) }}" required
                                           placeholder="Contoh: IK-001" maxlength="50">
                                    @error('code')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Kategori -->
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label fw-bold">
                                        Kategori <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('category') is-invalid @enderror"
                                            id="category" name="category" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="input" {{ old('category', $indicator->category) == 'input' ? 'selected' : '' }}>Input</option>
                                        <option value="output" {{ old('category', $indicator->category) == 'output' ? 'selected' : '' }}>Output</option>
                                        <option value="outcome" {{ old('category', $indicator->category) == 'outcome' ? 'selected' : '' }}>Outcome</option>
                                        <option value="impact" {{ old('category', $indicator->category) == 'impact' ? 'selected' : '' }}>Impact</option>
                                    </select>
                                    @error('category')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Nama Indikator -->
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        Nama Indikator <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $indicator->name) }}" required
                                           placeholder="Masukkan nama indikator" maxlength="255">
                                    @error('name')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Frekuensi -->
                                <div class="form-group mb-3">
                                    <label for="frequency" class="form-label fw-bold">
                                        Frekuensi <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('frequency') is-invalid @enderror"
                                            id="frequency" name="frequency" required>
                                        <option value="">-- Pilih Frekuensi --</option>
                                        <option value="monthly" {{ old('frequency', $indicator->frequency) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                        <option value="quarterly" {{ old('frequency', $indicator->frequency) == 'quarterly' ? 'selected' : '' }}>Triwulan</option>
                                        <option value="semester" {{ old('frequency', $indicator->frequency) == 'semester' ? 'selected' : '' }}>Semester</option>
                                        <option value="annual" {{ old('frequency', $indicator->frequency) == 'annual' ? 'selected' : '' }}>Tahunan</option>
                                    </select>
                                    @error('frequency')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label fw-bold">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Jelaskan secara detail tentang indikator ini" maxlength="500">{{ old('description', $indicator->description) }}</textarea>
                            @error('description')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Target & Pengukuran -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-bullseye"></i> Target & Pengukuran
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Satuan Pengukuran -->
                                <div class="form-group mb-3">
                                    <label for="measurement_unit" class="form-label fw-bold">
                                        Satuan Pengukuran <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('measurement_unit') is-invalid @enderror"
                                           id="measurement_unit" name="measurement_unit" value="{{ old('measurement_unit', $indicator->measurement_unit) }}"
                                           placeholder="Contoh: %, Orang, Kg, Unit" maxlength="100" required>
                                    @error('measurement_unit')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Satuan untuk mengukur indikator ini</small>
                                </div>

                                <!-- Tipe Pengukuran -->
                                <div class="form-group mb-3">
                                    <label for="measurement_type" class="form-label fw-bold">
                                        Tipe Pengukuran
                                    </label>
                                    <select class="form-select @error('measurement_type') is-invalid @enderror"
                                            id="measurement_type" name="measurement_type">
                                        <option value="">-- Pilih Tipe Pengukuran --</option>
                                        <option value="percentage" {{ old('measurement_type', $indicator->measurement_type) == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="number" {{ old('measurement_type', $indicator->measurement_type) == 'number' ? 'selected' : '' }}>Angka</option>
                                        <option value="ratio" {{ old('measurement_type', $indicator->measurement_type) == 'ratio' ? 'selected' : '' }}>Rasio</option>
                                        <option value="index" {{ old('measurement_type', $indicator->measurement_type) == 'index' ? 'selected' : '' }}>Indeks</option>
                                    </select>
                                    @error('measurement_type')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Jenis pengukuran untuk indikator</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Sumber Data -->
                                <div class="form-group mb-3">
                                    <label for="data_source" class="form-label fw-bold">
                                        Sumber Data <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('data_source') is-invalid @enderror"
                                           id="data_source" name="data_source" value="{{ old('data_source', $indicator->data_source) }}"
                                           placeholder="Contoh: Sistem Informasi, Laporan Bulanan" maxlength="255" required>
                                    @error('data_source')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Metode Pengumpulan -->
                                <div class="form-group mb-3">
                                    <label for="collection_method" class="form-label fw-bold">
                                        Metode Pengumpulan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('collection_method') is-invalid @enderror"
                                            id="collection_method" name="collection_method" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="manual" {{ old('collection_method', $indicator->collection_method) == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="automated" {{ old('collection_method', $indicator->collection_method) == 'automated' ? 'selected' : '' }}>Otomatis</option>
                                        <option value="survey" {{ old('collection_method', $indicator->collection_method) == 'survey' ? 'selected' : '' }}>Survei</option>
                                        <option value="interview" {{ old('collection_method', $indicator->collection_method) == 'interview' ? 'selected' : '' }}>Wawancara</option>
                                        <option value="observation" {{ old('collection_method', $indicator->collection_method) == 'observation' ? 'selected' : '' }}>Observasi</option>
                                        <option value="document_review" {{ old('collection_method', $indicator->collection_method) == 'document_review' ? 'selected' : '' }}>Telaah Dokumen</option>
                                    </select>
                                    @error('collection_method')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Formula Perhitungan -->
                        <div class="form-group mb-3">
                            <label for="calculation_formula" class="form-label fw-bold">
                                Formula Perhitungan
                            </label>
                            <textarea class="form-control @error('calculation_formula') is-invalid @enderror"
                                      id="calculation_formula" name="calculation_formula" rows="2"
                                      placeholder="Contoh: (Keluaran / Target) * 100">{{ old('calculation_formula', $indicator->calculation_formula) }}</textarea>
                            @error('calculation_formula')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mandatory -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_mandatory" name="is_mandatory"
                                   value="1" {{ old('is_mandatory', $indicator->is_mandatory) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_mandatory">
                                <strong>Indikator Wajib (Mandatory)</strong>
                                <br>
                                <small class="text-muted">Centang jika indikator ini bersifat wajib untuk dilaporkan</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Keterkaitan Strategis & Kegiatan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-link"></i> Keterkaitan Strategis & Kegiatan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <!-- Sasaran Strategis -->
                                <div class="form-group mb-3">
                                    <label for="sasaran_strategis_id" class="form-label fw-bold">
                                        Sasaran Strategis
                                    </label>
                                    <select class="form-select @error('sasaran_strategis_id') is-invalid @enderror"
                                            id="sasaran_strategis_id" name="sasaran_strategis_id" onchange="loadProgram(this.value)">
                                        <option value="">-- Pilih Sasaran Strategis --</option>
                                    </select>
                                    @error('sasaran_strategis_id')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Program -->
                                <div class="form-group mb-3">
                                    <label for="program_id" class="form-label fw-bold">
                                        Program Terkait
                                    </label>
                                    <select class="form-select @error('program_id') is-invalid @enderror"
                                            id="program_id" name="program_id" onchange="loadKegiatan(this.value)">
                                        <option value="">-- Pilih Program --</option>
                                    </select>
                                    @error('program_id')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Kegiatan -->
                                <div class="form-group mb-3">
                                    <label for="kegiatan_id" class="form-label fw-bold">
                                        Kegiatan Terkait
                                    </label>
                                    <select class="form-select @error('kegiatan_id') is-invalid @enderror"
                                            id="kegiatan_id" name="kegiatan_id">
                                        <option value="">-- Pilih Kegiatan --</option>
                                    </select>
                                    @error('kegiatan_id')
                                        <div class="form-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Panel & Delete -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info"></i> Detail Indikator
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Kode:</strong> {{ $indicator->code }}
                    </p>
                    <p class="mb-2">
                        <strong>Kategori:</strong> {{ ucfirst($indicator->category) }}
                    </p>
                    <p class="mb-2">
                        <strong>Satuan:</strong> {{ $indicator->measurement_unit }}
                    </p>
                    <p class="mb-0">
                        <strong>Status:</strong>
                        @if($indicator->is_mandatory)
                            <span class="badge badge-danger">Wajib</span>
                        @else
                            <span class="badge badge-warning">Opsional</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-trash"></i> Zona Berbahaya
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <strong>Perhatian:</strong> Aksi ini tidak dapat dibatalkan. Indikator dan semua data terkait akan dihapus permanen.
                    </p>

                    @can('delete', $indicator)
                    <form action="{{ route('sakip.indicators.destroy', $indicator) }}" method="POST"
                          onsubmit="return confirm('Anda yakin ingin menghapus indikator ini? Aksi ini tidak dapat dibatalkan!');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash"></i> Hapus Indikator
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Audit Trail
                    </h6>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-1">
                        <strong>Dibuat:</strong> {{ $indicator->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-1">
                        <strong>Diupdate:</strong> {{ $indicator->updated_at->format('d/m/Y H:i') }}
                    </p>
                    @if($indicator->deleted_at)
                    <p class="mb-0">
                        <strong>Dihapus:</strong> {{ $indicator->deleted_at->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ app()->bound('csp-nonce') ? app('csp-nonce') : '' }}">
// Load Sasaran Strategis ketika Instansi berubah
document.getElementById('instansi_id').addEventListener('change', function() {
    loadSasaranStrategis(this.value);
});

// Load Program ketika Sasaran Strategis berubah
document.getElementById('sasaran_strategis_id').addEventListener('change', function() {
    loadProgram(this.value);
});

// Load Sasaran Strategis
function loadSasaranStrategis(instansiId) {
    const select = document.getElementById('sasaran_strategis_id');
    const programSelect = document.getElementById('program_id');

    // Reset
    select.innerHTML = '<option value="">-- Pilih Sasaran Strategis --</option>';
    programSelect.innerHTML = '<option value="">-- Pilih Program --</option>';

    if (!instansiId) return;

    fetch(`{{ url('sakip/api/sasaran-strategis/by-instansi') }}/${instansiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_strategis;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading sasaran strategis:', error);
        });
}

// Load Program
function loadProgram(sasaranStrategisId) {
    const select = document.getElementById('program_id');
    const kegiatanSelect = document.getElementById('kegiatan_id');

    // Reset
    select.innerHTML = '<option value="">-- Pilih Program --</option>';
    kegiatanSelect.innerHTML = '<option value="">-- Pilih Kegiatan --</option>';

    if (!sasaranStrategisId) return;

    fetch(`{{ url('sakip/api/program/by-sasaran-strategis') }}/${sasaranStrategisId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_program;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading program:', error);
        });
}

// Load Kegiatan
function loadKegiatan(programId) {
    const select = document.getElementById('kegiatan_id');

    // Reset
    select.innerHTML = '<option value="">-- Pilih Kegiatan --</option>';

    if (!programId) return;

    fetch(`{{ url('sakip/api/kegiatan/by-program') }}/${programId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_kegiatan;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kegiatan:', error);
        });
}

// Initialize with current values
window.addEventListener('DOMContentLoaded', function() {
    const instansiId = document.getElementById('instansi_id').value;
    const sasaranId = '{{ $indicator->sasaran_strategis_id }}';
    const programId = '{{ $indicator->program_id }}';
    const kegiatanId = '{{ $indicator->kegiatan_id }}';

    if (instansiId) {
        loadSasaranStrategis(instansiId);

        // Set current values after loading
        setTimeout(() => {
            if (sasaranId) {
                document.getElementById('sasaran_strategis_id').value = sasaranId;
                loadProgram(sasaranId);

                setTimeout(() => {
                    if (programId) {
                        document.getElementById('program_id').value = programId;
                        loadKegiatan(programId);

                        setTimeout(() => {
                            if (kegiatanId) {
                                document.getElementById('kegiatan_id').value = kegiatanId;
                            }
                        }, 500);
                    }
                }, 500);
            }
        }, 500);
    }
});
</script>

@endsection
