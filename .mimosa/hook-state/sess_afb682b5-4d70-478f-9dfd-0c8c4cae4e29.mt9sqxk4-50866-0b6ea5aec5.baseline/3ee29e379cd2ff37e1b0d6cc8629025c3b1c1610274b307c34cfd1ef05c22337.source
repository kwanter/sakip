@extends('layouts.modern')

@section('title', 'Tambah Sasaran Strategis')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Tambah Sasaran Strategis</h1>
                <p class="page-header-subtitle">Form tambah sasaran strategis instansi</p>
            </div>
        </div>
    </div>

    @if($instansis->isEmpty())
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle alert-icon"></i>
        <div>
            <strong>Peringatan!</strong> Tidak ada data Instansi. Silakan tambahkan Instansi terlebih dahulu.
            <a href="{{ route('sakip.instansi.create') }}" class="btn btn-sm btn-primary ms-2">
                <i class="fas fa-plus"></i> Tambah Instansi
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
            <form action="{{ route('sakip.sasaran-strategis.store') }}" method="POST">
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
                                        id="instansi_id" name="instansi_id" required {{ $instansis->isEmpty() ? 'disabled' : '' }}>
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
                            <div class="form-group mb-0">
                                <label for="kode_sasaran_strategis" class="form-label">Kode Sasaran Strategis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kode_sasaran_strategis') is-invalid @enderror"
                                       id="kode_sasaran_strategis" name="kode_sasaran_strategis"
                                       value="{{ old('kode_sasaran_strategis') }}" required placeholder="Contoh: SS-001">
                                @error('kode_sasaran_strategis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Detail -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-align-left"></i>
                        <span>Detail Sasaran</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="nama_strategis" class="form-label">Nama Sasaran Strategis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_strategis') is-invalid @enderror"
                                       id="nama_strategis" name="nama_strategis" value="{{ old('nama_strategis') }}" required
                                       placeholder="Masukkan nama sasaran strategis">
                                @error('nama_strategis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                          id="deskripsi" name="deskripsi" rows="4" placeholder="Penjelasan detail tentang sasaran strategis">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
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
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
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
                    <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary" {{ $instansis->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save"></i>
                        <span class="ms-1">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
