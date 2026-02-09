@extends('layouts.modern')

@section('title', 'System Settings')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h3">System Settings</h1>
      <p class="text-muted">Manage application-wide settings. Update values and types, then save.</p>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

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
              <input type="text" id="app_name" class="form-control" maxlength="150" value="{{ collect($settings)->firstWhere('key','app.name')?->value ?? config('app.name') }}" required>
              <div class="invalid-feedback">Application name is required.</div>
            </div>
            <div class="mb-3">
              <label for="app_description" class="form-label">Application Description</label>
              <textarea id="app_description" class="form-control" rows="3" maxlength="500">{{ collect($settings)->firstWhere('key','app.description')?->value ?? '' }}</textarea>
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
          <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            @php $skipKeys = ['app.name','app.description']; @endphp

            <div class="row g-3">
              @forelse($settings as $setting)
                @continue(in_array($setting->key, $skipKeys))
                <div class="col-12">
                  <div class="border rounded p-3 mb-2">
                    <div class="row g-3 align-items-start">
                      <div class="col-md-4">
                        <label class="form-label">Key</label>
                        <input type="text" class="form-control" value="{{ $setting->key }}" readonly>
                        <input type="hidden" name="settings[{{ $setting->key }}][key]" value="{{ $setting->key }}">
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="settings[{{ $setting->key }}][type]">
                          @php
                            $types = ['string','integer','float','boolean','array','json'];
                            $currentType = $setting->type ?? 'string';
                          @endphp
                          @foreach($types as $type)
                            <option value="{{ $type }}" @selected($currentType === $type)>{{ ucfirst($type) }}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-md-4">
                        <label class="form-label">Value</label>
                        @php $type = $setting->type ?? 'string'; @endphp
                        @if($type === 'boolean')
                          <select class="form-select" name="settings[{{ $setting->key }}][value]">
                            <option value="1" @selected((string)$setting->value === '1')>True</option>
                            <option value="0" @selected((string)$setting->value === '0')>False</option>
                          </select>
                        @elseif($type === 'integer')
                          <input type="number" step="1" class="form-control" name="settings[{{ $setting->key }}][value]" value="{{ old('settings.'.$setting->key.'.value', $setting->value) }}">
                        @elseif($type === 'float')
                          <input type="number" step="any" class="form-control" name="settings[{{ $setting->key }}][value]" value="{{ old('settings.'.$setting->key.'.value', $setting->value) }}">
                        @elseif($type === 'json' || $type === 'array')
                          <textarea class="form-control" rows="4" name="settings[{{ $setting->key }}][value]">{{ old('settings.'.$setting->key.'.value', is_string($setting->value) ? $setting->value : json_encode($setting->value, JSON_PRETTY_PRINT)) }}</textarea>
                        @else
                          <input type="text" class="form-control" name="settings[{{ $setting->key }}][value]" value="{{ old('settings.'.$setting->key.'.value', $setting->value) }}">
                        @endif
                      </div>

                      <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" name="settings[{{ $setting->key }}][description]">{{ old('settings.'.$setting->key.'.description', $setting->description) }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div class="col-12">
                  <p class="text-muted mb-0">No settings found.</p>
                </div>
              @endforelse
            </div>

            <div class="mt-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Reset</a>
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
            <span class="text-muted">Laravel {{ app()->version() }}</span>
          </div>
          <div class="mb-3">
            <strong>PHP Version:</strong><br>
            <span class="text-muted">{{ PHP_VERSION }}</span>
          </div>
          <div class="mb-3">
            <strong>Database Engine:</strong><br>
            <span class="text-muted">{{ strtoupper(config('database.default')) }} ({{ config('database.connections.' . config('database.default') . '.driver') }})</span>
          </div>
          <div class="mb-3">
            <strong>Database Name:</strong><br>
            <span class="text-muted">{{ config('database.connections.' . config('database.default') . '.database') ?: 'N/A' }}</span>
          </div>
          <div class="mb-3">
            <strong>Environment:</strong><br>
            <span class="badge {{ app()->environment('production') ? 'bg-success' : 'bg-warning' }}">
              {{ strtoupper(app()->environment()) }}
            </span>
          </div>
          <div class="mb-3">
            <strong>Last Updated:</strong><br>
            <span class="text-muted">{{ date('d M Y H:i:s') }}</span>
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
            <button type="button" class="btn btn-outline-primary btn-sm" data-onclick="clearCache()">
              <i class="fas fa-broom me-2"></i>
              Clear Cache
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" data-onclick="optimizeApp()">
              <i class="fas fa-rocket me-2"></i>
              Optimize App
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" data-onclick="backupDatabase()">
              <i class="fas fa-database me-2"></i>
              Backup Database
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Define routes for admin settings
  window.adminRoutes = {
    clearCache: '{{ route("admin.settings.clear-cache") }}',
    optimize: '{{ route("admin.settings.optimize") }}',
    backup: '{{ route("admin.settings.backup") }}',
    update: '{{ route("admin.settings.update") }}'
  };
</script>
<script src="{{ asset('js/admin-settings.js') }}"></script>
@endpush
