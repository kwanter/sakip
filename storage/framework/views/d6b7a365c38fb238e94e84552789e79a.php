<?php $__env->startSection('title', 'System Settings'); ?>

<?php $__env->startPush('head'); ?>
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h3">System Settings</h1>
      <p class="text-muted">Manage application-wide settings. Update values and types, then save.</p>
    </div>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if(session('status')): ?>
    <div class="alert alert-success"><?php echo e(session('status')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-lg-8">
      <!-- Application Settings -->
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span>Application Settings</span>
          <small class="text-muted">Basic app configuration</small>
        </div>
        <div class="card-body">
          <form id="appSettingsForm" onsubmit="return saveAppSettings(event)">
            <div class="mb-3">
              <label for="app_name" class="form-label">Application Name</label>
              <input type="text" id="app_name" class="form-control" maxlength="150" value="<?php echo e(collect($settings)->firstWhere('key','app.name')?->value ?? config('app.name')); ?>" required>
              <div class="invalid-feedback">Application name is required.</div>
            </div>
            <div class="mb-3">
              <label for="app_description" class="form-label">Application Description</label>
              <textarea id="app_description" class="form-control" rows="3" maxlength="500"><?php echo e(collect($settings)->firstWhere('key','app.description')?->value ?? ''); ?></textarea>
              <div class="d-flex justify-content-between mt-1">
                <small class="text-muted"><span id="descCount">0</span>/500</small>
                <small id="descFeedback" class="text-danger" style="display:none">Description must be 0–500 characters.</small>
              </div>
            </div>
            <div class="mt-2">
              <button type="submit" class="btn btn-primary" id="appSettingsSaveBtn">Save Application Settings</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Dynamic Settings -->
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span>Settings</span>
          <small class="text-muted">Edit and save changes</small>
        </div>
        <div class="card-body">
          <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
            <?php echo csrf_field(); ?>

            <?php $skipKeys = ['app.name','app.description']; ?>

            <div class="row g-3">
              <?php $__empty_1 = true; $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if(in_array($setting->key, $skipKeys)) continue; ?>
                <div class="col-12">
                  <div class="border rounded p-3 mb-2">
                    <div class="row g-3 align-items-start">
                      <div class="col-md-4">
                        <label class="form-label">Key</label>
                        <input type="text" class="form-control" value="<?php echo e($setting->key); ?>" readonly>
                        <input type="hidden" name="settings[<?php echo e($setting->key); ?>][key]" value="<?php echo e($setting->key); ?>">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="settings[<?php echo e($setting->key); ?>][type]">
                          <?php
                            $types = ['string','integer','float','boolean','array','json'];
                            $currentType = $setting->type ?? 'string';
                          ?>
                          <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php if($currentType === $type): echo 'selected'; endif; ?>><?php echo e(ucfirst($type)); ?></option>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Value</label>
                        <?php $type = $setting->type ?? 'string'; ?>
                        <?php if($type === 'boolean'): ?>
                          <select class="form-select" name="settings[<?php echo e($setting->key); ?>][value]">
                            <option value="1" <?php if((string)$setting->value === '1'): echo 'selected'; endif; ?>>True</option>
                            <option value="0" <?php if((string)$setting->value === '0'): echo 'selected'; endif; ?>>False</option>
                          </select>
                        <?php elseif($type === 'integer'): ?>
                          <input type="number" step="1" class="form-control" name="settings[<?php echo e($setting->key); ?>][value]" value="<?php echo e(old('settings.'.$setting->key.'.value', $setting->value)); ?>">
                        <?php elseif($type === 'float'): ?>
                          <input type="number" step="any" class="form-control" name="settings[<?php echo e($setting->key); ?>][value]" value="<?php echo e(old('settings.'.$setting->key.'.value', $setting->value)); ?>">
                        <?php elseif($type === 'json' || $type === 'array'): ?>
                          <textarea class="form-control" rows="4" name="settings[<?php echo e($setting->key); ?>][value]"><?php echo e(old('settings.'.$setting->key.'.value', is_string($setting->value) ? $setting->value : json_encode($setting->value, JSON_PRETTY_PRINT))); ?></textarea>
                        <?php else: ?>
                          <input type="text" class="form-control" name="settings[<?php echo e($setting->key); ?>][value]" value="<?php echo e(old('settings.'.$setting->key.'.value', $setting->value)); ?>">
                        <?php endif; ?>
                      </div>

                      <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" name="settings[<?php echo e($setting->key); ?>][description]"><?php echo e(old('settings.'.$setting->key.'.description', $setting->description)); ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                  <p class="text-muted mb-0">No settings found.</p>
                </div>
              <?php endif; ?>
            </div>

            <div class="mt-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="<?php echo e(url()->current()); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-info-circle me-2"></i>
            System Info
          </h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <strong>Framework:</strong><br>
            <span class="text-muted">Laravel <?php echo e(app()->version()); ?></span>
          </div>
          <div class="mb-3">
            <strong>PHP Version:</strong><br>
            <span class="text-muted"><?php echo e(PHP_VERSION); ?></span>
          </div>
          <div class="mb-3">
            <strong>Database Engine:</strong><br>
            <span class="text-muted"><?php echo e(strtoupper(config('database.default'))); ?> (<?php echo e(config('database.connections.' . config('database.default') . '.driver')); ?>)</span>
          </div>
          <div class="mb-3">
            <strong>Database Name:</strong><br>
            <span class="text-muted"><?php echo e(config('database.connections.' . config('database.default') . '.database') ?: 'N/A'); ?></span>
          </div>
          <div class="mb-3">
            <strong>Environment:</strong><br>
            <span class="badge <?php echo e(app()->environment('production') ? 'bg-success' : 'bg-warning'); ?>">
              <?php echo e(strtoupper(app()->environment())); ?>

            </span>
          </div>
          <div class="mb-3">
            <strong>Last Updated:</strong><br>
            <span class="text-muted"><?php echo e(date('d M Y H:i:s')); ?></span>
          </div>
        </div>
      </div>

      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-tools me-2"></i>
            Maintenance
          </h6>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearCache()">
              <i class="fas fa-broom me-2"></i>
              Clear Cache
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" onclick="optimizeApp()">
              <i class="fas fa-rocket me-2"></i>
              Optimize App
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="backupDatabase()">
              <i class="fas fa-database me-2"></i>
              Backup Database
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  function clearCache() {
    if (confirm('Are you sure you want to clear application cache?')) {
      $.ajax({
        url: '<?php echo e(route("admin.settings.clear-cache")); ?>',
        type: 'POST',
        beforeSend: function() {
          $('button[onclick="clearCache()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Clearing...');
        },
        success: function(response) {
          alert((response.success ? '✅ ' : '❌ ') + response.message);
        },
        error: function(xhr) {
          let message = 'Error clearing cache';
          if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
          alert('❌ ' + message);
        },
        complete: function() {
          $('button[onclick="clearCache()"]').prop('disabled', false).html('<i class="fas fa-broom"></i> Clear Cache');
        }
      });
    }
  }

  function optimizeApp() {
    if (confirm('Are you sure you want to optimize the application?')) {
      $.ajax({
        url: '<?php echo e(route("admin.settings.optimize")); ?>',
        type: 'POST',
        beforeSend: function() {
          $('button[onclick="optimizeApp()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Optimizing...');
        },
        success: function(response) {
          alert((response.success ? '✅ ' : '❌ ') + response.message);
        },
        error: function(xhr) {
          let message = 'Error optimizing application';
          if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
          alert('❌ ' + message);
        },
        complete: function() {
          $('button[onclick="optimizeApp()"]').prop('disabled', false).html('<i class="fas fa-rocket"></i> Optimize App');
        }
      });
    }
  }

  function backupDatabase() {
    if (confirm('Are you sure you want to create a database backup?')) {
      $.ajax({
        url: '<?php echo e(route("admin.settings.backup")); ?>',
        type: 'POST',
        beforeSend: function() {
          $('button[onclick="backupDatabase()"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Backing up...');
        },
        success: function(response) {
          let message = (response.success ? '✅ ' : '❌ ') + response.message;
          if (response.file_size) { message += '\nSize: ' + response.file_size; }
          if (response.file_path) { message += '\nPath: ' + response.file_path; }
          alert(message);
        },
        error: function(xhr) {
          let message = 'Error creating backup';
          if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
          alert('❌ ' + message);
        },
        complete: function() {
          $('button[onclick="backupDatabase()"]').prop('disabled', false).html('<i class="fas fa-database"></i> Backup Database');
        }
      });
    }
  }

  // Application Settings: client-side validation and AJAX save
  (function() {
    // Cache inputs and feedback elements
    const appNameInput = document.getElementById('app_name');
    const appDescInput = document.getElementById('app_description');
    const descCount = document.getElementById('descCount');
    const descFeedback = document.getElementById('descFeedback');

    // Update description char counter and validity
    function updateDescCount() {
      if (!appDescInput || !descCount || !descFeedback) return;
      const len = appDescInput.value.length;
      descCount.textContent = len;
      if (len > 500) {
        descFeedback.style.display = 'inline';
        appDescInput.classList.add('is-invalid');
      } else {
        descFeedback.style.display = 'none';
        appDescInput.classList.remove('is-invalid');
      }
    }
    appDescInput && appDescInput.addEventListener('input', updateDescCount);
    updateDescCount();

    appNameInput && appNameInput.addEventListener('input', function() {
      if (appNameInput.value.trim() === '') {
        appNameInput.classList.add('is-invalid');
      } else {
        appNameInput.classList.remove('is-invalid');
      }
    });

    /**
     * Save Application Settings via AJAX
     * Performs client-side validation, posts to server, and updates UI.
     */
    window.saveAppSettings = function(e) {
      e.preventDefault();
      const btn = document.getElementById('appSettingsSaveBtn');
      const name = appNameInput ? appNameInput.value.trim() : '';
      const desc = appDescInput ? appDescInput.value : '';

      // Client-side checks
      let hasError = false;
      if (!name && appNameInput) { appNameInput.classList.add('is-invalid'); hasError = true; }
      if (desc.length > 500 && appDescInput) { appDescInput.classList.add('is-invalid'); hasError = true; }
      if (hasError) { return false; }

      $.ajax({
        url: '<?php echo e(route("admin.settings.update")); ?>',
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
          settings: {
            'app.name': { key: 'app.name', value: name, type: 'string', description: 'Application name' },
            'app.description': { key: 'app.description', value: desc, type: 'string', description: 'Application description' }
          }
        },
        beforeSend: function() {
          if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
          }
        },
        success: function(res) {
          const newName = name;
          if (newName) {
            document.title = newName + ' • Admin Settings';
            const navBrand = document.querySelector('.navbar-brand');
            if (navBrand) { navBrand.textContent = newName; }
            document.querySelectorAll('[data-app-name]').forEach(function(el) { el.textContent = newName; });
          }
          alert('✅ Application settings saved successfully.');
        },
        error: function(xhr) {
          let message = 'Error saving application settings';
          if (xhr.responseJSON && xhr.responseJSON.message) { message = xhr.responseJSON.message; }
          alert('❌ ' + message);
        },
        complete: function() {
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Save Application Settings';
          }
        }
      });
      return false;
    };
  })();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/macbook/Developer/php/sakip/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>