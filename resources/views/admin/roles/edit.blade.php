@extends('layouts.app')

@section('title', 'Edit Role: ' . $role->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Role: {{ $role->name }}
        </h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shield-alt"></i> Informasi Role
                    </h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

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

                    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Role Name -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label font-weight-bold">
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
                        <div class="form-group mb-3">
                            <label for="guard_name" class="form-label font-weight-bold">
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
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-key"></i> Pilih Izin
                            </label>
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($permissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                           class="custom-control-input"
                                                           id="permission_{{ $permission->id }}"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="permission_{{ $permission->id }}">
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
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Perbarui Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info and Danger Zone -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body small">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">ID:</td>
                            <td>{{ $role->id }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pengguna:</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $role->users()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Izin:</td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ $role->permissions()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td>{{ $role->created_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui:</td>
                            <td>{{ $role->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if(!in_array($role->name, ['Super Admin', 'admin', 'super-admin']))
                <div class="card shadow border-danger">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">
                            <i class="fas fa-warning"></i> Menghapus role akan mempengaruhi pengguna yang memiliki role ini.
                        </p>
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini? Pengguna akan kehilangan akses berdasarkan role ini.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
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
