@extends('layouts.modern')

@section('title', 'Tambah Izin Baru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Tambah Izin Baru</h1>
                <p class="page-header-subtitle">Buat aksi spesifik baru dengan format penamaan yang konsisten.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
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
                        <i class="fas fa-key"></i> Informasi Izin
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

                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf

                        <!-- Permission Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Izin <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: create-indicator, edit-target, approve-data"
                                   required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">
                                Nama unik untuk izin. Gunakan format: <code>action-resource</code> (contoh: <code>create-indicator</code>, <code>edit-target</code>)
                            </small>
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
                            <small class="form-text text-muted">Opsional - nama guard untuk membedakan konteks autentikasi (biasanya 'web')</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Izin
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-info-circle"></i> Panduan Penamaan
                    </h2>
                </div>
                <div class="card-body small">
                    <p><strong>Gunakan format konsisten:</strong> <code>action-resource</code></p>

                    <h3 class="fs-6 fw-semibold mt-3">Action yang Umum:</h3>
                    <ul class="ps-3 mb-2">
                        <li><strong>view</strong> - Melihat/menampilkan</li>
                        <li><strong>create</strong> - Membuat baru</li>
                        <li><strong>edit</strong> - Mengedit</li>
                        <li><strong>delete</strong> - Menghapus</li>
                        <li><strong>approve</strong> - Menyetujui</li>
                        <li><strong>export</strong> - Mengekspor</li>
                    </ul>

                    <h3 class="fs-6 fw-semibold mt-3">Resource yang Umum:</h3>
                    <ul class="ps-3">
                        <li>indicator</li>
                        <li>target</li>
                        <li>data</li>
                        <li>user</li>
                        <li>role</li>
                        <li>permission</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-check-circle"></i> Contoh Izin
                    </h2>
                </div>
                <div class="card-body small">
                    <ul class="ps-3 mb-0">
                        <li><code>view-dashboard</code></li>
                        <li><code>create-indicator</code></li>
                        <li><code>edit-indicator</code></li>
                        <li><code>delete-indicator</code></li>
                        <li><code>create-target</code></li>
                        <li><code>approve-target</code></li>
                        <li><code>export-data</code></li>
                        <li><code>manage-users</code></li>
                        <li><code>manage-roles</code></li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-lightbulb"></i>
                <strong>Tips:</strong> Setelah membuat izin, tambahkan ke Role di halaman Manajemen Role agar pengguna dapat menggunakannya.
            </div>
        </div>
    </div>
</div>
@endsection
