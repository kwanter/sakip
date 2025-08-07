@extends('layouts.app')

@section('title', 'Tambah Laporan Kinerja')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Laporan Kinerja</h1>
        <a href="{{ route('laporan-kinerja.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Laporan Kinerja</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('laporan-kinerja.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="indikator_kinerja_id">Indikator Kinerja <span class="text-danger">*</span></label>
                                    <select class="form-control @error('indikator_kinerja_id') is-invalid @enderror" 
                                            id="indikator_kinerja_id" name="indikator_kinerja_id" required>
                                        <option value="">Pilih Indikator Kinerja</option>
                                        @foreach($indikatorKinerjas as $indikator)
                                            <option value="{{ $indikator->id }}" 
                                                    {{ (old('indikator_kinerja_id') == $indikator->id || ($selectedIndikator && $selectedIndikator->id == $indikator->id)) ? 'selected' : '' }}
                                                    data-target="{{ $indikator->target }}"
                                                    data-satuan="{{ $indikator->satuan }}">
                                                {{ $indikator->nama_indikator }} 
                                                ({{ $indikator->kegiatan->nama_kegiatan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('indikator_kinerja_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted" id="indikator-info"></small>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tahun">Tahun <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tahun') is-invalid @enderror" 
                                            id="tahun" name="tahun" required>
                                        <option value="">Pilih Tahun</option>
                                        @for($year = date('Y') + 5; $year >= 2020; $year--)
                                            <option value="{{ $year }}" {{ old('tahun') == $year ? 'selected' : '' }}>
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
                                    <option value="januari" {{ old('periode') == 'januari' ? 'selected' : '' }}>Januari</option>
                                    <option value="februari" {{ old('periode') == 'februari' ? 'selected' : '' }}>Februari</option>
                                    <option value="maret" {{ old('periode') == 'maret' ? 'selected' : '' }}>Maret</option>
                                    <option value="april" {{ old('periode') == 'april' ? 'selected' : '' }}>April</option>
                                    <option value="mei" {{ old('periode') == 'mei' ? 'selected' : '' }}>Mei</option>
                                    <option value="juni" {{ old('periode') == 'juni' ? 'selected' : '' }}>Juni</option>
                                    <option value="juli" {{ old('periode') == 'juli' ? 'selected' : '' }}>Juli</option>
                                    <option value="agustus" {{ old('periode') == 'agustus' ? 'selected' : '' }}>Agustus</option>
                                    <option value="september" {{ old('periode') == 'september' ? 'selected' : '' }}>September</option>
                                    <option value="oktober" {{ old('periode') == 'oktober' ? 'selected' : '' }}>Oktober</option>
                                    <option value="november" {{ old('periode') == 'november' ? 'selected' : '' }}>November</option>
                                    <option value="desember" {{ old('periode') == 'desember' ? 'selected' : '' }}>Desember</option>
                                </optgroup>
                                <optgroup label="Triwulanan">
                                    <option value="triwulan1" {{ old('periode') == 'triwulan1' ? 'selected' : '' }}>Triwulan I</option>
                                    <option value="triwulan2" {{ old('periode') == 'triwulan2' ? 'selected' : '' }}>Triwulan II</option>
                                    <option value="triwulan3" {{ old('periode') == 'triwulan3' ? 'selected' : '' }}>Triwulan III</option>
                                    <option value="triwulan4" {{ old('periode') == 'triwulan4' ? 'selected' : '' }}>Triwulan IV</option>
                                </optgroup>
                                <optgroup label="Tahunan">
                                    <option value="tahunan" {{ old('periode') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
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
                                        <input type="number" step="0.01" min="0" 
                                               class="form-control @error('nilai_realisasi') is-invalid @enderror" 
                                               id="nilai_realisasi" name="nilai_realisasi" 
                                               value="{{ old('nilai_realisasi') }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="satuan-display">-</span>
                                        </div>
                                    </div>
                                    @error('nilai_realisasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="persentase_capaian">Persentase Capaian (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control @error('persentase_capaian') is-invalid @enderror" 
                                           id="persentase_capaian" name="persentase_capaian" 
                                           value="{{ old('persentase_capaian') }}" readonly>
                                    @error('persentase_capaian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Akan dihitung otomatis berdasarkan target</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status_verifikasi">Status Verifikasi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status_verifikasi') is-invalid @enderror" 
                                            id="status_verifikasi" name="status_verifikasi" required>
                                        <option value="draft" {{ old('status_verifikasi') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="submitted" {{ old('status_verifikasi') == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                                        <option value="verified" {{ old('status_verifikasi') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                                        <option value="rejected" {{ old('status_verifikasi') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                    @error('status_verifikasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kendala">Kendala</label>
                                    <textarea class="form-control @error('kendala') is-invalid @enderror" 
                                              id="kendala" name="kendala" rows="4" 
                                              placeholder="Jelaskan kendala yang dihadapi dalam pencapaian target...">{{ old('kendala') }}</textarea>
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
                                              placeholder="Jelaskan rencana tindak lanjut untuk perbaikan...">{{ old('tindak_lanjut') }}</textarea>
                                    @error('tindak_lanjut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="file_pendukung">File Pendukung</label>
                                    <input type="file" class="form-control-file @error('file_pendukung') is-invalid @enderror" 
                                           id="file_pendukung" name="file_pendukung" 
                                           accept=".pdf,.doc,.docx,.xls,.xlsx">
                                    @error('file_pendukung')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format yang diizinkan: PDF, DOC, DOCX, XLS, XLSX. Maksimal 5MB.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Laporan
                            </button>
                            <a href="{{ route('laporan-kinerja.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update info indikator dan satuan ketika indikator dipilih
    $('#indikator_kinerja_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var target = selectedOption.data('target');
        var satuan = selectedOption.data('satuan');
        
        if (target && satuan) {
            $('#indikator-info').text('Target: ' + target + ' ' + satuan);
            $('#satuan-display').text(satuan);
        } else {
            $('#indikator-info').text('');
            $('#satuan-display').text('-');
        }
        
        // Reset dan hitung ulang persentase
        calculatePercentage();
    });
    
    // Hitung persentase capaian otomatis
    $('#nilai_realisasi').on('input', function() {
        calculatePercentage();
    });
    
    function calculatePercentage() {
        var selectedOption = $('#indikator_kinerja_id').find('option:selected');
        var target = parseFloat(selectedOption.data('target'));
        var realisasi = parseFloat($('#nilai_realisasi').val());
        
        if (target > 0 && realisasi >= 0) {
            var percentage = (realisasi / target) * 100;
            $('#persentase_capaian').val(percentage.toFixed(2));
        } else {
            $('#persentase_capaian').val('');
        }
    }
    
    // Trigger pada load jika ada selected indikator
    $('#indikator_kinerja_id').trigger('change');
});
</script>
@endpush