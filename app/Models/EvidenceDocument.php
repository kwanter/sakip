<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EvidenceDocument Model
 * 
 * Represents supporting documents and evidence for performance data.
 * Stores file metadata and paths for validation purposes.
 */
class EvidenceDocument extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evidence_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'performance_data_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'metadata',
        'uploaded_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the performance data that owns the evidence document.
     */
    public function performanceData()
    {
        return $this->belongsTo(PerformanceData::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute()
    {
        if ($this->file_size === null) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if the file is an image.
     */
    public function getIsImageAttribute()
    {
        return in_array(strtolower($this->file_extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg']);
    }

    /**
     * Check if the file is a PDF.
     */
    public function getIsPdfAttribute()
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    /**
     * Check if the file is a spreadsheet.
     */
    public function getIsSpreadsheetAttribute()
    {
        return in_array(strtolower($this->file_extension), ['xls', 'xlsx', 'csv', 'ods']);
    }

    /**
     * Get the appropriate icon class for the file type.
     */
    public function getIconClassAttribute()
    {
        if ($this->is_image) {
            return 'fas fa-image';
        } elseif ($this->is_pdf) {
            return 'fas fa-file-pdf';
        } elseif ($this->is_spreadsheet) {
            return 'fas fa-file-excel';
        } elseif (in_array(strtolower($this->file_extension), ['doc', 'docx'])) {
            return 'fas fa-file-word';
        } elseif (in_array(strtolower($this->file_extension), ['ppt', 'pptx'])) {
            return 'fas fa-file-powerpoint';
        } else {
            return 'fas fa-file';
        }
    }

    /**
     * Get the full file URL.
     */
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    public function deleteFile()
    {
        if ($this->file_path && file_exists(storage_path('app/public/' . $this->file_path))) {
            unlink(storage_path('app/public/' . $this->file_path));
        }
    }

    /**
     * Boot method to handle file cleanup.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->isForceDeleting()) {
                $document->deleteFile();
            }
        });
    }

    /**
     * Scope to get documents by file type.
     */
    public function scopeByFileType($query, string $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Scope to get documents for specific performance data.
     */
    public function scopeForPerformanceData($query, int $performanceDataId)
    {
        return $query->where('performance_data_id', $performanceDataId);
    }

    /**
     * Scope to get documents uploaded by a specific user.
     */
    public function scopeUploadedBy($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }
}