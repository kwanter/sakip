@extends('layouts.modern')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Audit Logs</h1>
                <p class="page-header-subtitle">Review system activities, user actions, and changes over time.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title fs-6 m-0">Search &amp; Filters</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.audit-logs') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="action" class="form-label">Action</label>
                                <input type="text" class="form-control" id="action" name="action" value="{{ request('action') }}" placeholder="e.g. user.updated, settings.updated">
                            </div>
                            <div class="col-md-4">
                                <label for="user" class="form-label">User</label>
                                <input type="text" class="form-control" id="user" name="user" value="{{ request('user') }}" placeholder="name or email">
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.audit-logs') }}" class="btn btn-outline-secondary">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 class="card-title fs-6 m-0">Logs</h2>
                    <span class="badge badge-neutral">Total: {{ $logs->total() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover modern-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 22%">User</th>
                                    <th style="width: 14%">Action</th>
                                    <th>Details</th>
                                    <th style="width: 14%">IP Address</th>
                                    <th style="width: 18%">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>
                                        @if($log->user)
                                            <strong>{{ $log->user->name }}</strong><br>
                                            <small class="text-muted">{{ $log->user->email }}</small>
                                        @else
                                            <em>System</em>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $log->action }}</span>
                                    </td>
                                    <td>
                                        @php
                                            // Normalize details into array for display
                                            $details = is_array($log->details) ? $log->details : (array) $log->details;
                                        @endphp
                                        @if(empty($details))
                                            <span class="text-muted">(no details)</span>
                                        @else
                                            <div class="small">
                                                <ul class="mb-0 ps-3">
                                                    @foreach($details as $key => $value)
                                                        <li>
                                                            <strong>{{ $key }}</strong>:
                                                            @if(is_array($value))
                                                                <code>{{ json_encode($value, JSON_UNESCAPED_UNICODE) }}</code>
                                                            @else
                                                                <code>{{ (string) $value }}</code>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div>{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                                            <div class="text-muted">{{ $log->created_at->diffForHumans() }}</div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <div>No audit logs found for the current filters.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
