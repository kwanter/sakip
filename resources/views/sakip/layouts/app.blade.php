<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - SAKIP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SAKIP Configuration -->
    <script>
        window.SAKIP_CONFIG = {
            apiUrl: '{{ url("/api/sakip") }}',
            csrfToken: '{{ csrf_token() }}',
            user: @json(auth()->user() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'roles' => auth()->user()->roles->pluck('name'),
                'permissions' => auth()->user()->permissions->pluck('name'),
            ] : null),
            config: @json(app('sakip')->getConfiguration()),
            metadata: @json([
                'indicator_categories' => app('sakip')->getIndicatorCategories(),
                'indicator_units' => app('sakip')->getIndicatorUnits(),
                'report_types' => app('sakip')->getReportTypes(),
                'report_formats' => app('sakip')->getReportFormats(),
                'assessment_scoring' => app('sakip')->getAssessmentScoring(),
                'assessment_grading' => app('sakip')->getAssessmentGrading(),
                'notification_channels' => app('sakip')->getNotificationChannels(),
                'notification_types' => app('sakip')->getNotificationTypes(),
                'file_upload_settings' => app('sakip')->getFileUploadSettings(),
                'audit_log_types' => app('sakip')->getAuditLogTypes(),
            ]),
        };
    </script>
    
    <!-- Styles -->
    @livewireStyles
    
    <!-- Custom SAKIP Styles -->
    <style>
        :root {
            --sakip-primary: #1e40af;
            --sakip-primary-dark: #1e3a8a;
            --sakip-secondary: #0f172a;
            --sakip-accent: #f59e0b;
            --sakip-success: #059669;
            --sakip-warning: #d97706;
            --sakip-danger: #dc2626;
            --sakip-info: #0284c7;
        }
        
        .sakip-bg-primary { background-color: var(--sakip-primary); }
        .sakip-bg-primary-dark { background-color: var(--sakip-primary-dark); }
        .sakip-bg-secondary { background-color: var(--sakip-secondary); }
        .sakip-bg-accent { background-color: var(--sakip-accent); }
        .sakip-bg-success { background-color: var(--sakip-success); }
        .sakip-bg-warning { background-color: var(--sakip-warning); }
        .sakip-bg-danger { background-color: var(--sakip-danger); }
        .sakip-bg-info { background-color: var(--sakip-info); }
        
        .sakip-text-primary { color: var(--sakip-primary); }
        .sakip-text-primary-dark { color: var(--sakip-primary-dark); }
        .sakip-text-secondary { color: var(--sakip-secondary); }
        .sakip-text-accent { color: var(--sakip-accent); }
        .sakip-text-success { color: var(--sakip-success); }
        .sakip-text-warning { color: var(--sakip-warning); }
        .sakip-text-danger { color: var(--sakip-danger); }
        .sakip-text-info { color: var(--sakip-info); }
        
        .sakip-border-primary { border-color: var(--sakip-primary); }
        .sakip-border-accent { border-color: var(--sakip-accent); }
        .sakip-border-success { border-color: var(--sakip-success); }
        .sakip-border-warning { border-color: var(--sakip-warning); }
        .sakip-border-danger { border-color: var(--sakip-danger); }
        
        .sakip-hover-primary:hover { background-color: var(--sakip-primary-dark); }
        .sakip-hover-accent:hover { background-color: #f59e0b; opacity: 0.9; }
        
        .sakip-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sakip-shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .sakip-transition {
            transition: all 0.3s ease;
        }
        
        .sakip-focus:focus {
            outline: 2px solid var(--sakip-primary);
            outline-offset: 2px;
        }
        
        .sakip-loading {
            position: relative;
            overflow: hidden;
        }
        
        .sakip-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .sakip-print {
            display: none;
        }
        
        @media print {
            .sakip-no-print {
                display: none !important;
            }
            .sakip-print {
                display: block !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .sakip-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
        
        .sakip-card {
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .sakip-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .sakip-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
        }
        
        .sakip-btn:focus {
            outline: 2px solid var(--sakip-primary);
            outline-offset: 2px;
        }
        
        .sakip-btn-primary {
            background-color: var(--sakip-primary);
            color: white;
        }
        
        .sakip-btn-primary:hover {
            background-color: var(--sakip-primary-dark);
        }
        
        .sakip-btn-secondary {
            background-color: var(--sakip-secondary);
            color: white;
        }
        
        .sakip-btn-outline {
            background-color: transparent;
            border-color: var(--sakip-primary);
            color: var(--sakip-primary);
        }
        
        .sakip-btn-outline:hover {
            background-color: var(--sakip-primary);
            color: white;
        }
        
        .sakip-form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .sakip-form-input:focus {
            outline: none;
            border-color: var(--sakip-primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .sakip-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .sakip-badge-success {
            background-color: rgba(5, 150, 105, 0.1);
            color: var(--sakip-success);
        }
        
        .sakip-badge-warning {
            background-color: rgba(217, 119, 6, 0.1);
            color: var(--sakip-warning);
        }
        
        .sakip-badge-danger {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--sakip-danger);
        }
        
        .sakip-badge-info {
            background-color: rgba(2, 132, 199, 0.1);
            color: var(--sakip-info);
        }
        
        .sakip-badge-primary {
            background-color: rgba(30, 64, 175, 0.1);
            color: var(--sakip-primary);
        }
        
        .sakip-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .sakip-table th {
            background-color: var(--sakip-primary);
            color: white;
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .sakip-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .sakip-table tr:hover {
            background-color: #f9fafb;
        }
        
        .sakip-progress {
            width: 100%;
            height: 0.5rem;
            background-color: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
        }
        
        .sakip-progress-bar {
            height: 100%;
            background-color: var(--sakip-primary);
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
        
        .sakip-alert {
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid;
            margin-bottom: 1rem;
        }
        
        .sakip-alert-success {
            background-color: rgba(5, 150, 105, 0.1);
            border-color: var(--sakip-success);
            color: var(--sakip-success);
        }
        
        .sakip-alert-warning {
            background-color: rgba(217, 119, 6, 0.1);
            border-color: var(--sakip-warning);
            color: var(--sakip-warning);
        }
        
        .sakip-alert-danger {
            background-color: rgba(220, 38, 38, 0.1);
            border-color: var(--sakip-danger);
            color: var(--sakip-danger);
        }
        
        .sakip-alert-info {
            background-color: rgba(2, 132, 199, 0.1);
            border-color: var(--sakip-info);
            color: var(--sakip-info);
        }
        
        .sakip-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .sakip-modal-content {
            background: white;
            border-radius: 8px;
            max-width: 90vw;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .sakip-tooltip {
            position: relative;
            cursor: help;
        }
        
        .sakip-tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1f2937;
            color: white;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sakip-tooltip:hover::after {
            opacity: 1;
            visibility: visible;
        }
        
        .sakip-sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        .sakip-skeleton {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .sakip-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .sakip-slide-in {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        .sakip-bounce {
            animation: bounce 1s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
            40%, 43% { transform: translateY(-8px); }
            70% { transform: translateY(-4px); }
            90% { transform: translateY(-2px); }
        }
        
        .sakip-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .sakip-rotate {
            animation: rotate 1s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .sakip-scale {
            animation: scale 0.2s ease-out;
        }
        
        @keyframes scale {
            from { transform: scale(0.95); }
            to { transform: scale(1); }
        }
        
        .sakip-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .sakip-flip {
            animation: flip 0.6s ease-in-out;
        }
        
        @keyframes flip {
            from { transform: perspective(400px) rotateY(0); }
            to { transform: perspective(400px) rotateY(360deg); }
        }
        
        .sakip-zoom {
            animation: zoom 0.3s ease-out;
        }
        
        @keyframes zoom {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .sakip-blur {
            filter: blur(4px);
        }
        
        .sakip-brightness {
            filter: brightness(1.2);
        }
        
        .sakip-contrast {
            filter: contrast(1.2);
        }
        
        .sakip-grayscale {
            filter: grayscale(1);
        }
        
        .sakip-hue-rotate {
            filter: hue-rotate(180deg);
        }
        
        .sakip-invert {
            filter: invert(1);
        }
        
        .sakip-saturate {
            filter: saturate(1.5);
        }
        
        .sakip-sepia {
            filter: sepia(1);
        }
        
        .sakip-drop-shadow {
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        .sakip-backdrop-blur {
            backdrop-filter: blur(4px);
        }
        
        .sakip-backdrop-brightness {
            backdrop-filter: brightness(1.2);
        }
        
        .sakip-backdrop-contrast {
            backdrop-filter: contrast(1.2);
        }
        
        .sakip-backdrop-grayscale {
            backdrop-filter: grayscale(1);
        }
        
        .sakip-backdrop-hue-rotate {
            backdrop-filter: hue-rotate(180deg);
        }
        
        .sakip-backdrop-invert {
            backdrop-filter: invert(1);
        }
        
        .sakip-backdrop-saturate {
            backdrop-filter: saturate(1.5);
        }
        
        .sakip-backdrop-sepia {
            backdrop-filter: sepia(1);
        }
        
        .sakip-backdrop-blur-md {
            backdrop-filter: blur(8px);
        }
        
        .sakip-backdrop-blur-lg {
            backdrop-filter: blur(16px);
        }
        
        .sakip-backdrop-blur-xl {
            backdrop-filter: blur(24px);
        }
        
        .sakip-gradient-primary {
            background: linear-gradient(135deg, var(--sakip-primary) 0%, var(--sakip-primary-dark) 100%);
        }
        
        .sakip-gradient-secondary {
            background: linear-gradient(135deg, var(--sakip-secondary) 0%, #374151 100%);
        }
        
        .sakip-gradient-accent {
            background: linear-gradient(135deg, var(--sakip-accent) 0%, #f59e0b 100%);
        }
        
        .sakip-gradient-success {
            background: linear-gradient(135deg, var(--sakip-success) 0%, #047857 100%);
        }
        
        .sakip-gradient-warning {
            background: linear-gradient(135deg, var(--sakip-warning) 0%, #b45309 100%);
        }
        
        .sakip-gradient-danger {
            background: linear-gradient(135deg, var(--sakip-danger) 0%, #b91c1c 100%);
        }
        
        .sakip-gradient-info {
            background: linear-gradient(135deg, var(--sakip-info) 0%, #0369a1 100%);
        }
        
        .sakip-text-gradient {
            background: linear-gradient(135deg, var(--sakip-primary) 0%, var(--sakip-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sakip-border-gradient {
            border: 2px solid;
            border-image: linear-gradient(135deg, var(--sakip-primary) 0%, var(--sakip-accent) 100%) 1;
        }
        
        .sakip-ring-primary {
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .sakip-ring-accent {
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .sakip-ring-success {
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .sakip-ring-warning {
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.1);
        }
        
        .sakip-ring-danger {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .sakip-ring-info {
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
        
        .sakip-ring-offset {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-primary);
        }
        
        .sakip-ring-offset-accent {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-accent);
        }
        
        .sakip-ring-offset-success {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-success);
        }
        
        .sakip-ring-offset-warning {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-warning);
        }
        
        .sakip-ring-offset-danger {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-danger);
        }
        
        .sakip-ring-offset-info {
            box-shadow: 0 0 0 2px white, 0 0 0 4px var(--sakip-info);
        }
        
        .sakip-divide-primary > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-primary);
        }
        
        .sakip-divide-accent > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-accent);
        }
        
        .sakip-divide-success > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-success);
        }
        
        .sakip-divide-warning > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-warning);
        }
        
        .sakip-divide-danger > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-danger);
        }
        
        .sakip-divide-info > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--sakip-info);
        }
        
        .sakip-space-y > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1rem;
        }
        
        .sakip-space-x > :not([hidden]) ~ :not([hidden]) {
            margin-left: 1rem;
        }
        
        .sakip-space-y-2 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.5rem;
        }
        
        .sakip-space-x-2 > :not([hidden]) ~ :not([hidden]) {
            margin-left: 0.5rem;
        }
        
        .sakip-space-y-4 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1rem;
        }
        
        .sakip-space-x-4 > :not([hidden]) ~ :not([hidden]) {
            margin-left: 1rem;
        }
        
        .sakip-space-y-6 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1.5rem;
        }
        
        .sakip-space-x-6 > :not([hidden]) ~ :not([hidden]) {
            margin-left: 1.5rem;
        }
        
        .sakip-space-y-8 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 2rem;
        }
        
        .sakip-space-x-8 > :not([hidden]) ~ :not([hidden]) {
            margin-left: 2rem;
        }
        
        .sakip-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-container-sm {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-container-md {
            width: 100%;
            max-width: 768px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-container-lg {
            width: 100%;
            max-width: 1024px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-container-xl {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-container-2xl {
            width: 100%;
            max-width: 1536px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .sakip-grid {
            display: grid;
            gap: 1rem;
        }
        
        .sakip-grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-5 {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-6 {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-7 {
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-8 {
            grid-template-columns: repeat(8, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-9 {
            grid-template-columns: repeat(9, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-10 {
            grid-template-columns: repeat(10, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-11 {
            grid-template-columns: repeat(11, minmax(0, 1fr));
        }
        
        .sakip-grid-cols-12 {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
        
        .sakip-flex {
            display: flex;
        }
        
        .sakip-flex-col {
            flex-direction: column;
        }
        
        .sakip-flex-row {
            flex-direction: row;
        }
        
        .sakip-flex-wrap {
            flex-wrap: wrap;
        }
        
        .sakip-flex-nowrap {
            flex-wrap: nowrap;
        }
        
        .sakip-flex-1 {
            flex: 1 1 0%;
        }
        
        .sakip-flex-auto {
            flex: 1 1 auto;
        }
        
        .sakip-flex-initial {
            flex: 0 1 auto;
        }
        
        .sakip-flex-none {
            flex: none;
        }
        
        .sakip-items-start {
            align-items: flex-start;
        }
        
        .sakip-items-end {
            align-items: flex-end;
        }
        
        .sakip-items-center {
            align-items: center;
        }
        
        .sakip-items-baseline {
            align-items: baseline;
        }
        
        .sakip-items-stretch {
            align-items: stretch;
        }
        
        .sakip-justify-start {
            justify-content: flex-start;
        }
        
        .sakip-justify-end {
            justify-content: flex-end;
        }
        
        .sakip-justify-center {
            justify-content: center;
        }
        
        .sakip-justify-between {
            justify-content: space-between;
        }
        
        .sakip-justify-around {
            justify-content: space-around;
        }
        
        .sakip-justify-evenly {
            justify-content: space-evenly;
        }
        
        .sakip-text-left {
            text-align: left;
        }
        
        .sakip-text-center {
            text-align: center;
        }
        
        .sakip-text-right {
            text-align: right;
        }
        
        .sakip-text-justify {
            text-align: justify;
        }
        
        .sakip-text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        
        .sakip-text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        .sakip-text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }
        
        .sakip-text-lg {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }
        
        .sakip-text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }
        
        .sakip-text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        
        .sakip-text-3xl {
            font-size: 1.875rem;
            line-height: 2.25rem;
        }
        
        .sakip-text-4xl {
            font-size: 2.25rem;
            line-height: 2.5rem;
        }
        
        .sakip-text-5xl {
            font-size: 3rem;
            line-height: 1;
        }
        
        .sakip-font-thin {
            font-weight: 100;
        }
        
        .sakip-font-extralight {
            font-weight: 200;
        }
        
        .sakip-font-light {
            font-weight: 300;
        }
        
        .sakip-font-normal {
            font-weight: 400;
        }
        
        .sakip-font-medium {
            font-weight: 500;
        }
        
        .sakip-font-semibold {
            font-weight: 600;
        }
        
        .sakip-font-bold {
            font-weight: 700;
        }
        
        .sakip-font-extrabold {
            font-weight: 800;
        }
        
        .sakip-font-black {
            font-weight: 900;
        }
        
        .sakip-leading-none {
            line-height: 1;
        }
        
        .sakip-leading-tight {
            line-height: 1.25;
        }
        
        .sakip-leading-snug {
            line-height: 1.375;
        }
        
        .sakip-leading-normal {
            line-height: 1.5;
        }
        
        .sakip-leading-relaxed {
            line-height: 1.625;
        }
        
        .sakip-leading-loose {
            line-height: 2;
        }
        
        .sakip-tracking-tighter {
            letter-spacing: -0.05em;
        }
        
        .sakip-tracking-tight {
            letter-spacing: -0.025em;
        }
        
        .sakip-tracking-normal {
            letter-spacing: 0em;
        }
        
        .sakip-tracking-wide {
            letter-spacing: 0.025em;
        }
        
        .sakip-tracking-wider {
            letter-spacing: 0.05em;
        }
        
        .sakip-tracking-widest {
            letter-spacing: 0.1em;
        }
        
        .sakip-uppercase {
            text-transform: uppercase;
        }
        
        .sakip-lowercase {
            text-transform: lowercase;
        }
        
        .sakip-capitalize {
            text-transform: capitalize;
        }
        
        .sakip-normal-case {
            text-transform: none;
        }
        
        .sakip-italic {
            font-style: italic;
        }
        
        .sakip-not-italic {
            font-style: normal;
        }
        
        .sakip-underline {
            text-decoration-line: underline;
        }
        
        .sakip-overline {
            text-decoration-line: overline;
        }
        
        .sakip-line-through {
            text-decoration-line: line-through;
        }
        
        .sakip-no-underline {
            text-decoration-line: none;
        }
        
        .sakip-antialiased {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .sakip-subpixel-antialiased {
            -webkit-font-smoothing: auto;
            -moz-osx-font-smoothing: auto;
        }
        
        .sakip-w-0 {
            width: 0px;
        }
        
        .sakip-w-1 {
            width: 0.25rem;
        }
        
        .sakip-w-2 {
            width: 0.5rem;
        }
        
        .sakip-w-3 {
            width: 0.75rem;
        }
        
        .sakip-w-4 {
            width: 1rem;
        }
        
        .sakip-w-5 {
            width: 1.25rem;
        }
        
        .sakip-w-6 {
            width: 1.5rem;
        }
        
        .sakip-w-7 {
            width: 1.75rem;
        }
        
        .sakip-w-8 {
            width: 2rem;
        }
        
        .sakip-w-9 {
            width: 2.25rem;
        }
        
        .sakip-w-10 {
            width: 2.5rem;
        }
        
        .sakip-w-11 {
            width: 2.75rem;
        }
        
        .sakip-w-12 {
            width: 3rem;
        }
        
        .sakip-w-14 {
            width: 3.5rem;
        }
        
        .sakip-w-16 {
            width: 4rem;
        }
        
        .sakip-w-20 {
            width: 5rem;
        }
        
        .sakip-w-24 {
            width: 6rem;
        }
        
        .sakip-w-28 {
            width: 7rem;
        }
        
        .sakip-w-32 {
            width: 8rem;
        }
        
        .sakip-w-36 {
            width: 9rem;
        }
        
        .sakip-w-40 {
            width: 10rem;
        }
        
        .sakip-w-44 {
            width: 11rem;
        }
        
        .sakip-w-48 {
            width: 12rem;
        }
        
        .sakip-w-52 {
            width: 13rem;
        }
        
        .sakip-w-56 {
            width: 14rem;
        }
        
        .sakip-w-60 {
            width: 15rem;
        }
        
        .sakip-w-64 {
            width: 16rem;
        }
        
        .sakip-w-72 {
            width: 18rem;
        }
        
        .sakip-w-80 {
            width: 20rem;
        }
        
        .sakip-w-96 {
            width: 24rem;
        }
        
        .sakip-w-auto {
            width: auto;
        }
        
        .sakip-w-1\/2 {
            width: 50%;
        }
        
        .sakip-w-1\/3 {
            width: 33.333333%;
        }
        
        .sakip-w-2\/3 {
            width: 66.666667%;
        }
        
        .sakip-w-1\/4 {
            width: 25%;
        }
        
        .sakip-w-2\/4 {
            width: 50%;
        }
        
        .sakip-w-3\/4 {
            width: 75%;
        }
        
        .sakip-w-1\/5 {
            width: 20%;
        }
        
        .sakip-w-2\/5 {
            width: 40%;
        }
        
        .sakip-w-3\/5 {
            width: 60%;
        }
        
        .sakip-w-4\/5 {
            width: 80%;
        }
        
        .sakip-w-1\/6 {
            width: 16.666667%;
        }
        
        .sakip-w-2\/6 {
            width: 33.333333%;
        }
        
        .sakip-w-3\/6 {
            width: 50%;
        }
        
        .sakip-w-4\/6 {
            width: 66.666667%;
        }
        
        .sakip-w-5\/6 {
            width: 83.333333%;
        }
        
        .sakip-w-1\/12 {
            width: 8.333333%;
        }
        
        .sakip-w-2\/12 {
            width: 16.666667%;
        }
        
        .sakip-w-3\/12 {
            width: 25%;
        }
        
        .sakip-w-4\/12 {
            width: 33.333333%;
        }
        
        .sakip-w-5\/12 {
            width: 41.666667%;
        }
        
        .sakip-w-6\/12 {
            width: 50%;
        }
        
        .sakip-w-7\/12 {
            width: 58.333333%;
        }
        
        .sakip-w-8\/12 {
            width: 66.666667%;
        }
        
        .sakip-w-9\/12 {
            width: 75%;
        }
        
        .sakip-w-10\/12 {
            width: 83.333333%;
        }
        
        .sakip-w-11\/12 {
            width: 91.666667%;
        }
        
        .sakip-w-full {
            width: 100%;
        }
        
        .sakip-w-screen {
            width: 100vw;
        }
        
        .sakip-w-min {
            width: min-content;
        }
        
        .sakip-w-max {
            width: max-content;
        }
        
        .sakip-h-0 {
            height: 0px;
        }
        
        .sakip-h-1 {
            height: 0.25rem;
        }
        
        .sakip-h-2 {
            height: 0.5rem;
        }
        
        .sakip-h-3 {
            height: 0.75rem;
        }
        
        .sakip-h-4 {
            height: 1rem;
        }
        
        .sakip-h-5 {
            height: 1.25rem;
        }
        
        .sakip-h-6 {
            height: 1.5rem;
        }
        
        .sakip-h-7 {
            height: 1.75rem;
        }
        
        .sakip-h-8 {
            height: 2rem;
        }
        
        .sakip-h-9 {
            height: 2.25rem;
        }
        
        .sakip-h-10 {
            height: 2.5rem;
        }
        
        .sakip-h-11 {
            height: 2.75rem;
        }
        
        .sakip-h-12 {
            height: 3rem;
        }
        
        .sakip-h-14 {
            height: 3.5rem;
        }
        
        .sakip-h-16 {
            height: 4rem;
        }
        
        .sakip-h-20 {
            height: 5rem;
        }
        
        .sakip-h-24 {
            height: 6rem;
        }
        
        .sakip-h-28 {
            height: 7rem;
        }
        
        .sakip-h-32 {
            height: 8rem;
        }
        
        .sakip-h-36 {
            height: 9rem;
        }
        
        .sakip-h-40 {
            height: 10rem;
        }
        
        .sakip-h-44 {
            height: 11rem;
        }
        
        .sakip-h-48 {
            height: 12rem;
        }
        
        .sakip-h-52 {
            height: 13rem;
        }
        
        .sakip-h-56 {
            height: 14rem;
        }
        
        .sakip-h-60 {
            height: 15rem;
        }
        
        .sakip-h-64 {
            height: 16rem;
        }
        
        .sakip-h-72 {
            height: 18rem;
        }
        
        .sakip-h-80 {
            height: 20rem;
        }
        
        .sakip-h-96 {
            height: 24rem;
        }
        
        .sakip-h-auto {
            height: auto;
        }
        
        .sakip-h-1\/2 {
            height: 50%;
        }
        
        .sakip-h-1\/3 {
            height: 33.333333%;
        }
        
        .sakip-h-2\/3 {
            height: 66.666667%;
        }
        
        .sakip-h-1\/4 {
            height: 25%;
        }
        
        .sakip-h-2\/4 {
            height: 50%;
        }
        
        .sakip-h-3\/4 {
            height: 75%;
        }
        
        .sakip-h-1\/5 {
            height: 20%;
        }
        
        .sakip-h-2\/5 {
            height: 40%;
        }
        
        .sakip-h-3\/5 {
            height: 60%;
        }
        
        .sakip-h-4\/5 {
            height: 80%;
        }
        
        .sakip-h-1\/6 {
            height: 16.666667%;
        }
        
        .sakip-h-2\/6 {
            height: 33.333333%;
        }
        
        .sakip-h-3\/6 {
            height: 50%;
        }
        
        .sakip-h-4\/6 {
            height: 66.666667%;
        }
        
        .sakip-h-5\/6 {
            height: 83.333333%;
        }
        
        .sakip-h-full {
            height: 100%;
        }
        
        .sakip-h-screen {
            height: 100vh;
        }
        
        .sakip-min-h-0 {
            min-height: 0px;
        }
        
        .sakip-min-h-full {
            min-height: 100%;
        }
        
        .sakip-min-h-screen {
            min-height: 100vh;
        }
        
        .sakip-max-h-0 {
            max-height: 0px;
        }
        
        .sakip-max-h-full {
            max-height: 100%;
        }
        
        .sakip-max-h-screen {
            max-height: 100vh;
        }
        
        .sakip-m-0 {
            margin: 0px;
        }
        
        .sakip-m-1 {
            margin: 0.25rem;
        }
        
        .sakip-m-2 {
            margin: 0.5rem;
        }
        
        .sakip-m-3 {
            margin: 0.75rem;
        }
        
        .sakip-m-4 {
            margin: 1rem;
        }
        
        .sakip-m-5 {
            margin: 1.25rem;
        }
        
        .sakip-m-6 {
            margin: 1.5rem;
        }
        
        .sakip-m-7 {
            margin: 1.75rem;
        }
        
        .sakip-m-8 {
            margin: 2rem;
        }
        
        .sakip-m-9 {
            margin: 2.25rem;
        }
        
        .sakip-m-10 {
            margin: 2.5rem;
        }
        
        .sakip-m-11 {
            margin: 2.75rem;
        }
        
        .sakip-m-12 {
            margin: 3rem;
        }
        
        .sakip-m-14 {
            margin: 3.5rem;
        }
        
        .sakip-m-16 {
            margin: 4rem;
        }
        
        .sakip-m-20 {
            margin: 5rem;
        }
        
        .sakip-m-24 {
            margin: 6rem;
        }
        
        .sakip-m-28 {
            margin: 7rem;
        }
        
        .sakip-m-32 {
            margin: 8rem;
        }
        
        .sakip-m-36 {
            margin: 9rem;
        }
        
        .sakip-m-40 {
            margin: 10rem;
        }
        
        .sakip-m-44 {
            margin: 11rem;
        }
        
        .sakip-m-48 {
            margin: 12rem;
        }
        
        .sakip-m-52 {
            margin: 13rem;
        }
        
        .sakip-m-56 {
            margin: 14rem;
        }
        
        .sakip-m-60 {
            margin: 15rem;
        }
        
        .sakip-m-64 {
            margin: 16rem;
        }
        
        .sakip-m-72 {
            margin: 18rem;
        }
        
        .sakip-m-80 {
            margin: 20rem;
        }
        
        .sakip-m-96 {
            margin: 24rem;
        }
        
        .sakip-m-auto {
            margin: auto;
        }
        
        .sakip-m-px {
            margin: 1px;
        }
        
        .sakip--m-0 {
            margin: 0px;
        }
        
        .sakip--m-1 {
            margin: -0.25rem;
        }
        
        .sakip--m-2 {
            margin: -0.5rem;
        }
        
        .sakip--m-3 {
            margin: -0.75rem;
        }
        
        .sakip--m-4 {
            margin: -1rem;
        }
        
        .sakip--m-5 {
            margin: -1.25rem;
        }
        
        .sakip--m-6 {
            margin: -1.5rem;
        }
        
        .sakip--m-7 {
            margin: -1.75rem;
        }
        
        .sakip--m-8 {
            margin: -2rem;
        }
        
        .sakip--m-9 {
            margin: -2.25rem;
        }
        
        .sakip--m-10 {
            margin: -2.5rem;
        }
        
        .sakip--m-11 {
            margin: -2.75rem;
        }
        
        .sakip--m-12 {
            margin: -3rem;
        }
        
        .sakip--m-14 {
            margin: -3.5rem;
        }
        
        .sakip--m-16 {
            margin: -4rem;
        }
        
        .sakip--m-20 {
            margin: -5rem;
        }
        
        .sakip--m-24 {
            margin: -6rem;
        }
        
        .sakip--m-28 {
            margin: -7rem;
        }
        
        .sakip--m-32 {
            margin: -8rem;
        }
        
        .sakip--m-36 {
            margin: -9rem;
        }
        
        .sakip--m-40 {
            margin: -10rem;
        }
        
        .sakip--m-44 {
            margin: -11rem;
        }
        
        .sakip--m-48 {
            margin: -12rem;
        }
        
        .sakip--m-52 {
            margin: -13rem;
        }
        
        .sakip--m-56 {
            margin: -14rem;
        }
        
        .sakip--m-60 {
            margin: -15rem;
        }
        
        .sakip--m-64 {
            margin: -16rem;
        }
        
        .sakip--m-72 {
            margin: -18rem;
        }
        
        .sakip--m-80 {
            margin: -20rem;
        }
        
        .sakip--m-96 {
            margin: -24rem;
        }
        
        .sakip--m-px {
            margin: -1px;
        }
        
        .sakip-my-0 {
            margin-top: 0px;
            margin-bottom: 0px;
        }
        
        .sakip-my-1 {
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sakip-my-2 {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .sakip-my-3 {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .sakip-my-4 {
            margin-top: 1rem;
            margin-bottom: 1rem;
        }
        
        .sakip-my-5 {
            margin-top: 1.25rem;
            margin-bottom: 1.25rem;
        }
        
        .sakip-my-6 {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .sakip-my-7 {
            margin-top: 1.75rem;
            margin-bottom: 1.75rem;
        }
        
        .sakip-my-8 {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .sakip-my-9 {
            margin-top: 2.25rem;
            margin-bottom: 2.25rem;
        }
        
        .sakip-my-10 {
            margin-top: 2.5rem;
            margin-bottom: 2.5rem;
        }
        
        .sakip-my-11 {
            margin-top: 2.75rem;
            margin-bottom: 2.75rem;
        }
        
        .sakip-my-12 {
            margin-top: 3rem;
            margin-bottom: 3rem;
        }
        
        .sakip-my-14 {
            margin-top: 3.5rem;
            margin-bottom: 3.5rem;
        }
        
        .sakip-my-16 {
            margin-top: 4rem;
            margin-bottom: 4rem;
        }
        
        .sakip-my-20 {
            margin-top: 5rem;
            margin-bottom: 5rem;
        }
        
        .sakip-my-24 {
            margin-top: 6rem;
            margin-bottom: 6rem;
        }
        
        .sakip-my-28 {
            margin-top: 7rem;
            margin-bottom: 7rem;
        }
        
        .sakip-my-32 {
            margin-top: 8rem;
            margin-bottom: 8rem;
        }
        
        .sakip-my-36 {
            margin-top: 9rem;
            margin-bottom: 9rem;
        }
        
        .sakip-my-40 {
            margin-top: 10rem;
            margin-bottom: 10rem;
        }
        
        .sakip-my-44 {
            margin-top: 11rem;
            margin-bottom: 11rem;
        }
        
        .sakip-my-48 {
            margin-top: 12rem;
            margin-bottom: 12rem;
        }
        
        .sakip-my-52 {
            margin-top: 13rem;
            margin-bottom: 13rem;
        }
        
        .sakip-my-56 {
            margin-top: 14rem;
            margin-bottom: 14rem;
        }
        
        .sakip-my-60 {
            margin-top: 15rem;
            margin-bottom: 15rem;
        }
        
        .sakip-my-64 {
            margin-top: 16rem;
            margin-bottom: 16rem;
        }
        
        .sakip-my-72 {
            margin-top: 18rem;
            margin-bottom: 18rem;
        }
        
        .sakip-my-80 {
            margin-top: 20rem;
            margin-bottom: 20rem;
        }
        
        .sakip-my-96 {
            margin-top: 24rem;
            margin-bottom: 24rem;
        }
        
        .sakip-my-auto {
            margin-top: auto;
            margin-bottom: auto;
        }
        
        .sakip-my-px {
            margin-top: 1px;
            margin-bottom: 1px;
        }
        
        .sakip--my-0 {
            margin-top: 0px;
            margin-bottom: 0px;
        }
        
        .sakip--my-1 {
            margin-top: -0.25rem;
            margin-bottom: -0.25rem;
        }
        
        .sakip--my-2 {
            margin-top: -0.5rem;
            margin-bottom: -0.5rem;
        }
        
        .sakip--my-3 {
            margin-top: -0.75rem;
            margin-bottom: -0.75rem;
        }
        
        .sakip--my-4 {
            margin-top: -1rem;
            margin-bottom: -1rem;
        }
        
        .sakip--my-5 {
            margin-top: -1.25rem;
            margin-bottom: -1.25rem;
        }
        
        .sakip--my-6 {
            margin-top: -1.5rem;
            margin-bottom: -1.5rem;
        }
        
        .sakip--my-7 {
            margin-top: -1.75rem;
            margin-bottom: -1.75rem;
        }
        
        .sakip--my-8 {
            margin-top: -2rem;
            margin-bottom: -2rem;
        }
        
        .sakip--my-9 {
            margin-top: -2.25rem;
            margin-bottom: -2.25rem;
        }
        
        .sakip--my-10 {
            margin-top: -2.5rem;
            margin-bottom: -2.5rem;
        }
        
        .sakip--my-11 {
            margin-top: -2.75rem;
            margin-bottom: -2.75rem;
        }
        
        .sakip--my-12 {
            margin-top: -3rem;
            margin-bottom: -3rem;
        }
        
        .sakip--my-14 {
            margin-top: -3.5rem;
            margin-bottom: -3.5rem;
        }
        
        .sakip--my-16 {
            margin-top: -4rem;
            margin-bottom: -4rem;
        }
        
        .sakip--my-20 {
            margin-top: -5rem;
            margin-bottom: -5rem;
        }
        
        .sakip--my-24 {
            margin-top: -6rem;
            margin-bottom: -6rem;
        }
        
        .sakip--my-28 {
            margin-top: -7rem;
            margin-bottom: -7rem;
        }
        
        .sakip--my-32 {
            margin-top: -8rem;
            margin-bottom: -8rem;
        }
        
        .sakip--my-36 {
            margin-top: -9rem;
            margin-bottom: -9rem;
        }
        
        .sakip--my-40 {
            margin-top: -10rem;
            margin-bottom: -10rem;
        }
        
        .sakip--my-44 {
            margin-top: -11rem;
            margin-bottom: -11rem;
        }
        
        .sakip--my-48 {
            margin-top: -12rem;
            margin-bottom: -12rem;
        }
        
        .sakip--my-52 {
            margin-top: -13rem;
            margin-bottom: -13rem;
        }
        
        .sakip--my-56 {
            margin-top: -14rem;
            margin-bottom: -14rem;
        }
        
        .sakip--my-60 {
            margin-top: -15rem;
            margin-bottom: -15rem;
        }
        
        .sakip--my-64 {
            margin-top: -16rem;
            margin-bottom: -16rem;
        }
        
        .sakip--my-72 {
            margin-top: -18rem;
            margin-bottom: -18rem;
        }
        
        .sakip--my-80 {
            margin-top: -20rem;
            margin-bottom: -20rem;
        }
        
        .sakip--my-96 {
            margin-top: -24rem;
            margin-bottom: -24rem;
        }
        
        .sakip--my-px {
            margin-top: -1px;
            margin-bottom: -1px;
        }
        
        .sakip-mx-0 {
            margin-left: 0px;
            margin-right: 0px;
        }
        
        .sakip-mx-1 {
            margin-left: 0.25rem;
            margin-right: 0.25rem;
        }
        
        .sakip-mx-2 {
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }
        
        .sakip-mx-3 {
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }
        
        .sakip-mx-4 {
            margin-left: 1rem;
            margin-right: 1rem;
        }
        
        .sakip-mx-5 {
            margin-left: 1.25rem;
            margin-right: 1.25rem;
        }
        
        .sakip-mx-6 {
            margin-left: 1.5rem;
            margin-right: 1.5rem;
        }
        
        .sakip-mx-7 {
            margin-left: 1.75rem;
            margin-right: 1.75rem;
        }
        
        .sakip-mx-8 {
            margin-left: 2rem;
            margin-right: 2rem;
        }
    </style>
</head>
<body class="font-sans antialiased sakip-layout" data-theme="light">
    <div id="sakip-notification-container" class="sakip-notification-container"></div>
    
    <div class="min-h-screen bg-gray-100">
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>
    
    <!-- Stack for additional scripts -->
    @stack('scripts')
</body>
</html>