@extends('layouts.app')

@section('title', 'Detail Role: ' . $role->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shield-alt"></i> Detail Role: {{ $role->name }}
        </h1>
        <div>
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Role Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informasi Role
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="font-weight-bold">Nama Role:</td>
                            <td>{{ $role->name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Guard:</td>
                            <td>{{ $role->guard_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Pengguna:</td>
                            <td>
                                <span class="badge badge-info">{{ $role->users()->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jumlah Izin:</td>
                            <td>
                                <span class="badge badge-secondary">{{ $role->permissions()->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dibuat:</td>
                            <td>{{ $role->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Diperbarui:</td>
                            <td>{{ $role->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Permissions and Users -->
        <div class="col-lg-8">
            <!-- Permissions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-key"></i> Izin ({{ $role->permissions()->count() }})
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom">Nama Izin</th>
                                <th class="border-bottom">Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($role->permissions as $permission)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">{{ $permission->name }}</span>
                                    </td>
                                    <td class="align-middle text-muted small">
                                        {{ $permission->description ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted">
                                        Tidak ada izin untuk role ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users with this Role -->
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-users"></i> Pengguna dengan Role Ini ({{ $role->users()->count() }})
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom">Nama Pengguna</th>
                                <th class="border-bottom">Email</th>
                                <th class="border-bottom text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($role->users as $user)
                                <tr>
                                    <td class="align-middle">{{ $user->name }}</td>
                                    <td class="align-middle">{{ $user->email }}</td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Tidak ada pengguna dengan role ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
