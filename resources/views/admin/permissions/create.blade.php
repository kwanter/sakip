@extends('layouts.app')

@section('title', 'Tambah Izin Baru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Tambah Izin Baru
        </h1>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-key"></i> Informasi Izin
                    </h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('admin.permissions.store') }}" method="POST">
                        @csrf

                        <!-- Permission Name -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label font-weight-bold">
                                Nama Izin <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('name') is-invalid @enderror"
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
                        <div class="form-group mb-3">
                            <label for="guard_name" class="form-label font-weight-bold">
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
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Simpan Izin
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Panduan Penamaan
                    </h6>
                </div>
                <div class="card-body small">
                    <p><strong>Gunakan format konsisten:</strong> <code>action-resource</code></p>

                    <h6 class="font-weight-bold mt-3">Action yang Umum:</h6>
                    <ul class="pl-3 mb-2">
                        <li><strong>view</strong> - Melihat/menampilkan</li>
                        <li><strong>create</strong> - Membuat baru</li>
                        <li><strong>edit</strong> - Mengedit</li>
                        <li><strong>delete</strong> - Menghapus</li>
                        <li><strong>approve</strong> - Menyetujui</li>
                        <li><strong>export</strong> - Mengekspor</li>
                    </ul>

                    <h6 class="font-weight-bold mt-3">Resource yang Umum:</h6>
                    <ul class="pl-3">
                        <li>indicator</li>
                        <li>target</li>
                        <li>data</li>
                        <li>user</li>
                        <li>role</li>
                        <li>permission</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-check-circle"></i> Contoh Izin
                    </h6>
                </div>
                <div class="card-body small">
                    <ul class="pl-3 mb-0">
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
