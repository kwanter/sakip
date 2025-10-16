/**
 * SAKIP Validation Utilities
 * Client-side validation functions for SAKIP forms and data
 */

class SakipValidation {
    constructor() {
        this.errors = {};
        this.rules = {};
        this.customValidators = {};
        this.init();
    }

    init() {
        this.setupCustomValidators();
        this.bindEvents();
    }

    /**
     * Setup custom validators for SAKIP-specific validation
     */
    setupCustomValidators() {
        // NIP (Nomor Induk Pegawai) validator
        this.addCustomValidator('nip', (value) => {
            if (!value) return { valid: false, message: 'NIP wajib diisi' };
            if (!/^\d{18}$/.test(value)) {
                return { valid: false, message: 'NIP harus 18 digit angka' };
            }
            return { valid: true };
        });

        // NIK (Nomor Induk Kependudukan) validator
        this.addCustomValidator('nik', (value) => {
            if (!value) return { valid: false, message: 'NIK wajib diisi' };
            if (!/^\d{16}$/.test(value)) {
                return { valid: false, message: 'NIK harus 16 digit angka' };
            }
            return { valid: true };
        });

        // NPWP validator
        this.addCustomValidator('npwp', (value) => {
            if (!value) return { valid: false, message: 'NPWP wajib diisi' };
            if (!/^\d{2}\.\d{3}\.\d{3}\.\d{1}-\d{3}\.\d{3}$/.test(value)) {
                return { valid: false, message: 'Format NPWP tidak valid (XX.XXX.XXX.X-XXX.XXX)' };
            }
            return { valid: true };
        });

        // Phone number validator (Indonesian format)
        this.addCustomValidator('phone', (value) => {
            if (!value) return { valid: false, message: 'Nomor telepon wajib diisi' };
            if (!/^((\+62)|0)[2-9]{1}[0-9]{8,11}$/.test(value)) {
                return { valid: false, message: 'Format nomor telepon tidak valid' };
            }
            return { valid: true };
        });

        // Postal code validator
        this.addCustomValidator('postal_code', (value) => {
            if (!value) return { valid: false, message: 'Kode pos wajib diisi' };
            if (!/^\d{5}$/.test(value)) {
                return { valid: false, message: 'Kode pos harus 5 digit angka' };
            }
            return { valid: true };
        });

        // Percentage validator (0-100)
        this.addCustomValidator('percentage', (value) => {
            if (value === '' || value === null || value === undefined) {
                return { valid: false, message: 'Persentase wajib diisi' };
            }
            const num = parseFloat(value);
            if (isNaN(num) || num < 0 || num > 100) {
                return { valid: false, message: 'Persentase harus antara 0-100' };
            }
            return { valid: true };
        });

        // Budget amount validator
        this.addCustomValidator('budget', (value) => {
            if (!value) return { valid: false, message: 'Jumlah anggaran wajib diisi' };
            const num = parseFloat(value.replace(/[,.]/g, ''));
            if (isNaN(num) || num < 0) {
                return { valid: false, message: 'Jumlah anggaran tidak valid' };
            }
            if (num > 1000000000000) { // Max 1 Trillion
                return { valid: false, message: 'Jumlah anggaran melebihi batas maksimal' };
            }
            return { valid: true };
        });

        // Date range validator
        this.addCustomValidator('date_range', (value, field) => {
            const startDate = field.getAttribute('data-start-date');
            const endDate = field.getAttribute('data-end-date');
            
            if (!value) return { valid: false, message: 'Tanggal wajib diisi' };
            
            const currentDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (currentDate > today) {
                return { valid: false, message: 'Tanggal tidak boleh melebihi hari ini' };
            }
            
            if (startDate && currentDate < new Date(startDate)) {
                return { valid: false, message: 'Tanggal tidak boleh sebelum tanggal mulai' };
            }
            
            if (endDate && currentDate > new Date(endDate)) {
                return { valid: false, message: 'Tanggal tidak boleh melebihi tanggal selesai' };
            }
            
            return { valid: true };
        });

        // File size validator
        this.addCustomValidator('file_size', (value, field) => {
            const maxSize = parseInt(field.getAttribute('data-max-size') || '5242880'); // 5MB default
            const file = field.files[0];
            
            if (!file) return { valid: false, message: 'File wajib diunggah' };
            if (file.size > maxSize) {
                return { valid: false, message: `Ukuran file tidak boleh melebihi ${this.formatFileSize(maxSize)}` };
            }
            return { valid: true };
        });

        // File type validator
        this.addCustomValidator('file_type', (value, field) => {
            const allowedTypes = (field.getAttribute('data-allowed-types') || '').split(',');
            const file = field.files[0];
            
            if (!file) return { valid: false, message: 'File wajib diunggah' };
            if (allowedTypes.length && !allowedTypes.includes(file.type)) {
                return { valid: false, message: 'Tipe file tidak diizinkan' };
            }
            return { valid: true };
        });

        // Strong password validator
        this.addCustomValidator('strong_password', (value) => {
            if (!value) return { valid: false, message: 'Password wajib diisi' };
            if (value.length < 8) {
                return { valid: false, message: 'Password minimal 8 karakter' };
            }
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/.test(value)) {
                return { valid: false, message: 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus' };
            }
            return { valid: true };
        });

        // Match field validator
        this.addCustomValidator('match_field', (value, field) => {
            const matchField = field.getAttribute('data-match-field');
            const matchValue = document.querySelector(`[name="${matchField}"]`).value;
            
            if (!value) return { valid: false, message: 'Field wajib diisi' };
            if (value !== matchValue) {
                return { valid: false, message: 'Nilai tidak cocok' };
            }
            return { valid: true };
        });
    }

    /**
     * Add custom validator
     */
    addCustomValidator(name, validator) {
        this.customValidators[name] = validator;
    }

    /**
     * Bind validation events
     */
    bindEvents() {
        document.addEventListener('blur', (e) => {
            if (e.target.hasAttribute('data-validate')) {
                this.validateField(e.target);
            }
        }, true);

        document.addEventListener('input', (e) => {
            if (e.target.hasAttribute('data-validate') && e.target.classList.contains('is-invalid')) {
                this.validateField(e.target);
            }
        }, true);

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.hasAttribute('data-validate-form')) {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            }
        }, true);
    }

    /**
     * Parse validation rules from data attributes
     */
    parseRules(field) {
        const rules = [];
        const validateAttr = field.getAttribute('data-validate');
        
        if (validateAttr) {
            rules.push(...validateAttr.split(',').map(rule => rule.trim()));
        }

        // Check for required
        if (field.hasAttribute('required') || field.hasAttribute('data-required')) {
            rules.push('required');
        }

        // Check for min/max length
        const minLength = field.getAttribute('minlength');
        const maxLength = field.getAttribute('maxlength');
        
        if (minLength) rules.push(`min:${minLength}`);
        if (maxLength) rules.push(`max:${maxLength}`);

        // Check for min/max value
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        
        if (min) rules.push(`min_value:${min}`);
        if (max) rules.push(`max_value:${max}`);

        // Check for pattern
        const pattern = field.getAttribute('pattern');
        if (pattern) rules.push(`pattern:${pattern}`);

        return rules;
    }

    /**
     * Validate single field
     */
    validateField(field) {
        const rules = this.parseRules(field);
        const value = field.value.trim();
        const fieldName = field.getAttribute('data-field-name') || field.name || field.id;
        
        this.clearFieldError(field);
        
        for (const rule of rules) {
            const result = this.validateRule(value, rule, field);
            if (!result.valid) {
                this.showFieldError(field, result.message);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate rule
     */
    validateRule(value, rule, field) {
        const [ruleName, ...params] = rule.split(':');
        
        // Check custom validators first
        if (this.customValidators[ruleName]) {
            return this.customValidators[ruleName](value, field);
        }
        
        // Built-in validators
        switch (ruleName) {
            case 'required':
                return value ? { valid: true } : { valid: false, message: 'Field ini wajib diisi' };
            
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value) ? { valid: true } : { valid: false, message: 'Format email tidak valid' };
            
            case 'url':
                try {
                    new URL(value);
                    return { valid: true };
                } catch {
                    return { valid: false, message: 'Format URL tidak valid' };
                }
            
            case 'numeric':
                return !isNaN(value) && !isNaN(parseFloat(value)) ? { valid: true } : { valid: false, message: 'Harus berupa angka' };
            
            case 'integer':
                return /^-?\d+$/.test(value) ? { valid: true } : { valid: false, message: 'Harus berupa bilangan bulat' };
            
            case 'min':
                const minLen = parseInt(params[0]);
                return value.length >= minLen ? { valid: true } : { valid: false, message: `Minimal ${minLen} karakter` };
            
            case 'max':
                const maxLen = parseInt(params[0]);
                return value.length <= maxLen ? { valid: true } : { valid: false, message: `Maksimal ${maxLen} karakter` };
            
            case 'min_value':
                const minVal = parseFloat(params[0]);
                const numVal = parseFloat(value);
                return !isNaN(numVal) && numVal >= minVal ? { valid: true } : { valid: false, message: `Minimal ${minVal}` };
            
            case 'max_value':
                const maxVal = parseFloat(params[0]);
                const numVal2 = parseFloat(value);
                return !isNaN(numVal2) && numVal2 <= maxVal ? { valid: true } : { valid: false, message: `Maksimal ${maxVal}` };
            
            case 'pattern':
                try {
                    const regex = new RegExp(params[0]);
                    return regex.test(value) ? { valid: true } : { valid: false, message: 'Format tidak valid' };
                } catch {
                    return { valid: false, message: 'Pattern regex tidak valid' };
                }
            
            default:
                return { valid: false, message: `Validator '${ruleName}' tidak dikenal` };
        }
    }

    /**
     * Show field error
     */
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        // Create error element
        const errorElement = document.createElement('div');
        errorElement.className = 'invalid-feedback';
        errorElement.textContent = message;
        
        // Insert after field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
        
        // Add error to errors object
        this.errors[field.name || field.id] = message;
    }

    /**
     * Clear field error
     */
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        
        // Remove error element
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.remove();
        }
        
        // Remove from errors object
        delete this.errors[field.name || field.id];
    }

    /**
     * Validate entire form
     */
    validateForm(form) {
        const fields = form.querySelectorAll('[data-validate], [required], [data-required]');
        let isValid = true;
        
        this.errors = {};
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            // Focus first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        return isValid;
    }

    /**
     * Get validation errors
     */
    getErrors() {
        return this.errors;
    }

    /**
     * Check if has errors
     */
    hasErrors() {
        return Object.keys(this.errors).length > 0;
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Validate file upload
     */
    validateFile(file, options = {}) {
        const errors = [];
        
        // Size validation
        if (options.maxSize && file.size > options.maxSize) {
            errors.push(`Ukuran file melebihi ${this.formatFileSize(options.maxSize)}`);
        }
        
        // Type validation
        if (options.allowedTypes && !options.allowedTypes.includes(file.type)) {
            errors.push('Tipe file tidak diizinkan');
        }
        
        // Extension validation
        if (options.allowedExtensions) {
            const extension = file.name.split('.').pop().toLowerCase();
            if (!options.allowedExtensions.includes(extension)) {
                errors.push('Ekstensi file tidak diizinkan');
            }
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Validate array of files
     */
    validateFiles(files, options = {}) {
        const results = [];
        
        for (const file of files) {
            const result = this.validateFile(file, options);
            results.push({
                file: file,
                valid: result.valid,
                errors: result.errors
            });
        }
        
        return results;
    }

    /**
     * Destroy validation
     */
    destroy() {
        // Remove event listeners
        document.removeEventListener('blur', this.validateField);
        document.removeEventListener('input', this.validateField);
        document.removeEventListener('submit', this.validateForm);
        
        // Clear errors
        this.errors = {};
        this.rules = {};
        this.customValidators = {};
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SakipValidation;
} else if (typeof window !== 'undefined') {
    window.SakipValidation = SakipValidation;
}