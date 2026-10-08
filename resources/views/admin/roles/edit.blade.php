@extends('layouts.modern')

@section('title', 'Edit Role: ' . $role->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Edit Role: {{ $role->name }}</h1>
                <p class="page-header-subtitle">Perbarui nama role dan izin yang menyertainya.</p>
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

                    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Role Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $role->name) }}"
                                   required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
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
                                   value="{{ old('guard_name', $role->guard_name) }}">
                            @error('guard_name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
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
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
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
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Perbarui Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-info-circle"></i> Informasi
                    </h2>
                </div>
                <div class="card-body small">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-semibold">ID:</td>
                            <td>{{ $role->id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Pengguna:</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $role->users()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Izin:</td>
                            <td>
                                <span class="badge badge-neutral">
                                    {{ $role->permissions()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat:</td>
                            <td>{{ $role->created_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Diperbarui:</td>
                            <td>{{ $role->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if(!in_array($role->name, ['Super Admin', 'admin', 'super-admin']))
                <div class="card border-danger">
                    <div class="card-header">
                        <h2 class="card-title fs-6 m-0 text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                        </h2>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">
                            <i class="fas fa-warning"></i> Menghapus role akan mempengaruhi pengguna yang memiliki role ini.
                        </p>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini? Pengguna akan kehilangan akses berdasarkan role ini.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash"></i> Hapus Role
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-lock"></i> Role default tidak dapat dihapus
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
