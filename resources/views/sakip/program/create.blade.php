@extends('layouts.app')

@section('title', 'Tambah Program')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks"></i> Tambah Program
        </h1>
        <a href="{{ route('sakip.program.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if($sasaranStrategis->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Peringatan!</strong> Tidak ada data Sasaran Strategis. Silakan tambahkan Sasaran Strategis terlebih dahulu sebelum membuat Program.
        <a href="{{ route('sakip.sasaran-strategis.create') }}" class="btn btn-sm btn-primary ml-2">
            <i class="fas fa-plus"></i> Tambah Sasaran Strategis
        </a>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Tambah Program</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sakip.program.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instansi_id">Instansi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('instansi_id') is-invalid @enderror"
                                            id="instansi_id" name="instansi_id" required {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                                        <option value="">Pilih Instansi</option>
                                        @foreach($instansis as $instansi)
                                            <option value="{{ $instansi->id }}" {{ old('instansi_id') == $instansi->id ? 'selected' : '' }}>
                                                {{ $instansi->nama_instansi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('instansi_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sasaran_strategis_id">Sasaran Strategis <span class="text-danger">*</span></label>
                                    <select class="form-control @error('sasaran_strategis_id') is-invalid @enderror"
                                            id="sasaran_strategis_id" name="sasaran_strategis_id" required {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                                        <option value="">Pilih Instansi terlebih dahulu</option>
                                    </select>
                                    @error('sasaran_strategis_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_program">Kode Program <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('kode_program') is-invalid @enderror"
                                           id="kode_program" name="kode_program" value="{{ old('kode_program') }}" required>
                                    @error('kode_program')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Contoh: PRG-001</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_program">Nama Program <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_program') is-invalid @enderror"
                                           id="nama_program" name="nama_program" value="{{ old('nama_program') }}" required>
                                    @error('nama_program')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                      id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Penjelasan detail tentang program ini</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="anggaran">Anggaran <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('anggaran') is-invalid @enderror"
                                           id="anggaran" name="anggaran" value="{{ old('anggaran', 0) }}" min="0" step="1" required>
                                    @error('anggaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Dalam Rupiah</small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tahun">Tahun <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" required>
                                        <option value="">Pilih Tahun</option>
                                        @for($year = date('Y'); $year <= date('Y') + 5; $year++)
                                            <option value="{{ $year }}" {{ old('tahun', date('Y')) == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="penanggung_jawab">Penanggung Jawab</label>
                            <input type="text" class="form-control @error('penanggung_jawab') is-invalid @enderror"
                                   id="penanggung_jawab" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}">
                            @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" {{ $sasaranStrategis->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('sakip.program.index') }}" class="btn btn-secondary">
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
    // Cascade dropdown for Sasaran Strategis based on Instansi selection
    $('#instansi_id').on('change', function() {
        var instansiId = $(this).val();
        var sasaranSelect = $('#sasaran_strategis_id');

        // Clear current options
        sasaranSelect.html('<option value="">Loading...</option>');

        if (instansiId) {
            // Fetch Sasaran Strategis for selected Instansi
            $.ajax({
                url: '/sakip/api/sasaran-strategis/by-instansi/' + instansiId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    sasaranSelect.html('<option value="">Pilih Sasaran Strategis</option>');

                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            sasaranSelect.append('<option value="' + value.id + '">' + value.nama_strategis + '</option>');
                        });
                    } else {
                        sasaranSelect.html('<option value="">Tidak ada sasaran strategis untuk instansi ini</option>');
                    }
                },
                error: function() {
                    sasaranSelect.html('<option value="">Error loading data</option>');
                }
            });
        } else {
            sasaranSelect.html('<option value="">Pilih Instansi terlebih dahulu</option>');
        }
    });

    // Trigger change if instansi is already selected (for edit or old input)
    @if(old('instansi_id'))
        $('#instansi_id').trigger('change');
        @if(old('sasaran_strategis_id'))
            setTimeout(function() {
                $('#sasaran_strategis_id').val('{{ old('sasaran_strategis_id') }}');
            }, 500);
        @endif
    @endif

    // Format currency input
    $('#anggaran').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });
});
</script>
@endpush
