@extends('layouts.modern')

@section('title', 'Buat Laporan SAKIP')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <a href="{{ route('sakip.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span class="ms-1">Kembali</span>
                </a>
                <h1 class="page-header-title">Buat Laporan SAKIP</h1>
                <p class="page-header-subtitle">Generate laporan kinerja dari data yang telah dikumpulkan</p>
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

    <!-- Report Form -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sakip.reports.store') }}" method="POST" id="reportForm">
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
                                <label for="title" class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" required class="form-control @error('title') is-invalid @enderror" placeholder="Masukkan judul laporan">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="report_type" class="form-label">Jenis Laporan <span class="text-danger">*</span></label>
                                <select name="report_type" id="report_type" required class="form-select @error('report_type') is-invalid @enderror">
                                    <option value="">Pilih Jenis Laporan</option>
                                    <option value="monthly">Laporan Bulanan</option>
                                    <option value="quarterly">Laporan Triwulan</option>
                                    <option value="semester">Laporan Semester</option>
                                    <option value="annual">Laporan Tahunan</option>
                                    <option value="custom">Laporan Custom</option>
                                </select>
                                @error('report_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Kategori Laporan <span class="text-danger">*</span></label>
                                <select name="category" id="category" required class="form-select @error('category') is-invalid @enderror">
                                    <option value="">Pilih Kategori</option>
                                    <option value="performance">Laporan Kinerja</option>
                                    <option value="assessment">Laporan Penilaian</option>
                                    <option value="compliance">Laporan Kepatuhan</option>
                                    <option value="summary">Laporan Ringkasan</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="period" class="form-label">Periode <span class="text-danger">*</span></label>
                                <select name="period" id="period" required class="form-select @error('period') is-invalid @enderror">
                                    <option value="">Pilih Periode</option>
                                    @foreach($availablePeriods ?? [] as $availablePeriod)
                                        <option value="{{ $availablePeriod['value'] }}">{{ $availablePeriod['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('period')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="template_id" class="form-label">Template Laporan</label>
                                <select name="template_id" id="template_id" class="form-select @error('template_id') is-invalid @enderror">
                                    <option value="">Pilih Template (Opsional)</option>
                                    @foreach($templates ?? [] as $template)
                                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                @error('template_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Indikator Kinerja -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-chart-line"></i>
                        <span>Indikator Kinerja</span>
                    </div>
                    <div class="form-text mb-3">Pilih indikator kinerja yang akan dimasukkan dalam laporan (minimal 1)</div>

                    <div class="indicator-list">
                        @if(isset($indicators) && $indicators->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">Tidak ada indikator kinerja tersedia</p>
                            </div>
                        @elseif(isset($indicators))
                            <div class="row">
                                @foreach($indicators as $indicator)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check form-check-card p-2 rounded">
                                        <input type="checkbox" name="indicators[]" value="{{ $indicator->id }}" id="indicator_{{ $indicator->id }}" class="form-check-input">
                                        <label for="indicator_{{ $indicator->id }}" class="form-check-label">
                                            <span class="badge bg-light text-dark me-1">{{ $indicator->code }}</span>
                                            {{ $indicator->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @error('indicators')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Section: Opsi Laporan -->
                <div class="form-section mb-4">
                    <div class="form-section-header">
                        <i class="fas fa-cog"></i>
                        <span>Opsi Laporan</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Deskripsi Laporan</label>
                                <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Masukkan deskripsi laporan (opsional)"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="include_assessments" id="include_assessments" value="1" class="form-check-input">
                                <label for="include_assessments" class="form-check-label">Sertakan Penilaian</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="include_benchmarks" id="include_benchmarks" value="1" class="form-check-input">
                                <label for="include_benchmarks" class="form-check-label">Sertakan Benchmark</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="include_recommendations" id="include_recommendations" value="1" class="form-check-input">
                                <label for="include_recommendations" class="form-check-label">Sertakan Rekomendasi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="format" class="form-label">Format Output <span class="text-danger">*</span></label>
                                <select name="format" id="format" required class="form-select @error('format') is-invalid @enderror">
                                    <option value="">Pilih Format</option>
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="word">Word</option>
                                </select>
                                @error('format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="{{ route('sakip.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                        <span class="ms-1">Batal</span>
                    </a>
                    <div class="btn-group">
                        <button type="button" id="preview-btn" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#previewModal">
                            <i class="fas fa-eye"></i>
                            <span class="ms-1">Pratinjau</span>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i>
                            <span class="ms-1">Buat Laporan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2 text-primary"></i>
                    Pratinjau Laporan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="preview-content">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-file-alt fa-3x mb-3"></i>
                    <p>Isi form dan klik Pratinjau untuk melihat hasil</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    <span class="ms-1">Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('preview-btn');
    const previewContent = document.getElementById('preview-content');

    previewBtn.addEventListener('click', function() {
        const title = document.getElementById('title').value;
        const reportType = document.getElementById('report_type');
        const typeText = reportType.options[reportType.selectedIndex]?.text || '[Jenis Laporan]';
        const period = document.getElementById('period');
        const periodText = period.options[period.selectedIndex]?.text || '[Periode]';
        const description = document.getElementById('description').value;
        const format = document.getElementById('format');
        const formatText = format.options[format.selectedIndex]?.text || '[Format]';

        // Get selected indicators using DOM methods
        const selectedIndicators = [];
        document.querySelectorAll('input[name="indicators[]"]:checked').forEach(function(checkbox) {
            const label = document.querySelector('label[for="' + checkbox.id + '"]');
            if (label) {
                selectedIndicators.push(label.textContent.trim());
            }
        });

        // Build preview content using DOM methods - no innerHTML with user content
        previewContent.textContent = '';
        
        const container = document.createElement('div');
        
        // Header
        const header = document.createElement('div');
        header.className = 'text-center mb-4 pb-3 border-bottom';
        
        const titleEl = document.createElement('h4');
        titleEl.className = 'mb-2';
        titleEl.textContent = title || '[Judul Laporan]';
        
        const metaInfo = document.createElement('p');
        metaInfo.className = 'text-muted mb-1';
        metaInfo.textContent = typeText + ' - ' + periodText;
        
        const formatInfo = document.createElement('p');
        formatInfo.className = 'text-muted small mb-0';
        formatInfo.innerHTML = '<i class="fas fa-file-export me-1"></i>' + formatText;
        
        header.appendChild(titleEl);
        header.appendChild(metaInfo);
        header.appendChild(formatInfo);
        container.appendChild(header);

        if (description) {
            const descSection = document.createElement('div');
            descSection.className = 'mb-3';
            
            const descTitle = document.createElement('h6');
            descTitle.innerHTML = '<i class="fas fa-align-left me-2 text-muted"></i>Deskripsi:';
            
            const descText = document.createElement('div');
            descText.className = 'p-3 bg-light rounded small';
            descText.textContent = description;
            
            descSection.appendChild(descTitle);
            descSection.appendChild(descText);
            container.appendChild(descSection);
        }

        if (selectedIndicators.length > 0) {
            const indicatorSection = document.createElement('div');
            
            const indTitle = document.createElement('h6');
            indTitle.innerHTML = '<i class="fas fa-chart-line me-2 text-muted"></i>Indikator yang dipilih:';
            
            const badgeContainer = document.createElement('div');
            badgeContainer.className = 'd-flex flex-wrap gap-2';
            
            selectedIndicators.forEach(function(ind) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary';
                badge.textContent = ind;
                badgeContainer.appendChild(badge);
            });
            
            indicatorSection.appendChild(indTitle);
            indicatorSection.appendChild(badgeContainer);
            container.appendChild(indicatorSection);
        }

        // Options selected
        const optionsSection = document.createElement('div');
        const optionsTitle = document.createElement('h6');
        optionsTitle.innerHTML = '<i class="fas fa-cog me-2 text-muted"></i>Opsi:';
        
        const optionsList = document.createElement('ul');
        optionsList.className = 'list-unstyled small';
        
        const options = [];
        if (document.getElementById('include_assessments').checked) {
            options.push('Sertakan Penilaian');
        }
        if (document.getElementById('include_benchmarks').checked) {
            options.push('Sertakan Benchmark');
        }
        if (document.getElementById('include_recommendations').checked) {
            options.push('Sertakan Rekomendasi');
        }
        
        if (options.length > 0) {
            options.forEach(function(opt) {
                const li = document.createElement('li');
                li.innerHTML = '<i class="fas fa-check text-success me-2"></i>' + opt;
                optionsList.appendChild(li);
            });
            optionsSection.appendChild(optionsTitle);
            optionsSection.appendChild(optionsList);
            container.appendChild(optionsSection);
        }

        previewContent.appendChild(container);
    });
});
</script>
@endpush
@endsection
