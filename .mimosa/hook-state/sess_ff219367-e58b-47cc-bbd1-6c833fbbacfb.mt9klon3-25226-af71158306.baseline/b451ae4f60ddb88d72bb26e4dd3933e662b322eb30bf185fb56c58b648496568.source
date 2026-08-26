<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Templates Configuration
    |--------------------------------------------------------------------------
    |
    | Template configurations for different types of SAKIP reports
    |
    */

    'templates' => [
        'performance_report' => [
            'title' => 'Laporan Kinerja',
            'description' => 'Laporan kinerja instansi pemerintah berdasarkan indikator-indikator utama',
            'sections' => [
                'executive_summary' => [
                    'title' => 'Ringkasan Eksekutif',
                    'enabled' => true,
                    'required' => true,
                ],
                'performance_indicators' => [
                    'title' => 'Capaian Indikator Kinerja',
                    'enabled' => true,
                    'required' => true,
                ],
                'target_achievement' => [
                    'title' => 'Pencapaian Target',
                    'enabled' => true,
                    'required' => true,
                ],
                'analysis' => [
                    'title' => 'Analisis Kinerja',
                    'enabled' => true,
                    'required' => false,
                ],
                'recommendations' => [
                    'title' => 'Rekomendasi',
                    'enabled' => true,
                    'required' => false,
                ],
            ],
            'charts' => [
                'achievement_chart' => true,
                'trend_chart' => true,
                'category_chart' => true,
            ],
            'format' => 'pdf',
        ],

        'compliance_report' => [
            'title' => 'Laporan Kepatuhan',
            'description' => 'Laporan kepatuhan terhadap regulasi dan standar',
            'sections' => [
                'compliance_summary' => [
                    'title' => 'Ringkasan Kepatuhan',
                    'enabled' => true,
                    'required' => true,
                ],
                'regulatory_compliance' => [
                    'title' => 'Kepatuhan Regulasi',
                    'enabled' => true,
                    'required' => true,
                ],
                'standard_compliance' => [
                    'title' => 'Kepatuhan Standar',
                    'enabled' => true,
                    'required' => true,
                ],
                'non_compliance_issues' => [
                    'title' => 'Isi Kepatuhan',
                    'enabled' => true,
                    'required' => true,
                ],
                'corrective_actions' => [
                    'title' => 'Tindakan Perbaikan',
                    'enabled' => true,
                    'required' => false,
                ],
            ],
            'charts' => [
                'compliance_chart' => true,
                'issue_chart' => true,
            ],
            'format' => 'pdf',
        ],

        'assessment_report' => [
            'title' => 'Laporan Penilaian',
            'description' => 'Laporan hasil penilaian kinerja instansi',
            'sections' => [
                'assessment_summary' => [
                    'title' => 'Ringkasan Penilaian',
                    'enabled' => true,
                    'required' => true,
                ],
                'scoring_results' => [
                    'title' => 'Hasil Penilaian',
                    'enabled' => true,
                    'required' => true,
                ],
                'criteria_analysis' => [
                    'title' => 'Analisis Kriteria',
                    'enabled' => true,
                    'required' => true,
                ],
                'strengths_weaknesses' => [
                    'title' => 'Kekuatan dan Kelemahan',
                    'enabled' => true,
                    'required' => true,
                ],
                'improvement_suggestions' => [
                    'title' => 'Saran Perbaikan',
                    'enabled' => true,
                    'required' => false,
                ],
            ],
            'charts' => [
                'score_chart' => true,
                'criteria_chart' => true,
                'comparison_chart' => true,
            ],
            'format' => 'pdf',
        ],

        'audit_report' => [
            'title' => 'Laporan Audit',
            'description' => 'Laporan hasil audit SAKIP',
            'sections' => [
                'audit_summary' => [
                    'title' => 'Ringkasan Audit',
                    'enabled' => true,
                    'required' => true,
                ],
                'audit_findings' => [
                    'title' => 'Temuan Audit',
                    'enabled' => true,
                    'required' => true,
                ],
                'recommendations' => [
                    'title' => 'Rekomendasi',
                    'enabled' => true,
                    'required' => true,
                ],
                'action_plan' => [
                    'title' => 'Rencana Tindakan',
                    'enabled' => true,
                    'required' => false,
                ],
            ],
            'charts' => [
                'finding_chart' => true,
                'severity_chart' => true,
            ],
            'format' => 'pdf',
        ],

        'executive_summary' => [
            'title' => 'Ringkasan Eksekutif',
            'description' => 'Ringkasan eksekutif kinerja instansi',
            'sections' => [
                'key_performance' => [
                    'title' => 'Kinerja Utama',
                    'enabled' => true,
                    'required' => true,
                ],
                'achievement_summary' => [
                    'title' => 'Ringkasan Pencapaian',
                    'enabled' => true,
                    'required' => true,
                ],
                'challenges' => [
                    'title' => 'Tantangan',
                    'enabled' => true,
                    'required' => false,
                ],
                'next_steps' => [
                    'title' => 'Langkah Selanjutnya',
                    'enabled' => true,
                    'required' => false,
                ],
            ],
            'charts' => [
                'summary_chart' => true,
            ],
            'format' => 'pdf',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assessment Criteria Templates
    |--------------------------------------------------------------------------
    */

    'assessment_criteria' => [
        'planning' => [
            'title' => 'Perencanaan',
            'weight' => 15,
            'sub_criteria' => [
                'vision_mission' => [
                    'title' => 'Visi dan Misi',
                    'weight' => 3,
                    'description' => 'Kesesuaian visi dan misi dengan tujuan organisasi',
                ],
                'strategic_planning' => [
                    'title' => 'Perencanaan Strategis',
                    'weight' => 4,
                    'description' => 'Kualitas perencanaan strategis dan roadmap',
                ],
                'performance_planning' => [
                    'title' => 'Perencanaan Kinerja',
                    'weight' => 4,
                    'description' => 'Penyusunan rencana kinerja yang jelas dan terukur',
                ],
                'resource_planning' => [
                    'title' => 'Perencanaan Sumber Daya',
                    'weight' => 4,
                    'description' => 'Perencanaan sumber daya yang efektif',
                ],
            ],
        ],

        'implementation' => [
            'title' => 'Pelaksanaan',
            'weight' => 35,
            'sub_criteria' => [
                'program_implementation' => [
                    'title' => 'Pelaksanaan Program',
                    'weight' => 10,
                    'description' => 'Efektivitas pelaksanaan program dan kegiatan',
                ],
                'resource_utilization' => [
                    'title' => 'Pemanfaatan Sumber Daya',
                    'weight' => 8,
                    'description' => 'Efisiensi pemanfaatan sumber daya',
                ],
                'process_efficiency' => [
                    'title' => 'Efisiensi Proses',
                    'weight' => 8,
                    'description' => 'Efisiensi proses kerja dan prosedur',
                ],
                'quality_management' => [
                    'title' => 'Manajemen Kualitas',
                    'weight' => 9,
                    'description' => 'Penerapan sistem manajemen kualitas',
                ],
            ],
        ],

        'monitoring' => [
            'title' => 'Pemantauan',
            'weight' => 25,
            'sub_criteria' => [
                'performance_monitoring' => [
                    'title' => 'Pemantauan Kinerja',
                    'weight' => 8,
                    'description' => 'Sistem pemantauan kinerja yang efektif',
                ],
                'data_collection' => [
                    'title' => 'Pengumpulan Data',
                    'weight' => 6,
                    'description' => 'Ketepatan dan kelengkapan pengumpulan data',
                ],
                'reporting' => [
                    'title' => 'Pelaporan',
                    'weight' => 6,
                    'description' => 'Ketepatan waktu dan kualitas laporan',
                ],
                'evaluation' => [
                    'title' => 'Evaluasi',
                    'weight' => 5,
                    'description' => 'Kualitas evaluasi dan analisis',
                ],
            ],
        ],

        'evaluation' => [
            'title' => 'Evaluasi',
            'weight' => 25,
            'sub_criteria' => [
                'performance_evaluation' => [
                    'title' => 'Evaluasi Kinerja',
                    'weight' => 8,
                    'description' => 'Kualitas evaluasi kinerja secara menyeluruh',
                ],
                'improvement_actions' => [
                    'title' => 'Tindakan Perbaikan',
                    'weight' => 7,
                    'description' => 'Tindak lanjut hasil evaluasi dan perbaikan',
                ],
                'lessons_learned' => [
                    'title' => 'Pelajaran yang Dipetik',
                    'weight' => 5,
                    'description' => 'Pemanfaatan pelajaran yang dipetik',
                ],
                'continuous_improvement' => [
                    'title' => 'Peningkatan Berkelanjutan',
                    'weight' => 5,
                    'description' => 'Penerapan prinsip peningkatan berkelanjutan',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Templates
    |--------------------------------------------------------------------------
    */

    'notification_templates' => [
        'assessment_submitted' => [
            'title' => 'Penilaian Baru Dikirim',
            'message' => 'Penilaian kinerja untuk {institution_name} telah dikirim dan menunggu verifikasi.',
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
            'priority' => 'medium',
        ],

        'assessment_verified' => [
            'title' => 'Penilaian Diverifikasi',
            'message' => 'Penilaian kinerja untuk {institution_name} telah diverifikasi oleh {verifier_name}.',
            'icon' => 'fas fa-check-double',
            'color' => 'info',
            'priority' => 'medium',
        ],

        'assessment_rejected' => [
            'title' => 'Penilaian Ditolak',
            'message' => 'Penilaian kinerja untuk {institution_name} ditolak. Alasan: {reason}',
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'priority' => 'high',
        ],

        'assessment_approved' => [
            'title' => 'Penilaian Disetujui',
            'message' => 'Penilaian kinerja untuk {institution_name} telah disetujui. Skor akhir: {final_score}',
            'icon' => 'fas fa-award',
            'color' => 'success',
            'priority' => 'medium',
        ],

        'data_submitted' => [
            'title' => 'Data Kinerja Baru',
            'message' => 'Data kinerja baru telah dikirim untuk indikator {indicator_name}.',
            'icon' => 'fas fa-database',
            'color' => 'success',
            'priority' => 'low',
        ],

        'data_verified' => [
            'title' => 'Data Kinerja Diverifikasi',
            'message' => 'Data kinerja untuk indikator {indicator_name} telah diverifikasi.',
            'icon' => 'fas fa-check',
            'color' => 'info',
            'priority' => 'low',
        ],

        'deadline_reminder' => [
            'title' => 'Pengingat Tenggat Waktu',
            'message' => 'Tenggat waktu untuk {task_name} akan berakhir pada {deadline_date}.',
            'icon' => 'fas fa-clock',
            'color' => 'warning',
            'priority' => 'high',
        ],

        'report_generated' => [
            'title' => 'Laporan Selesai Dihasilkan',
            'message' => 'Laporan {report_type} telah selesai dibuat dan siap untuk diunduh.',
            'icon' => 'fas fa-file-alt',
            'color' => 'success',
            'priority' => 'low',
        ],

        'compliance_issue' => [
            'title' => 'Isu Kepatuhan Terdeteksi',
            'message' => 'Isu kepatuhan baru terdeteksi: {issue_description}',
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'danger',
            'priority' => 'urgent',
        ],

        'audit_finding' => [
            'title' => 'Temuan Audit Baru',
            'message' => 'Temuan audit baru untuk {institution_name}: {finding_description}',
            'icon' => 'fas fa-search',
            'color' => 'warning',
            'priority' => 'high',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    */

    'email_templates' => [
        'assessment_notification' => [
            'subject' => 'Notifikasi Penilaian SAKIP',
            'template' => 'emails.assessment_notification',
            'variables' => ['institution_name', 'assessment_date', 'deadline_date'],
        ],

        'deadline_reminder' => [
            'subject' => 'Pengingat Tenggat Waktu SAKIP',
            'template' => 'emails.deadline_reminder',
            'variables' => ['task_name', 'deadline_date', 'remaining_days'],
        ],

        'report_ready' => [
            'subject' => 'Laporan SAKIP Siap Diunduh',
            'template' => 'emails.report_ready',
            'variables' => ['report_type', 'report_date', 'download_link'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chart Templates
    |--------------------------------------------------------------------------
    */

    'chart_templates' => [
        'achievement_chart' => [
            'type' => 'bar',
            'title' => 'Pencapaian Target',
            'description' => 'Grafik pencapaian target kinerja',
            'colors' => ['#28a745', '#ffc107', '#dc3545'],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'max' => 100,
                    ],
                ],
            ],
        ],

        'trend_chart' => [
            'type' => 'line',
            'title' => 'Tren Kinerja',
            'description' => 'Grafik tren kinerja per periode',
            'colors' => ['#007bff', '#17a2b8', '#6f42c1'],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ],

        'category_chart' => [
            'type' => 'doughnut',
            'title' => 'Kinerja Berdasarkan Kategori',
            'description' => 'Distribusi kinerja berdasarkan kategori indikator',
            'colors' => ['#28a745', '#ffc107', '#dc3545', '#17a2b8'],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ],
        ],

        'compliance_chart' => [
            'type' => 'pie',
            'title' => 'Status Kepatuhan',
            'description' => 'Distribusi status kepatuhan',
            'colors' => ['#28a745', '#dc3545', '#ffc107', '#6c757d'],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ],
        ],
    ],
];