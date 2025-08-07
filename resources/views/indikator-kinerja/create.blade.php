@extends('layouts.app')

@section('title', 'Tambah Indikator Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Indikator Kinerja</h1>
        <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Indikator Kinerja</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('indikator-kinerja.store') }}" method="POST" id="indikatorForm">
                        @csrf

                        <!-- Activity Selection -->
                        <div class="form-group">
                            <label for="kegiatan_id" class="form-label">Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-control @error('kegiatan_id') is-invalid @enderror"
                                    id="kegiatan_id" name="kegiatan_id" required>
                                <option value="">Pilih Kegiatan</option>
                                @foreach($kegiatans as $kegiatan)
                                    <option value="{{ $kegiatan->id }}"
                                            {{ (old('kegiatan_id', $selectedKegiatan?->id) == $kegiatan->id) ? 'selected' : '' }}>
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
                                   id="nama_indikator" name="nama_indikator" value="{{ old('nama_indikator') }}"
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
                                      placeholder="Deskripsi detail indikator kinerja (opsional)">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Target and Unit -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target" class="form-label">Target <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0"
                                           class="form-control @error('target') is-invalid @enderror"
                                           id="target" name="target" value="{{ old('target') }}"
                                           placeholder="0.00" required>
                                    @error('target')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('satuan') is-invalid @enderror"
                                           id="satuan" name="satuan" value="{{ old('satuan') }}"
                                           placeholder="Contoh: %, unit, orang, dll" required>
                                    @error('satuan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Input -->
                        <div class="form-group">
                            <label for="input" class="form-label">Input <small class="text-muted">(Opsional - untuk perhitungan efisiensi)</small></label>
                            <input type="number" step="0.01" 
                                   class="form-control @error('input') is-invalid @enderror" 
                                   id="input" name="input" 
                                   value="{{ old('input') }}" 
                                   placeholder="Masukkan nilai input (jika menggunakan metode input vs realisasi)">
                            <small class="form-text text-muted">
                                Isi field ini jika indikator mengukur efisiensi atau rasio antara input dan realisasi.
                            </small>
                            @error('input')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Indicator Type -->
                        <div class="form-group">
                            <label for="jenis" class="form-label">Jenis Indikator <span class="text-danger">*</span></label>
                            <select class="form-control @error('jenis') is-invalid @enderror"
                                    id="jenis" name="jenis" required>
                                <option value="">Pilih Jenis Indikator</option>
                                <option value="input" {{ old('jenis') == 'input' ? 'selected' : '' }}>Input</option>
                                <option value="output" {{ old('jenis') == 'output' ? 'selected' : '' }}>Output</option>
                                <option value="outcome" {{ old('jenis') == 'outcome' ? 'selected' : '' }}>Outcome</option>
                                <option value="impact" {{ old('jenis') == 'impact' ? 'selected' : '' }}>Impact</option>
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
                                <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('indikator-kinerja.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Panduan Pengisian</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">Nama Indikator</h6>
                        <p class="small text-muted">Gunakan nama yang jelas dan spesifik untuk mengukur kinerja kegiatan. Contoh: "Persentase Peserta Pelatihan yang Lulus"</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Target</h6>
                        <p class="small text-muted">Tentukan target yang realistis dan terukur. Target ini akan menjadi acuan dalam penilaian kinerja.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">Jenis Indikator</h6>
                        <ul class="small text-muted">
                            <li><strong>Input:</strong> Mengukur sumber daya (anggaran, SDM, waktu)</li>
                            <li><strong>Output:</strong> Mengukur hasil langsung kegiatan</li>
                            <li><strong>Outcome:</strong> Mengukur manfaat jangka menengah</li>
                            <li><strong>Impact:</strong> Mengukur dampak jangka panjang</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Examples Panel -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Contoh Indikator</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-success">Input</h6>
                        <p class="small text-muted">"Jumlah Anggaran Terserap" (Target: 100, Satuan: %)</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-success">Output</h6>
                        <p class="small text-muted">"Jumlah Peserta Pelatihan" (Target: 50, Satuan: orang)</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-success">Outcome</h6>
                        <p class="small text-muted">"Tingkat Kepuasan Peserta" (Target: 85, Satuan: %)</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-success">Impact</h6>
                        <p class="small text-muted">"Peningkatan Produktivitas" (Target: 20, Satuan: %)</p>
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
                
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-calculator"></i> Contoh Perhitungan:</h6>
                    <div class="mb-3">
                        <strong>Metode 1 - Berdasarkan Target:</strong><br>
                        <small>Target: 100 unit, Realisasi: 85 unit</small><br>
                        <small>Perhitungan: (85 / 100) × 100% = 85%</small>
                        <div class="progress mt-1" style="height: 15px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                                85%
                            </div>
                        </div>
                        <small class="text-muted">Kategori: Baik (75% - 99%)</small>
                    </div>
                    
                    <div class="mb-0">
                        <strong>Metode 2 - Berdasarkan Input:</strong><br>
                        <small>Input: 120 unit, Realisasi: 85 unit</small><br>
                        <small>Perhitungan: (85 / 120) × 100% = 70.8%</small>
                        <div class="progress mt-1" style="height: 15px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 70.8%" aria-valuenow="70.8" aria-valuemin="0" aria-valuemax="100">
                                70.8%
                            </div>
                        </div>
                        <small class="text-muted">Kategori: Cukup (50% - 74%)</small>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Tips Penting:</h6>
                    <ul class="mb-0">
                        <li>Pastikan target yang ditetapkan realistis dan terukur</li>
                        <li>Satuan harus konsisten antara target dan realisasi</li>
                        <li>Dokumentasikan metode pengukuran untuk konsistensi</li>
                        <li>Review dan evaluasi target secara berkala</li>
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

        if (satuanField.val() === '') {
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
});
</script>
@endpush
