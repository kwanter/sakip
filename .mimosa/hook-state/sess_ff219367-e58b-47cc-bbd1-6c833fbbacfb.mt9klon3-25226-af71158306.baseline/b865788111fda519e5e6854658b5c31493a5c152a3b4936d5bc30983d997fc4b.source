/**
 * SAKIP Status Badges Helper Functions
 * Comprehensive status badge generators for government-style SAKIP module
 */

// ==========================================================================
// Status Badge Configuration
// ==========================================================================

/**
 * Badge style configurations
 */
const BADGE_STYLES = {
  // Assessment status badges
  ASSESSMENT: {
    DRAFT: {
      text: { id: 'Konsep', en: 'Draft' },
      class: 'badge-draft',
      icon: '📝',
      color: '#6c757d',
      backgroundColor: '#f8f9fa',
      borderColor: '#6c757d'
    },
    IN_PROGRESS: {
      text: { id: 'Dalam Proses', en: 'In Progress' },
      class: 'badge-in-progress',
      icon: '🔄',
      color: '#ffc107',
      backgroundColor: '#fff3cd',
      borderColor: '#ffc107'
    },
    SUBMITTED: {
      text: { id: 'Terkirim', en: 'Submitted' },
      class: 'badge-submitted',
      icon: '📤',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    UNDER_REVIEW: {
      text: { id: 'Dalam Tinjauan', en: 'Under Review' },
      class: 'badge-under-review',
      icon: '👁️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    },
    APPROVED: {
      text: { id: 'Disetujui', en: 'Approved' },
      class: 'badge-approved',
      icon: '✅',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    REJECTED: {
      text: { id: 'Ditolak', en: 'Rejected' },
      class: 'badge-rejected',
      icon: '❌',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    EXPIRED: {
      text: { id: 'Kedaluwarsa', en: 'Expired' },
      class: 'badge-expired',
      icon: '⏰',
      color: '#6c757d',
      backgroundColor: '#e9ecef',
      borderColor: '#6c757d'
    }
  },

  // Report status badges
  REPORT: {
    DRAFT: {
      text: { id: 'Konsep', en: 'Draft' },
      class: 'badge-report-draft',
      icon: '📝',
      color: '#6c757d',
      backgroundColor: '#f8f9fa',
      borderColor: '#6c757d'
    },
    GENERATING: {
      text: { id: 'Membuat', en: 'Generating' },
      class: 'badge-report-generating',
      icon: '⚙️',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    COMPLETED: {
      text: { id: 'Selesai', en: 'Completed' },
      class: 'badge-report-completed',
      icon: '✅',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    FAILED: {
      text: { id: 'Gagal', en: 'Failed' },
      class: 'badge-report-failed',
      icon: '❌',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    ARCHIVED: {
      text: { id: 'Diarsipkan', en: 'Archived' },
      class: 'badge-report-archived',
      icon: '📁',
      color: '#6c757d',
      backgroundColor: '#e9ecef',
      borderColor: '#6c757d'
    }
  },

  // Priority badges
  PRIORITY: {
    LOW: {
      text: { id: 'Rendah', en: 'Low' },
      class: 'badge-priority-low',
      icon: '🔽',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    NORMAL: {
      text: { id: 'Normal', en: 'Normal' },
      class: 'badge-priority-normal',
      icon: '➡️',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    HIGH: {
      text: { id: 'Tinggi', en: 'High' },
      class: 'badge-priority-high',
      icon: '🔼',
      color: '#ffc107',
      backgroundColor: '#fff3cd',
      borderColor: '#ffc107'
    },
    CRITICAL: {
      text: { id: 'Kritis', en: 'Critical' },
      class: 'badge-priority-critical',
      icon: '🚨',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    URGENT: {
      text: { id: 'Mendesak', en: 'Urgent' },
      class: 'badge-priority-urgent',
      icon: '⚠️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    }
  },

  // Achievement level badges
  ACHIEVEMENT: {
    EXCELLENT: {
      text: { id: 'Sangat Baik', en: 'Excellent' },
      class: 'badge-achievement-excellent',
      icon: '🏆',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    GOOD: {
      text: { id: 'Baik', en: 'Good' },
      class: 'badge-achievement-good',
      icon: '🥈',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    ADEQUATE: {
      text: { id: 'Cukup', en: 'Adequate' },
      class: 'badge-achievement-adequate',
      icon: '🥉',
      color: '#ffc107',
      backgroundColor: '#fff3cd',
      borderColor: '#ffc107'
    },
    POOR: {
      text: { id: 'Kurang', en: 'Poor' },
      class: 'badge-achievement-poor',
      icon: '⚠️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    },
    VERY_POOR: {
      text: { id: 'Sangat Kurang', en: 'Very Poor' },
      class: 'badge-achievement-very-poor',
      icon: '❌',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    }
  },

  // Institution type badges
  INSTITUTION_TYPE: {
    MINISTRY: {
      text: { id: 'Kementerian', en: 'Ministry' },
      class: 'badge-institution-ministry',
      icon: '🏛️',
      color: '#6f42c1',
      backgroundColor: '#e7d7ff',
      borderColor: '#6f42c1'
    },
    AGENCY: {
      text: { id: 'Lembaga', en: 'Agency' },
      class: 'badge-institution-agency',
      icon: '🏢',
      color: '#20c997',
      backgroundColor: '#d1f2eb',
      borderColor: '#20c997'
    },
    PROVINCE: {
      text: { id: 'Provinsi', en: 'Province' },
      class: 'badge-institution-province',
      icon: '🗺️',
      color: '#e83e8c',
      backgroundColor: '#fce4ec',
      borderColor: '#e83e8c'
    },
    CITY: {
      text: { id: 'Kota', en: 'City' },
      class: 'badge-institution-city',
      icon: '🏙️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    },
    DISTRICT: {
      text: { id: 'Kabupaten', en: 'District' },
      class: 'badge-institution-district',
      icon: '🌾',
      color: '#6f42c1',
      backgroundColor: '#e7d7ff',
      borderColor: '#6f42c1'
    }
  },

  // User role badges
  USER_ROLE: {
    ADMIN: {
      text: { id: 'Administrator', en: 'Administrator' },
      class: 'badge-role-admin',
      icon: '👨‍💼',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    ASSESSOR: {
      text: { id: 'Penilai', en: 'Assessor' },
      class: 'badge-role-assessor',
      icon: '🧑‍⚖️',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    REVIEWER: {
      text: { id: 'Peninjau', en: 'Reviewer' },
      class: 'badge-role-reviewer',
      icon: '👁️',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    INSTITUTION_HEAD: {
      text: { id: 'Kepala Instansi', en: 'Institution Head' },
      class: 'badge-role-head',
      icon: '👨‍💼',
      color: '#6f42c1',
      backgroundColor: '#e7d7ff',
      borderColor: '#6f42c1'
    },
    STAFF: {
      text: { id: 'Staf', en: 'Staff' },
      class: 'badge-role-staff',
      icon: '👤',
      color: '#6c757d',
      backgroundColor: '#e9ecef',
      borderColor: '#6c757d'
    }
  },

  // File type badges
  FILE_TYPE: {
    PDF: {
      text: { id: 'PDF', en: 'PDF' },
      class: 'badge-file-pdf',
      icon: '📄',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    EXCEL: {
      text: { id: 'Excel', en: 'Excel' },
      class: 'badge-file-excel',
      icon: '📊',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    WORD: {
      text: { id: 'Word', en: 'Word' },
      class: 'badge-file-word',
      icon: '📝',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    IMAGE: {
      text: { id: 'Gambar', en: 'Image' },
      class: 'badge-file-image',
      icon: '🖼️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    },
    ZIP: {
      text: { id: 'ZIP', en: 'ZIP' },
      class: 'badge-file-zip',
      icon: '🗜️',
      color: '#6c757d',
      backgroundColor: '#e9ecef',
      borderColor: '#6c757d'
    }
  },

  // Notification type badges
  NOTIFICATION_TYPE: {
    INFO: {
      text: { id: 'Info', en: 'Info' },
      class: 'badge-notification-info',
      icon: 'ℹ️',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    SUCCESS: {
      text: { id: 'Sukses', en: 'Success' },
      class: 'badge-notification-success',
      icon: '✅',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    WARNING: {
      text: { id: 'Peringatan', en: 'Warning' },
      class: 'badge-notification-warning',
      icon: '⚠️',
      color: '#ffc107',
      backgroundColor: '#fff3cd',
      borderColor: '#ffc107'
    },
    ERROR: {
      text: { id: 'Error', en: 'Error' },
      class: 'badge-notification-error',
      icon: '❌',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    CRITICAL: {
      text: { id: 'Kritis', en: 'Critical' },
      class: 'badge-notification-critical',
      icon: '🚨',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    }
  },

  // Score range badges
  SCORE_RANGE: {
    '90-100': {
      text: { id: 'A (90-100)', en: 'A (90-100)' },
      class: 'badge-score-a',
      icon: '🏆',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    '80-89': {
      text: { id: 'B (80-89)', en: 'B (80-89)' },
      class: 'badge-score-b',
      icon: '🥈',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    },
    '70-79': {
      text: { id: 'C (70-79)', en: 'C (70-79)' },
      class: 'badge-score-c',
      icon: '🥉',
      color: '#ffc107',
      backgroundColor: '#fff3cd',
      borderColor: '#ffc107'
    },
    '60-69': {
      text: { id: 'D (60-69)', en: 'D (60-69)' },
      class: 'badge-score-d',
      icon: '⚠️',
      color: '#fd7e14',
      backgroundColor: '#ffeaa7',
      borderColor: '#fd7e14'
    },
    '0-59': {
      text: { id: 'E (0-59)', en: 'E (0-59)' },
      class: 'badge-score-e',
      icon: '❌',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    }
  },

  // Government institution level badges
  INSTITUTION_LEVEL: {
    CENTRAL: {
      text: { id: 'Pusat', en: 'Central' },
      class: 'badge-level-central',
      icon: '🏛️',
      color: '#dc3545',
      backgroundColor: '#f8d7da',
      borderColor: '#dc3545'
    },
    PROVINCIAL: {
      text: { id: 'Provinsi', en: 'Provincial' },
      class: 'badge-level-provincial',
      icon: '🗺️',
      color: '#6f42c1',
      backgroundColor: '#e7d7ff',
      borderColor: '#6f42c1'
    },
    REGENCY: {
      text: { id: 'Kabupaten', en: 'Regency' },
      class: 'badge-level-regency',
      icon: '🌾',
      color: '#28a745',
      backgroundColor: '#d4edda',
      borderColor: '#28a745'
    },
    CITY: {
      text: { id: 'Kota', en: 'City' },
      class: 'badge-level-city',
      icon: '🏙️',
      color: '#17a2b8',
      backgroundColor: '#d1ecf1',
      borderColor: '#17a2b8'
    }
  }
};

// ==========================================================================
// Badge Generator Functions
// ==========================================================================

/**
 * Status badge generator functions
 */
const StatusBadgeGenerators = {
  /**
   * Generate assessment status badge
   * @param {string} status - Assessment status
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateAssessmentBadge: function(status, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.ASSESSMENT[status];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Status: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate report status badge
   * @param {string} status - Report status
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateReportBadge: function(status, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.REPORT[status];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Status: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate priority badge
   * @param {string} priority - Priority level
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generatePriorityBadge: function(priority, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.PRIORITY[priority];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Prioritas: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate achievement level badge
   * @param {string} level - Achievement level
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateAchievementBadge: function(level, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.ACHIEVEMENT[level];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Capaian: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate score badge based on numeric score
   * @param {number} score - Numeric score (0-100)
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateScoreBadge: function(score, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default',
      showScore: false
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    // Determine score range
    let range;
    if (score >= 90) range = '90-100';
    else if (score >= 80) range = '80-89';
    else if (score >= 70) range = '70-79';
    else if (score >= 60) range = '60-69';
    else range = '0-59';
    
    const config = BADGE_STYLES.SCORE_RANGE[range];
    if (!config) return null;
    
    // Customize text if showing score
    if (finalOptions.showScore) {
      config = {
        ...config,
        text: {
          id: `${config.text.id} (${score})`,
          en: `${config.text.en} (${score})`
        }
      };
    }
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Nilai: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate institution type badge
   * @param {string} type - Institution type
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateInstitutionTypeBadge: function(type, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.INSTITUTION_TYPE[type];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Jenis Institusi: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate user role badge
   * @param {string} role - User role
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateUserRoleBadge: function(role, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.USER_ROLE[role];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Peran: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate file type badge
   * @param {string} fileType - File type
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateFileTypeBadge: function(fileType, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.FILE_TYPE[fileType];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Tipe File: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate notification type badge
   * @param {string} type - Notification type
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateNotificationTypeBadge: function(type, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'small',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.NOTIFICATION_TYPE[type];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Tipe: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate institution level badge
   * @param {string} level - Institution level
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateInstitutionLevelBadge: function(level, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const config = BADGE_STYLES.INSTITUTION_LEVEL[level];
    if (!config) return null;
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Tingkat: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate custom badge
   * @param {Object} config - Custom badge configuration
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateCustomBadge: function(config, options = {}) {
    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return {
      class: `sakip-badge ${config.class || 'custom-badge'} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color || '#6c757d',
        backgroundColor: config.backgroundColor || '#f8f9fa',
        borderColor: config.borderColor || '#6c757d'
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: config.text ? `Status: ${config.text[finalOptions.language]}` : 'Custom Badge',
      title: config.text ? config.text[finalOptions.language] : ''
    };
  },

  /**
   * Generate badge content
   * @param {Object} config - Badge configuration
   * @param {Object} options - Content options
   * @returns {string} Badge content HTML
   */
  generateBadgeContent: function(config, options) {
    const parts = [];
    
    if (options.showIcon && config.icon) {
      parts.push(`<span class="badge-icon">${config.icon}</span>`);
    }
    
    if (options.showText && config.text) {
      parts.push(`<span class="badge-text">${config.text[options.language]}</span>`);
    }
    
    return parts.join(' ');
  }
};

// ==========================================================================
// Badge HTML Generator Functions
// ==========================================================================

/**
 * HTML badge generator functions
 */
const BadgeHTMLGenerators = {
  /**
   * Generate HTML for assessment status badge
   * @param {string} status - Assessment status
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateAssessmentBadgeHTML: function(status, options = {}) {
    const badge = StatusBadgeGenerators.generateAssessmentBadge(status, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for report status badge
   * @param {string} status - Report status
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateReportBadgeHTML: function(status, options = {}) {
    const badge = StatusBadgeGenerators.generateReportBadge(status, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for priority badge
   * @param {string} priority - Priority level
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generatePriorityBadgeHTML: function(priority, options = {}) {
    const badge = StatusBadgeGenerators.generatePriorityBadge(priority, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for achievement badge
   * @param {string} level - Achievement level
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateAchievementBadgeHTML: function(level, options = {}) {
    const badge = StatusBadgeGenerators.generateAchievementBadge(level, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for score badge
   * @param {number} score - Numeric score
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateScoreBadgeHTML: function(score, options = {}) {
    const badge = StatusBadgeGenerators.generateScoreBadge(score, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for institution type badge
   * @param {string} type - Institution type
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateInstitutionTypeBadgeHTML: function(type, options = {}) {
    const badge = StatusBadgeGenerators.generateInstitutionTypeBadge(type, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for user role badge
   * @param {string} role - User role
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateUserRoleBadgeHTML: function(role, options = {}) {
    const badge = StatusBadgeGenerators.generateUserRoleBadge(role, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for file type badge
   * @param {string} fileType - File type
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateFileTypeBadgeHTML: function(fileType, options = {}) {
    const badge = StatusBadgeGenerators.generateFileTypeBadge(fileType, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for notification type badge
   * @param {string} type - Notification type
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateNotificationTypeBadgeHTML: function(type, options = {}) {
    const badge = StatusBadgeGenerators.generateNotificationTypeBadge(type, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for institution level badge
   * @param {string} level - Institution level
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateInstitutionLevelBadgeHTML: function(level, options = {}) {
    const badge = StatusBadgeGenerators.generateInstitutionLevelBadge(level, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate HTML for custom badge
   * @param {Object} config - Custom badge configuration
   * @param {Object} options - Badge options
   * @returns {string} HTML string
   */
  generateCustomBadgeHTML: function(config, options = {}) {
    const badge = StatusBadgeGenerators.generateCustomBadge(config, options);
    if (!badge) return '';
    
    return this.generateBadgeHTML(badge);
  },

  /**
   * Generate badge HTML from configuration
   * @param {Object} badge - Badge configuration
   * @returns {string} HTML string
   */
  generateBadgeHTML: function(badge) {
    const style = `color: ${badge.style.color}; background-color: ${badge.style.backgroundColor}; border-color: ${badge.style.borderColor};`;
    
    return `<span class="${badge.class}" style="${style}" aria-label="${badge.ariaLabel}" title="${badge.title}">${badge.content}</span>`;
  },

  /**
   * Generate badge group HTML
   * @param {Array} badges - Array of badge configurations
   * @param {Object} options - Group options
   * @returns {string} HTML string
   */
  generateBadgeGroupHTML: function(badges, options = {}) {
    const defaultOptions = {
      separator: ' ',
      wrapperClass: 'badge-group'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    const badgeHTMLs = badges.map(badge => this.generateBadgeHTML(badge));
    
    return `<div class="${finalOptions.wrapperClass}">${badgeHTMLs.join(finalOptions.separator)}</div>`;
  }
};

// ==========================================================================
// Utility Functions
// ==========================================================================

/**
 * Utility functions for status badges
 */
const BadgeUtilities = {
  /**
   * Get badge configuration by status and category
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @returns {Object} Badge configuration
   */
  getBadgeConfig: function(status, category) {
    const categoryConfigs = BADGE_STYLES[category.toUpperCase()];
    if (!categoryConfigs) return null;
    
    return categoryConfigs[status] || null;
  },

  /**
   * Get all badge configurations for a category
   * @param {string} category - Badge category
   * @returns {Object} All badge configurations for category
   */
  getAllBadgeConfigs: function(category) {
    return BADGE_STYLES[category.toUpperCase()] || {};
  },

  /**
   * Get badge style by score
   * @param {number} score - Numeric score
   * @returns {Object} Badge style configuration
   */
  getBadgeStyleByScore: function(score) {
    if (score >= 90) return BADGE_STYLES.SCORE_RANGE['90-100'];
    if (score >= 80) return BADGE_STYLES.SCORE_RANGE['80-89'];
    if (score >= 70) return BADGE_STYLES.SCORE_RANGE['70-79'];
    if (score >= 60) return BADGE_STYLES.SCORE_RANGE['60-69'];
    return BADGE_STYLES.SCORE_RANGE['0-59'];
  },

  /**
   * Determine achievement level from score
   * @param {number} score - Numeric score
   * @returns {string} Achievement level
   */
  getAchievementLevelFromScore: function(score) {
    if (score >= 90) return 'EXCELLENT';
    if (score >= 80) return 'GOOD';
    if (score >= 70) return 'ADEQUATE';
    if (score >= 60) return 'POOR';
    return 'VERY_POOR';
  },

  /**
   * Get color from status
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @returns {string} Color hex code
   */
  getColorFromStatus: function(status, category) {
    const config = this.getBadgeConfig(status, category);
    return config ? config.color : '#6c757d';
  },

  /**
   * Get background color from status
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @returns {string} Background color hex code
   */
  getBackgroundColorFromStatus: function(status, category) {
    const config = this.getBadgeConfig(status, category);
    return config ? config.backgroundColor : '#f8f9fa';
  },

  /**
   * Get text from status
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @param {string} language - Language code
   * @returns {string} Status text
   */
  getTextFromStatus: function(status, category, language = 'id') {
    const config = this.getBadgeConfig(status, category);
    return config ? config.text[language] || config.text.id : status;
  },

  /**
   * Get icon from status
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @returns {string} Icon character
   */
  getIconFromStatus: function(status, category) {
    const config = this.getBadgeConfig(status, category);
    return config ? config.icon : '';
  },

  /**
   * Validate badge status
   * @param {string} status - Status value
   * @param {string} category - Badge category
   * @returns {boolean} Whether status is valid
   */
  isValidBadgeStatus: function(status, category) {
    const categoryConfigs = BADGE_STYLES[category.toUpperCase()];
    if (!categoryConfigs) return false;
    
    return categoryConfigs.hasOwnProperty(status);
  },

  /**
   * Get all valid statuses for category
   * @param {string} category - Badge category
   * @returns {Array} Array of valid statuses
   */
  getValidStatuses: function(category) {
    const categoryConfigs = BADGE_STYLES[category.toUpperCase()];
    if (!categoryConfigs) return [];
    
    return Object.keys(categoryConfigs);
  },

  /**
   * Create badge from score with custom ranges
   * @param {number} score - Numeric score
   * @param {Array} ranges - Custom score ranges
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  createScoreBadge: function(score, ranges, options = {}) {
    const defaultRanges = [
      { min: 90, max: 100, level: 'EXCELLENT', text: 'A' },
      { min: 80, max: 89, level: 'GOOD', text: 'B' },
      { min: 70, max: 79, level: 'ADEQUATE', text: 'C' },
      { min: 60, max: 69, level: 'POOR', text: 'D' },
      { min: 0, max: 59, level: 'VERY_POOR', text: 'E' }
    ];
    
    const scoreRanges = ranges || defaultRanges;
    const range = scoreRanges.find(r => score >= r.min && score <= r.max);
    
    if (!range) return null;
    
    return StatusBadgeGenerators.generateAchievementBadge(range.level, options);
  }
};

// ==========================================================================
// Export Status Badge Functions
// ==========================================================================

/**
 * Export all status badge functions and configurations
 */
const SAKIP_STATUS_BADGES = {
  CONFIGS: BADGE_STYLES,
  GENERATORS: StatusBadgeGenerators,
  HTML: BadgeHTMLGenerators,
  UTILITIES: BadgeUtilities
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_STATUS_BADGES;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_STATUS_BADGES;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.STATUS_BADGES = SAKIP_STATUS_BADGES;
}