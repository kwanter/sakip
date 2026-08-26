/**
 * SAKIP Notification Templates Configuration
 * Comprehensive notification message templates for government-style SAKIP module
 */

// ==========================================================================
// Notification Categories
// ==========================================================================

/**
 * Notification categories and types
 */
const NOTIFICATION_CATEGORIES = {
  SYSTEM: 'system',
  ASSESSMENT: 'assessment',
  REPORT: 'report',
  USER: 'user',
  INSTITUTION: 'institution',
  AUDIT: 'audit',
  ALERT: 'alert',
  REMINDER: 'reminder'
};

/**
 * Notification priorities
 */
const NOTIFICATION_PRIORITIES = {
  LOW: 'low',
  NORMAL: 'normal',
  HIGH: 'high',
  CRITICAL: 'critical'
};

/**
 * Notification channels
 */
const NOTIFICATION_CHANNELS = {
  IN_APP: 'in_app',
  EMAIL: 'email',
  SMS: 'sms',
  WEBHOOK: 'webhook',
  BROWSER: 'browser'
};

// ==========================================================================
// System Notification Templates
// ==========================================================================

/**
 * System notification templates
 */
const SYSTEM_NOTIFICATIONS = {
  SYSTEM_STARTUP: {
    id: 'system_startup',
    category: NOTIFICATION_CATEGORIES.SYSTEM,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Sistem SAKIP Dimulai',
      en: 'SAKIP System Started'
    },
    message: {
      id: 'Sistem SAKIP telah berhasil dimulai dan siap digunakan',
      en: 'SAKIP system has been successfully started and is ready for use'
    },
    template: {
      id: 'Sistem SAKIP telah dimulai pada {timestamp}',
      en: 'SAKIP system started at {timestamp}'
    },
    variables: ['timestamp'],
    actions: [
      {
        type: 'view_system_status',
        label: { id: 'Lihat Status', en: 'View Status' },
        url: '/admin/system-status'
      }
    ]
  },

  SYSTEM_MAINTENANCE: {
    id: 'system_maintenance',
    category: NOTIFICATION_CATEGORIES.SYSTEM,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Pemeliharaan Sistem',
      en: 'System Maintenance'
    },
    message: {
      id: 'Sistem akan dipelihara pada {start_time} hingga {end_time}',
      en: 'System will be under maintenance from {start_time} to {end_time}'
    },
    template: {
      id: 'Pemeliharaan sistem direncanakan pada {date} pukul {start_time} - {end_time}. {description}',
      en: 'System maintenance scheduled on {date} from {start_time} to {end_time}. {description}'
    },
    variables: ['date', 'start_time', 'end_time', 'description'],
    actions: [
      {
        type: 'view_schedule',
        label: { id: 'Lihat Jadwal', en: 'View Schedule' },
        url: '/admin/maintenance-schedule'
      }
    ]
  },

  SYSTEM_ERROR: {
    id: 'system_error',
    category: NOTIFICATION_CATEGORIES.SYSTEM,
    priority: NOTIFICATION_PRIORITIES.CRITICAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Kesalahan Sistem',
      en: 'System Error'
    },
    message: {
      id: 'Terjadi kesalahan pada sistem. Silakan hubungi administrator',
      en: 'System error occurred. Please contact administrator'
    },
    template: {
      id: 'Kesalahan sistem terdeteksi: {error_type}. Kode error: {error_code}. Silakan hubungi tim teknis.',
      en: 'System error detected: {error_type}. Error code: {error_code}. Please contact technical team.'
    },
    variables: ['error_type', 'error_code', 'timestamp'],
    actions: [
      {
        type: 'report_issue',
        label: { id: 'Laporkan Masalah', en: 'Report Issue' },
        url: '/support/report-issue'
      }
    ]
  },

  BACKUP_COMPLETED: {
    id: 'backup_completed',
    category: NOTIFICATION_CATEGORIES.SYSTEM,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Backup Selesai',
      en: 'Backup Completed'
    },
    message: {
      id: 'Backup data telah berhasil dilakukan',
      en: 'Data backup has been successfully completed'
    },
    template: {
      id: 'Backup data berhasil dilakukan pada {timestamp}. Ukuran backup: {size}',
      en: 'Data backup completed at {timestamp}. Backup size: {size}'
    },
    variables: ['timestamp', 'size'],
    actions: [
      {
        type: 'view_backup',
        label: { id: 'Lihat Backup', en: 'View Backup' },
        url: '/admin/backups'
      }
    ]
  }
};

// ==========================================================================
// Assessment Notification Templates
// ==========================================================================

/**
 * Assessment notification templates
 */
const ASSESSMENT_NOTIFICATIONS = {
  ASSESSMENT_ASSIGNED: {
    id: 'assessment_assigned',
    category: NOTIFICATION_CATEGORIES.ASSESSMENT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Penilaian Baru Ditugaskan',
      en: 'New Assessment Assigned'
    },
    message: {
      id: 'Anda telah ditugaskan untuk melakukan penilaian SAKIP',
      en: 'You have been assigned to conduct a SAKIP assessment'
    },
    template: {
      id: 'Anda ditugaskan untuk melakukan penilaian SAKIP untuk {institution_name} periode {assessment_period}. Batas waktu: {deadline}',
      en: 'You have been assigned to conduct SAKIP assessment for {institution_name} period {assessment_period}. Deadline: {deadline}'
    },
    variables: ['institution_name', 'assessment_period', 'deadline', 'assessment_type'],
    actions: [
      {
        type: 'view_assessment',
        label: { id: 'Lihat Penilaian', en: 'View Assessment' },
        url: '/assessments/{assessment_id}'
      },
      {
        type: 'start_assessment',
        label: { id: 'Mulai Penilaian', en: 'Start Assessment' },
        url: '/assessments/{assessment_id}/start'
      }
    ]
  },

  ASSESSMENT_SUBMITTED: {
    id: 'assessment_submitted',
    category: NOTIFICATION_CATEGORIES.ASSESSMENT,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Penilaian Disubmit',
      en: 'Assessment Submitted'
    },
    message: {
      id: 'Penilaian SAKIP telah berhasil disubmit',
      en: 'SAKIP assessment has been successfully submitted'
    },
    template: {
      id: 'Penilaian SAKIP untuk {institution_name} periode {assessment_period} telah disubmit oleh {submitter_name} pada {timestamp}',
      en: 'SAKIP assessment for {institution_name} period {assessment_period} has been submitted by {submitter_name} at {timestamp}'
    },
    variables: ['institution_name', 'assessment_period', 'submitter_name', 'timestamp', 'assessment_id'],
    actions: [
      {
        type: 'view_assessment',
        label: { id: 'Lihat Penilaian', en: 'View Assessment' },
        url: '/assessments/{assessment_id}'
      },
      {
        type: 'view_report',
        label: { id: 'Lihat Laporan', en: 'View Report' },
        url: '/reports/assessment/{assessment_id}'
      }
    ]
  },

  ASSESSMENT_APPROVED: {
    id: 'assessment_approved',
    category: NOTIFICATION_CATEGORIES.ASSESSMENT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Penilaian Disetujui',
      en: 'Assessment Approved'
    },
    message: {
      id: 'Penilaian SAKIP telah disetujui',
      en: 'SAKIP assessment has been approved'
    },
    template: {
      id: 'Penilaian SAKIP untuk {institution_name} periode {assessment_period} telah disetujui oleh {approver_name} pada {timestamp} dengan skor akhir {final_score}',
      en: 'SAKIP assessment for {institution_name} period {assessment_period} has been approved by {approver_name} at {timestamp} with final score {final_score}'
    },
    variables: ['institution_name', 'assessment_period', 'approver_name', 'timestamp', 'final_score', 'assessment_id'],
    actions: [
      {
        type: 'view_certificate',
        label: { id: 'Lihat Sertifikat', en: 'View Certificate' },
        url: '/assessments/{assessment_id}/certificate'
      },
      {
        type: 'view_report',
        label: { id: 'Lihat Laporan', en: 'View Report' },
        url: '/reports/assessment/{assessment_id}'
      }
    ]
  },

  ASSESSMENT_REJECTED: {
    id: 'assessment_rejected',
    category: NOTIFICATION_CATEGORIES.ASSESSMENT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Penilaian Ditolak',
      en: 'Assessment Rejected'
    },
    message: {
      id: 'Penilaian SAKIP ditolak dan perlu direvisi',
      en: 'SAKIP assessment rejected and needs revision'
    },
    template: {
      id: 'Penilaian SAKIP untuk {institution_name} periode {assessment_period} ditolak oleh {rejector_name}. Alasan: {rejection_reason}',
      en: 'SAKIP assessment for {institution_name} period {assessment_period} rejected by {rejector_name}. Reason: {rejection_reason}'
    },
    variables: ['institution_name', 'assessment_period', 'rejector_name', 'rejection_reason', 'assessment_id'],
    actions: [
      {
        type: 'revise_assessment',
        label: { id: 'Revisi Penilaian', en: 'Revise Assessment' },
        url: '/assessments/{assessment_id}/edit'
      },
      {
        type: 'contact_reviewer',
        label: { id: 'Hubungi Peninjau', en: 'Contact Reviewer' },
        url: '/messages/new?to={rejector_id}'
      }
    ]
  },

  ASSESSMENT_DEADLINE_APPROACHING: {
    id: 'assessment_deadline_approaching',
    category: NOTIFICATION_CATEGORIES.ASSESSMENT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Tenggat Waktu Mendekat',
      en: 'Deadline Approaching'
    },
    message: {
      id: 'Tenggat waktu penilaian segera berakhir',
      en: 'Assessment deadline is approaching'
    },
    template: {
      id: 'Tenggat waktu penilaian untuk {institution_name} akan berakhir dalam {days_remaining} hari ({deadline_date})',
      en: 'Assessment deadline for {institution_name} will expire in {days_remaining} days ({deadline_date})'
    },
    variables: ['institution_name', 'days_remaining', 'deadline_date', 'assessment_id'],
    actions: [
      {
        type: 'complete_assessment',
        label: { id: 'Selesaikan Penilaian', en: 'Complete Assessment' },
        url: '/assessments/{assessment_id}'
      },
      {
        type: 'request_extension',
        label: { id: 'Minta Perpanjangan', en: 'Request Extension' },
        url: '/assessments/{assessment_id}/extension-request'
      }
    ]
  }
};

// ==========================================================================
// Report Notification Templates
// ==========================================================================

/**
 * Report notification templates
 */
const REPORT_NOTIFICATIONS = {
  REPORT_GENERATED: {
    id: 'report_generated',
    category: NOTIFICATION_CATEGORIES.REPORT,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Laporan Selesai Dibuat',
      en: 'Report Generated'
    },
    message: {
      id: 'Laporan SAKIP telah selesai dibuat',
      en: 'SAKIP report has been generated'
    },
    template: {
      id: 'Laporan {report_type} untuk periode {report_period} telah selesai dibuat pada {timestamp}',
      en: '{report_type} report for period {report_period} has been generated at {timestamp}'
    },
    variables: ['report_type', 'report_period', 'timestamp', 'report_id'],
    actions: [
      {
        type: 'download_report',
        label: { id: 'Unduh Laporan', en: 'Download Report' },
        url: '/reports/{report_id}/download'
      },
      {
        type: 'view_report',
        label: { id: 'Lihat Laporan', en: 'View Report' },
        url: '/reports/{report_id}'
      }
    ]
  },

  REPORT_SCHEDULED: {
    id: 'report_scheduled',
    category: NOTIFICATION_CATEGORIES.REPORT,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Laporan Dijadwalkan',
      en: 'Report Scheduled'
    },
    message: {
      id: 'Laporan telah dijadwalkan untuk dibuat otomatis',
      en: 'Report has been scheduled for automatic generation'
    },
    template: {
      id: 'Laporan {report_type} telah dijadwalkan untuk dibuat otomatis setiap {schedule_frequency}',
      en: '{report_type} report has been scheduled for automatic generation every {schedule_frequency}'
    },
    variables: ['report_type', 'schedule_frequency', 'next_run_time'],
    actions: [
      {
        type: 'view_schedule',
        label: { id: 'Lihat Jadwal', en: 'View Schedule' },
        url: '/reports/schedule'
      }
    ]
  },

  REPORT_FAILED: {
    id: 'report_failed',
    category: NOTIFICATION_CATEGORIES.REPORT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Gagal Membuat Laporan',
      en: 'Report Generation Failed'
    },
    message: {
      id: 'Gagal membuat laporan. Silakan coba lagi',
      en: 'Failed to generate report. Please try again'
    },
    template: {
      id: 'Gagal membuat laporan {report_type} untuk periode {report_period}. Alasan: {error_message}',
      en: 'Failed to generate {report_type} report for period {report_period}. Reason: {error_message}'
    },
    variables: ['report_type', 'report_period', 'error_message', 'report_id'],
    actions: [
      {
        type: 'retry_generation',
        label: { id: 'Coba Lagi', en: 'Retry' },
        url: '/reports/{report_id}/retry'
      },
      {
        type: 'contact_support',
        label: { id: 'Hubungi Dukungan', en: 'Contact Support' },
        url: '/support/contact'
      }
    ]
  }
};

// ==========================================================================
// User Notification Templates
// ==========================================================================

/**
 * User notification templates
 */
const USER_NOTIFICATIONS = {
  USER_REGISTERED: {
    id: 'user_registered',
    category: NOTIFICATION_CATEGORIES.USER,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Pendaftaran Berhasil',
      en: 'Registration Successful'
    },
    message: {
      id: 'Akun Anda telah berhasil dibuat',
      en: 'Your account has been successfully created'
    },
    template: {
      id: 'Selamat datang di SAKIP, {user_name}! Akun Anda telah berhasil dibuat. Silakan login untuk memulai.',
      en: 'Welcome to SAKIP, {user_name}! Your account has been successfully created. Please login to get started.'
    },
    variables: ['user_name', 'user_email', 'login_url'],
    actions: [
      {
        type: 'login',
        label: { id: 'Login', en: 'Login' },
        url: '/login'
      },
      {
        type: 'complete_profile',
        label: { id: 'Lengkapi Profil', en: 'Complete Profile' },
        url: '/profile/complete'
      }
    ]
  },

  PASSWORD_RESET: {
    id: 'password_reset',
    category: NOTIFICATION_CATEGORIES.USER,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Reset Password',
      en: 'Password Reset'
    },
    message: {
      id: 'Permintaan reset password telah dikirim',
      en: 'Password reset request has been sent'
    },
    template: {
      id: 'Klik link berikut untuk reset password Anda: {reset_link}. Link akan kedaluwarsa dalam {expiry_hours} jam.',
      en: 'Click the following link to reset your password: {reset_link}. Link will expire in {expiry_hours} hours.'
    },
    variables: ['reset_link', 'expiry_hours', 'user_name'],
    actions: [
      {
        type: 'reset_password',
        label: { id: 'Reset Password', en: 'Reset Password' },
        url: '{reset_link}'
      }
    ]
  },

  PROFILE_UPDATED: {
    id: 'profile_updated',
    category: NOTIFICATION_CATEGORIES.USER,
    priority: NOTIFICATION_PRIORITIES.LOW,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Profil Diperbarui',
      en: 'Profile Updated'
    },
    message: {
      id: 'Profil Anda telah berhasil diperbarui',
      en: 'Your profile has been successfully updated'
    },
    template: {
      id: 'Profil Anda telah diperbarui pada {timestamp}',
      en: 'Your profile has been updated at {timestamp}'
    },
    variables: ['timestamp', 'updated_fields'],
    actions: [
      {
        type: 'view_profile',
        label: { id: 'Lihat Profil', en: 'View Profile' },
        url: '/profile'
      }
    ]
  },

  ROLE_CHANGED: {
    id: 'role_changed',
    category: NOTIFICATION_CATEGORIES.USER,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Peran Diubah',
      en: 'Role Changed'
    },
    message: {
      id: 'Peran Anda telah diubah',
      en: 'Your role has been changed'
    },
    template: {
      id: 'Peran Anda telah diubah dari {old_role} menjadi {new_role} oleh {changed_by} pada {timestamp}',
      en: 'Your role has been changed from {old_role} to {new_role} by {changed_by} at {timestamp}'
    },
    variables: ['old_role', 'new_role', 'changed_by', 'timestamp'],
    actions: [
      {
        type: 'view_permissions',
        label: { id: 'Lihat Hak Akses', en: 'View Permissions' },
        url: '/profile/permissions'
      }
    ]
  }
};

// ==========================================================================
// Institution Notification Templates
// ==========================================================================

/**
 * Institution notification templates
 */
const INSTITUTION_NOTIFICATIONS = {
  INSTITUTION_REGISTERED: {
    id: 'institution_registered',
    category: NOTIFICATION_CATEGORIES.INSTITUTION,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Institusi Terdaftar',
      en: 'Institution Registered'
    },
    message: {
      id: 'Institusi baru telah terdaftar dalam sistem',
      en: 'New institution has been registered in the system'
    },
    template: {
      id: 'Institusi {institution_name} ({institution_code}) telah terdaftar pada {timestamp} oleh {registrar_name}',
      en: 'Institution {institution_name} ({institution_code}) has been registered at {timestamp} by {registrar_name}'
    },
    variables: ['institution_name', 'institution_code', 'timestamp', 'registrar_name', 'institution_id'],
    actions: [
      {
        type: 'view_institution',
        label: { id: 'Lihat Institusi', en: 'View Institution' },
        url: '/institutions/{institution_id}'
      },
      {
        type: 'assign_assessor',
        label: { id: 'Tugaskan Penilai', en: 'Assign Assessor' },
        url: '/assessors/assign/{institution_id}'
      }
    ]
  },

  INSTITUTION_UPDATED: {
    id: 'institution_updated',
    category: NOTIFICATION_CATEGORIES.INSTITUTION,
    priority: NOTIFICATION_PRIORITIES.LOW,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Data Institusi Diperbarui',
      en: 'Institution Data Updated'
    },
    message: {
      id: 'Data institusi telah diperbarui',
      en: 'Institution data has been updated'
    },
    template: {
      id: 'Data institusi {institution_name} telah diperbarui oleh {updater_name} pada {timestamp}',
      en: 'Institution data for {institution_name} has been updated by {updater_name} at {timestamp}'
    },
    variables: ['institution_name', 'updater_name', 'timestamp', 'updated_fields', 'institution_id'],
    actions: [
      {
        type: 'view_changes',
        label: { id: 'Lihat Perubahan', en: 'View Changes' },
        url: '/institutions/{institution_id}/history'
      }
    ]
  }
};

// ==========================================================================
// Audit Notification Templates
// ==========================================================================

/**
 * Audit notification templates
 */
const AUDIT_NOTIFICATIONS = {
  AUDIT_TRAIL_CREATED: {
    id: 'audit_trail_created',
    category: NOTIFICATION_CATEGORIES.AUDIT,
    priority: NOTIFICATION_PRIORITIES.LOW,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Jejak Audit Dibuat',
      en: 'Audit Trail Created'
    },
    message: {
      id: 'Jejak audit telah dibuat untuk aktivitas ini',
      en: 'Audit trail has been created for this activity'
    },
    template: {
      id: 'Jejak audit dibuat untuk {activity_type} oleh {user_name} pada {timestamp}',
      en: 'Audit trail created for {activity_type} by {user_name} at {timestamp}'
    },
    variables: ['activity_type', 'user_name', 'timestamp', 'audit_id'],
    actions: [
      {
        type: 'view_audit',
        label: { id: 'Lihat Audit', en: 'View Audit' },
        url: '/audit/{audit_id}'
      }
    ]
  },

  SUSPICIOUS_ACTIVITY: {
    id: 'suspicious_activity',
    category: NOTIFICATION_CATEGORIES.AUDIT,
    priority: NOTIFICATION_PRIORITIES.CRITICAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Aktivitas Mencurigakan',
      en: 'Suspicious Activity'
    },
    message: {
      id: 'Aktivitas mencurigakan terdeteksi dalam sistem',
      en: 'Suspicious activity detected in the system'
    },
    template: {
      id: 'Aktivitas mencurigakan terdeteksi: {activity_description} oleh {user_name} pada {timestamp}',
      en: 'Suspicious activity detected: {activity_description} by {user_name} at {timestamp}'
    },
    variables: ['activity_description', 'user_name', 'timestamp', 'risk_level', 'audit_id'],
    actions: [
      {
        type: 'investigate',
        label: { id: 'Investigasi', en: 'Investigate' },
        url: '/audit/{audit_id}/investigate'
      },
      {
        type: 'block_user',
        label: { id: 'Blokir Pengguna', en: 'Block User' },
        url: '/users/{user_id}/block'
      }
    ]
  }
};

// ==========================================================================
// Alert Notification Templates
// ==========================================================================

/**
 * Alert notification templates
 */
const ALERT_NOTIFICATIONS = {
  PERFORMANCE_ALERT: {
    id: 'performance_alert',
    category: NOTIFICATION_CATEGORIES.ALERT,
    priority: NOTIFICATION_PRIORITIES.HIGH,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Peringatan Kinerja',
      en: 'Performance Alert'
    },
    message: {
      id: 'Kinerja sistem menunjukkan indikasi masalah',
      en: 'System performance shows signs of issues'
    },
    template: {
      id: 'Peringatan kinerja: {metric_name} mencapai {current_value} yang melebihi ambang batas {threshold_value}',
      en: 'Performance alert: {metric_name} reached {current_value} which exceeds threshold {threshold_value}'
    },
    variables: ['metric_name', 'current_value', 'threshold_value', 'alert_severity', 'timestamp'],
    actions: [
      {
        type: 'view_metrics',
        label: { id: 'Lihat Metrik', en: 'View Metrics' },
        url: '/admin/performance-metrics'
      },
      {
        type: 'view_logs',
        label: { id: 'Lihat Log', en: 'View Logs' },
        url: '/admin/system-logs'
      }
    ]
  },

  SECURITY_ALERT: {
    id: 'security_alert',
    category: NOTIFICATION_CATEGORIES.ALERT,
    priority: NOTIFICATION_PRIORITIES.CRITICAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Peringatan Keamanan',
      en: 'Security Alert'
    },
    message: {
      id: 'Ancaman keamanan terdeteksi dalam sistem',
      en: 'Security threat detected in the system'
    },
    template: {
      id: 'Peringatan keamanan: {threat_type} terdeteksi pada {timestamp}. Tingkat ancaman: {threat_level}',
      en: 'Security alert: {threat_type} detected at {timestamp}. Threat level: {threat_level}'
    },
    variables: ['threat_type', 'threat_level', 'timestamp', 'affected_systems'],
    actions: [
      {
        type: 'investigate_security',
        label: { id: 'Investigasi Keamanan', en: 'Investigate Security' },
        url: '/security/incidents/{incident_id}'
      },
      {
        type: 'emergency_response',
        label: { id: 'Respons Darurat', en: 'Emergency Response' },
        url: '/security/emergency'
      }
    ]
  }
};

// ==========================================================================
// Reminder Notification Templates
// ==========================================================================

/**
 * Reminder notification templates
 */
const REMINDER_NOTIFICATIONS = {
  ASSESSMENT_REMINDER: {
    id: 'assessment_reminder',
    category: NOTIFICATION_CATEGORIES.REMINDER,
    priority: NOTIFICATION_PRIORITIES.NORMAL,
    channels: [NOTIFICATION_CHANNELS.IN_APP, NOTIFICATION_CHANNELS.EMAIL],
    title: {
      id: 'Pengingat Penilaian',
      en: 'Assessment Reminder'
    },
    message: {
      id: 'Jangan lupa untuk menyelesaikan penilaian SAKIP',
      en: 'Don\'t forget to complete the SAKIP assessment'
    },
    template: {
      id: 'Pengingat: Anda memiliki penilaian SAKIP yang belum selesai untuk {institution_name} periode {assessment_period}. Tenggat waktu: {deadline}',
      en: 'Reminder: You have an incomplete SAKIP assessment for {institution_name} period {assessment_period}. Deadline: {deadline}'
    },
    variables: ['institution_name', 'assessment_period', 'deadline', 'days_remaining', 'assessment_id'],
    actions: [
      {
        type: 'continue_assessment',
        label: { id: 'Lanjutkan Penilaian', en: 'Continue Assessment' },
        url: '/assessments/{assessment_id}'
      }
    ]
  },

  REPORT_REMINDER: {
    id: 'report_reminder',
    category: NOTIFICATION_CATEGORIES.REMINDER,
    priority: NOTIFICATION_PRIORITIES.LOW,
    channels: [NOTIFICATION_CHANNELS.IN_APP],
    title: {
      id: 'Pengingat Laporan',
      en: 'Report Reminder'
    },
    message: {
      id: 'Jangan lupa untuk membuat laporan periodik',
      en: 'Don\'t forget to create periodic reports'
    },
    template: {
      id: 'Pengingat: Laporan {report_type} untuk periode {report_period} belum dibuat. Jadwal: {schedule_date}',
      en: 'Reminder: {report_type} report for period {report_period} has not been created. Schedule: {schedule_date}'
    },
    variables: ['report_type', 'report_period', 'schedule_date'],
    actions: [
      {
        type: 'create_report',
        label: { id: 'Buat Laporan', en: 'Create Report' },
        url: '/reports/create?type={report_type}&period={report_period}'
      }
    ]
  }
};

// ==========================================================================
// Template Processing Functions
// ==========================================================================

/**
 * Notification template processing utilities
 */
const NotificationTemplateUtils = {
  /**
   * Process notification template with variables
   * @param {Object} template - Notification template
   * @param {Object} variables - Template variables
   * @param {string} language - Language code (id/en)
   * @returns {Object} Processed notification
   */
  processTemplate: function(template, variables = {}, language = 'id') {
    if (!template) return null;
    
    const processed = {
      id: template.id,
      category: template.category,
      priority: template.priority,
      channels: template.channels,
      title: this.processText(template.title, variables, language),
      message: this.processText(template.message, variables, language),
      timestamp: new Date().toISOString(),
      read: false,
      actions: this.processActions(template.actions, variables)
    };
    
    return processed;
  },

  /**
   * Process text with variables
   * @param {Object} textObj - Text object with language keys
   * @param {Object} variables - Template variables
   * @param {string} language - Language code
   * @returns {string} Processed text
   */
  processText: function(textObj, variables, language) {
    if (!textObj) return '';
    
    const text = textObj[language] || textObj.id || textObj.en || '';
    return this.replaceVariables(text, variables);
  },

  /**
   * Replace variables in text
   * @param {string} text - Text with placeholders
   * @param {Object} variables - Variable values
   * @returns {string} Text with replaced variables
   */
  replaceVariables: function(text, variables) {
    if (!text || !variables) return text;
    
    let processedText = text;
    
    Object.keys(variables).forEach(key => {
      const placeholder = `{${key}}`;
      const value = variables[key] || '';
      processedText = processedText.replace(new RegExp(placeholder, 'g'), value);
    });
    
    return processedText;
  },

  /**
   * Process action URLs with variables
   * @param {Array} actions - Action configurations
   * @param {Object} variables - Template variables
   * @returns {Array} Processed actions
   */
  processActions: function(actions, variables) {
    if (!actions) return [];
    
    return actions.map(action => ({
      ...action,
      url: this.replaceVariables(action.url, variables),
      label: action.label
    }));
  },

  /**
   * Get notification template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Notification template
   */
  getTemplateById: function(templateId) {
    const allTemplates = {
      ...SYSTEM_NOTIFICATIONS,
      ...ASSESSMENT_NOTIFICATIONS,
      ...REPORT_NOTIFICATIONS,
      ...USER_NOTIFICATIONS,
      ...INSTITUTION_NOTIFICATIONS,
      ...AUDIT_NOTIFICATIONS,
      ...ALERT_NOTIFICATIONS,
      ...REMINDER_NOTIFICATIONS
    };
    
    return allTemplates[templateId] || null;
  },

  /**
   * Get templates by category
   * @param {string} category - Notification category
   * @returns {Array} Templates in category
   */
  getTemplatesByCategory: function(category) {
    const categoryTemplates = [];
    const allTemplates = {
      ...SYSTEM_NOTIFICATIONS,
      ...ASSESSMENT_NOTIFICATIONS,
      ...REPORT_NOTIFICATIONS,
      ...USER_NOTIFICATIONS,
      ...INSTITUTION_NOTIFICATIONS,
      ...AUDIT_NOTIFICATIONS,
      ...ALERT_NOTIFICATIONS,
      ...REMINDER_NOTIFICATIONS
    };
    
    Object.values(allTemplates).forEach(template => {
      if (template.category === category) {
        categoryTemplates.push(template);
      }
    });
    
    return categoryTemplates;
  },

  /**
   * Get templates by priority
   * @param {string} priority - Notification priority
   * @returns {Array} Templates with priority
   */
  getTemplatesByPriority: function(priority) {
    const priorityTemplates = [];
    const allTemplates = {
      ...SYSTEM_NOTIFICATIONS,
      ...ASSESSMENT_NOTIFICATIONS,
      ...REPORT_NOTIFICATIONS,
      ...USER_NOTIFICATIONS,
      ...INSTITUTION_NOTIFICATIONS,
      ...AUDIT_NOTIFICATIONS,
      ...ALERT_NOTIFICATIONS,
      ...REMINDER_NOTIFICATIONS
    };
    
    Object.values(allTemplates).forEach(template => {
      if (template.priority === priority) {
        priorityTemplates.push(template);
      }
    });
    
    return priorityTemplates;
  },

  /**
   * Validate template variables
   * @param {Object} template - Notification template
   * @param {Object} variables - Provided variables
   * @returns {Object} Validation result
   */
  validateTemplateVariables: function(template, variables) {
    if (!template || !template.variables) {
      return { valid: true, missing: [] };
    }
    
    const missing = [];
    
    template.variables.forEach(variable => {
      if (!variables.hasOwnProperty(variable)) {
        missing.push(variable);
      }
    });
    
    return {
      valid: missing.length === 0,
      missing
    };
  },

  /**
   * Create notification from template
   * @param {string} templateId - Template ID
   * @param {Object} variables - Template variables
   * @param {Object} options - Additional options
   * @returns {Object} Created notification
   */
  createNotification: function(templateId, variables = {}, options = {}) {
    const template = this.getTemplateById(templateId);
    if (!template) {
      throw new Error(`Template with ID '${templateId}' not found`);
    }
    
    // Validate required variables
    const validation = this.validateTemplateVariables(template, variables);
    if (!validation.valid) {
      throw new Error(`Missing required variables: ${validation.missing.join(', ')}`);
    }
    
    const language = options.language || 'id';
    const notification = this.processTemplate(template, variables, language);
    
    // Add additional properties
    notification.recipient_id = options.recipient_id;
    notification.sender_id = options.sender_id;
    notification.metadata = options.metadata || {};
    notification.expires_at = options.expires_at;
    notification.tags = options.tags || [];
    
    return notification;
  }
};

// ==========================================================================
// Export Notification Templates
// ==========================================================================

/**
 * Export all notification templates and utilities
 */
const SAKIP_NOTIFICATION_TEMPLATES = {
  CATEGORIES: NOTIFICATION_CATEGORIES,
  PRIORITIES: NOTIFICATION_PRIORITIES,
  CHANNELS: NOTIFICATION_CHANNELS,
  TEMPLATES: {
    SYSTEM: SYSTEM_NOTIFICATIONS,
    ASSESSMENT: ASSESSMENT_NOTIFICATIONS,
    REPORT: REPORT_NOTIFICATIONS,
    USER: USER_NOTIFICATIONS,
    INSTITUTION: INSTITUTION_NOTIFICATIONS,
    AUDIT: AUDIT_NOTIFICATIONS,
    ALERT: ALERT_NOTIFICATIONS,
    REMINDER: REMINDER_NOTIFICATIONS
  },
  UTILS: NotificationTemplateUtils
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_NOTIFICATION_TEMPLATES;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_NOTIFICATION_TEMPLATES;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.NOTIFICATION_TEMPLATES = SAKIP_NOTIFICATION_TEMPLATES;
}