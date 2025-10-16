/**
 * SAKIP Validation Rules Configuration
 * Comprehensive validation rules for government-style SAKIP module
 */

// ==========================================================================
// Base Validation Rules
// ==========================================================================

/**
 * Base validation rule definitions
 */
const BASE_VALIDATION_RULES = {
  // Text validation
  TEXT: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'Field ini wajib diisi',
      priority: 1
    },
    min_length: {
      rule: (value, min) => !value || value.length >= min,
      message: (min) => `Minimal ${min} karakter`,
      priority: 2
    },
    max_length: {
      rule: (value, max) => !value || value.length <= max,
      message: (max) => `Maksimal ${max} karakter`,
      priority: 3
    },
    pattern: {
      rule: (value, pattern) => !value || new RegExp(pattern).test(value),
      message: 'Format tidak valid',
      priority: 4
    },
    alphanumeric: {
      rule: (value) => !value || /^[a-zA-Z0-9\s]+$/.test(value),
      message: 'Hanya huruf, angka, dan spasi yang diizinkan',
      priority: 5
    },
    no_special_chars: {
      rule: (value) => !value || /^[a-zA-Z0-9\s\-\.\,\(\)]+$/.test(value),
      message: 'Karakter khusus tidak diizinkan',
      priority: 6
    }
  },

  // Number validation
  NUMBER: {
    required: {
      rule: (value) => value !== null && value !== undefined && value !== '',
      message: 'Field ini wajib diisi',
      priority: 1
    },
    numeric: {
      rule: (value) => !value || !isNaN(parseFloat(value)),
      message: 'Harus berupa angka',
      priority: 2
    },
    min: {
      rule: (value, min) => !value || parseFloat(value) >= min,
      message: (min) => `Minimal nilai adalah ${min}`,
      priority: 3
    },
    max: {
      rule: (value, max) => !value || parseFloat(value) <= max,
      message: (max) => `Maksimal nilai adalah ${max}`,
      priority: 4
    },
    integer: {
      rule: (value) => !value || Number.isInteger(parseFloat(value)),
      message: 'Harus berupa bilangan bulat',
      priority: 5
    },
    positive: {
      rule: (value) => !value || parseFloat(value) > 0,
      message: 'Harus berupa angka positif',
      priority: 6
    }
  },

  // Date validation
  DATE: {
    required: {
      rule: (value) => value && value instanceof Date && !isNaN(value.getTime()),
      message: 'Tanggal wajib dipilih',
      priority: 1
    },
    date_format: {
      rule: (value, format) => {
        if (!value) return true;
        const date = new Date(value);
        return !isNaN(date.getTime());
      },
      message: 'Format tanggal tidak valid',
      priority: 2
    },
    min_date: {
      rule: (value, minDate) => {
        if (!value) return true;
        const date = new Date(value);
        const min = new Date(minDate);
        return date >= min;
      },
      message: (minDate) => `Tanggal minimal adalah ${minDate}`,
      priority: 3
    },
    max_date: {
      rule: (value, maxDate) => {
        if (!value) return true;
        const date = new Date(value);
        const max = new Date(maxDate);
        return date <= max;
      },
      message: (maxDate) => `Tanggal maksimal adalah ${maxDate}`,
      priority: 4
    },
    not_future: {
      rule: (value) => {
        if (!value) return true;
        const date = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return date <= today;
      },
      message: 'Tanggal tidak boleh melebihi hari ini',
      priority: 5
    }
  }
};

// ==========================================================================
// Government-Specific Validation Rules
// ==========================================================================

/**
 * Government institution specific validation rules
 */
const GOVERNMENT_VALIDATION_RULES = {
  // NIP (Nomor Induk Pegawai) validation
  NIP: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'NIP wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // NIP format: 18 digits
        return /^\d{18}$/.test(value.replace(/\s/g, ''));
      },
      message: 'NIP harus 18 digit angka',
      priority: 2
    },
    valid_check: {
      rule: (value) => {
        if (!value) return true;
        const cleanNip = value.replace(/\s/g, '');
        if (cleanNip.length !== 18) return false;
        
        // Basic validation: check if all digits and reasonable birth date
        const birthDatePart = cleanNip.substring(0, 8);
        const birthDate = new Date(
          birthDatePart.substring(0, 4),
          parseInt(birthDatePart.substring(4, 6)) - 1,
          birthDatePart.substring(6, 8)
        );
        
        return !isNaN(birthDate.getTime());
      },
      message: 'Format NIP tidak valid',
      priority: 3
    }
  },

  // NIK (Nomor Induk Kependudukan) validation
  NIK: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'NIK wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // NIK format: 16 digits
        return /^\d{16}$/.test(value.replace(/\s/g, ''));
      },
      message: 'NIK harus 16 digit angka',
      priority: 2
    },
    valid_check: {
      rule: (value) => {
        if (!value) return true;
        const cleanNik = value.replace(/\s/g, '');
        if (cleanNik.length !== 16) return false;
        
        // Basic validation: check birth date and gender code
        const birthDatePart = cleanNik.substring(6, 12);
        const day = parseInt(birthDatePart.substring(0, 2));
        const month = parseInt(birthDatePart.substring(2, 4));
        const year = parseInt(birthDatePart.substring(4, 6));
        
        // Adjust for female (day > 40)
        const actualDay = day > 40 ? day - 40 : day;
        
        // Check if valid date
        const fullYear = year < 30 ? 2000 + year : 1900 + year;
        const date = new Date(fullYear, month - 1, actualDay);
        
        return !isNaN(date.getTime()) && month >= 1 && month <= 12 && actualDay >= 1 && actualDay <= 31;
      },
      message: 'Format NIK tidak valid',
      priority: 3
    }
  },

  // NPWP (Nomor Pokok Wajib Pajak) validation
  NPWP: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'NPWP wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // NPWP format: XX.XXX.XXX.X-XXX.XXX
        return /^\d{2}\.\d{3}\.\d{3}\.\d{1}-\d{3}\.\d{3}$/.test(value);
      },
      message: 'Format NPWP tidak valid (XX.XXX.XXX.X-XXX.XXX)',
      priority: 2
    },
    valid_check: {
      rule: (value) => {
        if (!value) return true;
        const cleanNpwp = value.replace(/[\.\-]/g, '');
        if (cleanNpwp.length !== 15) return false;
        
        // Check if all digits
        return /^\d{15}$/.test(cleanNpwp);
      },
      message: 'NPWP tidak valid',
      priority: 3
    }
  },

  // Institution Code validation
  INSTITUTION_CODE: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'Kode instansi wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // Institution code format: alphanumeric with specific patterns
        return /^[A-Z0-9]{3,10}$/.test(value);
      },
      message: 'Kode instansi harus 3-10 karakter huruf besar dan angka',
      priority: 2
    },
    unique: {
      rule: async (value, institutionId) => {
        if (!value) return true;
        // This would typically make an API call to check uniqueness
        // For now, return true as placeholder
        return true;
      },
      message: 'Kode instansi sudah digunakan',
      priority: 3,
      async: true
    }
  },

  // Budget Amount validation
  BUDGET_AMOUNT: {
    required: {
      rule: (value) => value !== null && value !== undefined && value !== '',
      message: 'Jumlah anggaran wajib diisi',
      priority: 1
    },
    numeric: {
      rule: (value) => !value || !isNaN(parseFloat(value)),
      message: 'Harus berupa angka',
      priority: 2
    },
    positive: {
      rule: (value) => !value || parseFloat(value) > 0,
      message: 'Jumlah anggaran harus positif',
      priority: 3
    },
    max_amount: {
      rule: (value, max) => !value || parseFloat(value) <= max,
      message: (max) => `Maksimal jumlah anggaran adalah Rp ${max.toLocaleString('id-ID')}`,
      priority: 4
    },
    realistic: {
      rule: (value) => {
        if (!value) return true;
        const amount = parseFloat(value);
        // Reasonable budget range for government institutions
        return amount >= 1000000 && amount <= 1000000000000; // 1 juta - 1 triliun
      },
      message: 'Jumlah anggaran tidak realistis',
      priority: 5
    }
  },

  // Phone Number validation (Indonesian format)
  PHONE_NUMBER: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'Nomor telepon wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // Indonesian phone formats: +62, 08, 62
        return /^(\+62|62|0)8[1-9][0-9]{6,10}$/.test(value.replace(/\s/g, ''));
      },
      message: 'Format nomor telepon tidak valid',
      priority: 2
    },
    valid_prefix: {
      rule: (value) => {
        if (!value) return true;
        const cleanNumber = value.replace(/\s/g, '');
        const prefixes = ['+628', '628', '08'];
        return prefixes.some(prefix => cleanNumber.startsWith(prefix));
      },
      message: 'Nomor telepon harus dimulai dengan +62, 62, atau 08',
      priority: 3
    }
  },

  // Postal Code validation (Indonesian format)
  POSTAL_CODE: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'Kode pos wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        // Indonesian postal code: 5 digits
        return /^\d{5}$/.test(value.replace(/\s/g, ''));
      },
      message: 'Kode pos harus 5 digit angka',
      priority: 2
    },
    valid_range: {
      rule: (value) => {
        if (!value) return true;
        const postalCode = parseInt(value.replace(/\s/g, ''));
        // Indonesian postal codes range from 10000 to 99999
        return postalCode >= 10000 && postalCode <= 99999;
      },
      message: 'Kode pos tidak valid',
      priority: 3
    }
  }
};

// ==========================================================================
// Assessment-Specific Validation Rules
// ==========================================================================

/**
 * Assessment and scoring validation rules
 */
const ASSESSMENT_VALIDATION_RULES = {
  // Assessment Score validation
  SCORE: {
    required: {
      rule: (value) => value !== null && value !== undefined && value !== '',
      message: 'Skor wajib diisi',
      priority: 1
    },
    numeric: {
      rule: (value) => !value || !isNaN(parseFloat(value)),
      message: 'Skor harus berupa angka',
      priority: 2
    },
    range: {
      rule: (value) => {
        if (!value) return true;
        const score = parseFloat(value);
        return score >= 0 && score <= 100;
      },
      message: 'Skor harus antara 0-100',
      priority: 3
    },
    decimal_places: {
      rule: (value, decimals) => {
        if (!value) return true;
        const score = parseFloat(value);
        const decimalPlaces = (score.toString().split('.')[1] || '').length;
        return decimalPlaces <= decimals;
      },
      message: (decimals) => `Maksimal ${decimals} angka desimal`,
      priority: 4
    }
  },

  // Evidence validation
  EVIDENCE: {
    required: {
      rule: (value) => value && (Array.isArray(value) ? value.length > 0 : true),
      message: 'Bukti pendukung wajib diunggah',
      priority: 1
    },
    file_count: {
      rule: (value, maxCount) => {
        if (!value || !Array.isArray(value)) return true;
        return value.length <= maxCount;
      },
      message: (maxCount) => `Maksimal ${maxCount} file dapat diunggah`,
      priority: 2
    },
    file_size: {
      rule: (value, maxSize) => {
        if (!value || !Array.isArray(value)) return true;
        return value.every(file => file.size <= maxSize);
      },
      message: (maxSize) => `Ukuran file maksimal ${(maxSize / 1024 / 1024).toFixed(2)} MB`,
      priority: 3
    },
    file_type: {
      rule: (value, allowedTypes) => {
        if (!value || !Array.isArray(value)) return true;
        return value.every(file => {
          const extension = file.name.split('.').pop().toLowerCase();
          return allowedTypes.includes(extension);
        });
      },
      message: 'Tipe file tidak diizinkan',
      priority: 4
    }
  },

  // Indicator Weight validation
  INDICATOR_WEIGHT: {
    required: {
      rule: (value) => value !== null && value !== undefined && value !== '',
      message: 'Bobot indikator wajib diisi',
      priority: 1
    },
    numeric: {
      rule: (value) => !value || !isNaN(parseFloat(value)),
      message: 'Bobot harus berupa angka',
      priority: 2
    },
    range: {
      rule: (value) => {
        if (!value) return true;
        const weight = parseFloat(value);
        return weight >= 0 && weight <= 100;
      },
      message: 'Bobot harus antara 0-100%',
      priority: 3
    },
    total_weight: {
      rule: async (value, totalWeight, indicatorId) => {
        if (!value) return true;
        // This would typically check total weight across all indicators
        // For now, return true as placeholder
        return true;
      },
      message: 'Total bobot semua indikator harus 100%',
      priority: 4,
      async: true
    }
  }
};

// ==========================================================================
// File Upload Validation Rules
// ==========================================================================

/**
 * File upload and processing validation rules
 */
const FILE_VALIDATION_RULES = {
  // File size validation
  FILE_SIZE: {
    required: {
      rule: (value) => value && value.size > 0,
      message: 'File wajib dipilih',
      priority: 1
    },
    max_size: {
      rule: (value, maxSize) => !value || value.size <= maxSize,
      message: (maxSize) => `Ukuran file maksimal ${(maxSize / 1024 / 1024).toFixed(2)} MB`,
      priority: 2
    },
    min_size: {
      rule: (value, minSize) => !value || value.size >= minSize,
      message: (minSize) => `Ukuran file minimal ${(minSize / 1024).toFixed(2)} KB`,
      priority: 3
    }
  },

  // File type validation
  FILE_TYPE: {
    extension: {
      rule: (value, allowedExtensions) => {
        if (!value) return true;
        const fileExtension = value.name.split('.').pop().toLowerCase();
        return allowedExtensions.includes(fileExtension);
      },
      message: 'Ekstensi file tidak diizinkan',
      priority: 1
    },
    mime_type: {
      rule: (value, allowedMimeTypes) => {
        if (!value) return true;
        return allowedMimeTypes.includes(value.type);
      },
      message: 'Tipe file tidak diizinkan',
      priority: 2
    }
  },

  // Image file validation
  IMAGE_FILE: {
    dimensions: {
      rule: (value, maxWidth, maxHeight) => {
        if (!value || !value.type.startsWith('image/')) return true;
        
        return new Promise((resolve) => {
          const img = new Image();
          img.onload = () => {
            resolve(img.width <= maxWidth && img.height <= maxHeight);
          };
          img.onerror = () => resolve(false);
          img.src = URL.createObjectURL(value);
        });
      },
      message: (maxWidth, maxHeight) => `Dimensi gambar maksimal ${maxWidth}x${maxHeight}px`,
      priority: 1,
      async: true
    },
    aspect_ratio: {
      rule: (value, ratio) => {
        if (!value || !value.type.startsWith('image/')) return true;
        
        return new Promise((resolve) => {
          const img = new Image();
          img.onload = () => {
            const actualRatio = img.width / img.height;
            resolve(Math.abs(actualRatio - ratio) < 0.1);
          };
          img.onerror = () => resolve(false);
          img.src = URL.createObjectURL(value);
        });
      },
      message: (ratio) => `Aspek rasio gambar harus ${ratio}:1`,
      priority: 2,
      async: true
    }
  }
};

// ==========================================================================
// Custom Validation Rules
// ==========================================================================

/**
 * Custom validation rules for specific use cases
 */
const CUSTOM_VALIDATION_RULES = {
  // Password validation
  PASSWORD: {
    required: {
      rule: (value) => value && value.length > 0,
      message: 'Password wajib diisi',
      priority: 1
    },
    min_length: {
      rule: (value, min) => !value || value.length >= min,
      message: (min) => `Password minimal ${min} karakter`,
      priority: 2
    },
    uppercase: {
      rule: (value) => !value || /[A-Z]/.test(value),
      message: 'Password harus mengandung huruf besar',
      priority: 3
    },
    lowercase: {
      rule: (value) => !value || /[a-z]/.test(value),
      message: 'Password harus mengandung huruf kecil',
      priority: 4
    },
    number: {
      rule: (value) => !value || /\d/.test(value),
      message: 'Password harus mengandung angka',
      priority: 5
    },
    special_char: {
      rule: (value) => !value || /[!@#$%^&*(),.?":{}|<>]/.test(value),
      message: 'Password harus mengandung karakter khusus',
      priority: 6
    }
  },

  // Email validation
  EMAIL: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'Email wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
      },
      message: 'Format email tidak valid',
      priority: 2
    },
    government_domain: {
      rule: (value) => {
        if (!value) return true;
        const governmentDomains = [
          'go.id', 'ac.id', 'mil.id', 'polri.go.id', 'tni.mil.id'
        ];
        const domain = value.split('@')[1]?.toLowerCase();
        return governmentDomains.some(govDomain => domain?.endsWith(govDomain));
      },
      message: 'Email harus menggunakan domain pemerintah',
      priority: 3
    }
  },

  // URL validation
  URL: {
    required: {
      rule: (value) => value && value.trim().length > 0,
      message: 'URL wajib diisi',
      priority: 1
    },
    format: {
      rule: (value) => {
        if (!value) return true;
        try {
          new URL(value);
          return true;
        } catch {
          return false;
        }
      },
      message: 'Format URL tidak valid',
      priority: 2
    },
    https_only: {
      rule: (value) => {
        if (!value) return true;
        try {
          const url = new URL(value);
          return url.protocol === 'https:';
        } catch {
          return false;
        }
      },
      message: 'URL harus menggunakan HTTPS',
      priority: 3
    }
  },

  // Date range validation
  DATE_RANGE: {
    start_before_end: {
      rule: (startDate, endDate) => {
        if (!startDate || !endDate) return true;
        const start = new Date(startDate);
        const end = new Date(endDate);
        return start <= end;
      },
      message: 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai',
      priority: 1
    },
    max_duration: {
      rule: (startDate, endDate, maxDays) => {
        if (!startDate || !endDate) return true;
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays <= maxDays;
      },
      message: (maxDays) => `Durasi maksimal adalah ${maxDays} hari`,
      priority: 2
    }
  }
};

// ==========================================================================
// Validation Rule Engine
// ==========================================================================

/**
 * Validation rule engine for processing validation rules
 */
const ValidationRuleEngine = {
  /**
   * Validate a single field
   * @param {any} value - Field value
   * @param {Array} rules - Validation rules
   * @param {Object} context - Validation context
   * @returns {Object} Validation result
   */
  validateField: async function(value, rules, context = {}) {
    const errors = [];
    const warnings = [];
    
    // Sort rules by priority
    const sortedRules = rules.sort((a, b) => (a.priority || 1) - (b.priority || 1));
    
    for (const rule of sortedRules) {
      try {
        let result;
        
        if (rule.async) {
          // Handle async rules
          result = await rule.rule(value, rule.param, context);
        } else {
          // Handle sync rules
          result = rule.rule(value, rule.param, context);
        }
        
        if (!result) {
          const message = typeof rule.message === 'function' 
            ? rule.message(rule.param) 
            : rule.message;
          
          if (rule.severity === 'warning') {
            warnings.push(message);
          } else {
            errors.push(message);
            // Stop validation on first error (unless continueOnError is true)
            if (!rule.continueOnError) break;
          }
        }
      } catch (error) {
        console.error('Validation rule error:', error);
        errors.push('Terjadi kesalahan dalam validasi');
        break;
      }
    }
    
    return {
      valid: errors.length === 0,
      errors,
      warnings,
      value
    };
  },

  /**
   * Validate multiple fields
   * @param {Object} data - Data to validate
   * @param {Object} validationRules - Validation rules by field
   * @param {Object} context - Validation context
   * @returns {Object} Validation results
   */
  validateForm: async function(data, validationRules, context = {}) {
    const results = {};
    let overallValid = true;
    
    for (const [fieldName, rules] of Object.entries(validationRules)) {
      const value = data[fieldName];
      const result = await this.validateField(value, rules, context);
      
      results[fieldName] = result;
      if (!result.valid) {
        overallValid = false;
      }
    }
    
    return {
      valid: overallValid,
      results,
      data
    };
  },

  /**
   * Create validation rule
   * @param {string} name - Rule name
   * @param {Function} rule - Validation function
   * @param {string} message - Error message
   * @param {Object} options - Rule options
   * @returns {Object} Validation rule
   */
  createRule: function(name, rule, message, options = {}) {
    return {
      name,
      rule,
      message,
      priority: options.priority || 1,
      param: options.param,
      async: options.async || false,
      severity: options.severity || 'error',
      continueOnError: options.continueOnError || false
    };
  },

  /**
   * Get validation rules by type
   * @param {string} type - Validation type
   * @returns {Object} Validation rules
   */
  getRulesByType: function(type) {
    const ruleSets = {
      text: BASE_VALIDATION_RULES.TEXT,
      number: BASE_VALIDATION_RULES.NUMBER,
      date: BASE_VALIDATION_RULES.DATE,
      nip: GOVERNMENT_VALIDATION_RULES.NIP,
      nik: GOVERNMENT_VALIDATION_RULES.NIK,
      npwp: GOVERNMENT_VALIDATION_RULES.NPWP,
      institution_code: GOVERNMENT_VALIDATION_RULES.INSTITUTION_CODE,
      budget_amount: GOVERNMENT_VALIDATION_RULES.BUDGET_AMOUNT,
      phone: GOVERNMENT_VALIDATION_RULES.PHONE_NUMBER,
      postal_code: GOVERNMENT_VALIDATION_RULES.POSTAL_CODE,
      score: ASSESSMENT_VALIDATION_RULES.SCORE,
      evidence: ASSESSMENT_VALIDATION_RULES.EVIDENCE,
      indicator_weight: ASSESSMENT_VALIDATION_RULES.INDICATOR_WEIGHT,
      file_size: FILE_VALIDATION_RULES.FILE_SIZE,
      file_type: FILE_VALIDATION_RULES.FILE_TYPE,
      image_file: FILE_VALIDATION_RULES.IMAGE_FILE,
      password: CUSTOM_VALIDATION_RULES.PASSWORD,
      email: CUSTOM_VALIDATION_RULES.EMAIL,
      url: CUSTOM_VALIDATION_RULES.URL,
      date_range: CUSTOM_VALIDATION_RULES.DATE_RANGE
    };
    
    return ruleSets[type] || {};
  }
};

// ==========================================================================
// Validation Helper Functions
// ==========================================================================

/**
 * Validation helper utilities
 */
const ValidationHelpers = {
  /**
   * Format validation error messages
   * @param {Array} errors - Error messages
   * @param {string} fieldName - Field name
   * @returns {string} Formatted error message
   */
  formatErrorMessage: function(errors, fieldName) {
    if (!errors || errors.length === 0) return '';
    if (errors.length === 1) return errors[0];
    return `${fieldName}: ${errors.join(', ')}`;
  },

  /**
   * Check if value is empty
   * @param {any} value - Value to check
   * @returns {boolean} Whether value is empty
   */
  isEmpty: function(value) {
    return value === null || value === undefined || value === '' || 
           (Array.isArray(value) && value.length === 0) ||
           (typeof value === 'object' && Object.keys(value).length === 0);
  },

  /**
   * Sanitize input value
   * @param {any} value - Value to sanitize
   * @param {string} type - Sanitization type
   * @returns {any} Sanitized value
   */
  sanitize: function(value, type) {
    if (this.isEmpty(value)) return value;
    
    switch (type) {
      case 'text':
        return value.toString().trim();
      case 'number':
        return parseFloat(value) || 0;
      case 'integer':
        return parseInt(value) || 0;
      case 'date':
        return new Date(value);
      case 'boolean':
        return Boolean(value);
      default:
        return value;
    }
  },

  /**
   * Validate Indonesian identity numbers
   * @param {string} number - Identity number
   * @param {string} type - Type (nip, nik, npwp)
   * @returns {Object} Validation result
   */
  validateIndonesianId: function(number, type) {
    const rules = this.getIndonesianIdRules(type);
    return ValidationRuleEngine.validateField(number, rules);
  },

  /**
   * Get Indonesian ID validation rules
   * @param {string} type - ID type
   * @returns {Array} Validation rules
   */
  getIndonesianIdRules: function(type) {
    switch (type) {
      case 'nip':
        return Object.values(GOVERNMENT_VALIDATION_RULES.NIP);
      case 'nik':
        return Object.values(GOVERNMENT_VALIDATION_RULES.NIK);
      case 'npwp':
        return Object.values(GOVERNMENT_VALIDATION_RULES.NPWP);
      default:
        return [];
    }
  }
};

// ==========================================================================
// Export Validation Rules
// ==========================================================================

/**
 * Export all validation rules and utilities
 */
const SAKIP_VALIDATION_RULES = {
  BASE: BASE_VALIDATION_RULES,
  GOVERNMENT: GOVERNMENT_VALIDATION_RULES,
  ASSESSMENT: ASSESSMENT_VALIDATION_RULES,
  FILE: FILE_VALIDATION_RULES,
  CUSTOM: CUSTOM_VALIDATION_RULES,
  ENGINE: ValidationRuleEngine,
  HELPERS: ValidationHelpers
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_VALIDATION_RULES;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_VALIDATION_RULES;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.VALIDATION = SAKIP_VALIDATION_RULES;
}