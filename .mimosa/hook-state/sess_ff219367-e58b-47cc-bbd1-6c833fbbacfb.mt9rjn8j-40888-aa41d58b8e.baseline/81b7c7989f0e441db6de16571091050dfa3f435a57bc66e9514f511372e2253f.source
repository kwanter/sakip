@props(['metrics' => [], 'charts' => [], 'recentActivities' => [], 'notifications' => []])

<div class="sakip-dashboard">
    <!-- Metrics Cards -->
    @if(!empty($metrics))
    <div class="sakip-dashboard-metrics">
        @foreach($metrics as $metric)
        <div class="sakip-metric-card" data-metric-type="{{ $metric['type'] ?? 'default' }}">
            <div class="sakip-metric-icon">
                <i class="{{ $metric['icon'] ?? 'fas fa-chart-line' }}"></i>
            </div>
            <div class="sakip-metric-content">
                <div class="sakip-metric-value">{{ $metric['value'] ?? 0 }}</div>
                <div class="sakip-metric-label">{{ $metric['label'] ?? 'Metric' }}</div>
                @if(isset($metric['change']))
                <div class="sakip-metric-change {{ $metric['change']['positive'] ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $metric['change']['positive'] ? 'up' : 'down' }}"></i>
                    {{ $metric['change']['value'] ?? 0 }}%
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Charts Section -->
    @if(!empty($charts))
    <div class="sakip-dashboard-charts">
        @foreach($charts as $chart)
        <div class="sakip-chart-container" data-chart-type="{{ $chart['type'] ?? 'line' }}">
            <div class="sakip-chart-header">
                <h3 class="sakip-chart-title">{{ $chart['title'] ?? 'Chart' }}</h3>
                <div class="sakip-chart-actions">
                    @if(isset($chart['exportable']) && $chart['exportable'])
                    <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-chart-export="{{ $chart['id'] ?? 'chart_' . $loop->index }}">
                        <i class="fas fa-download"></i>
                    </button>
                    @endif
                    @if(isset($chart['refreshable']) && $chart['refreshable'])
                    <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-chart-refresh="{{ $chart['id'] ?? 'chart_' . $loop->index }}">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    @endif
                </div>
            </div>
            <div class="sakip-chart-content">
                <canvas id="{{ $chart['id'] ?? 'chart_' . $loop->index }}" data-chart-config="{{ json_encode($chart) }}"></canvas>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Recent Activities and Notifications -->
    <div class="sakip-dashboard-bottom">
        @if(!empty($recentActivities))
        <div class="sakip-dashboard-section">
            <div class="sakip-section-header">
                <h3 class="sakip-section-title">Recent Activities</h3>
                <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-activities-refresh>
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="sakip-activities-list">
                @foreach($recentActivities as $activity)
                <div class="sakip-activity-item" data-activity-type="{{ $activity['type'] ?? 'default' }}">
                    <div class="sakip-activity-icon">
                        <i class="{{ $activity['icon'] ?? 'fas fa-info-circle' }}"></i>
                    </div>
                    <div class="sakip-activity-content">
                        <div class="sakip-activity-title">{{ $activity['title'] ?? 'Activity' }}</div>
                        <div class="sakip-activity-description">{{ $activity['description'] ?? '' }}</div>
                        <div class="sakip-activity-time">{{ $activity['time'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($notifications))
        <div class="sakip-dashboard-section">
            <div class="sakip-section-header">
                <h3 class="sakip-section-title">Notifications</h3>
                <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-notifications-refresh>
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="sakip-notifications-list">
                @foreach($notifications as $notification)
                <div class="sakip-notification-item {{ $notification['unread'] ? 'unread' : '' }}" data-notification-id="{{ $notification['id'] ?? $loop->index }}">
                    <div class="sakip-notification-icon">
                        <i class="{{ $notification['icon'] ?? 'fas fa-bell' }}"></i>
                    </div>
                    <div class="sakip-notification-content">
                        <div class="sakip-notification-title">{{ $notification['title'] ?? 'Notification' }}</div>
                        <div class="sakip-notification-message">{{ $notification['message'] ?? '' }}</div>
                        <div class="sakip-notification-time">{{ $notification['time'] ?? '' }}</div>
                    </div>
                    @if($notification['unread'])
                    <button type="button" class="sakip-notification-mark-read" data-notification-mark-read="{{ $notification['id'] ?? $loop->index }}">
                        <i class="fas fa-check"></i>
                    </button>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SAKIP_DASHBOARD !== 'undefined') {
        SAKIP_DASHBOARD.init();
    }
});
</script>
@endpush