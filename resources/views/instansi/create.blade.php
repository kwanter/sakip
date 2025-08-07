@extends('layouts.app')

@section('title', 'Tambah Instansi')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Instansi</h1>
        <a href="{{ route('instansi.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Tambah Instansi</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('instansi.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode_instansi" class="form-label">Kode Instansi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_instansi') is-invalid @enderror" 
                                   id="kode_instansi" name="kode_instansi" value="{{ old('kode_instansi') }}" 
                                   placeholder="Contoh: INST001" maxlength="20" required>
                            @error('kode_instansi')
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
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nama_instansi" class="form-label">Nama Instansi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_instansi') is-invalid @enderror" 
                           id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" 
                           placeholder="Masukkan nama instansi" required>
                    @error('nama_instansi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" 
                              id="alamat" name="alamat" rows="3" 
                              placeholder="Masukkan alamat lengkap instansi" required>{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kepala_instansi" class="form-label">Kepala Instansi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kepala_instansi') is-invalid @enderror" 
                                   id="kepala_instansi" name="kepala_instansi" value="{{ old('kepala_instansi') }}" 
                                   placeholder="Nama kepala instansi" required>
                            @error('kepala_instansi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nip_kepala" class="form-label">NIP Kepala Instansi</label>
                            <input type="text" class="form-control @error('nip_kepala') is-invalid @enderror" 
                                   id="nip_kepala" name="nip_kepala" value="{{ old('nip_kepala') }}" 
                                   placeholder="NIP kepala instansi" maxlength="20">
                            @error('nip_kepala')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telepon" class="form-label">Telepon</label>
                            <input type="text" class="form-control @error('telepon') is-invalid @enderror" 
                                   id="telepon" name="telepon" value="{{ old('telepon') }}" 
                                   placeholder="Nomor telepon" maxlength="20">
                            @error('telepon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="alamat@email.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                   id="website" name="website" value="{{ old('website') }}" 
                                   placeholder="https://website.com">
                            @error('website')
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
                    <a href="{{ route('instansi.index') }}" class="btn btn-secondary ml-2">
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
    // Auto-generate kode instansi
    $('#nama_instansi').on('input', function() {
        let nama = $(this).val();
        if (nama && !$('#kode_instansi').val()) {
            let kode = nama.split(' ').map(word => word.charAt(0).toUpperCase()).join('').substring(0, 5);
            let timestamp = Date.now().toString().slice(-3);
            $('#kode_instansi').val(kode + timestamp);
        }
    });
    
    // Format phone number
    $('#telepon').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
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
        
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
        }
    });
});
</script>
@endpush