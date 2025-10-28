<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecureFileUploadMiddleware
{
    /**
     * Allowed MIME types for file uploads.
     */
    protected array $allowedMimeTypes = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',

        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',

        // Archives (be cautious)
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
    ];

    /**
     * Allowed file extensions.
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp',
        'svg', 'zip', 'rar',
    ];

    /**
     * Dangerous file extensions that should never be allowed.
     */
    protected array $dangerousExtensions = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'pht',
        'exe', 'com', 'bat', 'cmd', 'sh', 'bash', 'ps1',
        'js', 'vbs', 'jar', 'app', 'dmg', 'msi',
        'sql', 'sqlite', 'db',
    ];

    /**
     * Maximum file size in bytes (default: 10MB).
     */
    protected int $maxFileSize = 10485760; // 10MB

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check requests with file uploads
        if ($request->hasFile('file') || $request->hasFile('evidence') || $request->hasFile('document')) {
            $this->validateFileUploads($request);
        }

        // Check all uploaded files
        foreach ($request->allFiles() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile, $key);
                }
            } else {
                $this->validateSingleFile($file, $key);
            }
        }

        return $next($request);
    }

    /**
     * Validate all file uploads in the request.
     *
     * @param Request $request
     * @return void
     */
    protected function validateFileUploads(Request $request): void
    {
        $files = $request->allFiles();

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $this->validateSingleFile($singleFile, $key);
                }
            } else {
                $this->validateSingleFile($file, $key);
            }
        }
    }

    /**
     * Validate a single uploaded file.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $fieldName
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function validateSingleFile($file, string $fieldName): void
    {
        if (!$file->isValid()) {
            Log::warning('Invalid file upload attempt', [
                'field' => $fieldName,
                'error' => $file->getErrorMessage(),
                'ip' => request()->ip(),
            ]);

            abort(422, 'Invalid file upload: ' . $file->getErrorMessage());
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            Log::warning('File size exceeded', [
                'field' => $fieldName,
                'size' => $file->getSize(),
                'max_size' => $this->maxFileSize,
                'ip' => request()->ip(),
            ]);

            abort(422, 'File size exceeds maximum allowed size of ' . $this->formatBytes($this->maxFileSize));
        }

        // Check for dangerous extensions
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, $this->dangerousExtensions)) {
            Log::warning('Dangerous file extension blocked', [
                'field' => $fieldName,
                'extension' => $extension,
                'filename' => $file->getClientOriginalName(),
                'ip' => request()->ip(),
            ]);

            abort(422, 'File type not allowed for security reasons.');
        }

        // Check allowed extensions
        if (!in_array($extension, $this->allowedExtensions)) {
            Log::warning('Disallowed file extension', [
                'field' => $fieldName,
                'extension' => $extension,
                'filename' => $file->getClientOriginalName(),
                'ip' => request()->ip(),
            ]);

            abort(422, 'File extension .' . $extension . ' is not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions));
        }

        // Check MIME type
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            Log::warning('Disallowed MIME type', [
                'field' => $fieldName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'filename' => $file->getClientOriginalName(),
                'ip' => request()->ip(),
            ]);

            abort(422, 'File type ' . $mimeType . ' is not allowed.');
        }

        // Check for double extensions (e.g., file.php.jpg)
        $filename = $file->getClientOriginalName();
        if ($this->hasDoubleExtension($filename)) {
            Log::warning('Double extension detected', [
                'field' => $fieldName,
                'filename' => $filename,
                'ip' => request()->ip(),
            ]);

            abort(422, 'Files with double extensions are not allowed for security reasons.');
        }

        // Additional security check: verify MIME type matches extension
        if (!$this->mimeTypeMatchesExtension($mimeType, $extension)) {
            Log::warning('MIME type mismatch with extension', [
                'field' => $fieldName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'filename' => $filename,
                'ip' => request()->ip(),
            ]);

            abort(422, 'File content does not match its extension.');
        }

        // Check for executable content in the file
        if ($this->containsExecutableContent($file)) {
            Log::warning('Executable content detected in uploaded file', [
                'field' => $fieldName,
                'filename' => $filename,
                'mime_type' => $mimeType,
                'ip' => request()->ip(),
            ]);

            abort(422, 'File contains potentially dangerous content.');
        }

        // Log successful upload validation
        Log::info('File upload validated successfully', [
            'field' => $fieldName,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $mimeType,
            'extension' => $extension,
        ]);
    }

    /**
     * Check if filename has double extension.
     *
     * @param string $filename
     * @return bool
     */
    protected function hasDoubleExtension(string $filename): bool
    {
        // Count dots in filename (excluding the final extension)
        $parts = explode('.', $filename);

        if (count($parts) < 3) {
            return false;
        }

        // Check if any part before the last extension is a dangerous extension
        array_pop($parts); // Remove the last extension

        foreach ($parts as $part) {
            if (in_array(strtolower($part), $this->dangerousExtensions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify that MIME type matches the file extension.
     *
     * @param string $mimeType
     * @param string $extension
     * @return bool
     */
    protected function mimeTypeMatchesExtension(string $mimeType, string $extension): bool
    {
        $mimeTypeMap = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt' => ['text/plain'],
            'csv' => ['text/csv', 'text/plain', 'application/csv'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed'],
        ];

        $allowedMimes = $mimeTypeMap[$extension] ?? [];

        return in_array($mimeType, $allowedMimes);
    }

    /**
     * Check if file contains executable content.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    protected function containsExecutableContent($file): bool
    {
        // Read first 1KB of file
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return false;
        }

        $content = fread($handle, 1024);
        fclose($handle);

        // Check for common executable signatures
        $signatures = [
            '<?php',
            '<?=',
            '<%',
            '<script',
            'eval(',
            'exec(',
            'system(',
            'passthru(',
            'shell_exec(',
            'base64_decode(',
        ];

        foreach ($signatures as $signature) {
            if (stripos($content, $signature) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Set custom allowed MIME types.
     *
     * @param array $mimeTypes
     * @return $this
     */
    public function setAllowedMimeTypes(array $mimeTypes): self
    {
        $this->allowedMimeTypes = $mimeTypes;
        return $this;
    }

    /**
     * Set custom maximum file size.
     *
     * @param int $bytes
     * @return $this
     */
    public function setMaxFileSize(int $bytes): self
    {
        $this->maxFileSize = $bytes;
        return $this;
    }
}
