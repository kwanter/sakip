@extends('layouts.app')

@section('title', 'Daftar Laporan Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Laporan Kinerja</h1>
        <div>
            @anyrole('admin,manager')
<button type="button" class="btn btn-info btn-sm mr-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#quarterlyModal">
    <i class="fas fa-chart-line fa-sm text-white-50"></i> Buat Laporan Triwulan
</button>
@endanyrole
            @anyrole('admin,manager')
<a href="{{ route('laporan-kinerja.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
@endanyrole
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Laporan
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan-kinerja.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <select class="form-control" id="tahun" name="tahun">
                                <option value="">Semua Tahun</option>
                                @for($year = date('Y') + 5; $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="periode_type">Tipe Periode</label>
                            <select name="periode_type" class="form-control" id="periode_type">
                                <option value="">Semua Tipe</option>
                                <option value="monthly" {{ request('periode_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                                <option value="quarterly" {{ request('periode_type') == 'quarterly' ? 'selected' : '' }}>Triwulanan</option>
                                <option value="yearly" {{ request('periode_type') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="periode">Periode</label>
                            <select name="periode" class="form-control" id="periode_filter">
                                <option value="">Semua Periode</option>
                                <!-- Monthly options -->
                                <optgroup label="Bulanan" id="monthly_options" style="display: none;">
                                    <option value="januari" {{ request('periode') == 'januari' ? 'selected' : '' }}>Januari</option>
                                    <option value="februari" {{ request('periode') == 'februari' ? 'selected' : '' }}>Februari</option>
                                    <option value="maret" {{ request('periode') == 'maret' ? 'selected' : '' }}>Maret</option>
                                    <option value="april" {{ request('periode') == 'april' ? 'selected' : '' }}>April</option>
                                    <option value="mei" {{ request('periode') == 'mei' ? 'selected' : '' }}>Mei</option>
                                    <option value="juni" {{ request('periode') == 'juni' ? 'selected' : '' }}>Juni</option>
                                    <option value="juli" {{ request('periode') == 'juli' ? 'selected' : '' }}>Juli</option>
                                    <option value="agustus" {{ request('periode') == 'agustus' ? 'selected' : '' }}>Agustus</option>
                                    <option value="september" {{ request('periode') == 'september' ? 'selected' : '' }}>September</option>
                                    <option value="oktober" {{ request('periode') == 'oktober' ? 'selected' : '' }}>Oktober</option>
                                    <option value="november" {{ request('periode') == 'november' ? 'selected' : '' }}>November</option>
                                    <option value="desember" {{ request('periode') == 'desember' ? 'selected' : '' }}>Desember</option>
                                </optgroup>
                                <!-- Quarterly options -->
                                <optgroup label="Triwulanan" id="quarterly_options" style="display: none;">
                                    <option value="triwulan1" {{ request('periode') == 'triwulan1' ? 'selected' : '' }}>Triwulan I</option>
                                    <option value="triwulan2" {{ request('periode') == 'triwulan2' ? 'selected' : '' }}>Triwulan II</option>
                                    <option value="triwulan3" {{ request('periode') == 'triwulan3' ? 'selected' : '' }}>Triwulan III</option>
                                    <option value="triwulan4" {{ request('periode') == 'triwulan4' ? 'selected' : '' }}>Triwulan IV</option>
                                </optgroup>
                                <!-- Yearly options -->
                                <optgroup label="Tahunan" id="yearly_options" style="display: none;">
                                    <option value="tahunan" {{ request('periode') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('laporan-kinerja.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Laporan Kinerja</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Indikator Kinerja</th>
                            <th>Kegiatan</th>
                            <th>Tahun</th>
                            <th>Periode</th>
                            <th>Nilai Realisasi</th>
                            <th>Persentase Capaian</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanKinerjas as $index => $laporan)
                            <tr>
                                <td>
                                    {{ $laporanKinerjas->firstItem() + $index }}
                                </td>
                                <td>
                                    <strong>{{ $laporan->indikatorKinerja->nama_indikator }}</strong><br>
                                    <small class="text-muted">{{ $laporan->indikatorKinerja->satuan }}</small>
                                </td>
                                <td>
                                    <strong>{{ $laporan->indikatorKinerja->kegiatan->nama_kegiatan }}</strong><br>
                                    <small class="text-muted">{{ $laporan->indikatorKinerja->kegiatan->program->nama_program }}</small>
                                </td>
                                <td>{{ $laporan->tahun }}</td>
                                <td>
                                    @switch($laporan->periode)
                                        @case('triwulan1')
                                            <span class="badge badge-info">Triwulan I</span>
                                            @break
                                        @case('triwulan2')
                                            <span class="badge badge-info">Triwulan II</span>
                                            @break
                                        @case('triwulan3')
                                            <span class="badge badge-info">Triwulan III</span>
                                            @break
                                        @case('triwulan4')
                                            <span class="badge badge-info">Triwulan IV</span>
                                            @break
                                        @case('tahunan')
                                            <span class="badge badge-primary">Tahunan</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $laporan->periode_name }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    {{ number_format($laporan->nilai_realisasi, 2) }}
                                    <br>
                                    <small class="text-muted">{{ $laporan->indikatorKinerja->satuan }}</small>
                                </td>
                                <td>
                                    @if($laporan->persentase_capaian)
                                        <span class="badge badge-{{ $laporan->persentase_capaian >= 80 ? 'success' : ($laporan->persentase_capaian >= 60 ? 'warning' : 'danger') }}">
                                            {{ number_format($laporan->persentase_capaian, 2) }}%
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($laporan->status_verifikasi)
                                        @case('draft')
                                            <span class="badge badge-secondary">Draft</span>
                                            @break
                                        @case('submitted')
                                            <span class="badge badge-warning">Diajukan</span>
                                            @break
                                        @case('verified')
                                            <span class="badge badge-success">Terverifikasi</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge badge-danger">Ditolak</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('laporan-kinerja.show', $laporan) }}"
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('laporan-kinerja.edit', $laporan) }}"
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('laporan-kinerja.destroy', $laporan) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                                        <p class="text-gray-500">Belum ada data laporan kinerja</p>
                                        <a href="{{ route('laporan-kinerja.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Tambah Laporan Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($laporanKinerjas->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $laporanKinerjas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quarterly Report Modal -->
<div class="modal fade" id="quarterlyModal" tabindex="-1" role="dialog" aria-labelledby="quarterlyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quarterlyModalLabel">Buat Laporan Triwulan dari Data Bulanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('laporan-kinerja.create-from-monthly') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_indikator_kinerja_id">Indikator Kinerja</label>
                        <select name="indikator_kinerja_id" id="modal_indikator_kinerja_id" class="form-control" required>
                            <option value="">Pilih Indikator Kinerja</option>
                            @foreach(\App\Models\IndikatorKinerja::with('kegiatan.program')->get() as $indikator)
                                <option value="{{ $indikator->id }}">
                                    {{ $indikator->nama_indikator }} - {{ $indikator->kegiatan->nama_kegiatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_tahun">Tahun</label>
                        <select name="tahun" id="modal_tahun" class="form-control" required>
                            <option value="">Pilih Tahun</option>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_quarter">Triwulan</label>
                        <select name="quarter" id="modal_quarter" class="form-control" required>
                            <option value="">Pilih Triwulan</option>
                            <option value="triwulan1">Triwulan I (Jan-Mar)</option>
                            <option value="triwulan2">Triwulan II (Apr-Jun)</option>
                            <option value="triwulan3">Triwulan III (Jul-Sep)</option>
                            <option value="triwulan4">Triwulan IV (Okt-Des)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Buat Laporan Triwulan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Wait for DOM to be fully ready and check table exists
        setTimeout(function() {
            var table = $('#dataTable');
            
            // Validate table structure before initialization
            if (table.length === 0) {
                console.warn('DataTable element not found');
                return;
            }
            
            // Check if table has data rows (not just empty state)
            var dataRows = table.find('tbody tr').not(':has(td[colspan])');
            var headerCells = table.find('thead tr:first th').length;
            
            // Only initialize DataTables if there are actual data rows
            if (dataRows.length === 0) {
                console.log('No data rows found, skipping DataTables initialization');
                return;
            }
            
            var firstRowCells = dataRows.first().find('td').length;
            
            if (headerCells !== firstRowCells) {
                console.warn('Column count mismatch: header=' + headerCells + ', body=' + firstRowCells);
                return; // Don't initialize if column count doesn't match
            }
            
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                $('#dataTable').DataTable().destroy();
            }
            
            // Initialize DataTable with minimal configuration
            try {
                table.DataTable({
                    "paging": false,
                    "searching": false,
                    "info": false,
                    "ordering": false,
                    "autoWidth": false,
                    "deferRender": true,
                    "processing": false,
                    "language": {
                        "emptyTable": "Belum ada data laporan kinerja"
                    }
                });
                console.log('DataTables initialized successfully');
            } catch (error) {
                console.error('DataTables initialization error:', error);
                // Fallback: just show the table without DataTables
                table.show();
            }
        }, 100);

        // Handle period type filtering
        $('#periode_type').change(function() {
            var selectedType = $(this).val();
            var periodeFilter = $('#periode_filter');

            // Hide all optgroups first
            $('#monthly_options, #quarterly_options, #yearly_options').hide();

            // Reset periode selection
            periodeFilter.val('');

            // Show relevant optgroup based on selection
            if (selectedType === 'monthly') {
                $('#monthly_options').show();
            } else if (selectedType === 'quarterly') {
                $('#quarterly_options').show();
            } else if (selectedType === 'yearly') {
                $('#yearly_options').show();
            } else {
                // Show all if no specific type selected
                $('#monthly_options, #quarterly_options, #yearly_options').show();
            }
        });

        // Initialize period filter based on current selection
        var currentPeriodeType = $('#periode_type').val();
        if (currentPeriodeType) {
            $('#periode_type').trigger('change');
        } else {
            $('#monthly_options, #quarterly_options, #yearly_options').show();
        }
    });
</script>
@endpush
