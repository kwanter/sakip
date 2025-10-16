/**
 * SAKIP Export Utilities Helper Functions
 * Comprehensive export functionality for government-style SAKIP module
 */

// ==========================================================================
// Export Format Definitions
// ==========================================================================

/**
 * Supported export formats and their configurations
 */
const EXPORT_FORMATS = {
  PDF: {
    extension: 'pdf',
    mimeType: 'application/pdf',
    icon: '📄',
    description: { id: 'Dokumen PDF', en: 'PDF Document' },
    maxRecords: 10000,
    features: ['charts', 'tables', 'images', 'formatting'],
    libraries: ['jsPDF', 'pdfmake'],
    default: true
  },
  EXCEL: {
    extension: 'xlsx',
    mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    icon: '📊',
    description: { id: 'Spreadsheet Excel', en: 'Excel Spreadsheet' },
    maxRecords: 100000,
    features: ['formulas', 'charts', 'formatting', 'multiple_sheets'],
    libraries: ['SheetJS', 'xlsx'],
    default: true
  },
  CSV: {
    extension: 'csv',
    mimeType: 'text/csv',
    icon: '📋',
    description: { id: 'File CSV', en: 'CSV File' },
    maxRecords: 1000000,
    features: ['raw_data', 'simple_format'],
    libraries: [],
    default: true
  },
  WORD: {
    extension: 'docx',
    mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    icon: '📝',
    description: { id: 'Dokumen Word', en: 'Word Document' },
    maxRecords: 5000,
    features: ['rich_text', 'tables', 'images'],
    libraries: ['docxtemplater', 'mammoth'],
    default: false
  },
  JSON: {
    extension: 'json',
    mimeType: 'application/json',
    icon: '📄',
    description: { id: 'Data JSON', en: 'JSON Data' },
    maxRecords: 1000000,
    features: ['structured_data', 'api_compatible'],
    libraries: [],
    default: true
  },
  XML: {
    extension: 'xml',
    mimeType: 'application/xml',
    icon: '📄',
    description: { id: 'Data XML', en: 'XML Data' },
    maxRecords: 100000,
    features: ['structured_data', 'hierarchical'],
    libraries: [],
    default: false
  }
};

// ==========================================================================
// Export Configuration Functions
// ==========================================================================

/**
 * Export configuration utility functions
 */
const ExportConfig = {
  /**
   * Create export configuration
   * @param {string} format - Export format
   * @param {Object} options - Export options
   * @returns {Object} Export configuration
   */
  createExportConfig: function(format, options = {}) {
    const formatConfig = EXPORT_FORMATS[format.toUpperCase()];
    if (!formatConfig) {
      throw new Error(`Unsupported export format: ${format}`);
    }

    const defaultOptions = {
      filename: `sakip_export_${new Date().toISOString().slice(0, 10)}`,
      includeHeaders: true,
      includeMetadata: true,
      language: 'id',
      dateFormat: 'DD/MM/YYYY',
      numberFormat: 'id-ID',
      currency: 'IDR',
      timezone: 'Asia/Jakarta',
      encoding: 'utf-8',
      delimiter: ',',
      quoteChar: '"',
      escapeChar: '\\',
      lineTerminator: '\r\n',
      sheetName: 'Data',
      pageSize: 'A4',
      orientation: 'portrait',
      margins: { top: 20, right: 20, bottom: 20, left: 20 },
      fontSize: 12,
      fontFamily: 'Arial',
      theme: 'government'
    };

    return {
      format: format.toUpperCase(),
      formatConfig: formatConfig,
      options: { ...defaultOptions, ...options }
    };
  },

  /**
   * Get available export formats
   * @param {boolean} includeDefaultsOnly - Include only default formats
   * @returns {Array} Available formats
   */
  getAvailableFormats: function(includeDefaultsOnly = true) {
    return Object.entries(EXPORT_FORMATS)
      .filter(([key, config]) => !includeDefaultsOnly || config.default)
      .map(([key, config]) => ({
        key: key,
        extension: config.extension,
        description: config.description,
        icon: config.icon,
        maxRecords: config.maxRecords,
        features: config.features
      }));
  },

  /**
   * Validate export format
   * @param {string} format - Export format
   * @param {number} recordCount - Number of records
   * @returns {Object} Validation result
   */
  validateFormat: function(format, recordCount) {
    const formatConfig = EXPORT_FORMATS[format.toUpperCase()];
    if (!formatConfig) {
      return {
        valid: false,
        error: { id: 'Format tidak didukung', en: 'Format not supported' }
      };
    }

    if (recordCount > formatConfig.maxRecords) {
      return {
        valid: false,
        error: { 
          id: `Maksimal ${formatConfig.maxRecords} data`, 
          en: `Maximum ${formatConfig.maxRecords} records` 
        }
      };
    }

    return {
      valid: true,
      format: formatConfig
    };
  },

  /**
   * Get export format configuration
   * @param {string} format - Export format
   * @returns {Object} Format configuration
   */
  getFormatConfig: function(format) {
    return EXPORT_FORMATS[format.toUpperCase()] || null;
  }
};

// ==========================================================================
// Data Processing Functions
// ==========================================================================

/**
 * Data processing utility functions
 */
const DataProcessing = {
  /**
   * Process data for export
   * @param {Array} data - Raw data
   * @param {Object} config - Export configuration
   * @returns {Object} Processed data
   */
  processData: function(data, config) {
    const { format, options } = config;
    
    // Apply data transformations based on format
    let processedData = this.applyDataTransformations(data, options);
    
    // Apply format-specific processing
    switch (format) {
      case 'CSV':
        processedData = this.processForCSV(processedData, options);
        break;
      case 'EXCEL':
        processedData = this.processForExcel(processedData, options);
        break;
      case 'PDF':
        processedData = this.processForPDF(processedData, options);
        break;
      case 'JSON':
        processedData = this.processForJSON(processedData, options);
        break;
      case 'XML':
        processedData = this.processForXML(processedData, options);
        break;
      case 'WORD':
        processedData = this.processForWord(processedData, options);
        break;
    }
    
    return processedData;
  },

  /**
   * Apply data transformations
   * @param {Array} data - Raw data
   * @param {Object} options - Transformation options
   * @returns {Array} Transformed data
   */
  applyDataTransformations: function(data, options) {
    return data.map(item => {
      const transformed = { ...item };
      
      // Format dates
      if (options.dateFormat) {
        this.formatDates(transformed, options.dateFormat);
      }
      
      // Format numbers
      if (options.numberFormat) {
        this.formatNumbers(transformed, options.numberFormat);
      }
      
      // Format currency
      if (options.currency) {
        this.formatCurrency(transformed, options.currency);
      }
      
      // Translate fields
      if (options.language) {
        this.translateFields(transformed, options.language);
      }
      
      return transformed;
    });
  },

  /**
   * Process data for CSV export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} CSV data
   */
  processForCSV: function(data, options) {
    if (!data || data.length === 0) {
      return { headers: [], rows: [] };
    }

    const headers = options.includeHeaders ? Object.keys(data[0]) : [];
    const rows = data.map(item => Object.values(item));

    return {
      headers: headers,
      rows: rows,
      delimiter: options.delimiter || ',',
      quoteChar: options.quoteChar || '"',
      escapeChar: options.escapeChar || '\\',
      lineTerminator: options.lineTerminator || '\r\n'
    };
  },

  /**
   * Process data for Excel export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} Excel data
   */
  processForExcel: function(data, options) {
    if (!data || data.length === 0) {
      return { sheets: [{ name: options.sheetName || 'Data', data: [] }] };
    }

    const headers = options.includeHeaders ? Object.keys(data[0]) : [];
    const rows = data.map(item => Object.values(item));

    return {
      sheets: [{
        name: options.sheetName || 'Data',
        headers: headers,
        data: rows,
        includeHeaders: options.includeHeaders
      }],
      metadata: options.includeMetadata ? this.generateMetadata() : null
    };
  },

  /**
   * Process data for PDF export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} PDF data
   */
  processForPDF: function(data, options) {
    return {
      title: options.title || 'SAKIP Export',
      subtitle: options.subtitle || '',
      data: data,
      headers: options.includeHeaders ? Object.keys(data[0] || {}) : [],
      metadata: options.includeMetadata ? this.generateMetadata() : null,
      pageSize: options.pageSize || 'A4',
      orientation: options.orientation || 'portrait',
      margins: options.margins || { top: 20, right: 20, bottom: 20, left: 20 },
      fontSize: options.fontSize || 12,
      fontFamily: options.fontFamily || 'Arial'
    };
  },

  /**
   * Process data for JSON export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} JSON data
   */
  processForJSON: function(data, options) {
    return {
      data: data,
      metadata: options.includeMetadata ? this.generateMetadata() : null,
      exportedAt: new Date().toISOString(),
      count: data.length
    };
  },

  /**
   * Process data for XML export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} XML data
   */
  processForXML: function(data, options) {
    return {
      rootElement: options.rootElement || 'data',
      itemElement: options.itemElement || 'item',
      data: data,
      metadata: options.includeMetadata ? this.generateMetadata() : null
    };
  },

  /**
   * Process data for Word export
   * @param {Array} data - Data to process
   * @param {Object} options - Processing options
   * @returns {Object} Word data
   */
  processForWord: function(data, options) {
    return {
      title: options.title || 'SAKIP Export',
      data: data,
      headers: options.includeHeaders ? Object.keys(data[0] || {}) : [],
      metadata: options.includeMetadata ? this.generateMetadata() : null,
      fontSize: options.fontSize || 12,
      fontFamily: options.fontFamily || 'Arial'
    };
  },

  /**
   * Format dates in data
   * @param {Object} item - Data item
   * @param {string} dateFormat - Date format
   */
  formatDates: function(item, dateFormat) {
    const dateFields = ['created_at', 'updated_at', 'date', 'tanggal', 'tgl'];
    
    dateFields.forEach(field => {
      if (item[field] && this.isDate(item[field])) {
        item[field] = this.formatDate(item[field], dateFormat);
      }
    });
  },

  /**
   * Format numbers in data
   * @param {Object} item - Data item
   * @param {string} numberFormat - Number format
   */
  formatNumbers: function(item, numberFormat) {
    const numberFields = ['score', 'nilai', 'amount', 'jumlah', 'total', 'count'];
    
    numberFields.forEach(field => {
      if (item[field] && typeof item[field] === 'number') {
        item[field] = this.formatNumber(item[field], numberFormat);
      }
    });
  },

  /**
   * Format currency in data
   * @param {Object} item - Data item
   * @param {string} currency - Currency code
   */
  formatCurrency: function(item, currency) {
    const currencyFields = ['budget', 'anggaran', 'cost', 'biaya', 'price', 'harga'];
    
    currencyFields.forEach(field => {
      if (item[field] && typeof item[field] === 'number') {
        item[field] = this.formatCurrency(item[field], currency);
      }
    });
  },

  /**
   * Translate fields in data
   * @param {Object} item - Data item
   * @param {string} language - Language code
   */
  translateFields: function(item, language) {
    // This would require a translation dictionary
    // For now, we'll just return the item as-is
    return item;
  },

  /**
   * Generate export metadata
   * @returns {Object} Metadata
   */
  generateMetadata: function() {
    return {
      exportedAt: new Date().toISOString(),
      exportedBy: 'SAKIP System',
      version: '1.0.0',
      timezone: 'Asia/Jakarta'
    };
  },

  /**
   * Check if value is a date
   * @param {*} value - Value to check
   * @returns {boolean} Whether value is a date
   */
  isDate: function(value) {
    return value instanceof Date || !isNaN(Date.parse(value));
  },

  /**
   * Format date
   * @param {Date|string} date - Date to format
   * @param {string} format - Date format
   * @returns {string} Formatted date
   */
  formatDate: function(date, format) {
    const d = new Date(date);
    const day = d.getDate().toString().padStart(2, '0');
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const year = d.getFullYear();
    
    return format.replace('DD', day).replace('MM', month).replace('YYYY', year);
  },

  /**
   * Format number
   * @param {number} number - Number to format
   * @param {string} locale - Locale
   * @returns {string} Formatted number
   */
  formatNumber: function(number, locale) {
    return number.toLocaleString(locale);
  },

  /**
   * Format currency
   * @param {number} amount - Amount to format
   * @param {string} currency - Currency code
   * @returns {string} Formatted currency
   */
  formatCurrency: function(amount, currency) {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: currency
    }).format(amount);
  }
};

// ==========================================================================
// Export Generation Functions
// ==========================================================================

/**
 * Export generation utility functions
 */
const ExportGeneration = {
  /**
   * Generate CSV content
   * @param {Object} data - CSV data
   * @param {Object} options - Generation options
   * @returns {string} CSV content
   */
  generateCSV: function(data, options = {}) {
    const { headers, rows, delimiter = ',', quoteChar = '"', escapeChar = '\\', lineTerminator = '\r\n' } = data;
    
    let csvContent = '';
    
    // Add headers
    if (headers && headers.length > 0) {
      csvContent += headers.map(header => this.escapeCSV(header, quoteChar, escapeChar)).join(delimiter) + lineTerminator;
    }
    
    // Add rows
    rows.forEach(row => {
      csvContent += row.map(cell => this.escapeCSV(cell, quoteChar, escapeChar)).join(delimiter) + lineTerminator;
    });
    
    return csvContent;
  },

  /**
   * Generate JSON content
   * @param {Object} data - JSON data
   * @param {Object} options - Generation options
   * @returns {string} JSON content
   */
  generateJSON: function(data, options = {}) {
    const { indent = 2, pretty = true } = options;
    
    if (pretty) {
      return JSON.stringify(data, null, indent);
    } else {
      return JSON.stringify(data);
    }
  },

  /**
   * Generate XML content
   * @param {Object} data - XML data
   * @param {Object} options - Generation options
   * @returns {string} XML content
   */
  generateXML: function(data, options = {}) {
    const { rootElement = 'data', itemElement = 'item', indent = 2 } = data;
    
    let xmlContent = `<?xml version="1.0" encoding="UTF-8"?>\n`;
    xmlContent += `<${rootElement}>\n`;
    
    data.data.forEach(item => {
      xmlContent += this.generateXMLItem(item, itemElement, indent);
    });
    
    xmlContent += `</${rootElement}>`;
    
    return xmlContent;
  },

  /**
   * Generate XML item
   * @param {Object} item - Data item
   * @param {string} elementName - Element name
   * @param {number} indent - Indentation level
   * @returns {string} XML item
   */
  generateXMLItem: function(item, elementName, indent) {
    let xml = '  '.repeat(indent) + `<${elementName}>\n`;
    
    Object.entries(item).forEach(([key, value]) => {
      const escapedValue = this.escapeXML(value);
      xml += '  '.repeat(indent + 1) + `<${key}>${escapedValue}</${key}>\n`;
    });
    
    xml += '  '.repeat(indent) + `</${elementName}>\n`;
    return xml;
  },

  /**
   * Escape CSV value
   * @param {*} value - Value to escape
   * @param {string} quoteChar - Quote character
   * @param {string} escapeChar - Escape character
   * @returns {string} Escaped value
   */
  escapeCSV: function(value, quoteChar = '"', escapeChar = '\\') {
    if (value === null || value === undefined) {
      return '';
    }
    
    value = value.toString();
    
    // Check if value needs quoting
    if (value.includes(quoteChar) || value.includes(',') || value.includes('\n') || value.includes('\r')) {
      // Escape quotes
      value = value.replace(new RegExp(quoteChar, 'g'), escapeChar + quoteChar);
      return quoteChar + value + quoteChar;
    }
    
    return value;
  },

  /**
   * Escape XML value
   * @param {*} value - Value to escape
   * @returns {string} Escaped value
   */
  escapeXML: function(value) {
    if (value === null || value === undefined) {
      return '';
    }
    
    return value.toString()
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  },

  /**
   * Create download blob
   * @param {string} content - File content
   * @param {string} filename - Filename
   * @param {string} mimeType - MIME type
   * @returns {Blob} Download blob
   */
  createDownloadBlob: function(content, filename, mimeType) {
    return new Blob([content], { type: mimeType });
  },

  /**
   * Trigger file download
   * @param {Blob} blob - File blob
   * @param {string} filename - Filename
   */
  triggerDownload: function(blob, filename) {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }
};

// ==========================================================================
// Export Manager
// ==========================================================================

/**
 * Export manager for handling export operations
 */
const ExportManager = {
  /**
   * Create export manager
   * @param {Object} options - Manager options
   * @returns {Object} Export manager
   */
  createExportManager: function(options = {}) {
    const defaultOptions = {
      maxConcurrentExports: 3,
      retryAttempts: 3,
      retryDelay: 1000,
      showProgress: true,
      autoDownload: true
    };

    const config = { ...defaultOptions, ...options };
    
    return {
      exports: new Map(),
      config: config,
      
      /**
       * Export data
       * @param {Array} data - Data to export
       * @param {Object} exportConfig - Export configuration
       * @returns {Promise<Object>} Export result
       */
      export: async function(data, exportConfig) {
        const exportId = this.generateExportId();
        const exportJob = {
          id: exportId,
          status: 'pending',
          progress: 0,
          createdAt: new Date().toISOString(),
          data: data,
          config: exportConfig
        };
        
        this.exports.set(exportId, exportJob);
        
        try {
          exportJob.status = 'processing';
          const result = await this.processExport(exportJob);
          
          exportJob.status = 'completed';
          exportJob.result = result;
          exportJob.completedAt = new Date().toISOString();
          
          return result;
        } catch (error) {
          exportJob.status = 'failed';
          exportJob.error = error.message;
          exportJob.failedAt = new Date().toISOString();
          
          throw error;
        }
      },
      
      /**
       * Process export job
       * @param {Object} job - Export job
       * @returns {Promise<Object>} Export result
       */
      processExport: async function(job) {
        const { data, config } = job;
        const { format, options } = config;
        
        // Process data
        const processedData = DataProcessing.processData(data, config);
        
        // Generate export content
        let content;
        switch (format) {
          case 'CSV':
            content = ExportGeneration.generateCSV(processedData, options);
            break;
          case 'JSON':
            content = ExportGeneration.generateJSON(processedData, options);
            break;
          case 'XML':
            content = ExportGeneration.generateXML(processedData, options);
            break;
          default:
            throw new Error(`Export format ${format} not implemented`);
        }
        
        // Create download blob
        const mimeType = EXPORT_FORMATS[format].mimeType;
        const filename = `${options.filename}.${EXPORT_FORMATS[format].extension}`;
        const blob = ExportGeneration.createDownloadBlob(content, filename, mimeType);
        
        // Trigger download if autoDownload is enabled
        if (this.config.autoDownload) {
          ExportGeneration.triggerDownload(blob, filename);
        }
        
        return {
          success: true,
          format: format,
          filename: filename,
          size: blob.size,
          blob: blob,
          content: content
        };
      },
      
      /**
       * Generate export ID
       * @returns {string} Export ID
       */
      generateExportId: function() {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 9);
        return `export_${timestamp}_${random}`;
      },
      
      /**
       * Get export status
       * @param {string} exportId - Export ID
       * @returns {Object} Export status
       */
      getExportStatus: function(exportId) {
        return this.exports.get(exportId) || null;
      },
      
      /**
       * Get all exports
       * @returns {Array} All exports
       */
      getAllExports: function() {
        return Array.from(this.exports.values());
      },
      
      /**
       * Cancel export
       * @param {string} exportId - Export ID
       * @returns {boolean} Success status
       */
      cancelExport: function(exportId) {
        const exportJob = this.exports.get(exportId);
        if (exportJob && exportJob.status === 'processing') {
          exportJob.status = 'cancelled';
          exportJob.cancelledAt = new Date().toISOString();
          return true;
        }
        return false;
      },
      
      /**
       * Cleanup completed exports
       * @param {number} maxAge - Maximum age in milliseconds
       */
      cleanup: function(maxAge = 3600000) { // 1 hour default
        const now = Date.now();
        const cutoff = now - maxAge;
        
        for (const [exportId, exportJob] of this.exports.entries()) {
          const completedAt = new Date(exportJob.completedAt || exportJob.failedAt).getTime();
          if (completedAt < cutoff) {
            this.exports.delete(exportId);
          }
        }
      }
    };
  }
};

// ==========================================================================
// Export Templates
// ==========================================================================

/**
 * Export templates for common use cases
 */
const ExportTemplates = {
  /**
   * Assessment report template
   * @returns {Object} Template configuration
   */
  assessmentReport: function() {
    return {
      title: { id: 'Laporan Penilaian SAKIP', en: 'SAKIP Assessment Report' },
      format: 'PDF',
      options: {
        includeHeaders: true,
        includeMetadata: true,
        pageSize: 'A4',
        orientation: 'portrait',
        theme: 'government'
      },
      columns: [
        { field: 'institution_name', header: { id: 'Nama Institusi', en: 'Institution Name' } },
        { field: 'assessment_year', header: { id: 'Tahun', en: 'Year' } },
        { field: 'score', header: { id: 'Nilai', en: 'Score' } },
        { field: 'grade', header: { id: 'Grade', en: 'Grade' } },
        { field: 'status', header: { id: 'Status', en: 'Status' } }
      ]
    };
  },

  /**
   * Institution data template
   * @returns {Object} Template configuration
   */
  institutionData: function() {
    return {
      title: { id: 'Data Institusi', en: 'Institution Data' },
      format: 'EXCEL',
      options: {
        includeHeaders: true,
        includeMetadata: true,
        sheetName: 'Institusi'
      },
      columns: [
        { field: 'code', header: { id: 'Kode', en: 'Code' } },
        { field: 'name', header: { id: 'Nama', en: 'Name' } },
        { field: 'type', header: { id: 'Tipe', en: 'Type' } },
        { field: 'level', header: { id: 'Tingkat', en: 'Level' } },
        { field: 'address', header: { id: 'Alamat', en: 'Address' } }
      ]
    };
  },

  /**
   * Audit trail template
   * @returns {Object} Template configuration
   */
  auditTrail: function() {
    return {
      title: { id: 'Jejak Audit', en: 'Audit Trail' },
      format: 'CSV',
      options: {
        includeHeaders: true,
        includeMetadata: true,
        delimiter: ';'
      },
      columns: [
        { field: 'timestamp', header: { id: 'Waktu', en: 'Timestamp' } },
        { field: 'user', header: { id: 'Pengguna', en: 'User' } },
        { field: 'action', header: { id: 'Aksi', en: 'Action' } },
        { field: 'entity', header: { id: 'Entitas', en: 'Entity' } },
        { field: 'details', header: { id: 'Detail', en: 'Details' } }
      ]
    };
  }
};

// ==========================================================================
// Export Status Badge Functions
// ==========================================================================

/**
 * Export status badge functions
 */
const ExportStatusBadges = {
  /**
   * Generate export status badge
   * @param {string} status - Export status
   * @param {Object} options - Badge options
   * @returns {Object} Badge configuration
   */
  generateExportStatusBadge: function(status, options = {}) {
    const statusConfig = {
      pending: {
        text: { id: 'Menunggu', en: 'Pending' },
        class: 'badge-export-pending',
        icon: '⏳',
        color: '#6c757d',
        backgroundColor: '#f8f9fa',
        borderColor: '#6c757d'
      },
      processing: {
        text: { id: 'Memproses', en: 'Processing' },
        class: 'badge-export-processing',
        icon: '⚙️',
        color: '#17a2b8',
        backgroundColor: '#d1ecf1',
        borderColor: '#17a2b8'
      },
      completed: {
        text: { id: 'Selesai', en: 'Completed' },
        class: 'badge-export-completed',
        icon: '✅',
        color: '#28a745',
        backgroundColor: '#d4edda',
        borderColor: '#28a745'
      },
      failed: {
        text: { id: 'Gagal', en: 'Failed' },
        class: 'badge-export-failed',
        icon: '❌',
        color: '#dc3545',
        backgroundColor: '#f8d7da',
        borderColor: '#dc3545'
      },
      cancelled: {
        text: { id: 'Dibatalkan', en: 'Cancelled' },
        class: 'badge-export-cancelled',
        icon: '🚫',
        color: '#6c757d',
        backgroundColor: '#e9ecef',
        borderColor: '#6c757d'
      }
    };

    const config = statusConfig[status];
    if (!config) return null;

    const defaultOptions = {
      showIcon: true,
      showText: true,
      language: 'id',
      size: 'medium',
      variant: 'default'
    };

    const finalOptions = { ...defaultOptions, ...options };

    return {
      class: `sakip-badge ${config.class} ${finalOptions.size} ${finalOptions.variant}`,
      style: {
        color: config.color,
        backgroundColor: config.backgroundColor,
        borderColor: config.borderColor
      },
      content: this.generateBadgeContent(config, finalOptions),
      ariaLabel: `Status Export: ${config.text[finalOptions.language]}`,
      title: config.text[finalOptions.language]
    };
  },

  /**
   * Generate badge content
   * @param {Object} config - Badge configuration
   * @param {Object} options - Content options
   * @returns {string} Badge content
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
// Export All Functions
// ==========================================================================

/**
 * Export all export utility functions
 */
const SAKIP_EXPORT_UTILS = {
  FORMATS: EXPORT_FORMATS,
  CONFIG: ExportConfig,
  DATA_PROCESSING: DataProcessing,
  GENERATION: ExportGeneration,
  MANAGER: ExportManager,
  TEMPLATES: ExportTemplates,
  STATUS_BADGES: ExportStatusBadges
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_EXPORT_UTILS;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_EXPORT_UTILS;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.EXPORT_UTILS = SAKIP_EXPORT_UTILS;
}