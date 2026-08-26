/**
 * SAKIP Data Collection JavaScript Module
 * Handles data collection forms, validation, and bulk import
 */

class SakipDataCollection {
    constructor() {
        this.validationRules = {};
        this.uploadedFiles = new Map();
        this.init();
    }

    /**
     * Initialize data collection functionality
     */
    init() {
        this.initializeFormValidation();
        this.setupFileUpload();
        this.setupBulkImport();
        this.setupDynamicValidation();
        this.initializeDatePickers();
    }

    /**
     * Initialize form validation
     */
    initializeFormValidation() {
        const forms = document.querySelectorAll('.sakip-data-form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showValidationErrors(form);
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                
                input.addEventListener('input', () => {
                    this.clearFieldError(input);
                });
            });
        });
    }

    /**
     * Validate entire form
     */
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        const rules = this.getFieldRules(field);
        let isValid = true;
        
        // Required validation
        if (rules.required && !field.value.trim()) {
            this.showFieldError(field, 'Field ini wajib diisi');
            isValid = false;
        }
        
        // Numeric validation
        if (rules.numeric && field.value && isNaN(field.value)) {
            this.showFieldError(field, 'Field ini harus berupa angka');
            isValid = false;
        }
        
        // Range validation
        if (rules.min && field.value && parseFloat(field.value) < rules.min) {
            this.showFieldError(field, `Nilai minimal adalah ${rules.min}`);
            isValid = false;
        }
        
        if (rules.max && field.value && parseFloat(field.value) > rules.max) {
            this.showFieldError(field, `Nilai maksimal adalah ${rules.max}`);
            isValid = false;
        }
        
        // Date validation
        if (rules.date && field.value && !this.isValidDate(field.value)) {
            this.showFieldError(field, 'Format tanggal tidak valid');
            isValid = false;
        }
        
        // Custom validation
        if (rules.custom && !rules.custom(field.value)) {
            this.showFieldError(field, rules.customMessage || 'Validasi gagal');
            isValid = false;
        }
        
        if (isValid) {
            this.clearFieldError(field);
        }
        
        return isValid;
    }

    /**
     * Get validation rules for field
     */
    getFieldRules(field) {
        const rules = {};
        
        if (field.hasAttribute('required')) {
            rules.required = true;
        }
        
        if (field.classList.contains('numeric-input')) {
            rules.numeric = true;
        }
        
        if (field.dataset.min) {
            rules.min = parseFloat(field.dataset.min);
        }
        
        if (field.dataset.max) {
            rules.max = parseFloat(field.dataset.max);
        }
        
        if (field.classList.contains('date-input')) {
            rules.date = true;
        }
        
        // Custom validation functions
        if (field.dataset.validation) {
            rules.custom = this.getCustomValidation(field.dataset.validation);
            rules.customMessage = field.dataset.validationMessage;
        }
        
        return rules;
    }

    /**
     * Get custom validation function
     */
    getCustomValidation(validationName) {
        const validations = {
            'performance-indicator': (value) => {
                // Check if performance indicator exists
                return this.validatePerformanceIndicator(value);
            },
            'target-achievement': (value, field) => {
                // Validate target achievement percentage
                const target = parseFloat(field.dataset.targetValue || '0');
                return target > 0 && value >= 0;
            },
            'evidence-required': (value, field) => {
                // Check if evidence is required for this field
                return this.hasRequiredEvidence(field);
            }
        };
        
        return validations[validationName] || null;
    }

    /**
     * Setup file upload functionality
     */
    setupFileUpload() {
        const uploadAreas = document.querySelectorAll('.file-upload-area');
        
        uploadAreas.forEach(area => {
            const fileInput = area.querySelector('input[type="file"]');
            const dropZone = area.querySelector('.drop-zone') || area;
            
            // Click to upload
            dropZone.addEventListener('click', () => {
                fileInput.click();
            });
            
            // Drag and drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            
            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('drag-over');
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                
                const files = Array.from(e.dataTransfer.files);
                this.handleFileSelection(files, fileInput);
            });
            
            // File input change
            fileInput.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                this.handleFileSelection(files, fileInput);
            });
        });
    }

    /**
     * Handle file selection
     */
    handleFileSelection(files, input) {
        const maxSize = parseInt(input.dataset.maxSize || '10485760'); // 10MB default
        const allowedTypes = (input.dataset.allowedTypes || 'pdf,jpg,jpeg,png,doc,docx,xlsx').split(',');
        
        const validFiles = [];
        const errors = [];
        
        files.forEach(file => {
            // Check file size
            if (file.size > maxSize) {
                errors.push(`${file.name}: Ukuran file melebihi batas ${maxSize / 1048576}MB`);
                return;
            }
            
            // Check file type
            const extension = file.name.split('.').pop().toLowerCase();
            if (!allowedTypes.includes(extension)) {
                errors.push(`${file.name}: Tipe file tidak diizinkan. Hanya ${allowedTypes.join(', ')} yang diizinkan`);
                return;
            }
            
            validFiles.push(file);
        });
        
        if (errors.length > 0) {
            this.showNotification(errors.join('\n'), 'error');
        }
        
        if (validFiles.length > 0) {
            this.processFiles(validFiles, input);
        }
    }

    /**
     * Process uploaded files
     */
    async processFiles(files, input) {
        const container = input.closest('.file-upload-area');
        const previewContainer = container.querySelector('.file-preview') || container;
        
        for (const file of files) {
            try {
                const fileId = this.generateFileId();
                const previewElement = this.createFilePreview(file, fileId);
                
                previewContainer.appendChild(previewElement);
                
                // Upload file
                const uploadedFile = await this.uploadFile(file, input.dataset.uploadUrl);
                
                // Store file reference
                this.uploadedFiles.set(fileId, uploadedFile);
                
                // Update preview with success state
                this.updateFilePreview(fileId, 'success', uploadedFile);
                
            } catch (error) {
                console.error('Error uploading file:', error);
                this.updateFilePreview(this.generateFileId(), 'error', null, error.message);
            }
        }
    }

    /**
     * Upload file to server
     */
    async uploadFile(file, uploadUrl) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        const response = await fetch(uploadUrl || '/sakip/api/evidence/upload', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Upload gagal');
        }
        
        return await response.json();
    }

    /**
     * Create file preview element
     */
    createFilePreview(file, fileId) {
        const preview = document.createElement('div');
        preview.className = 'file-preview-item';
        preview.id = `preview-${fileId}`;
        preview.innerHTML = `
            <div class="file-info">
                <i class="fas fa-file"></i>
                <span class="file-name">${file.name}</span>
                <span class="file-size">${this.formatFileSize(file.size)}</span>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-sm btn-danger remove-file" data-file-id="${fileId}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="upload-progress">
                <div class="progress">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
            </div>
        `;
        
        // Add remove functionality
        preview.querySelector('.remove-file').addEventListener('click', () => {
            this.removeFile(fileId);
        });
        
        return preview;
    }

    /**
     * Update file preview state
     */
    updateFilePreview(fileId, state, fileData = null, errorMessage = null) {
        const preview = document.getElementById(`preview-${fileId}`);
        if (!preview) return;
        
        const progressBar = preview.querySelector('.progress-bar');
        
        switch (state) {
            case 'uploading':
                progressBar.style.width = '50%';
                progressBar.className = 'progress-bar progress-bar-animated';
                break;
                
            case 'success':
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-success';
                
                if (fileData) {
                    const fileInfo = preview.querySelector('.file-info');
                    fileInfo.innerHTML = `
                        <i class="fas fa-check-circle text-success"></i>
                        <span class="file-name">${fileData.name}</span>
                        <span class="file-size">${this.formatFileSize(fileData.size)}</span>
                    `;
                }
                
                // Hide progress after success
                setTimeout(() => {
                    preview.querySelector('.upload-progress').style.display = 'none';
                }, 1000);
                break;
                
            case 'error':
                progressBar.style.width = '100%';
                progressBar.className = 'progress-bar bg-danger';
                
                const fileInfo = preview.querySelector('.file-info');
                fileInfo.innerHTML = `
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <span class="file-name">${errorMessage || 'Upload gagal'}</span>
                `;
                break;
        }
    }

    /**
     * Setup bulk import functionality
     */
    setupBulkImport() {
        const importArea = document.getElementById('bulkImportArea');
        if (!importArea) return;
        
        const fileInput = importArea.querySelector('input[type="file"]');
        const dropZone = importArea.querySelector('.drop-zone') || importArea;
        
        // Template download
        const downloadTemplateBtn = document.getElementById('downloadTemplate');
        if (downloadTemplateBtn) {
            downloadTemplateBtn.addEventListener('click', () => {
                this.downloadImportTemplate();
            });
        }
        
        // File upload handlers
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            const files = Array.from(e.dataTransfer.files);
            this.processImportFile(files[0]);
        });
        
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.processImportFile(file);
            }
        });
    }

    /**
     * Process import file
     */
    async processImportFile(file) {
        if (!file.name.endsWith('.xlsx') && !file.name.endsWith('.xls')) {
            this.showNotification('Format file tidak valid. Gunakan file Excel (.xlsx atau .xls)', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        try {
            this.showImportProgress(0);
            
            const response = await fetch('/sakip/api/data-collection/import', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showImportResults(result);
                this.showNotification(`Import berhasil: ${result.imported} data berhasil diimport`, 'success');
            } else {
                this.showNotification(`Import gagal: ${result.message}`, 'error');
            }
            
        } catch (error) {
            console.error('Import error:', error);
            this.showNotification('Terjadi kesalahan saat import data', 'error');
        }
    }

    /**
     * Setup dynamic validation
     */
    setupDynamicValidation() {
        // Performance indicator change
        document.querySelectorAll('.indicator-select').forEach(select => {
            select.addEventListener('change', (e) => {
                this.updateIndicatorValidation(e.target);
            });
        });
        
        // Period change validation
        document.querySelectorAll('.period-input').forEach(input => {
            input.addEventListener('change', (e) => {
                this.validatePeriod(e.target);
            });
        });
        
        // Target vs actual validation
        document.querySelectorAll('.target-input, .actual-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.validateTargetVsActual(e.target);
            });
        });
    }

    /**
     * Update indicator validation
     */
    updateIndicatorValidation(indicatorSelect) {
        const indicatorId = indicatorSelect.value;
        if (!indicatorId) return;
        
        // Fetch indicator details
        fetch(`/sakip/api/indicators/${indicatorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.applyIndicatorValidation(data.indicator);
                }
            })
            .catch(error => {
                console.error('Error fetching indicator:', error);
            });
    }

    /**
     * Apply indicator-specific validation
     */
    applyIndicatorValidation(indicator) {
        const actualInput = document.querySelector('.actual-input');
        if (!actualInput) return;
        
        // Apply measurement unit validation
        if (indicator.measurement_unit === 'percentage') {
            actualInput.dataset.max = '100';
            actualInput.dataset.validation = 'percentage';
        } else if (indicator.measurement_unit === 'ratio') {
            actualInput.dataset.validation = 'ratio';
        }
        
        // Apply calculation formula validation
        if (indicator.calculation_formula) {
            actualInput.dataset.formula = indicator.calculation_formula;
        }
        
        // Update field help text
        const helpText = actualInput.parentElement.querySelector('.form-text');
        if (helpText) {
            helpText.textContent = `Satuan: ${indicator.measurement_unit}`;
        }
    }

    /**
     * Initialize date pickers
     */
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('.date-input');
        
        dateInputs.forEach(input => {
            // Use flatpickr or native date picker
            if (typeof flatpickr !== 'undefined') {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    locale: 'id'
                });
            }
        });
    }

    /**
     * Utility functions
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    generateFileId() {
        return 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    isValidDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    }

    showNotification(message, type = 'info') {
        // Implementation similar to dashboard notification
        console.log(`[${type.toUpperCase()}] ${message}`);
    }

    showFieldError(field, message) {
        const errorElement = field.parentElement.querySelector('.invalid-feedback') || 
                           field.parentElement.querySelector('.field-error');
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        field.classList.add('is-invalid');
    }

    clearFieldError(field) {
        const errorElement = field.parentElement.querySelector('.invalid-feedback') || 
                           field.parentElement.querySelector('.field-error');
        
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
        
        field.classList.remove('is-invalid');
    }
}

// Initialize data collection when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.sakipDataCollection = new SakipDataCollection();
});