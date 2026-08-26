@extends('layouts.app')

@section('title', 'Edit Kegiatan')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Kegiatan
        </h1>
        <a href="{{ route('sakip.kegiatan.show', $kegiatan) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Kegiatan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sakip.kegiatan.update', $kegiatan) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                            {{ old('program_id', $kegiatan->program_id) == $prog->id ? 'selected' : '' }}>
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
                                   id="kode_kegiatan" name="kode_kegiatan" value="{{ old('kode_kegiatan', $kegiatan->kode_kegiatan) }}"
                                   placeholder="Contoh: KEG-001" maxlength="50" required>
                            @error('kode_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nama Kegiatan -->
                        <div class="form-group mb-3">
                            <label for="nama_kegiatan" class="form-label">
                                Nama Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                   id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}"
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
                                      placeholder="Jelaskan deskripsi kegiatan">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
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
                                       id="anggaran" name="anggaran" value="{{ old('anggaran', $kegiatan->anggaran) }}"
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
                                   id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $kegiatan->tanggal_mulai?->format('Y-m-d')) }}">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="form-group mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $kegiatan->tanggal_selesai?->format('Y-m-d')) }}">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Penanggung Jawab -->
                        <div class="form-group mb-3">
                            <label for="penanggung_jawab" class="form-label">Penanggung Jawab</label>
                            <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror"
                                   id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab', $kegiatan->penanggung_jawab) }}"
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
                                <option value="draft" {{ old('status', $kegiatan->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ old('status', $kegiatan->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ old('status', $kegiatan->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('sakip.kegiatan.show', $kegiatan) }}" class="btn btn-secondary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Kegiatan</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Kode:</strong> {{ $kegiatan->kode_kegiatan }}</p>
                    <p class="mb-2"><strong>Program:</strong> {{ $kegiatan->program->nama_program }}</p>
                    <p class="mb-2"><strong>Anggaran:</strong> Rp {{ number_format($kegiatan->anggaran, 0, ',', '.') }}</p>
                    <p class="mb-2"><strong>Status:</strong>
                        @if($kegiatan->status == 'aktif')
                            <span class="badge badge-success">Aktif</span>
                        @elseif($kegiatan->status == 'selesai')
                            <span class="badge badge-info">Selesai</span>
                        @else
                            <span class="badge badge-secondary">Draft</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('sakip.kegiatan.show', $kegiatan) }}" class="btn btn-info btn-sm btn-block mb-2">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="{{ route('sakip.kegiatan.index') }}" class="btn btn-secondary btn-sm btn-block">
                        <i class="fas fa-list"></i> Daftar Kegiatan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
