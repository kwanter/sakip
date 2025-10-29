@extends('layouts.app')

@section('title', 'Detail Indikator Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.indicators.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Detail Indikator Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">Informasi lengkap indikator kinerja</p>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="mb-6 flex items-center space-x-3">
            <a href="{{ route('sakip.indicators.edit', $indicator) }}" class="inline-flex items-center px-3 py-2 bg-blue-800 text-white text-sm font-medium rounded-md hover:bg-blue-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>

            <form action="{{ route('sakip.indicators.destroy', $indicator) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus indikator ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>

        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Kode Indikator</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $indicator->code }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nama Indikator</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $indicator->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                    <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $indicator->category)) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Satuan</label>
                    <p class="text-gray-900">{{ $indicator->measurement_unit }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Instansi</label>
                    <p class="text-gray-900">{{ $indicator->instansi->nama_instansi ?? '-' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Frekuensi Pengukuran</label>
                    <p class="text-gray-900">{{ ucfirst($indicator->frequency) }}</p>
                </div>
            </div>

            @if($indicator->description)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi</label>
                <p class="text-gray-900">{{ $indicator->description }}</p>
            </div>
            @endif
        </div>

        <!-- Target Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Target Kinerja</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Tahun {{ $currentYear }}</span>
                    @can('update', $indicator)
                        <a href="{{ route('sakip.targets.create', $indicator) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Target
                        </a>
                    @endcan
                </div>
            </div>

            @if($currentTargets && $currentTargets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Minimum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Justifikasi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($currentTargets as $target)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $target->year }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="text-2xl font-bold text-blue-600">{{ number_format($target->target_value, 2) }}</span>
                                        <span class="text-sm text-gray-500">{{ $indicator->measurement_unit }}</span>
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
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $target->justification ?? '-' }}
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

                                        {{-- Approval workflow buttons for draft status --}}
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada target</h3>
                    <p class="mt-1 text-sm text-gray-500">Target untuk tahun {{ $currentYear }} belum ditetapkan.</p>
                </div>
            @endif

            <!-- All Targets History -->
            @if($indicator->targets && $indicator->targets->count() > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Riwayat Target</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($indicator->targets()->orderBy('year', 'desc')->get() as $target)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm text-gray-500">Tahun {{ $target->year }}</div>
                                <div class="text-xl font-bold text-gray-900">{{ number_format($target->target_value, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $indicator->measurement_unit }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Assessment Criteria -->
        @if(isset($indicator->criteria) && $indicator->criteria->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Kriteria Penilaian</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kriteria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot (%)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($indicator->criteria as $criterion)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $criterion->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $criterion->weight }}%</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $criterion->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Performance Data -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Data Kinerja Terbaru</h3>
                <a href="{{ route('sakip.data-collection.create') }}?indicator_id={{ $indicator->id }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Data
                </a>
            </div>

            @if(isset($recentData) && $recentData->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pencapaian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentData as $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $data->period }} {{ $data->year }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data->value, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($indicator->target_value > 0)
                                    {{ number_format(($data->value / $indicator->target_value) * 100, 1) }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $data->status == 'validated' ? 'bg-green-100 text-green-800' : ($data->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($data->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('sakip.data-collection.show', $data) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data kinerja</h3>
                <p class="mt-1 text-sm text-gray-500">Mulai input data kinerja untuk indikator ini.</p>
            </div>
            @endif
        </div>

        <!-- Recent Assessments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Penilaian Terbaru</h3>
                <a href="{{ route('sakip.assessments.create') }}?indicator_id={{ $indicator->id }}" class="inline-flex items-center px-3 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Buat Penilaian
                </a>
            </div>

            @if(isset($recentAssessments) && $recentAssessments->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentAssessments as $assessment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $assessment->period }} {{ $assessment->year }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $assessment->assessor->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($assessment->total_score, 1) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assessment->status == 'approved' ? 'bg-green-100 text-green-800' : ($assessment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($assessment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('sakip.assessments.show', $assessment) }}" class="text-blue-600 hover:text-blue-900">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada penilaian</h3>
                <p class="mt-1 text-sm text-gray-500">Buat penilaian untuk indikator ini.</p>
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
@stop
