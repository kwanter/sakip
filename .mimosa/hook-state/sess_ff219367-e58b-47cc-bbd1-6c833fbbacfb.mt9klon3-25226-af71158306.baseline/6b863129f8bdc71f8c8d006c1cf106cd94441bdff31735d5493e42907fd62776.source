/**
 * SAKIP Templates Configuration
 * Report and form templates for government-style SAKIP module
 */

// ==========================================================================
// Report Templates
// ==========================================================================

/**
 * Standard report templates for SAKIP assessments
 */
const REPORT_TEMPLATES = {
  // Performance Assessment Report
  PERFORMANCE_ASSESSMENT: {
    id: 'performance_assessment',
    name: 'Laporan Penilaian Kinerja',
    description: 'Template laporan penilaian kinerja instansi pemerintah',
    category: 'assessment',
    format: 'pdf',
    sections: [
      {
        id: 'executive_summary',
        title: 'Executive Summary',
        type: 'summary',
        required: true,
        order: 1
      },
      {
        id: 'institution_profile',
        title: 'Profil Instansi',
        type: 'profile',
        required: true,
        order: 2
      },
      {
        id: 'assessment_results',
        title: 'Hasil Penilaian',
        type: 'results',
        required: true,
        order: 3
      },
      {
        id: 'indicator_analysis',
        title: 'Analisis Indikator',
        type: 'analysis',
        required: true,
        order: 4
      },
      {
        id: 'recommendations',
        title: 'Rekomendasi',
        type: 'recommendations',
        required: true,
        order: 5
      },
      {
        id: 'action_plan',
        title: 'Rencana Tindak Lanjut',
        type: 'action_plan',
        required: false,
        order: 6
      }
    ],
    styling: {
      font_family: 'Times New Roman',
      font_size: 12,
      line_height: 1.5,
      margin: '2cm',
      header_style: 'government',
      footer_style: 'standard'
    },
    metadata: {
      author: 'Sistem SAKIP',
      subject: 'Laporan Penilaian Kinerja',
      keywords: 'SAKIP, kinerja, pemerintah, penilaian',
      language: 'id'
    }
  },

  // Financial Assessment Report
  FINANCIAL_ASSESSMENT: {
    id: 'financial_assessment',
    name: 'Laporan Penilaian Keuangan',
    description: 'Template laporan penilaian keuangan dan anggaran',
    category: 'financial',
    format: 'excel',
    sections: [
      {
        id: 'budget_overview',
        title: 'Ikhtisar Anggaran',
        type: 'overview',
        required: true,
        order: 1
      },
      {
        id: 'financial_indicators',
        title: 'Indikator Keuangan',
        type: 'indicators',
        required: true,
        order: 2
      },
      {
        id: 'variance_analysis',
        title: 'Analisis Variansi',
        type: 'analysis',
        required: true,
        order: 3
      },
      {
        id: 'compliance_check',
        title: 'Pemeriksaan Kepatuhan',
        type: 'compliance',
        required: true,
        order: 4
      }
    ],
    styling: {
      header_color: '#1f2937',
      header_text_color: '#ffffff',
      alternating_rows: true,
      grid_lines: true,
      font_family: 'Arial',
      font_size: 10
    }
  },

  // Risk Assessment Report
  RISK_ASSESSMENT: {
    id: 'risk_assessment',
    name: 'Laporan Penilaian Risiko',
    description: 'Template laporan penilaian risiko organisasi',
    category: 'risk',
    format: 'pdf',
    sections: [
      {
        id: 'risk_identification',
        title: 'Identifikasi Risiko',
        type: 'identification',
        required: true,
        order: 1
      },
      {
        id: 'risk_analysis',
        title: 'Analisis Risiko',
        type: 'analysis',
        required: true,
        order: 2
      },
      {
        id: 'risk_matrix',
        title: 'Matriks Risiko',
        type: 'matrix',
        required: true,
        order: 3
      },
      {
        id: 'mitigation_plan',
        title: 'Rencana Mitigasi',
        type: 'mitigation',
        required: true,
        order: 4
      }
    ]
  },

  // Comparative Analysis Report
  COMPARATIVE_ANALYSIS: {
    id: 'comparative_analysis',
    name: 'Laporan Analisis Komparatif',
    description: 'Template laporan analisis komparatif antar instansi',
    category: 'comparative',
    format: 'excel',
    sections: [
      {
        id: 'benchmark_overview',
        title: 'Ikhtisar Benchmark',
        type: 'overview',
        required: true,
        order: 1
      },
      {
        id: 'performance_comparison',
        title: 'Perbandingan Kinerja',
        type: 'comparison',
        required: true,
        order: 2
      },
      {
        id: 'ranking_analysis',
        title: 'Analisis Peringkat',
        type: 'ranking',
        required: true,
        order: 3
      },
      {
        id: 'best_practices',
        title: 'Praktik Terbaik',
        type: 'best_practices',
        required: false,
        order: 4
      }
    ]
  }
};

// ==========================================================================
// Form Templates
// ==========================================================================

/**
 * Form templates for data entry and assessment
 */
const FORM_TEMPLATES = {
  // Institution Registration Form
  INSTITUTION_REGISTRATION: {
    id: 'institution_registration',
    name: 'Formulir Registrasi Instansi',
    description: 'Template formulir pendaftaran instansi pemerintah',
    category: 'registration',
    sections: [
      {
        id: 'basic_info',
        title: 'Informasi Dasar',
        order: 1,
        fields: [
          {
            id: 'institution_name',
            label: 'Nama Instansi',
            type: 'text',
            required: true,
            validation: {
              min_length: 3,
              max_length: 200,
              pattern: '^[a-zA-Z0-9\\s\\-\\.\(\)]+$'
            }
          },
          {
            id: 'institution_type',
            label: 'Jenis Instansi',
            type: 'select',
            required: true,
            options: [
              { value: 'ministry', label: 'Kementerian' },
              { value: 'agency', label: 'Lembaga' },
              { value: 'department', label: 'Dinas' },
              { value: 'unit', label: 'Unit Kerja' }
            ]
          },
          {
            id: 'institution_level',
            label: 'Tingkat Instansi',
            type: 'select',
            required: true,
            options: [
              { value: 'central', label: 'Pusat' },
              { value: 'provincial', label: 'Provinsi' },
              { value: 'regency', label: 'Kabupaten/Kota' },
              { value: 'district', label: 'Kecamatan' }
            ]
          }
        ]
      },
      {
        id: 'contact_info',
        title: 'Informasi Kontak',
        order: 2,
        fields: [
          {
            id: 'address',
            label: 'Alamat',
            type: 'textarea',
            required: true,
            validation: {
              min_length: 10,
              max_length: 500
            }
          },
          {
            id: 'phone',
            label: 'Telepon',
            type: 'tel',
            required: true,
            validation: {
              pattern: '^[0-9\\+\\-\\s]{10,15}$'
            }
          },
          {
            id: 'email',
            label: 'Email',
            type: 'email',
            required: true,
            validation: {
              pattern: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$'
            }
          }
        ]
      },
      {
        id: 'authorization',
        title: 'Otorisasi',
        order: 3,
        fields: [
          {
            id: 'authorized_person',
            label: 'Penanggung Jawab',
            type: 'text',
            required: true,
            validation: {
              min_length: 3,
              max_length: 100
            }
          },
          {
            id: 'position',
            label: 'Jabatan',
            type: 'text',
            required: true,
            validation: {
              min_length: 3,
              max_length: 100
            }
          },
          {
            id: 'authorization_letter',
            label: 'Surat Otorisasi',
            type: 'file',
            required: true,
            validation: {
              file_types: ['pdf', 'doc', 'docx'],
              max_size: 5242880 // 5MB
            }
          }
        ]
      }
    ],
    styling: {
      layout: 'vertical',
      show_progress: true,
      allow_save_draft: true,
      validation_mode: 'real_time'
    }
  },

  // Assessment Scoring Form
  ASSESSMENT_SCORING: {
    id: 'assessment_scoring',
    name: 'Formulir Penilaian',
    description: 'Template formulir untuk penilaian kinerja',
    category: 'assessment',
    sections: [
      {
        id: 'indicator_selection',
        title: 'Pemilihan Indikator',
        order: 1,
        fields: [
          {
            id: 'assessment_period',
            label: 'Periode Penilaian',
            type: 'select',
            required: true,
            options: [
              { value: '2024', label: 'Tahun 2024' },
              { value: '2023', label: 'Tahun 2023' },
              { value: '2022', label: 'Tahun 2022' }
            ]
          },
          {
            id: 'assessment_type',
            label: 'Jenis Penilaian',
            type: 'select',
            required: true,
            options: [
              { value: 'renstra', label: 'RENSTRA' },
              { value: 'renja', label: 'RENJA' },
              { value: 'pk', label: 'Perjanjian Kinerja' },
              { value: 'iku', label: 'IKU' }
            ]
          }
        ]
      },
      {
        id: 'scoring_section',
        title: 'Penilaian',
        order: 2,
        dynamic: true,
        fields: [
          {
            id: 'indicator_score',
            label: 'Skor Indikator',
            type: 'number',
            required: true,
            validation: {
              min: 0,
              max: 100,
              step: 0.1
            }
          },
          {
            id: 'evidence_upload',
            label: 'Unggah Bukti',
            type: 'file',
            required: true,
            multiple: true,
            validation: {
              file_types: ['pdf', 'jpg', 'jpeg', 'png', 'xlsx'],
              max_size: 10485760, // 10MB
              max_files: 5
            }
          },
          {
            id: 'notes',
            label: 'Catatan',
            type: 'textarea',
            required: false,
            validation: {
              max_length: 1000
            }
          }
        ]
      }
    ],
    styling: {
      layout: 'horizontal',
      show_progress: true,
      auto_save: true,
      validation_mode: 'on_submit'
    }
  },

  // Evidence Upload Form
  EVIDENCE_UPLOAD: {
    id: 'evidence_upload',
    name: 'Formulir Unggah Bukti',
    description: 'Template formulir untuk mengunggah bukti pendukung',
    category: 'evidence',
    sections: [
      {
        id: 'evidence_info',
        title: 'Informasi Bukti',
        order: 1,
        fields: [
          {
            id: 'evidence_type',
            label: 'Jenis Bukti',
            type: 'select',
            required: true,
            options: [
              { value: 'document', label: 'Dokumen' },
              { value: 'image', label: 'Gambar' },
              { value: 'video', label: 'Video' },
              { value: 'data', label: 'Data' },
              { value: 'link', label: 'Tautan' }
            ]
          },
          {
            id: 'evidence_title',
            label: 'Judul Bukti',
            type: 'text',
            required: true,
            validation: {
              min_length: 5,
              max_length: 200
            }
          },
          {
            id: 'description',
            label: 'Deskripsi',
            type: 'textarea',
            required: true,
            validation: {
              min_length: 10,
              max_length: 1000
            }
          }
        ]
      },
      {
        id: 'file_upload',
        title: 'Unggah File',
        order: 2,
        fields: [
          {
            id: 'files',
            label: 'Pilih File',
            type: 'file',
            required: true,
            multiple: true,
            validation: {
              file_types: ['pdf', 'doc', 'docx', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4'],
              max_size: 52428800, // 50MB
              max_files: 10
            }
          },
          {
            id: 'confidentiality',
            label: 'Tingkat Kerahasiaan',
            type: 'select',
            required: true,
            options: [
              { value: 'public', label: 'Publik' },
              { value: 'internal', label: 'Internal' },
              { value: 'confidential', label: 'Rahasia' },
              { value: 'restricted', label: 'Terbatas' }
            ]
          }
        ]
      }
    ],
    styling: {
      layout: 'vertical',
      show_progress: false,
      drag_drop_enabled: true,
      preview_enabled: true
    }
  }
};

// ==========================================================================
// Email Templates
// ==========================================================================

/**
 * Email notification templates
 */
const EMAIL_TEMPLATES = {
  // Assessment Submission Confirmation
  ASSESSMENT_SUBMISSION: {
    id: 'assessment_submission',
    subject: 'Konfirmasi Pengiriman Penilaian - SAKIP',
    template: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: #1f2937; color: white; padding: 20px; text-align: center;">
          <h2>Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)</h2>
        </div>
        <div style="padding: 30px; background: #f9fafb;">
          <h3 style="color: #1f2937;">Halo {{user_name}},</h3>
          <p>Penilaian kinerja Anda telah berhasil dikirim dan sedang dalam proses review.</p>
          
          <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #1f2937; margin-top: 0;">Detail Penilaian:</h4>
            <table style="width: 100%; border-collapse: collapse;">
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Instansi:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{institution_name}}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Periode:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{assessment_period}}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Skor Total:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{total_score}}/100</td>
              </tr>
              <tr>
                <td style="padding: 8px;"><strong>Status:</strong></td>
                <td style="padding: 8px;"><span style="color: #059669;">Dikirim untuk Review</span></td>
              </tr>
            </table>
          </div>
          
          <p>Anda akan menerima notifikasi tambahan saat penilaian Anda telah direview.</p>
          
          <div style="text-align: center; margin: 30px 0;">
            <a href="{{dashboard_url}}" style="background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Lihat Dashboard</a>
          </div>
          
          <p style="color: #6b7280; font-size: 12px;">
            Email ini dikirim secara otomatis oleh Sistem SAKIP. Jangan membalas email ini.
          </p>
        </div>
        <div style="background: #1f2937; color: white; padding: 20px; text-align: center; font-size: 12px;">
          <p>© 2024 Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi</p>
        </div>
      </div>
    `,
    variables: ['user_name', 'institution_name', 'assessment_period', 'total_score', 'dashboard_url']
  },

  // Assessment Review Notification
  ASSESSMENT_REVIEW: {
    id: 'assessment_review',
    subject: 'Penilaian Baru Perlu Direview - SAKIP',
    template: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: #1f2937; color: white; padding: 20px; text-align: center;">
          <h2>Sistem SAKIP</h2>
        </div>
        <div style="padding: 30px; background: #f9fafb;">
          <h3 style="color: #1f2937;">Halo {{reviewer_name}},</h3>
          <p>Ada penilaian kinerja baru yang membutuhkan review Anda.</p>
          
          <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #1f2937; margin-top: 0;">Detail Penilaian:</h4>
            <table style="width: 100%; border-collapse: collapse;">
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Instansi:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{institution_name}}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Periode:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{assessment_period}}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;"><strong>Dikirim oleh:</strong></td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{submitted_by}}</td>
              </tr>
              <tr>
                <td style="padding: 8px;"><strong>Dikirim pada:</strong></td>
                <td style="padding: 8px;">{{submitted_at}}</td>
              </tr>
            </table>
          </div>
          
          <div style="text-align: center; margin: 30px 0;">
            <a href="{{review_url}}" style="background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Review Penilaian</a>
          </div>
          
          <p style="color: #6b7280; font-size: 12px;">
            Email ini dikirim secara otomatis oleh Sistem SAKIP.
          </p>
        </div>
        <div style="background: #1f2937; color: white; padding: 20px; text-align: center; font-size: 12px;">
          <p>© 2024 Kementerian PANRB</p>
        </div>
      </div>
    `,
    variables: ['reviewer_name', 'institution_name', 'assessment_period', 'submitted_by', 'submitted_at', 'review_url']
  },

  // System Alert
  SYSTEM_ALERT: {
    id: 'system_alert',
    subject: 'Pemberitahuan Sistem - SAKIP',
    template: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: #dc2626; color: white; padding: 20px; text-align: center;">
          <h2>Pemberitahuan Sistem</h2>
        </div>
        <div style="padding: 30px; background: #f9fafb;">
          <h3 style="color: #dc2626;">Penting: {{alert_title}}</h3>
          <p>{{alert_message}}</p>
          
          <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626;">
            <h4 style="margin-top: 0;">Detail:</h4>
            <p><strong>Tanggal:</strong> {{alert_date}}</p>
            <p><strong>Tingkat:</strong> {{alert_level}}</p>
            <p><strong>Sistem:</strong> {{system_name}}</p>
          </div>
          
          <div style="text-align: center; margin: 30px 0;">
            <a href="{{action_url}}" style="background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">{{action_text}}</a>
          </div>
          
          <p style="color: #6b7280; font-size: 12px;">
            Email ini dikirim secara otomatis oleh Sistem SAKIP.
          </p>
        </div>
      </div>
    `,
    variables: ['alert_title', 'alert_message', 'alert_date', 'alert_level', 'system_name', 'action_url', 'action_text']
  }
};

// ==========================================================================
// Document Templates
// ==========================================================================

/**
 * Document templates for government documents
 */
const DOCUMENT_TEMPLATES = {
  // Assessment Letter Template
  ASSESSMENT_LETTER: {
    id: 'assessment_letter',
    name: 'Surat Penilaian',
    description: 'Template surat resmi untuk hasil penilaian',
    category: 'official_letter',
    format: 'pdf',
    content: `
      <div style="font-family: 'Times New Roman', serif; max-width: 800px; margin: 0 auto; padding: 40px;">
        <!-- Kop Surat -->
        <div style="text-align: center; border-bottom: 3px solid #1f2937; padding-bottom: 20px; margin-bottom: 30px;">
          <div style="display: flex; align-items: center; justify-content: center;">
            <img src="{{logo_url}}" alt="Logo" style="width: 80px; height: 80px; margin-right: 20px;">
            <div>
              <h2 style="margin: 0; color: #1f2937;">PEMERINTAH REPUBLIK INDONESIA</h2>
              <h1 style="margin: 5px 0; color: #1f2937;">{{institution_name}}</h1>
              <p style="margin: 0; color: #6b7280;">{{institution_address}}</p>
            </div>
          </div>
        </div>

        <!-- Nomor dan Tanggal -->
        <div style="margin: 30px 0;">
          <table style="width: 100%;">
            <tr>
              <td style="width: 100px;">Nomor</td>
              <td>: {{letter_number}}</td>
              <td style="text-align: right;">{{city}}, {{date}}</td>
            </tr>
            <tr>
              <td>Lampiran</td>
              <td>: {{attachment_count}} berkas</td>
              <td></td>
            </tr>
            <tr>
              <td>Perihal</td>
              <td>: {{subject}}</td>
              <td></td>
            </tr>
          </table>
        </div>

        <!-- Isi Surat -->
        <div style="margin: 40px 0;">
          <p>Kepada Yth.</p>
          <p><strong>{{recipient_name}}</strong></p>
          <p>{{recipient_position}}</p>
          <p>{{recipient_address}}</p>
          <p style="margin-top: 30px;">Dengan hormat,</p>
          
          <p style="text-align: justify; line-height: 1.6;">
            {{letter_content}}
          </p>
          
          <p style="text-align: justify; line-height: 1.6;">
            {{closing_content}}
          </p>
          
          <p style="margin-top: 40px;">Demikian surat ini kami sampaikan. Atas perhatian dan kerjasama yang baik, kami ucapkan terima kasih.</p>
        </div>

        <!-- Tanda Tangan -->
        <div style="margin: 60px 0; text-align: right;">
          <p>{{sender_position}},</p>
          <div style="height: 80px;"></div>
          <p><strong>{{sender_name}}</strong></p>
          <p>NIP. {{sender_nip}}</p>
        </div>

        <!-- Tembusan -->
        <div style="margin-top: 40px;">
          <p><strong>Tembusan:</strong></p>
          <ol style="margin-left: 20px;">
            {{cc_list}}
          </ol>
        </div>
      </div>
    `,
    variables: [
      'logo_url', 'institution_name', 'institution_address', 'letter_number',
      'city', 'date', 'attachment_count', 'subject', 'recipient_name',
      'recipient_position', 'recipient_address', 'letter_content',
      'closing_content', 'sender_position', 'sender_name', 'sender_nip', 'cc_list'
    ]
  },

  // Assessment Certificate Template
  ASSESSMENT_CERTIFICATE: {
    id: 'assessment_certificate',
    name: 'Sertifikat Penilaian',
    description: 'Template sertifikat hasil penilaian kinerja',
    category: 'certificate',
    format: 'pdf',
    content: `
      <div style="font-family: 'Times New Roman', serif; text-align: center; padding: 40px; border: 5px solid #1f2937; border-radius: 20px;">
        <div style="margin-bottom: 30px;">
          <img src="{{logo_url}}" alt="Logo" style="width: 100px; height: 100px;">
        </div>
        
        <h1 style="color: #1f2937; font-size: 36px; margin: 20px 0;">SERTIFIKAT</h1>
        <h2 style="color: #6b7280; font-size: 24px; margin: 20px 0;">Nomor: {{certificate_number}}</h2>
        
        <div style="margin: 40px 0; font-size: 18px; line-height: 1.8;">
          <p>Diberikan kepada:</p>
          <h3 style="color: #1f2937; font-size: 28px; margin: 20px 0;">{{institution_name}}</h3>
          
          <p>Atas pencapaian kinerja dalam penilaian akuntabilitas kinerja instansi pemerintah</p>
          <p>Periode: {{assessment_period}}</p>
          
          <div style="background: #f3f4f6; padding: 20px; border-radius: 10px; margin: 30px auto; max-width: 400px;">
            <h2 style="color: #059669; margin: 0;">Predikat: {{achievement_level}}</h2>
            <p style="margin: 10px 0;">Skor: {{total_score}}/100</p>
          </div>
          
          <p style="font-size: 16px; color: #6b7280;">{{assessment_date}}</p>
        </div>
        
        <div style="margin-top: 60px;">
          <p style="margin-bottom: 80px;">{{city}}, {{date}}</p>
          <p><strong>{{authority_name}}</strong></p>
          <p>{{authority_position}}</p>
          <p>NIP. {{authority_nip}}</p>
        </div>
        
        <div style="margin-top: 40px; font-size: 12px; color: #6b7280;">
          <p>Sertifikat ini diterbitkan secara elektronik dan memiliki kekuatan hukum yang sah</p>
          <p>Verifikasi: {{verification_url}}</p>
        </div>
      </div>
    `,
    variables: [
      'logo_url', 'certificate_number', 'institution_name', 'assessment_period',
      'achievement_level', 'total_score', 'assessment_date', 'city', 'date',
      'authority_name', 'authority_position', 'authority_nip', 'verification_url'
    ]
  }
};

// ==========================================================================
// Chart Templates
// ==========================================================================

/**
 * Chart configuration templates
 */
const CHART_TEMPLATES = {
  // Performance Overview Chart
  PERFORMANCE_OVERVIEW: {
    id: 'performance_overview',
    name: 'Grafik Ikhtisar Kinerja',
    description: 'Template grafik untuk menampilkan ikhtisar kinerja',
    category: 'performance',
    config: {
      type: 'bar',
      data: {
        labels: ['Indikator 1', 'Indikator 2', 'Indikator 3', 'Indikator 4', 'Indikator 5'],
        datasets: [{
          label: 'Skor',
          data: [85, 92, 78, 88, 95],
          backgroundColor: [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)'
          ],
          borderColor: [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Ikhtisar Kinerja Indikator',
            font: {
              size: 16,
              weight: 'bold'
            }
          },
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              stepSize: 20
            }
          }
        }
      }
    }
  },

  // Trend Analysis Chart
  TREND_ANALYSIS: {
    id: 'trend_analysis',
    name: 'Grafik Analisis Tren',
    description: 'Template grafik untuk menampilkan tren kinerja dari waktu ke waktu',
    category: 'trend',
    config: {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        datasets: [
          {
            label: 'Tahun 2024',
            data: [65, 72, 78, 82, 85, 88, 90, 92, 89, 91, 93, 95],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
          },
          {
            label: 'Tahun 2023',
            data: [60, 68, 72, 75, 78, 80, 82, 85, 83, 86, 88, 90],
            borderColor: 'rgb(156, 163, 175)',
            backgroundColor: 'rgba(156, 163, 175, 0.1)',
            tension: 0.4
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Tren Kinerja Tahunan',
            font: {
              size: 16,
              weight: 'bold'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            min: 50,
            max: 100,
            ticks: {
              stepSize: 10
            }
          }
        }
      }
    }
  },

  // Comparative Analysis Chart
  COMPARATIVE_ANALYSIS: {
    id: 'comparative_analysis',
    name: 'Grafik Analisis Komparatif',
    description: 'Template grafik untuk menampilkan perbandingan antar instansi',
    category: 'comparative',
    config: {
      type: 'radar',
      data: {
        labels: ['Perencanaan', 'Pelaksanaan', 'Pengawasan', 'Evaluasi', 'Pelaporan'],
        datasets: [
          {
            label: 'Instansi A',
            data: [85, 90, 78, 88, 92],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(59, 130, 246)'
          },
          {
            label: 'Instansi B',
            data: [78, 85, 82, 75, 88],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            pointBackgroundColor: 'rgb(16, 185, 129)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgb(16, 185, 129)'
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Analisis Komparatif Kinerja',
            font: {
              size: 16,
              weight: 'bold'
            }
          }
        },
        scales: {
          r: {
            angleLines: {
              display: true
            },
            suggestedMin: 0,
            suggestedMax: 100
          }
        }
      }
    }
  }
};

// ==========================================================================
// Template Helper Functions
// ==========================================================================

/**
 * Template rendering utilities
 */
const TemplateUtils = {
  /**
   * Render template with variables
   * @param {string} template - Template string
   * @param {Object} variables - Variables to replace
   * @returns {string} Rendered template
   */
  renderTemplate: function(template, variables) {
    let rendered = template;
    for (const [key, value] of Object.entries(variables)) {
      const regex = new RegExp(`{{${key}}}`, 'g');
      rendered = rendered.replace(regex, value || '');
    }
    return rendered;
  },

  /**
   * Get report template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Report template
   */
  getReportTemplate: function(templateId) {
    return REPORT_TEMPLATES[templateId] || null;
  },

  /**
   * Get form template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Form template
   */
  getFormTemplate: function(templateId) {
    return FORM_TEMPLATES[templateId] || null;
  },

  /**
   * Get email template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Email template
   */
  getEmailTemplate: function(templateId) {
    return EMAIL_TEMPLATES[templateId] || null;
  },

  /**
   * Get document template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Document template
   */
  getDocumentTemplate: function(templateId) {
    return DOCUMENT_TEMPLATES[templateId] || null;
  },

  /**
   * Get chart template by ID
   * @param {string} templateId - Template ID
   * @returns {Object} Chart template
   */
  getChartTemplate: function(templateId) {
    return CHART_TEMPLATES[templateId] || null;
  },

  /**
   * Get all templates by category
   * @param {string} category - Template category
   * @param {string} type - Template type (report, form, email, document, chart)
   * @returns {Array} Array of matching templates
   */
  getTemplatesByCategory: function(category, type = 'report') {
    const templates = {
      report: REPORT_TEMPLATES,
      form: FORM_TEMPLATES,
      email: EMAIL_TEMPLATES,
      document: DOCUMENT_TEMPLATES,
      chart: CHART_TEMPLATES
    };

    const templateSet = templates[type];
    if (!templateSet) return [];

    return Object.values(templateSet).filter(template => template.category === category);
  },

  /**
   * Validate template variables
   * @param {Object} template - Template object
   * @param {Object} variables - Variables to validate
   * @returns {Object} Validation result
   */
  validateTemplateVariables: function(template, variables) {
    const requiredVars = template.variables || [];
    const missing = [];
    const provided = [];

    requiredVars.forEach(varName => {
      if (variables.hasOwnProperty(varName) && variables[varName] !== null && variables[varName] !== undefined) {
        provided.push(varName);
      } else {
        missing.push(varName);
      }
    });

    return {
      valid: missing.length === 0,
      missing,
      provided,
      total: requiredVars.length
    };
  },

  /**
   * Generate report from template
   * @param {string} templateId - Template ID
   * @param {Object} data - Data for report generation
   * @returns {Object} Generated report
   */
  generateReport: function(templateId, data) {
    const template = this.getReportTemplate(templateId);
    if (!template) {
      throw new Error(`Report template '${templateId}' not found`);
    }

    const validation = this.validateTemplateVariables(template, data);
    if (!validation.valid) {
      throw new Error(`Missing required variables: ${validation.missing.join(', ')}`);
    }

    // Generate sections
    const sections = template.sections.map(section => ({
      ...section,
      content: this.renderTemplate(section.template || '', data)
    }));

    return {
      template: templateId,
      title: template.name,
      sections,
      metadata: {
        ...template.metadata,
        generated_at: new Date().toISOString(),
        data_source: 'SAKIP System'
      },
      styling: template.styling
    };
  }
};

// ==========================================================================
// Export Templates
// ==========================================================================

/**
 * Export all templates and utilities
 */
const SAKIP_TEMPLATES = {
  REPORT_TEMPLATES,
  FORM_TEMPLATES,
  EMAIL_TEMPLATES,
  DOCUMENT_TEMPLATES,
  CHART_TEMPLATES,
  TemplateUtils
};

// ==========================================================================
// Export for Use
// ==========================================================================

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
  // CommonJS
  module.exports = SAKIP_TEMPLATES;
} else if (typeof define === 'function' && define.amd) {
  // AMD
  define(function() {
    return SAKIP_TEMPLATES;
  });
} else {
  // Browser global
  window.SAKIP = window.SAKIP || {};
  window.SAKIP.TEMPLATES = SAKIP_TEMPLATES;
}