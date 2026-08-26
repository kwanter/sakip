@extends('layouts.app')

@section('title', 'Tambah Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Tambah Kegiatan
        </h1>
        <a href="{{ route('sakip.kegiatan.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sakip.kegiatan.store') }}" method="POST">
                        @csrf

                        <!-- Program -->
                        <div class="form-group mb-3">
                            <label for="program_id" class="form-label">
                                Program <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('program_id') is-invalid @enderror"
                                    id="program_id" name="program_id" required>
                                <option value="">Pilih Program</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}"
                                            {{ (old('program_id') ?? $program?->id) == $prog->id ? 'selected' : '' }}>
                                        {{ $prog->nama_program }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kode Kegiatan -->
                        <div class="form-group mb-3">
                            <label for="kode_kegiatan" class="form-label">
                                Kode Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('kode_kegiatan') is-invalid @enderror"
                                   id="kode_kegiatan" name="kode_kegiatan" value="{{ old('kode_kegiatan') }}"
                                   placeholder="Contoh: KEG-001" maxlength="50" required>
                            @error('kode_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Kode unik untuk identifikasi kegiatan</small>
                        </div>

                        <!-- Nama Kegiatan -->
                        <div class="form-group mb-3">
                            <label for="nama_kegiatan" class="form-label">
                                Nama Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                   id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                   placeholder="Masukkan nama kegiatan" maxlength="255" required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                      id="deskripsi" name="deskripsi" rows="4"
                                      placeholder="Jelaskan deskripsi kegiatan">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Anggaran -->
                        <div class="form-group mb-3">
                            <label for="anggaran" class="form-label">
                                Anggaran <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('anggaran') is-invalid @enderror"
                                       id="anggaran" name="anggaran" value="{{ old('anggaran', 0) }}"
                                       placeholder="0" min="0" step="1000" required>
                            </div>
                            @error('anggaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="form-group mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="form-group mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Penanggung Jawab -->
                        <div class="form-group mb-3">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                            <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror"
                                   id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}"
                                   placeholder="Nama penanggung jawab kegiatan" maxlength="255">
                            @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group mb-4">
                            <label for="status" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Kegiatan
                            </button>
                            <a href="{{ route('sakip.kegiatan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Apa itu Kegiatan?</strong></p>
                    <p class="small text-muted mb-3">
                        Kegiatan adalah aktivitas atau pekerjaan operasional yang merupakan bagian dari sebuah Program. Setiap Kegiatan memiliki target, anggaran, dan indikator kinerja tersendiri.
                    </p>

                    <p class="mb-2"><strong>Panduan Pengisian:</strong></p>
                    <ul class="small text-muted">
                        <li>Pilih program yang akan digunakan sebagai induk kegiatan</li>
                        <li>Berikan kode unik untuk kegiatan</li>
                        <li>Isi nama kegiatan dengan jelas dan spesifik</li>
                        <li>Tentukan anggaran yang diperlukan</li>
                        <li>Tetapkan tanggal mulai dan selesai</li>
                        <li>Pilih penanggung jawab kegiatan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
