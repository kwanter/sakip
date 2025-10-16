/**
 * SAKIP File Utilities Helper Functions
 * Comprehensive file upload and processing utilities for government-style SAKIP module
 */

// ==========================================================================
// File Type Definitions
// ==========================================================================

/**
 * Supported file types and their configurations
 */
const FILE_TYPES = {
  // Document files
  PDF: {
    extensions: ['pdf'],
    mimeTypes: ['application/pdf'],
    maxSize: 10 * 1024 * 1024, // 10MB
    icon: '📄',
    description: { id: 'Dokumen PDF', en: 'PDF Document' }
  },
  EXCEL: {
    extensions: ['xlsx', 'xls'],
    mimeTypes: [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel'
    ],
    maxSize: 5 * 1024 * 1024, // 5MB
    icon: '📊',
    description: { id: 'Spreadsheet Excel', en: 'Excel Spreadsheet' }
  },
  WORD: {
    extensions: ['docx', 'doc'],
    mimeTypes: [
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/msword'
    ],
    maxSize: 5 * 1024 * 1024, // 5MB
    icon: '📝',
    description: { id: 'Dokumen Word', en: 'Word Document' }
  },
  
  // Image files
  IMAGE: {
    extensions: ['jpg', 'jpeg', 'png', 'gif', 'bmp'],
    mimeTypes: [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/bmp'
    ],
    maxSize: 2 * 1024 * 1024, // 2MB
    icon: '🖼️',
    description: { id: 'Gambar', en: 'Image' },
    dimensions: {
      maxWidth: 1920,
      maxHeight: 1080,
      minWidth: 100,
      minHeight: 100
    }
  },
  
  // Archive files
  ZIP: {
    extensions: ['zip', 'rar', '7z'],
    mimeTypes: [
      'application/zip',
      'application/x-rar-compressed',
      'application/x-7z-compressed'
    ],
    maxSize: 20 * 1024 * 1024, // 20MB
    icon: '🗜️',
    description: { id: 'File Arsip', en: 'Archive File' }
  }
};

// ==========================================================================
// File Validation Functions
// ==========================================================================

/**
 * File validation utility functions
 */
const FileValidation = {
  /**
   * Validate file extension
   * @param {File} file - File object
   * @param {Array} allowedExtensions - Array of allowed extensions
   * @returns {Object} Validation result
   */
  validateExtension: function(file, allowedExtensions) {
    const fileExtension = file.name.split('.').pop().toLowerCase();
    const isValid = allowedExtensions.some(ext => 
      ext.toLowerCase() === fileExtension
    );
    
    return {
      valid: isValid,
      extension: fileExtension,
      allowed: allowedExtensions,
      message: isValid ? 
        { id: 'Ekstensi file valid', en: 'File extension is valid' } :
        { id: `Ekstensi file tidak valid. Diperbolehkan: ${allowedExtensions.join(', ')}`, 
          en: `Invalid file extension. Allowed: ${allowedExtensions.join(', ')}` }
    };
  },

  /**
   * Validate file MIME type
   * @param {File} file - File object
   * @param {Array} allowedMimeTypes - Array of allowed MIME types
   * @returns {Object} Validation result
   */
  validateMimeType: function(file, allowedMimeTypes) {
    const isValid = allowedMimeTypes.some(mimeType => 
      file.type === mimeType
    );
    
    return {
      valid: isValid,
      mimeType: file.type,
      allowed: allowedMimeTypes,
      message: isValid ? 
        { id: 'Tipe file valid', en: 'File type is valid' } :
        { id: `Tipe file tidak valid. Diperbolehkan: ${allowedMimeTypes.join(', ')}`, 
          en: `Invalid file type. Allowed: ${allowedMimeTypes.join(', ')}` }
    };
  },

  /**
   * Validate file size
   * @param {File} file - File object
   * @param {number} maxSize - Maximum file size in bytes
   * @returns {Object} Validation result
   */
  validateSize: function(file, maxSize) {
    const isValid = file.size <= maxSize;
    
    return {
      valid: isValid,
      size: file.size,
      maxSize: maxSize,
      formattedSize: this.formatFileSize(file.size),
      formattedMaxSize: this.formatFileSize(maxSize),
      message: isValid ? 
        { id: 'Ukuran file valid', en: 'File size is valid' } :
        { id: `Ukuran file terlalu besar. Maksimal: ${this.formatFileSize(maxSize)}`, 
          en: `File size is too large. Maximum: ${this.formatFileSize(maxSize)}` }
    };
  },

  /**
   * Validate image dimensions
   * @param {File} file - Image file
   * @param {Object} dimensionConstraints - Dimension constraints
   * @returns {Promise<Object>} Validation result
   */
  validateImageDimensions: function(file, dimensionConstraints) {
    return new Promise((resolve) => {
      const img = new Image();
      const objectUrl = URL.createObjectURL(file);
      
      img.onload = function() {
        URL.revokeObjectURL(objectUrl);
        
        const { maxWidth, maxHeight, minWidth, minHeight } = dimensionConstraints;
        const isValid = img.width <= maxWidth && 
                       img.height <= maxHeight &&
                       img.width >= minWidth && 
                       img.height >= minHeight;
        
        resolve({
          valid: isValid,
          width: img.width,
          height: img.height,
          constraints: dimensionConstraints,
          message: isValid ? 
            { id: 'Dimensi gambar valid', en: 'Image dimensions are valid' } :
            { id: `Dimensi gambar tidak valid. Diperlukan: ${minWidth}x${minHeight} - ${maxWidth}x${maxHeight}px`, 
              en: `Image dimensions are invalid. Required: ${minWidth}x${minHeight} - ${maxWidth}x${maxHeight}px` }
        });
      };
      
      img.onerror = function() {
        URL.revokeObjectURL(objectUrl);
        resolve({
          valid: false,
          message: { id: 'Gagal membaca gambar', en: 'Failed to read image' }
        });
      };
      
      img.src = objectUrl;
    });
  },

  /**
   * Comprehensive file validation
   * @param {File} file - File object
   * @param {Object} constraints - Validation constraints
   * @returns {Promise<Object>} Validation result
   */
  validateFile: async function(file, constraints = {}) {
    const results = {
      valid: true,
      errors: [],
      warnings: [],
      details: {}
    };

    // Validate extension
    if (constraints.allowedExtensions) {
      const extResult = this.validateExtension(file, constraints.allowedExtensions);
      results.details.extension = extResult;
      if (!extResult.valid) {
        results.valid = false;
        results.errors.push(extResult.message);
      }
    }

    // Validate MIME type
    if (constraints.allowedMimeTypes) {
      const mimeResult = this.validateMimeType(file, constraints.allowedMimeTypes);
      results.details.mimeType = mimeResult;
      if (!mimeResult.valid) {
        results.valid = false;
        results.errors.push(mimeResult.message);
      }
    }

    // Validate size
    if (constraints.maxSize) {
      const sizeResult = this.validateSize(file, constraints.maxSize);
      results.details.size = sizeResult;
      if (!sizeResult.valid) {
        results.valid = false;
        results.errors.push(sizeResult.message);
      }
    }

    // Validate image dimensions
    if (constraints.dimensionConstraints && file.type.startsWith('image/')) {
      const dimResult = await this.validateImageDimensions(file, constraints.dimensionConstraints);
      results.details.dimensions = dimResult;
      if (!dimResult.valid) {
        results.valid = false;
        results.errors.push(dimResult.message);
      }
    }

    return results;
  },

  /**
   * Format file size to human readable format
   * @param {number} bytes - File size in bytes
   * @param {number} decimals - Number of decimal places
   * @returns {string} Formatted file size
   */
  formatFileSize: function(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  },

  /**
   * Get file type from extension
   * @param {string} fileName - File name
   * @returns {string} File type
   */
  getFileType: function(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    
    for (const [type, config] of Object.entries(FILE_TYPES)) {
      if (config.extensions.includes(extension)) {
        return type;
      }
    }
    
    return 'UNKNOWN';
  },

  /**
   * Get file type configuration
   * @param {string} fileType - File type
   * @returns {Object} File type configuration
   */
  getFileTypeConfig: function(fileType) {
    return FILE_TYPES[fileType.toUpperCase()] || null;
  }
};

// ==========================================================================
// File Upload Functions
// ==========================================================================

/**
 * File upload utility functions
 */
const FileUpload = {
  /**
   * Create file upload configuration
   * @param {string} uploadType - Type of upload (document, image, etc.)
   * @param {Object} customConfig - Custom configuration
   * @returns {Object} Upload configuration
   */
  createUploadConfig: function(uploadType, customConfig = {}) {
    const baseConfig = {
      maxFiles: 5,
      maxFileSize: 10 * 1024 * 1024, // 10MB
      allowedTypes: ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
      chunkSize: 1 * 1024 * 1024, // 1MB chunks
      retryAttempts: 3,
      retryDelay: 1000,
      showProgress: true,
      showPreview: false,
      validateOnClient: true,
      autoUpload: false
    };

    const typeConfig = this.getUploadTypeConfig(uploadType);
    
    return {
      ...baseConfig,
      ...typeConfig,
      ...customConfig
    };
  },

  /**
   * Get upload type configuration
   * @param {string} uploadType - Upload type
   * @returns {Object} Type configuration
   */
  getUploadTypeConfig: function(uploadType) {
    const configs = {
      document: {
        allowedTypes: ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        maxFileSize: 10 * 1024 * 1024,
        description: { id: 'Dokumen', en: 'Documents' }
      },
      image: {
        allowedTypes: ['jpg', 'jpeg', 'png', 'gif'],
        maxFileSize: 2 * 1024 * 1024,
        showPreview: true,
        description: { id: 'Gambar', en: 'Images' }
      },
      evidence: {
        allowedTypes: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'],
        maxFileSize: 5 * 1024 * 1024,
        showPreview: true,
        description: { id: 'Bukti', en: 'Evidence' }
      },
      report: {
        allowedTypes: ['pdf', 'xlsx', 'xls'],
        maxFileSize: 20 * 1024 * 1024,
        description: { id: 'Laporan', en: 'Reports' }
      },
      archive: {
        allowedTypes: ['zip', 'rar', '7z'],
        maxFileSize: 50 * 1024 * 1024,
        description: { id: 'Arsip', en: 'Archives' }
      }
    };

    return configs[uploadType] || {};
  },

  /**
   * Create file upload progress tracker
   * @param {Object} options - Progress options
   * @returns {Object} Progress tracker
   */
  createProgressTracker: function(options = {}) {
    const defaultOptions = {
      showProgress: true,
      showSpeed: true,
      showTimeRemaining: true,
      updateInterval: 100 // milliseconds
    };

    const config = { ...defaultOptions, ...options };
    
    return {
      total: 0,
      loaded: 0,
      percent: 0,
      speed: 0,
      timeRemaining: 0,
      startTime: null,
      lastUpdate: null,
      
      start: function(totalSize) {
        this.total = totalSize;
        this.loaded = 0;
        this.percent = 0;
        this.speed = 0;
        this.timeRemaining = 0;
        this.startTime = Date.now();
        this.lastUpdate = Date.now();
      },
      
      update: function(loaded) {
        const now = Date.now();
        const timeDiff = now - this.lastUpdate;
        
        if (timeDiff >= config.updateInterval) {
          const bytesDiff = loaded - this.loaded;
          this.speed = bytesDiff / (timeDiff / 1000); // bytes per second
          
          if (this.speed > 0) {
            this.timeRemaining = (this.total - loaded) / this.speed;
          }
          
          this.loaded = loaded;
          this.percent = (loaded / this.total) * 100;
          this.lastUpdate = now;
        }
      },
      
      getFormattedSpeed: function() {
        return FileValidation.formatFileSize(this.speed) + '/s';
      },
      
      getFormattedTimeRemaining: function() {
        const seconds = Math.floor(this.timeRemaining);
        if (seconds < 60) return `${seconds}s`;
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds}s`;
      }
    };
  },

  /**
   * Create file chunk for chunked upload
   * @param {File} file - File object
   * @param {number} start - Start byte
   * @param {number} end - End byte
   * @returns {Blob} File chunk
   */
  createFileChunk: function(file, start, end) {
    return file.slice(start, end);
  },

  /**
   * Calculate file chunks
   * @param {File} file - File object
   * @param {number} chunkSize - Chunk size in bytes
   * @returns {Array} Array of chunk information
   */
  calculateChunks: function(file, chunkSize) {
    const chunks = [];
    const totalChunks = Math.ceil(file.size / chunkSize);
    
    for (let i = 0; i < totalChunks; i++) {
      const start = i * chunkSize;
      const end = Math.min(start + chunkSize, file.size);
      
      chunks.push({
        index: i,
        start: start,
        end: end,
        size: end - start,
        total: totalChunks
      });
    }
    
    return chunks;
  },

  /**
   * Generate unique file ID
   * @param {File} file - File object
   * @returns {string} Unique file ID
   */
  generateFileId: function(file) {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substr(2, 9);
    const name = file.name.replace(/[^a-zA-Z0-9]/g, '_');
    return `${timestamp}_${random}_${name}`;
  },

  /**
   * Create file metadata
   * @param {File} file - File object
   * @param {Object} additionalMetadata - Additional metadata
   * @returns {Object} File metadata
   */
  createFileMetadata: function(file, additionalMetadata = {}) {
    return {
      name: file.name,
      size: file.size,
      type: file.type,
      lastModified: file.lastModified,
      extension: file.name.split('.').pop().toLowerCase(),
      fileType: FileValidation.getFileType(file.name),
      uploadedAt: new Date().toISOString(),
      ...additionalMetadata
    };
  }
};

// ==========================================================================
// File Processing Functions
// ==========================================================================

/**
 * File processing utility functions
 */
const FileProcessing = {
  /**
   * Read file as text
   * @param {File} file - File object
   * @returns {Promise<string>} File content as text
   */
  readAsText: function(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result);
      reader.onerror = e => reject(new Error('Failed to read file as text'));
      reader.readAsText(file);
    });
  },

  /**
   * Read file as data URL
   * @param {File} file - File object
   * @returns {Promise<string>} File content as data URL
   */
  readAsDataURL: function(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result);
      reader.onerror = e => reject(new Error('Failed to read file as data URL'));
      reader.readAsDataURL(file);
    });
  },

  /**
   * Read file as array buffer
   * @param {File} file - File object
   * @returns {Promise<ArrayBuffer>} File content as array buffer
   */
  readAsArrayBuffer: function(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result);
      reader.onerror = e => reject(new Error('Failed to read file as array buffer'));
      reader.readAsArrayBuffer(file);
    });
  },

  /**
   * Create file preview
   * @param {File} file - File object
   * @param {Object} options - Preview options
   * @returns {Promise<Object>} Preview information
   */
  createPreview: async function(file, options = {}) {
    const defaultOptions = {
      maxWidth: 200,
      maxHeight: 200,
      quality: 0.8,
      format: 'image/jpeg'
    };

    const config = { ...defaultOptions, ...options };
    const fileType = FileValidation.getFileType(file.name);

    try {
      if (fileType === 'IMAGE') {
        return await this.createImagePreview(file, config);
      } else if (fileType === 'PDF') {
        return await this.createPDFPreview(file, config);
      } else {
        return this.createGenericPreview(file, fileType);
      }
    } catch (error) {
      return {
        success: false,
        error: error.message,
        preview: null
      };
    }
  },

  /**
   * Create image preview
   * @param {File} file - Image file
   * @param {Object} config - Preview configuration
   * @returns {Promise<Object>} Preview information
   */
  createImagePreview: function(file, config) {
    return new Promise((resolve) => {
      const img = new Image();
      const objectUrl = URL.createObjectURL(file);
      
      img.onload = function() {
        URL.revokeObjectURL(objectUrl);
        
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Calculate dimensions maintaining aspect ratio
        let { width, height } = img;
        const aspectRatio = width / height;
        
        if (width > config.maxWidth) {
          width = config.maxWidth;
          height = width / aspectRatio;
        }
        
        if (height > config.maxHeight) {
          height = config.maxHeight;
          width = height * aspectRatio;
        }
        
        canvas.width = width;
        canvas.height = height;
        
        // Draw image
        ctx.drawImage(img, 0, 0, width, height);
        
        // Convert to data URL
        const dataUrl = canvas.toDataURL(config.format, config.quality);
        
        resolve({
          success: true,
          preview: dataUrl,
          width: width,
          height: height,
          originalWidth: img.width,
          originalHeight: img.height
        });
      };
      
      img.onerror = function() {
        URL.revokeObjectURL(objectUrl);
        resolve({
          success: false,
          error: 'Failed to load image',
          preview: null
        });
      };
      
      img.src = objectUrl;
    });
  },

  /**
   * Create PDF preview (placeholder)
   * @param {File} file - PDF file
   * @param {Object} config - Preview configuration
   * @returns {Promise<Object>} Preview information
   */
  createPDFPreview: function(file, config) {
    return Promise.resolve({
      success: true,
      preview: null, // PDF preview would require PDF.js or similar library
      icon: '📄',
      type: 'PDF'
    });
  },

  /**
   * Create generic file preview
   * @param {File} file - File object
   * @param {string} fileType - File type
   * @returns {Object} Preview information
   */
  createGenericPreview: function(file, fileType) {
    const config = FILE_TYPES[fileType.toUpperCase()];
    const icon = config ? config.icon : '📎';
    
    return {
      success: true,
      preview: null,
      icon: icon,
      type: fileType,
      name: file.name,
      size: FileValidation.formatFileSize(file.size)
    };
  },

  /**
   * Extract text from file (basic implementation)
   * @param {File} file - File object
   * @returns {Promise<string>} Extracted text
   */
  extractText: async function(file) {
    const fileType = FileValidation.getFileType(file.name);
    
    try {
      if (fileType === 'PDF') {
        // This would require PDF.js library
        return 'PDF text extraction requires additional library';
      } else if (fileType === 'EXCEL') {
        // This would require xlsx library
        return 'Excel text extraction requires additional library';
      } else if (fileType === 'WORD') {
        // This would require mammoth.js or similar
        return 'Word text extraction requires additional library';
      } else if (fileType === 'IMAGE') {
        // This would require OCR library
        return 'Image text extraction requires OCR library';
      } else {
        // Try to read as text
        return await this.readAsText(file);
      }
    } catch (error) {
      return `Text extraction failed: ${error.message}`;
    }
  },

  /**
   * Get file hash (basic implementation)
   * @param {File} file - File object
   * @param {string} algorithm - Hash algorithm
   * @returns {Promise<string>} File hash
   */
  getFileHash: async function(file, algorithm = 'SHA-256') {
    try {
      const buffer = await this.readAsArrayBuffer(file);
      const hashBuffer = await crypto.subtle.digest(algorithm, buffer);
      const hashArray = Array.from(new Uint8Array(hashBuffer));
      const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
      return hashHex;
    } catch (error) {
      throw new Error(`Hash calculation failed: ${error.message}`);
    }
  }
};

// ==========================================================================
// File Management Functions
// ==========================================================================

/**
 * File management utility functions
 */
const FileManagement = {
  /**
   * Create file manager
   * @param {Object} options - Manager options
   * @returns {Object} File manager
   */
  createFileManager: function(options = {}) {
    const defaultOptions = {
      maxFiles: 10,
      maxTotalSize: 100 * 1024 * 1024, // 100MB
      autoCleanup: true,
      cleanupInterval: 3600000 // 1 hour
    };

    const config = { ...defaultOptions, ...options };
    
    return {
      files: new Map(),
      totalSize: 0,
      config: config,
      
      addFile: function(file, metadata = {}) {
        const fileId = FileUpload.generateFileId(file);
        const fileMetadata = FileUpload.createFileMetadata(file, metadata);
        
        if (this.files.size >= config.maxFiles) {
          return {
            success: false,
            error: { id: 'Jumlah file maksimal tercapai', en: 'Maximum file count reached' }
          };
        }
        
        if (this.totalSize + file.size > config.maxTotalSize) {
          return {
            success: false,
            error: { id: 'Ukuran total file maksimal tercapai', en: 'Maximum total file size reached' }
          };
        }
        
        this.files.set(fileId, {
          file: file,
          metadata: fileMetadata,
          uploadedAt: new Date().toISOString()
        });
        
        this.totalSize += file.size;
        
        return {
          success: true,
          fileId: fileId,
          metadata: fileMetadata
        };
      },
      
      removeFile: function(fileId) {
        const fileData = this.files.get(fileId);
        if (!fileData) {
          return {
            success: false,
            error: { id: 'File tidak ditemukan', en: 'File not found' }
          };
        }
        
        this.files.delete(fileId);
        this.totalSize -= fileData.file.size;
        
        return {
          success: true,
          metadata: fileData.metadata
        };
      },
      
      getFile: function(fileId) {
        const fileData = this.files.get(fileId);
        return fileData ? fileData.file : null;
      },
      
      getMetadata: function(fileId) {
        const fileData = this.files.get(fileId);
        return fileData ? fileData.metadata : null;
      },
      
      getAllFiles: function() {
        return Array.from(this.files.entries()).map(([id, data]) => ({
          id: id,
          file: data.file,
          metadata: data.metadata,
          uploadedAt: data.uploadedAt
        }));
      },
      
      clear: function() {
        this.files.clear();
        this.totalSize = 0;
      },
      
      cleanup: function() {
        if (!config.autoCleanup) return;
        
        const now = Date.now();
        const cutoff = now - config.cleanupInterval;
        
        for (const [fileId, fileData] of this.files.entries()) {
          const uploadedAt = new Date(fileData.uploadedAt).getTime();
          if (uploadedAt < cutoff) {
            this.removeFile(fileId);
          }
        }
      }
    };
  },

  /**
   * Create file queue
   * @param {Object} options - Queue options
   * @returns {Object} File queue
   */
  createFileQueue: function(options = {}) {
    const defaultOptions = {
      maxConcurrent: 3,
      retryAttempts: 3,
      retryDelay: 1000
    };

    const config = { ...defaultOptions, ...options };
    
    return {
      queue: [],
      active: [],
      completed: [],
      failed: [],
      config: config,
      
      add: function(file, uploadConfig = {}) {
        const queueItem = {
          id: FileUpload.generateFileId(file),
          file: file,
          config: uploadConfig,
          status: 'pending',
          attempts: 0,
          createdAt: new Date().toISOString()
        };
        
        this.queue.push(queueItem);
        return queueItem.id;
      },
      
      process: async function() {
        while (this.active.length < config.maxConcurrent && this.queue.length > 0) {
          const item = this.queue.shift();
          this.active.push(item);
          
          try {
            item.status = 'uploading';
            // Upload logic would go here
            item.status = 'completed';
            this.completed.push(item);
          } catch (error) {
            item.attempts++;
            if (item.attempts < config.retryAttempts) {
              item.status = 'retrying';
              this.queue.unshift(item); // Put back in queue
              await new Promise(resolve => setTimeout(resolve, config.retryDelay));
            } else {
              item.status = 'failed';
              item.error = error.message;
              this.failed.push(item);
            }
          } finally {
            this.active = this.active.filter(activeItem => activeItem.id !== item.id);
          }
        }
      },
      
      getStatus: function() {
        return {
          pending: this.queue.length,
          active: this.active.length,
          completed: this.completed.length,
          failed: this.failed.length,
          total: this.queue.length + this.active.length + this.completed.length + this.failed.length
        };
      }
    };
  }
};

// ==========================================================================
// Export File Utilities
// ==========================================================================

/**
 * Export all file utility functions
 */
const SAKIP_FILE_UTILS = {
  FILE_TYPES: FILE_TYPES,
  VALIDATION: FileValidation,
  UPLOAD: FileUpload,
  PROCESSING: FileProcessing,
  MANAGEMENT: FileManagement
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_FILE_UTILS;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_FILE_UTILS;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.FILE_UTILS = SAKIP_FILE_UTILS;
}