<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class SakipService
{
    /**
     * Get SAKIP configuration
     */
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return Config::get('sakip', []);
        }
        
        return Config::get('sakip.' . $key, $default);
    }

    /**
     * Render SAKIP component
     */
    public function renderComponent(string $component, array $data = [])
    {
        $viewPath = 'sakip.components.' . $component;
        
        if (!View::exists($viewPath)) {
            throw new \InvalidArgumentException("SAKIP component '{$component}' not found");
        }
        
        return View::make($viewPath, $data)->render();
    }

    /**
     * Get assessment scoring configuration
     */
    public function getAssessmentScoring(): array
    {
        return $this->getConfig('assessment.scoring', []);
    }

    /**
     * Get performance indicator categories
     */
    public function getIndicatorCategories(): array
    {
        return $this->getConfig('indicators.categories', []);
    }

    /**
     * Get report types
     */
    public function getReportTypes(): array
    {
        return $this->getConfig('reports.types', []);
    }

    /**
     * Get file upload configuration
     */
    public function getFileUploadConfig(): array
    {
        return $this->getConfig('files.upload', []);
    }

    /**
     * Get notification configuration
     */
    public function getNotificationConfig(): array
    {
        return $this->getConfig('notifications', []);
    }

    /**
     * Get audit logging configuration
     */
    public function getAuditConfig(): array
    {
        return $this->getConfig('audit', []);
    }

    /**
     * Check if feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return $this->getConfig('features.' . $feature, false);
    }

    /**
     * Get user permissions for SAKIP
     */
    public function getUserPermissions($user = null): array
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return [];
        }
        
        $permissions = [];
        
        // Check SAKIP-specific permissions
        if ($user->can('view sakip dashboard')) {
            $permissions[] = 'dashboard';
        }
        
        if ($user->can('manage sakip indicators')) {
            $permissions[] = 'indicators';
        }
        
        if ($user->can('manage sakip programs')) {
            $permissions[] = 'programs';
        }
        
        if ($user->can('manage sakip activities')) {
            $permissions[] = 'activities';
        }
        
        if ($user->can('manage sakip reports')) {
            $permissions[] = 'reports';
        }
        
        if ($user->can('manage sakip assessments')) {
            $permissions[] = 'assessments';
        }
        
        if ($user->can('manage sakip audit')) {
            $permissions[] = 'audit';
        }
        
        return $permissions;
    }

    /**
     * Get user roles for SAKIP
     */
    public function getUserRoles($user = null): array
    {
        $user = $user ?: auth()->user();
        
        if (!$user) {
            return [];
        }
        
        $roles = [];
        
        // Check SAKIP-specific roles
        if ($user->hasRole('sakip-admin')) {
            $roles[] = 'admin';
        }
        
        if ($user->hasRole('sakip-manager')) {
            $roles[] = 'manager';
        }
        
        if ($user->hasRole('sakip-analyst')) {
            $roles[] = 'analyst';
        }
        
        if ($user->hasRole('sakip-reviewer')) {
            $roles[] = 'reviewer';
        }
        
        if ($user->hasRole('sakip-user')) {
            $roles[] = 'user';
        }
        
        return $roles;
    }

    /**
     * Generate unique SAKIP ID
     */
    public function generateSakipId(string $prefix = 'SAKIP'): string
    {
        return $prefix . '_' . date('YmdHis') . '_' . uniqid();
    }

    /**
     * Format currency for SAKIP
     */
    public function formatCurrency(float $amount, string $currency = 'IDR'): string
    {
        $formatter = new \NumberFormatter('id_ID', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Format percentage for SAKIP
     */
    public function formatPercentage(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Validate SAKIP configuration
     */
    public function validateConfiguration(): array
    {
        $config = $this->getConfig();
        $errors = [];
        $warnings = [];
        
        // Check required sections
        $requiredSections = ['api', 'dashboard', 'assessment', 'indicators', 'reports'];
        foreach ($requiredSections as $section) {
            if (!isset($config[$section])) {
                $errors[] = "Missing required configuration section: {$section}";
            }
        }
        
        // Validate API configuration
        if (isset($config['api'])) {
            if (!isset($config['api']['base_url']) || empty($config['api']['base_url'])) {
                $warnings[] = "API base URL not configured";
            }
            if (!isset($config['api']['timeout']) || $config['api']['timeout'] < 1) {
                $warnings[] = "API timeout should be at least 1 second";
            }
        }
        
        // Validate dashboard configuration
        if (isset($config['dashboard'])) {
            if (!isset($config['dashboard']['default_view']) || empty($config['dashboard']['default_view'])) {
                $warnings[] = "Dashboard default view not configured";
            }
            if (!isset($config['dashboard']['refresh_interval']) || $config['dashboard']['refresh_interval'] < 5) {
                $warnings[] = "Dashboard refresh interval should be at least 5 seconds";
            }
        }
        
        // Validate assessment configuration
        if (isset($config['assessment'])) {
            if (!isset($config['assessment']['scoring']) || empty($config['assessment']['scoring'])) {
                $errors[] = "Assessment scoring configuration missing";
            }
        }
        
        // Validate indicators configuration
        if (isset($config['indicators'])) {
            if (!isset($config['indicators']['categories']) || empty($config['indicators']['categories'])) {
                $warnings[] = "No indicator categories configured";
            }
        }
        
        // Validate reports configuration
        if (isset($config['reports'])) {
            if (!isset($config['reports']['types']) || empty($config['reports']['types'])) {
                $warnings[] = "No report types configured";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'config_summary' => [
                'sections' => array_keys($config),
                'features_enabled' => $this->getEnabledFeatures(),
            ]
        ];
    }

    /**
     * Get enabled features
     */
    public function getEnabledFeatures(): array
    {
        $features = [];
        $featureKeys = ['dashboard', 'indicators', 'reports', 'assessments', 'audit', 'notifications'];
        
        foreach ($featureKeys as $feature) {
            if ($this->isFeatureEnabled($feature)) {
                $features[] = $feature;
            }
        }
        
        return $features;
    }

    /**
     * Calculate achievement percentage
     */
    public function calculateAchievement(float $target, float $realization): float
    {
        if ($target == 0) {
            return 0;
        }
        
        return min(($realization / $target) * 100, 100);
    }

    /**
     * Get achievement status
     */
    public function getAchievementStatus(float $achievement): string
    {
        if ($achievement >= 100) {
            return 'excellent';
        } elseif ($achievement >= 80) {
            return 'good';
        } elseif ($achievement >= 60) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get achievement status badge class
     */
    public function getAchievementBadgeClass(string $status): string
    {
        $classes = [
            'excellent' => 'sakip-status-success',
            'good' => 'sakip-status-info',
            'fair' => 'sakip-status-warning',
            'poor' => 'sakip-status-error'
        ];
        
        return $classes[$status] ?? 'sakip-status-neutral';
    }

    /**
     * Get assessment grade
     */
    public function getAssessmentGrade(float $score): string
    {
        $scoring = $this->getAssessmentScoring();
        
        foreach ($scoring as $grade => $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return $grade;
            }
        }
        
        return 'E';
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, string $type = 'document'): array
    {
        $config = $this->getFileUploadConfig();
        $typeConfig = $config[$type] ?? $config['default'];
        
        $errors = [];
        
        // Check file size
        if ($file->getSize() > $typeConfig['max_size']) {
            $errors[] = 'File size exceeds maximum allowed size of ' . 
                       $this->formatFileSize($typeConfig['max_size']);
        }
        
        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $typeConfig['allowed_types'])) {
            $errors[] = 'File type not allowed. Allowed types: ' . 
                       implode(', ', $typeConfig['allowed_types']);
        }
        
        return $errors;
    }

    /**
     * Format file size
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get audit log types
     */
    public function getAuditLogTypes(): array
    {
        return $this->getConfig('audit.log_types', []);
    }

    /**
     * Check if audit logging is enabled
     */
    public function isAuditLoggingEnabled(): bool
    {
        return $this->getConfig('audit.enabled', false);
    }

    /**
     * Get notification channels
     */
    public function getNotificationChannels(): array
    {
        return $this->getConfig('notifications.channels', []);
    }

    /**
     * Check if notification channel is enabled
     */
    public function isNotificationChannelEnabled(string $channel): bool
    {
        $channels = $this->getNotificationChannels();
        return in_array($channel, $channels);
    }

    /**
     * Format date
     */
    public function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$date) {
            return '';
        }
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }

    /**
     * Format indicator value
     */
    public function formatIndicatorValue($value, string $unit = 'percentage'): string
    {
        switch ($unit) {
            case 'percentage':
                return $this->formatPercentage($value);
            case 'currency':
                return $this->formatCurrency($value);
            case 'number':
                return number_format($value, 2);
            case 'rupiah':
                return 'Rp ' . number_format($value, 0, ',', '.');
            default:
                return (string) $value;
        }
    }

    /**
     * Get status badge
     */
    public function getStatusBadge(string $status): array
    {
        $badges = [
            'active' => ['text' => 'Aktif', 'class' => 'sakip-status-success'],
            'inactive' => ['text' => 'Tidak Aktif', 'class' => 'sakip-status-error'],
            'pending' => ['text' => 'Menunggu', 'class' => 'sakip-status-warning'],
            'approved' => ['text' => 'Disetujui', 'class' => 'sakip-status-success'],
            'rejected' => ['text' => 'Ditolak', 'class' => 'sakip-status-error'],
            'draft' => ['text' => 'Draf', 'class' => 'sakip-status-info'],
        ];
        
        return $badges[$status] ?? ['text' => $status, 'class' => 'sakip-status-neutral'];
    }

    /**
     * Get assessment color
     */
    public function getAssessmentColor(float $score): string
    {
        if ($score >= 90) {
            return '#10B981'; // green
        } elseif ($score >= 80) {
            return '#3B82F6'; // blue
        } elseif ($score >= 70) {
            return '#F59E0B'; // yellow
        } elseif ($score >= 60) {
            return '#F97316'; // orange
        } else {
            return '#EF4444'; // red
        }
    }

    /**
     * Get indicator icon
     */
    public function getIndicatorIcon(string $type): string
    {
        $icons = [
            'quantitative' => 'chart-bar',
            'qualitative' => 'document-text',
            'input' => 'arrow-down',
            'output' => 'arrow-up',
            'outcome' => 'target',
            'process' => 'cog',
        ];
        
        return $icons[$type] ?? 'question-mark-circle';
    }

    /**
     * Get notification types
     */
    public function getNotificationTypes(): array
    {
        return [
            'achievement_target' => 'Pencapaian Target',
            'deadline_reminder' => 'Pengingat Deadline',
            'report_submission' => 'Pengumpulan Laporan',
            'assessment_completion' => 'Penyelesaian Assessment',
            'compliance_warning' => 'Peringatan Kepatuhan',
        ];
    }

    /**
     * Get data table configuration
     */
    public function getDataTableConfig(string $type): array
    {
        $configs = [
            'indicator' => [
                'columns' => [
                    ['data' => 'kode', 'title' => 'Kode'],
                    ['data' => 'nama', 'title' => 'Nama Indikator'],
                    ['data' => 'kategori', 'title' => 'Kategori'],
                    ['data' => 'satuan', 'title' => 'Satuan'],
                    ['data' => 'target', 'title' => 'Target'],
                    ['data' => 'realisasi', 'title' => 'Realisasi'],
                    ['data' => 'capaian', 'title' => 'Capaian'],
                    ['data' => 'status', 'title' => 'Status'],
                    ['data' => 'instansi', 'title' => 'Instansi'],
                    ['data' => 'created_at', 'title' => 'Tanggal Buat'],
                ],
                'ajax_url' => route('sakip.api.indicators'),
                'page_length' => 10,
                'order' => [[0, 'asc']],
            ],
            'program' => [
                'columns' => [
                    ['data' => 'kode', 'title' => 'Kode'],
                    ['data' => 'nama', 'title' => 'Nama Program'],
                    ['data' => 'instansi', 'title' => 'Instansi'],
                    ['data' => 'anggaran', 'title' => 'Anggaran'],
                    ['data' => 'realisasi_anggaran', 'title' => 'Realisasi'],
                    ['data' => 'capaian_anggaran', 'title' => 'Capaian'],
                    ['data' => 'status', 'title' => 'Status'],
                    ['data' => 'created_at', 'title' => 'Tanggal Buat'],
                ],
                'ajax_url' => route('sakip.api.programs'),
                'page_length' => 10,
                'order' => [[0, 'asc']],
            ],
        ];
        
        return $configs[$type] ?? [];
    }

    /**
     * Get data table data
     */
    public function getDataTableData(string $type, array $params = []): array
    {
        // This is a simplified version - in a real implementation,
        // you would query the database based on the type and parameters
        $data = [];
        
        switch ($type) {
            case 'indicator':
                $data = $this->getSampleIndicatorData();
                break;
            case 'program':
                $data = $this->getSampleProgramData();
                break;
        }
        
        return [
            'data' => $data,
            'total' => count($data),
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 10,
            'total_pages' => 1,
        ];
    }

    /**
     * Get sample indicator data
     */
    protected function getSampleIndicatorData(): array
    {
        return [
            [
                'id' => 1,
                'kode' => 'IK-001',
                'nama' => 'Persentase capaian target kinerja',
                'kategori' => 'outcome',
                'satuan' => 'persentase',
                'target' => '100',
                'realisasi' => '85',
                'capaian' => '85%',
                'status' => ['text' => 'Aktif', 'class' => 'sakip-status-success'],
                'instansi' => 'Dinas Pendidikan',
                'created_at' => '2025-01-15 10:00:00',
                'updated_at' => '2025-01-15 10:00:00',
            ],
            [
                'id' => 2,
                'kode' => 'IK-002',
                'nama' => 'Jumlah program yang terselenggara',
                'kategori' => 'output',
                'satuan' => 'angka',
                'target' => '50',
                'realisasi' => '45',
                'capaian' => '90%',
                'status' => ['text' => 'Aktif', 'class' => 'sakip-status-success'],
                'instansi' => 'Dinas Kesehatan',
                'created_at' => '2025-01-16 10:00:00',
                'updated_at' => '2025-01-16 10:00:00',
            ],
        ];
    }

    /**
     * Get sample program data
     */
    protected function getSampleProgramData(): array
    {
        return [
            [
                'id' => 1,
                'kode' => 'PRG-001',
                'nama' => 'Program Peningkatan Kualitas Pendidikan',
                'instansi' => 'Dinas Pendidikan',
                'anggaran' => 'Rp 1.000.000.000',
                'realisasi_anggaran' => 'Rp 850.000.000',
                'capaian_anggaran' => '85%',
                'status' => ['text' => 'Aktif', 'class' => 'sakip-status-success'],
                'created_at' => '2025-01-01 10:00:00',
                'updated_at' => '2025-01-15 10:00:00',
            ],
        ];
    }

    /**
     * Format number
     */
    public function formatNumber($number, int $decimals = 2): string
    {
        return number_format($number, $decimals, ',', '.');
    }
}