@extends('layouts.modern')

@section('title', 'Tambah Target Kinerja')

@section('page-title', 'Tambah Target Kinerja')

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
                        <h1 class="page-header-title">Tambah Target Kinerja</h1>
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

            <!-- Form -->
            <form action="{{ route('sakip.targets.store', $indicator) }}" method="POST">
                @csrf

                <div class="modern-card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Informasi Target</h6>

                        <!-- Year -->
                        <div class="form-group mb-3">
                            <label for="year" class="form-label">
                                Tahun <span class="text-danger">*</span>
                            </label>
                            <select name="year" id="year" required class="form-select @error('year') is-invalid @enderror">
                                <option value="">-- Pilih Tahun --</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('year')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Target Value -->
                        <div class="form-group mb-3">
                            <label for="target_value" class="form-label">
                                Nilai Target <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="target_value" id="target_value" value="{{ old('target_value') }}" required class="form-control @error('target_value') is-invalid @enderror" placeholder="0.00">
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
                                <input type="number" step="0.01" name="minimum_value" id="minimum_value" value="{{ old('minimum_value') }}" class="form-control @error('minimum_value') is-invalid @enderror" placeholder="0.00">
                                <span class="input-group-text">{{ $indicator->measurement_unit }}</span>
                            </div>
                            @error('minimum_value')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Nilai minimum yang dapat diterima</div>
                        </div>

                        <!-- Justification -->
                        <div class="form-group mb-0">
                            <label for="justification" class="form-label">
                                Justifikasi (Opsional)
                            </label>
                            <textarea name="justification" id="justification" rows="4" class="form-control @error('justification') is-invalid @enderror" placeholder="Jelaskan alasan penetapan target ini...">{{ old('justification') }}</textarea>
                            @error('justification')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">Alasan atau dasar penetapan nilai target</div>
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
                        <span class="ms-1">Simpan Target</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
