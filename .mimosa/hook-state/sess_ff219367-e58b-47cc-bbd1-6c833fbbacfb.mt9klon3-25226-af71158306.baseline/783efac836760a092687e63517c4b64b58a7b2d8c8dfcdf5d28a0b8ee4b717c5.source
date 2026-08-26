<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * System Maintenance Controller
 *
 * Handles system maintenance operations including cache management,
 * application optimization, database backups, and health monitoring.
 */
class MaintenanceController extends Controller
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
        $this->middleware('can:manage-system-maintenance');
    }

    /**
     * Display the maintenance dashboard with backup list.
     */
    public function index()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $filePath = $backupPath . '/' . $file;
                if (is_file($filePath)) {
                    $backups[] = [
                        'filename' => $file,
                        'size' => $this->formatBytes(filesize($filePath)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                    ];
                }
            }
            // Sort by creation date (newest first)
            usort($backups, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        }

        return view('admin.maintenance.index', compact('backups'));
    }

    /**
     * Clear application caches.
     *
     * Clears all Laravel caches: application, config, routes, and views.
     */
    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            Log::info('Application cache cleared', [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->handleError($e, redirectBack: false);
        }
    }

    /**
     * Optimize application for production.
     *
     * Caches all Laravel components for optimal performance.
     */
    public function optimizeApp()
    {
        try {
            \Artisan::call('optimize');
            \Artisan::call('config:cache');
            \Artisan::call('route:cache');
            \Artisan::call('view:cache');

            Log::info('Application optimized', [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Application optimization failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->handleError($e, redirectBack: false);
        }
    }

    /**
     * Create database backup.
     */
    public function backupDatabase()
    {
        try {
            $result = $this->backupService->createBackup();

            // Log the backup action for audit trail
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'database.backup.created',
                'model_type' => 'Database',
                'description' => "Database backup created: {$result['filename']}",
            ]);

            Log::info('Database backup created', [
                'filename' => $result['filename'],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Database backup created! File: ' . $result['filename'],
                'file_path' => $result['path'],
                'file_size' => $result['size'],
            ]);
        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return $this->handleError($e, redirectBack: false);
        }
    }

    /**
     * Download a specific backup file.
     */
    public function downloadBackup($filename)
    {
        $filename = $this->sanitizeFilename($filename);
        $backupPath = storage_path('app/backups');
        
        // SECURITY: Whitelist allowed backup files to prevent path traversal
        $allowedFiles = array_map('basename', glob($backupPath . '/*.sql'));
        
        if (!in_array($filename, $allowedFiles)) {
            Log::warning("Unauthorized backup file access attempted: {$filename}", [
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'File not allowed.'], 403);
        }
        
        $filePath = $backupPath . '/' . $filename;
        
        // SECURITY: Additional realpath check to prevent directory traversal
        $realPath = realpath($filePath);
        $realBackupPath = realpath($backupPath);
        
        if ($realPath === false || strpos($realPath, $realBackupPath) !== 0) {
            Log::warning("Path traversal attempt blocked: {$filename}", [
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid file path.'], 403);
        }

        if (!file_exists($realPath)) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        return response()->download($realPath);
    }

    /**
     * Delete a specific backup file.
     */
    public function deleteBackup($filename)
    {
        $filename = $this->sanitizeFilename($filename);
        $backupPath = storage_path('app/backups');
        $filePath = $backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        if (unlink($filePath)) {
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'database.backup.deleted',
                'model_type' => 'Database',
                'description' => "Database backup deleted: {$filename}",
            ]);

            return response()->json(['success' => true, 'message' => 'Backup deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete backup.'], 500);
    }

    /**
     * Get system health information.
     */
    public function healthCheck()
    {
        $health = [
            'disk' => $this->getDiskUsage(),
            'database' => $this->getDatabaseStatus(),
            'cache' => $this->getCacheStatus(),
            'queue' => $this->getQueueStatus(),
        ];

        return response()->json(['success' => true, 'health' => $health]);
    }

    // =====================================================
    // PRIVATE HELPER METHODS
    // =====================================================

    /**
     * Sanitize filename to prevent directory traversal.
     */
    private function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');
        if (empty($filename)) {
            $filename = 'backup_' . date('Y-m-d_His');
        }
        return substr($filename, 0, 200);
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Get disk usage information.
     */
    private function getDiskUsage(): array
    {
        $total = disk_total_space(base_path());
        $free = disk_free_space(base_path());
        $used = $total - $free;
        $percentage = $total > 0 ? ($used / $total) * 100 : 0;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round($percentage, 2),
        ];
    }

    /**
     * Get database connection status.
     */
    private function getDatabaseStatus(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'message' => 'OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection failed'];
        }
    }

    /**
     * Get cache driver status.
     */
    private function getCacheStatus(): array
    {
        try {
            $driver = config('cache.default');
            return ['status' => 'enabled', 'driver' => $driver];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache unavailable'];
        }
    }

    /**
     * Get queue worker status.
     */
    private function getQueueStatus(): array
    {
        $driver = config('queue.default');
        return ['status' => 'configured', 'driver' => $driver];
    }
}
