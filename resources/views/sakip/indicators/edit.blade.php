@extends('sakip.layouts.app')

@section('title', 'Edit Indikator Kinerja - SAKIP')

@section('content')
<div class="sakip-indicators-edit">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <h1 class="page-title">Edit Indikator Kinerja</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sakip.indicators.index') }}">Indikator Kinerja</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
            <div class="col-sm-4 text-end">
                <a href="{{ route('sakip.indicators.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="{{ route('sakip.indicators.show', $indicator->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>

    <!-- Audit Trail Info -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="card-title mb-0">
                <i class="fas fa-history"></i> Informasi Perubahan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Dibuat oleh</small>
                    <p class="mb-0"><strong>Dr. Andi Wijaya, M.Si</strong></p>
                    <small class="text-muted">20 Januari 2024</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Terakhir diubah</small>
                    <p class="mb-0"><strong>Siti Nurhaliza, S.Kom</strong></p>
                    <small class="text-muted">15 Maret 2024</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Status</small>
                    <p class="mb-0"><span class="badge bg-success">Aktif</span></p>
                    <small class="text-muted">Disetujui oleh: Kepala Bappeda</small>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Versi</small>
                    <p class="mb-0"><strong>v2.1</strong></p>
                    <small class="text-muted">5 kali perubahan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-indicator mb-4">
        <div class="progress-step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Informasi Dasar</div>
        </div>
        <div class="progress-step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Target & Formula</div>
        </div>
        <div class="progress-step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Validasi & Dokumen</div>
        </div>
        <div class="progress-step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Review & Update</div>
        </div>
    </div>

    <!-- Form Container -->
    <form id="indicatorForm" method="POST" action="{{ route('sakip.indicators.update', $indicator->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Step 1: Basic Information -->
        <div class="form-step" id="step1">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Informasi Dasar Indikator
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">
                                    Kode Indikator <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">IK</span>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $indicator->code ?? 'U-001') }}" required 
                                           placeholder="Contoh: U-001" maxlength="10">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Kode unik untuk identifikasi indikator</small>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Nama Indikator <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $indicator->name ?? 'Capaian Kinerja Utama Pelayanan Kesehatan') }}" required 
                                       placeholder="Masukkan nama indikator" maxlength="255">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="character-count">
                                    <small class="text-muted">
                                        <span id="nameCount">{{ strlen(old('name', $indicator->name ?? '')) }}</span>/255 karakter
                                    </small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">
                                    Kategori Indikator <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category" required onchange="updateCategoryDescription()">
                                    <option value="">Pilih Kategori</option>
                                    <option value="iku" {{ old('category', $indicator->category ?? 'iku') == 'iku' ? 'selected' : '' }}>
                                        IKU (Indikator Kinerja Utama)
                                    </option>
                                    <option value="ikk" {{ old('category', $indicator->category ?? 'iku') == 'ikk' ? 'selected' : '' }}>
                                        IKK (Indikator Kinerja Kegiatan)
                                    </option>
                                    <option value="ikt" {{ old('category', $indicator->category ?? 'iku') == 'ikt' ? 'selected' : '' }}>
                                        IKT (Indikator Kinerja Turunan)
                                    </option>
                                    <option value="iks" {{ old('category', $indicator->category ?? 'iku') == 'iks' ? 'selected' : '' }}>
                                        IKS (Indikator Kinerja Strategis)
                                    </option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted" id="categoryDescription">
                                    {{ getCategoryDescription(old('category', $indicator->category ?? 'iku')) }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">
                                    Unit Kerja <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id" required>
                                    <option value="">Pilih Unit Kerja</option>
                                    <option value="1" {{ old('department_id', $indicator->department_id ?? '1') == '1' ? 'selected' : '' }}>
                                        Dinas Kesehatan
                                    </option>
                                    <option value="2" {{ old('department_id', $indicator->department_id ?? '1') == '2' ? 'selected' : '' }}>
                                        Dinas Pendidikan
                                    </option>
                                    <option value="3" {{ old('department_id', $indicator->department_id ?? '1') == '3' ? 'selected' : '' }}>
                                        Dinas Sosial
                                    </option>
                                    <option value="4" {{ old('department_id', $indicator->department_id ?? '1') == '4' ? 'selected' : '' }}>
                                        Dinas PU
                                    </option>
                                    <option value="5" {{ old('department_id', $indicator->department_id ?? '1') == '5' ? 'selected' : '' }}>
                                        Dinas Perhubungan
                                    </option>
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="year" class="form-label">
                                    Tahun <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('year') is-invalid @enderror" 
                                        id="year" name="year" required>
                                    <option value="">Pilih Tahun</option>
                                    @for($year = date('Y') + 1; $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}" {{ old('year', $indicator->year ?? '2024') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi Indikator</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Jelaskan secara detail tentang indikator ini">{{ old('description', $indicator->description ?? 'Indikator utama untuk mengukur efektivitas pelayanan kesehatan kepada masyarakat') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="character-count">
                                    <small class="text-muted">
                                        <span id="descriptionCount">{{ strlen(old('description', $indicator->description ?? '')) }}</span>/500 karakter
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-link"></i> Keterkaitan dengan Tujuan Strategis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="strategic_goal_id" class="form-label">
                                    Tujuan Strategis
                                </label>
                                <select class="form-select @error('strategic_goal_id') is-invalid @enderror" 
                                        id="strategic_goal_id" name="strategic_goal_id">
                                    <option value="">Pilih Tujuan Strategis</option>
                                    <option value="1" {{ old('strategic_goal_id', $indicator->strategic_goal_id ?? '1') == '1' ? 'selected' : '' }}>
                                        Meningkatkan Kualitas Pelayanan Publik
                                    </option>
                                    <option value="2" {{ old('strategic_goal_id', $indicator->strategic_goal_id ?? '1') == '2' ? 'selected' : '' }}>
                                        Meningkatkan Kesejahteraan Masyarakat
                                    </option>
                                    <option value="3" {{ old('strategic_goal_id', $indicator->strategic_goal_id ?? '1') == '3' ? 'selected' : '' }}>
                                        Meningkatkan Infrastruktur Daerah
                                    </option>
                                </select>
                                @error('strategic_goal_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="program_id" class="form-label">
                                    Program Terkait
                                </label>
                                <select class="form-select @error('program_id') is-invalid @enderror" 
                                        id="program_id" name="program_id">
                                    <option value="">Pilih Program</option>
                                    <option value="1" {{ old('program_id', $indicator->program_id ?? '1') == '1' ? 'selected' : '' }}>
                                        Program Pelayanan Kesehatan
                                    </option>
                                    <option value="2" {{ old('program_id', $indicator->program_id ?? '1') == '2' ? 'selected' : '' }}>
                                        Program Pendidikan Dasar
                                    </option>
                                    <option value="3" {{ old('program_id', $indicator->program_id ?? '1') == '3' ? 'selected' : '' }}>
                                        Program Sosial Masyarakat
                                    </option>
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Target & Formula -->
        <div class="form-step" id="step2" style="display: none;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye"></i> Target Indikator
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="target_value" class="form-label">
                                    Nilai Target <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_value') is-invalid @enderror" 
                                           id="target_value" name="target_value" value="{{ old('target_value', $indicator->target_value ?? '85') }}" 
                                           required step="0.01" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('target_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="target_type" class="form-label">
                                    Tipe Target <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('target_type') is-invalid @enderror" 
                                        id="target_type" name="target_type" required onchange="updateTargetFields()">
                                    <option value="">Pilih Tipe</option>
                                    <option value="percentage" {{ old('target_type', $indicator->target_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                        Persentase (%)
                                    </option>
                                    <option value="number" {{ old('target_type', $indicator->target_type ?? 'percentage') == 'number' ? 'selected' : '' }}>
                                        Angka
                                    </option>
                                    <option value="ratio" {{ old('target_type', $indicator->target_type ?? 'percentage') == 'ratio' ? 'selected' : '' }}>
                                        Rasio
                                    </option>
                                    <option value="index" {{ old('target_type', $indicator->target_type ?? 'percentage') == 'index' ? 'selected' : '' }}>
                                        Indeks
                                    </option>
                                </select>
                                @error('target_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="target_direction" class="form-label">
                                    Arah Target <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('target_direction') is-invalid @enderror" 
                                        id="target_direction" name="target_direction" required>
                                    <option value="">Pilih Arah</option>
                                    <option value="higher_better" {{ old('target_direction', $indicator->target_direction ?? 'higher_better') == 'higher_better' ? 'selected' : '' }}>
                                        Lebih Tinggi Lebih Baik
                                    </option>
                                    <option value="lower_better" {{ old('target_direction', $indicator->target_direction ?? 'higher_better') == 'lower_better' ? 'selected' : '' }}>
                                        Lebih Rendah Lebih Baik
                                    </option>
                                    <option value="exact" {{ old('target_direction', $indicator->target_direction ?? 'higher_better') == 'exact' ? 'selected' : '' }}>
                                        Tepat Sasaran
                                    </option>
                                </select>
                                @error('target_direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="baseline_value" class="form-label">
                                    Nilai Dasar (Baseline)
                                </label>
                                <input type="number" class="form-control @error('baseline_value') is-invalid @enderror" 
                                       id="baseline_value" name="baseline_value" value="{{ old('baseline_value', $indicator->baseline_value ?? '65') }}" 
                                       step="0.01" min="0">
                                @error('baseline_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Nilai awal sebelum target ditetapkan</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="baseline_year" class="form-label">
                                    Tahun Dasar
                                </label>
                                <select class="form-select @error('baseline_year') is-invalid @enderror" 
                                        id="baseline_year" name="baseline_year">
                                    <option value="">Pilih Tahun</option>
                                    @for($year = date('Y'); $year >= date('Y') - 10; $year--)
                                        <option value="{{ $year }}" {{ old('baseline_year', $indicator->baseline_year ?? '2023') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('baseline_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> Formula Perhitungan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="numerator" class="form-label">
                                    Pembilang (Numerator)
                                </label>
                                <textarea class="form-control @error('numerator') is-invalid @enderror" 
                                          id="numerator" name="numerator" rows="2" 
                                          placeholder="Jelaskan pembilang dalam rumus">{{ old('numerator', $indicator->numerator ?? 'Jumlah pelayanan kesehatan yang memenuhi standar') }}</textarea>
                                @error('numerator')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="denominator" class="form-label">
                                    Penyebut (Denominator)
                                </label>
                                <textarea class="form-control @error('denominator') is-invalid @enderror" 
                                          id="denominator" name="denominator" rows="2" 
                                          placeholder="Jelaskan penyebut dalam rumus">{{ old('denominator', $indicator->denominator ?? 'Total jumlah pelayanan kesehatan') }}</textarea>
                                @error('denominator')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="calculation_method" class="form-label">
                            Metode Perhitungan
                        </label>
                        <textarea class="form-control @error('calculation_method') is-invalid @enderror" 
                                  id="calculation_method" name="calculation_method" rows="3" 
                                  placeholder="Jelaskan secara detail metode perhitungan indikator ini">{{ old('calculation_method', $indicator->calculation_method ?? 'Perhitungan dilakukan dengan membagi jumlah pelayanan yang memenuhi standar dengan total pelayanan, dikalikan 100%') }}</textarea>
                        @error('calculation_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="data_source" class="form-label">
                            Sumber Data
                        </label>
                        <input type="text" class="form-control @error('data_source') is-invalid @enderror" 
                               id="data_source" name="data_source" value="{{ old('data_source', $indicator->data_source ?? 'Sistem Informasi Kesehatan, Laporan Bulanan') }}" 
                               placeholder="Contoh: Sistem Informasi Kesehatan, Laporan Bulanan">
                        @error('data_source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Validation & Documents -->
        <div class="form-step" id="step3" style="display: none;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-check-circle"></i> Validasi & Verifikasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="validation_frequency" class="form-label">
                                    Frekuensi Validasi
                                </label>
                                <select class="form-select @error('validation_frequency') is-invalid @enderror" 
                                        id="validation_frequency" name="validation_frequency">
                                    <option value="">Pilih Frekuensi</option>
                                    <option value="monthly" {{ old('validation_frequency', $indicator->validation_frequency ?? 'quarterly') == 'monthly' ? 'selected' : '' }}>
                                        Bulanan
                                    </option>
                                    <option value="quarterly" {{ old('validation_frequency', $indicator->validation_frequency ?? 'quarterly') == 'quarterly' ? 'selected' : '' }}>
                                        Triwulan
                                    </option>
                                    <option value="semester" {{ old('validation_frequency', $indicator->validation_frequency ?? 'quarterly') == 'semester' ? 'selected' : '' }}>
                                        Semester
                                    </option>
                                    <option value="yearly" {{ old('validation_frequency', $indicator->validation_frequency ?? 'quarterly') == 'yearly' ? 'selected' : '' }}>
                                        Tahunan
                                    </option>
                                </select>
                                @error('validation_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsible_person" class="form-label">
                                    Penanggung Jawab
                                </label>
                                <input type="text" class="form-control @error('responsible_person') is-invalid @enderror" 
                                       id="responsible_person" name="responsible_person" value="{{ old('responsible_person', $indicator->responsible_person ?? 'Dr. Ratna Sari, M.Kes') }}" 
                                       placeholder="Nama penanggung jawab indikator">
                                @error('responsible_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Catatan tambahan untuk indikator ini">{{ old('notes', $indicator->notes ?? 'Indikator ini merupakan indikator prioritas untuk tahun 2024') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paperclip"></i> Dokumen Pendukung
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="supporting_documents" class="form-label">
                            Upload Dokumen Baru
                        </label>
                        <input type="file" class="form-control @error('supporting_documents') is-invalid @enderror" 
                               id="supporting_documents" name="supporting_documents[]" multiple 
                               accept=".pdf,.doc,.docx,.xls,.xlsx" onchange="previewFiles()">
                        @error('supporting_documents')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Format yang diizinkan: PDF, DOC, DOCX, XLS, XLSX. Maksimal 5MB per file.
                        </small>
                    </div>

                    <div id="filePreview" class="file-preview mb-3"></div>

                    <!-- Existing Documents -->
                    <div class="existing-documents mb-3">
                        <h6 class="mb-2">Dokumen Saat Ini</h6>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <span>Pedoman Teknis Indikator Kinerja.pdf</span>
                                    <small class="text-muted d-block">Diupload pada: 15 Januari 2024</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Hapus" onclick="deleteDocument(1)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    <span>Template Laporan Kinerja.xlsx</span>
                                    <small class="text-muted d-block">Diupload pada: 20 Januari 2024</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="#" class="btn btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Hapus" onclick="deleteDocument(2)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="document_description" class="form-label">
                            Deskripsi Dokumen
                        </label>
                        <textarea class="form-control @error('document_description') is-invalid @enderror" 
                                  id="document_description" name="document_description" rows="2" 
                                  placeholder="Jelaskan isi dan kegunaan dokumen yang diupload">{{ old('document_description') }}</textarea>
                        @error('document_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="character-count">
                            <small class="text-muted">
                                <span id="documentDescriptionCount">{{ strlen(old('document_description', '')) }}</span>/200 karakter
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Review & Update -->
        <div class="form-step" id="step4" style="display: none;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye"></i> Review Perubahan Indikator
                    </h5>
                </div>
                <div class="card-body">
                    <div class="review-section">
                        <h6 class="review-title">Informasi Dasar</h6>
                        <div class="review-content" id="reviewBasicInfo">
                            <!-- Content will be filled dynamically -->
                        </div>
                    </div>

                    <div class="review-section">
                        <h6 class="review-title">Target & Formula</h6>
                        <div class="review-content" id="reviewTargetFormula">
                            <!-- Content will be filled dynamically -->
                        </div>
                    </div>

                    <div class="review-section">
                        <h6 class="review-title">Validasi & Dokumen</h6>
                        <div class="review-content" id="reviewValidation">
                            <!-- Content will be filled dynamically -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-paper-plane"></i> Update Indikator
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="change_reason" class="form-label">
                            Alasan Perubahan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('change_reason') is-invalid @enderror" 
                                  id="change_reason" name="change_reason" rows="3" required 
                                  placeholder="Jelaskan alasan mengapa indikator ini perlu diubah">{{ old('change_reason') }}</textarea>
                        @error('change_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Alasan perubahan akan dicatat dalam audit trail untuk keperluan dokumentasi.
                        </small>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmation" name="confirmation" required>
                        <label class="form-check-label" for="confirmation">
                            Saya menyatakan bahwa semua perubahan yang dilakukan adalah benar dan dapat dipertanggungjawabkan.
                        </label>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Perubahan pada indikator akan memerlukan persetujuan kembali dari pihak berwenang sebelum dapat diterapkan.
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="form-navigation mt-4">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Sebelumnya
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                        Selanjutnya <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="fas fa-save"></i> Update Indikator
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Change History Modal -->
    <div class="modal fade" id="changeHistoryModal" tabindex="-1" aria-labelledby="changeHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeHistoryModalLabel">Riwayat Perubahan Indikator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Versi 2.1 - 15 Maret 2024</h6>
                                <p><strong>Diubah oleh:</strong> Siti Nurhaliza, S.Kom</p>
                                <p><strong>Alasan:</strong> Update target tahunan sesuai dengan RPJMD terbaru</p>
                                <p><strong>Perubahan:</strong> Target dari 80% menjadi 85%</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6>Versi 2.0 - 10 Februari 2024</h6>
                                <p><strong>Diubah oleh:</strong> Dr. Ratna Sari, M.Kes</p>
                                <p><strong>Alasan:</strong> Penambahan deskripsi dan metode perhitungan</p>
                                <p><strong>Perubahan:</strong> Deskripsi indikator dan formula perhitungan</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6>Versi 1.0 - 20 Januari 2024</h6>
                                <p><strong>Dibuat oleh:</strong> Dr. Andi Wijaya, M.Si</p>
                                <p><strong>Status:</strong> Indikator baru dibuat</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Indicators Edit Styles */
.sakip-indicators-edit {
    padding: 20px 0;
}

.page-header {
    background: var(--sakip-primary-light);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border-left: 4px solid var(--sakip-primary);
}

.page-title {
    color: var(--sakip-primary-dark);
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 5px;
}

/* Audit Trail Info */
.card.border-warning {
    border-color: var(--sakip-warning) !important;
}

.card-header.bg-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

/* Progress Indicator */
.progress-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.progress-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: var(--sakip-border);
    z-index: 1;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
    flex: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--sakip-light);
    color: var(--sakip-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background: var(--sakip-primary);
    color: white;
}

.progress-step.completed .step-number {
    background: var(--sakip-success);
    color: white;
}

.step-label {
    font-size: 0.85rem;
    color: var(--sakip-text-muted);
    text-align: center;
    font-weight: 500;
}

.progress-step.active .step-label {
    color: var(--sakip-primary-dark);
    font-weight: 600;
}

/* Card Styles */
.card {
    border: 1px solid var(--sakip-border);
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.card-header {
    background: var(--sakip-light);
    border-bottom: 1px solid var(--sakip-border);
    border-radius: 12px 12px 0 0 !important;
    padding: 15px 20px;
}

.card-title {
    color: var(--sakip-primary-dark);
    font-weight: 600;
}

/* Form Styles */
.form-label {
    font-weight: 600;
    color: var(--sakip-text-dark);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-color: var(--sakip-border);
    border-radius: 6px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--sakip-primary);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: var(--sakip-danger);
}

.invalid-feedback {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

/* Character Count */
.character-count {
    text-align: right;
    margin-top: 0.25rem;
}

/* Input Group */
.input-group-text {
    background: var(--sakip-light);
    border-color: var(--sakip-border);
    color: var(--sakip-text-muted);
}

/* File Preview */
.file-preview {
    border: 2px dashed var(--sakip-border);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: var(--sakip-light);
}

.file-preview-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    background: white;
    border: 1px solid var(--sakip-border);
    border-radius: 6px;
    margin-bottom: 8px;
}

.file-preview-item:last-child {
    margin-bottom: 0;
}

/* Existing Documents */
.existing-documents {
    background: var(--sakip-light);
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--sakip-border);
}

.list-group-item {
    border: 1px solid var(--sakip-border);
    margin-bottom: 8px;
    border-radius: 6px !important;
}

.list-group-item:last-child {
    margin-bottom: 0;
}

/* Review Section */
.review-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--sakip-border);
}

.review-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.review-title {
    color: var(--sakip-primary-dark);
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.review-content {
    background: var(--sakip-light);
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid var(--sakip-primary);
}

.review-item {
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.review-item:last-child {
    margin-bottom: 0;
}

.review-label {
    font-weight: 600;
    color: var(--sakip-text-dark);
    min-width: 150px;
}

.review-value {
    color: var(--sakip-text-muted);
    text-align: right;
    flex: 1;
    margin-left: 15px;
}

/* Form Navigation */
.form-navigation {
    padding: 20px 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--sakip-primary);
    border-color: var(--sakip-primary);
}

.btn-primary:hover {
    background: var(--sakip-primary-dark);
    border-color: var(--sakip-primary-dark);
}

.btn-success {
    background: var(--sakip-success);
    border-color: var(--sakip-success);
}

.btn-success:hover {
    background: #218838;
    border-color: #218838;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--sakip-border);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px var(--sakip-border);
}

.timeline-marker.bg-success {
    background: var(--sakip-success) !important;
    box-shadow: 0 0 0 2px var(--sakip-success) !important;
}

.timeline-marker.bg-info {
    background: var(--sakip-info) !important;
    box-shadow: 0 0 0 2px var(--sakip-info) !important;
}

.timeline-marker.bg-primary {
    background: var(--sakip-primary) !important;
    box-shadow: 0 0 0 2px var(--sakip-primary) !important;
}

.timeline-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--sakip-border);
}

.timeline-content h6 {
    color: var(--sakip-primary-dark);
    font-weight: 600;
    margin-bottom: 8px;
}

.timeline-content p {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

/* Alert */
.alert-warning {
    background: var(--sakip-warning-light);
    border-color: var(--sakip-warning);
    color: var(--sakip-warning-dark);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .progress-indicator {
        flex-direction: column;
        gap: 20px;
    }
    
    .progress-indicator::before {
        display: none;
    }
    
    .progress-step {
        flex-direction: row;
        justify-content: flex-start;
        gap: 15px;
    }
    
    .step-label {
        text-align: left;
    }
    
    .form-navigation .row {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-navigation .col-md-6.text-end {
        text-align: left !important;
    }
    
    .review-item {
        flex-direction: column;
        gap: 5px;
    }
    
    .review-value {
        text-align: left;
        margin-left: 0;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -18px;
    }
}

@media print {
    .form-navigation,
    .progress-indicator,
    .modal {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .timeline {
        padding-left: 20px !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Global variables
let currentStep = 1;
const totalSteps = 4;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    updateStepDisplay();
    updateCharacterCounts();
    updateCategoryDescription();
});

// Event Listeners
function initializeEventListeners() {
    // Character count for text inputs
    ['name', 'description', 'document_description'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                updateCharacterCount(fieldId);
            });
        }
    });

    // Form validation on submit
    const form = document.getElementById('indicatorForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
            }
        });
    }

    // Confirmation checkbox
    const confirmation = document.getElementById('confirmation');
    if (confirmation) {
        confirmation.addEventListener('change', function() {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = !this.checked;
            }
        });
    }

    // Change history button (add to header)
    const header = document.querySelector('.page-header .col-sm-4');
    if (header) {
        const changeHistoryBtn = document.createElement('button');
        changeHistoryBtn.className = 'btn btn-outline-info btn-sm ms-2';
        changeHistoryBtn.innerHTML = '<i class="fas fa-history"></i> Riwayat';
        changeHistoryBtn.onclick = showChangeHistory;
        header.appendChild(changeHistoryBtn);
    }
}

// Step navigation
function changeStep(direction) {
    if (direction > 0 && !validateCurrentStep()) {
        return;
    }

    const newStep = currentStep + direction;
    if (newStep >= 1 && newStep <= totalSteps) {
        // Hide current step
        document.getElementById(`step${currentStep}`).style.display = 'none';
        
        // Show new step
        currentStep = newStep;
        document.getElementById(`step${currentStep}`).style.display = 'block';
        
        // Update step display
        updateStepDisplay();
        
        // Update review if going to step 4
        if (currentStep === 4) {
            updateReviewData();
        }
    }
}

// Update step display
function updateStepDisplay() {
    // Update progress indicator
    document.querySelectorAll('.progress-step').forEach((step, index) => {
        if (index + 1 < currentStep) {
            step.classList.add('completed');
            step.classList.remove('active');
        } else if (index + 1 === currentStep) {
            step.classList.add('active');
            step.classList.remove('completed');
        } else {
            step.classList.remove('active', 'completed');
        }
    });

    // Update navigation buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    if (prevBtn) {
        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
    }

    if (nextBtn && submitBtn) {
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }
    }
}

// Validate current step
function validateCurrentStep() {
    const currentStepElement = document.getElementById(`step${currentStep}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Update character count
function updateCharacterCount(fieldId) {
    const field = document.getElementById(fieldId);
    const countElement = document.getElementById(fieldId + 'Count');
    
    if (field && countElement) {
        const maxLength = field.getAttribute('maxlength') || 500;
        const currentLength = field.value.length;
        countElement.textContent = currentLength;
        
        // Change color if approaching limit
        if (currentLength > maxLength * 0.8) {
            countElement.style.color = 'var(--sakip-warning)';
        } else {
            countElement.style.color = 'var(--sakip-text-muted)';
        }
    }
}

// Update all character counts
function updateCharacterCounts() {
    ['name', 'description', 'document_description'].forEach(fieldId => {
        updateCharacterCount(fieldId);
    });
}

// Generate code
function generateCode() {
    const category = document.getElementById('category').value;
    const year = document.getElementById('year').value;
    
    if (category && year) {
        const categoryMap = {
            'iku': 'U',
            'ikk': 'K',
            'ikt': 'T',
            'iks': 'S'
        };
        
        const categoryCode = categoryMap[category] || 'X';
        const randomNumber = Math.floor(Math.random() * 900) + 100;
        const code = `${categoryCode}-${randomNumber}`;
        
        document.getElementById('code').value = code;
        showToast('Kode indikator telah digenerate', 'success');
    } else {
        showToast('Pilih kategori dan tahun terlebih dahulu', 'warning');
    }
}

// Update category description
function updateCategoryDescription() {
    const category = document.getElementById('category').value;
    const descriptionElement = document.getElementById('categoryDescription');
    
    const descriptions = {
        'iku': 'Indikator Kinerja Utama: Mengukur hasil utama dari unit kerja',
        'ikk': 'Indikator Kinerja Kegiatan: Mengukur hasil dari kegiatan tertentu',
        'ikt': 'Indikator Kinerja Turunan: Indikator yang diturunkan dari IKU',
        'iks': 'Indikator Kinerja Strategis: Mengukur pencapaian strategis organisasi'
    };
    
    if (descriptionElement) {
        descriptionElement.textContent = descriptions[category] || 'Pilih kategori yang sesuai dengan jenis indikator';
    }
}

// Update target fields
function updateTargetFields() {
    const targetType = document.getElementById('target_type').value;
    const targetValue = document.getElementById('target_value');
    
    if (targetType === 'percentage') {
        targetValue.max = 100;
        targetValue.placeholder = '0-100';
    } else {
        targetValue.removeAttribute('max');
        targetValue.placeholder = 'Masukkan nilai target';
    }
}

// Preview files
function previewFiles() {
    const fileInput = document.getElementById('supporting_documents');
    const previewElement = document.getElementById('filePreview');
    
    if (!fileInput || !previewElement) return;
    
    previewElement.innerHTML = '';
    
    if (fileInput.files.length > 0) {
        Array.from(fileInput.files).forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            fileItem.innerHTML = `
                <div>
                    <i class="fas fa-file-pdf text-danger"></i>
                    <span>${file.name}</span>
                    <small class="text-muted">(${formatFileSize(file.size)})</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewElement.appendChild(fileItem);
        });
    } else {
        previewElement.innerHTML = '<p class="text-muted">Belum ada file yang dipilih</p>';
    }
}

// Delete document
function deleteDocument(id) {
    if (confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
        showToast('Dokumen akan dihapus setelah disimpan', 'info');
        // Remove from DOM
        const documentElement = event.target.closest('.list-group-item');
        if (documentElement) {
            documentElement.style.opacity = '0.5';
            documentElement.style.textDecoration = 'line-through';
        }
    }
}

// Show change history
function showChangeHistory() {
    const modal = new bootstrap.Modal(document.getElementById('changeHistoryModal'));
    modal.show();
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Update review data
function updateReviewData() {
    // Basic Info
    const basicInfo = document.getElementById('reviewBasicInfo');
    if (basicInfo) {
        basicInfo.innerHTML = `
            <div class="review-item">
                <span class="review-label">Kode Indikator:</span>
                <span class="review-value">${document.getElementById('code').value || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Nama Indikator:</span>
                <span class="review-value">${document.getElementById('name').value || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Kategori:</span>
                <span class="review-value">${getCategoryName(document.getElementById('category').value) || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Unit Kerja:</span>
                <span class="review-value">${getDepartmentName(document.getElementById('department_id').value) || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Tahun:</span>
                <span class="review-value">${document.getElementById('year').value || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Deskripsi:</span>
                <span class="review-value">${document.getElementById('description').value || '-'}</span>
            </div>
        `;
    }

    // Target & Formula
    const targetFormula = document.getElementById('reviewTargetFormula');
    if (targetFormula) {
        targetFormula.innerHTML = `
            <div class="review-item">
                <span class="review-label">Nilai Target:</span>
                <span class="review-value">${document.getElementById('target_value').value || '-'}%</span>
            </div>
            <div class="review-item">
                <span class="review-label">Tipe Target:</span>
                <span class="review-value">${getTargetTypeName(document.getElementById('target_type').value) || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Arah Target:</span>
                <span class="review-value">${getTargetDirectionName(document.getElementById('target_direction').value) || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Sumber Data:</span>
                <span class="review-value">${document.getElementById('data_source').value || '-'}</span>
            </div>
        `;
    }

    // Validation
    const validation = document.getElementById('reviewValidation');
    if (validation) {
        const fileCount = document.getElementById('supporting_documents').files.length;
        validation.innerHTML = `
            <div class="review-item">
                <span class="review-label">Frekuensi Validasi:</span>
                <span class="review-value">${getFrequencyName(document.getElementById('validation_frequency').value) || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Penanggung Jawab:</span>
                <span class="review-value">${document.getElementById('responsible_person').value || '-'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Dokumen Baru:</span>
                <span class="review-value">${fileCount > 0 ? fileCount + ' file' : 'Tidak ada'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Alasan Perubahan:</span>
                <span class="review-value">${document.getElementById('change_reason').value || '-'}</span>
            </div>
        `;
    }
}

// Helper functions for display names
function getCategoryName(value) {
    const names = {
        'iku': 'IKU (Indikator Kinerja Utama)',
        'ikk': 'IKK (Indikator Kinerja Kegiatan)',
        'ikt': 'IKT (Indikator Kinerja Turunan)',
        'iks': 'IKS (Indikator Kinerja Strategis)'
    };
    return names[value] || value;
}

function getDepartmentName(value) {
    const names = {
        '1': 'Dinas Kesehatan',
        '2': 'Dinas Pendidikan',
        '3': 'Dinas Sosial',
        '4': 'Dinas PU',
        '5': 'Dinas Perhubungan'
    };
    return names[value] || value;
}

function getTargetTypeName(value) {
    const names = {
        'percentage': 'Persentase (%)',
        'number': 'Angka',
        'ratio': 'Rasio',
        'index': 'Indeks'
    };
    return names[value] || value;
}

function getTargetDirectionName(value) {
    const names = {
        'higher_better': 'Lebih Tinggi Lebih Baik',
        'lower_better': 'Lebih Rendah Lebih Baik',
        'exact': 'Tepat Sasaran'
    };
    return names[value] || value;
}

function getFrequencyName(value) {
    const names = {
        'monthly': 'Bulanan',
        'quarterly': 'Triwulan',
        'semester': 'Semester',
        'yearly': 'Tahunan'
    };
    return names[value] || value;
}

// Toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    const container = document.querySelector('.toast-container');
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    const toast = new bootstrap.Toast(container.lastElementChild);
    toast.show();
    
    // Remove toast after it's hidden
    container.lastElementChild.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Initialize tooltips and other interactive elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);