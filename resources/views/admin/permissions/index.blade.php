@extends('layouts.modern')

@section('title', 'Manajemen Izin')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Manajemen Izin (Permission)</h1>
                <p class="page-header-subtitle">Kelola aksi spesifik yang dapat dilakukan pengguna dalam sistem.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Izin
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                <div class="flex-grow-1">
                    <input type="text" name="search" class="form-control w-100"
                           placeholder="Cari izin..."
                           value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Permissions Table Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title fs-6 m-0">
                Daftar Izin ({{ $permissions->total() }} Total)
            </h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover modern-table mb-0">
                <thead>
                    <tr>
                        <th>
                            Nama Izin
                        </th>
                        <th class="text-center" style="width: 120px;">
                            Guard
                        </th>
                        <th class="text-center" style="width: 100px;">
                            Role
                        </th>
                        <th class="text-center" style="width: 100px;">
                            Pengguna
                        </th>
                        <th class="text-center" style="width: 150px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td class="align-middle">
                                <div class="mb-0">
                                    <strong>{{ $permission->name }}</strong>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <small class="text-muted">{{ $permission->guard_name ?? 'web' }}</small>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-warning">
                                    {{ $permission->roles_count ?? 0 }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-primary">
                                    {{ $permission->users_count ?? 0 }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.permissions.show', $permission) }}"
                                       class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.permissions.edit', $permission) }}"
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.permissions.destroy', $permission) }}"
                                          method="POST" class="d-inline"
                                          data-confirm="Apakah Anda yakin ingin menghapus izin ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada izin ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>

    <!-- Info Panels -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Apa itu Izin?
                    </h2>
                </div>
                <div class="card-body small">
                    <p>
                        Izin (Permission) adalah aksi spesifik yang dapat dilakukan pengguna dalam sistem.
                        Izin dikelompokkan menjadi Role, dan Role diberikan kepada pengguna.
                    </p>
                    <p class="mb-0">
                        <strong>Contoh:</strong>
                        <ul class="mb-0">
                            <li><code>create-indicator</code> - Membuat indikator baru</li>
                            <li><code>edit-indicator</code> - Mengedit indikator</li>
                            <li><code>delete-indicator</code> - Menghapus indikator</li>
                        </ul>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-question-circle"></i> Cara Menggunakan
                    </h2>
                </div>
                <div class="card-body small">
                    <ol class="ps-3">
                        <li>Klik "Tambah Izin" untuk membuat izin baru</li>
                        <li>Berikan nama izin yang deskriptif (gunakan format: <code>action-resource</code>)</li>
                        <li>Tambahkan izin ke Role di halaman Manajemen Role</li>
                        <li>Role kemudian diberikan kepada pengguna</li>
                    </ol>
                    <p class="mt-2 mb-0">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Tips:</strong> Gunakan format konsisten seperti <code>view-dashboard</code>,
                        <code>create-indicator</code>, <code>approve-target</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
