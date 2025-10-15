@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Audit Logs</h1>
            <p class="text-muted">Review system activities, user actions, and changes over time.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <!-- Filters -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Search & Filters</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.audit-logs') }}">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="action">Action</label>
                                <input type="text" class="form-control" id="action" name="action" value="{{ request('action') }}" placeholder="e.g. user.updated, settings.updated">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="user">User</label>
                                <input type="text" class="form-control" id="user" name="user" value="{{ request('user') }}" placeholder="name or email">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="date_to">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.audit-logs') }}" class="btn btn-secondary">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Logs</h6>
                    <span class="badge badge-light">Total: {{ $logs->total() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
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
                                        <span class="badge badge-info">{{ $log->action }}</span>
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
                                                <ul class="mb-0 pl-3">
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
