@extends('layouts.app')

@section('title', 'Edit Program')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tasks"></i> Edit Program
        </h1>
        <a href="{{ route('sakip.program.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Edit Program</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('sakip.program.update', $program) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instansi_id">Instansi <span class="text-danger">*</span></label>
                                    <select class="form-control @error('instansi_id') is-invalid @enderror"
                                            id="instansi_id" name="instansi_id" required>
                                        <option value="">Pilih Instansi</option>
                                        @foreach($instansis as $instansi)
                                            <option value="{{ $instansi->id }}"
                                                {{ old('instansi_id', $program->instansi_id) == $instansi->id ? 'selected' : '' }}>
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
                                            id="sasaran_strategis_id" name="sasaran_strategis_id" required>
                                        <option value="">Pilih Instansi terlebih dahulu</option>
                                        @foreach($sasaranStrategis as $sasaran)
                                            <option value="{{ $sasaran->id }}"
                                                data-instansi="{{ $sasaran->instansi_id }}"
                                                {{ old('sasaran_strategis_id', $program->sasaran_strategis_id) == $sasaran->id ? 'selected' : '' }}>
                                                {{ $sasaran->nama_strategis }}
                                            </option>
                                        @endforeach
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
                                           id="kode_program" name="kode_program"
                                           value="{{ old('kode_program', $program->kode_program) }}" required>
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
                                           id="nama_program" name="nama_program"
                                           value="{{ old('nama_program', $program->nama_program) }}" required>
                                    @error('nama_program')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                      id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $program->deskripsi) }}</textarea>
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
                                           id="anggaran" name="anggaran"
                                           value="{{ old('anggaran', $program->anggaran) }}" min="0" step="1" required>
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
                                        @for($year = date('Y') - 5; $year <= date('Y') + 5; $year++)
                                            <option value="{{ $year }}"
                                                {{ old('tahun', $program->tahun) == $year ? 'selected' : '' }}>
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
                                        <option value="draft" {{ old('status', $program->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="aktif" {{ old('status', $program->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="selesai" {{ old('status', $program->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
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
                                   id="penanggung_jawab" name="penanggung_jawab"
                                   value="{{ old('penanggung_jawab', $program->penanggung_jawab) }}">
                            @error('penanggung_jawab')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
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
        var currentSasaran = '{{ old('sasaran_strategis_id', $program->sasaran_strategis_id) }}';

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
                            var selected = (value.id == currentSasaran) ? 'selected' : '';
                            sasaranSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.nama_strategis + '</option>');
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

    // Trigger change on page load to populate Sasaran Strategis
    $('#instansi_id').trigger('change');

    // Format currency input
    $('#anggaran').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });
});
</script>
@endpush
