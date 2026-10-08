@extends('layouts.modern')

@section('title', 'Create User')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Create User</h1>
                <p class="page-header-subtitle">Add a new user to the system</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">User Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-3 col-form-label text-md-end">Name</label>
                            <div class="col-md-9">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-3 col-form-label text-md-end">Email Address</label>
                            <div class="col-md-9">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="instansi_id" class="col-md-3 col-form-label text-md-end">Instansi</label>
                            <div class="col-md-9">
                                <select id="instansi_id" class="form-select @error('instansi_id') is-invalid @enderror"
                                        name="instansi_id">
                                    <option value="">-- Select Instansi (Optional) --</option>
                                    @foreach($instansis as $instansi)
                                        <option value="{{ $instansi->id }}" {{ old('instansi_id') == $instansi->id ? 'selected' : '' }}>
                                            {{ $instansi->nama_instansi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instansi_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">
                                    Assign user to a specific institution. Leave empty for system-wide access.
                                </small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-3 col-form-label text-md-end">Password</label>
                            <div class="col-md-9">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-3 col-form-label text-md-end">Confirm Password</label>
                            <div class="col-md-9">
                                <input id="password-confirm" type="password" class="form-control"
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="roles" class="col-md-3 col-form-label text-md-end">Roles</label>
                            <div class="col-md-9">
                                @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]"
                                           id="role_{{ $role->id }}" value="{{ $role->id }}"
                                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ ucfirst($role->name) }}
                                        @if($role->description)
                                            <small class="text-muted">- {{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                                @error('roles')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-9 offset-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create User
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Help</h2>
                </div>
                <div class="card-body">
                    <h3 class="fs-6 fw-semibold">Password Requirements</h3>
                    <ul class="small">
                        <li>Minimum 8 characters</li>
                        <li>Should include uppercase and lowercase letters</li>
                        <li>Should include numbers</li>
                        <li>Should include special characters</li>
                    </ul>

                    <h3 class="fs-6 fw-semibold">Role Assignment</h3>
                    <p class="small">
                        Select one or more roles for the user. Roles determine what permissions the user will have.
                        You can change roles later from the user edit page.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
