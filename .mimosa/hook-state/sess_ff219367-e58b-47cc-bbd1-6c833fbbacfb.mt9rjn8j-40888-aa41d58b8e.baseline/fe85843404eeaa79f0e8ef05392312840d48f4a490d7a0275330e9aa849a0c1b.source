/**
 * SAKIP Constants and Enums Configuration
 * Government-style constants for SAKIP module
 */

// ==========================================================================
// Application Constants
// ==========================================================================

/**
 * Application metadata and configuration
 */
const SAKIP_CONSTANTS = {
  // Application Information
  APP_NAME: 'Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)',
  APP_SHORT_NAME: 'SAKIP',
  APP_VERSION: '1.0.0',
  APP_BUILD: '2025.01.001',
  APP_COPYRIGHT: '© 2025 Pengadilan Negeri Tanah Grogot',

  // Environment
  ENVIRONMENT: {
    DEVELOPMENT: 'development',
    STAGING: 'staging',
    PRODUCTION: 'production',
    TESTING: 'testing'
  },

  // API Configuration
  API: {
    TIMEOUT: 30000, // 30 seconds
    RETRY_ATTEMPTS: 3,
    RETRY_DELAY: 1000, // 1 second
    CACHE_DURATION: 300000, // 5 minutes
    MAX_CONCURRENT_REQUESTS: 5,
    RATE_LIMIT_DELAY: 100 // 100ms between requests
  },

  // Pagination
  PAGINATION: {
    DEFAULT_PAGE_SIZE: 10,
    PAGE_SIZE_OPTIONS: [10, 25, 50, 100],
    MAX_PAGE_SIZE: 100,
    SHOW_SIZE_CHANGER: true,
    SHOW_QUICK_JUMPER: true,
    SHOW_TOTAL: true
  },

  // File Upload
  FILE_UPLOAD: {
    MAX_FILE_SIZE: 10485760, // 10MB in bytes
    CHUNK_SIZE: 1048576, // 1MB in bytes
    MAX_CONCURRENT_UPLOADS: 3,
    ALLOWED_EXTENSIONS: [
      'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
      'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg',
      'zip', 'rar', '7z', 'tar', 'gz',
      'txt', 'csv', 'xml', 'json'
    ],
    ALLOWED_MIME_TYPES: [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-powerpoint',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/bmp',
      'image/svg+xml',
      'application/zip',
      'application/x-rar-compressed',
      'application/x-7z-compressed',
      'text/plain',
      'text/csv',
      'application/xml',
      'application/json'
    ]
  },

  // Date and Time
  DATE_TIME: {
    DEFAULT_FORMAT: 'DD/MM/YYYY',
    TIME_FORMAT: 'HH:mm',
    DATE_TIME_FORMAT: 'DD/MM/YYYY HH:mm',
    MONTH_YEAR_FORMAT: 'MM/YYYY',
    YEAR_ONLY_FORMAT: 'YYYY',
    LOCALE: 'id-ID',
    TIMEZONE: 'Asia/Jakarta'
  },

  // Currency and Numbers
  CURRENCY: {
    SYMBOL: 'Rp',
    CODE: 'IDR',
    DECIMAL_PLACES: 0,
    THOUSAND_SEPARATOR: '.',
    DECIMAL_SEPARATOR: ',',
    CURRENCY_POSITION: 'before' // before or after
  },

  // Language
  LANGUAGE: {
    DEFAULT: 'id',
    AVAILABLE: ['id', 'en'],
    FALLBACK: 'id'
  }
};

// ==========================================================================
// Assessment Constants
// ==========================================================================

/**
 * Assessment-related constants
 */
const ASSESSMENT_CONSTANTS = {
  // Assessment Types
  TYPES: {
    RENSTRA: 'renstra',
    RENJA: 'renja',
    PK: 'pk',
    IKU: 'iku',
    IKK: 'ikk',
    RISK: 'risk',
    FINANCIAL: 'financial',
    PERFORMANCE: 'performance',
    COMPLIANCE: 'compliance'
  },

  // Assessment Status
  STATUS: {
    DRAFT: 'draft',
    SUBMITTED: 'submitted',
    IN_REVIEW: 'in_review',
    APPROVED: 'approved',
    REJECTED: 'rejected',
    REVISED: 'revised',
    FINAL: 'final',
    ARCHIVED: 'archived'
  },

  // Achievement Levels (Predikat)
  ACHIEVEMENT_LEVELS: {
    EXCELLENT: { code: 'SANGAT_BAIK', label: 'Sangat Baik', min_score: 90, color: '#22c55e' },
    GOOD: { code: 'BAIK', label: 'Baik', min_score: 70, color: '#3b82f6' },
    ADEQUATE: { code: 'CUKUP', label: 'Cukup', min_score: 50, color: '#f59e0b' },
    POOR: { code: 'KURANG', label: 'Kurang', min_score: 30, color: '#ef4444' },
    VERY_POOR: { code: 'SANGAT_KURANG', label: 'Sangat Kurang', min_score: 0, color: '#dc2626' }
  },

  // Scoring Methods
  SCORING_METHODS: {
    MANUAL: 'manual',
    AUTOMATIC: 'automatic',
    FORMULA: 'formula',
    WEIGHTED: 'weighted'
  },

  // Evidence Types
  EVIDENCE_TYPES: {
    DOCUMENT: 'document',
    IMAGE: 'image',
    VIDEO: 'video',
    AUDIO: 'audio',
    LINK: 'link',
    DATA: 'data'
  },

  // Weight Categories
  WEIGHT_CATEGORIES: {
    STRATEGIC: 40,
    OPERATIONAL: 30,
    SUPPORT: 20,
    OTHER: 10
  },

  // Maximum Scores
  MAX_SCORES: {
    INDICATOR: 100,
    SUB_INDICATOR: 100,
    EVIDENCE: 100,
    OVERALL: 100
  }
};

// ==========================================================================
// User and Role Constants
// ==========================================================================

/**
 * User and role management constants
 */
const USER_CONSTANTS = {
  // User Types
  TYPES: {
    ADMIN: 'admin',
    SUPER_ADMIN: 'super_admin',
    ASSESSOR: 'assessor',
    ASSESSEE: 'assessee',
    REVIEWER: 'reviewer',
    APPROVER: 'approver',
    AUDITOR: 'auditor',
    REPORTER: 'reporter',
    GUEST: 'guest'
  },

  // User Status
  STATUS: {
    ACTIVE: 'active',
    INACTIVE: 'inactive',
    SUSPENDED: 'suspended',
    PENDING: 'pending',
    LOCKED: 'locked',
    DELETED: 'deleted'
  },

  // Permission Levels
  PERMISSIONS: {
    VIEW: 'view',
    CREATE: 'create',
    EDIT: 'edit',
    DELETE: 'delete',
    APPROVE: 'approve',
    REVIEW: 'review',
    AUDIT: 'audit',
    EXPORT: 'export',
    IMPORT: 'import',
    CONFIGURE: 'configure'
  },

  // Session Configuration
  SESSION: {
    TIMEOUT: 1800000, // 30 minutes in milliseconds
    WARNING_TIME: 300000, // 5 minutes before timeout
    MAX_CONCURRENT_SESSIONS: 3,
    REMEMBER_ME_DURATION: 2592000000 // 30 days in milliseconds
  },

  // Authentication
  AUTH: {
    MAX_LOGIN_ATTEMPTS: 5,
    LOCKOUT_DURATION: 900000, // 15 minutes
    PASSWORD_MIN_LENGTH: 8,
    PASSWORD_REQUIREMENTS: {
      UPPERCASE: true,
      LOWERCASE: true,
      NUMBERS: true,
      SPECIAL_CHARS: true,
      MIN_LENGTH: 8,
      MAX_LENGTH: 128
    }
  }
};

// ==========================================================================
// Institution and Organization Constants
// ==========================================================================

/**
 * Government institution constants
 */
const INSTITUTION_CONSTANTS = {
  // Institution Types
  TYPES: {
    MINISTRY: 'ministry',
    AGENCY: 'agency',
    DEPARTMENT: 'department',
    UNIT: 'unit',
    REGIONAL: 'regional',
    LOCAL: 'local',
    STATE_OWNED: 'state_owned',
    COMMISSION: 'commission',
    BOARD: 'board',
    SECRETARIAT: 'secretariat'
  },

  // Institution Levels
  LEVELS: {
    CENTRAL: 'central',
    PROVINCIAL: 'provincial',
    REGENCY: 'regency',
    DISTRICT: 'district',
    VILLAGE: 'village'
  },

  // Institution Status
  STATUS: {
    ACTIVE: 'active',
    INACTIVE: 'inactive',
    MERGED: 'merged',
    DISSOLVED: 'dissolved',
    RESTRUCTURED: 'restructured'
  },

  // Indonesian Government Structure
  INDONESIAN_GOVERNMENT: {
    MINISTRIES: [
      { code: 'KEMENPANRB', name: 'Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi' },
      { code: 'KEMENKEU', name: 'Kementerian Keuangan' },
      { code: 'KEMENKUMHAM', name: 'Kementerian Hukum dan Hak Asasi Manusia' },
      { code: 'KEMENKES', name: 'Kementerian Kesehatan' },
      { code: 'KEMENDIKBUD', name: 'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi' },
      { code: 'KEMENAG', name: 'Kementerian Agama' },
      { code: 'KEMENKO_MARITIM', name: 'Kementerian Koordinator Bidang Kemaritiman dan Investasi' },
      { code: 'KEMENKO_PMK', name: 'Kementerian Koordinator Bidang Perekonomian' },
      { code: 'KEMENKO_POLHUKAM', name: 'Kementerian Koordinator Bidang Politik, Hukum, dan Keamanan' }
    ],
    PROVINCES: [
      { code: 'ACEH', name: 'Aceh' },
      { code: 'SUMUT', name: 'Sumatera Utara' },
      { code: 'SUMBAR', name: 'Sumatera Barat' },
      { code: 'SUMSEL', name: 'Sumatera Selatan' },
      { code: 'BENGKULU', name: 'Bengkulu' },
      { code: 'RIAU', name: 'Riau' },
      { code: 'KEPRI', name: 'Kepulauan Riau' },
      { code: 'JAMBI', name: 'Jambi' },
      { code: 'LAMPUNG', name: 'Lampung' },
      { code: 'BABEL', name: 'Bangka Belitung' },
      { code: 'DKI_JAKARTA', name: 'DKI Jakarta' },
      { code: 'JAWA_BARAT', name: 'Jawa Barat' },
      { code: 'JAWA_TENGAH', name: 'Jawa Tengah' },
      { code: 'JAWA_TIMUR', name: 'Jawa Timur' },
      { code: 'DI_YOGYAKARTA', name: 'DI Yogyakarta' },
      { code: 'BANTEN', name: 'Banten' }
    ]
  }
};

// ==========================================================================
// Report and Document Constants
// ==========================================================================

/**
 * Report generation constants
 */
const REPORT_CONSTANTS = {
  // Report Types
  TYPES: {
    PERFORMANCE: 'performance',
    FINANCIAL: 'financial',
    COMPLIANCE: 'compliance',
    RISK: 'risk',
    AUDIT: 'audit',
    EVALUATION: 'evaluation',
    MONITORING: 'monitoring',
    SUMMARY: 'summary',
    DETAILED: 'detailed',
    COMPARATIVE: 'comparative',
    TREND: 'trend'
  },

  // Report Formats
  FORMATS: {
    PDF: 'pdf',
    EXCEL: 'excel',
    WORD: 'word',
    CSV: 'csv',
    JSON: 'json',
    XML: 'xml',
    HTML: 'html'
  },

  // Report Periods
  PERIODS: {
    MONTHLY: 'monthly',
    QUARTERLY: 'quarterly',
    SEMESTER: 'semester',
    ANNUAL: 'annual',
    MULTI_YEAR: 'multi_year',
    CUSTOM: 'custom'
  },

  // Report Status
  STATUS: {
    DRAFT: 'draft',
    GENERATING: 'generating',
    COMPLETED: 'completed',
    FAILED: 'failed',
    CANCELLED: 'cancelled',
    EXPIRED: 'expired'
  },

  // Template Types
  TEMPLATES: {
    STANDARD: 'standard',
    EXECUTIVE: 'executive',
    TECHNICAL: 'technical',
    GOVERNMENT: 'government',
    INTERNATIONAL: 'international'
  },

  // Maximum Sizes
  LIMITS: {
    MAX_ROWS: 100000,
    MAX_COLUMNS: 500,
    MAX_FILE_SIZE: 52428800, // 50MB
    MAX_CHART_ELEMENTS: 100,
    MAX_TABLE_CELLS: 500000
  }
};

// ==========================================================================
// Notification and Alert Constants
// ==========================================================================

/**
 * Notification system constants
 */
const NOTIFICATION_CONSTANTS = {
  // Notification Types
  TYPES: {
    INFO: 'info',
    SUCCESS: 'success',
    WARNING: 'warning',
    ERROR: 'error',
    SYSTEM: 'system',
    ALERT: 'alert',
    REMINDER: 'reminder',
    UPDATE: 'update'
  },

  // Notification Channels
  CHANNELS: {
    IN_APP: 'in_app',
    EMAIL: 'email',
    SMS: 'sms',
    PUSH: 'push',
    WEBHOOK: 'webhook',
    BROADCAST: 'broadcast'
  },

  // Priority Levels
  PRIORITIES: {
    LOW: 1,
    NORMAL: 2,
    HIGH: 3,
    URGENT: 4,
    CRITICAL: 5
  },

  // Status
  STATUS: {
    UNREAD: 'unread',
    READ: 'read',
    ARCHIVED: 'archived',
    DELETED: 'deleted',
    DISMISSED: 'dismissed'
  },

  // Configuration
  CONFIG: {
    MAX_NOTIFICATIONS: 100,
    AUTO_HIDE_DELAY: 5000, // 5 seconds
    MAX_DISPLAY_NOTIFICATIONS: 5,
    SOUND_ENABLED: true,
    DESKTOP_NOTIFICATIONS: true,
    EMAIL_DIGEST_INTERVAL: 86400000 // 24 hours
  }
};

// ==========================================================================
// Audit and Logging Constants
// ==========================================================================

/**
 * Audit trail constants
 */
const AUDIT_CONSTANTS = {
  // Action Types
  ACTIONS: {
    CREATE: 'create',
    READ: 'read',
    UPDATE: 'update',
    DELETE: 'delete',
    LOGIN: 'login',
    LOGOUT: 'logout',
    EXPORT: 'export',
    IMPORT: 'import',
    APPROVE: 'approve',
    REJECT: 'reject',
    SUBMIT: 'submit',
    REVIEW: 'review',
    COMMENT: 'comment',
    ATTACH: 'attach',
    DOWNLOAD: 'download',
    PRINT: 'print',
    CONFIGURE: 'configure',
    BACKUP: 'backup',
    RESTORE: 'restore'
  },

  // Entity Types
  ENTITIES: {
    USER: 'user',
    ASSESSMENT: 'assessment',
    INSTITUTION: 'institution',
    INDICATOR: 'indicator',
    EVIDENCE: 'evidence',
    REPORT: 'report',
    DOCUMENT: 'document',
    CONFIGURATION: 'configuration',
    PERMISSION: 'permission',
    ROLE: 'role',
    NOTIFICATION: 'notification',
    AUDIT_LOG: 'audit_log'
  },

  // Severity Levels
  SEVERITY: {
    LOW: 'low',
    MEDIUM: 'medium',
    HIGH: 'high',
    CRITICAL: 'critical'
  },

  // Retention Policies
  RETENTION: {
    AUDIT_LOGS: 2555, // 7 years in days
    USER_LOGS: 1095, // 3 years in days
    SYSTEM_LOGS: 365, // 1 year in days
    ERROR_LOGS: 180 // 6 months in days
  }
};

// ==========================================================================
// System and Technical Constants
// ==========================================================================

/**
 * System configuration constants
 */
const SYSTEM_CONSTANTS = {
  // Cache Keys
  CACHE_KEYS: {
    USER_SESSION: 'sakip:user:session',
    USER_PREFERENCES: 'sakip:user:preferences',
    INSTITUTION_DATA: 'sakip:institution:data',
    ASSESSMENT_DATA: 'sakip:assessment:data',
    REPORT_DATA: 'sakip:report:data',
    CONFIGURATION: 'sakip:configuration',
    TRANSLATIONS: 'sakip:translations',
    VALIDATION_RULES: 'sakip:validation:rules'
  },

  // Session Keys
  SESSION_KEYS: {
    USER: 'sakip_session_user',
    TOKEN: 'sakip_session_token',
    PERMISSIONS: 'sakip_session_permissions',
    PREFERENCES: 'sakip_session_preferences',
    LAST_ACTIVITY: 'sakip_session_last_activity',
    CSRF_TOKEN: 'sakip_csrf_token'
  },

  // Local Storage Keys
  LOCAL_STORAGE: {
    USER_PREFERENCES: 'sakip_user_preferences',
    RECENT_ASSESSMENTS: 'sakip_recent_assessments',
    DRAFT_DATA: 'sakip_draft_data',
    NOTIFICATION_SETTINGS: 'sakip_notification_settings',
    LANGUAGE: 'sakip_language',
    THEME: 'sakip_theme'
  },

  // Cookie Keys
  COOKIES: {
    SESSION_ID: 'sakip_session_id',
    REMEMBER_ME: 'sakip_remember_me',
    LANGUAGE: 'sakip_language',
    THEME: 'sakip_theme',
    CSRF_TOKEN: 'sakip_csrf_token'
  },

  // Error Codes
  ERROR_CODES: {
    // Authentication Errors (1xxx)
    AUTH_INVALID_CREDENTIALS: 1001,
    AUTH_SESSION_EXPIRED: 1002,
    AUTH_INSUFFICIENT_PERMISSIONS: 1003,
    AUTH_ACCOUNT_LOCKED: 1004,
    AUTH_ACCOUNT_SUSPENDED: 1005,

    // Validation Errors (2xxx)
    VALIDATION_FAILED: 2001,
    VALIDATION_REQUIRED: 2002,
    VALIDATION_FORMAT: 2003,
    VALIDATION_RANGE: 2004,
    VALIDATION_UNIQUE: 2005,

    // Business Logic Errors (3xxx)
    BUSINESS_RULE_VIOLATION: 3001,
    STATE_TRANSITION_INVALID: 3002,
    RESOURCE_NOT_FOUND: 3003,
    RESOURCE_ALREADY_EXISTS: 3004,
    RESOURCE_IN_USE: 3005,

    // System Errors (4xxx)
    SYSTEM_ERROR: 4001,
    DATABASE_ERROR: 4002,
    FILE_SYSTEM_ERROR: 4003,
    NETWORK_ERROR: 4004,
    TIMEOUT_ERROR: 4005,

    // External Service Errors (5xxx)
    EXTERNAL_SERVICE_ERROR: 5001,
    API_RATE_LIMIT_EXCEEDED: 5002,
    EXTERNAL_SERVICE_UNAVAILABLE: 5003
  },

  // HTTP Status Codes
  HTTP_STATUS: {
    OK: 200,
    CREATED: 201,
    ACCEPTED: 202,
    NO_CONTENT: 204,
    BAD_REQUEST: 400,
    UNAUTHORIZED: 401,
    FORBIDDEN: 403,
    NOT_FOUND: 404,
    METHOD_NOT_ALLOWED: 405,
    CONFLICT: 409,
    UNPROCESSABLE_ENTITY: 422,
    TOO_MANY_REQUESTS: 429,
    INTERNAL_SERVER_ERROR: 500,
    BAD_GATEWAY: 502,
    SERVICE_UNAVAILABLE: 503,
    GATEWAY_TIMEOUT: 504
  }
};

// ==========================================================================
// Export All Constants
// ==========================================================================

/**
 * Export all constants for use in other modules
 */
const SAKIP_CONFIG = {
  APP: SAKIP_CONSTANTS,
  ASSESSMENT: ASSESSMENT_CONSTANTS,
  USER: USER_CONSTANTS,
  INSTITUTION: INSTITUTION_CONSTANTS,
  REPORT: REPORT_CONSTANTS,
  NOTIFICATION: NOTIFICATION_CONSTANTS,
  AUDIT: AUDIT_CONSTANTS,
  SYSTEM: SYSTEM_CONSTANTS
};

// ==========================================================================
// Helper Functions
// ==========================================================================

/**
 * Get constant value by path
 * @param {string} path - Dot-separated path to constant (e.g., 'ASSESSMENT.STATUS.DRAFT')
 * @returns {*} Constant value or undefined
 */
function getConstant(path) {
  const keys = path.split('.');
  let current = SAKIP_CONFIG;

  for (const key of keys) {
    if (current && typeof current === 'object' && key in current) {
      current = current[key];
    } else {
      return undefined;
    }
  }

  return current;
}

/**
 * Get achievement level by score
 * @param {number} score - Assessment score (0-100)
 * @returns {Object} Achievement level object
 */
function getAchievementLevel(score) {
  const levels = ASSESSMENT_CONSTANTS.ACHIEVEMENT_LEVELS;

  if (score >= levels.EXCELLENT.min_score) return levels.EXCELLENT;
  if (score >= levels.GOOD.min_score) return levels.GOOD;
  if (score >= levels.ADEQUATE.min_score) return levels.ADEQUATE;
  if (score >= levels.POOR.min_score) return levels.POOR;
  return levels.VERY_POOR;
}

/**
 * Check if file extension is allowed
 * @param {string} extension - File extension
 * @returns {boolean} Whether extension is allowed
 */
function isAllowedFileExtension(extension) {
  const allowed = SAKIP_CONSTANTS.FILE_UPLOAD.ALLOWED_EXTENSIONS;
  return allowed.includes(extension.toLowerCase());
}

/**
 * Check if MIME type is allowed
 * @param {string} mimeType - MIME type
 * @returns {boolean} Whether MIME type is allowed
 */
function isAllowedMimeType(mimeType) {
  const allowed = SAKIP_CONSTANTS.FILE_UPLOAD.ALLOWED_MIME_TYPES;
  return allowed.includes(mimeType);
}

/**
 * Format file size to human readable format
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Get error message by error code
 * @param {number} errorCode - Error code
 * @param {string} language - Language code (default: 'id')
 * @returns {string} Error message
 */
function getErrorMessage(errorCode, language = 'id') {
  const messages = {
    id: {
      1001: 'Kredensial tidak valid. Silakan periksa username dan password Anda.',
      1002: 'Sesi telah kedaluwarsa. Silakan login kembali.',
      1003: 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
      1004: 'Akun Anda dikunci karena terlalu banyak percobaan login gagal.',
      1005: 'Akun Anda ditangguhkan. Silakan hubungi administrator.',
      2001: 'Validasi data gagal. Silakan periksa kembali data yang Anda masukkan.',
      2002: 'Field wajib tidak boleh kosong.',
      2003: 'Format data tidak valid.',
      2004: 'Data berada di luar rentang yang diizinkan.',
      2005: 'Data sudah ada dalam sistem.',
      3001: 'Pelanggaran aturan bisnis.',
      3002: 'Transisi status tidak valid.',
      3003: 'Resource tidak ditemukan.',
      3004: 'Resource sudah ada.',
      3005: 'Resource sedang digunakan.',
      4001: 'Terjadi kesalahan sistem.',
      4002: 'Kesalahan database.',
      4003: 'Kesalahan sistem file.',
      4004: 'Kesalahan jaringan.',
      4005: 'Permintaan timeout.',
      5001: 'Kesalahan layanan eksternal.',
      5002: 'Batas rate API terlampaui.',
      5003: 'Layanan eksternal tidak tersedia.'
    },
    en: {
      1001: 'Invalid credentials. Please check your username and password.',
      1002: 'Session has expired. Please log in again.',
      1003: 'You do not have permission to perform this action.',
      1004: 'Your account is locked due to too many failed login attempts.',
      1005: 'Your account is suspended. Please contact the administrator.',
      2001: 'Data validation failed. Please check the data you entered.',
      2002: 'Required fields cannot be empty.',
      2003: 'Invalid data format.',
      2004: 'Data is outside the allowed range.',
      2005: 'Data already exists in the system.',
      3001: 'Business rule violation.',
      3002: 'Invalid status transition.',
      3003: 'Resource not found.',
      3004: 'Resource already exists.',
      3005: 'Resource is in use.',
      4001: 'System error occurred.',
      4002: 'Database error.',
      4003: 'File system error.',
      4004: 'Network error.',
      4005: 'Request timeout.',
      5001: 'External service error.',
      5002: 'API rate limit exceeded.',
      5003: 'External service unavailable.'
    }
  };

  return messages[language]?.[errorCode] || messages[SAKIP_CONSTANTS.LANGUAGE.FALLBACK][errorCode] || 'Unknown error occurred.';
}

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = {
    SAKIP_CONFIG,
    SAKIP_CONSTANTS,
    ASSESSMENT_CONSTANTS,
    USER_CONSTANTS,
    INSTITUTION_CONSTANTS,
    REPORT_CONSTANTS,
    NOTIFICATION_CONSTANTS,
    AUDIT_CONSTANTS,
    SYSTEM_CONSTANTS,
    getConstant,
    getAchievementLevel,
    isAllowedFileExtension,
    isAllowedMimeType,
    formatFileSize,
    getErrorMessage
  };
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return {
      SAKIP_CONFIG,
      SAKIP_CONSTANTS,
      ASSESSMENT_CONSTANTS,
      USER_CONSTANTS,
      INSTITUTION_CONSTANTS,
      REPORT_CONSTANTS,
      NOTIFICATION_CONSTANTS,
      AUDIT_CONSTANTS,
      SYSTEM_CONSTANTS,
      getConstant,
      getAchievementLevel,
      isAllowedFileExtension,
      isAllowedMimeType,
      formatFileSize,
      getErrorMessage
    };
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.CONSTANTS = {
    SAKIP_CONFIG,
    SAKIP_CONSTANTS,
    ASSESSMENT_CONSTANTS,
    USER_CONSTANTS,
    INSTITUTION_CONSTANTS,
    REPORT_CONSTANTS,
    NOTIFICATION_CONSTANTS,
    AUDIT_CONSTANTS,
    SYSTEM_CONSTANTS,
    getConstant,
    getAchievementLevel,
    isAllowedFileExtension,
    isAllowedMimeType,
    formatFileSize,
    getErrorMessage
  };
}
