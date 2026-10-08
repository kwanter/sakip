@extends('layouts.modern')

@section('title', 'Daftar Target Kinerja')

@section('page-title', 'Daftar Target Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Daftar Target Kinerja</h1>
                <p class="page-header-subtitle">{{ $indicator->code }} - {{ $indicator->name }}</p>
            </div>
            <div class="page-header-actions">
                @can('update', $indicator)
                <a href="{{ route('sakip.targets.create', $indicator) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span class="ms-1">Tambah Target</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Info Messages -->
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-info-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-message">{{ session('info') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Targets Table -->
    <div class="modern-table-container">
        @if($targets->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th scope="col">
                                Tahun
                            </th>
                            <th scope="col">
                                Nilai Target
                            </th>
                            <th scope="col">
                                Nilai Minimum
                            </th>
                            <th scope="col">
                                Status
                            </th>
                            <th scope="col">
                                Justifikasi
                            </th>
                            <th scope="col">
                                Disetujui Oleh
                            </th>
                            <th scope="col" class="text-end">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targets as $target)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $target->year }}</span>
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
                                    @if($target->notes)
                                        <button data-onclick="showNotes('{{ $target->id }}')" class="btn btn-sm btn-outline-warning ms-1" title="Lihat Catatan">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 220px;" title="{{ $target->justification }}">
                                        {{ $target->justification ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    @if($target->approved_by)
                                        {{ $target->approver->name ?? '-' }}
                                        <div>
                                            <small class="text-muted">
                                                {{ $target->approved_at ? $target->approved_at->format('d M Y') : '-' }}
                                            </small>
                                        </div>
                                    @else
                                        -
                                    @endif
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
                                                <form action="{{ route('sakip.targets.destroy', [$indicator, $target]) }}" method="POST" class="d-inline" data-confirm="Yakin ingin menghapus target ini?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        {{-- Approval workflow buttons for draft and revised status --}}
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

                            <!-- Hidden notes row -->
                            <tr id="notes-{{ $target->id }}" class="d-none">
                                <td colspan="7">
                                    <div class="alert alert-warning mb-0" role="alert">
                                        <i class="fas fa-info-circle alert-icon"></i>
                                        <div class="alert-content">
                                            <div class="alert-title">Catatan:</div>
                                            <div class="alert-message">{{ $target->notes }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($targets->hasPages())
            <div class="p-3 border-top">
                {{ $targets->links() }}
            </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fas fa-bullseye text-muted"></i>
                <p class="mb-0">Belum ada target</p>
                <small class="text-muted">Mulai dengan menambahkan target untuk indikator ini.</small>
                @can('update', $indicator)
                <div class="mt-3">
                    <a href="{{ route('sakip.targets.create', $indicator) }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span class="ms-1">Tambah Target</span>
                    </a>
                </div>
                @endcan
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ app()->bound('csp-nonce') ? app('csp-nonce') : '' }}">
function showNotes(targetId) {
    const notesRow = document.getElementById(`notes-${targetId}`);
    notesRow.classList.toggle('d-none');
}

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
