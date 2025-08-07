@extends('layouts.app')

@section('title', 'Pengaturan')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0" style="color: var(--text-color);">Pengaturan Sistem</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cog me-2"></i>
                    Pengaturan Aplikasi
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('pengaturan.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Nama Aplikasi</label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror" 
                                       id="app_name" name="app_name" value="{{ old('app_name', 'SAKIP') }}" required>
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Email Kontak</label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', 'admin@sakip.go.id') }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" name="contact_phone" value="{{ old('contact_phone', '021-1234567') }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="app_version" class="form-label">Versi Aplikasi</label>
                                <input type="text" class="form-control" id="app_version" value="1.0.0" readonly>
                                <small class="form-text" style="color: var(--secondary-color);">Versi aplikasi saat ini</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="app_description" class="form-label">Deskripsi Aplikasi</label>
                        <textarea class="form-control @error('app_description') is-invalid @enderror" 
                                  id="app_description" name="app_description" rows="3">{{ old('app_description', 'Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) adalah aplikasi untuk mengelola dan memantau kinerja instansi pemerintah.') }}</textarea>
                        @error('app_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi Sistem
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong style="color: var(--text-color);">Framework:</strong><br>
                    <span style="color: var(--secondary-color);">Laravel {{ app()->version() }}</span>
                </div>
                
                <div class="mb-3">
                    <strong style="color: var(--text-color);">PHP Version:</strong><br>
                    <span style="color: var(--secondary-color);">{{ PHP_VERSION }}</span>
                </div>
                
                <div class="mb-3">
                    <strong style="color: var(--text-color);">Database Engine:</strong><br>
                    <span style="color: var(--secondary-color);">{{ strtoupper(config('database.default')) }} ({{ config('database.connections.' . config('database.default') . '.driver') }})</span>
                </div>
                
                <div class="mb-3">
                    <strong style="color: var(--text-color);">Database Name:</strong><br>
                    <span style="color: var(--secondary-color);">{{ config('database.connections.' . config('database.default') . '.database') ?: 'N/A' }}</span>
                </div>
                
                <div class="mb-3">
                    <strong style="color: var(--text-color);">Environment:</strong><br>
                    <span class="badge {{ app()->environment('production') ? 'bg-success' : 'bg-warning' }}">
                        {{ strtoupper(app()->environment()) }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <strong style="color: var(--text-color);">Last Updated:</strong><br>
                    <span style="color: var(--secondary-color);">{{ date('d M Y H:i:s') }}</span>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tools me-2"></i>
                    Maintenance
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>
                        Clear Cache
                    </button>
                    
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="optimizeApp()">
                        <i class="fas fa-rocket me-2"></i>
                        Optimize App
                    </button>
                    
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="backupDatabase()">
                        <i class="fas fa-database me-2"></i>
                        Backup Database
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function clearCache() {
        if (confirm('Apakah Anda yakin ingin menghapus cache aplikasi?')) {
            $.ajax({
                url: '{{ route("pengaturan.clear-cache") }}',
                type: 'POST',
                beforeSend: function() {
                    // Show loading state
                    $('button[onclick="clearCache()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menghapus...');
                },
                success: function(response) {
                     if (response.success) {
                         let message = '✅ ' + response.message;
                         if (response.file_size) {
                             message += '\nUkuran file: ' + response.file_size;
                         }
                         if (response.file_path) {
                             message += '\nLokasi: ' + response.file_path;
                         }
                         alert(message);
                     } else {
                         alert('❌ ' + response.message);
                     }
                 },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat menghapus cache';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('❌ ' + message);
                },
                complete: function() {
                    // Reset button state
                    $('button[onclick="clearCache()"]').prop('disabled', false).html('<i class="fas fa-trash"></i> Clear Cache');
                }
            });
        }
    }
    
    function optimizeApp() {
        if (confirm('Apakah Anda yakin ingin mengoptimalkan aplikasi?')) {
            $.ajax({
                url: '{{ route("pengaturan.optimize") }}',
                type: 'POST',
                beforeSend: function() {
                    $('button[onclick="optimizeApp()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengoptimalkan...');
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message);
                    } else {
                        alert('❌ ' + response.message);
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat mengoptimalkan aplikasi';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('❌ ' + message);
                },
                complete: function() {
                    $('button[onclick="optimizeApp()"]').prop('disabled', false).html('<i class="fas fa-rocket"></i> Optimize App');
                }
            });
        }
    }
    
    function backupDatabase() {
        if (confirm('Apakah Anda yakin ingin membuat backup database?')) {
            $.ajax({
                url: '{{ route("pengaturan.backup") }}',
                type: 'POST',
                beforeSend: function() {
                    $('button[onclick="backupDatabase()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Membackup...');
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message);
                    } else {
                        alert('❌ ' + response.message);
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat membuat backup';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert('❌ ' + message);
                },
                complete: function() {
                    $('button[onclick="backupDatabase()"]').prop('disabled', false).html('<i class="fas fa-database"></i> Backup Database');
                }
            });
        }
    }
</script>
@endpush