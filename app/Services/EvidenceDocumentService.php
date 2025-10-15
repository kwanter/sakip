<?php

namespace App\Services;

use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\UploadedFile;
use Exception;

class EvidenceDocumentService
{
    protected $cacheTimeout = 3600; // 1 hour
    protected $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff',
        'csv', 'txt', 'zip', 'rar'
    ];
    
    protected $maxFileSize = 10 * 1024 * 1024; // 10MB
    protected $uploadPath = 'evidence-documents';

    /**
     * Upload evidence document
     */
    public function uploadEvidence(array $data, UploadedFile $file): EvidenceDocument
    {
        return DB::transaction(function () use ($data, $file) {
            // Validate data and file
            $validator = $this->validateEvidenceData($data, $file);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Validate file
            $this->validateFile($file);

            // Generate unique file name
            $fileName = $this->generateFileName($file);
            $filePath = $this->getUploadPath($data['instansi_id'] ?? null) . '/' . $fileName;

            // Store file
            $storedPath = $file->storeAs($this->uploadPath, $filePath, 'public');

            if (!$storedPath) {
                throw new Exception('Failed to upload file');
            }

            // Create evidence document record
            $evidence = EvidenceDocument::create([
                'instansi_id' => $data['instansi_id'] ?? null,
                'performance_data_id' => $data['performance_data_id'] ?? null,
                'assessment_id' => $data['assessment_id'] ?? null,
                'document_type' => $data['document_type'] ?? 'evidence',
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'file_extension' => $file->getClientOriginalExtension(),
                'description' => $data['description'] ?? null,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'validation_status' => 'pending',
                'validation_notes' => null,
                'validated_by' => null,
                'validated_at' => null,
            ]);

            // Log activity
            $this->logActivity('upload', $evidence, 'Evidence document uploaded');

            // Clear cache
            $this->clearEvidenceCache($data['instansi_id'] ?? null);

            // Process document (e.g., extract metadata, generate thumbnail)
            $this->processDocument($evidence);

            return $evidence->fresh(['uploadedBy', 'instansi', 'performanceData', 'assessment']);
        });
    }

    /**
     * Upload multiple evidence documents
     */
    public function uploadMultipleEvidences(array $files, array $metadata = []): array
    {
        $results = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $data = array_merge($metadata, [
                    'document_type' => $metadata['document_type'] ?? 'evidence',
                    'description' => $metadata['descriptions'][$index] ?? null,
                    'performance_data_id' => $metadata['performance_data_ids'][$index] ?? $metadata['performance_data_id'] ?? null,
                    'assessment_id' => $metadata['assessment_ids'][$index] ?? $metadata['assessment_id'] ?? null,
                    'instansi_id' => $metadata['instansi_id'] ?? null,
                ]);

                $results[] = $this->uploadEvidence($data, $file);
            } catch (Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => count($files),
            'success_count' => count($results),
            'error_count' => count($errors),
        ];
    }

    /**
     * Get evidence document by ID
     */
    public function getEvidence($id): ?EvidenceDocument
    {
        return Cache::remember("evidence_{$id}", $this->cacheTimeout, function () use ($id) {
            return EvidenceDocument::with(['uploadedBy', 'validatedBy', 'instansi', 'performanceData', 'assessment'])->find($id);
        });
    }

    /**
     * Get evidence documents with filters
     */
    public function getEvidences(array $filters = [], $perPage = 15)
    {
        $query = EvidenceDocument::with(['uploadedBy', 'validatedBy', 'instansi', 'performanceData', 'assessment']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_data_id'])) {
            $query->where('performance_data_id', $filters['performance_data_id']);
        }

        if (isset($filters['assessment_id'])) {
            $query->where('assessment_id', $filters['assessment_id']);
        }

        if (isset($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        if (isset($filters['validation_status'])) {
            $query->where('validation_status', $filters['validation_status']);
        }

        if (isset($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        if (isset($filters['validated_by'])) {
            $query->where('validated_by', $filters['validated_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('file_name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('instansi', function ($q2) use ($filters) {
                        $q2->where('name', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('uploadedBy', function ($q2) use ($filters) {
                        $q2->where('name', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        // Date range filters
        if (isset($filters['uploaded_from'])) {
            $query->whereDate('uploaded_at', '>=', $filters['uploaded_from']);
        }

        if (isset($filters['uploaded_to'])) {
            $query->whereDate('uploaded_at', '<=', $filters['uploaded_to']);
        }

        if (isset($filters['validated_from'])) {
            $query->whereDate('validated_at', '>=', $filters['validated_from']);
        }

        if (isset($filters['validated_to'])) {
            $query->whereDate('validated_at', '<=', $filters['validated_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'uploaded_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Update evidence document
     */
    public function updateEvidence(EvidenceDocument $evidence, array $data): EvidenceDocument
    {
        return DB::transaction(function () use ($evidence, $data) {
            // Validate data
            $validator = Validator::make($data, [
                'description' => 'nullable|string|max:1000',
                'document_type' => 'nullable|string|in:evidence,supporting_document,reference,other',
            ]);

            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Update evidence
            $evidence->update($data);

            // Log activity
            $this->logActivity('update', $evidence, 'Evidence document updated');

            // Clear cache
            $this->clearEvidenceCache($evidence->instansi_id);

            return $evidence->fresh(['uploadedBy', 'validatedBy', 'instansi', 'performanceData', 'assessment']);
        });
    }

    /**
     * Validate evidence document
     */
    public function validateEvidence(EvidenceDocument $evidence, string $status, string $notes = null): EvidenceDocument
    {
        return DB::transaction(function () use ($evidence, $status, $notes) {
            if (!in_array($status, ['validated', 'rejected', 'pending'])) {
                throw new Exception('Invalid validation status');
            }

            $oldStatus = $evidence->validation_status;

            $evidence->update([
                'validation_status' => $status,
                'validation_notes' => $notes,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            // Log activity
            $this->logActivity('validate', $evidence, "Evidence document {$status}: {$notes}");

            // Clear cache
            $this->clearEvidenceCache($evidence->instansi_id);

            // Trigger notifications
            $this->notifyValidationResult($evidence, $oldStatus, $status);

            return $evidence->fresh(['uploadedBy', 'validatedBy', 'instansi', 'performanceData', 'assessment']);
        });
    }

    /**
     * Delete evidence document
     */
    public function deleteEvidence(EvidenceDocument $evidence): bool
    {
        return DB::transaction(function () use ($evidence) {
            // Delete file if exists
            if ($evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
                Storage::disk('public')->delete($evidence->file_path);
            }

            // Log activity
            $this->logActivity('delete', $evidence, 'Evidence document deleted');

            // Delete record
            $result = $evidence->delete();

            // Clear cache
            $this->clearEvidenceCache($evidence->instansi_id);

            return $result;
        });
    }

    /**
     * Download evidence document
     */
    public function downloadEvidence(EvidenceDocument $evidence): array
    {
        if (!$evidence->file_path || !Storage::disk('public')->exists($evidence->file_path)) {
            throw new Exception('Evidence document file not found');
        }

        // Update download count
        $evidence->increment('download_count');

        // Log activity
        $this->logActivity('download', $evidence, 'Evidence document downloaded');

        return [
            'file_path' => $evidence->file_path,
            'file_name' => $evidence->file_name,
            'mime_type' => $evidence->file_type,
            'file_size' => $evidence->file_size,
        ];
    }

    /**
     * Get evidence statistics
     */
    public function getEvidenceStatistics($instansiId = null): array
    {
        $cacheKey = "evidence_statistics_{$instansiId}";
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId) {
            $query = EvidenceDocument::query();
            
            if ($instansiId) {
                $query->where('instansi_id', $instansiId);
            }

            return [
                'total_documents' => $query->count(),
                'validated' => $query->where('validation_status', 'validated')->count(),
                'rejected' => $query->where('validation_status', 'rejected')->count(),
                'pending' => $query->where('validation_status', 'pending')->count(),
                'total_size' => $query->sum('file_size'),
                'by_type' => $query->select('document_type', DB::raw('count(*) as count'))
                    ->groupBy('document_type')
                    ->pluck('count', 'document_type')
                    ->toArray(),
                'by_validation_status' => $query->select('validation_status', DB::raw('count(*) as count'))
                    ->groupBy('validation_status')
                    ->pluck('count', 'validation_status')
                    ->toArray(),
            ];
        });
    }

    /**
     * Bulk validate evidence documents
     */
    public function bulkValidateEvidences(array $evidenceIds, string $status, string $notes = null): array
    {
        $results = [];
        $errors = [];

        foreach ($evidenceIds as $evidenceId) {
            try {
                $evidence = EvidenceDocument::find($evidenceId);
                if (!$evidence) {
                    throw new Exception('Evidence document not found');
                }

                $results[] = $this->validateEvidence($evidence, $status, $notes);
            } catch (Exception $e) {
                $errors[] = [
                    'evidence_id' => $evidenceId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => count($evidenceIds),
            'success_count' => count($results),
            'error_count' => count($errors),
        ];
    }

    /**
     * Validate file
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new Exception('File size exceeds maximum allowed size of 10MB');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions));
        }

        // Validate MIME type
        $mimeType = $file->getMimeType();
        if (!$this->isValidMimeType($mimeType)) {
            throw new Exception('Invalid file type');
        }

        // Check if file is actually an uploaded file
        if (!$file->isValid()) {
            throw new Exception('File upload failed');
        }
    }

    /**
     * Check if MIME type is valid
     */
    protected function isValidMimeType(string $mimeType): bool
    {
        $allowedMimeTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'text/csv',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed',
        ];

        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Generate unique file name
     */
    protected function generateFileName(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Sanitize original name
        $sanitizedName = preg_replace('/[^a-zA-Z0-9-_]/', '_', $originalName);
        
        return sprintf('%s_%s_%s.%s', $sanitizedName, $timestamp, $random, $extension);
    }

    /**
     * Get upload path
     */
    protected function getUploadPath($instansiId = null): string
    {
        $basePath = date('Y/m');
        
        if ($instansiId) {
            return sprintf('%s/instansi_%s', $basePath, $instansiId);
        }
        
        return $basePath;
    }

    /**
     * Process document (extract metadata, generate thumbnail, etc.)
     */
    protected function processDocument(EvidenceDocument $evidence): void
    {
        try {
            // Extract file metadata
            $metadata = $this->extractFileMetadata($evidence);
            
            // Update evidence with metadata
            $evidence->update([
                'metadata' => json_encode($metadata),
            ]);

            // Generate thumbnail for images
            if ($this->isImageFile($evidence->file_extension)) {
                $this->generateThumbnail($evidence);
            }
        } catch (Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to process evidence document', [
                'evidence_id' => $evidence->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract file metadata
     */
    protected function extractFileMetadata(EvidenceDocument $evidence): array
    {
        $filePath = Storage::disk('public')->path($evidence->file_path);
        
        if (!file_exists($filePath)) {
            return [];
        }

        $metadata = [
            'file_size' => filesize($filePath),
            'modified_time' => filemtime($filePath),
            'created_time' => filectime($filePath),
        ];

        // Extract image metadata
        if ($this->isImageFile($evidence->file_extension)) {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $metadata['image_width'] = $imageInfo[0];
                $metadata['image_height'] = $imageInfo[1];
                $metadata['image_type'] = $imageInfo['mime'];
            }
        }

        return $metadata;
    }

    /**
     * Check if file is an image
     */
    protected function isImageFile(string $extension): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff'];
        return in_array(strtolower($extension), $imageExtensions);
    }

    /**
     * Generate thumbnail for image files
     */
    protected function generateThumbnail(EvidenceDocument $evidence): void
    {
        // This would typically use ImageMagick or GD library
        // For now, we'll just log that thumbnail generation is needed
        \Log::info('Thumbnail generation needed for evidence document', [
            'evidence_id' => $evidence->id,
            'file_path' => $evidence->file_path,
        ]);
    }

    /**
     * Validate evidence data
     */
    protected function validateEvidenceData(array $data, UploadedFile $file = null): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'instansi_id' => 'nullable|exists:instansi,id',
            'performance_data_id' => 'nullable|exists:performance_data,id',
            'assessment_id' => 'nullable|exists:assessments,id',
            'document_type' => 'required|string|in:evidence,supporting_document,reference,other',
            'description' => 'nullable|string|max:1000',
        ];

        // Add cross-validation rules
        $rules['performance_data_id'] = [
            'nullable',
            'exists:performance_data,id',
            function ($attribute, $value, $fail) use ($data) {
                if ($value && isset($data['instansi_id'])) {
                    $performanceData = PerformanceData::find($value);
                    if ($performanceData && $performanceData->instansi_id != $data['instansi_id']) {
                        $fail('Performance data does not belong to the specified institution.');
                    }
                }
            }
        ];

        $rules['assessment_id'] = [
            'nullable',
            'exists:assessments,id',
            function ($attribute, $value, $fail) use ($data) {
                if ($value && isset($data['instansi_id'])) {
                    $assessment = Assessment::find($value);
                    if ($assessment && $assessment->instansi_id != $data['instansi_id']) {
                        $fail('Assessment does not belong to the specified institution.');
                    }
                }
            }
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Notify validation result
     */
    protected function notifyValidationResult(EvidenceDocument $evidence, string $oldStatus, string $newStatus): void
    {
        // This would typically use a notification service
        // For now, we'll just log the notification
        \Log::info('Evidence validation notification', [
            'evidence_id' => $evidence->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'validated_by' => auth()->id(),
        ]);
    }

    /**
     * Clear evidence cache
     */
    protected function clearEvidenceCache($instansiId): void
    {
        Cache::forget("evidence_statistics_{$instansiId}");
        
        // Clear all evidence caches for this instansi
        $keys = Cache::getRedis()->keys("evidence_*");
        foreach ($keys as $key) {
            if (strpos($key, "_{$instansiId}") !== false) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, EvidenceDocument $evidence, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => $evidence->instansi_id,
            'module' => 'sakip',
            'activity' => $action . '_evidence',
            'description' => $description,
            'old_values' => $action === 'update' || $action === 'validate' ? $evidence->getOriginal() : null,
            'new_values' => $action !== 'delete' ? $evidence->toArray() : null,
        ]);
    }
}