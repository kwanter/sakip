@props([
    'title' => '',
    'value' => 0,
    'icon' => 'fa-chart-line',
    'trend' => null,
    'trendValue' => null,
    'color' => 'primary',
    'href' => null,
])

@php
$colorClasses = [
    'primary' => 'stat-icon primary',
    'success' => 'stat-icon success',
    'warning' => 'stat-icon warning',
    'danger' => 'stat-icon danger',
];

$trendClasses = [
    'up' => 'stat-trend up',
    'down' => 'stat-trend down',
    'neutral' => 'stat-trend neutral',
];

$iconClass = $colorClasses[$color] ?? $colorClasses['primary'];
$trendClass = $trendClasses[$trend] ?? '';
@endphp

<div class="stat-card" {{ $href ? 'onclick="window.location.href=\'' . $href . '\'" style="cursor:pointer"' : '' }}>
    <div class="stat-card-header">
        <div class="{{ $iconClass }}">
            <i class="fas fa-{{ $icon }}"></i>
        </div>
        @if($trend && $trendValue)
        <div class="{{ $trendClass }}">
            <i class="fas fa-{{ $trend === 'up' ? 'arrow-up' : ($trend === 'down' ? 'arrow-down' : 'minus') }}"></i>
            <span>{{ $trendValue }}</span>
        </div>
        @endif
    </div>
    <div class="stat-value">{{ number_format($value, 0, ',', '.') }}</div>
    <div class="stat-label">{{ $title }}</div>
</div>
