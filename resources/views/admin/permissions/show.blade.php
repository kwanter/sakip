@extends('layouts.modern')

@section('title', 'Detail Izin: ' . $permission->name)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Detail Izin: {{ $permission->name }}</h1>
                <p class="page-header-subtitle">Role dan pengguna yang terhubung dengan izin ini.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
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
        <!-- Permission Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Informasi Izin
                    </h2>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-semibold">Nama:</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $permission->name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Guard:</td>
                            <td>{{ $permission->guard_name ?? 'web' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ID:</td>
                            <td><small>{{ $permission->id }}</small></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Role:</td>
                            <td>
                                <span class="badge badge-warning">
                                    {{ $permission->roles()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Pengguna:</td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $permission->users()->count() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat:</td>
                            <td>{{ $permission->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Diperbarui:</td>
                            <td>{{ $permission->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Role and User Lists -->
        <div class="col-lg-8">
            <!-- Roles Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-shield-alt"></i> Role yang Memiliki Izin Ini ({{ $permission->roles()->count() }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Nama Role</th>
                                <th class="text-center" style="width: 100px;">Pengguna</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permission->roles as $role)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge badge-neutral">{{ $role->name }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-primary">{{ $role->users()->count() }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.roles.show', $role) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        Izin ini belum ditambahkan ke role manapun
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users with this Permission -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-users"></i> Pengguna dengan Izin Ini ({{ $permission->users()->count() }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Nama Pengguna</th>
                                <th>Email</th>
                                <th class="text-center" style="width: 100px;">Role</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permission->users as $user)
                                <tr>
                                    <td class="align-middle">{{ $user->name }}</td>
                                    <td class="align-middle">{{ $user->email }}</td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-neutral">
                                            {{ $user->roles()->count() }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Tidak ada pengguna dengan izin ini
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
