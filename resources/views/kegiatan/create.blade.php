@extends('layouts.app')

@section('title', 'Tambah Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Kegiatan</h1>
        <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('kegiatan.store') }}" method="POST" id="kegiatanForm">
                        @csrf
                        
                        <!-- Program Selection -->
                        <div class="form-group">
                            <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                            <select class="form-control @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id" required>
                                <option value="">Pilih Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->nama_program }} - {{ $program->instansi->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Activity Code -->
                        <div class="form-group">
                            <label for="kode_kegiatan" class="form-label">Kode Kegiatan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('kode_kegiatan') is-invalid @enderror" 
                                       id="kode_kegiatan" name="kode_kegiatan" value="{{ old('kode_kegiatan') }}" 
                                       placeholder="Contoh: KEG-001" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="generateCode">
                                        <i class="fas fa-magic"></i> Generate
                                    </button>
                                </div>
                            </div>
                            @error('kode_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Kode unik untuk identifikasi kegiatan</small>
                        </div>

                        <!-- Activity Name -->
                        <div class="form-group">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" 
                                   placeholder="Masukkan nama kegiatan" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Deskripsi detail kegiatan (opsional)">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Budget -->
                        <div class="form-group">
                            <label for="anggaran" class="form-label">Anggaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control @error('anggaran') is-invalid @enderror" 
                                       id="anggaran" name="anggaran" value="{{ old('anggaran') }}" 
                                       placeholder="0" required>
                            </div>
                            @error('anggaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan anggaran dalam rupiah</small>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                           id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}">
                                    @error('tanggal_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                           id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}">
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Responsible Person -->
                        <div class="form-group">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror" 
                                   id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" 
                                   placeholder="Nama penanggung jawab kegiatan" required>
                            @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="berjalan" {{ old('status') == 'berjalan' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="tunda" {{ old('status') == 'tunda' ? 'selected' : '' }}>Tunda</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Panduan Pengisian</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Kode Kegiatan</h6>
                        <p class="small text-muted">Gunakan format yang konsisten, misalnya KEG-001, KEG-002, dst. Klik tombol Generate untuk membuat kode otomatis.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Anggaran</h6>
                        <p class="small text-muted">Masukkan nilai anggaran dalam rupiah. Sistem akan memformat angka secara otomatis.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Periode Kegiatan</h6>
                        <p class="small text-muted">Tanggal mulai dan selesai bersifat opsional, namun disarankan untuk diisi untuk tracking yang lebih baik.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Status</h6>
                        <ul class="small text-muted">
                            <li><strong>Aktif:</strong> Kegiatan sedang berjalan</li>
                            <li><strong>Selesai:</strong> Kegiatan telah selesai</li>
                            <li><strong>Tunda:</strong> Kegiatan ditangguhkan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Currency formatting
    Helpers.initialCurrencyFormat('#anggaran');
    Helpers.attachCurrencyInputFormatting('#anggaran');
    Helpers.sanitizeCurrencyBeforeSubmit('#kegiatanForm', '#anggaran');

    // Generate activity code
    $('#generateCode').click(function() {
        const programId = $('#program_id').val();
        if (!programId) {
            alert('Pilih program terlebih dahulu');
            return;
        }
        const timestamp = Date.now().toString().slice(-4);
        const code = 'KEG-' + String(programId).padStart(2, '0') + '-' + timestamp;
        $('#kode_kegiatan').val(code);
    });

    // Date validation
    Helpers.validateDateRange('#tanggal_mulai', '#tanggal_selesai');

    // Basic required validation
    $('#kegiatanForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['program_id', 'kode_kegiatan', 'nama_kegiatan', 'anggaran', 'penanggung_jawab', 'status'];
        requiredFields.forEach(function(field) {
            const el = document.getElementById(field);
            if (!el || !el.value || el.value.trim() === '') {
                isValid = false;
                if (el) el.classList.add('is-invalid');
            } else {
                el.classList.remove('is-invalid');
            }
        });
        const budget = Helpers.stripCurrency(document.getElementById('anggaran').value);
        if (budget === '' || parseInt(budget) <= 0) {
            isValid = false;
            document.getElementById('anggaran').classList.add('is-invalid');
            alert('Anggaran harus lebih dari 0');
        }
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi');
        }
    });
});
</script>
@endpush