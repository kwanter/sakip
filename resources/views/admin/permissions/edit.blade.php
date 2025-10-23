@extends('layouts.app')

@section('title', 'Edit Izin: ' . $permission->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Izin: {{ $permission->name }}
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

                    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Permission Name -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label font-weight-bold">
                                Nama Izin <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg @error('name') is-invalid @enderror"
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
                        <div class="form-group mb-3">
                            <label for="guard_name" class="form-label font-weight-bold">
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
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Perbarui Izin
                            </button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-lg">
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
                        <i class="fas fa-info-circle"></i> Informasi Izin
                    </h6>
                </div>
                <div class="card-body small">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">ID:</td>
                            <td>{{ $permission->id }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Guard:</td>
                            <td>{{ $permission->guard_name ?? 'web' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Role:</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $permission->roles()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pengguna:</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $permission->users()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td>{{ $permission->created_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui:</td>
                            <td>{{ $permission->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-link"></i> Terkait Dengan
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="font-weight-bold mb-2">Role yang Menggunakan Izin Ini:</p>
                    @forelse($permission->roles as $role)
                        <a href="{{ route('admin.roles.show', $role) }}"
                           class="badge badge-warning mr-1 mb-1">
                            {{ $role->name }}
                        </a>
                    @empty
                        <p class="text-muted mb-0">Belum digunakan oleh role manapun</p>
                    @endforelse

                    <p class="font-weight-bold mb-2 mt-3">Pengguna Langsung:</p>
                    @if($permission->users()->count() > 0)
                        <small class="text-muted">
                            {{ $permission->users()->count() }} pengguna memiliki izin ini
                        </small>
                    @else
                        <p class="text-muted mb-0">Tidak ada pengguna langsung</p>
                    @endif
                </div>
            </div>

            <div class="card shadow border-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-exclamation-triangle"></i> Zona Berbahaya
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-3">
                        <i class="fas fa-warning"></i> Menghapus izin akan mempengaruhi semua role dan pengguna yang memiliki izin ini.
                    </p>
                    <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus izin ini? Ini akan mempengaruhi ' + {{ $permission->roles()->count() }} + ' role dan ' + {{ $permission->users()->count() }} + ' pengguna.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Hapus Izin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
