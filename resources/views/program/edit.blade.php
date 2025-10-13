@extends('layouts.app')

@section('title', 'Edit Program')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Program</h1>
        <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Program</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('program.update', $program) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="instansi_id" class="form-label">Instansi <span class="text-danger">*</span></label>
                            <select class="form-control @error('instansi_id') is-invalid @enderror" 
                                    id="instansi_id" name="instansi_id" required>
                                <option value="">Pilih Instansi</option>
                                @foreach($instansis as $instansi)
                                    <option value="{{ $instansi->id }}" 
                                            {{ old('instansi_id', $program->instansi_id) == $instansi->id ? 'selected' : '' }}>
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
                        <div class="form-group">
                            <label for="kode_program" class="form-label">Kode Program <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_program') is-invalid @enderror" 
                                   id="kode_program" name="kode_program" 
                                   value="{{ old('kode_program', $program->kode_program) }}" 
                                   placeholder="Contoh: PROG001" maxlength="20" required>
                            @error('kode_program')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nama_program" class="form-label">Nama Program <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_program') is-invalid @enderror" 
                           id="nama_program" name="nama_program" 
                           value="{{ old('nama_program', $program->nama_program) }}" 
                           placeholder="Masukkan nama program" required>
                    @error('nama_program')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                              id="deskripsi" name="deskripsi" rows="4" 
                              placeholder="Masukkan deskripsi program">{{ old('deskripsi', $program->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="anggaran" class="form-label">Anggaran <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="text" class="form-control @error('anggaran') is-invalid @enderror" 
                                       id="anggaran" name="anggaran" 
                                       value="{{ old('anggaran', number_format($program->anggaran, 0, ',', '.')) }}" 
                                       placeholder="0" required>
                            </div>
                            @error('anggaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tahun_mulai" class="form-label">Tahun Mulai <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('tahun_mulai') is-invalid @enderror" 
                                   id="tahun_mulai" name="tahun_mulai" 
                                   value="{{ old('tahun_mulai', $program->tahun) }}" 
                                   min="2020" max="2030" required>
                            @error('tahun_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tahun_selesai" class="form-label">Tahun Selesai <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('tahun_selesai') is-invalid @enderror" 
                                   id="tahun_selesai" name="tahun_selesai" 
                                   value="{{ old('tahun_selesai', $program->tahun + 1) }}" 
                                   min="2020" max="2030" required>
                            @error('tahun_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror" 
                                   id="penanggung_jawab" name="penanggung_jawab" 
                                   value="{{ old('penanggung_jawab', $program->penanggung_jawab) }}" 
                                   placeholder="Nama penanggung jawab program" required>
                            @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="draft" {{ old('status', $program->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ old('status', $program->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ old('status', $program->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>
                
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('program.show', $program) }}" class="btn btn-info ml-2">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="{{ route('program.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Program Statistics -->
    @if($program->kegiatans->count() > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Statistik Program</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $program->kegiatans->count() }}</h4>
                        <p class="text-gray-600 mb-0">Total Kegiatan</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success">{{ $program->kegiatans->where('status', 'berjalan')->count() }}</h4>
                        <p class="text-gray-600 mb-0">Kegiatan Aktif</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info">{{ $program->kegiatans->where('status', 'selesai')->count() }}</h4>
                        <p class="text-gray-600 mb-0">Kegiatan Selesai</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $program->kegiatans->sum('anggaran') }}</h4>
                        <p class="text-gray-600 mb-0">Total Anggaran Kegiatan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Currency formatting
    Helpers.initialCurrencyFormat('#anggaran');
    Helpers.attachCurrencyInputFormatting('#anggaran');
    Helpers.sanitizeCurrencyBeforeSubmit('form', '#anggaran');

    // Year range validation
    Helpers.validateYearRange('#tahun_mulai', '#tahun_selesai');

    // Basic required validation
    $('form').on('submit', function(e) {
        let isValid = true;
        $('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
        }
    });
});
</script>
@endpush