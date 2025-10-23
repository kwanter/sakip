@extends('layouts.app')

@section('title', 'Tambah Sasaran Strategis')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bullseye"></i> Tambah Sasaran Strategis
        </h1>
        <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if($instansis->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Peringatan!</strong> Tidak ada data Instansi. Silakan tambahkan Instansi terlebih dahulu sebelum membuat Sasaran Strategis.
        <a href="{{ route('sakip.instansi.create') }}" class="btn btn-sm btn-primary ml-2">
            <i class="fas fa-plus"></i> Tambah Instansi
        </a>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Sasaran Strategis</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sakip.sasaran-strategis.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instansi_id">Instansi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('instansi_id') is-invalid @enderror"
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
                                <div class="form-group">
                                    <label for="kode_sasaran_strategis">Kode Sasaran Strategis <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_sasaran_strategis') is-invalid @enderror"
                                           id="kode_sasaran_strategis" name="kode_sasaran_strategis"
                                           value="{{ old('kode_sasaran_strategis') }}" required>
                                    @error('kode_sasaran_strategis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Contoh: SS-001</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nama_strategis">Nama Sasaran Strategis <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_strategis') is-invalid @enderror"
                                   id="nama_strategis" name="nama_strategis" value="{{ old('nama_strategis') }}" required>
                            @error('nama_strategis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                      id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Penjelasan detail tentang sasaran strategis ini</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" {{ $instansis->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('sakip.sasaran-strategis.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
