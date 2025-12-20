<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * FileValidationService - Validates uploaded files by checking file headers (magic bytes)
 * 
 * Security Implementation:
 * [183] Validate uploaded files are the expected type by checking file headers.
 * Checking for file type by extension alone is not sufficient.
 * 
 * This service checks the actual file content (magic bytes/file signature) 
 * to ensure files are what they claim to be, preventing malicious file uploads.
 */
class FileValidationService
{
    /**
     * Magic bytes for common image file types
     * First few bytes of file that identify the file format
     */
    private const MAGIC_BYTES = [
        'image/jpeg' => [
            ['bytes' => [0xFF, 0xD8, 0xFF], 'offset' => 0], // JPEG/JFIF
        ],
        'image/png' => [
            ['bytes' => [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A], 'offset' => 0], // PNG
        ],
        'image/gif' => [
            ['bytes' => [0x47, 0x49, 0x46, 0x38, 0x37, 0x61], 'offset' => 0], // GIF87a
            ['bytes' => [0x47, 0x49, 0x46, 0x38, 0x39, 0x61], 'offset' => 0], // GIF89a
        ],
        'image/webp' => [
            ['bytes' => [0x52, 0x49, 0x46, 0x46], 'offset' => 0, 'secondary' => 
            [0x57, 0x45, 0x42, 0x50], 'secondary_offset' => 8], // RIFF....WEBP
        ],
    ];

    /**
     * Allowed MIME types for vendor uploads
     */
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Maximum file size in bytes (2MB)
     */
    private const MAX_FILE_SIZE = 2097152; // 2 * 1024 * 1024

    /**
     * Validate uploaded image file by checking file headers
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $context Context for logging (e.g., 'menu_item', 'vendor_logo')
     * @return array ['valid' => bool, 'mime_type' => string|null, 'error' => string|null]
     */
    public function validateImage(UploadedFile $file, string $context = 'upload'): array
    {
        try {
            // Check if file is valid
            if (!$file->isValid()) {
                $this->logValidationFailure($context, 'Invalid file upload');
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'The uploaded file is invalid or corrupted.'
                ];
            }

            // Check file size
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                $this->logValidationFailure($context, 'File size exceeds limit', [
                    'size' => $file->getSize(),
                    'limit' => self::MAX_FILE_SIZE
                ]);
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'File size must not exceed 2MB.'
                ];
            }

            // Read first 32 bytes of file for magic byte checking
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                $this->logValidationFailure($context, 'Cannot read file');
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'Unable to read the uploaded file.'
                ];
            }

            $fileHeader = fread($handle, 32);
            fclose($handle);

            if ($fileHeader === false || strlen($fileHeader) < 8) {
                $this->logValidationFailure($context, 'Cannot read file header');
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'Unable to validate file format.'
                ];
            }

            // Check magic bytes against known signatures
            $detectedMimeType = $this->detectMimeTypeFromHeader($fileHeader);

            if ($detectedMimeType === null) {
                $this->logValidationFailure($context, 'Unknown file type detected', [
                    'claimed_mime' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension()
                ]);
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'Unsupported file type. Only JPEG, PNG, GIF, and WebP images are allowed.'
                ];
            }

            // Verify the detected MIME type matches the claimed type
            $claimedMimeType = $file->getMimeType();
            if ($detectedMimeType !== $claimedMimeType) {
                $this->logValidationFailure($context, 'MIME type mismatch', [
                    'claimed' => $claimedMimeType,
                    'detected' => $detectedMimeType,
                    'extension' => $file->getClientOriginalExtension()
                ]);
                return [
                    'valid' => false,
                    'mime_type' => null,
                    'error' => 'File type mismatch detected. The file may be corrupted or unsafe.'
                ];
            }

            // Optional validation: Check if file is actually an image using GD library
            // This is a bonus check - we don't fail if it doesn't work, as magic bytes + MIME are sufficient
            $gdValidation = $this->isValidImageContent($file->getRealPath());
            if (!$gdValidation) {
                // Log warning but don't reject - GD library might not be available or image might be valid but not loadable
                Log::info('GD library validation skipped or failed (non-critical)', [
                    'context' => $context,
                    'mime_type' => $detectedMimeType,
                ]);
            }

            // Log successful validation
            Log::info('File validation successful', [
                'context' => $context,
                'mime_type' => $detectedMimeType,
                'size' => $file->getSize(),
                'vendor_id' => Auth::check() ? Auth::user()->user_id : null,
            ]);

            return [
                'valid' => true,
                'mime_type' => $detectedMimeType,
                'error' => null
            ];

        } catch (\Exception $e) {
            // [107] Do not disclose sensitive information in error responses
            // Log full error details but return generic message to user
            Log::error('File validation exception', [
                'context' => $context,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'vendor_id' => Auth::check() ? Auth::user()->user_id : null,
            ]);

            return [
                'valid' => false,
                'mime_type' => null,
                'error' => 'An error occurred while validating the file. Please try again.'
            ];
        }
    }

    /**
     * Detect MIME type by checking file header (magic bytes)
     * 
     * @param string $fileHeader First bytes of the file
     * @return string|null Detected MIME type or null if unknown
     */
    private function detectMimeTypeFromHeader(string $fileHeader): ?string
    {
        foreach (self::MAGIC_BYTES as $mimeType => $signatures) {
            foreach ($signatures as $signature) {
                $matches = true;
                
                // Check primary magic bytes
                foreach ($signature['bytes'] as $index => $expectedByte) {
                    $offset = $signature['offset'] + $index;
                    if (!isset($fileHeader[$offset]) || ord($fileHeader[$offset]) !== $expectedByte) {
                        $matches = false;
                        break;
                    }
                }

                // Check secondary magic bytes if specified (e.g., for WebP)
                if ($matches && isset($signature['secondary'])) {
                    foreach ($signature['secondary'] as $index => $expectedByte) {
                        $offset = $signature['secondary_offset'] + $index;
                        if (!isset($fileHeader[$offset]) || ord($fileHeader[$offset]) !== $expectedByte) {
                            $matches = false;
                            break;
                        }
                    }
                }

                if ($matches) {
                    return $mimeType;
                }
            }
        }

        return null;
    }

    /**
     * Validate image content using GD library
     * 
     * @param string $filePath Path to the file
     * @return bool True if file is a valid image
     */
    private function isValidImageContent(string $filePath): bool
    {
        try {
            // Check if GD library is available
            if (!function_exists('getimagesize')) {
                // GD library not available - skip this validation
                return true; // Don't block upload
            }

            // Try to get image info using getimagesize
            $imageInfo = @\getimagesize($filePath);
            
            if ($imageInfo === false) {
                return false;
            }

            // Check if MIME type is in allowed list
            if (!in_array($imageInfo['mime'], self::ALLOWED_IMAGE_TYPES)) {
                return false;
            }

            // Additional check: Try to create image resource (only if functions exist)
            $imageResource = null;
            switch ($imageInfo['mime']) {
                case 'image/jpeg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $imageResource = @\imagecreatefromjpeg($filePath);
                    }
                    break;
                case 'image/png':
                    if (function_exists('imagecreatefrompng')) {
                        $imageResource = @\imagecreatefrompng($filePath);
                    }
                    break;
                case 'image/gif':
                    if (function_exists('imagecreatefromgif')) {
                        $imageResource = @\imagecreatefromgif($filePath);
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $imageResource = @\imagecreatefromwebp($filePath);
                    }
                    break;
            }

            if ($imageResource !== false && $imageResource !== null) {
                if (function_exists('imagedestroy')) {
                    @\imagedestroy($imageResource);
                }
                return true;
            }

            // If we got here and have valid imageInfo, that's good enough
            return true;

        } catch (\Exception $e) {
            return true; // Don't block upload on exception
        }
    }

    /**
     * Log validation failure with context
     * [121] Log all input validation failures
     * 
     * @param string $context Upload context
     * @param string $reason Failure reason
     * @param array $additional Additional data to log
     */
    private function logValidationFailure(string $context, string $reason, array $additional = []): void
    {
        Log::warning('File validation failed', array_merge([
            'context' => $context,
            'reason' => $reason,
            'vendor_id' => Auth::check() ? Auth::user()->user_id : null,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ], $additional));
    }

    /**
     * Get allowed file extensions
     * 
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    }

    /**
     * Get maximum file size in human-readable format
     * 
     * @return string
     */
    public function getMaxFileSize(): string
    {
        return '2MB';
    }
}
