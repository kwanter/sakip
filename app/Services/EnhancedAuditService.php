<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class EnhancedAuditService
{
    /**
     * Log levels for audit events.
     */
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_SECURITY = 'security';

    /**
     * Event categories for audit logging.
     */
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_AUTHORIZATION = 'authorization';
    const CATEGORY_DATA_ACCESS = 'data_access';
    const CATEGORY_DATA_MODIFICATION = 'data_modification';
    const CATEGORY_FILE_OPERATION = 'file_operation';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_API = 'api';
    const CATEGORY_ADMIN = 'admin';

    /**
     * Log an audit event.
     *
     * @param string $action
     * @param string $category
     * @param string $level
     * @param array $data
     * @param string|null $description
     * @return AuditLog
     */
    public function log(
        string $action,
        string $category,
        string $level = self::LEVEL_INFO,
        array $data = [],
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'category' => $category,
            'level' => $level,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'data' => $this->sanitizeData($data),
            'context' => $this->gatherContext(),
        ]);
    }

    /**
     * Log successful authentication.
     *
     * @param int|string $userId
     * @param array $data
     * @return AuditLog
     */
    public function logSuccessfulLogin($userId, array $data = []): AuditLog
    {
        return $this->log(
            'login_success',
            self::CATEGORY_AUTH,
            self::LEVEL_INFO,
            array_merge($data, [
                'user_id' => $userId,
                'remember_me' => request()->boolean('remember'),
            ]),
            'User successfully logged in'
        );
    }

    /**
     * Log failed authentication attempt.
     *
     * @param string $email
     * @param string $reason
     * @return AuditLog
     */
    public function logFailedLogin(string $email, string $reason = 'invalid_credentials'): AuditLog
    {
        return $this->log(
            'login_failed',
            self::CATEGORY_SECURITY,
            self::LEVEL_WARNING,
            [
                'email' => $email,
                'reason' => $reason,
                'attempt_number' => $this->getRecentLoginAttempts($email),
            ],
            "Failed login attempt for {$email}: {$reason}"
        );
    }

    /**
     * Log logout event.
     *
     * @return AuditLog
     */
    public function logLogout(): AuditLog
    {
        return $this->log(
            'logout',
            self::CATEGORY_AUTH,
            self::LEVEL_INFO,
            ['user_id' => Auth::id()],
            'User logged out'
        );
    }

    /**
     * Log unauthorized access attempt.
     *
     * @param string $resource
     * @param string $action
     * @param array $data
     * @return AuditLog
     */
    public function logUnauthorizedAccess(string $resource, string $action, array $data = []): AuditLog
    {
        return $this->log(
            'unauthorized_access',
            self::CATEGORY_SECURITY,
            self::LEVEL_WARNING,
            array_merge($data, [
                'resource' => $resource,
                'attempted_action' => $action,
            ]),
            "Unauthorized access attempt to {$resource} ({$action})"
        );
    }

    /**
     * Log data access event.
     *
     * @param string $model
     * @param int|string $modelId
     * @param string $action
     * @return AuditLog
     */
    public function logDataAccess(string $model, $modelId, string $action = 'view'): AuditLog
    {
        return $this->log(
            "data_access_{$action}",
            self::CATEGORY_DATA_ACCESS,
            self::LEVEL_INFO,
            [
                'model' => $model,
                'model_id' => $modelId,
                'action' => $action,
            ],
            "Accessed {$model} #{$modelId}"
        );
    }

    /**
     * Log data creation event.
     *
     * @param string $model
     * @param int|string $modelId
     * @param array $attributes
     * @return AuditLog
     */
    public function logDataCreated(string $model, $modelId, array $attributes = []): AuditLog
    {
        return $this->log(
            'data_created',
            self::CATEGORY_DATA_MODIFICATION,
            self::LEVEL_INFO,
            [
                'model' => $model,
                'model_id' => $modelId,
                'attributes' => $this->sanitizeData($attributes),
            ],
            "Created new {$model} #{$modelId}"
        );
    }

    /**
     * Log data update event.
     *
     * @param string $model
     * @param int|string $modelId
     * @param array $changes
     * @return AuditLog
     */
    public function logDataUpdated(string $model, $modelId, array $changes = []): AuditLog
    {
        return $this->log(
            'data_updated',
            self::CATEGORY_DATA_MODIFICATION,
            self::LEVEL_INFO,
            [
                'model' => $model,
                'model_id' => $modelId,
                'changes' => $this->sanitizeData($changes),
            ],
            "Updated {$model} #{$modelId}"
        );
    }

    /**
     * Log data deletion event.
     *
     * @param string $model
     * @param int|string $modelId
     * @param bool $softDelete
     * @return AuditLog
     */
    public function logDataDeleted(string $model, $modelId, bool $softDelete = false): AuditLog
    {
        $action = $softDelete ? 'soft_deleted' : 'permanently_deleted';

        return $this->log(
            "data_{$action}",
            self::CATEGORY_DATA_MODIFICATION,
            self::LEVEL_WARNING,
            [
                'model' => $model,
                'model_id' => $modelId,
                'soft_delete' => $softDelete,
            ],
            ($softDelete ? 'Soft deleted' : 'Permanently deleted') . " {$model} #{$modelId}"
        );
    }

    /**
     * Log file upload event.
     *
     * @param string $filename
     * @param int $size
     * @param string $mimeType
     * @param array $data
     * @return AuditLog
     */
    public function logFileUpload(string $filename, int $size, string $mimeType, array $data = []): AuditLog
    {
        return $this->log(
            'file_upload',
            self::CATEGORY_FILE_OPERATION,
            self::LEVEL_INFO,
            array_merge($data, [
                'filename' => $filename,
                'size' => $size,
                'mime_type' => $mimeType,
                'size_formatted' => $this->formatBytes($size),
            ]),
            "Uploaded file: {$filename} ({$this->formatBytes($size)})"
        );
    }

    /**
     * Log file deletion event.
     *
     * @param string $filename
     * @param string $path
     * @return AuditLog
     */
    public function logFileDeleted(string $filename, string $path): AuditLog
    {
        return $this->log(
            'file_deleted',
            self::CATEGORY_FILE_OPERATION,
            self::LEVEL_WARNING,
            [
                'filename' => $filename,
                'path' => $path,
            ],
            "Deleted file: {$filename}"
        );
    }

    /**
     * Log security event.
     *
     * @param string $event
     * @param string $description
     * @param array $data
     * @return AuditLog
     */
    public function logSecurityEvent(string $event, string $description, array $data = []): AuditLog
    {
        return $this->log(
            $event,
            self::CATEGORY_SECURITY,
            self::LEVEL_SECURITY,
            $data,
            $description
        );
    }

    /**
     * Log rate limit exceeded.
     *
     * @param string $limiter
     * @param int $maxAttempts
     * @return AuditLog
     */
    public function logRateLimitExceeded(string $limiter, int $maxAttempts): AuditLog
    {
        return $this->log(
            'rate_limit_exceeded',
            self::CATEGORY_SECURITY,
            self::LEVEL_WARNING,
            [
                'limiter' => $limiter,
                'max_attempts' => $maxAttempts,
            ],
            "Rate limit exceeded for {$limiter}"
        );
    }

    /**
     * Log API request.
     *
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param float $responseTime
     * @return AuditLog
     */
    public function logApiRequest(string $endpoint, string $method, int $statusCode, float $responseTime): AuditLog
    {
        return $this->log(
            'api_request',
            self::CATEGORY_API,
            $statusCode >= 400 ? self::LEVEL_WARNING : self::LEVEL_INFO,
            [
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $statusCode,
                'response_time' => round($responseTime, 3),
            ],
            "{$method} {$endpoint} - {$statusCode} ({$responseTime}ms)"
        );
    }

    /**
     * Log permission change.
     *
     * @param int|string $userId
     * @param string $action
     * @param array $permissions
     * @return AuditLog
     */
    public function logPermissionChange($userId, string $action, array $permissions): AuditLog
    {
        return $this->log(
            "permission_{$action}",
            self::CATEGORY_ADMIN,
            self::LEVEL_WARNING,
            [
                'target_user_id' => $userId,
                'action' => $action,
                'permissions' => $permissions,
            ],
            "Permission {$action} for user #{$userId}"
        );
    }

    /**
     * Log role change.
     *
     * @param int|string $userId
     * @param string $action
     * @param array $roles
     * @return AuditLog
     */
    public function logRoleChange($userId, string $action, array $roles): AuditLog
    {
        return $this->log(
            "role_{$action}",
            self::CATEGORY_ADMIN,
            self::LEVEL_WARNING,
            [
                'target_user_id' => $userId,
                'action' => $action,
                'roles' => $roles,
            ],
            "Role {$action} for user #{$userId}"
        );
    }

    /**
     * Log system configuration change.
     *
     * @param string $key
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return AuditLog
     */
    public function logConfigChange(string $key, $oldValue, $newValue): AuditLog
    {
        return $this->log(
            'config_change',
            self::CATEGORY_SYSTEM,
            self::LEVEL_WARNING,
            [
                'config_key' => $key,
                'old_value' => $this->sanitizeData($oldValue),
                'new_value' => $this->sanitizeData($newValue),
            ],
            "Configuration changed: {$key}"
        );
    }

    /**
     * Log bulk operation.
     *
     * @param string $operation
     * @param string $model
     * @param int $count
     * @param array $data
     * @return AuditLog
     */
    public function logBulkOperation(string $operation, string $model, int $count, array $data = []): AuditLog
    {
        return $this->log(
            "bulk_{$operation}",
            self::CATEGORY_DATA_MODIFICATION,
            self::LEVEL_WARNING,
            array_merge($data, [
                'operation' => $operation,
                'model' => $model,
                'count' => $count,
            ]),
            "Bulk {$operation} on {$count} {$model} records"
        );
    }

    /**
     * Log export operation.
     *
     * @param string $type
     * @param string $format
     * @param int $recordCount
     * @return AuditLog
     */
    public function logExport(string $type, string $format, int $recordCount): AuditLog
    {
        return $this->log(
            'data_export',
            self::CATEGORY_DATA_ACCESS,
            self::LEVEL_INFO,
            [
                'export_type' => $type,
                'format' => $format,
                'record_count' => $recordCount,
            ],
            "Exported {$recordCount} {$type} records as {$format}"
        );
    }

    /**
     * Log import operation.
     *
     * @param string $type
     * @param int $successCount
     * @param int $failureCount
     * @return AuditLog
     */
    public function logImport(string $type, int $successCount, int $failureCount): AuditLog
    {
        return $this->log(
            'data_import',
            self::CATEGORY_DATA_MODIFICATION,
            $failureCount > 0 ? self::LEVEL_WARNING : self::LEVEL_INFO,
            [
                'import_type' => $type,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'total' => $successCount + $failureCount,
            ],
            "Imported {$type}: {$successCount} successful, {$failureCount} failed"
        );
    }

    /**
     * Log critical error.
     *
     * @param string $error
     * @param \Throwable|null $exception
     * @return AuditLog
     */
    public function logCriticalError(string $error, ?\Throwable $exception = null): AuditLog
    {
        $data = ['error' => $error];

        if ($exception) {
            $data['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(5)->toArray(),
            ];
        }

        return $this->log(
            'critical_error',
            self::CATEGORY_SYSTEM,
            self::LEVEL_CRITICAL,
            $data,
            $error
        );
    }

    /**
     * Sanitize data to remove sensitive information.
     *
     * @param mixed $data
     * @return mixed
     */
    protected function sanitizeData($data)
    {
        if (is_array($data)) {
            $sensitiveKeys = [
                'password',
                'password_confirmation',
                'current_password',
                'new_password',
                'api_key',
                'api_secret',
                'secret',
                'token',
                'access_token',
                'refresh_token',
                'private_key',
                'credit_card',
                'cvv',
                'ssn',
            ];

            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveKeys)) {
                    $data[$key] = '[REDACTED]';
                } elseif (is_array($value)) {
                    $data[$key] = $this->sanitizeData($value);
                }
            }
        }

        return $data;
    }

    /**
     * Gather contextual information for the audit log.
     *
     * @return array
     */
    protected function gatherContext(): array
    {
        return [
            'session_id' => session()->getId(),
            'referer' => Request::header('referer'),
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'app_version' => config('app.version', '1.0.0'),
        ];
    }

    /**
     * Get recent login attempts for an email.
     *
     * @param string $email
     * @return int
     */
    protected function getRecentLoginAttempts(string $email): int
    {
        return AuditLog::where('action', 'login_failed')
            ->where('ip_address', Request::ip())
            ->where('data->email', $email)
            ->where('created_at', '>', now()->subHour())
            ->count();
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get audit logs for a specific user.
     *
     * @param int|string $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAuditLogs($userId, int $limit = 50)
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent security events.
     *
     * @param int $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentSecurityEvents(int $hours = 24)
    {
        return AuditLog::where('category', self::CATEGORY_SECURITY)
            ->where('created_at', '>', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get failed login attempts.
     *
     * @param int $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFailedLoginAttempts(int $hours = 24)
    {
        return AuditLog::where('action', 'login_failed')
            ->where('created_at', '>', now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit statistics.
     *
     * @param int $days
     * @return array
     */
    public function getStatistics(int $days = 30)
    {
        $from = now()->subDays($days);

        return [
            'total_events' => AuditLog::where('created_at', '>', $from)->count(),
            'by_category' => AuditLog::where('created_at', '>', $from)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_level' => AuditLog::where('created_at', '>', $from)
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
            'security_events' => AuditLog::where('category', self::CATEGORY_SECURITY)
                ->where('created_at', '>', $from)
                ->count(),
            'failed_logins' => AuditLog::where('action', 'login_failed')
                ->where('created_at', '>', $from)
                ->count(),
            'unique_users' => AuditLog::where('created_at', '>', $from)
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }
}
