<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ReportTemplate Model
 * 
 * Represents report templates for SAKIP module.
 * Stores template configurations, content, and metadata for report generation.
 */
class ReportTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'report_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'module',
        'type',
        'content',
        'template_file',
        'instansi_id',
        'is_active',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'instansi_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the institution that owns the template.
     */
    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    /**
     * Get the user who created the template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the template.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get reports that use this template.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'template_id');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by module.
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by institution.
     */
    public function scopeByInstansi($query, $instansiId)
    {
        return $query->where(function($q) use ($instansiId) {
            $q->where('instansi_id', $instansiId)
              ->orWhereNull('instansi_id');
        });
    }

    /**
     * Check if template is available for specific institution.
     */
    public function isAvailableFor($instansiId = null)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->instansi_id === null) {
            return true; // Global template
        }

        return $this->instansi_id === $instansiId;
    }

    /**
     * Get template content with variable replacements.
     */
    public function getProcessedContent($variables = [])
    {
        $content = $this->content;
        
        if (empty($variables) || empty($content)) {
            return $content;
        }

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}