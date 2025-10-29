@extends('layouts.app')

@section('title', 'Edit Target Kinerja')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">Edit Target Kinerja</h1>
            </div>
            <p class="mt-2 text-gray-600">{{ $indicator->code }} - {{ $indicator->name }}</p>
        </div>

        <!-- Alert Notifications -->
        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Warning for approved targets --}}
        @if($target->status === 'approved')
        <div class="mb-6 bg-orange-50 border border-orange-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-orange-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-orange-800">Peringatan: Target Sudah Disetujui</h3>
                    <p class="mt-1 text-sm text-orange-700">
                        Target ini sudah disetujui. Jika Anda melakukan perubahan, status target akan direset ke <strong>Draft</strong> dan perlu mendapatkan persetujuan kembali.
                    </p>
                    @if($target->approver)
                    <p class="mt-1 text-xs text-orange-600">
                        Disetujui oleh: <strong>{{ $target->approver->name }}</strong> pada {{ $target->approved_at ? $target->approved_at->format('d M Y, H:i') : '-' }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Notes for rejected/revised targets --}}
        @if($target->notes && in_array($target->status, ['rejected', 'revised']))
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        @if($target->status === 'rejected')
                            Alasan Penolakan:
                        @else
                            Catatan Revisi:
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-yellow-700">{{ $target->notes }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('sakip.targets.update', [$indicator, $target]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Informasi Target</h3>

                <!-- Year (Read-only) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun
                    </label>
                    <div class="block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700">
                        {{ $target->year }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Tahun tidak dapat diubah</p>
                </div>

                <!-- Target Value -->
                <div class="mb-6">
                    <label for="target_value" class="block text-sm font-medium text-gray-700 mb-2">
                        Nilai Target <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" name="target_value" id="target_value" value="{{ old('target_value', $target->target_value) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('target_value') border-red-300 @enderror" placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $indicator->measurement_unit }}</span>
                        </div>
                    </div>
                    @error('target_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Nilai target yang ingin dicapai untuk tahun tersebut</p>
                </div>

                <!-- Minimum Value -->
                <div class="mb-6">
                    <label for="minimum_value" class="block text-sm font-medium text-gray-700 mb-2">
                        Nilai Minimum (Opsional)
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" name="minimum_value" id="minimum_value" value="{{ old('minimum_value', $target->minimum_value) }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('minimum_value') border-red-300 @enderror" placeholder="0.00">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $indicator->measurement_unit }}</span>
                        </div>
                    </div>
                    @error('minimum_value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Nilai minimum yang dapat diterima</p>
                </div>

                <!-- Justification -->
                <div class="mb-6">
                    <label for="justification" class="block text-sm font-medium text-gray-700 mb-2">
                        Justifikasi (Opsional)
                    </label>
                    <textarea name="justification" id="justification" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('justification') border-red-300 @enderror" placeholder="Jelaskan alasan penetapan target ini...">{{ old('justification', $target->justification) }}</textarea>
                    @error('justification')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Alasan atau dasar penetapan nilai target</p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Setelah diperbarui, status target akan kembali ke <strong>Draft</strong> dan perlu disetujui kembali.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('sakip.indicators.show', $indicator) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Perbarui Target
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
