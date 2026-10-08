@extends('layouts.modern')

@section('title', 'User Details - ' . $user->name)

@section('content')
{{-- Page-scoped timeline styles (Genesis tokens only) --}}
<style>
    .timeline-item {
        display: flex;
        gap: 12px;
    }
    .timeline-marker {
        width: 8px;
        height: 8px;
        border-radius: var(--radius-full);
        background: var(--primary-500);
        margin-top: 6px;
        flex-shrink: 0;
    }
    .timeline-title {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 4px;
    }
    .user-avatar-lg {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-full);
        background: var(--primary-50);
        color: var(--primary-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
    }
</style>

<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">User Details</h1>
                <p class="page-header-subtitle">View detailed information about {{ $user->name }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">User Information</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="user-avatar-lg" aria-hidden="true">
                            <span>{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    </div>

                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Instansi:</dt>
                        <dd class="col-sm-8">
                            @if($user->instansi_id)
                                <span class="badge badge-primary">{{ $user->instansi->nama_instansi ?? 'N/A' }}</span>
                            @else
                                <span class="badge badge-neutral">System Wide</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Verified</span>
                            @else
                                <span class="badge badge-warning">Unverified</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('Y-m-d H:i') }}</dd>
                    </dl>

                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash"></i> Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles and Permissions -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Roles</h2>
                </div>
                <div class="card-body">
                    @if($user->roles->isNotEmpty())
                        @foreach($user->roles as $role)
                            <div class="mb-2">
                                <strong>{{ ucfirst($role->name) }}</strong>
                                @if($role->description)
                                    <br><small class="text-muted">{{ $role->description }}</small>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No roles assigned</p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Direct Permissions</h2>
                </div>
                <div class="card-body">
                    @if($user->permissions->isNotEmpty())
                        @foreach($user->permissions as $permission)
                            <span class="badge badge-primary mb-1">{{ $permission->name }}</span>
                        @endforeach
                    @else
                        <p class="text-muted">No direct permissions</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Recent Activity</h2>
                </div>
                <div class="card-body">
                    @if($user->auditLogs->isNotEmpty())
                        <div class="timeline">
                            @foreach($user->auditLogs->take(10) as $log)
                                <div class="timeline-item mb-3">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h3 class="timeline-title">{{ $log->action }}</h3>
                                        <p class="timeline-text small text-muted">
                                            {{ $log->created_at->diffForHumans() }}
                                            <br>IP: {{ $log->ip_address }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- All Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">All Activity</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->auditLogs as $log)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ $log->action }}</span>
                                    </td>
                                    <td>
                                        <pre class="small mb-0">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                    </td>
                                    <td><code>{{ $log->ip_address }}</code></td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No activity found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
