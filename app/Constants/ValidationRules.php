<?php

namespace App\Constants;

/**
 * Validation Rule Constants
 *
 * Provides centralized validation rules to eliminate magic numbers
 * and ensure consistency across form validations.
 */
class ValidationRules
{
    // Field length limits
    public const CODE_MAX_LENGTH = 50;
    public const NAME_MAX_LENGTH = 255;
    public const DESCRIPTION_MAX_LENGTH = 1000;
    public const SHORT_TEXT_MAX_LENGTH = 500;
    public const MEDIUM_TEXT_MAX_LENGTH = 1000;
    public const LONG_TEXT_MAX_LENGTH = 5000;

    // File upload limits
    public const MAX_FILE_SIZE = 10240; // 10MB in KB
    public const MAX_IMAGE_SIZE = 2048; // 2MB in KB
    public const MAX_DOCUMENT_SIZE = 5120; // 5MB in KB

    // Number ranges
    public const MIN_YEAR = 2000;
    public const MAX_YEAR = 2099;
    public const MIN_PERCENTAGE = 0;
    public const MAX_PERCENTAGE = 100;

    /**
     * Get allowed file mime types for documents
     */
    public static function allowedDocumentMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/jpg',
        ];
    }

    /**
     * Get allowed file extensions for documents
     */
    public static function allowedDocumentExtensions(): array
    {
        return [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'jpg',
            'jpeg',
            'png',
        ];
    }
}
