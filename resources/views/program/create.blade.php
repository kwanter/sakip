@extends('layouts.app')

@section('title', 'Tambah Program')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Program</h1>
        <a href="{{ route('program.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Program</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('program.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="instansi_id" class="form-label">Instansi <span class="text-danger">*</span></label>
                            <select class="form-control @error('instansi_id') is-invalid @enderror" 
                                    id="instansi_id" name="instansi_id" required>
                                <option value="">Pilih Instansi</option>
                                @foreach($instansis as $instansi)
                                    <option value="{{ $instansi->id }}" 
                                            {{ old('instansi_id', request('instansi_id')) == $instansi->id ? 'selected' : '' }}>
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
                            <div class="input-group">
                                <input type="text" class="form-control @error('kode_program') is-invalid @enderror" 
                                       id="kode_program" name="kode_program" 
                                       value="{{ old('kode_program') }}" 
                                       placeholder="Contoh: PROG001" maxlength="20" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="generateKodeProgram()" title="Generate Otomatis">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
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
                           value="{{ old('nama_program') }}" 
                           placeholder="Masukkan nama program" required>
                    @error('nama_program')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                              id="deskripsi" name="deskripsi" rows="4" 
                              placeholder="Masukkan deskripsi program">{{ old('deskripsi') }}</textarea>
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
                                       value="{{ old('anggaran') }}" 
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
                                   value="{{ old('tahun_mulai', date('Y')) }}" 
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
                                   value="{{ old('tahun_selesai', date('Y') + 1) }}" 
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
                                   value="{{ old('penanggung_jawab') }}" 
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
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
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
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <a href="{{ route('program.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Format currency input
    $('#anggaran').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            $(this).val(parseInt(value).toLocaleString('id-ID'));
        }
    });
    
    // Validate year range
    $('#tahun_mulai, #tahun_selesai').on('change', function() {
        let tahunMulai = parseInt($('#tahun_mulai').val());
        let tahunSelesai = parseInt($('#tahun_selesai').val());
        
        if (tahunMulai && tahunSelesai && tahunSelesai < tahunMulai) {
            alert('Tahun selesai tidak boleh lebih kecil dari tahun mulai.');
            $('#tahun_selesai').val(tahunMulai + 1);
        }
    });
    
    // Validate form before submit
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Convert currency format back to number
        let anggaranValue = $('#anggaran').val().replace(/[^0-9]/g, '');
        $('#anggaran').val(anggaranValue);
        
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
        }
    });
});

function generateKodeProgram() {
    let instansiId = $('#instansi_id').val();
    if (!instansiId) {
        alert('Pilih instansi terlebih dahulu.');
        return;
    }
    
    // Generate simple code based on timestamp
    let timestamp = Date.now().toString().slice(-6);
    let kode = 'PROG' + timestamp;
    $('#kode_program').val(kode);
}
</script>
@endpush