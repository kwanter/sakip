@extends('layouts.app')

@section('title', 'Edit Laporan Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Laporan Kinerja</h1>
        <a href="{{ route('laporan-kinerja.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Laporan Kinerja</h6>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('laporan-kinerja.update', $laporanKinerja) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="indikator_kinerja_id">Indikator Kinerja <span class="text-danger">*</span></label>
                                    <select class="form-control @error('indikator_kinerja_id') is-invalid @enderror" 
                                            id="indikator_kinerja_id" name="indikator_kinerja_id" required>
                                        <option value="">Pilih Indikator Kinerja</option>
                                        @foreach($indikatorKinerjas as $indikator)
                                            <option value="{{ $indikator->id }}" 
                                                    data-target="{{ $indikator->target }}" 
                                                    data-satuan="{{ $indikator->satuan }}"
                                                    {{ old('indikator_kinerja_id', $laporanKinerja->indikator_kinerja_id) == $indikator->id ? 'selected' : '' }}>
                                                {{ $indikator->nama_indikator }} ({{ $indikator->kegiatan->nama_kegiatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('indikator_kinerja_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tahun">Tahun <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" required>
                                        @for($year = date('Y') - 2; $year <= date('Y') + 2; $year++)
                                            <option value="{{ $year }}" {{ old('tahun', $laporanKinerja->tahun) == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                            <label for="periode">Periode <span class="text-danger">*</span></label>
                            <select class="form-control @error('periode') is-invalid @enderror" id="periode" name="periode" required>
                                <option value="">Pilih Periode</option>
                                <optgroup label="Bulanan">
                                    <option value="januari" {{ old('periode', $laporanKinerja->periode) == 'januari' ? 'selected' : '' }}>Januari</option>
                                    <option value="februari" {{ old('periode', $laporanKinerja->periode) == 'februari' ? 'selected' : '' }}>Februari</option>
                                    <option value="maret" {{ old('periode', $laporanKinerja->periode) == 'maret' ? 'selected' : '' }}>Maret</option>
                                    <option value="april" {{ old('periode', $laporanKinerja->periode) == 'april' ? 'selected' : '' }}>April</option>
                                    <option value="mei" {{ old('periode', $laporanKinerja->periode) == 'mei' ? 'selected' : '' }}>Mei</option>
                                    <option value="juni" {{ old('periode', $laporanKinerja->periode) == 'juni' ? 'selected' : '' }}>Juni</option>
                                    <option value="juli" {{ old('periode', $laporanKinerja->periode) == 'juli' ? 'selected' : '' }}>Juli</option>
                                    <option value="agustus" {{ old('periode', $laporanKinerja->periode) == 'agustus' ? 'selected' : '' }}>Agustus</option>
                                    <option value="september" {{ old('periode', $laporanKinerja->periode) == 'september' ? 'selected' : '' }}>September</option>
                                    <option value="oktober" {{ old('periode', $laporanKinerja->periode) == 'oktober' ? 'selected' : '' }}>Oktober</option>
                                    <option value="november" {{ old('periode', $laporanKinerja->periode) == 'november' ? 'selected' : '' }}>November</option>
                                    <option value="desember" {{ old('periode', $laporanKinerja->periode) == 'desember' ? 'selected' : '' }}>Desember</option>
                                </optgroup>
                                <optgroup label="Triwulanan">
                                    <option value="triwulan1" {{ old('periode', $laporanKinerja->periode) == 'triwulan1' ? 'selected' : '' }}>Triwulan I</option>
                                    <option value="triwulan2" {{ old('periode', $laporanKinerja->periode) == 'triwulan2' ? 'selected' : '' }}>Triwulan II</option>
                                    <option value="triwulan3" {{ old('periode', $laporanKinerja->periode) == 'triwulan3' ? 'selected' : '' }}>Triwulan III</option>
                                    <option value="triwulan4" {{ old('periode', $laporanKinerja->periode) == 'triwulan4' ? 'selected' : '' }}>Triwulan IV</option>
                                </optgroup>
                                <optgroup label="Tahunan">
                                    <option value="tahunan" {{ old('periode', $laporanKinerja->periode) == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                                </optgroup>
                            </select>
                            @error('periode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_realisasi">Nilai Realisasi <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" 
                                               class="form-control @error('nilai_realisasi') is-invalid @enderror" 
                                               id="nilai_realisasi" name="nilai_realisasi" 
                                               value="{{ old('nilai_realisasi', $laporanKinerja->nilai_realisasi) }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="satuan-display">{{ $laporanKinerja->indikatorKinerja->satuan }}</span>
                                        </div>
                                    </div>
                                    @error('nilai_realisasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="target_display">Target</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="target_display" 
                                               value="{{ number_format($laporanKinerja->indikatorKinerja->target, 2) }}" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="target-satuan">{{ $laporanKinerja->indikatorKinerja->satuan }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="persentase_capaian">Persentase Capaian (%)</label>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="persentase_capaian" name="persentase_capaian" 
                                           value="{{ old('persentase_capaian', $laporanKinerja->persentase_capaian) }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status_verifikasi">Status Verifikasi</label>
                            <select class="form-control @error('status_verifikasi') is-invalid @enderror" 
                                    id="status_verifikasi" name="status_verifikasi">
                                <option value="draft" {{ old('status_verifikasi', $laporanKinerja->status_verifikasi) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ old('status_verifikasi', $laporanKinerja->status_verifikasi) == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                                <option value="verified" {{ old('status_verifikasi', $laporanKinerja->status_verifikasi) == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                <option value="rejected" {{ old('status_verifikasi', $laporanKinerja->status_verifikasi) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                            @error('status_verifikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kendala">Kendala</label>
                                    <textarea class="form-control @error('kendala') is-invalid @enderror" 
                                              id="kendala" name="kendala" rows="4" 
                                              placeholder="Jelaskan kendala yang dihadapi (opsional)">{{ old('kendala', $laporanKinerja->kendala) }}</textarea>
                                    @error('kendala')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tindak_lanjut">Tindak Lanjut</label>
                                    <textarea class="form-control @error('tindak_lanjut') is-invalid @enderror" 
                                              id="tindak_lanjut" name="tindak_lanjut" rows="4" 
                                              placeholder="Jelaskan rencana tindak lanjut (opsional)">{{ old('tindak_lanjut', $laporanKinerja->tindak_lanjut) }}</textarea>
                                    @error('tindak_lanjut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="file_pendukung">File Pendukung</label>
                            @if($laporanKinerja->file_pendukung)
                                <div class="mb-2">
                                    <small class="text-muted">File saat ini: 
                                        <a href="{{ Storage::url($laporanKinerja->file_pendukung) }}" target="_blank">
                                            {{ basename($laporanKinerja->file_pendukung) }}
                                        </a>
                                    </small>
                                </div>
                            @endif
                            <input type="file" class="form-control-file @error('file_pendukung') is-invalid @enderror" 
                                   id="file_pendukung" name="file_pendukung" 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Format yang diizinkan: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG. Maksimal 5MB.
                                {{ $laporanKinerja->file_pendukung ? 'Biarkan kosong jika tidak ingin mengubah file.' : '' }}
                            </small>
                            @error('file_pendukung')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="catatan_verifikasi">Catatan Verifikasi</label>
                            <textarea class="form-control @error('catatan_verifikasi') is-invalid @enderror" 
                                      id="catatan_verifikasi" name="catatan_verifikasi" rows="3" 
                                      placeholder="Catatan dari verifikator (opsional)">{{ old('catatan_verifikasi', $laporanKinerja->catatan_verifikasi) }}</textarea>
                            @error('catatan_verifikasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Laporan
                            </button>
                            <a href="{{ route('laporan-kinerja.show', $laporanKinerja) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Current Indikator Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Indikator Kinerja Saat Ini</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Nama Indikator:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->nama_indikator }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Target:</label>
                        <p class="text-gray-800">{{ number_format($laporanKinerja->indikatorKinerja->target, 2) }} {{ $laporanKinerja->indikatorKinerja->satuan }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Kegiatan:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->nama_kegiatan }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="font-weight-bold text-gray-800">Program:</label>
                        <p class="text-gray-800">{{ $laporanKinerja->indikatorKinerja->kegiatan->program->nama_program }}</p>
                    </div>
                </div>
            </div>

            <!-- Help -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bantuan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Nilai Realisasi</h6>
                        <p class="small text-gray-600">Masukkan nilai pencapaian aktual dari indikator kinerja untuk periode yang dipilih.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Persentase Capaian</h6>
                        <p class="small text-gray-600">Akan dihitung otomatis berdasarkan nilai realisasi dibagi target dikali 100%.</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Status Verifikasi</h6>
                        <ul class="small text-gray-600">
                            <li><strong>Draft:</strong> Laporan masih dalam tahap penyusunan</li>
                            <li><strong>Diajukan:</strong> Laporan telah diajukan untuk verifikasi</li>
                            <li><strong>Terverifikasi:</strong> Laporan telah diverifikasi dan disetujui</li>
                            <li><strong>Ditolak:</strong> Laporan ditolak dan perlu diperbaiki</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto calculate percentage when nilai_realisasi changes
document.getElementById('nilai_realisasi').addEventListener('input', function() {
    const nilaiRealisasi = parseFloat(this.value) || 0;
    const indikatorSelect = document.getElementById('indikator_kinerja_id');
    const selectedOption = indikatorSelect.options[indikatorSelect.selectedIndex];
    const target = parseFloat(selectedOption.getAttribute('data-target')) || 0;
    
    if (target > 0) {
        const persentase = (nilaiRealisasi / target) * 100;
        document.getElementById('persentase_capaian').value = persentase.toFixed(2);
    } else {
        document.getElementById('persentase_capaian').value = '';
    }
});

// Update satuan and target when indikator changes
document.getElementById('indikator_kinerja_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const satuan = selectedOption.getAttribute('data-satuan') || '';
    const target = parseFloat(selectedOption.getAttribute('data-target')) || 0;
    
    document.getElementById('satuan-display').textContent = satuan;
    document.getElementById('target-satuan').textContent = satuan;
    document.getElementById('target_display').value = target.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Recalculate percentage
    const nilaiRealisasi = parseFloat(document.getElementById('nilai_realisasi').value) || 0;
    if (target > 0 && nilaiRealisasi > 0) {
        const persentase = (nilaiRealisasi / target) * 100;
        document.getElementById('persentase_capaian').value = persentase.toFixed(2);
    } else {
        document.getElementById('persentase_capaian').value = '';
    }
});

// Initialize calculation on page load
document.addEventListener('DOMContentLoaded', function() {
    const nilaiRealisasiInput = document.getElementById('nilai_realisasi');
    if (nilaiRealisasiInput.value) {
        nilaiRealisasiInput.dispatchEvent(new Event('input'));
    }
});
</script>
@endsection