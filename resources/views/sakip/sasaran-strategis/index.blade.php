@extends('layouts.app')

@section('title', 'Daftar Sasaran Strategis')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bullseye"></i> Daftar Sasaran Strategis
        </h1>
        @can('create', App\Models\SasaranStrategis::class)
        <a href="{{ route('sakip.sasaran-strategis.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Sasaran Strategis
        </a>
        @endcan
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.sasaran-strategis.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="instansi_id" class="form-control">
                            <option value="">Semua Instansi</option>
                            @foreach($instansis as $inst)
                                <option value="{{ $inst->id }}" {{ request('instansi_id') == $inst->id ? 'selected' : '' }}>
                                    {{ $inst->nama_instansi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Sasaran Strategis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Strategis</th>
                            <th>Instansi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sasaranStrategis as $key => $sasaran)
                        <tr>
                            <td>{{ $sasaranStrategis->firstItem() + $key }}</td>
                            <td><strong>{{ $sasaran->kode_sasaran_strategis }}</strong></td>
                            <td>{{ $sasaran->nama_strategis }}</td>
                            <td>{{ $sasaran->instansi->nama_instansi }}</td>
                            <td>
                                @if($sasaran->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('sakip.sasaran-strategis.show', $sasaran) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $sasaran)
                                    <a href="{{ route('sakip.sasaran-strategis.edit', $sasaran) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $sasaran)
                                    <form action="{{ route('sakip.sasaran-strategis.destroy', $sasaran) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $sasaranStrategis->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
