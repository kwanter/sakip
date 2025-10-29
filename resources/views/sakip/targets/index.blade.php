@extends('layouts.app')

@section('title', 'Daftar Target Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <a href="{{ route('sakip.indicators.show', $indicator) }}" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </a>
                        <h1 class="ml-4 text-3xl font-bold text-gray-900">Daftar Target Kinerja</h1>
                    </div>
                    <p class="mt-2 text-gray-600">{{ $indicator->code }} - {{ $indicator->name }}</p>
                </div>
                @can('update', $indicator)
                <a href="{{ route('sakip.targets.create', $indicator) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Target
                </a>
                @endcan
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="ml-3 text-sm font-medium text-blue-800">{{ session('info') }}</p>
            </div>
        </div>
        @endif

        <!-- Targets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            @if($targets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tahun
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nilai Target
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nilai Minimum
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Justifikasi
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Disetujui Oleh
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($targets as $target)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $target->year }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-blue-600">{{ number_format($target->target_value, 2) }}</span>
                                            <span class="ml-2 text-sm text-gray-500">{{ $indicator->measurement_unit }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $target->minimum_value ? number_format($target->minimum_value, 2) . ' ' . $indicator->measurement_unit : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'revised' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Draft',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak',
                                                'revised' => 'Revisi'
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$target->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$target->status] ?? ucfirst($target->status) }}
                                        </span>
                                        @if($target->notes)
                                            <button onclick="showNotes('{{ $target->id }}')" class="ml-1 text-yellow-500 hover:text-yellow-700" title="Lihat Catatan">
                                                <i class="fas fa-comment-dots"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="max-w-xs truncate" title="{{ $target->justification }}">
                                            {{ $target->justification ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($target->approved_by)
                                            {{ $target->approver->name ?? '-' }}
                                            <div class="text-xs text-gray-400">
                                                {{ $target->approved_at ? $target->approved_at->format('d M Y') : '-' }}
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        {{-- Edit button available for all statuses --}}
                                        @can('update', $indicator)
                                            <a href="{{ route('sakip.targets.edit', [$indicator, $target]) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        {{-- Delete button only for draft or rejected --}}
                                        @if($target->status === 'draft' || $target->status === 'rejected')
                                            @can('update', $indicator)
                                                <form action="{{ route('sakip.targets.destroy', [$indicator, $target]) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus target ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        {{-- Approval workflow buttons for draft and revised status --}}
                                        @can('approve-targets')
                                            @if($target->status === 'draft' || $target->status === 'revised')
                                                <button onclick="approveTarget({{ $target->id }}, '{{ $indicator->id }}')" class="text-green-600 hover:text-green-900" title="Setujui">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button onclick="reviseTarget({{ $target->id }}, '{{ $indicator->id }}')" class="text-yellow-600 hover:text-yellow-900" title="Minta Revisi">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <button onclick="rejectTarget({{ $target->id }}, '{{ $indicator->id }}')" class="text-red-600 hover:text-red-900" title="Tolak">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>

                                <!-- Hidden notes row -->
                                <tr id="notes-{{ $target->id }}" class="hidden bg-yellow-50">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <h4 class="text-sm font-medium text-yellow-800">Catatan:</h4>
                                                <p class="mt-1 text-sm text-yellow-700">{{ $target->notes }}</p>
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
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $targets->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada target</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan target untuk indikator ini.</p>
                    @can('update', $indicator)
                    <div class="mt-6">
                        <a href="{{ route('sakip.targets.create', $indicator) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Tambah Target
                        </a>
                    </div>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function showNotes(targetId) {
    const notesRow = document.getElementById(`notes-${targetId}`);
    notesRow.classList.toggle('hidden');
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
