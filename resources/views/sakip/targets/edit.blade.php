@extends('layouts.modern')

@section('title', 'Edit Target Kinerja')

@section('page-title', 'Edit Target Kinerja')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-layout">
                    <div>
                        <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-outline-secondary btn-sm mb-2">
                            <i class="fas fa-arrow-left"></i>
                            <span class="ms-1">Kembali</span>
                        </a>
                        <h1 class="page-header-title">Edit Target Kinerja</h1>
                        <p class="page-header-subtitle">{{ $indicator->code }} - {{ $indicator->name }}</p>
                    </div>
                </div>
            </div>

            <!-- Alert Notifications -->
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div class="alert-content">
                    <div class="alert-title">Terdapat kesalahan:</div>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- Warning for approved targets --}}
            @if($target->status === 'approved')
            <div class="alert alert-warning mb-4" role="alert">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-content">
                    <div class="alert-title">Peringatan: Target Sudah Disetujui</div>
                    <div class="alert-message">
                        Target ini sudah disetujui. Jika Anda melakukan perubahan, status target akan direset ke <strong>Draft</strong> dan perlu mendapatkan persetujuan kembali.
                    </div>
                    @if($target->approver)
                    <div class="alert-message mt-1">
                        <small>
                            Disetujui oleh: <strong>{{ $target->approver->name }}</strong> pada {{ $target->approved_at ? $target->approved_at->format('d M Y, H:i') : '-' }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notes for rejected/revised targets --}}
            @if($target->notes && in_array($target->status, ['rejected', 'revised']))
            <div class="alert alert-warning mb-4" role="alert">
                <i class="fas fa-info-circle alert-icon"></i>
                <div class="alert-content">
                    <div class="alert-title">
                        @if($target->status === 'rejected')
                            Alasan Penolakan:
                        @else
                            Catatan Revisi:
                        @endif
                    </div>
                    <div class="alert-message">{{ $target->notes }}</div>
                </div>
            </div>
            @endif

            <!-- Form -->
            <form action="{{ route('sakip.targets.update', [$indicator, $target]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modern-card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Informasi Target</h6>

                        <!-- Year (Read-only) -->
                        <div class="form-group mb-3">
                            <label class="form-label">
                                Tahun
                            </label>
                            <input type="text" class="form-control" value="{{ $target->year }}" disabled>
                            <div class="form-help">Tahun tidak dapat diubah</div>
                        </div>

                        <!-- Target Value -->
                        <div class="form-group mb-3">
                            <label for="target_value" class="form-label">
                                Nilai Target <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="target_value" id="target_value" value="{{ old('target_value', $target->target_value) }}" required class="form-control @error('target_value') is-invalid @enderror" placeholder="0.00">
                                <span class="input-group-text">{{ $indicator->measurement_unit }}</span>
                            </div>
                            @error('target_value')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Nilai target yang ingin dicapai untuk tahun tersebut</div>
                        </div>

                        <!-- Minimum Value -->
                        <div class="form-group mb-3">
                            <label for="minimum_value" class="form-label">
                                Nilai Minimum (Opsional)
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="minimum_value" id="minimum_value" value="{{ old('minimum_value', $target->minimum_value) }}" class="form-control @error('minimum_value') is-invalid @enderror" placeholder="0.00">
                                <span class="input-group-text">{{ $indicator->measurement_unit }}</span>
                            </div>
                            @error('minimum_value')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Nilai minimum yang dapat diterima</div>
                        </div>

                        <!-- Justification -->
                        <div class="form-group mb-3">
                            <label for="justification" class="form-label">
                                Justifikasi (Opsional)
                            </label>
                            <textarea name="justification" id="justification" rows="4" class="form-control @error('justification') is-invalid @enderror" placeholder="Jelaskan alasan penetapan target ini...">{{ old('justification', $target->justification) }}</textarea>
                            @error('justification')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Alasan atau dasar penetapan nilai target</div>
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info mb-0" role="alert">
                            <i class="fas fa-info-circle alert-icon"></i>
                            <div class="alert-content">
                                <div class="alert-message">
                                    Setelah diperbarui, status target akan kembali ke <strong>Draft</strong> dan perlu disetujui kembali.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="{{ route('sakip.indicators.show', $indicator) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        <span class="ms-1">Perbarui Target</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
