@extends('layouts.modern')

@section('title', 'Edit Izin: ' . $permission->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Edit Izin: {{ $permission->name }}</h1>
                <p class="page-header-subtitle">Perbarui nama izin dan guard-nya.</p>
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
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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

                    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Permission Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Izin <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $permission->name) }}"
                                   required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">
                                Gunakan format: <code>action-resource</code>
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
                                   value="{{ old('guard_name', $permission->guard_name) }}">
                            @error('guard_name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Perbarui Izin
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info and Danger Zone -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Informasi Izin
                    </h2>
                </div>
                <div class="card-body small">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-semibold">ID:</td>
                            <td>{{ $permission->id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Guard:</td>
                            <td>{{ $permission->guard_name ?? 'web' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Role:</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $permission->roles()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Pengguna:</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $permission->users()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat:</td>
                            <td>{{ $permission->created_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Diperbarui:</td>
                            <td>{{ $permission->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-link"></i> Terkait Dengan
                    </h2>
                </div>
                <div class="card-body small">
                    <p class="fw-semibold mb-2">Role yang Menggunakan Izin Ini:</p>
                    @forelse($permission->roles as $role)
                        <a href="{{ route('admin.roles.show', $role) }}"
                           class="badge badge-warning me-1 mb-1">
                            {{ $role->name }}
                        </a>
                    @empty
                        <p class="text-muted mb-0">Belum digunakan oleh role manapun</p>
                    @endforelse

                    <p class="fw-semibold mb-2 mt-3">Pengguna Langsung:</p>
                    @if($permission->users()->count() > 0)
                        <small class="text-muted">
                            {{ $permission->users()->count() }} pengguna memiliki izin ini
                        </small>
                    @else
                        <p class="text-muted mb-0">Tidak ada pengguna langsung</p>
                    @endif
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0 text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                    </h2>
                </div>
                <div class="card-body">
                    <p class="small mb-3">
                        <i class="fas fa-warning"></i> Menghapus izin akan mempengaruhi semua role dan pengguna yang memiliki izin ini.
                    </p>
                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin ini? Ini akan mempengaruhi ' + {{ $permission->roles()->count() }} + ' role dan ' + {{ $permission->users()->count() }} + ' pengguna.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash"></i> Hapus Izin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
