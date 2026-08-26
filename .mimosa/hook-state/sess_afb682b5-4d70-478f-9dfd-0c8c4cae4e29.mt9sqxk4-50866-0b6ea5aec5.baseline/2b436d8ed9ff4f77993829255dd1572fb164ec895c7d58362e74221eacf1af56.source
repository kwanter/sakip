@extends('layouts.modern')

@section('title', 'Buat Penilaian Kinerja')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.assessments.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Buat Penilaian Kinerja</h1>
                <p class="page-header-subtitle">Evaluasi dan berikan penilaian kinerja instansi</p>
            </div>
        </div>
    </div>

    <!-- Alert Section -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div>
            <strong>Terjadi kesalahan</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Assessment Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sakip.assessments.store') }}" method="POST" id="assessmentForm">
                @csrf

                <!-- Section: Informasi Dasar -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-info-circle"></i>
                        <span>Informasi Dasar</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="performance_data_id" class="form-label">Data Kinerja <span class="text-danger">*</span></label>
                                <select name="performance_data_id" id="performance_data_id" required class="form-select @error('performance_data_id') is-invalid @enderror">
                                    <option value="">Pilih Data Kinerja</option>
                                    @foreach($performanceData as $data)
                                        <option value="{{ $data->id }}">{{ $data->indicator->code }} - {{ $data->indicator->name }}</option>
                                    @endforeach
                                </select>
                                @error('performance_data_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="assessor_id" class="form-label">Penilai <span class="text-danger">*</span></label>
                                <select name="assessor_id" id="assessor_id" required class="form-select @error('assessor_id') is-invalid @enderror">
                                    <option value="">Pilih Penilai</option>
                                    @foreach($assessors as $assessor)
                                        <option value="{{ $assessor->id }}">{{ $assessor->name }}</option>
                                    @endforeach
                                </select>
                                @error('assessor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="period" class="form-label">Periode <span class="text-danger">*</span></label>
                                <select name="period" id="period" required class="form-select @error('period') is-invalid @enderror">
                                    <option value="">Pilih Periode</option>
                                    <option value="triwulan-1">Triwulan I (Januari - Maret)</option>
                                    <option value="triwulan-2">Triwulan II (April - Juni)</option>
                                    <option value="triwulan-3">Triwulan III (Juli - September)</option>
                                    <option value="triwulan-4">Triwulan IV (Oktober - Desember)</option>
                                    <option value="bulanan">Bulanan</option>
                                    <option value="tahunan">Tahunan</option>
                                </select>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="year" class="form-label">Tahun <span class="text-danger">*</span></label>
                                <select name="year" id="year" required class="form-select @error('year') is-invalid @enderror">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Penilaian -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Penilaian</span>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="score" class="form-label">Skor (0-100) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="score" id="score" min="0" max="100" step="0.1" required class="form-control @error('score') is-invalid @enderror" placeholder="Masukkan skor 0-100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Skor akan menentukan grade penilaian secara otomatis</div>
                                @error('score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" required class="form-select @error('status') is-invalid @enderror">
                                    <option value="">Pilih Status</option>
                                    <option value="pending">Menunggu Penilaian</option>
                                    <option value="in_review">Dalam Penilaian</option>
                                    <option value="completed">Selesai</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="needs_revision">Perlu Revisi</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label for="notes" class="form-label">Catatan Penilaian</label>
                                <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Masukkan catatan, komentar, atau rekomendasi penilaian..."></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grade Preview -->
                <div class="alert alert-info mb-4" id="gradePreview" style="display: none;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-star fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-1">Prediksi Grade</h6>
                            <p class="mb-0" id="gradeText">-</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.assessments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span class="ms-1">Simpan Penilaian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reference Card -->
    <div class="card mt-4">
        <div class="card-body">
            <h6 class="card-title">
                <i class="fas fa-info-circle me-2 text-info"></i>
                Referensi Penilaian
            </h6>
            <div class="row text-center">
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded bg-success bg-opacity-10">
                        <span class="badge bg-success fs-6 mb-2">A</span>
                        <p class="mb-0 small">90 - 100<br><strong>Sangat Baik</strong></p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded bg-info bg-opacity-10">
                        <span class="badge bg-info fs-6 mb-2">B</span>
                        <p class="mb-0 small">75 - 89<br><strong>Baik</strong></p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded bg-warning bg-opacity-10">
                        <span class="badge bg-warning fs-6 mb-2">C</span>
                        <p class="mb-0 small">60 - 74<br><strong>Cukup</strong></p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-3 border rounded bg-danger bg-opacity-10">
                        <span class="badge bg-danger fs-6 mb-2">D</span>
                        <p class="mb-0 small">&lt; 60<br><strong>Kurang</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Grade calculation based on score - using safe DOM methods
    const scoreInput = document.getElementById('score');
    const gradePreview = document.getElementById('gradePreview');
    const gradeText = document.getElementById('gradeText');

    // Pre-create badge elements
    const gradeBadges = {
        A: createBadge('A', 'bg-success'),
        B: createBadge('B', 'bg-info'),
        C: createBadge('C', 'bg-warning'),
        D: createBadge('D', 'bg-danger')
    };

    const gradeDescriptions = {
        A: 'Sangat Baik',
        B: 'Baik',
        C: 'Cukup',
        D: 'Kurang'
    };

    function createBadge(text, className) {
        const span = document.createElement('span');
        span.className = 'badge ' + className + ' me-2';
        span.textContent = text;
        return span;
    }

    function updateGradePreview(score) {
        gradeText.innerHTML = '';

        if (score >= 90) {
            gradeText.appendChild(gradeBadges.A.cloneNode(true));
            gradeText.appendChild(document.createTextNode(' (' + score + '%) - Sangat Baik'));
        } else if (score >= 75) {
            gradeText.appendChild(gradeBadges.B.cloneNode(true));
            gradeText.appendChild(document.createTextNode(' (' + score + '%) - Baik'));
        } else if (score >= 60) {
            gradeText.appendChild(gradeBadges.C.cloneNode(true));
            gradeText.appendChild(document.createTextNode(' (' + score + '%) - Cukup'));
        } else {
            gradeText.appendChild(gradeBadges.D.cloneNode(true));
            gradeText.appendChild(document.createTextNode(' (' + score + '%) - Kurang'));
        }
    }

    scoreInput.addEventListener('input', function() {
        const score = parseFloat(this.value);

        if (!isNaN(score) && score >= 0 && score <= 100) {
            gradePreview.style.display = 'block';
            updateGradePreview(score);
        } else {
            gradePreview.style.display = 'none';
        }
    });
</script>
@endpush
@endsection
