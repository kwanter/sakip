@extends('layouts.modern')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Edit User</h1>
                <p class="page-header-subtitle">Update user information and roles</p>
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
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Instansi -->
                        <div class="mb-3">
                            <label for="instansi_id" class="form-label">Instansi</label>
                            <select id="instansi_id" class="form-select @error('instansi_id') is-invalid @enderror"
                                    name="instansi_id">
                                <option value="">-- Select Instansi (Optional) --</option>
                                @foreach($instansis as $instansi)
                                    <option value="{{ $instansi->id }}"
                                            {{ old('instansi_id', $user->instansi_id) == $instansi->id ? 'selected' : '' }}>
                                        {{ $instansi->nama_instansi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('instansi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Assign user to a specific institution. Leave empty for system-wide access.
                            </small>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Leave blank to keep current password. Minimum 8 characters.
                            </small>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <!-- Email Verification -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="email_verified" name="email_verified"
                                       {{ $user->email_verified_at ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verified">
                                    Email Verified
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to User
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Roles Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Roles</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.roles.update', $user) }}" method="POST" id="rolesForm">
                        @csrf
                        @foreach($roles as $role)
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                           id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                                           {{ $user->roles->contains('id', $role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ ucfirst($role->name) }}
                                        @if($role->description)
                                            <br><small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-save"></i> Update Roles
                        </button>
                    </form>
                </div>
            </div>

            <!-- Direct Permissions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Direct Permissions</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST" id="permissionsForm">
                        @csrf

                        @foreach($permissions as $permission)
                            <div class="mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                           id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}"
                                           {{ $user->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                        {{ ucfirst(str_replace('.', ' ', $permission->name)) }}
                                        @if($permission->description)
                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-save"></i> Update Permissions
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            @if($user->id !== auth()->id())
                <div class="card mb-4 border-danger">
                    <div class="card-header">
                        <h2 class="card-title fs-6 m-0 text-danger">Danger Zone</h2>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              data-confirm="Are you sure you want to delete this user? This action cannot be undone.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
