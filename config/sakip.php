<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAKIP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the SAKIP (Sistem Akuntabilitas
    | Kinerja Instansi Pemerintah) system.
    |
    */

    'enabled' => env('SAKIP_ENABLED', true),

    'api' => [
        'prefix' => 'sakip/api',
        'middleware' => ['auth', 'api'],
        'rate_limit' => env('SAKIP_API_RATE_LIMIT', 60),
    ],

    'cache' => [
        'enabled' => env('SAKIP_CACHE_ENABLED', true),
        'ttl' => env('SAKIP_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'sakip',
    ],

    'dashboard' => [
        'default_period' => 'current_year',
        'chart_colors' => [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
            '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'
        ],
    ],

    'assessment' => [
        'scoring' => [
            'excellent' => ['min' => 90, 'max' => 100, 'label' => 'Sangat Baik'],
            'good' => ['min' => 80, 'max' => 89, 'label' => 'Baik'],
            'adequate' => ['min' => 70, 'max' => 79, 'label' => 'Cukup'],
            'poor' => ['min' => 60, 'max' => 69, 'label' => 'Kurang'],
            'very_poor' => ['min' => 0, 'max' => 59, 'label' => 'Sangat Kurang'],
        ],
    ],

    'indicators' => [
        'categories' => [
            'outcome' => 'Outcome',
            'output' => 'Output',
            'input' => 'Input',
            'process' => 'Process',
        ],
        'units' => [
            'percentage' => 'Persentase (%)',
            'number' => 'Angka',
            'rupiah' => 'Rupiah (Rp)',
            'unit' => 'Satuan',
            'time' => 'Waktu',
        ],
    ],

    'reports' => [
        'types' => [
            'quarterly' => 'Triwulan',
            'semester' => 'Semester',
            'annual' => 'Tahunan',
            'monthly' => 'Bulanan',
        ],
        'formats' => ['pdf', 'excel', 'word'],
    ],

    'notifications' => [
        'enabled' => env('SAKIP_NOTIFICATIONS_ENABLED', true),
        'channels' => ['database', 'mail'],
        'email' => [
            'from' => env('SAKIP_NOTIFICATION_EMAIL_FROM', env('MAIL_FROM_ADDRESS')),
            'subject_prefix' => env('SAKIP_NOTIFICATION_SUBJECT_PREFIX', '[SAKIP]'),
        ],
    ],

    'audit' => [
        'enabled' => env('SAKIP_AUDIT_ENABLED', true),
        'retention_days' => env('SAKIP_AUDIT_RETENTION_DAYS', 365),
        'log_types' => [
            'create', 'update', 'delete', 'view', 'export', 'import', 'login', 'logout'
        ],
    ],

    'file_upload' => [
        'max_size' => env('SAKIP_FILE_MAX_SIZE', 10240), // 10MB in KB
        'allowed_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'storage_path' => env('SAKIP_FILE_STORAGE_PATH', 'sakip/uploads'),
    ],

    'compliance' => [
        'warning_threshold' => env('SAKIP_COMPLIANCE_WARNING_THRESHOLD', 7), // days
        'critical_threshold' => env('SAKIP_COMPLIANCE_CRITICAL_THRESHOLD', 3), // days
    ],

    'roles' => [
        'superadmin' => 'Super Admin',
        'executive' => 'Executive',
        'data_collector' => 'Data Collector',
        'assessor' => 'Assessor',
        'auditor' => 'Auditor',
        'government_agency' => 'Government Agency',
    ],

    'permissions' => [
        'view_dashboard' => 'View Dashboard',
        'manage_indicators' => 'Manage Indicators',
        'manage_programs' => 'Manage Programs',
        'manage_activities' => 'Manage Activities',
        'manage_reports' => 'Manage Reports',
        'manage_assessments' => 'Manage Assessments',
        'manage_users' => 'Manage Users',
        'view_audit_logs' => 'View Audit Logs',
        'export_data' => 'Export Data',
        'import_data' => 'Import Data',
    ],
];