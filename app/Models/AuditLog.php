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
        "instansi_id",
        "action",
        "module",
        "activity",
        "description",
        "details",
        "old_values",
        "new_values",
        "model_type",
        "ip_address",
        "user_agent",
        "compliance_status",
        "compliance_notes",
        "impact_level",
    ];

    protected $casts = [
        "details" => "array",
        "old_values" => "array",
        "new_values" => "array",
    ];

    protected $keyType = "string";
    public $incrementing = false;

    /**
     * Fields that should be masked in audit logs
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, "instansi_id");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($auditLog) {
            foreach (["details", "old_values", "new_values"] as $field) {
                if (isset($auditLog->{$field}) && is_array($auditLog->{$field})) {
                    $auditLog->{$field} = self::maskSensitiveData(
                        $auditLog->{$field},
                    );
                }
            }
        });

        // Append-only: refuse updates from application code
        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }

    public static function maskSensitiveData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::$sensitiveFields, true)) {
                $data[$key] = self::maskValue($value);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = self::maskSensitiveData($value);
            }
        }

        return $data;
    }

    protected static function maskValue($value): string
    {
        if (empty($value)) {
            return "[MASKED]";
        }

        $stringValue = (string) $value;
        $length = strlen($stringValue);

        if ($length <= 8) {
            return str_repeat("*", $length);
        }

        $start = substr($stringValue, 0, 4);
        $end = substr($stringValue, -4);
        $middle = str_repeat("*", $length - 8);

        return $start . $middle . $end;
    }

    public static function createWithMasking(array $data): AuditLog
    {
        foreach (["details", "old_values", "new_values"] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = self::maskSensitiveData($data[$field]);
            }
        }

        return static::create($data);
    }
}
