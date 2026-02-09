<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit Log Model
 *
 * SECURITY: Implements data masking to prevent sensitive data exposure in audit trails.
 * Sensitive fields are automatically masked before storage while maintaining audit integrity.
 */
class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        "user_id",
        "action",
        "details",
        "ip_address",
        "user_agent",
    ];

    protected $casts = [
        "details" => "array",
    ];

    protected $keyType = "string";
    public $incrementing = false;

    /**
     * Fields that should be masked in audit logs
     * These contain sensitive information that shouldn't be stored in plain text
     */
    protected static $sensitiveFields = [
        "password",
        "password_confirmation",
        "api_token",
        "remember_token",
        "secret",
        "secret_key",
        "access_token",
        "refresh_token",
        "private_key",
        "credit_card",
        "ssn",
        "social_security",
        "bank_account",
        "personal_identification",
    ];

    /**
     * Fields that should be completely excluded from audit logs
     */
    protected static $excludedFields = [
        "notes",
        "description",
        "metadata",
        "attachments",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-mask sensitive data before creating audit log
        static::creating(function ($auditLog) {
            if (isset($auditLog->details) && is_array($auditLog->details)) {
                $auditLog->details = self::maskSensitiveData(
                    $auditLog->details,
                );
            }
        });

        // Auto-mask sensitive data before updating audit log
        static::updating(function ($auditLog) {
            if (isset($auditLog->details) && is_array($auditLog->details)) {
                $auditLog->details = self::maskSensitiveData(
                    $auditLog->details,
                );
            }
        });
    }

    /**
     * Mask sensitive data in audit log details
     * SECURITY: Prevents sensitive information exposure in audit trails
     *
     * @param array $data The data to mask
     * @return array The masked data
     */
    public static function maskSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            // Skip excluded fields entirely
            if (in_array($key, self::$excludedFields)) {
                unset($data[$key]);
                continue;
            }

            // Mask sensitive fields
            if (in_array($key, self::$sensitiveFields)) {
                $data[$key] = self::maskValue($value);
                continue;
            }

            // Recursively mask nested arrays
            if (is_array($value)) {
                $data[$key] = self::maskSensitiveData($value);
            }
        }

        return $data;
    }

    /**
     * Mask a sensitive value
     * Shows first 4 and last 4 characters with asterisks in between
     *
     * @param mixed $value The value to mask
     * @return string The masked value
     */
    protected static function maskValue($value): string
    {
        if (empty($value)) {
            return "[MASKED]";
        }

        $stringValue = (string) $value;
        $length = strlen($stringValue);

        // For short values, mask completely
        if ($length <= 8) {
            return str_repeat("*", $length);
        }

        // For longer values, show first 4 and last 4 characters
        $start = substr($stringValue, 0, 4);
        $end = substr($stringValue, -4);
        $middle = str_repeat("*", $length - 8);

        return $start . $middle . $end;
    }

    /**
     * Create audit log with automatic data masking
     *
     * @param array $data
     * @return AuditLog
     */
    public static function createWithMasking(array $data): AuditLog
    {
        // Ensure sensitive data is masked
        if (isset($data["details"])) {
            $data["details"] = self::maskSensitiveData($data["details"]);
        }

        return static::create($data);
    }
}
