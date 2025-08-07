@extends('layouts.app')

@section('title', 'Edit Indikator Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Indikator Kinerja</h1>
        <div>
            <a href="{{ route('indikator-kinerja.show', $indikatorKinerja->id) }}" class="btn btn-info btn-sm shadow-sm mr-2">
                <i class="fas fa-eye fa-sm text-white-50"></i> Lihat Detail
            </a>
            <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Indikator Kinerja</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('indikator-kinerja.update', $indikatorKinerja->id) }}" method="POST" id="indikatorForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Activity Selection -->
                        <div class="form-group">
                            <label for="kegiatan_id" class="form-label">Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control @error('kegiatan_id') is-invalid @enderror" 
                                    id="kegiatan_id" name="kegiatan_id" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatans as $kegiatan)
                                    <option value="{{ $kegiatan->id }}" 
                                            {{ (old('kegiatan_id', $indikatorKinerja->kegiatan_id) == $kegiatan->id) ? 'selected' : '' }}>
                                        {{ $kegiatan->nama_kegiatan }} - {{ $kegiatan->program->nama_program }} ({{ $kegiatan->program->instansi->nama_instansi }})
                                    </option>
                                @endforeach
                            </select>
                            @error('kegiatan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Indicator Name -->
                        <div class="form-group">
                            <label for="nama_indikator" class="form-label">Nama Indikator <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_indikator') is-invalid @enderror" 
                                   id="nama_indikator" name="nama_indikator" 
                                   value="{{ old('nama_indikator', $indikatorKinerja->nama_indikator) }}" 
                                   placeholder="Masukkan nama indikator kinerja" required>
                            @error('nama_indikator')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Deskripsi detail indikator kinerja (opsional)">{{ old('deskripsi', $indikatorKinerja->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Target -->
                        <div class="form-group">
                            <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" 
                                   class="form-control @error('target') is-invalid @enderror" 
                                   id="target" name="target" 
                                   value="{{ old('target', $indikatorKinerja->target) }}" 
                                   placeholder="0.00" required>
                            @error('target')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input -->
                        <div class="form-group">
                            <label for="input" class="form-label">Input <small class="text-muted">(Opsional - untuk perhitungan efisiensi)</small></label>
                            <input type="number" step="0.01" 
                                   class="form-control @error('input') is-invalid @enderror" 
                                   id="input" name="input" 
                                   value="{{ old('input', $indikatorKinerja->input) }}" 
                                   placeholder="Masukkan nilai input (jika menggunakan metode input vs realisasi)">
                            <small class="form-text text-muted">
                                Isi field ini jika indikator mengukur efisiensi atau rasio antara input dan realisasi.
                            </small>
                            @error('input')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit -->
                        <div class="form-group">
                            <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('satuan') is-invalid @enderror" 
                                   id="satuan" name="satuan" 
                                   value="{{ old('satuan', $indikatorKinerja->satuan) }}" 
                                   placeholder="Contoh: %, unit, orang, dll" required>
                            @error('satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Indicator Type -->
                        <div class="form-group">
                            <label for="jenis" class="form-label">Jenis Indikator <span class="text-danger">*</span></label>
                            <select class="form-control @error('jenis') is-invalid @enderror"
                                    id="jenis" name="jenis" required>
                                <option value="">Pilih Jenis Indikator</option>
                                <option value="input" {{ old('jenis', $indikatorKinerja->jenis) == 'input' ? 'selected' : '' }}>Input</option>
                                <option value="output" {{ old('jenis', $indikatorKinerja->jenis) == 'output' ? 'selected' : '' }}>Output</option>
                                <option value="outcome" {{ old('jenis', $indikatorKinerja->jenis) == 'outcome' ? 'selected' : '' }}>Outcome</option>
                                <option value="impact" {{ old('jenis', $indikatorKinerja->jenis) == 'impact' ? 'selected' : '' }}>Impact</option>
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Input:</strong> Sumber daya yang digunakan | 
                                <strong>Output:</strong> Hasil langsung | 
                                <strong>Outcome:</strong> Manfaat jangka menengah | 
                                <strong>Impact:</strong> Dampak jangka panjang
                            </small>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="aktif" {{ old('status', $indikatorKinerja->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status', $indikatorKinerja->status) == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('indikator-kinerja.show', $indikatorKinerja->id) }}" class="btn btn-info ml-2">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                            <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <!-- Current Data -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Data Saat Ini</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Kegiatan</h6>
                        <p class="small text-muted">{{ $indikatorKinerja->kegiatan->nama_kegiatan }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Program</h6>
                        <p class="small text-muted">{{ $indikatorKinerja->kegiatan->program->nama_program }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Instansi</h6>
                        <p class="small text-muted">{{ $indikatorKinerja->kegiatan->program->instansi->nama_instansi }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Dibuat</h6>
                        <p class="small text-muted">{{ $indikatorKinerja->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Terakhir Diupdate</h6>
                        <p class="small text-muted">{{ $indikatorKinerja->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Performance Reports -->
            @if($indikatorKinerja->laporanKinerjas->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Peringatan</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong> Indikator ini memiliki {{ $indikatorKinerja->laporanKinerjas->count() }} laporan kinerja. 
                        Perubahan pada target atau satuan dapat mempengaruhi perhitungan kinerja yang sudah ada.
                    </div>
                </div>
            </div>
            @endif

            <!-- Help Panel -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Tips Edit</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">Nama Indikator</h6>
                        <p class="small text-muted">Pastikan nama tetap jelas dan konsisten dengan laporan yang sudah ada.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-success">Target</h6>
                        <p class="small text-muted">Pertimbangkan dampak perubahan target terhadap pencapaian kinerja yang sudah dilaporkan.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-success">Satuan</h6>
                        <p class="small text-muted">Hindari mengubah satuan jika sudah ada laporan kinerja untuk menjaga konsistensi data.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tata Cara Perhitungan Indikator Kinerja -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Tata Cara Perhitungan Indikator Kinerja</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="font-weight-bold text-gray-800">Formula Perhitungan:</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="mb-2">
                            <strong>Metode 1 - Berdasarkan Target:</strong><br>
                            <code class="text-dark">
                                Persentase Capaian = (Realisasi / Target) × 100%
                            </code>
                        </div>
                        <div>
                            <strong>Metode 2 - Berdasarkan Input:</strong><br>
                            <code class="text-dark">
                                Persentase Capaian = (Realisasi / Input) × 100%
                            </code>
                            <small class="text-muted d-block mt-1">
                                *Gunakan metode ini jika indikator mengukur efisiensi atau rasio antara input dan realisasi
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="font-weight-bold text-gray-800">Contoh dengan Data Saat Ini:</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-2"><strong>Target Saat Ini:</strong> {{ number_format($indikatorKinerja->target, 2) }} {{ $indikatorKinerja->satuan }}</p>
                        @if($indikatorKinerja->input)
                            <p class="mb-2"><strong>Input Saat Ini:</strong> {{ number_format($indikatorKinerja->input, 2) }} {{ $indikatorKinerja->satuan }}</p>
                        @endif
                        @if($indikatorKinerja->laporanKinerjas->count() > 0)
                            @php
                                $laporanTerbaru = $indikatorKinerja->laporanKinerjas->sortByDesc('periode_laporan')->first();
                                $persentaseTarget = $indikatorKinerja->target > 0 ? ($laporanTerbaru->realisasi / $indikatorKinerja->target) * 100 : 0;
                                $persentaseInput = $indikatorKinerja->input > 0 ? ($laporanTerbaru->realisasi / $indikatorKinerja->input) * 100 : 0;
                            @endphp
                            <p class="mb-2"><strong>Realisasi Terbaru:</strong> {{ number_format($laporanTerbaru->realisasi, 2) }} {{ $indikatorKinerja->satuan }}</p>
                            
                            <div class="mb-3">
                                <strong>Metode 1 - Berdasarkan Target:</strong><br>
                                <small>Perhitungan: ({{ number_format($laporanTerbaru->realisasi, 2) }} / {{ number_format($indikatorKinerja->target, 2) }}) × 100% = {{ number_format($persentaseTarget, 2) }}%</small>
                                <div class="progress mt-1" style="height: 15px;">
                                    <div class="progress-bar bg-{{ $persentaseTarget >= 100 ? 'success' : ($persentaseTarget >= 75 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($persentaseTarget, 100) }}%" 
                                         aria-valuenow="{{ $persentaseTarget }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($persentaseTarget, 1) }}%
                                    </div>
                                </div>
                            </div>
                            
                            @if($indikatorKinerja->input)
                            <div class="mb-3">
                                <strong>Metode 2 - Berdasarkan Input:</strong><br>
                                <small>Perhitungan: ({{ number_format($laporanTerbaru->realisasi, 2) }} / {{ number_format($indikatorKinerja->input, 2) }}) × 100% = {{ number_format($persentaseInput, 2) }}%</small>
                                <div class="progress mt-1" style="height: 15px;">
                                    <div class="progress-bar bg-{{ $persentaseInput >= 100 ? 'success' : ($persentaseInput >= 75 ? 'warning' : 'info') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($persentaseInput, 100) }}%" 
                                         aria-valuenow="{{ $persentaseInput }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($persentaseInput, 1) }}%
                                    </div>
                                </div>
                            </div>
                            @endif
                        @else
                            <p class="text-muted">Belum ada data realisasi</p>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="font-weight-bold text-gray-800">Kategori Penilaian:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-2 bg-success text-white rounded mb-2">
                                <strong>Sangat Baik</strong><br>
                                <small>≥ 100%</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-2 bg-warning text-white rounded mb-2">
                                <strong>Baik</strong><br>
                                <small>75% - 99%</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-2 bg-info text-white rounded mb-2">
                                <strong>Cukup</strong><br>
                                <small>50% - 74%</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-2 bg-danger text-white rounded mb-2">
                                <strong>Kurang</strong><br>
                                <small>< 50%</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Perhatian Saat Edit:</h6>
                    <ul class="mb-0">
                        <li>Perubahan target akan mempengaruhi perhitungan persentase capaian</li>
                        <li>Pastikan satuan tetap konsisten dengan laporan yang sudah ada</li>
                        <li>Jika mengubah target, pertimbangkan dampaknya pada evaluasi kinerja</li>
                        @if($indikatorKinerja->laporanKinerjas->count() > 0)
                        <li>Indikator ini memiliki {{ $indikatorKinerja->laporanKinerjas->count() }} laporan kinerja yang akan terpengaruh</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#indikatorForm').submit(function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['kegiatan_id', 'nama_indikator', 'target', 'satuan', 'jenis', 'status'];
        requiredFields.forEach(function(field) {
            const value = $('#' + field).val();
            if (!value || value.trim() === '') {
                isValid = false;
                $('#' + field).addClass('is-invalid');
            } else {
                $('#' + field).removeClass('is-invalid');
            }
        });
        
        // Check target value
        const target = parseFloat($('#target').val());
        if (isNaN(target) || target < 0) {
            isValid = false;
            $('#target').addClass('is-invalid');
            alert('Target harus berupa angka dan tidak boleh negatif');
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi');
        }
    });
    
    // Auto-suggest satuan based on indicator type
    $('#jenis').change(function() {
        const jenis = $(this).val();
        const satuanField = $('#satuan');
        const currentSatuan = satuanField.val();
        
        // Only suggest if field is empty
        if (currentSatuan === '') {
            switch(jenis) {
                case 'input':
                    satuanField.attr('placeholder', 'Contoh: %, Rp, jam, orang');
                    break;
                case 'output':
                    satuanField.attr('placeholder', 'Contoh: unit, dokumen, kegiatan');
                    break;
                case 'outcome':
                    satuanField.attr('placeholder', 'Contoh: %, tingkat, indeks');
                    break;
                case 'impact':
                    satuanField.attr('placeholder', 'Contoh: %, poin, skor');
                    break;
                default:
                    satuanField.attr('placeholder', 'Contoh: %, unit, orang, dll');
            }
        }
    });
    
    // Warn about changes if there are existing reports
    @if($indikatorKinerja->laporanKinerjas->count() > 0)
    let originalTarget = $('#target').val();
    let originalSatuan = $('#satuan').val();
    
    $('#target, #satuan').change(function() {
        const currentTarget = $('#target').val();
        const currentSatuan = $('#satuan').val();
        
        if (currentTarget !== originalTarget || currentSatuan !== originalSatuan) {
            if (!$('#change-warning').length) {
                const warning = `
                    <div id="change-warning" class="alert alert-warning mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian!</strong> Anda mengubah target atau satuan. 
                        Hal ini dapat mempengaruhi perhitungan kinerja yang sudah dilaporkan.
                    </div>
                `;
                $(this).closest('.form-group').after(warning);
            }
        } else {
            $('#change-warning').remove();
        }
    });
    @endif
});
</script>
@endpush