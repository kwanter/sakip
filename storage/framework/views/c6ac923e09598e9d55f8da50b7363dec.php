<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_users']); ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_roles']); ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['active_sessions']); ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e(number_format($stats['system_load'], 2)); ?></div>
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
                                <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <?php if($log->user): ?>
                                            <strong><?php echo e($log->user->name); ?></strong><br>
                                            <small class="text-muted"><?php echo e($log->user->email); ?></small>
                                        <?php else: ?>
                                            <em>System</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo e($log->action); ?></span>
                                    </td>
                                    <td>
                                        <small><?php echo e($log->created_at->diffForHumans()); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo e($log->ip_address); ?></code>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent activity</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('admin.audit-logs')); ?>" class="btn btn-sm btn-primary">
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
                        <a href="<?php echo e(route('admin.users.create')); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus fa-fw mr-2"></i>
                            Create New User
                        </a>
                        <a href="<?php echo e(route('admin.users.index')); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-users fa-fw mr-2"></i>
                            Manage Users
                        </a>
                        <a href="<?php echo e(route('admin.audit-logs')); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-history fa-fw mr-2"></i>
                            View Audit Logs
                        </a>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage-settings')): ?>
                        <a href="<?php echo e(route('admin.settings.index')); ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog fa-fw mr-2"></i>
                            System Settings
                        </a>
                        <?php endif; ?>
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
                        <strong>Permissions:</strong> <?php echo e($stats['total_permissions']); ?><br>
                        <strong>Recent Logins:</strong> <?php echo e($stats['recent_logins']); ?> (last 7 days)<br>
                        <strong>PHP Version:</strong> <?php echo e(phpversion()); ?><br>
                        <strong>Laravel Version:</strong> <?php echo e(app()->version()); ?><br>
                        <strong>Environment:</strong> <?php echo e(config('app.env')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // User growth chart
    const userGrowthData = <?php echo json_encode($userGrowth, 15, 512) ?>;
    
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>