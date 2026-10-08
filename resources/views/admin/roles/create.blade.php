@extends('layouts.modern')

@section('title', 'Tambah Role Baru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Tambah Role Baru</h1>
                <p class="page-header-subtitle">Buat kelompok izin baru untuk pengguna.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-shield-alt"></i> Informasi Role
                    </h2>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h2 class="fs-6"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h2>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <!-- Role Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Editor, Reviewer, Analyst"
                                   required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">Nama unik untuk role ini</small>
                        </div>

                        <!-- Guard Name -->
                        <div class="mb-3">
                            <label for="guard_name" class="form-label fw-semibold">
                                Guard Name
                            </label>
                            <input type="text"
                                   class="form-control @error('guard_name') is-invalid @enderror"
                                   id="guard_name"
                                   name="guard_name"
                                   value="{{ old('guard_name', 'web') }}"
                                   placeholder="web">
                            @error('guard_name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">Opsional - untuk membedakan guard (biasanya 'web')</small>
                        </div>

                        <!-- Permissions Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-key"></i> Pilih Izin
                            </label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($permissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           id="permission_{{ $permission->id }}"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <br>
                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <p class="text-muted mb-0">Tidak ada izin tersedia</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @error('permissions')
                                <small class="form-text text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted d-block mt-2">Pilih izin-izin yang akan diberikan ke role ini</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Panduan
                    </h2>
                </div>
                <div class="card-body small">
                    <h3 class="fs-6 fw-semibold">Cara Membuat Role:</h3>
                    <ol class="ps-3">
                        <li>Masukkan nama role yang deskriptif</li>
                        <li>Pilih izin-izin yang dibutuhkan</li>
                        <li>Klik "Simpan Role"</li>
                    </ol>

                    <hr>

                    <h3 class="fs-6 fw-semibold">Tips:</h3>
                    <ul class="ps-3 mb-0">
                        <li>Gunakan nama yang jelas dan deskriptif</li>
                        <li>Pilih izin minimal yang dibutuhkan</li>
                        <li>Anda dapat mengubah izin nanti</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-lightbulb"></i> Contoh Role
                    </h2>
                </div>
                <div class="card-body small">
                    <p><strong>Editor:</strong> Dapat membuat dan mengedit konten</p>
                    <p><strong>Reviewer:</strong> Dapat melihat dan mengomentari konten</p>
                    <p><strong>Admin:</strong> Akses penuh ke semua fitur</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
