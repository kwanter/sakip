@extends('layouts.modern')

@section('title', 'Tambah Program')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.program.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Tambah Program</h1>
                <p class="page-header-subtitle">Form tambah program instansi</p>
            </div>
        </div>
    </div>

    @if($sasaranStrategis->isEmpty())
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle alert-icon"></i>
        <div>
            <strong>Peringatan!</strong> Tidak ada data Sasaran Strategis. Silakan tambahkan Sasaran Strategis terlebih dahulu.
            <a href="{{ route('sakip.sasaran-strategis.create') }}" class="btn btn-sm btn-primary ms-2">
                <i class="fas fa-plus"></i> Tambah Sasaran Strategis
            </a>
        </div>
    </div>
    @endif

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
            <form action="{{ route('sakip.program.store') }}" method="POST">
                @csrf

                <!-- Section: Informasi Dasar -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Informasi Dasar</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="instansi_id" class="form-label">Instansi <span class="text-danger">*</span></label>
                                <select class="form-select @error('instansi_id') is-invalid @enderror"
                                        id="instansi_id" name="instansi_id" required {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Pilih Instansi</option>
                                    @foreach($instansis as $instansi)
                                        <option value="{{ $instansi->id }}" {{ old('instansi_id') == $instansi->id ? 'selected' : '' }}>
                                            {{ $instansi->nama_instansi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instansi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="sasaran_strategis_id" class="form-label">Sasaran Strategis <span class="text-danger">*</span></label>
                                <select class="form-select @error('sasaran_strategis_id') is-invalid @enderror"
                                        id="sasaran_strategis_id" name="sasaran_strategis_id" required {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Pilih Instansi terlebih dahulu</option>
                                </select>
                                @error('sasaran_strategis_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="kode_program" class="form-label">Kode Program <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode_program') is-invalid @enderror"
                                       id="kode_program" name="kode_program" value="{{ old('kode_program') }}" required
                                       placeholder="Contoh: PRG-001">
                                @error('kode_program')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="nama_program" class="form-label">Nama Program <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_program') is-invalid @enderror"
                                       id="nama_program" name="nama_program" value="{{ old('nama_program') }}" required
                                       placeholder="Masukkan nama program">
                                @error('nama_program')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Detail Program -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-align-left"></i>
                        <span>Detail Program</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                          id="deskripsi" name="deskripsi" rows="4" placeholder="Penjelasan detail tentang program">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Anggaran & Waktu -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Anggaran & Waktu</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="anggaran" class="form-label">Anggaran <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('anggaran') is-invalid @enderror"
                                       id="anggaran" name="anggaran" value="{{ old('anggaran', 0) }}" min="0" step="1" required
                                       placeholder="0">
                                @error('anggaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Dalam Rupiah</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select class="form-select @error('tahun') is-invalid @enderror" id="tahun" name="tahun" required>
                                    <option value="">Pilih Tahun</option>
                                    @for($year = date('Y'); $year <= date('Y') + 5; $year++)
                                        <option value="{{ $year }}" {{ old('tahun', date('Y')) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('tahun')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Penanggung Jawab -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-user"></i>
                        <span>Penanggung Jawab</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                                <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror"
                                       id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}"
                                       placeholder="Nama penanggung jawab">
                                @error('penanggung_jawab')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.program.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary" {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save"></i>
                        <span class="ms-1">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const instansiSelect = document.getElementById('instansi_id');
    const sasaranSelect = document.getElementById('sasaran_strategis_id');

    // Cascade dropdown for Sasaran Strategis based on Instansi selection
    instansiSelect.addEventListener('change', function() {
        const instansiId = this.value;

        // Clear current options
        sasaranSelect.innerHTML = '<option value="">Loading...</option>';

        if (instansiId) {
            // Fetch Sasaran Strategis for selected Instansi
            fetch('/sakip/api/sasaran-strategis/by-instansi/' + instansiId, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                sasaranSelect.innerHTML = '<option value="">Pilih Sasaran Strategis</option>';

                if (data.length > 0) {
                    data.forEach(function(value) {
                        const option = document.createElement('option');
                        option.value = value.id;
                        option.textContent = value.nama_strategis;
                        sasaranSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Tidak ada sasaran strategis untuk instansi ini';
                    sasaranSelect.appendChild(option);
                }
            })
            .catch(error => {
                sasaranSelect.innerHTML = '<option value="">Error loading data</option>';
            });
        } else {
            sasaranSelect.innerHTML = '<option value="">Pilih Instansi terlebih dahulu</option>';
        }
    });

    // Format currency input
    const anggaranInput = document.getElementById('anggaran');
    anggaranInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
});
</script>
@endpush
@endsection
