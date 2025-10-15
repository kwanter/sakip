<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SAKIP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
    |
    */

    'app' => [
        'name' => env('SAKIP_APP_NAME', 'SAKIP'),
        'version' => env('SAKIP_VERSION', '1.0.0'),
        'institution_name' => env('SAKIP_INSTITUTION_NAME', 'Instansi Pemerintah'),
        'institution_code' => env('SAKIP_INSTITUTION_CODE', '000'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assessment Configuration
    |--------------------------------------------------------------------------
    */

    'assessment' => [
        'scoring' => [
            'min_score' => 0,
            'max_score' => 100,
            'passing_score' => 60,
            'weight_distribution' => [
                'planning' => 15,
                'implementation' => 35,
                'monitoring' => 25,
                'evaluation' => 25,
            ],
        ],
        'grading' => [
            'A' => ['min' => 90, 'max' => 100, 'color' => 'success'],
            'B' => ['min' => 80, 'max' => 89, 'color' => 'info'],
            'C' => ['min' => 70, 'max' => 79, 'color' => 'warning'],
            'D' => ['min' => 60, 'max' => 69, 'color' => 'orange'],
            'E' => ['min' => 0, 'max' => 59, 'color' => 'danger'],
        ],
        'frequency' => [
            'quarterly' => 'Triwulan',
            'semester' => 'Semester',
            'annual' => 'Tahunan',
        ],
        'status' => [
            'draft' => 'Draft',
            'submitted' => 'Dikirim',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            'approved' => 'Disetujui',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Indicators Configuration
    |--------------------------------------------------------------------------
    */

    'performance_indicators' => [
        'categories' => [
            'outcome' => 'Outcome',
            'output' => 'Output',
            'input' => 'Input',
            'process' => 'Proses',
        ],
        'measurement_units' => [
            'percentage' => 'Persentase (%)',
            'number' => 'Angka',
            'ratio' => 'Rasio',
            'time' => 'Waktu',
            'cost' => 'Biaya',
            'quantity' => 'Kuantitas',
            'quality' => 'Kualitas',
        ],
        'frequencies' => [
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'semester' => 'Semester',
            'annual' => 'Tahunan',
        ],
        'collection_methods' => [
            'manual' => 'Manual',
            'automatic' => 'Otomatis',
            'survey' => 'Survei',
            'interview' => 'Wawancara',
        ],
        'data_sources' => [
            'internal' => 'Internal',
            'external' => 'Eksternal',
            'third_party' => 'Pihak Ketiga',
            'survey' => 'Survei',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Configuration
    |--------------------------------------------------------------------------
    */

    'reports' => [
        'types' => [
            'performance' => 'Laporan Kinerja',
            'compliance' => 'Laporan Kepatuhan',
            'assessment' => 'Laporan Penilaian',
            'audit' => 'Laporan Audit',
            'summary' => 'Ringkasan Kinerja',
        ],
        'formats' => [
            'pdf' => 'PDF',
            'excel' => 'Excel',
            'word' => 'Word',
        ],
        'periods' => [
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'semester' => 'Semester',
            'annual' => 'Tahunan',
            'custom' => 'Kustom',
        ],
        'templates' => [
            'standard' => 'Standar',
            'detailed' => 'Detail',
            'executive' => 'Eksekutif',
            'technical' => 'Teknis',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */

    'file_upload' => [
        'max_size' => env('SAKIP_MAX_FILE_SIZE', 10 * 1024 * 1024), // 10MB
        'allowed_types' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'
        ],
        'evidence_path' => 'sakip/evidence',
        'report_path' => 'sakip/reports',
        'temp_path' => 'sakip/temp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'channels' => ['database', 'mail', 'broadcast'],
        'types' => [
            'assessment_submitted' => 'Penilaian Dikirim',
            'assessment_verified' => 'Penilaian Diverifikasi',
            'assessment_rejected' => 'Penilaian Ditolak',
            'assessment_approved' => 'Penilaian Disetujui',
            'data_submitted' => 'Data Dikirim',
            'data_verified' => 'Data Diverifikasi',
            'deadline_reminder' => 'Pengingat Tenggat',
            'report_generated' => 'Laporan Dihasilkan',
        ],
        'priorities' => [
            'low' => 'Rendah',
            'medium' => 'Menengah',
            'high' => 'Tinggi',
            'urgent' => 'Darurat',
        ],
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'retention_days' => 365,
        'log_types' => [
            'create' => 'Membuat',
            'update' => 'Memperbarui',
            'delete' => 'Menghapus',
            'view' => 'Melihat',
            'export' => 'Mengekspor',
            'import' => 'Mengimpor',
            'approve' => 'Menyetujui',
            'reject' => 'Menolak',
            'verify' => 'Memverifikasi',
        ],
        'modules' => [
            'assessment' => 'Penilaian',
            'performance_indicator' => 'Indikator Kinerja',
            'target' => 'Target',
            'performance_data' => 'Data Kinerja',
            'evidence' => 'Bukti',
            'report' => 'Laporan',
            'user' => 'Pengguna',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Compliance Configuration
    |--------------------------------------------------------------------------
    */

    'compliance' => [
        'status' => [
            'compliant' => 'Patuh',
            'non_compliant' => 'Tidak Patuh',
            'partially_compliant' => 'Sebagian Patuh',
            'not_applicable' => 'Tidak Berlaku',
            'pending' => 'Menunggu',
        ],
        'categories' => [
            'regulatory' => 'Regulasi',
            'policy' => 'Kebijakan',
            'procedure' => 'Prosedur',
            'standard' => 'Standar',
        ],
        'severity' => [
            'low' => 'Rendah',
            'medium' => 'Menengah',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Configuration
    |--------------------------------------------------------------------------
    */

    'charts' => [
        'colors' => [
            'primary' => '#007bff',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'info' => '#17a2b8',
            'secondary' => '#6c757d',
            'light' => '#f8f9fa',
            'dark' => '#343a40',
        ],
        'types' => [
            'line' => 'Garis',
            'bar' => 'Batang',
            'pie' => 'Pie',
            'doughnut' => 'Donat',
            'radar' => 'Radar',
            'polar' => 'Polar',
        ],
        'default_options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    'api' => [
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
        'rate_limit' => [
            'per_minute' => 60,
            'per_hour' => 1000,
        ],
        'cache' => [
            'ttl' => 3600, // 1 hour
            'tags' => ['sakip'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */

    'security' => [
        'encryption_key' => env('SAKIP_ENCRYPTION_KEY'),
        'allowed_ips' => explode(',', env('SAKIP_ALLOWED_IPS', '')),
        'session_timeout' => 1800, // 30 minutes
        'password_expiry_days' => 90,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
    ],
];