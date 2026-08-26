@extends('layouts.modern')

@section('title', 'Tambah Instansi')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.instansi.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Tambah Instansi</h1>
                <p class="page-header-subtitle">Form tambah data instansi pemerintahan</p>
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
            <form action="{{ route('sakip.instansi.store') }}" method="POST">
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
                                <label for="kode_instansi" class="form-label">Kode Instansi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode_instansi') is-invalid @enderror"
                                       id="kode_instansi" name="kode_instansi" value="{{ old('kode_instansi') }}" required
                                       placeholder="Masukkan kode instansi">
                                @error('kode_instansi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="nama_instansi" class="form-label">Nama Instansi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_instansi') is-invalid @enderror"
                                       id="nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" required
                                       placeholder="Masukkan nama instansi">
                                @error('nama_instansi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror"
                                          id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Kontak -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-address-book"></i>
                        <span>Informasi Kontak</span>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control @error('telepon') is-invalid @enderror"
                                       id="telepon" name="telepon" value="{{ old('telepon') }}" placeholder="Contoh: (021) 1234567">
                                @error('telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" placeholder="email@instansi.go.id">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website') }}" placeholder="https://instansi.go.id">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Pimpinan -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-user-tie"></i>
                        <span>Pimpinan Instansi</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="kepala_instansi" class="form-label">Kepala Instansi</label>
                                <input type="text" class="form-control @error('kepala_instansi') is-invalid @enderror"
                                       id="kepala_instansi" name="kepala_instansi" value="{{ old('kepala_instansi') }}"
                                       placeholder="Nama kepala instansi">
                                @error('kepala_instansi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="nip_kepala" class="form-label">NIP Kepala Instansi</label>
                                <input type="text" class="form-control @error('nip_kepala') is-invalid @enderror"
                                       id="nip_kepala" name="nip_kepala" value="{{ old('nip_kepala') }}"
                                       placeholder="NIP kepala instansi">
                                @error('nip_kepala')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Status -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-toggle-on"></i>
                        <span>Status</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="status" class="form-label">Status Instansi <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.instansi.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span class="ms-1">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
