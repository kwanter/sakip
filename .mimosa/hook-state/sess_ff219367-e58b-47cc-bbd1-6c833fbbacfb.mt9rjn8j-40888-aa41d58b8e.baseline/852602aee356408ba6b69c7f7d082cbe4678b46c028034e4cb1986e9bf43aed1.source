@extends('layouts.app')

@section('page-title', 'Tambah Data Kinerja')

@section('content')
<!-- Breadcrumbs -->
<nav class="breadcrumbs">
    <a href="{{ route('sakip.dashboard') }}" class="breadcrumb-item">
        <i class="fas fa-home"></i>
    </a>
    <span class="breadcrumb-separator"><i class="fas fa-chevron-right" style="font-size: 0.625rem;"></i></span>
    <a href="{{ route('sakip.data-collection.index') }}" class="breadcrumb-item">
        <span>Pengumpulan Data</span>
    </a>
    <span class="breadcrumb-separator"><i class="fas fa-chevron-right" style="font-size: 0.625rem;"></i></span>
    <span class="breadcrumb-item active">Tambah Data</span>
</nav>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-lg font-semibold text-primary">Tambah Data Kinerja</h1>
        <p class="text-secondary text-sm">Isi formulir di bawah untuk menambahkan data kinerja baru.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('sakip.data-collection.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>
</div>

<!-- Multi-step Form -->
<div class="modern-card" style="max-width: 800px; margin: 0 auto;">
    <!-- Progress Steps -->
    <div class="form-steps">
        <div class="form-step active" data-step="1">
            <div class="form-step-number">1</div>
            <div class="form-step-label">Pilih Indikator</div>
        </div>
        <div class="form-step-connector"></div>
        <div class="form-step" data-step="2">
            <div class="form-step-number">2</div>
            <div class="form-step-label">Isi Data</div>
        </div>
        <div class="form-step-connector"></div>
        <div class="form-step" data-step="3">
            <div class="form-step-number">3</div>
            <div class="form-step-label">Unggah Bukti</div>
        </div>
        <div class="form-step-connector"></div>
        <div class="form-step" data-step="4">
            <div class="form-step-number">4</div>
            <div class="form-step-label">Konfirmasi</div>
        </div>
    </div>

    <form action="{{ route('sakip.data-collection.store') }}" method="POST" enctype="multipart/form-data" id="dataCollectionForm" class="form-content">
        @csrf

        <!-- Step 1: Select Indicator -->
        <div class="form-panel active" data-panel="1">
            <div class="panel-header">
                <h3 class="panel-title">Pilih Indikator Kinerja</h3>
                <p class="panel-description">Pilih indikator kinerja yang akan dilaporkan.</p>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <label class="form-label required" for="indicator_id">Indikator Kinerja</label>
                    <select name="indicator_id" id="indicator_id" class="form-select" required>
                        <option value="">-- Pilih Indikator --</option>
                        @foreach($indicators ?? [] as $indicator)
                        <option value="{{ $indicator->id }}" data-unit="{{ $indicator->unit ?? '' }}" data-target="{{ $indicator->target ?? 0 }}">
                            {{ $indicator->code }} - {{ $indicator->name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="form-help">Mulai mengetik untuk mencari indikator...</small>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="instansi_id">Instansi</label>
                    <select name="instansi_id" id="instansi_id" class="form-select" required>
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($instansis ?? [] as $instansi)
                        <option value="{{ $instansi->id }}">{{ $instansi->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="period">Periode</label>
                    <input type="month" name="period" id="period" class="form-input" required value="{{ date('Y-m') }}">
                    <small class="form-help">Periode pelaporan data kinerja.</small>
                </div>
            </div>

            <div class="panel-footer">
                <button type="button" class="btn btn-primary next-step" data-next="2">
                    <span>Lanjut</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Enter Data -->
        <div class="form-panel" data-panel="2">
            <div class="panel-header">
                <h3 class="panel-title">Isi Data Kinerja</h3>
                <p class="panel-description">Masukkan nilai aktual dan informasi terkait.</p>
            </div>

            <div class="panel-body">
                <div class="info-banner info" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Target:</strong> <span id="targetValue">-</span>
                        <span id="unitDisplay"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label required" for="actual_value">Nilai Aktual</label>
                        <input type="number" step="0.01" name="actual_value" id="actual_value" class="form-input" required placeholder="0.00">
                        <small class="form-help">Nilai capaian kinerja yang dicapai.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="target_value">Nilai Target (Opsional)</label>
                        <input type="number" step="0.01" name="target_value" id="target_value_override" class="form-input" placeholder="Gunakan target default">
                        <small class="form-help">Kosongkan untuk menggunakan target yang sudah ditetapkan.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="achievement_percentage">Persentase Capaian</label>
                    <div class="achievement-display">
                        <div class="achievement-value" id="achievementValue">0%</div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="achievementBar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_source">Sumber Data</label>
                    <input type="text" name="data_source" id="data_source" class="form-input" placeholder="Contoh: Laporan Bulanan, Sistem Informasi...">
                    <small class="form-help">Sumber dari mana data diperoleh.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Catatan / Keterangan</label>
                    <textarea name="notes" id="notes" class="form-textarea" rows="3" placeholder="Jelaskan jika ada hal-hal yang perlu diketahui..."></textarea>
                </div>
            </div>

            <div class="panel-footer">
                <button type="button" class="btn btn-secondary prev-step" data-prev="1">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="3">
                    <span>Lanjut</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 3: Upload Evidence -->
        <div class="form-panel" data-panel="3">
            <div class="panel-header">
                <h3 class="panel-title">Unggah Bukti Pendukung</h3>
                <p class="panel-description">Lampirkan dokumen sebagai bukti data kinerja.</p>
            </div>

            <div class="panel-body">
                <div class="upload-zone" id="uploadZone">
                    <input type="file" name="evidence_files[]" id="evidence_files" class="upload-input" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        <strong>Klik untuk unggah</strong> atau seret file ke sini
                    </div>
                    <div class="upload-hint">
                        PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Maks. 10MB per file)
                    </div>
                </div>

                <div class="file-list" id="fileList"></div>

                <div class="info-banner warning" style="margin-top: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Penting:</strong> Pastikan dokumen yang diunggah valid dan dapat dibaca.
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <button type="button" class="btn btn-secondary prev-step" data-prev="2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </button>
                <button type="button" class="btn btn-primary next-step" data-next="4">
                    <span>Lanjut</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 4: Confirmation -->
        <div class="form-panel" data-panel="4">
            <div class="panel-header">
                <h3 class="panel-title">Konfirmasi Data</h3>
                <p class="panel-description">Periksa kembali data sebelum disubmit.</p>
            </div>

            <div class="panel-body">
                <div class="summary-list">
                    <div class="summary-item">
                        <span class="summary-label">Indikator</span>
                        <span class="summary-value" id="summaryIndicator">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Instansi</span>
                        <span class="summary-value" id="summaryInstansi">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Periode</span>
                        <span class="summary-value" id="summaryPeriod">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Nilai Aktual</span>
                        <span class="summary-value" id="summaryActual">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Target</span>
                        <span class="summary-value" id="summaryTarget">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Persentase Capaian</span>
                        <span class="summary-value" id="summaryAchievement">-</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">File Bukti</span>
                        <span class="summary-value" id="summaryFiles">-</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="flex items-center gap-2" style="cursor: pointer;">
                        <input type="checkbox" name="confirm" id="confirm" required>
                        <span>Saya menyatakan bahwa data yang diisi adalah benar dan dapat dipertanggungjawabkan.</span>
                    </label>
                </div>
            </div>

            <div class="panel-footer">
                <button type="button" class="btn btn-secondary prev-step" data-prev="3">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // Multi-step form functionality
    document.addEventListener('DOMContentLoaded', function() {
        initMultiStepForm();
        initFileUpload();
        initAchievementCalculator();
    });

    function initMultiStepForm() {
        const steps = document.querySelectorAll('.form-step');
        const panels = document.querySelectorAll('.form-panel');
        const nextBtns = document.querySelectorAll('.next-step');
        const prevBtns = document.querySelectorAll('.prev-step');

        nextBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const nextPanel = this.dataset.next;
                goToPanel(nextPanel);
            });
        });

        prevBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const prevPanel = this.dataset.prev;
                goToPanel(prevPanel);
            });
        });

        function goToPanel(panelNum) {
            // Update steps
            steps.forEach(step => {
                const stepNum = step.dataset.step;
                step.classList.remove('active', 'completed');
                if (parseInt(stepNum) < parseInt(panelNum)) {
                    step.classList.add('completed');
                } else if (parseInt(stepNum) === parseInt(panelNum)) {
                    step.classList.add('active');
                }
            });

            // Update panels
            panels.forEach(panel => {
                panel.classList.remove('active');
                if (panel.dataset.panel === panelNum) {
                    panel.classList.add('active');
                }
            });

            // Update summary if going to confirmation
            if (panelNum === '4') {
                updateSummary();
            }
        }
    }

    function initFileUpload() {
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('evidence_files');
        const fileList = document.getElementById('fileList');
        let files = [];

        uploadZone.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        function handleFiles(newFiles) {
            files = Array.from(newFiles);
            fileList.textContent = '';

            files.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';

                const iconDiv = document.createElement('div');
                iconDiv.className = 'file-icon';
                const icon = document.createElement('i');
                icon.className = 'fas fa-' + getFileIcon(file.name);
                iconDiv.appendChild(icon);

                const infoDiv = document.createElement('div');
                infoDiv.className = 'file-info';

                const nameDiv = document.createElement('div');
                nameDiv.className = 'file-name';
                nameDiv.textContent = file.name;

                const sizeDiv = document.createElement('div');
                sizeDiv.className = 'file-size';
                sizeDiv.textContent = formatFileSize(file.size);

                infoDiv.appendChild(nameDiv);
                infoDiv.appendChild(sizeDiv);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'file-remove';
                removeBtn.dataset.index = index;
                const removeIcon = document.createElement('i');
                removeIcon.className = 'fas fa-times';
                removeBtn.appendChild(removeIcon);

                fileItem.appendChild(iconDiv);
                fileItem.appendChild(infoDiv);
                fileItem.appendChild(removeBtn);
                fileList.appendChild(fileItem);
            });

            const summaryFiles = document.getElementById('summaryFiles');
            if (summaryFiles) {
                summaryFiles.textContent = files.length + ' file';
            }
        }

        fileList.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.file-remove');
            if (removeBtn) {
                const index = removeBtn.dataset.index;
                files.splice(index, 1);
                removeBtn.closest('.file-item').remove();
                const summaryFiles = document.getElementById('summaryFiles');
                if (summaryFiles) {
                    summaryFiles.textContent = files.length + ' file';
                }
            }
        });

        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            const icons = {
                'pdf': 'file-pdf',
                'doc': 'file-word',
                'docx': 'file-word',
                'xls': 'file-excel',
                'xlsx': 'file-excel',
                'jpg': 'file-image',
                'jpeg': 'file-image',
                'png': 'file-image'
            };
            return icons[ext] || 'file';
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
    }

    function initAchievementCalculator() {
        const indicatorSelect = document.getElementById('indicator_id');
        const actualInput = document.getElementById('actual_value');
        const targetInput = document.getElementById('target_value_override');
        const achievementValue = document.getElementById('achievementValue');
        const achievementBar = document.getElementById('achievementBar');
        const targetDisplay = document.getElementById('targetValue');

        if (indicatorSelect) {
            indicatorSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const unit = selectedOption.dataset.unit || '';
                const target = parseFloat(selectedOption.dataset.target) || 0;

                const unitDisplay = document.getElementById('unitDisplay');
                if (unitDisplay) {
                    unitDisplay.textContent = unit ? ' ' + unit : '';
                }
                if (targetDisplay) {
                    targetDisplay.textContent = target.toLocaleString('id-ID');
                }

                calculateAchievement();
            });
        }

        actualInput.addEventListener('input', calculateAchievement);
        targetInput.addEventListener('input', calculateAchievement);

        function calculateAchievement() {
            const actual = parseFloat(actualInput.value) || 0;
            const selectedOption = indicatorSelect.options[indicatorSelect.selectedIndex];
            let target = parseFloat(targetInput.value) || parseFloat(selectedOption?.dataset.target) || 0;

            if (target > 0) {
                const percentage = Math.round((actual / target) * 100);
                achievementValue.textContent = percentage + '%';
                achievementBar.style.width = Math.min(percentage, 100) + '%';

                if (percentage >= 100) {
                    achievementBar.style.background = 'var(--success)';
                } else if (percentage >= 80) {
                    achievementBar.style.background = 'var(--primary-500)';
                } else if (percentage >= 60) {
                    achievementBar.style.background = 'var(--warning)';
                } else {
                    achievementBar.style.background = 'var(--danger)';
                }
            }
        }
    }

    function updateSummary() {
        const indicatorSelect = document.getElementById('indicator_id');
        const instansiSelect = document.getElementById('instansi_id');
        const periodInput = document.getElementById('period');
        const actualInput = document.getElementById('actual_value');
        const targetInput = document.getElementById('target_value_override');

        const summaryIndicator = document.getElementById('summaryIndicator');
        const summaryInstansi = document.getElementById('summaryInstansi');
        const summaryPeriod = document.getElementById('summaryPeriod');
        const summaryActual = document.getElementById('summaryActual');
        const summaryTarget = document.getElementById('summaryTarget');
        const summaryAchievement = document.getElementById('summaryAchievement');

        if (summaryIndicator && indicatorSelect) {
            summaryIndicator.textContent = indicatorSelect.options[indicatorSelect.selectedIndex]?.text || '-';
        }
        if (summaryInstansi && instansiSelect) {
            summaryInstansi.textContent = instansiSelect.options[instansiSelect.selectedIndex]?.text || '-';
        }
        if (summaryPeriod) {
            summaryPeriod.textContent = periodInput.value || '-';
        }

        const actual = parseFloat(actualInput.value) || 0;
        const selectedOption = indicatorSelect.options[indicatorSelect.selectedIndex];
        let target = parseFloat(targetInput.value) || parseFloat(selectedOption?.dataset.target) || 0;
        const unit = selectedOption?.dataset.unit || '';

        if (summaryActual) {
            summaryActual.textContent = actual.toLocaleString('id-ID') + (unit ? ' ' + unit : '');
        }
        if (summaryTarget) {
            summaryTarget.textContent = target.toLocaleString('id-ID') + (unit ? ' ' + unit : '');
        }

        const percentage = target > 0 ? Math.round((actual / target) * 100) : 0;
        if (summaryAchievement) {
            summaryAchievement.textContent = percentage + '%';
        }
    }
</script>
@endpush

@push('styles')
<style>
/* Multi-step Form Styles */
.form-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-xl) var(--space-lg);
    gap: 0;
}

.form-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
}

.form-step-number {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    background: var(--gray-100);
    color: var(--text-tertiary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all var(--transition-base);
}

.form-step.active .form-step-number {
    background: var(--primary-500);
    color: white;
    box-shadow: 0 0 0 4px var(--primary-100);
}

.form-step.completed .form-step-number {
    background: var(--success);
    color: white;
}

.form-step-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-tertiary);
    transition: color var(--transition-base);
}

.form-step.active .form-step-label {
    color: var(--primary-600);
}

.form-step.completed .form-step-label {
    color: var(--success);
}

.form-step-connector {
    flex: 1;
    height: 2px;
    background: var(--gray-200);
    margin: 0 0.5rem;
    max-width: 60px;
}

/* Form Panels */
.form-content {
    position: relative;
}

.form-panel {
    display: none;
    animation: fadeIn 0.3s ease;
}

.form-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.panel-header {
    padding: var(--space-lg) var(--space-lg) 0;
    text-align: center;
}

.panel-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
}

.panel-description {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin: 0;
}

.panel-body {
    padding: var(--space-xl);
}

.panel-footer {
    display: flex;
    justify-content: space-between;
    gap: var(--space-md);
    padding: var(--space-lg);
    border-top: 1px solid var(--border-light);
}

/* Info Banner */
.info-banner {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
}

.info-banner.info {
    background: var(--info-light);
    color: #0e7490;
}

.info-banner.warning {
    background: var(--warning-light);
    color: #92400e;
}

/* Upload Zone */
.upload-zone {
    border: 2px dashed var(--border-medium);
    border-radius: var(--radius-lg);
    padding: var(--space-2xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.upload-zone:hover,
.upload-zone.dragover {
    border-color: var(--primary-400);
    background: var(--primary-50);
}

.upload-icon {
    font-size: 2.5rem;
    color: var(--text-tertiary);
    margin-bottom: var(--space-md);
}

.upload-text {
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.upload-hint {
    color: var(--text-tertiary);
    font-size: 0.8125rem;
}

.upload-input {
    display: none;
}

/* File List */
.file-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin-top: var(--space-lg);
}

.file-item {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
}

.file-icon {
    width: 36px;
    height: 36px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-tertiary);
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-weight: 500;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-size {
    font-size: 0.75rem;
    color: var(--text-tertiary);
}

.file-remove {
    width: 28px;
    height: 28px;
    border: none;
    background: var(--danger-light);
    color: var(--danger);
    border-radius: var(--radius-sm);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

.file-remove:hover {
    background: var(--danger);
    color: white;
}

/* Achievement Display */
.achievement-display {
    text-align: center;
}

.achievement-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-sm);
}

/* Summary List */
.summary-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-md) 0;
    border-bottom: 1px solid var(--border-light);
}

.summary-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.summary-value {
    font-weight: 500;
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 640px) {
    .form-steps {
        flex-wrap: wrap;
    }

    .form-step-connector {
        display: none;
    }

    .panel-footer {
        flex-direction: column-reverse;
    }

    .panel-footer .btn {
        width: 100%;
    }
}
</style>
@endpush
