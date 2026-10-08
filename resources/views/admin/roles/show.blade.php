@extends('layouts.modern')

@section('title', 'Detail Role: ' . $role->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Detail Role: {{ $role->name }}</h1>
                <p class="page-header-subtitle">Izin dan pengguna yang terhubung dengan role ini.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Role Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Informasi Role
                    </h2>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-semibold">Nama Role:</td>
                            <td>{{ $role->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Guard:</td>
                            <td>{{ $role->guard_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Pengguna:</td>
                            <td>
                                <span class="badge badge-primary">{{ $role->users()->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Izin:</td>
                            <td>
                                <span class="badge badge-neutral">{{ $role->permissions()->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat:</td>
                            <td>{{ $role->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Diperbarui:</td>
                            <td>{{ $role->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Permissions and Users -->
        <div class="col-lg-8">
            <!-- Permissions Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-key"></i> Izin ({{ $role->permissions()->count() }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Nama Izin</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($role->permissions as $permission)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge badge-neutral">{{ $permission->name }}</span>
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
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-users"></i> Pengguna dengan Role Ini ({{ $role->users()->count() }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($role->users as $user)
                                <tr>
                                    <td class="align-middle">{{ $user->name }}</td>
                                    <td class="align-middle">{{ $user->email }}</td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
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
