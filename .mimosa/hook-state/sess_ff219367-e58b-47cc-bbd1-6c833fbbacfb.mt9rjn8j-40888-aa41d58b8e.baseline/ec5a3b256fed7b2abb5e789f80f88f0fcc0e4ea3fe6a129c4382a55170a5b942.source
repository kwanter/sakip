/**
 * SAKIP Report Generator
 * Government-style report generation utilities for SAKIP module
 * Provides report building, rendering, and download helpers (CSV/JSON/XML/PDF/Excel*).
 *
 * Note: PDF/Excel generation depends on libraries configured in SAKIP_EXPORT_UTILS.
 */
(function (global, factory) {
  if (typeof exports === 'object' && typeof module !== 'undefined') {
    module.exports = factory();
  } else if (typeof define === 'function' && define.amd) {
    define(factory);
  } else {
    (global.SAKIP = global.SAKIP || {}).REPORT_GENERATOR = factory();
  }
}(typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : this, function () {
  'use strict';

  const REPORT_CONSTANTS = {
    TYPES: {
      ASSESSMENT: 'assessment',
      INSTITUTION: 'institution',
      AUDIT_TRAIL: 'audit_trail',
      DASHBOARD_SUMMARY: 'dashboard_summary'
    },
    DEFAULT_OPTIONS: {
      title: 'Laporan SAKIP',
      language: 'id',
      includeMetadata: true,
      includeCharts: false,
      dateFormat: 'YYYY-MM-DD',
      numberLocale: 'id-ID',
      currency: 'IDR',
      format: 'PDF',
      filename: 'laporan-sakip'
    }
  };

  /**
   * ReportBuilder: normalizes input data and composes a report payload
   */
  class ReportBuilder {
    buildPayload(data = {}, options = {}) {
      const opts = { ...REPORT_CONSTANTS.DEFAULT_OPTIONS, ...options };
      const now = new Date();
      const metadata = opts.includeMetadata ? {
        generated_at: now.toISOString(),
        generator: 'SAKIP Report Generator',
        version: '1.0.0'
      } : undefined;

      return {
        title: opts.title,
        type: opts.type || REPORT_CONSTANTS.TYPES.DASHBOARD_SUMMARY,
        sections: this.composeSections(data, opts),
        charts: opts.includeCharts ? (data.charts || []) : [],
        filters: data.filters || {},
        metadata
      };
    }

    composeSections(data, opts) {
      const sections = [];
      if (data.summary) {
        sections.push({ id: 'summary', label: 'Ringkasan', content: data.summary });
      }
      if (Array.isArray(data.tables)) {
        data.tables.forEach((t, i) => {
          sections.push({ id: `table_${i + 1}`, label: t.title || `Tabel ${i + 1}`, content: t.rows || [] });
        });
      }
      if (Array.isArray(data.items)) {
        sections.push({ id: 'items', label: 'Data', content: data.items });
      }
      return sections;
    }
  }

  /**
   * ReportRenderer: delegates to SAKIP_EXPORT_UTILS to generate content and download
   */
  class ReportRenderer {
    constructor() {
      this.utils = (typeof window !== 'undefined' ? window.SAKIP?.EXPORT_UTILS : undefined) ||
                   (typeof require === 'function' ? (() => { try { return require('./helpers/sakip-export-utils.js'); } catch (_) { return null; } })() : null);
      if (!this.utils) throw new Error('SAKIP_EXPORT_UTILS not available');
    }

    renderAndDownload(payload, options = {}) {
      const fmt = (options.format || REPORT_CONSTANTS.DEFAULT_OPTIONS.format).toUpperCase();
      const filename = (options.filename || REPORT_CONSTANTS.DEFAULT_OPTIONS.filename) + '.' + (this.utils.FORMATS[fmt]?.extension || fmt.toLowerCase());

      const processed = this.utils.DATA_PROCESSING.processData(payload, {
        format: fmt,
        dateFormat: options.dateFormat || REPORT_CONSTANTS.DEFAULT_OPTIONS.dateFormat,
        numberLocale: options.numberLocale || REPORT_CONSTANTS.DEFAULT_OPTIONS.numberLocale,
        currency: options.currency || REPORT_CONSTANTS.DEFAULT_OPTIONS.currency
      });

      switch (fmt) {
        case 'CSV':
          return this.utils.GENERATION.generateCSV(processed, { filename });
        case 'JSON':
          return this.utils.GENERATION.generateJSON(processed, { filename });
        case 'XML':
          return this.utils.GENERATION.generateXML(processed, { filename });
        case 'EXCEL':
        case 'PDF':
        default:
          // Delegate to ExportManager for formats that may need libraries
          const manager = this.utils.MANAGER.createExportManager();
          return manager.export(processed, { format: fmt, filename });
      }
    }
  }

  /**
   * ReportManager: high-level API to build and export reports
   */
  class ReportManager {
    constructor() {
      this.builder = new ReportBuilder();
      this.renderer = new ReportRenderer();
    }

    generateReport(data, options = {}) {
      const payload = this.builder.buildPayload(data, options);
      return this.renderer.renderAndDownload(payload, options);
    }

    previewJSON(data, options = {}) {
      const payload = this.builder.buildPayload(data, options);
      return JSON.stringify(payload, null, 2);
    }
  }

  /**
   * Simple UI helper to wire a container with controls
   */
  class ReportUIManager {
    create(containerId, config = {}) {
      const el = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
      if (!el) return null;
      el.innerHTML = `
        <div class="sakip-card">
          <div class="sakip-card-header"><strong>Generator Laporan</strong></div>
          <div class="sakip-card-body sakip-form-group">
            <label class="sakip-form-label">Judul</label>
            <input id="rg-title" class="sakip-form-control" placeholder="Laporan SAKIP" />
            <label class="sakip-form-label sakip-mt-3">Format</label>
            <select id="rg-format" class="sakip-form-control">
              <option>PDF</option><option>EXCEL</option><option>CSV</option><option>JSON</option><option>XML</option>
            </select>
            <button id="rg-generate" class="sakip-btn sakip-btn-primary sakip-mt-3">Generate</button>
            <pre id="rg-preview" class="sakip-mt-3" style="max-height:240px;overflow:auto"></pre>
          </div>
        </div>`;
      const mgr = new ReportManager();
      el.querySelector('#rg-generate').addEventListener('click', () => {
        const title = el.querySelector('#rg-title').value || REPORT_CONSTANTS.DEFAULT_OPTIONS.title;
        const format = el.querySelector('#rg-format').value;
        const data = config.data || { summary: 'Ringkasan tidak tersedia', items: [] };
        const preview = el.querySelector('#rg-preview');
        preview.textContent = mgr.previewJSON(data, { title, format });
        mgr.generateReport(data, { title, format });
      });
      return el;
    }

    destroy(containerId) {
      const el = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
      if (el) el.innerHTML = '';
    }
  }

  const SAKIP_REPORT_GENERATOR = {
    CONSTANTS: REPORT_CONSTANTS,
    ReportBuilder,
    ReportRenderer,
    ReportManager,
    ReportUIManager,
    manager: new ReportManager(),
    ui: new ReportUIManager(),
    generate: (data, options) => (new ReportManager()).generateReport(data, options),
    preview: (data, options) => (new ReportManager()).previewJSON(data, options),
    createUI: (containerId, config) => (new ReportUIManager()).create(containerId, config),
    destroyUI: (containerId) => (new ReportUIManager()).destroy(containerId)
  };

  return SAKIP_REPORT_GENERATOR;
}));