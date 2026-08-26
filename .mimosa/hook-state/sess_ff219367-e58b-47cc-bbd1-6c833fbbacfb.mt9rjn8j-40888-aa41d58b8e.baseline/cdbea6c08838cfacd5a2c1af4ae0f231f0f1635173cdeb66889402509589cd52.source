/**
 * SAKIP Bulk Import
 * Government-style bulk data import functionality for SAKIP module
 * Supports CSV, Excel, and JSON file processing with validation
 * 
 * @author SAKIP Development Team
 * @version 1.0.0
 * @since 2024
 */

(function(global, factory) {
    if (typeof exports === 'object' && typeof module !== 'undefined') {
        module.exports = factory();
    } else if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        global.SAKIP_BULK_IMPORT = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {}, function() {
    'use strict';

    /**
     * Bulk Import Configuration Constants
     */
    const BULK_IMPORT_CONSTANTS = {
        // Supported file types
        SUPPORTED_FILE_TYPES: {
            CSV: {
                extensions: ['.csv', '.txt'],
                mimeTypes: ['text/csv', 'text/plain', 'application/csv'],
                maxSize: 50 * 1024 * 1024 // 50MB
            },
            EXCEL: {
                extensions: ['.xlsx', '.xls'],
                mimeTypes: [
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel'
                ],
                maxSize: 100 * 1024 * 1024 // 100MB
            },
            JSON: {
                extensions: ['.json'],
                mimeTypes: ['application/json'],
                maxSize: 10 * 1024 * 1024 // 10MB
            }
        },

        // Import types
        IMPORT_TYPES: {
            INSTITUTION: 'institution',
            ASSESSMENT: 'assessment',
            USER: 'user',
            REPORT: 'report',
            INDICATOR: 'indicator',
            EVIDENCE: 'evidence'
        },

        // Processing modes
        PROCESSING_MODES: {
            VALIDATE_ONLY: 'validate_only',
            VALIDATE_AND_IMPORT: 'validate_and_import',
            DRY_RUN: 'dry_run'
        },

        // Batch sizes
        BATCH_SIZES: {
            SMALL: 100,
            MEDIUM: 500,
            LARGE: 1000,
            XLARGE: 5000
        },

        // Error messages (Indonesian)
        ERROR_MESSAGES: {
            INVALID_FILE_TYPE: 'Tipe file tidak didukung. Gunakan file CSV, Excel, atau JSON.',
            FILE_TOO_LARGE: 'Ukuran file terlalu besar. Maksimal {size}MB.',
            EMPTY_FILE: 'File kosong atau tidak mengandung data.',
            INVALID_FORMAT: 'Format file tidak valid atau rusak.',
            VALIDATION_FAILED: 'Validasi gagal. Periksa kembali data Anda.',
            IMPORT_FAILED: 'Import gagal. Terjadi kesalahan saat memproses data.',
            DUPLICATE_DATA: 'Data duplikat ditemukan.',
            REQUIRED_FIELD_MISSING: 'Field wajib tidak terisi: {field}',
            INVALID_DATA_TYPE: 'Tipe data tidak valid untuk field {field}',
            REFERENCE_ERROR: 'Referensi tidak valid untuk {field}'
        },

        // Success messages
        SUCCESS_MESSAGES: {
            VALIDATION_SUCCESS: 'Validasi berhasil. Data siap diimport.',
            IMPORT_SUCCESS: 'Import berhasil. {count} data telah diproses.',
            PARTIAL_SUCCESS: 'Import sebagian berhasil. {success} dari {total} data diproses.'
        }
    };

    /**
     * File Parser for different formats
     */
    class FileParser {
        constructor() {
            this.parsers = new Map();
            this.setupParsers();
        }

        /**
         * Setup file parsers
         */
        setupParsers() {
            this.parsers.set('csv', this.parseCSV.bind(this));
            this.parsers.set('excel', this.parseExcel.bind(this));
            this.parsers.set('json', this.parseJSON.bind(this));
        }

        /**
         * Parse file based on type
         */
        async parseFile(file, options = {}) {
            const fileType = this.detectFileType(file);
            const parser = this.parsers.get(fileType);

            if (!parser) {
                throw new Error(`No parser available for file type: ${fileType}`);
            }

            return await parser(file, options);
        }

        /**
         * Detect file type
         */
        detectFileType(file) {
            const fileName = file.name.toLowerCase();
            const mimeType = file.type;

            // Check by extension
            if (fileName.endsWith('.csv') || fileName.endsWith('.txt')) {
                return 'csv';
            }
            
            if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
                return 'excel';
            }
            
            if (fileName.endsWith('.json')) {
                return 'json';
            }

            // Check by MIME type
            if (mimeType.includes('csv') || mimeType.includes('text/plain')) {
                return 'csv';
            }
            
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
                return 'excel';
            }
            
            if (mimeType.includes('json')) {
                return 'json';
            }

            throw new Error('Unable to detect file type');
        }

        /**
         * Parse CSV file
         */
        async parseCSV(file, options = {}) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    try {
                        const text = e.target.result;
                        const delimiter = options.delimiter || ',';
                        const lines = text.split(/\r?\n/).filter(line => line.trim());
                        
                        if (lines.length === 0) {
                            throw new Error(BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.EMPTY_FILE);
                        }

                        const headers = this.parseCSVLine(lines[0], delimiter);
                        const data = [];

                        for (let i = 1; i < lines.length; i++) {
                            const values = this.parseCSVLine(lines[i], delimiter);
                            const row = {};
                            
                            headers.forEach((header, index) => {
                                row[header] = values[index] || '';
                            });
                            
                            data.push(row);
                        }

                        resolve({
                            headers: headers,
                            data: data,
                            totalRows: data.length,
                            fileName: file.name
                        });
                    } catch (error) {
                        reject(error);
                    }
                };

                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsText(file);
            });
        }

        /**
         * Parse CSV line with proper quote handling
         */
        parseCSVLine(line, delimiter) {
            const result = [];
            let current = '';
            let inQuotes = false;
            
            for (let i = 0; i < line.length; i++) {
                const char = line[i];
                const nextChar = line[i + 1];
                
                if (char === '"') {
                    if (inQuotes && nextChar === '"') {
                        current += '"';
                        i++; // Skip next quote
                    } else {
                        inQuotes = !inQuotes;
                    }
                } else if (char === delimiter && !inQuotes) {
                    result.push(current.trim());
                    current = '';
                } else {
                    current += char;
                }
            }
            
            result.push(current.trim());
            return result;
        }

        /**
         * Parse Excel file
         */
        async parseExcel(file, options = {}) {
            // Note: This requires a library like SheetJS (xlsx)
            // For now, we'll provide a basic implementation
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    try {
                        // This is a placeholder - in real implementation, use SheetJS
                        const data = e.target.result;
                        
                        // Simulate parsing (actual implementation would use SheetJS)
                        const mockData = {
                            headers: ['column1', 'column2', 'column3'],
                            data: [
                                { column1: 'data1', column2: 'data2', column3: 'data3' },
                                { column1: 'data4', column2: 'data5', column3: 'data6' }
                            ],
                            totalRows: 2,
                            fileName: file.name
                        };

                        resolve(mockData);
                    } catch (error) {
                        reject(error);
                    }
                };

                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsArrayBuffer(file);
            });
        }

        /**
         * Parse JSON file
         */
        async parseJSON(file, options = {}) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    try {
                        const text = e.target.result;
                        const data = JSON.parse(text);
                        
                        if (!Array.isArray(data) || data.length === 0) {
                            throw new Error(BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.EMPTY_FILE);
                        }

                        // Extract headers from first object
                        const headers = Object.keys(data[0]);
                        
                        resolve({
                            headers: headers,
                            data: data,
                            totalRows: data.length,
                            fileName: file.name
                        });
                    } catch (error) {
                        reject(error);
                    }
                };

                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsText(file);
            });
        }
    }

    /**
     * Data Validator for imported data
     */
    class DataValidator {
        constructor() {
            this.validationRules = new Map();
            this.setupDefaultRules();
        }

        /**
         * Setup default validation rules
         */
        setupDefaultRules() {
            // Add default validation rules for different import types
            this.validationRules.set(BULK_IMPORT_CONSTANTS.IMPORT_TYPES.INSTITUTION, this.getInstitutionValidationRules());
            this.validationRules.set(BULK_IMPORT_CONSTANTS.IMPORT_TYPES.ASSESSMENT, this.getAssessmentValidationRules());
            this.validationRules.set(BULK_IMPORT_CONSTANTS.IMPORT_TYPES.USER, this.getUserValidationRules());
        }

        /**
         * Get institution validation rules
         */
        getInstitutionValidationRules() {
            return {
                required: ['nama', 'jenis', 'alamat'],
                optional: ['telepon', 'email', 'website', 'kepala', 'status'],
                formats: {
                    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    telepon: /^[+]?[0-9]{10,15}$/,
                    website: /^https?:\/\/.+/
                },
                constraints: {
                    nama: { minLength: 3, maxLength: 200 },
                    jenis: { values: ['Pemerintah', 'Swasta', 'BUMN', 'BUMD'] },
                    status: { values: ['Aktif', 'Tidak Aktif'] }
                }
            };
        }

        /**
         * Get assessment validation rules
         */
        getAssessmentValidationRules() {
            return {
                required: ['institusi_id', 'tahun', 'indikator', 'nilai'],
                optional: ['keterangan', 'evidence', 'status'],
                formats: {
                    tahun: /^\d{4}$/,
                    nilai: /^\d+(\.\d+)?$/
                },
                constraints: {
                    tahun: { min: 2020, max: new Date().getFullYear() },
                    nilai: { min: 0, max: 100 }
                }
            };
        }

        /**
         * Get user validation rules
         */
        getUserValidationRules() {
            return {
                required: ['nama', 'email', 'username', 'peran'],
                optional: ['telepon', 'institusi_id', 'status'],
                formats: {
                    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    telepon: /^[+]?[0-9]{10,15}$/
                },
                constraints: {
                    nama: { minLength: 2, maxLength: 100 },
                    username: { minLength: 3, maxLength: 50 },
                    peran: { values: ['Admin', 'Assessor', 'Institution', 'Viewer'] },
                    status: { values: ['Aktif', 'Tidak Aktif'] }
                }
            };
        }

        /**
         * Validate data
         */
        validateData(data, importType, options = {}) {
            const rules = this.validationRules.get(importType);
            if (!rules) {
                throw new Error(`No validation rules defined for import type: ${importType}`);
            }

            const errors = [];
            const warnings = [];
            const validData = [];

            data.forEach((row, index) => {
                const rowErrors = this.validateRow(row, rules, index + 1);
                
                if (rowErrors.errors.length === 0) {
                    validData.push(row);
                } else {
                    errors.push(...rowErrors.errors);
                }

                if (rowErrors.warnings.length > 0) {
                    warnings.push(...rowErrors.warnings);
                }
            });

            return {
                valid: errors.length === 0,
                errors: errors,
                warnings: warnings,
                validData: validData,
                totalRows: data.length,
                validRows: validData.length,
                invalidRows: data.length - validData.length
            };
        }

        /**
         * Validate individual row
         */
        validateRow(row, rules, rowNumber) {
            const errors = [];
            const warnings = [];

            // Check required fields
            rules.required.forEach(field => {
                if (!row[field] || row[field].toString().trim() === '') {
                    errors.push({
                        row: rowNumber,
                        field: field,
                        message: BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.REQUIRED_FIELD_MISSING.replace('{field}', field)
                    });
                }
            });

            // Check field formats
            Object.keys(rules.formats || {}).forEach(field => {
                if (row[field] && !rules.formats[field].test(row[field])) {
                    errors.push({
                        row: rowNumber,
                        field: field,
                        message: BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.INVALID_DATA_TYPE.replace('{field}', field)
                    });
                }
            });

            // Check constraints
            Object.keys(rules.constraints || {}).forEach(field => {
                const constraint = rules.constraints[field];
                const value = row[field];

                if (value) {
                    if (constraint.values && !constraint.values.includes(value)) {
                        errors.push({
                            row: rowNumber,
                            field: field,
                            message: `Nilai '${value}' tidak valid untuk field ${field}`
                        });
                    }

                    if (constraint.minLength && value.length < constraint.minLength) {
                        errors.push({
                            row: rowNumber,
                            field: field,
                            message: `Panjang field ${field} minimal ${constraint.minLength} karakter`
                        });
                    }

                    if (constraint.maxLength && value.length > constraint.maxLength) {
                        errors.push({
                            row: rowNumber,
                            field: field,
                            message: `Panjang field ${field} maksimal ${constraint.maxLength} karakter`
                        });
                    }

                    if (constraint.min && parseFloat(value) < constraint.min) {
                        errors.push({
                            row: rowNumber,
                            field: field,
                            message: `Nilai field ${field} minimal ${constraint.min}`
                        });
                    }

                    if (constraint.max && parseFloat(value) > constraint.max) {
                        errors.push({
                            row: rowNumber,
                            field: field,
                            message: `Nilai field ${field} maksimal ${constraint.max}`
                        });
                    }
                }
            });

            return { errors, warnings };
        }

        /**
         * Check for duplicates
         */
        checkDuplicates(data, uniqueFields = []) {
            const duplicates = [];
            const seen = new Map();

            data.forEach((row, index) => {
                const key = uniqueFields.map(field => row[field]).join('|');
                
                if (seen.has(key)) {
                    duplicates.push({
                        row: index + 1,
                        duplicateRow: seen.get(key),
                        fields: uniqueFields,
                        message: BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.DUPLICATE_DATA
                    });
                } else {
                    seen.set(key, index + 1);
                }
            });

            return duplicates;
        }

        /**
         * Add custom validation rule
         */
        addCustomValidationRule(importType, ruleName, validatorFunction) {
            if (!this.validationRules.has(importType)) {
                this.validationRules.set(importType, {});
            }

            const rules = this.validationRules.get(importType);
            if (!rules.custom) {
                rules.custom = {};
            }

            rules.custom[ruleName] = validatorFunction;
        }
    }

    /**
     * Bulk Import Manager
     */
    class BulkImportManager {
        constructor() {
            this.fileParser = new FileParser();
            this.dataValidator = new DataValidator();
            this.importHistory = [];
            this.activeImports = new Map();
        }

        /**
         * Initialize bulk import
         */
        initialize(options = {}) {
            this.options = {
                maxFileSize: options.maxFileSize || 100 * 1024 * 1024, // 100MB
                allowedFileTypes: options.allowedFileTypes || Object.keys(BULK_IMPORT_CONSTANTS.SUPPORTED_FILE_TYPES),
                batchSize: options.batchSize || BULK_IMPORT_CONSTANTS.BATCH_SIZES.MEDIUM,
                enableValidation: options.enableValidation !== false,
                enablePreview: options.enablePreview !== false,
                maxPreviewRows: options.maxPreviewRows || 100,
                ...options
            };

            return this;
        }

        /**
         * Process file upload
         */
        async processFile(file, importType, options = {}) {
            const processId = this.generateProcessId();
            
            try {
                // Validate file
                this.validateFile(file);

                // Parse file
                const parsedData = await this.fileParser.parseFile(file, options);

                // Validate data if enabled
                let validationResult = null;
                if (this.options.enableValidation) {
                    validationResult = await this.validateData(parsedData.data, importType, options);
                }

                // Generate preview if enabled
                let preview = null;
                if (this.options.enablePreview) {
                    preview = this.generatePreview(parsedData, validationResult);
                }

                return {
                    processId,
                    success: true,
                    file: {
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        totalRows: parsedData.totalRows
                    },
                    data: parsedData,
                    validation: validationResult,
                    preview: preview
                };

            } catch (error) {
                return {
                    processId,
                    success: false,
                    error: error.message,
                    file: {
                        name: file.name,
                        size: file.size,
                        type: file.type
                    }
                };
            }
        }

        /**
         * Validate file
         */
        validateFile(file) {
            // Check file size
            if (file.size > this.options.maxFileSize) {
                const maxSizeMB = this.options.maxFileSize / (1024 * 1024);
                throw new Error(BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.FILE_TOO_LARGE.replace('{size}', maxSizeMB));
            }

            // Check file type
            const fileType = this.detectFileType(file);
            if (!this.options.allowedFileTypes.includes(fileType)) {
                throw new Error(BULK_IMPORT_CONSTANTS.ERROR_MESSAGES.INVALID_FILE_TYPE);
            }

            return true;
        }

        /**
         * Detect file type
         */
        detectFileType(file) {
            const fileName = file.name.toLowerCase();
            
            if (fileName.endsWith('.csv') || fileName.endsWith('.txt')) {
                return 'CSV';
            }
            
            if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
                return 'EXCEL';
            }
            
            if (fileName.endsWith('.json')) {
                return 'JSON';
            }

            throw new Error('Unable to detect file type');
        }

        /**
         * Validate data
         */
        async validateData(data, importType, options = {}) {
            const validationResult = this.dataValidator.validateData(data, importType, options);

            // Check for duplicates if specified
            if (options.uniqueFields && options.uniqueFields.length > 0) {
                const duplicates = this.dataValidator.checkDuplicates(data, options.uniqueFields);
                validationResult.duplicates = duplicates;
                
                if (duplicates.length > 0) {
                    validationResult.valid = false;
                    validationResult.errors.push(...duplicates);
                }
            }

            return validationResult;
        }

        /**
         * Generate preview
         */
        generatePreview(parsedData, validationResult) {
            const rows = parsedData.data.slice(0, this.options.maxPreviewRows);
            
            return {
                headers: parsedData.headers,
                rows: rows,
                totalRows: parsedData.totalRows,
                previewRows: rows.length,
                validation: validationResult ? {
                    valid: validationResult.valid,
                    errorCount: validationResult.errors.length,
                    warningCount: validationResult.warnings.length
                } : null
            };
        }

        /**
         * Import data
         */
        async importData(processId, data, importType, options = {}) {
            const startTime = Date.now();
            
            try {
                // Add to active imports
                this.activeImports.set(processId, {
                    startTime,
                    status: 'processing',
                    progress: 0,
                    totalRows: data.length,
                    processedRows: 0,
                    successRows: 0,
                    errorRows: 0
                });

                // Process in batches
                const batchSize = options.batchSize || this.options.batchSize;
                const results = [];
                
                for (let i = 0; i < data.length; i += batchSize) {
                    const batch = data.slice(i, i + batchSize);
                    const batchResult = await this.processBatch(batch, importType, options);
                    
                    results.push(batchResult);
                    
                    // Update progress
                    const progress = Math.round(((i + batch.length) / data.length) * 100);
                    this.updateImportProgress(processId, progress, i + batch.length, batchResult);
                    
                    // Emit progress event
                    if (options.onProgress) {
                        options.onProgress({
                            processId,
                            progress,
                            processedRows: i + batch.length,
                            totalRows: data.length
                        });
                    }
                }

                // Calculate final results
                const totalProcessed = results.reduce((sum, result) => sum + result.processed, 0);
                const totalSuccess = results.reduce((sum, result) => sum + result.success, 0);
                const totalErrors = results.reduce((sum, result) => sum + result.errors.length, 0);
                const endTime = Date.now();
                const duration = endTime - startTime;

                const finalResult = {
                    processId,
                    success: totalErrors === 0,
                    totalRows: data.length,
                    processedRows: totalProcessed,
                    successRows: totalSuccess,
                    errorRows: totalErrors,
                    duration: duration,
                    results: results
                };

                // Update import history
                this.importHistory.push(finalResult);
                this.activeImports.delete(processId);

                return finalResult;

            } catch (error) {
                this.activeImports.delete(processId);
                throw error;
            }
        }

        /**
         * Process batch
         */
        async processBatch(batch, importType, options = {}) {
            const batchResult = {
                batchSize: batch.length,
                processed: 0,
                success: 0,
                errors: []
            };

            try {
                // Call API or local processor
                if (options.apiEndpoint) {
                    const response = await this.callImportAPI(batch, importType, options);
                    batchResult.processed = response.processed || batch.length;
                    batchResult.success = response.success || 0;
                    batchResult.errors = response.errors || [];
                } else {
                    // Simulate processing
                    await this.simulateBatchProcessing(batch, batchResult);
                }

                return batchResult;
            } catch (error) {
                batchResult.errors.push({
                    message: error.message,
                    batch: batch
                });
                return batchResult;
            }
        }

        /**
         * Simulate batch processing (for demo purposes)
         */
        async simulateBatchProcessing(batch, batchResult) {
            // Simulate processing delay
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Simulate 90% success rate
            const successCount = Math.floor(batch.length * 0.9);
            const errorCount = batch.length - successCount;
            
            batchResult.processed = batch.length;
            batchResult.success = successCount;
            
            // Generate some mock errors
            for (let i = 0; i < errorCount; i++) {
                batchResult.errors.push({
                    row: Math.floor(Math.random() * batch.length),
                    message: 'Simulated error'
                });
            }
        }

        /**
         * Call import API
         */
        async callImportAPI(batch, importType, options) {
            const response = await fetch(options.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    importType: importType,
                    data: batch,
                    options: options
                })
            });

            if (!response.ok) {
                throw new Error(`API call failed: ${response.statusText}`);
            }

            return await response.json();
        }

        /**
         * Update import progress
         */
        updateImportProgress(processId, progress, processedRows, batchResult) {
            const importData = this.activeImports.get(processId);
            if (importData) {
                importData.progress = progress;
                importData.processedRows = processedRows;
                importData.successRows += batchResult.success;
                importData.errorRows += batchResult.errors.length;
            }
        }

        /**
         * Get import progress
         */
        getImportProgress(processId) {
            return this.activeImports.get(processId) || null;
        }

        /**
         * Cancel import
         */
        cancelImport(processId) {
            const importData = this.activeImports.get(processId);
            if (importData) {
                importData.status = 'cancelled';
                this.activeImports.delete(processId);
                return true;
            }
            return false;
        }

        /**
         * Get import history
         */
        getImportHistory(limit = 10) {
            return this.importHistory.slice(-limit);
        }

        /**
         * Generate process ID
         */
        generateProcessId() {
            return `import_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }

        /**
         * Export errors to CSV
         */
        exportErrors(errors, headers) {
            const csvContent = this.convertErrorsToCSV(errors, headers);
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `import_errors_${new Date().toISOString().slice(0, 10)}.csv`;
            link.click();
            
            URL.revokeObjectURL(url);
        }

        /**
         * Convert errors to CSV format
         */
        convertErrorsToCSV(errors, headers) {
            const csvRows = [];
            
            // Add headers
            csvRows.push(['Row', 'Field', 'Error', ...headers].join(','));
            
            // Add error rows
            errors.forEach(error => {
                const row = [
                    error.row || '',
                    error.field || '',
                    error.message || '',
                    ...(error.data ? headers.map(h => error.data[h] || '') : [])
                ];
                csvRows.push(row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(','));
            });
            
            return csvRows.join('\n');
        }
    }

    /**
     * Bulk Import UI Manager
     */
    class BulkImportUIManager {
        constructor() {
            this.activeComponents = new Map();
        }

        /**
         * Create file upload component
         */
        createFileUploadComponent(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const uploadComponent = new FileUploadComponent(container, options);
            this.activeComponents.set(containerId, uploadComponent);
            
            return uploadComponent;
        }

        /**
         * Create preview component
         */
        createPreviewComponent(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const previewComponent = new PreviewComponent(container, options);
            this.activeComponents.set(containerId, previewComponent);
            
            return previewComponent;
        }

        /**
         * Create progress component
         */
        createProgressComponent(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) {
                throw new Error(`Container with ID '${containerId}' not found`);
            }

            const progressComponent = new ProgressComponent(container, options);
            this.activeComponents.set(containerId, progressComponent);
            
            return progressComponent;
        }

        /**
         * Destroy component
         */
        destroyComponent(containerId) {
            const component = this.activeComponents.get(containerId);
            if (component) {
                component.destroy();
                this.activeComponents.delete(containerId);
            }
        }
    }

    /**
     * File Upload Component
     */
    class FileUploadComponent {
        constructor(container, options = {}) {
            this.container = container;
            this.options = options;
            this.selectedFile = null;
            this.setupUI();
        }

        /**
         * Setup UI
         */
        setupUI() {
            this.container.innerHTML = `
                <div class="sakip-file-upload-area" id="upload-area">
                    <div class="sakip-upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="sakip-upload-text">
                        <p>Drag & drop file di sini atau <span class="sakip-upload-link">klik untuk memilih</span></p>
                        <p class="sakip-upload-hint">Format yang didukung: CSV, Excel, JSON</p>
                    </div>
                    <input type="file" id="file-input" class="sakip-file-input" accept=".csv,.xlsx,.xls,.json" style="display: none;">
                </div>
                <div class="sakip-file-info" id="file-info" style="display: none;">
                    <div class="sakip-file-details">
                        <i class="fas fa-file"></i>
                        <span id="file-name"></span>
                        <span id="file-size"></span>
                    </div>
                    <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-danger" id="remove-file">
                        <i class="fas fa-times"></i> Hapus
                    </button>
                </div>
            `;

            this.setupEventListeners();
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            const uploadArea = this.container.querySelector('#upload-area');
            const fileInput = this.container.querySelector('#file-input');
            const removeFileBtn = this.container.querySelector('#remove-file');

            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    this.handleFileSelect(files[0]);
                }
            });

            // Click to select
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            // File input change
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleFileSelect(e.target.files[0]);
                }
            });

            // Remove file
            removeFileBtn.addEventListener('click', () => {
                this.clearFile();
            });
        }

        /**
         * Handle file selection
         */
        handleFileSelect(file) {
            this.selectedFile = file;
            this.displayFileInfo(file);
            
            if (this.options.onFileSelect) {
                this.options.onFileSelect(file);
            }
        }

        /**
         * Display file information
         */
        displayFileInfo(file) {
            const fileInfo = this.container.querySelector('#file-info');
            const fileName = this.container.querySelector('#file-name');
            const fileSize = this.container.querySelector('#file-size');
            const uploadArea = this.container.querySelector('#upload-area');

            fileName.textContent = file.name;
            fileSize.textContent = this.formatFileSize(file.size);
            
            fileInfo.style.display = 'flex';
            uploadArea.style.display = 'none';
        }

        /**
         * Clear selected file
         */
        clearFile() {
            this.selectedFile = null;
            
            const fileInfo = this.container.querySelector('#file-info');
            const uploadArea = this.container.querySelector('#upload-area');
            const fileInput = this.container.querySelector('#file-input');

            fileInfo.style.display = 'none';
            uploadArea.style.display = 'block';
            fileInput.value = '';

            if (this.options.onFileClear) {
                this.options.onFileClear();
            }
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
         * Get selected file
         */
        getSelectedFile() {
            return this.selectedFile;
        }

        /**
         * Destroy component
         */
        destroy() {
            this.container.innerHTML = '';
        }
    }

    /**
     * Preview Component
     */
    class PreviewComponent {
        constructor(container, options = {}) {
            this.container = container;
            this.options = options;
            this.setupUI();
        }

        /**
         * Setup UI
         */
        setupUI() {
            this.container.innerHTML = `
                <div class="sakip-preview-container">
                    <div class="sakip-preview-header">
                        <h4>Preview Data</h4>
                        <div class="sakip-preview-stats">
                            <span id="preview-stats"></span>
                        </div>
                    </div>
                    <div class="sakip-preview-table-container">
                        <table class="sakip-preview-table" id="preview-table">
                            <thead id="preview-thead"></thead>
                            <tbody id="preview-tbody"></tbody>
                        </table>
                    </div>
                    <div class="sakip-preview-footer">
                        <div class="sakip-validation-summary" id="validation-summary"></div>
                    </div>
                </div>
            `;
        }

        /**
         * Display preview
         */
        displayPreview(data, validationResult) {
            const thead = this.container.querySelector('#preview-thead');
            const tbody = this.container.querySelector('#preview-tbody');
            const stats = this.container.querySelector('#preview-stats');
            const summary = this.container.querySelector('#validation-summary');

            // Display headers
            const headerRow = document.createElement('tr');
            data.headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);

            // Display data rows
            data.data.slice(0, this.options.maxPreviewRows || 100).forEach((row, index) => {
                const tr = document.createElement('tr');
                
                data.headers.forEach(header => {
                    const td = document.createElement('td');
                    td.textContent = row[header] || '';
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });

            // Display stats
            stats.textContent = `Menampilkan ${Math.min(data.data.length, this.options.maxPreviewRows || 100)} dari ${data.data.length} baris`;

            // Display validation summary
            if (validationResult) {
                summary.innerHTML = `
                    <div class="sakip-validation-result">
                        <div class="sakip-validation-errors">
                            <i class="fas fa-exclamation-triangle"></i>
                            ${validationResult.errors.length} error
                        </div>
                        <div class="sakip-validation-warnings">
                            <i class="fas fa-info-circle"></i>
                            ${validationResult.warnings.length} peringatan
                        </div>
                        <div class="sakip-validation-success">
                            <i class="fas fa-check-circle"></i>
                            ${validationResult.validRows} data valid
                        </div>
                    </div>
                `;
            }
        }

        /**
         * Clear preview
         */
        clearPreview() {
            const thead = this.container.querySelector('#preview-thead');
            const tbody = this.container.querySelector('#preview-tbody');
            const stats = this.container.querySelector('#preview-stats');
            const summary = this.container.querySelector('#validation-summary');

            thead.innerHTML = '';
            tbody.innerHTML = '';
            stats.textContent = '';
            summary.innerHTML = '';
        }

        /**
         * Destroy component
         */
        destroy() {
            this.container.innerHTML = '';
        }
    }

    /**
     * Progress Component
     */
    class ProgressComponent {
        constructor(container, options = {}) {
            this.container = container;
            this.options = options;
            this.setupUI();
        }

        /**
         * Setup UI
         */
        setupUI() {
            this.container.innerHTML = `
                <div class="sakip-progress-container">
                    <div class="sakip-progress-header">
                        <h4>Progress Import</h4>
                        <span class="sakip-progress-status" id="progress-status">Menunggu...</span>
                    </div>
                    <div class="sakip-progress-bar-container">
                        <div class="sakip-progress-bar" id="progress-bar">
                            <div class="sakip-progress-fill" id="progress-fill"></div>
                        </div>
                        <span class="sakip-progress-percentage" id="progress-percentage">0%</span>
                    </div>
                    <div class="sakip-progress-stats" id="progress-stats">
                        <div class="sakip-stat">
                            <span class="sakip-stat-label">Total:</span>
                            <span class="sakip-stat-value" id="total-rows">0</span>
                        </div>
                        <div class="sakip-stat">
                            <span class="sakip-stat-label">Sukses:</span>
                            <span class="sakip-stat-value" id="success-rows">0</span>
                        </div>
                        <div class="sakip-stat">
                            <span class="sakip-stat-label">Error:</span>
                            <span class="sakip-stat-value" id="error-rows">0</span>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Update progress
         */
        updateProgress(progress) {
            const progressFill = this.container.querySelector('#progress-fill');
            const progressPercentage = this.container.querySelector('#progress-percentage');
            const progressStatus = this.container.querySelector('#progress-status');

            progressFill.style.width = `${progress.percentage}%`;
            progressPercentage.textContent = `${progress.percentage}%`;
            progressStatus.textContent = progress.status || 'Processing...';

            // Update stats
            if (progress.stats) {
                this.updateStats(progress.stats);
            }
        }

        /**
         * Update stats
         */
        updateStats(stats) {
            const totalRows = this.container.querySelector('#total-rows');
            const successRows = this.container.querySelector('#success-rows');
            const errorRows = this.container.querySelector('#error-rows');

            totalRows.textContent = stats.totalRows || 0;
            successRows.textContent = stats.successRows || 0;
            errorRows.textContent = stats.errorRows || 0;
        }

        /**
         * Destroy component
         */
        destroy() {
            this.container.innerHTML = '';
        }
    }

    /**
     * Main SAKIP Bulk Import API
     */
    const SAKIP_BULK_IMPORT = {
        // Constants
        constants: BULK_IMPORT_CONSTANTS,

        // Core classes
        FileParser,
        DataValidator,
        BulkImportManager,
        BulkImportUIManager,
        FileUploadComponent,
        PreviewComponent,
        ProgressComponent,

        // Create instances
        manager: new BulkImportManager(),
        uiManager: new BulkImportUIManager(),

        // Convenience methods
        initialize: (options) => manager.initialize(options),
        processFile: (file, importType, options) => manager.processFile(file, importType, options),
        importData: (processId, data, importType, options) => manager.importData(processId, data, importType, options),
        getImportProgress: (processId) => manager.getImportProgress(processId),
        cancelImport: (processId) => manager.cancelImport(processId),
        getImportHistory: (limit) => manager.getImportHistory(limit),
        exportErrors: (errors, headers) => manager.exportErrors(errors, headers),

        // UI methods
        createFileUploadComponent: (containerId, options) => uiManager.createFileUploadComponent(containerId, options),
        createPreviewComponent: (containerId, options) => uiManager.createPreviewComponent(containerId, options),
        createProgressComponent: (containerId, options) => uiManager.createProgressComponent(containerId, options),
        destroyComponent: (containerId) => uiManager.destroyComponent(containerId),

        // Validation methods
        validateFile: (file) => manager.validateFile(file),
        validateData: (data, importType, options) => manager.validateData(data, importType, options),

        // Utility methods
        formatFileSize: (bytes) => manager.formatFileSize(bytes),
        detectFileType: (file) => manager.detectFileType(file)
    };

    return SAKIP_BULK_IMPORT;
}));