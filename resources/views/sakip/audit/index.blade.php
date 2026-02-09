@extends('layouts.modern')

@section('title', 'Audit & Compliance')

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-layout">
            <div>
                <h1 class="page-header-title">Audit & Compliance</h1>
                <p class="page-header-subtitle">Monitor audit trails dan kepatuhan</p>
            </div>
            <div class="page-header-actions">
                @can('run-compliance-check', App\Models\SakipAudit::class)
                <button type="button" onclick="runComplianceCheck()" class="btn btn-danger">
                    <i class="fas fa-shield-alt"></i>
                    <span class="ms-1">Run Compliance</span>
                </button>
                @endcan
                @can('generate-audit-report', App\Models\SakipAudit::class)
                <a href="{{ route('sakip.audit.export-report') }}" class="btn btn-success">
                    <i class="fas fa-file-export"></i>
                    <span class="ms-1">Export Report</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle alert-icon"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <span>{{ session('error') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Compliance Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-success">
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Overall Compliance</div>
                    <div class="stat-card-value">{{ $compliance['overall_score'] ?? 0 }}%</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-warning">
                <div class="stat-card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Violations</div>
                    <div class="stat-card-value">{{ $compliance['violations_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-card-info">
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-label">Last Check</div>
                    <div class="stat-card-value small">
                        {{ $compliance['last_check'] ? $compliance['last_check']->diffForHumans() : 'Never' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Details -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-list me-2"></i>
                Compliance Details
            </h5>
        </div>
        <div class="card-body">
            <div class="compliance-checks">
                @foreach($compliance['checks'] ?? [] as $check)
                <div class="compliance-check-item">
                    <div class="compliance-check-info">
                        <div class="compliance-check-name">{{ $check['name'] }}</div>
                        <div class="compliance-check-desc">{{ $check['description'] }}</div>
                    </div>
                    <div class="compliance-check-score">
                        <div class="progress" style="width: 100px; height: 8px;">
                            <div class="progress-bar bg-{{ $check['score'] >= 80 ? 'success' : ($check['score'] >= 60 ? 'warning' : 'danger') }}"
                                 role="progressbar"
                                 style="width: {{ $check['score'] }}%"></div>
                        </div>
                        <span class="ms-2 fw-bold">{{ $check['score'] }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Violations -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-exclamation-circle me-2"></i>
                Recent Violations
            </h5>
            <span class="badge bg-{{ count($compliance['violations'] ?? []) > 0 ? 'danger' : 'success' }}">
                {{ count($compliance['violations'] ?? []) }} violations
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Severity</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compliance['violations'] ?? [] as $violation)
                        <tr>
                            <td>
                                <span class="badge bg-danger">
                                    {{ ucfirst(str_replace('_', ' ', $violation['type'] ?? 'unknown')) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $violation['message'] ?? 'No description' }}</div>
                                <small class="text-muted">{{ $violation['recommendation'] ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ ($violation['severity'] ?? 'low') === 'high' ? 'danger' : (($violation['severity'] ?? 'low') === 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($violation['severity'] ?? 'low') }}
                                </span>
                            </td>
                            <td>{{ now()->format('M d, Y') }}</td>
                            <td>
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <p class="mb-0">No violations found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>
                Recent Audit Logs
            </h5>
            <span class="text-muted small">Showing latest activities</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs ?? [] as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $log->user->name ?? 'System' }}</td>
                            <td>
                                <span class="badge bg-{{ $log->event_type === 'delete' ? 'danger' : ($log->event_type === 'update' ? 'warning' : ($log->event_type === 'create' ? 'success' : 'info')) }}">
                                    {{ ucfirst($log->event_type) }}
                                </span>
                            </td>
                            <td>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td>
                                @if($log->old_values || $log->new_values)
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showChanges({{ $log->id }})">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                @else
                                    <span class="text-muted">No changes</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list text-muted"></i>
                                    <p class="mb-0">No audit logs found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($auditLogs) && $auditLogs->hasPages())
            <div class="p-3 border-top">
                {{ $auditLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Changes Modal -->
<div class="modal fade" id="changesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt me-2"></i>
                    Changes Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="changesContent">
                <!-- Changes will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    <span class="ms-1">Close</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function runComplianceCheck() {
    if (confirm('This will run a comprehensive compliance check. Continue?')) {
        fetch('{{ route('sakip.audit.run-compliance-check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Compliance check completed. Found ' + data.violations_count + ' violations.', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('Compliance check failed: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            showNotification('An error occurred during compliance check', 'danger');
        });
    }
}

function showChanges(logId) {
    fetch(`/sakip/audit/logs/${logId}/changes`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Server-sanitized HTML content
                const contentDiv = document.getElementById('changesContent');
                contentDiv.textContent = '';
                const temp = document.createElement('div');
                temp.innerHTML = data.html;
                while (temp.firstChild) {
                    contentDiv.appendChild(temp.firstChild);
                }

                const modal = new bootstrap.Modal(document.getElementById('changesModal'));
                modal.show();
            }
        })
        .catch(error => {
            showNotification('Failed to load changes', 'danger');
        });
}
</script>
@endpush
