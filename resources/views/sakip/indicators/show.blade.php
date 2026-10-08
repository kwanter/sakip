@extends('layouts.modern')

@section('title', 'Detail Indikator Kinerja')

@section('page-title', 'Detail Indikator Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.indicators.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Detail Indikator Kinerja</h1>
                <p class="page-header-subtitle">Informasi lengkap indikator kinerja</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('sakip.indicators.edit', $indicator) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i>
            <span class="ms-1">Edit</span>
        </a>

        <form action="{{ route('sakip.indicators.destroy', $indicator) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus indikator ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash"></i>
                <span class="ms-1">Hapus</span>
            </button>
        </form>
    </div>

    <!-- Basic Information -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Informasi Dasar</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stat-label mb-1">Kode Indikator</div>
                    <p class="fw-bold mb-0">{{ $indicator->code }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Nama Indikator</div>
                    <p class="fw-bold mb-0">{{ $indicator->name }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Kategori</div>
                    <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $indicator->category)) }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Satuan</div>
                    <p class="mb-0">{{ $indicator->measurement_unit }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Instansi</div>
                    <p class="mb-0">{{ $indicator->instansi->nama_instansi ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <div class="stat-label mb-1">Frekuensi Pengukuran</div>
                    <p class="mb-0">{{ ucfirst($indicator->frequency) }}</p>
                </div>
            </div>

            @if($indicator->description)
            <div class="mt-4">
                <div class="stat-label mb-1">Deskripsi</div>
                <p class="mb-0">{{ $indicator->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Target Information -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Target Kinerja</h6>
            <div class="card-actions">
                <span class="text-muted small">Tahun {{ $currentYear }}</span>
                @can('update', $indicator)
                    <a href="{{ route('sakip.targets.create', $indicator) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i>
                        <span class="ms-1">Tambah Target</span>
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @if($currentTargets && $currentTargets->count() > 0)
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Tahun</th>
                                <th>Nilai Target</th>
                                <th>Nilai Minimum</th>
                                <th>Status</th>
                                <th>Justifikasi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currentTargets as $target)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $target->year }}
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($target->target_value, 2) }}</span>
                                        <small class="text-muted">{{ $indicator->measurement_unit }}</small>
                                    </td>
                                    <td>
                                        {{ $target->minimum_value ? number_format($target->minimum_value, 2) . ' ' . $indicator->measurement_unit : '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'draft' => 'badge-neutral',
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                                'revised' => 'badge-warning'
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Draft',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                'revised' => 'Revisi'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$target->status] ?? 'badge-neutral' }}">
                                            {{ $statusLabels[$target->status] ?? ucfirst($target->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $target->justification ?? '-' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Edit button available for all statuses --}}
                                            @can('update', $indicator)
                                                <a href="{{ route('sakip.targets.edit', [$indicator, $target]) }}" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- Delete button only for draft or rejected --}}
                                            @if($target->status === 'draft' || $target->status === 'rejected')
                                                @can('update', $indicator)
                                                    <form action="{{ route('sakip.targets.destroy', [$indicator, $target]) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus target ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif

                                            {{-- Approval workflow buttons for draft status --}}
                                            @can('approve-targets')
                                                @if($target->status === 'draft' || $target->status === 'revised')
                                                    <button data-onclick="approveTarget({{ $target->id }}, '{{ $indicator->id }}')" class="btn btn-outline-success" title="Setujui">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                    <button data-onclick="reviseTarget({{ $target->id }}, '{{ $indicator->id }}')" class="btn btn-outline-warning" title="Minta Revisi">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button data-onclick="rejectTarget({{ $target->id }}, '{{ $indicator->id }}')" class="btn btn-outline-danger" title="Tolak">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-bullseye text-muted"></i>
                    <p class="mb-0">Belum ada target</p>
                    <small class="text-muted">Target untuk tahun {{ $currentYear }} belum ditetapkan.</small>
                </div>
            @endif

            <!-- All Targets History -->
            @if($indicator->targets && $indicator->targets->count() > 0)
                <div class="mt-4 pt-4 border-top">
                    <h6 class="card-title mb-3">Riwayat Target</h6>
                    <div class="row g-3">
                        @foreach($indicator->targets()->orderBy('year', 'desc')->get() as $target)
                            <div class="col-md-4">
                                <div class="p-3 rounded border">
                                    <div class="stat-label">Tahun {{ $target->year }}</div>
                                    <div class="stat-value">{{ number_format($target->target_value, 2) }}</div>
                                    <small class="text-muted">{{ $indicator->measurement_unit }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assessment Criteria -->
    @if(isset($indicator->criteria) && $indicator->criteria->count() > 0)
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Kriteria Penilaian</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Nama Kriteria</th>
                            <th>Bobot (%)</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indicator->criteria as $criterion)
                        <tr>
                            <td class="fw-bold">{{ $criterion->name }}</td>
                            <td>{{ $criterion->weight }}%</td>
                            <td>{{ $criterion->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Performance Data -->
    <div class="modern-card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Data Kinerja Terbaru</h6>
            <div class="card-actions">
                <a href="{{ route('sakip.data-collection.create') }}?indicator_id={{ $indicator->id }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Data</span>
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(isset($recentData) && $recentData->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Nilai</th>
                            <th>Pencapaian</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentData as $data)
                        <tr>
                            <td class="fw-bold">
                                {{ $data->period }} {{ $data->year }}
                            </td>
                            <td>{{ number_format($data->value, 2) }}</td>
                            <td>
                                @if($indicator->target_value > 0)
                                    {{ number_format(($data->value / $indicator->target_value) * 100, 1) }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $data->status == 'validated' ? 'badge-success' : ($data->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ ucfirst($data->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sakip.data-collection.show', $data) }}">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-database text-muted"></i>
                <p class="mb-0">Belum ada data kinerja</p>
                <small class="text-muted">Mulai input data kinerja untuk indikator ini.</small>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Assessments -->
    <div class="modern-card">
        <div class="card-header">
            <h6 class="card-title mb-0">Penilaian Terbaru</h6>
            <div class="card-actions">
                <a href="{{ route('sakip.assessments.create') }}?indicator_id={{ $indicator->id }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-clipboard-check"></i>
                    <span class="ms-1">Buat Penilaian</span>
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(isset($recentAssessments) && $recentAssessments->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Penilai</th>
                            <th>Skor</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAssessments as $assessment)
                        <tr>
                            <td class="fw-bold">
                                {{ $assessment->period }} {{ $assessment->year }}
                            </td>
                            <td>{{ $assessment->assessor->name }}</td>
                            <td>{{ number_format($assessment->total_score, 1) }}</td>
                            <td>
                                <span class="badge {{ $assessment->status == 'approved' ? 'badge-success' : ($assessment->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ ucfirst($assessment->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sakip.assessments.show', $assessment) }}">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-clipboard-check text-muted"></i>
                <p class="mb-0">Belum ada penilaian</p>
                <small class="text-muted">Buat penilaian untuk indikator ini.</small>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveTarget(targetId, indicatorId) {
    if (!confirm('Apakah Anda yakin ingin menyetujui target ini?')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/sakip/indicators/${indicatorId}/targets/${targetId}/approve`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}

function rejectTarget(targetId, indicatorId) {
    const notes = prompt('Masukkan alasan penolakan:');
    if (!notes) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/sakip/indicators/${indicatorId}/targets/${targetId}/reject`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const notesInput = document.createElement('input');
    notesInput.type = 'hidden';
    notesInput.name = 'notes';
    notesInput.value = notes;
    form.appendChild(notesInput);

    document.body.appendChild(form);
    form.submit();
}

function reviseTarget(targetId, indicatorId) {
    const notes = prompt('Masukkan catatan revisi:');
    if (!notes) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/sakip/indicators/${indicatorId}/targets/${targetId}/revise`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const notesInput = document.createElement('input');
    notesInput.type = 'hidden';
    notesInput.name = 'notes';
    notesInput.value = notes;
    form.appendChild(notesInput);

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
