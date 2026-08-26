<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

/**
 * SystemSetting Model
 *
 * Represents a key-value system configuration entry.
 * Uses UUID primary keys and stores values as strings; casting is handled by the service layer.
 */
class SystemSetting extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'system_settings';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Attribute casting rules.
     * Note: 'value' remains a raw string; casting is handled in SystemSettingsService.
     */
    protected $casts = [
        'key' => 'string',
        'value' => 'string',
        'type' => 'string',
        'description' => 'string',
    ];

    /**
     * Scope: filter by module prefix (e.g., module.setting -> module).
     *
     * @param Builder $query
     * @param string $module
     * @return Builder
     */
    public function scopeModule(Builder $query, string $module): Builder
    {
        return $query->where('key', 'LIKE', $module . '.%');
    }
}