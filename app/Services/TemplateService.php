<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\Institution;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Template Service
 * 
 * Handles report template management for SAKIP module including template creation,
 * modification, and rendering for different report types.
 */
class TemplateService
{
    /**
     * Get available templates for a specific module
     */
    public function getAvailableTemplates($module = 'sakip', $instansiId = null)
    {
        try {
            $query = ReportTemplate::where('module', $module)
                ->where('is_active', true);

            if ($instansiId) {
                $query->where(function($q) use ($instansiId) {
                    $q->where('instansi_id', $instansiId)
                      ->orWhereNull('instansi_id');
                });
            }

            return $query->orderBy('name')->get();

        } catch (\Exception $e) {
            Log::error('Failed to get available templates: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get template by ID
     */
    public function getTemplate($templateId)
    {
        try {
            return ReportTemplate::find($templateId);
        } catch (\Exception $e) {
            Log::error('Failed to get template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new template
     */
    public function createTemplate($data)
    {
        try {
            return ReportTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'module' => $data['module'] ?? 'sakip',
                'type' => $data['type'] ?? 'general',
                'content' => $data['content'] ?? null,
                'template_file' => $data['template_file'] ?? null,
                'instansi_id' => $data['instansi_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update template
     */
    public function updateTemplate($templateId, $data)
    {
        try {
            $template = ReportTemplate::find($templateId);
            
            if (!$template) {
                return null;
            }

            $template->update([
                'name' => $data['name'] ?? $template->name,
                'description' => $data['description'] ?? $template->description,
                'content' => $data['content'] ?? $template->content,
                'template_file' => $data['template_file'] ?? $template->template_file,
                'is_active' => $data['is_active'] ?? $template->is_active,
                'updated_by' => auth()->id(),
            ]);

            return $template;
        } catch (\Exception $e) {
            Log::error('Failed to update template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate($templateId)
    {
        try {
            $template = ReportTemplate::find($templateId);
            
            if (!$template) {
                return false;
            }

            // Delete associated template file if exists
            if ($template->template_file) {
                $this->deleteTemplateFile($template->template_file);
            }

            return $template->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload template file
     */
    public function uploadTemplateFile($file, $templateName)
    {
        try {
            $filename = str_slug($templateName) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('templates', $filename, 'public');
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to upload template file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete template file
     */
    private function deleteTemplateFile($filePath)
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete template file: ' . $e->getMessage());
        }
    }

    /**
     * Render template with data
     */
    public function renderTemplate($template, $data)
    {
        try {
            $content = $template->content;

            // Replace placeholders with actual data
            $content = $this->replacePlaceholders($content, $data);

            // Apply template-specific rendering
            switch ($template->type) {
                case 'performance_report':
                    $content = $this->renderPerformanceReport($content, $data);
                    break;
                case 'compliance_report':
                    $content = $this->renderComplianceReport($content, $data);
                    break;
                case 'summary_report':
                    $content = $this->renderSummaryReport($content, $data);
                    break;
                default:
                    $content = $this->renderGeneralReport($content, $data);
            }

            return $content;
        } catch (\Exception $e) {
            Log::error('Failed to render template: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Replace placeholders in template content
     */
    private function replacePlaceholders($content, $data)
    {
        // Basic placeholder replacements
        $replacements = [
            '{{institution_name}}' => $data['institution_name'] ?? '',
            '{{report_title}}' => $data['report_title'] ?? '',
            '{{report_period}}' => $data['report_period'] ?? '',
            '{{report_date}}' => now()->format('d F Y'),
            '{{current_year}}' => now()->year,
            '{{user_name}}' => auth()->user()->name ?? '',
            '{{user_role}}' => auth()->user()->roles->first()->name ?? '',
        ];

        foreach ($replacements as $placeholder => $value) {
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * Render performance report
     */
    private function renderPerformanceReport($content, $data)
    {
        // Add performance-specific rendering
        if (isset($data['performance_metrics'])) {
            $metricsHtml = $this->generatePerformanceMetricsHtml($data['performance_metrics']);
            $content = str_replace('{{performance_metrics}}', $metricsHtml, $content);
        }

        if (isset($data['charts'])) {
            $chartsHtml = $this->generateChartsHtml($data['charts']);
            $content = str_replace('{{charts}}', $chartsHtml, $content);
        }

        return $content;
    }

    /**
     * Render compliance report
     */
    private function renderComplianceReport($content, $data)
    {
        // Add compliance-specific rendering
        if (isset($data['compliance_status'])) {
            $complianceHtml = $this->generateComplianceStatusHtml($data['compliance_status']);
            $content = str_replace('{{compliance_status}}', $complianceHtml, $content);
        }

        if (isset($data['issues'])) {
            $issuesHtml = $this->generateIssuesHtml($data['issues']);
            $content = str_replace('{{issues}}', $issuesHtml, $content);
        }

        return $content;
    }

    /**
     * Render summary report
     */
    private function renderSummaryReport($content, $data)
    {
        // Add summary-specific rendering
        if (isset($data['summary_data'])) {
            $summaryHtml = $this->generateSummaryDataHtml($data['summary_data']);
            $content = str_replace('{{summary_data}}', $summaryHtml, $content);
        }

        return $content;
    }

    /**
     * Render general report
     */
    private function renderGeneralReport($content, $data)
    {
        // Add general report rendering
        if (isset($data['report_content'])) {
            $content = str_replace('{{report_content}}', $data['report_content'], $content);
        }

        return $content;
    }

    /**
     * Generate performance metrics HTML
     */
    private function generatePerformanceMetricsHtml($metrics)
    {
        $html = '<div class="performance-metrics">';
        
        foreach ($metrics as $metric) {
            $html .= '<div class="metric-item">';
            $html .= '<h4>' . ($metric['name'] ?? '') . '</h4>';
            $html .= '<p>Target: ' . ($metric['target'] ?? 0) . '</p>';
            $html .= '<p>Realisasi: ' . ($metric['actual'] ?? 0) . '</p>';
            $html .= '<p>Capaian: ' . ($metric['achievement'] ?? 0) . '%</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Generate charts HTML
     */
    private function generateChartsHtml($charts)
    {
        $html = '<div class="charts-section">';
        
        foreach ($charts as $chart) {
            $html .= '<div class="chart-item">';
            $html .= '<h4>' . ($chart['title'] ?? '') . '</h4>';
            $html .= '<p>' . ($chart['description'] ?? '') . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Generate compliance status HTML
     */
    private function generateComplianceStatusHtml($status)
    {
        $html = '<div class="compliance-status">';
        $html .= '<p>Status Kepatuhan: ' . ($status['overall_status'] ?? 'Unknown') . '</p>';
        $html .= '<p>Persentase: ' . ($status['compliance_percentage'] ?? 0) . '%</p>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Generate issues HTML
     */
    private function generateIssuesHtml($issues)
    {
        $html = '<div class="issues-section">';
        
        foreach ($issues as $issue) {
            $html .= '<div class="issue-item">';
            $html .= '<h4>' . ($issue['title'] ?? '') . '</h4>';
            $html .= '<p>' . ($issue['description'] ?? '') . '</p>';
            $html .= '<p>Status: ' . ($issue['status'] ?? 'Open') . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Generate summary data HTML
     */
    private function generateSummaryDataHtml($summaryData)
    {
        $html = '<div class="summary-data">';
        
        foreach ($summaryData as $key => $value) {
            $html .= '<div class="summary-item">';
            $html .= '<strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ';
            $html .= is_array($value) ? json_encode($value) : $value;
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }

    /**
     * Get default templates
     */
    public function getDefaultTemplates()
    {
        return [
            [
                'name' => 'Laporan Kinerja Triwulan',
                'description' => 'Template laporan kinerja triwulan',
                'type' => 'performance_report',
                'content' => $this->getDefaultPerformanceReportTemplate(),
            ],
            [
                'name' => 'Laporan Kepatuhan',
                'description' => 'Template laporan kepatuhan',
                'type' => 'compliance_report',
                'content' => $this->getDefaultComplianceReportTemplate(),
            ],
            [
                'name' => 'Laporan Ringkasan',
                'description' => 'Template laporan ringkasan',
                'type' => 'summary_report',
                'content' => $this->getDefaultSummaryReportTemplate(),
            ],
        ];
    }

    /**
     * Get default performance report template
     */
    private function getDefaultPerformanceReportTemplate()
    {
        return '<div class="report-header">
    <h1>{{report_title}}</h1>
    <p>Institusi: {{institution_name}}</p>
    <p>Periode: {{report_period}}</p>
    <p>Tanggal Laporan: {{report_date}}</p>
</div>

<div class="report-content">
    <h2>Metrik Kinerja</h2>
    {{performance_metrics}}
    
    <h2>Grafik dan Visualisasi</h2>
    {{charts}}
    
    <div class="report-footer">
        <p>Dibuat oleh: {{user_name}} ({{user_role}})</p>
    </div>
</div>';
    }

    /**
     * Get default compliance report template
     */
    private function getDefaultComplianceReportTemplate()
    {
        return '<div class="report-header">
    <h1>{{report_title}}</h1>
    <p>Institusi: {{institution_name}}</p>
    <p>Periode: {{report_period}}</p>
    <p>Tanggal Laporan: {{report_date}}</p>
</div>

<div class="report-content">
    <h2>Status Kepatuhan</h2>
    {{compliance_status}}
    
    <h2>Masalah dan Isu</h2>
    {{issues}}
    
    <div class="report-footer">
        <p>Dibuat oleh: {{user_name}} ({{user_role}})</p>
    </div>
</div>';
    }

    /**
     * Get default summary report template
     */
    private function getDefaultSummaryReportTemplate()
    {
        return '<div class="report-header">
    <h1>{{report_title}}</h1>
    <p>Institusi: {{institution_name}}</p>
    <p>Periode: {{report_period}}</p>
    <p>Tanggal Laporan: {{report_date}}</p>
</div>

<div class="report-content">
    <h2>Ringkasan Data</h2>
    {{summary_data}}
    
    <div class="report-footer">
        <p>Dibuat oleh: {{user_name}} ({{user_role}})</p>
    </div>
</div>';
    }
}