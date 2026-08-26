/**
 * SAKIP Formatters Helper Functions
 * Comprehensive formatting utilities for government-style SAKIP module
 */

// ==========================================================================
// Date and Time Formatters
// ==========================================================================

/**
 * Date and time formatting utilities
 */
const DateTimeFormatters = {
  /**
   * Format date in Indonesian style
   * @param {Date|string} date - Date to format
   * @param {string} format - Format type
   * @returns {string} Formatted date
   */
  formatDate: function(date, format = 'full') {
    if (!date) return '';

    const dateObj = date instanceof Date ? date : new Date(date);
    if (isNaN(dateObj.getTime())) return '';

    const options = {
      full: {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      },
      medium: {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      },
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      },
      numeric: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      }
    };

    return dateObj.toLocaleDateString('id-ID', options[format] || options.full);
  },

  /**
   * Format time in Indonesian style
   * @param {Date|string} date - Date to format
   * @param {string} format - Format type
   * @returns {string} Formatted time
   */
  formatTime: function(date, format = 'full') {
    if (!date) return '';

    const dateObj = date instanceof Date ? date : new Date(date);
    if (isNaN(dateObj.getTime())) return '';

    const options = {
      full: {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short'
      },
      medium: {
        hour: '2-digit',
        minute: '2-digit'
      },
      short: {
        hour: '2-digit',
        minute: '2-digit'
      },
      '24hour': {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
      }
    };

    return dateObj.toLocaleTimeString('id-ID', options[format] || options.full);
  },

  /**
   * Format datetime in Indonesian style
   * @param {Date|string} date - Date to format
   * @param {string} format - Format type
   * @returns {string} Formatted datetime
   */
  formatDateTime: function(date, format = 'full') {
    if (!date) return '';

    const dateObj = date instanceof Date ? date : new Date(date);
    if (isNaN(dateObj.getTime())) return '';

    const dateStr = this.formatDate(dateObj, format);
    const timeStr = this.formatTime(dateObj, format);

    return `${dateStr}, ${timeStr}`;
  },

  /**
   * Format relative time (e.g., "2 jam yang lalu")
   * @param {Date|string} date - Date to format
   * @returns {string} Relative time string
   */
  formatRelativeTime: function(date) {
    if (!date) return '';

    const dateObj = date instanceof Date ? date : new Date(date);
    if (isNaN(dateObj.getTime())) return '';

    const now = new Date();
    const diffMs = now - dateObj;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHour = Math.floor(diffMin / 60);
    const diffDay = Math.floor(diffHour / 24);
    const diffMonth = Math.floor(diffDay / 30);
    const diffYear = Math.floor(diffDay / 365);

    if (diffYear > 0) {
      return `${diffYear} tahun yang lalu`;
    } else if (diffMonth > 0) {
      return `${diffMonth} bulan yang lalu`;
    } else if (diffDay > 0) {
      return `${diffDay} hari yang lalu`;
    } else if (diffHour > 0) {
      return `${diffHour} jam yang lalu`;
    } else if (diffMin > 0) {
      return `${diffMin} menit yang lalu`;
    } else {
      return 'Baru saja';
    }
  },

  /**
   * Format date range
   * @param {Date|string} startDate - Start date
   * @param {Date|string} endDate - End date
   * @param {string} format - Format type
   * @returns {string} Formatted date range
   */
  formatDateRange: function(startDate, endDate, format = 'full') {
    if (!startDate || !endDate) return '';

    const start = startDate instanceof Date ? startDate : new Date(startDate);
    const end = endDate instanceof Date ? endDate : new Date(endDate);

    if (isNaN(start.getTime()) || isNaN(end.getTime())) return '';

    const startStr = this.formatDate(start, format);
    const endStr = this.formatDate(end, format);

    return `${startStr} - ${endStr}`;
  },

  /**
   * Get month name in Indonesian
   * @param {number} month - Month number (0-11)
   * @returns {string} Month name
   */
  getMonthName: function(month) {
    const months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return months[month] || '';
  },

  /**
   * Get day name in Indonesian
   * @param {number} day - Day number (0-6)
   * @returns {string} Day name
   */
  getDayName: function(day) {
    const days = [
      'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];

    return days[day] || '';
  }
};

// ==========================================================================
// Number and Currency Formatters
// ==========================================================================

/**
 * Number and currency formatting utilities
 */
const NumberFormatters = {
  /**
   * Format number with Indonesian locale
   * @param {number} number - Number to format
   * @param {Object} options - Formatting options
   * @returns {string} Formatted number
   */
  formatNumber: function(number, options = {}) {
    if (number === null || number === undefined || isNaN(number)) return '';

    const defaultOptions = {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
      useGrouping: true
    };

    const formatOptions = { ...defaultOptions, ...options };

    return new Intl.NumberFormat('id-ID', formatOptions).format(number);
  },

  /**
   * Format currency in Indonesian Rupiah
   * @param {number} amount - Amount to format
   * @param {Object} options - Formatting options
   * @returns {string} Formatted currency
   */
  formatCurrency: function(amount, options = {}) {
    if (amount === null || amount === undefined || isNaN(amount)) return '';

    const defaultOptions = {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    };

    const formatOptions = { ...defaultOptions, ...options };

    return new Intl.NumberFormat('id-ID', formatOptions).format(amount);
  },

  /**
   * Format percentage
   * @param {number} value - Percentage value
   * @param {number} decimals - Number of decimal places
   * @returns {string} Formatted percentage
   */
  formatPercentage: function(value, decimals = 1) {
    if (value === null || value === undefined || isNaN(value)) return '';

    return `${this.formatNumber(value, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    })}%`;
  },

  /**
   * Format score/grade
   * @param {number} score - Score value (0-100)
   * @param {Object} options - Formatting options
   * @returns {string} Formatted score
   */
  formatScore: function(score, options = {}) {
    if (score === null || score === undefined || isNaN(score)) return '';

    const defaultOptions = {
      minimumFractionDigits: 1,
      maximumFractionDigits: 1,
      showGrade: true,
      showPercentage: true
    };

    const formatOptions = { ...defaultOptions, ...options };

    let result = this.formatNumber(score, {
      minimumFractionDigits: formatOptions.minimumFractionDigits,
      maximumFractionDigits: formatOptions.maximumFractionDigits
    });

    if (formatOptions.showPercentage) {
      result += '/100';
    }

    if (formatOptions.showGrade) {
      const grade = this.getGradeFromScore(score);
      result += ` (${grade})`;
    }

    return result;
  },

  /**
   * Get grade from score
   * @param {number} score - Score value (0-100)
   * @returns {string} Grade letter
   */
  getGradeFromScore: function(score) {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    if (score >= 60) return 'D';
    return 'E';
  },

  /**
   * Format achievement level
   * @param {string} level - Achievement level
   * @returns {string} Formatted achievement level
   */
  formatAchievementLevel: function(level) {
    const levels = {
      'EXCELLENT': 'Sangat Baik',
      'GOOD': 'Baik',
      'ADEQUATE': 'Cukup',
      'POOR': 'Kurang',
      'VERY_POOR': 'Sangat Kurang'
    };

    return levels[level] || level;
  },

  /**
   * Format large numbers with abbreviations
   * @param {number} number - Number to format
   * @returns {string} Formatted number with abbreviation
   */
  formatLargeNumber: function(number) {
    if (number === null || number === undefined || isNaN(number)) return '';

    if (number >= 1000000000) {
      return `${this.formatNumber(number / 1000000000, { maximumFractionDigits: 1 })} Miliar`;
    } else if (number >= 1000000) {
      return `${this.formatNumber(number / 1000000, { maximumFractionDigits: 1 })} Juta`;
    } else if (number >= 1000) {
      return `${this.formatNumber(number / 1000, { maximumFractionDigits: 1 })} Ribu`;
    } else {
      return this.formatNumber(number);
    }
  },

  /**
   * Format file size
   * @param {number} bytes - File size in bytes
   * @returns {string} Formatted file size
   */
  formatFileSize: function(bytes) {
    if (bytes === null || bytes === undefined || isNaN(bytes)) return '';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
      size /= 1024;
      unitIndex++;
    }

    return `${this.formatNumber(size, { maximumFractionDigits: 2 })} ${units[unitIndex]}`;
  },

  /**
   * Format weight with unit
   * @param {number} weight - Weight value
   * @param {string} unit - Weight unit
   * @returns {string} Formatted weight
   */
  formatWeight: function(weight, unit = 'kg') {
    if (weight === null || weight === undefined || isNaN(weight)) return '';

    const formattedWeight = this.formatNumber(weight, { maximumFractionDigits: 2 });
    return `${formattedWeight} ${unit}`;
  }
};

// ==========================================================================
// Text and String Formatters
// ==========================================================================

/**
 * Text and string formatting utilities
 */
const TextFormatters = {
  /**
   * Capitalize first letter of each word
   * @param {string} text - Text to capitalize
   * @returns {string} Capitalized text
   */
  capitalizeWords: function(text) {
    if (!text) return '';

    return text.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
  },

  /**
   * Convert to title case
   * @param {string} text - Text to convert
   * @returns {string} Title case text
   */
  toTitleCase: function(text) {
    if (!text) return '';

    const smallWords = ['dan', 'atau', 'dari', 'untuk', 'dengan', 'pada', 'di', 'ke', 'oleh'];

    return text.toLowerCase().split(' ').map((word, index) => {
      if (index === 0 || !smallWords.includes(word)) {
        return word.charAt(0).toUpperCase() + word.slice(1);
      }
      return word;
    }).join(' ');
  },

  /**
   * Truncate text to specified length
   * @param {string} text - Text to truncate
   * @param {number} maxLength - Maximum length
   * @param {string} suffix - Suffix to add
   * @returns {string} Truncated text
   */
  truncateText: function(text, maxLength = 100, suffix = '...') {
    if (!text) return '';
    if (text.length <= maxLength) return text;

    return text.substring(0, maxLength - suffix.length) + suffix;
  },

  /**
   * Format institution name
   * @param {string} name - Institution name
   * @returns {string} Formatted institution name
   */
  formatInstitutionName: function(name) {
    if (!name) return '';

    // Remove common prefixes and suffixes
    let formatted = name
      .replace(/^Dinas\s+/i, '')
      .replace(/^Badan\s+/i, '')
      .replace(/^Kantor\s+/i, '')
      .replace(/^Kementerian\s+/i, '')
      .replace(/\s+Provinsi\s+.*$/i, '')
      .replace(/\s+Kabupaten\s+.*$/i, '')
      .replace(/\s+Kota\s+.*$/i, '');

    return this.toTitleCase(formatted);
  },

  /**
   * Format government position title
   * @param {string} position - Position title
   * @returns {string} Formatted position title
   */
  formatPositionTitle: function(position) {
    if (!position) return '';

    const titles = {
      'KEPALA': 'Kepala',
      'SEKRETARIS': 'Sekretaris',
      'KABAG': 'Kepala Bagian',
      'KASUBAG': 'Kepala Sub Bagian',
      'KASIE': 'Kepala Seksi',
      'STAF': 'Staf',
      'PEGAWAI': 'Pegawai',
      'ADMINISTRATOR': 'Administrator',
      'DIREKTUR': 'Direktur',
      'MANAGER': 'Manajer',
      'SUPERVISOR': 'Supervisor'
    };

    return titles[position.toUpperCase()] || this.toTitleCase(position);
  },

  /**
   * Format address
   * @param {Object} address - Address object
   * @returns {string} Formatted address
   */
  formatAddress: function(address) {
    if (!address) return '';

    const parts = [];

    if (address.street) parts.push(address.street);
    if (address.village) parts.push(`Kel. ${address.village}`);
    if (address.district) parts.push(`Kec. ${address.district}`);
    if (address.city) parts.push(address.city);
    if (address.province) parts.push(address.province);
    if (address.postal_code) parts.push(address.postal_code);

    return parts.join(', ');
  },

  /**
   * Format phone number
   * @param {string} phoneNumber - Phone number to format
   * @returns {string} Formatted phone number
   */
  formatPhoneNumber: function(phoneNumber) {
    if (!phoneNumber) return '';

    // Remove all non-digit characters
    const digits = phoneNumber.replace(/\D/g, '');

    if (digits.length === 0) return '';

    // Format Indonesian phone numbers
    if (digits.startsWith('62')) {
      // International format: +62 xxx-xxxx-xxxx
      return `+${digits.substring(0, 2)} ${digits.substring(2, 5)}-${digits.substring(5, 9)}-${digits.substring(9)}`;
    } else if (digits.startsWith('08')) {
      // Local format: 08xx-xxxx-xxxx
      return `${digits.substring(0, 4)}-${digits.substring(4, 8)}-${digits.substring(8)}`;
    } else if (digits.startsWith('8')) {
      // Local format without 0: 8xx-xxxx-xxxx
      return `0${digits.substring(0, 3)}-${digits.substring(3, 7)}-${digits.substring(7)}`;
    }

    return digits;
  },

  /**
   * Format NIP (Nomor Induk Pegawai)
   * @param {string} nip - NIP number
   * @returns {string} Formatted NIP
   */
  formatNIP: function(nip) {
    if (!nip) return '';

    // Remove all non-digit characters
    const digits = nip.replace(/\D/g, '');

    if (digits.length !== 18) return nip;

    // Format: XXXX XXXX XXXX XXXX XX
    return `${digits.substring(0, 8)} ${digits.substring(8, 14)} ${digits.substring(14, 18)}`;
  },

  /**
   * Format NIK (Nomor Induk Kependudukan)
   * @param {string} nik - NIK number
   * @returns {string} Formatted NIK
   */
  formatNIK: function(nik) {
    if (!nik) return '';

    // Remove all non-digit characters
    const digits = nik.replace(/\D/g, '');

    if (digits.length !== 16) return nik;

    // Format: XXXX XXXX XXXX XXXX
    return `${digits.substring(0, 4)} ${digits.substring(4, 8)} ${digits.substring(8, 12)} ${digits.substring(12, 16)}`;
  },

  /**
   * Format NPWP (Nomor Pokok Wajib Pajak)
   * @param {string} npwp - NPWP number
   * @returns {string} Formatted NPWP
   */
  formatNPWP: function(npwp) {
    if (!npwp) return '';

    // Remove all non-digit characters
    const digits = npwp.replace(/\D/g, '');

    if (digits.length !== 15) return npwp;

    // Format: XX.XXX.XXX.X-XXX.XXX
    return `${digits.substring(0, 2)}.${digits.substring(2, 5)}.${digits.substring(5, 8)}.${digits.substring(8, 9)}-${digits.substring(9, 12)}.${digits.substring(12, 15)}`;
  },

  /**
   * Format institution code
   * @param {string} code - Institution code
   * @returns {string} Formatted code
   */
  formatInstitutionCode: function(code) {
    if (!code) return '';

    return code.toUpperCase();
  },

  /**
   * Generate slug from text
   * @param {string} text - Text to convert
   * @returns {string} Generated slug
   */
  generateSlug: function(text) {
    if (!text) return '';

    return text
      .toLowerCase()
      .replace(/[^\w\s-]/g, '') // Remove special characters
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/-+/g, '-') // Replace multiple hyphens with single
      .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
  },

  /**
   * Extract initials from name
   * @param {string} name - Full name
   * @returns {string} Initials
   */
  getInitials: function(name) {
    if (!name) return '';

    const words = name.trim().split(/\s+/);
    if (words.length === 0) return '';

    if (words.length === 1) {
      return words[0].substring(0, 2).toUpperCase();
    }

    // Take first letter of first and last word
    const first = words[0].substring(0, 1).toUpperCase();
    const last = words[words.length - 1].substring(0, 1).toUpperCase();

    return first + last;
  },

  /**
   * Mask sensitive text
   * @param {string} text - Text to mask
   * @param {number} visibleChars - Number of visible characters
   * @param {string} maskChar - Mask character
   * @returns {string} Masked text
   */
  maskText: function(text, visibleChars = 4, maskChar = '*') {
    if (!text) return '';
    if (text.length <= visibleChars) return text;

    const visiblePart = text.substring(0, visibleChars);
    const maskedPart = maskChar.repeat(text.length - visibleChars);

    return visiblePart + maskedPart;
  }
};

// ==========================================================================
// Data and Array Formatters
// ==========================================================================

/**
 * Data and array formatting utilities
 */
const DataFormatters = {
  /**
   * Format array as comma-separated list
   * @param {Array} array - Array to format
   * @param {string} conjunction - Conjunction word
   * @returns {string} Formatted list
   */
  formatList: function(array, conjunction = 'dan') {
    if (!array || !Array.isArray(array)) return '';
    if (array.length === 0) return '';
    if (array.length === 1) return array[0].toString();
    if (array.length === 2) return `${array[0]} ${conjunction} ${array[1]}`;

    const lastItem = array[array.length - 1];
    const otherItems = array.slice(0, -1).join(', ');

    return `${otherItems}, ${conjunction} ${lastItem}`;
  },

  /**
   * Format object as key-value pairs
   * @param {Object} obj - Object to format
   * @param {string} separator - Key-value separator
   * @param {string} delimiter - Pair delimiter
   * @returns {string} Formatted object
   */
  formatObject: function(obj, separator = ': ', delimiter = ', ') {
    if (!obj || typeof obj !== 'object') return '';

    const pairs = Object.entries(obj).map(([key, value]) => {
      return `${key}${separator}${value}`;
    });

    return pairs.join(delimiter);
  },

  /**
   * Format percentage change
   * @param {number} oldValue - Old value
   * @param {number} newValue - New value
   * @param {Object} options - Formatting options
   * @returns {string} Formatted percentage change
   */
  formatPercentageChange: function(oldValue, newValue, options = {}) {
    if (oldValue === null || oldValue === undefined ||
        newValue === null || newValue === undefined) {
      return '';
    }

    if (oldValue === 0) {
      return newValue > 0 ? '+∞%' : '0%';
    }

    const change = ((newValue - oldValue) / oldValue) * 100;
    const sign = change >= 0 ? '+' : '';

    const defaultOptions = {
      decimals: 1,
      showSign: true
    };

    const formatOptions = { ...defaultOptions, ...options };

    const formattedChange = NumberFormatters.formatNumber(change, {
      minimumFractionDigits: formatOptions.decimals,
      maximumFractionDigits: formatOptions.decimals
    });

    const signText = formatOptions.showSign ? sign : '';

    return `${signText}${formattedChange}%`;
  },

  /**
   * Format status with icon
   * @param {string} status - Status value
   * @param {Object} options - Formatting options
   * @returns {string} Formatted status
   */
  formatStatus: function(status, options = {}) {
    if (!status) return '';

    const defaultOptions = {
      showIcon: true,
      iconMap: {
        'active': '✅',
        'inactive': '❌',
        'pending': '⏳',
        'approved': '✅',
        'rejected': '❌',
        'submitted': '📤',
        'draft': '📝',
        'completed': '✅',
        'in_progress': '🔄',
        'failed': '❌',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌',
        'info': 'ℹ️'
      }
    };

    const formatOptions = { ...defaultOptions, ...options };

    const statusText = TextFormatters.toTitleCase(status.replace(/_/g, ' '));

    if (formatOptions.showIcon && formatOptions.iconMap[status.toLowerCase()]) {
      return `${formatOptions.iconMap[status.toLowerCase()]} ${statusText}`;
    }

    return statusText;
  },

  /**
   * Format priority level
   * @param {string} priority - Priority level
   * @returns {string} Formatted priority
   */
  formatPriority: function(priority) {
    if (!priority) return '';

    const priorities = {
      'low': 'Rendah',
      'normal': 'Normal',
      'medium': 'Sedang',
      'high': 'Tinggi',
      'critical': 'Kritis',
      'urgent': 'Mendesak'
    };

    return priorities[priority.toLowerCase()] || TextFormatters.toTitleCase(priority);
  },

  /**
   * Format assessment category
   * @param {string} category - Assessment category
   * @returns {string} Formatted category
   */
  formatAssessmentCategory: function(category) {
    if (!category) return '';

    const categories = {
      'PERFORMANCE': 'Kinerja',
      'FINANCIAL': 'Keuangan',
      'COMPLIANCE': 'Kepatuhan',
      'RISK': 'Risiko',
      'STRATEGIC': 'Strategis',
      'OPERATIONAL': 'Operasional'
    };

    return categories[category.toUpperCase()] || TextFormatters.toTitleCase(category);
  },

  /**
   * Format institution type
   * @param {string} type - Institution type
   * @returns {string} Formatted institution type
   */
  formatInstitutionType: function(type) {
    if (!type) return '';

    const types = {
      'MINISTRY': 'Kementerian',
      'AGENCY': 'Lembaga',
      'PROVINCE': 'Provinsi',
      'CITY': 'Kota',
      'DISTRICT': 'Kabupaten',
      'SUB_DISTRICT': 'Kecamatan',
      'VILLAGE': 'Kelurahan',
      'GOVERNMENT_OFFICE': 'Dinas',
      'STATE_OWNED_ENTERPRISE': 'BUMN'
    };

    return types[type.toUpperCase()] || TextFormatters.toTitleCase(type);
  }
};

// ==========================================================================
// Export Formatters
// ==========================================================================

/**
 * Export all formatters
 */
const SAKIP_FORMATTERS = {
  DATE_TIME: DateTimeFormatters,
  NUMBER: NumberFormatters,
  TEXT: TextFormatters,
  DATA: DataFormatters
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_FORMATTERS;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_FORMATTERS;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.FORMATTERS = SAKIP_FORMATTERS;
}
