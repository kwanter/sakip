@extends('layouts.modern')

@section('title', 'Laporan SAKIP')

@section('page-title', 'Laporan SAKIP')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Laporan SAKIP</h1>
                <p class="page-header-subtitle">Kelola dan pantau laporan kinerja instansi</p>
            </div>
            <div class="page-header-actions">
                @can('create', App\Models\Report::class)
                <a href="{{ route('sakip.reports.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Buat Laporan</span>
                </a>
                @endcan
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    <i class="fas fa-download"></i>
                    <span class="ms-1">Download</span>
                </button>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#templateModal">
                    <i class="fas fa-file-contract"></i>
                    <span class="ms-1">Template</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(isset($statistics))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['total'] ?? 0 }}</div>
                <div class="stat-label">Total Laporan</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['approved'] ?? 0 }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['pending'] ?? 0 }}</div>
                <div class="stat-label">Menunggu</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
                <div class="stat-value">{{ $statistics['this_year'] ?? 0 }}</div>
                <div class="stat-label">Tahun Ini</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table Card -->
    <div class="card">
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle alert-icon"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Laporan</th>
                            <th>Instansi</th>
                            <th>Periode</th>
                            <th>Tahun</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($reports ?? []) as $report)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $report->title ?? Str::headline($report->report_type) }}</strong>
                                    @if($report->description)
                                    <br><small class="text-muted">{{ Str::limit($report->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $report->instansi->nama_instansi ?? '-' }}</td>
                            <td>{{ ucfirst($report->period ?? '-') }}</td>
                            <td>{{ $report->year ?? '-' }}</td>
                            <td>
                                <span class="badge @if($report->status === 'approved') badge-success @elseif($report->status === 'submitted') badge-primary @else badge-neutral @endif">
                                    {{ ucfirst(str_replace('_', ' ', $report->status ?? 'draft')) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $report->created_at?->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('sakip.reports.show', $report) }}" class="btn btn-outline-primary" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sakip.reports.download', $report) }}" class="btn btn-outline-primary" title="Unduh">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($report->status !== 'approved')
                                    @can('approve', $report)
                                    <form action="{{ route('sakip.reports.approve', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Setujui" onclick="return confirm('Setujui laporan ini?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-alt text-muted"></i>
                                    <p class="mb-0">Tidak ada laporan</p>
                                    <small class="text-muted">Silakan buat laporan baru</small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($reports) && $reports->hasPages())
            <div class="mt-3">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Download Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Unduh Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @php($downloadableReports = ($reports ?? collect())->whereNotNull('file_path'))
                    @if($downloadableReports->isEmpty())
                    <div class="empty-state">
                        <i class="fas fa-file-download text-muted"></i>
                        <p class="mb-0">Belum ada file laporan yang tersedia</p>
                        <small class="text-muted">File akan tersedia setelah proses pembuatan laporan selesai</small>
                    </div>
                    @else
                    <div class="mb-3">
                        <label for="exportReportSelect" class="form-label">Pilih Laporan</label>
                        <select id="exportReportSelect" class="form-select">
                            @foreach($downloadableReports as $report)
                            <option value="{{ $report->id }}">
                                {{ $report->title ?? $report->report_type }} — {{ $report->period }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-muted mb-0">
                        <small><i class="fas fa-info-circle me-1"></i>File akan diunduh dalam format yang tersimpan.</small>
                    </p>
                    @endif
                </div>
                @if($downloadableReports->isNotEmpty())
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="downloadSelectedReport()">
                        <i class="fas fa-download"></i>
                        <span class="ms-1">Unduh</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalLabel">Template Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @forelse(($templates ?? collect()) as $template)
                    <div class="d-flex justify-content-between align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="fw-semibold">{{ $template->name }}</div>
                            @if($template->description)
                            <small class="text-muted">{{ $template->description }}</small>
                            @endif
                        </div>
                        <a href="{{ route('sakip.reports.template', $template) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i>
                            <span class="ms-1">Gunakan</span>
                        </a>
                    </div>
                    @empty
                    <div class="empty-state">
                        <i class="fas fa-file-contract text-muted"></i>
                        <p class="mb-0">Belum ada template tersedia</p>
                        <small class="text-muted">Template dapat ditambahkan oleh administrator</small>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ app()->bound('csp-nonce') ? app('csp-nonce') : '' }}">
function downloadSelectedReport() {
    var select = document.getElementById('exportReportSelect');
    if (!select || !select.value) {
        return;
    }
    window.location.href = '/sakip/reports/' + encodeURIComponent(select.value) + '/download';
}
</script>
@endsection
