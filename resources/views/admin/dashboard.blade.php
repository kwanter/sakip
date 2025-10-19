@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
            <p class="text-muted">System administration and monitoring</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Roles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_roles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_sessions'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                System Load
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['system_load'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tachometer-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row">
        <!-- Recent Audit Logs -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
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
                                        <small>{{ $log->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $log->ip_address }}</code>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent activity</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.audit-logs') }}" class="btn btn-sm btn-primary">
                            View All Activity
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.users.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus fa-fw mr-2"></i>
                            Create New User
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-users fa-fw mr-2"></i>
                            Manage Users
                        </a>
                        <a href="{{ route('admin.audit-logs') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-history fa-fw mr-2"></i>
                            View Audit Logs
                        </a>
                        @can('manage-settings')
                        <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog fa-fw mr-2"></i>
                            System Settings
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- System Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <strong>Permissions:</strong> {{ $stats['total_permissions'] }}<br>
                        <strong>Recent Logins:</strong> {{ $stats['recent_logins'] }} (last 7 days)<br>
                        <strong>PHP Version:</strong> {{ phpversion() }}<br>
                        <strong>Laravel Version:</strong> {{ app()->version() }}<br>
                        <strong>Environment:</strong> {{ config('app.env') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // User growth chart
    const userGrowthData = @json($userGrowth);
    
    if (userGrowthData && userGrowthData.length > 0) {
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            const chartContainer = document.createElement('div');
            chartContainer.className = 'row';
            chartContainer.innerHTML = `
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">User Growth (Last 30 Days)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert before the closing of the main container
            const mainContent = document.querySelector('.container-fluid');
            if (mainContent) {
                mainContent.appendChild(chartContainer);
                
                // Create chart after DOM element exists
                const ctx = document.getElementById('userGrowthChart');
                if (ctx && typeof Chart !== 'undefined') {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: userGrowthData.map(item => item.date),
                            datasets: [{
                                label: 'New Users',
                                data: userGrowthData.map(item => item.count),
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }
        });
    }
</script>
@endsection