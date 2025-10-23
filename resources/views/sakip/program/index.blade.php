@extends('layouts.app')

@section('title', 'Daftar Program')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks"></i> Daftar Program
        </h1>
        @can('create', App\Models\Program::class)
        <a href="{{ route('sakip.program.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Program
        </a>
        @endcan
    </div>

    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sakip.program.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Cari nama atau kode program..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="instansi_id" class="form-control">
                                <option value="">Semua Instansi</option>
                                @foreach($instansis as $inst)
                                    <option value="{{ $inst->id }}" {{ request('instansi_id') == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="sasaran_strategis_id" class="form-control">
                                <option value="">Semua Sasaran</option>
                                @foreach($sasaranStrategis as $sasaran)
                                    <option value="{{ $sasaran->id }}" {{ request('sasaran_strategis_id') == $sasaran->id ? 'selected' : '' }}>
                                        {{ $sasaran->nama_strategis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="tahun" class="form-control">
                                <option value="">Semua Tahun</option>
                                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                    <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Program</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Program</th>
                            <th>Instansi</th>
                            <th>Sasaran Strategis</th>
                            <th>Tahun</th>
                            <th>Anggaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $key => $program)
                        <tr>
                            <td>{{ $programs->firstItem() + $key }}</td>
                            <td><strong>{{ $program->kode_program }}</strong></td>
                            <td>{{ $program->nama_program }}</td>
                            <td>{{ $program->instansi->nama_instansi }}</td>
                            <td>{{ $program->sasaranStrategis->nama_strategis }}</td>
                            <td>{{ $program->tahun }}</td>
                            <td>Rp {{ number_format($program->anggaran, 0, ',', '.') }}</td>
                            <td>
                                @if($program->status == 'aktif')
                                    <span class="badge badge-success">Aktif</span>
                                @elseif($program->status == 'selesai')
                                    <span class="badge badge-info">Selesai</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('sakip.program.show', $program) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $program)
                                    <a href="{{ route('sakip.program.edit', $program) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $program)
                                    <form action="{{ route('sakip.program.destroy', $program) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus program ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data program</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $programs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTables initialization if needed
});
</script>
@endpush
