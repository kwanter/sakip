/**
 * SAKIP Helper Functions
 * Utility functions for date formatting, number formatting, status badges, and file handling
 */

// Date formatting utilities
const SakipDateFormatter = {
    /**
     * Format date for government reports (Indonesian format)
     */
    formatGovernmentDate(date, format = 'full') {
        const options = {
            full: { year: 'numeric', month: 'long', day: 'numeric' },
            month: { year: 'numeric', month: 'long' },
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            datetime: { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }
        };
        
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        
        if (isNaN(dateObj.getTime())) {
            return 'Tanggal tidak valid';
        }
        
        return dateObj.toLocaleDateString('id-ID', options[format] || options.full);
    },

    /**
     * Format date for input fields (YYYY-MM-DD)
     */
    formatInputDate(date) {
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        
        if (isNaN(dateObj.getTime())) {
            return '';
        }
        
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
    },

    /**
     * Get quarter from date
     */
    getQuarter(date) {
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        const month = dateObj.getMonth() + 1;
        
        return Math.ceil(month / 3);
    },

    /**
     * Get semester from date
     */
    getSemester(date) {
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        const month = dateObj.getMonth() + 1;
        
        return month <= 6 ? 1 : 2;
    },

    /**
     * Get fiscal year (April - March)
     */
    getFiscalYear(date) {
        const dateObj = typeof date === 'string' ? new Date(date) : date;
        const year = dateObj.getFullYear();
        const month = dateObj.getMonth() + 1;
        
        // Fiscal year starts in April
        return month >= 4 ? year : year - 1;
    }
};

// Number formatting utilities
const SakipNumberFormatter = {
    /**
     * Format performance percentage
     */
    formatPercentage(value, decimals = 2) {
        const num = parseFloat(value);
        
        if (isNaN(num)) {
            return '-';
        }
        
        return `${num.toFixed(decimals)}%`;
    },

    /**
     * Format currency (IDR)
     */
    formatCurrency(amount, showSymbol = true) {
        const num = parseFloat(amount);
        
        if (isNaN(num)) {
            return '-';
        }
        
        const formatted = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num);
        
        return showSymbol ? `Rp ${formatted}` : formatted;
    },

    /**
     * Format number with thousand separators
     */
    formatNumber(value, decimals = 0) {
        const num = parseFloat(value);
        
        if (isNaN(num)) {
            return '-';
        }
        
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },

    /**
     * Format target achievement
     */
    formatAchievement(target, actual) {
        const targetNum = parseFloat(target);
        const actualNum = parseFloat(actual);
        
        if (isNaN(targetNum) || isNaN(actualNum) || targetNum === 0) {
            return { percentage: 0, status: 'error' };
        }
        
        const percentage = (actualNum / targetNum) * 100;
        
        let status = 'success';
        if (percentage < 50) status = 'danger';
        else if (percentage < 80) status = 'warning';
        else if (percentage < 100) status = 'info';
        
        return {
            percentage: Math.round(percentage * 100) / 100,
            status: status
        };
    },

    /**
     * Format performance score
     */
    formatScore(score, maxScore = 100) {
        const scoreNum = parseFloat(score);
        
        if (isNaN(scoreNum)) {
            return { score: 0, grade: 'E', color: 'danger' };
        }
        
        const percentage = maxScore > 0 ? (scoreNum / maxScore) * 100 : 0;
        
        let grade = 'E';
        let color = 'danger';
        
        if (percentage >= 90) {
            grade = 'A';
            color = 'success';
        } else if (percentage >= 80) {
            grade = 'B';
            color = 'info';
        } else if (percentage >= 70) {
            grade = 'C';
            color = 'warning';
        } else if (percentage >= 60) {
            grade = 'D';
            color = 'orange';
        }
        
        return {
            score: Math.round(percentage * 100) / 100,
            grade: grade,
            color: color
        };
    }
};

// Status badge utilities
const SakipStatusBadge = {
    /**
     * Create status badge
     */
    createBadge(text, type = 'secondary', size = 'sm') {
        const colors = {
            success: 'bg-success',
            warning: 'bg-warning',
            danger: 'bg-danger',
            info: 'bg-info',
            primary: 'bg-primary',
            secondary: 'bg-secondary',
            light: 'bg-light text-dark',
            dark: 'bg-dark'
        };
        
        const sizes = {
            sm: 'badge-sm',
            md: '',
            lg: 'badge-lg'
        };
        
        return `<span class="badge ${colors[type]} ${sizes[size]}">${text}</span>`;
    },

    /**
     * Create achievement badge
     */
    createAchievementBadge(percentage) {
        const num = parseFloat(percentage);
        
        if (isNaN(num)) {
            return this.createBadge('Data Error', 'secondary');
        }
        
        if (num >= 100) {
            return this.createBadge('Tercapai', 'success');
        } else if (num >= 80) {
            return this.createBadge('Hampir Tercapai', 'info');
        } else if (num >= 60) {
            return this.createBadge('Kurang', 'warning');
        } else {
            return this.createBadge('Belum Tercapai', 'danger');
        }
    },

    /**
     * Create compliance badge
     */
    createComplianceBadge(status) {
        const badges = {
            'compliant': this.createBadge('Patuh', 'success'),
            'non_compliant': this.createBadge('Tidak Patuh', 'danger'),
            'partially_compliant': this.createBadge('Sebagian Patuh', 'warning'),
            'not_applicable': this.createBadge('Tidak Berlaku', 'secondary'),
            'pending': this.createBadge('Menunggu', 'info')
        };
        
        return badges[status] || this.createBadge('Unknown', 'secondary');
    },

    /**
     * Create assessment badge
     */
    createAssessmentBadge(score) {
        const result = SakipNumberFormatter.formatScore(score);
        return this.createBadge(`${result.score}% (${result.grade})`, result.color);
    },

    /**
     * Create priority badge
     */
    createPriorityBadge(priority) {
        const badges = {
            'low': this.createBadge('Rendah', 'success'),
            'medium': this.createBadge('Menengah', 'warning'),
            'high': this.createBadge('Tinggi', 'danger'),
            'urgent': this.createBadge('Darurat', 'danger')
        };
        
        return badges[priority] || this.createBadge('Unknown', 'secondary');
    },

    /**
     * Create status badge for data collection
     */
    createDataStatusBadge(status) {
        const badges = {
            'draft': this.createBadge('Draft', 'secondary'),
            'submitted': this.createBadge('Dikirim', 'info'),
            'verified': this.createBadge('Terverifikasi', 'success'),
            'rejected': this.createBadge('Ditolak', 'danger'),
            'pending': this.createBadge('Menunggu', 'warning')
        };
        
        return badges[status] || this.createBadge('Unknown', 'secondary');
    }
};

// File upload utilities
const SakipFileUpload = {
    /**
     * Validate file upload
     */
    validateFile(file, allowedTypes, maxSize) {
        const errors = [];
        
        // Check file type
        if (allowedTypes && allowedTypes.length > 0) {
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const mimeType = file.type;
            
            const isAllowed = allowedTypes.some(type => {
                if (type.startsWith('.')) {
                    return fileExtension === type.slice(1);
                }
                return mimeType.includes(type) || fileExtension === type;
            });
            
            if (!isAllowed) {
                errors.push(`Tipe file tidak diizinkan. Tipe yang diizinkan: ${allowedTypes.join(', ')}`);
            }
        }
        
        // Check file size
        if (maxSize && file.size > maxSize) {
            const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(2);
            errors.push(`Ukuran file melebihi batas maksimum ${maxSizeMB} MB`);
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    },

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    /**
     * Get file icon
     */
    getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        
        const iconMap = {
            'pdf': 'fas fa-file-pdf text-danger',
            'doc': 'fas fa-file-word text-primary',
            'docx': 'fas fa-file-word text-primary',
            'xls': 'fas fa-file-excel text-success',
            'xlsx': 'fas fa-file-excel text-success',
            'ppt': 'fas fa-file-powerpoint text-warning',
            'pptx': 'fas fa-file-powerpoint text-warning',
            'jpg': 'fas fa-file-image text-info',
            'jpeg': 'fas fa-file-image text-info',
            'png': 'fas fa-file-image text-info',
            'zip': 'fas fa-file-archive text-secondary',
            'rar': 'fas fa-file-archive text-secondary',
            'txt': 'fas fa-file-alt text-muted'
        };
        
        return iconMap[extension] || 'fas fa-file text-muted';
    },

    /**
     * Create file preview
     */
    createFilePreview(file, options = {}) {
        const { showSize = true, showIcon = true, removable = false } = options;
        
        const preview = document.createElement('div');
        preview.className = 'file-preview-item';
        preview.dataset.filename = file.name;
        
        let content = '';
        
        if (showIcon) {
            const iconClass = this.getFileIcon(file.name);
            content += `<i class="${iconClass}"></i> `;
        }
        
        content += `<span class="file-name">${file.name}</span>`;
        
        if (showSize) {
            content += ` <span class="file-size text-muted">(${this.formatFileSize(file.size)})</span>`;
        }
        
        if (removable) {
            content += ' <button type="button" class="btn btn-sm btn-link text-danger remove-file" title="Hapus file">';
            content += '<i class="fas fa-times"></i></button>';
        }
        
        preview.innerHTML = content;
        
        return preview;
    },

    /**
     * Handle drag and drop
     */
    setupDragAndDrop(dropZone, callback, options = {}) {
        const { allowedTypes, maxSize, multiple = true } = options;
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight() {
            dropZone.classList.add('drag-over');
        }
        
        function unhighlight() {
            dropZone.classList.remove('drag-over');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = multiple ? [...dt.files] : [dt.files[0]];
            
            handleFiles(files);
        }
        
        function handleFiles(files) {
            const validFiles = [];
            const errors = [];
            
            files.forEach(file => {
                const validation = SakipFileUpload.validateFile(file, allowedTypes, maxSize);
                
                if (validation.valid) {
                    validFiles.push(file);
                } else {
                    errors.push({ file: file.name, errors: validation.errors });
                }
            });
            
            if (validFiles.length > 0) {
                callback(validFiles, errors);
            } else if (errors.length > 0) {
                // Show validation errors
                const errorMessages = errors.map(e => `${e.file}: ${e.errors.join(', ')}`).join('\n');
                alert(`Validasi file gagal:\n${errorMessages}`);
            }
        }
    }
};

// Export utilities for use in other modules
window.SakipHelpers = {
    DateFormatter: SakipDateFormatter,
    NumberFormatter: SakipNumberFormatter,
    StatusBadge: SakipStatusBadge,
    FileUpload: SakipFileUpload
};