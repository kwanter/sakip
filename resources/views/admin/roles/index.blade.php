@extends('layouts.modern')

@section('title', 'Manajemen Role')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Manajemen Role</h1>
                <p class="page-header-subtitle">Kelola kelompok izin yang diberikan kepada pengguna.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Role
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

    <!-- Search and Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.roles.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                <div class="flex-grow-1">
                    <input type="text" name="search" class="form-control w-100"
                           placeholder="Cari role..."
                           value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Roles Table Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title fs-6 m-0">
                Daftar Role ({{ $roles->total() }} Total)
            </h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover modern-table mb-0">
                <thead>
                    <tr>
                        <th>
                            Nama Role
                        </th>
                        <th class="text-center" style="width: 120px;">
                            Pengguna
                        </th>
                        <th class="text-center" style="width: 120px;">
                            Izin
                        </th>
                        <th class="text-center" style="width: 150px;">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td class="align-middle">
                                <div class="mb-0">
                                    <strong>{{ $role->name }}</strong>
                                    @if($role->guard_name)
                                        <br>
                                        <small class="text-muted">Guard: {{ $role->guard_name }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-primary">
                                    {{ $role->users_count ?? 0 }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-neutral">
                                    {{ $role->permissions_count ?? 0 }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.roles.show', $role) }}"
                                       class="btn btn-outline-secondary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.roles.edit', $role) }}"
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!in_array($role->name, ['Super Admin', 'admin', 'super-admin']))
                                        <form action="{{ route('admin.roles.destroy', $role) }}"
                                              method="POST" class="d-inline"
                                              data-confirm="Apakah Anda yakin ingin menghapus role ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">Tidak ada role ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $roles->links() }}
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-info-circle"></i> Informasi Role
                    </h2>
                </div>
                <div class="card-body small">
                    <p>
                        Role adalah kelompok izin yang dapat diberikan kepada pengguna.
                        Setiap role dapat memiliki satu atau lebih izin yang mendefinisikan
                        akses pengguna terhadap fitur sistem.
                    </p>
                    <ul class="mb-0">
                        <li>Klik tombol "Lihat Detail" untuk melihat izin role</li>
                        <li>Klik tombol "Edit" untuk mengubah role dan izinnya</li>
                        <li>Klik tombol "Hapus" untuk menghapus role (tidak dapat menghapus role default)</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">
                        <i class="fas fa-question-circle"></i> Pertanyaan Umum
                    </h2>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Apa perbedaan Role dan Permission?</strong><br>
                        Role adalah kumpulan permission. Permission adalah aksi spesifik yang dapat dilakukan pengguna.
                    </p>
                    <p class="mb-0">
                        <strong>Bagaimana cara memberikan role kepada pengguna?</strong><br>
                        Kunjungi halaman Manajemen Pengguna, pilih pengguna, lalu assign role di bagian "Role".
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
