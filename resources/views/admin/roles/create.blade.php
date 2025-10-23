@extends('layouts.app')

@section('title', 'Tambah Role Baru')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plus-circle"></i> Tambah Role Baru
        </h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shield-alt"></i> Informasi Role
                    </h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validasi Gagal!</h6>
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

                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf

                        <!-- Role Name -->
                        <div class="form-group mb-3">
                            <label for="name" class="form-label font-weight-bold">
                                Nama Role <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Contoh: Editor, Reviewer, Analyst"
                                   required>
                            @error('name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">Nama unik untuk role ini</small>
                        </div>

                        <!-- Guard Name -->
                        <div class="form-group mb-3">
                            <label for="guard_name" class="form-label font-weight-bold">
                                Guard Name
                            </label>
                            <input type="text"
                                   class="form-control @error('guard_name') is-invalid @enderror"
                                   id="guard_name"
                                   name="guard_name"
                                   value="{{ old('guard_name', 'web') }}"
                                   placeholder="web">
                            @error('guard_name')
                                <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">Opsional - untuk membedakan guard (biasanya 'web')</small>
                        </div>

                        <!-- Permissions Selection -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-key"></i> Pilih Izin
                            </label>
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <div class="row">
                                        @forelse($permissions as $permission)
                                            <div class="col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                           class="custom-control-input"
                                                           id="permission_{{ $permission->id }}"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <br>
                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <p class="text-muted mb-0">Tidak ada izin tersedia</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @error('permissions')
                                <small class="form-text text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted d-block mt-2">Pilih izin-izin yang akan diberikan ke role ini</small>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Simpan Role
                            </button>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Panduan
                    </h6>
                </div>
                <div class="card-body small">
                    <h6 class="font-weight-bold">Cara Membuat Role:</h6>
                    <ol class="pl-3">
                        <li>Masukkan nama role yang deskriptif</li>
                        <li>Pilih izin-izin yang dibutuhkan</li>
                        <li>Klik "Simpan Role"</li>
                    </ol>

                    <hr>

                    <h6 class="font-weight-bold">Tips:</h6>
                    <ul class="pl-3 mb-0">
                        <li>Gunakan nama yang jelas dan deskriptif</li>
                        <li>Pilih izin minimal yang dibutuhkan</li>
                        <li>Anda dapat mengubah izin nanti</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-lightbulb"></i> Contoh Role
                    </h6>
                </div>
                <div class="card-body small">
                    <p><strong>Editor:</strong> Dapat membuat dan mengedit konten</p>
                    <p><strong>Reviewer:</strong> Dapat melihat dan mengomentari konten</p>
                    <p><strong>Admin:</strong> Akses penuh ke semua fitur</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
