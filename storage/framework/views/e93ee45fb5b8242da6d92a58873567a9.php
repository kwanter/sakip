<?php $__env->startSection('title', 'Audit Logs'); ?>

<?php $__env->startSection('content'); ?>
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
                    <form method="GET" action="<?php echo e(route('admin.audit-logs')); ?>">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="action">Action</label>
                                <input type="text" class="form-control" id="action" name="action" value="<?php echo e(request('action')); ?>" placeholder="e.g. user.updated, settings.updated">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="user">User</label>
                                <input type="text" class="form-control" id="user" name="user" value="<?php echo e(request('user')); ?>" placeholder="name or email">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo e(request('date_from')); ?>">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="date_to">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo e(request('date_to')); ?>">
                            </div>
                        </div>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?php echo e(route('admin.audit-logs')); ?>" class="btn btn-secondary">
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
                    <span class="badge badge-light">Total: <?php echo e($logs->total()); ?></span>
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
                                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                                        <?php
                                            // Normalize details into array for display
                                            $details = is_array($log->details) ? $log->details : (array) $log->details;
                                        ?>
                                        <?php if(empty($details)): ?>
                                            <span class="text-muted">(no details)</span>
                                        <?php else: ?>
                                            <div class="small">
                                                <ul class="mb-0 pl-3">
                                                    <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li>
                                                            <strong><?php echo e($key); ?></strong>:
                                                            <?php if(is_array($value)): ?>
                                                                <code><?php echo e(json_encode($value, JSON_UNESCAPED_UNICODE)); ?></code>
                                                            <?php else: ?>
                                                                <code><?php echo e((string) $value); ?></code>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo e($log->ip_address); ?></code>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></div>
                                            <div class="text-muted"><?php echo e($log->created_at->diffForHumans()); ?></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-history fa-2x mb-2"></i>
                                        <div>No audit logs found for the current filters.</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <?php echo e($logs->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/audit-logs.blade.php ENDPATH**/ ?>