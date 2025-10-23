@extends('layouts.app')

@section('title', 'Tambah Indikator Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> Tambah Indikator Kinerja
        </h1>
        <a href="{{ route('sakip.indicators.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('sakip.indicators.store') }}" method="POST" enctype="multipart/form-data" id="indicatorForm">
                @csrf

                <!-- Informasi Dasar -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle"></i> Informasi Dasar Indikator
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Instansi -->
                        <div class="form-group mb-3">
                            <label for="instansi_id" class="form-label font-weight-bold">
                                Instansi <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg @error('instansi_id') is-invalid @enderror"
                                    id="instansi_id" name="instansi_id" required onchange="loadSasaranStrategis(this.value)">
                                <option value="">-- Pilih Instansi --</option>
                                @foreach($instansis as $inst)
                                    <option value="{{ $inst->id }}"
                                            {{ old('instansi_id', $userInstansi?->id) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instansi_id')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Kode Indikator -->
                                <div class="form-group mb-3">
                                    <label for="code" class="form-label font-weight-bold">
                                        Kode Indikator <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                                               id="code" name="code" value="{{ old('code') }}" required
                                               placeholder="Contoh: IK-001" maxlength="50">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateCode()" title="Generate otomatis">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                    @error('code')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Kode unik untuk identifikasi indikator</small>
                                </div>

                                <!-- Kategori -->
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label font-weight-bold">
                                        Kategori <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('category') is-invalid @enderror"
                                            id="category" name="category" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="input" {{ old('category') == 'input' ? 'selected' : '' }}>Input</option>
                                        <option value="output" {{ old('category') == 'output' ? 'selected' : '' }}>Output</option>
                                        <option value="outcome" {{ old('category') == 'outcome' ? 'selected' : '' }}>Outcome</option>
                                        <option value="impact" {{ old('category') == 'impact' ? 'selected' : '' }}>Impact</option>
                                    </select>
                                    @error('category')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Nama Indikator -->
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label font-weight-bold">
                                        Nama Indikator <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="Masukkan nama indikator" maxlength="255">
                                    @error('name')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Frekuensi -->
                                <div class="form-group mb-3">
                                    <label for="frequency" class="form-label font-weight-bold">
                                        Frekuensi <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('frequency') is-invalid @enderror"
                                            id="frequency" name="frequency" required>
                                        <option value="">-- Pilih Frekuensi --</option>
                                        <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                        <option value="quarterly" {{ old('frequency') == 'quarterly' ? 'selected' : '' }}>Triwulan</option>
                                        <option value="semester" {{ old('frequency') == 'semester' ? 'selected' : '' }}>Semester</option>
                                        <option value="annual" {{ old('frequency') == 'annual' ? 'selected' : '' }}>Tahunan</option>
                                    </select>
                                    @error('frequency')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label font-weight-bold">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Jelaskan secara detail tentang indikator ini" maxlength="500">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Target & Pengukuran -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-bullseye"></i> Target & Pengukuran
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Satuan Pengukuran -->
                                <div class="form-group mb-3">
                                    <label for="measurement_unit" class="form-label font-weight-bold">
                                        Satuan Pengukuran <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('measurement_unit') is-invalid @enderror"
                                           id="measurement_unit" name="measurement_unit" value="{{ old('measurement_unit') }}"
                                           placeholder="Contoh: %, Orang, Kg, Unit" maxlength="100" required>
                                    @error('measurement_unit')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Satuan untuk mengukur indikator ini</small>
                                </div>

                                <!-- Tipe Pengukuran -->
                                <div class="form-group mb-3">
                                    <label for="measurement_type" class="form-label font-weight-bold">
                                        Tipe Pengukuran
                                    </label>
                                    <select class="form-control @error('measurement_type') is-invalid @enderror"
                                            id="measurement_type" name="measurement_type">
                                        <option value="">-- Pilih Tipe Pengukuran --</option>
                                        <option value="percentage" {{ old('measurement_type') == 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                                        <option value="number" {{ old('measurement_type') == 'number' ? 'selected' : '' }}>Angka</option>
                                        <option value="ratio" {{ old('measurement_type') == 'ratio' ? 'selected' : '' }}>Rasio</option>
                                        <option value="index" {{ old('measurement_type') == 'index' ? 'selected' : '' }}>Indeks</option>
                                    </select>
                                    @error('measurement_type')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Jenis pengukuran untuk indikator</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Sumber Data -->
                                <div class="form-group mb-3">
                                    <label for="data_source" class="form-label font-weight-bold">
                                        Sumber Data <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('data_source') is-invalid @enderror"
                                           id="data_source" name="data_source" value="{{ old('data_source') }}"
                                           placeholder="Contoh: Sistem Informasi, Laporan Bulanan" maxlength="255" required>
                                    @error('data_source')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Metode Pengumpulan -->
                                <div class="form-group mb-3">
                                    <label for="collection_method" class="form-label font-weight-bold">
                                        Metode Pengumpulan <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('collection_method') is-invalid @enderror"
                                            id="collection_method" name="collection_method" required>
                                        <option value="">-- Pilih Metode --</option>
                                        <option value="manual" {{ old('collection_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="automated" {{ old('collection_method') == 'automated' ? 'selected' : '' }}>Otomatis</option>
                                        <option value="survey" {{ old('collection_method') == 'survey' ? 'selected' : '' }}>Survei</option>
                                        <option value="interview" {{ old('collection_method') == 'interview' ? 'selected' : '' }}>Wawancara</option>
                                        <option value="observation" {{ old('collection_method') == 'observation' ? 'selected' : '' }}>Observasi</option>
                                        <option value="document_review" {{ old('collection_method') == 'document_review' ? 'selected' : '' }}>Telaah Dokumen</option>
                                    </select>
                                    @error('collection_method')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Formula Perhitungan -->
                        <div class="form-group mb-3">
                            <label for="calculation_formula" class="form-label font-weight-bold">
                                Formula Perhitungan
                            </label>
                            <textarea class="form-control @error('calculation_formula') is-invalid @enderror"
                                      id="calculation_formula" name="calculation_formula" rows="2"
                                      placeholder="Contoh: (Keluaran / Target) * 100">{{ old('calculation_formula') }}</textarea>
                            @error('calculation_formula')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Bobot/Weight -->
                        <div class="form-group mb-3">
                            <label for="weight" class="form-label font-weight-bold">
                                Bobot (%)
                            </label>
                            <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                   id="weight" name="weight" value="{{ old('weight', 0) }}"
                                   min="0" max="100" step="0.01">
                            @error('weight')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">Persentase bobot dalam penilaian (opsional)</small>
                        </div>

                        <!-- Mandatory -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_mandatory" name="is_mandatory"
                                   value="1" {{ old('is_mandatory') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_mandatory">
                                <strong>Indikator Wajib (Mandatory)</strong>
                                <br>
                                <small class="text-muted">Centang jika indikator ini bersifat wajib untuk dilaporkan</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Target Tahunan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-warning text-dark">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-calendar-alt"></i> Target Tahunan <span class="text-danger">*</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle"></i> Tentukan target untuk setiap tahun. Minimal satu tahun harus ditambahkan.
                        </p>

                        <div id="targetsContainer">
                            <!-- Target rows will be dynamically added here -->
                            <div class="target-row mb-3" id="target-row-0">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="targets[0][year]" class="form-label font-weight-bold">
                                                    Tahun <span class="text-danger">*</span>
                                                </label>
                                                <select class="form-control @error('targets.0.year') is-invalid @enderror"
                                                        name="targets[0][year]" required>
                                                    <option value="">-- Pilih Tahun --</option>
                                                </select>
                                                @error('targets.0.year')
                                                    <small class="form-text text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label for="targets[0][target_value]" class="form-label font-weight-bold">
                                                    Nilai Target <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" class="form-control @error('targets.0.target_value') is-invalid @enderror"
                                                       name="targets[0][target_value]" step="0.01" min="0" required
                                                       placeholder="0.00">
                                                @error('targets.0.target_value')
                                                    <small class="form-text text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-3">
                                                <label for="targets[0][minimum_value]" class="form-label font-weight-bold">
                                                    Nilai Minimum
                                                </label>
                                                <input type="number" class="form-control @error('targets.0.minimum_value') is-invalid @enderror"
                                                       name="targets[0][minimum_value]" step="0.01" min="0"
                                                       placeholder="0.00 (opsional)">
                                                @error('targets.0.minimum_value')
                                                    <small class="form-text text-danger">{{ $message }}</small>
                                                @enderror
                                                <small class="form-text text-muted">Nilai minimum yang harus dicapai</small>
                                            </div>

                                            <div class="col-md-3 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeTargetRow(0)"
                                                        style="display: none;" id="remove-btn-0">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group mt-2 mb-0">
                                            <label for="targets[0][justification]" class="form-label font-weight-bold">
                                                Justifikasi (Alasan Penetapan Target)
                                            </label>
                                            <textarea class="form-control @error('targets.0.justification') is-invalid @enderror"
                                                      name="targets[0][justification]" rows="2"
                                                      placeholder="Jelaskan dasar pertimbangan penetapan target ini (opsional)"
                                                      maxlength="500"></textarea>
                                            @error('targets.0.justification')
                                                <small class="form-text text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success btn-sm mt-3" onclick="addTargetRow()">
                            <i class="fas fa-plus"></i> Tambah Tahun Target
                        </button>
                    </div>
                </div>

                <!-- Keterkaitan Strategis & Kegiatan -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-link"></i> Keterkaitan Strategis & Kegiatan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <!-- Sasaran Strategis -->
                                <div class="form-group mb-3">
                                    <label for="sasaran_strategis_id" class="form-label font-weight-bold">
                                        Sasaran Strategis
                                    </label>
                                    <select class="form-control @error('sasaran_strategis_id') is-invalid @enderror"
                                            id="sasaran_strategis_id" name="sasaran_strategis_id" onchange="loadProgram(this.value)">
                                        <option value="">-- Pilih Sasaran Strategis --</option>
                                    </select>
                                    @error('sasaran_strategis_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Pilih instansi terlebih dahulu</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Program -->
                                <div class="form-group mb-3">
                                    <label for="program_id" class="form-label font-weight-bold">
                                        Program Terkait
                                    </label>
                                    <select class="form-control @error('program_id') is-invalid @enderror"
                                            id="program_id" name="program_id" onchange="loadKegiatan(this.value)">
                                        <option value="">-- Pilih Program --</option>
                                    </select>
                                    @error('program_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Pilih sasaran strategis terlebih dahulu</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Kegiatan -->
                                <div class="form-group mb-3">
                                    <label for="kegiatan_id" class="form-label font-weight-bold">
                                        Kegiatan Terkait
                                    </label>
                                    <select class="form-control @error('kegiatan_id') is-invalid @enderror"
                                            id="kegiatan_id" name="kegiatan_id">
                                        <option value="">-- Pilih Kegiatan --</option>
                                    </select>
                                    @error('kegiatan_id')
                                        <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                    <small class="form-text text-muted">Pilih program terlebih dahulu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-save"></i> Simpan Indikator Kinerja
                        </button>
                        <a href="{{ route('sakip.indicators.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-dark text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info"></i> Panduan Pengisian
                    </h6>
                </div>
                <div class="card-body small">
                    <h6 class="font-weight-bold mb-2">Informasi Dasar:</h6>
                    <p class="text-muted">
                        Isi kode unik, nama indikator, dan pilih kategori sesuai dengan klasifikasi indikator (Input, Output, Outcome, atau Impact).
                    </p>

                    <h6 class="font-weight-bold mb-2 mt-3">Target & Pengukuran:</h6>
                    <p class="text-muted">
                        Tentukan satuan pengukuran, tipe pengukuran, metode pengumpulan data, dan sumber data yang akan digunakan untuk mengukur indikator.
                    </p>

                    <h6 class="font-weight-bold mb-2 mt-3">Keterkaitan Strategis:</h6>
                    <p class="text-muted">
                        Hubungkan indikator dengan sasaran strategis, program, dan kegiatan untuk memastikan alignment dengan rencana strategis organisasi.
                    </p>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Catatan Penting
                    </h6>
                </div>
                <div class="card-body small text-muted">
                    <ul>
                        <li>Kode indikator harus unik dalam sistem</li>
                        <li>Semua field bertanda <span class="text-danger">*</span> wajib diisi</li>
                        <li>Pastikan satuan pengukuran sesuai dengan jenis indikator</li>
                        <li>Data akan diaudit secara berkala untuk memastikan kualitas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize year dropdowns on page load
document.addEventListener('DOMContentLoaded', function() {
    populateYearOptions();
});

// Populate year dropdown options
function populateYearOptions() {
    const currentYear = new Date().getFullYear();
    const years = [];

    // Add current year and next 4 years
    for (let i = 0; i < 5; i++) {
        years.push(currentYear + i);
    }

    // Update all year dropdowns
    document.querySelectorAll('select[name*="[year]"]').forEach(select => {
        const currentValue = select.value;
        select.innerHTML = '<option value="">-- Pilih Tahun --</option>';
        years.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            select.appendChild(option);
        });
        if (currentValue) {
            select.value = currentValue;
        }
    });
}

// Add a new target row
function addTargetRow() {
    const container = document.getElementById('targetsContainer');
    const rowCount = container.querySelectorAll('.target-row').length;

    // Create new row HTML
    const newRow = document.createElement('div');
    newRow.className = 'target-row mb-3';
    newRow.id = 'target-row-' + rowCount;
    newRow.innerHTML = `
        <div class="card bg-light border-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="targets[${rowCount}][year]" class="form-label font-weight-bold">
                            Tahun <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" name="targets[${rowCount}][year]" required>
                            <option value="">-- Pilih Tahun --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="targets[${rowCount}][target_value]" class="form-label font-weight-bold">
                            Nilai Target <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" name="targets[${rowCount}][target_value]"
                               step="0.01" min="0" required placeholder="0.00">
                    </div>

                    <div class="col-md-3">
                        <label for="targets[${rowCount}][minimum_value]" class="form-label font-weight-bold">
                            Nilai Minimum
                        </label>
                        <input type="number" class="form-control" name="targets[${rowCount}][minimum_value]"
                               step="0.01" min="0" placeholder="0.00 (opsional)">
                        <small class="form-text text-muted">Nilai minimum yang harus dicapai</small>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeTargetRow(${rowCount})">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>

                <div class="form-group mt-2 mb-0">
                    <label for="targets[${rowCount}][justification]" class="form-label font-weight-bold">
                        Justifikasi (Alasan Penetapan Target)
                    </label>
                    <textarea class="form-control" name="targets[${rowCount}][justification]" rows="2"
                              placeholder="Jelaskan dasar pertimbangan penetapan target ini (opsional)"
                              maxlength="500"></textarea>
                </div>
            </div>
        </div>
    `;

    container.appendChild(newRow);

    // Populate year options for new row
    populateYearOptions();

    // Update remove button visibility
    updateRemoveButtonVisibility();
}

// Remove a target row
function removeTargetRow(rowNumber) {
    const row = document.getElementById('target-row-' + rowNumber);
    if (row) {
        row.remove();
        updateRemoveButtonVisibility();
    }
}

// Update remove button visibility based on number of rows
function updateRemoveButtonVisibility() {
    const rows = document.querySelectorAll('.target-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('[onclick*="removeTargetRow"]');
        if (removeBtn) {
            // Only show remove button if there's more than one row
            removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
    });
}

// Load Sasaran Strategis ketika Instansi berubah
function loadSasaranStrategis(instansiId) {
    const select = document.getElementById('sasaran_strategis_id');
    const programSelect = document.getElementById('program_id');
    const kegiatanSelect = document.getElementById('kegiatan_id');

    // Reset
    select.innerHTML = '<option value="">-- Pilih Sasaran Strategis --</option>';
    programSelect.innerHTML = '<option value="">-- Pilih Program --</option>';
    kegiatanSelect.innerHTML = '<option value="">-- Pilih Kegiatan --</option>';

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

// Load Program ketika Sasaran Strategis berubah
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

// Load Kegiatan ketika Program berubah
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

// Generate Code
function generateCode() {
    const name = document.getElementById('name').value;
    const category = document.getElementById('category').value;

    if (!name || !category) {
        alert('Silakan isi nama indikator dan kategori terlebih dahulu');
        return;
    }

    const categoryMap = {
        'input': 'I',
        'output': 'O',
        'outcome': 'OC',
        'impact': 'IM'
    };

    const prefix = categoryMap[category] || 'X';
    const timestamp = Date.now().toString().slice(-4);
    const code = prefix + '-' + timestamp;

    document.getElementById('code').value = code;
}

// Form validation and submit
document.getElementById('indicatorForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Check that at least one target is provided
    const targetRows = document.querySelectorAll('.target-row');
    let hasValidTarget = false;

    targetRows.forEach(row => {
        const yearInput = row.querySelector('select[name*="[year]"]');
        const targetValueInput = row.querySelector('input[name*="[target_value]"]');

        if (yearInput && yearInput.value && targetValueInput && targetValueInput.value) {
            hasValidTarget = true;
        }
    });

    if (!hasValidTarget) {
        isValid = false;
        alert('Mohon tambahkan minimal satu target tahun dengan nilai target yang valid');
    }

    if (!isValid) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi (ditandai dengan *)');
    }
});
</script>

<style>
.form-label {
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #dc3545;
}

.invalid-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

.btn-lg {
    padding: 0.75rem 2rem;
    font-size: 1rem;
    margin-right: 10px;
}

.form-control:focus,
.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endsection
