<?php $__env->startSection('title', 'User Details - ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">User Details</h1>
            <p class="text-muted">View detailed information about <?php echo e($user->name); ?></p>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center">
                            <span class="h3 mb-0"><?php echo e(substr($user->name, 0, 1)); ?></span>
                        </div>
                    </div>

                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?php echo e($user->name); ?></dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?php echo e($user->email); ?></dd>

                        <dt class="col-sm-4">Instansi:</dt>
                        <dd class="col-sm-8">
                            <?php if($user->instansi_id): ?>
                                <span class="badge badge-info"><?php echo e($user->instansi->nama_instansi ?? 'N/A'); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary">System Wide</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <?php if($user->email_verified_at): ?>
                                <span class="badge badge-success">Verified</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Unverified</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8"><?php echo e($user->created_at->format('Y-m-d H:i')); ?></dd>

                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8"><?php echo e($user->updated_at->format('Y-m-d H:i')); ?></dd>
                    </dl>

                    <div class="mt-4">
                        <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <?php if($user->id !== auth()->id()): ?>
                            <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-block mt-2">
                                    <i class="fas fa-trash"></i> Delete User
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles and Permissions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Roles</h6>
                </div>
                <div class="card-body">
                    <?php if($user->roles->isNotEmpty()): ?>
                        <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-2">
                                <strong><?php echo e(ucfirst($role->name)); ?></strong>
                                <?php if($role->description): ?>
                                    <br><small class="text-muted"><?php echo e($role->description); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-muted">No roles assigned</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Direct Permissions</h6>
                </div>
                <div class="card-body">
                    <?php if($user->permissions->isNotEmpty()): ?>
                        <?php $__currentLoopData = $user->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge badge-info mb-1"><?php echo e($permission->name); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-muted">No direct permissions</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <?php if($user->auditLogs->isNotEmpty()): ?>
                        <div class="timeline">
                            <?php $__currentLoopData = $user->auditLogs->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item mb-3">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title"><?php echo e($log->action); ?></h6>
                                        <p class="timeline-text small text-muted">
                                            <?php echo e($log->created_at->diffForHumans()); ?>

                                            <br>IP: <?php echo e($log->ip_address); ?>

                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- All Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Activity</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $user->auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-info"><?php echo e($log->action); ?></span>
                                    </td>
                                    <td>
                                        <pre class="small mb-0"><?php echo e(json_encode($log->details, JSON_PRETTY_PRINT)); ?></pre>
                                    </td>
                                    <td><code><?php echo e($log->ip_address); ?></code></td>
                                    <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No activity found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/users/show.blade.php ENDPATH**/ ?>