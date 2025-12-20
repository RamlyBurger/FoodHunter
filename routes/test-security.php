<?php

use Illuminate\Support\Facades\Route;
use App\Services\FileValidationService;
use App\Services\SecurityLoggingService;

/**
 * Security Features Testing Routes
 * 
 * Test the implemented security features:
 * [183] File header validation
 * [107] Generic error messages
 * [122] Authentication logging
 */

Route::middleware('web')->group(function () {
    
    /**
     * Test File Validation Service
     * GET /test-file-validation
     */
    Route::get('/test-file-validation', function () {
        $fileValidator = app(FileValidationService::class);
        
        return response()->json([
            'success' => true,
            'message' => 'File Validation Service is ready',
            'allowed_extensions' => $fileValidator->getAllowedExtensions(),
            'max_file_size' => $fileValidator->getMaxFileSize(),
            'features' => [
                'magic_bytes_checking' => true,
                'mime_type_verification' => true,
                'size_validation' => true,
                'gd_library_validation' => true,
            ],
            'supported_formats' => [
                'JPEG' => 'FF D8 FF',
                'PNG' => '89 50 4E 47 0D 0A 1A 0A',
                'GIF' => '47 49 46 38',
                'WebP' => '52 49 46 46 + WEBP',
            ],
        ]);
    });

    /**
     * Test Security Logging Service
     * GET /test-security-logging
     */
    Route::get('/test-security-logging', function () {
        $securityLogger = app(SecurityLoggingService::class);
        
        // Test logging (will appear in storage/logs/laravel.log)
        $securityLogger->logAuthenticationAttempt('test', true, [
            'test' => 'Testing authentication logging',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Security Logging Service is ready',
            'log_file' => storage_path('logs/laravel.log'),
            'features' => [
                'authentication_logging' => true,
                'validation_failure_logging' => true,
                'exception_logging' => true,
                'access_control_logging' => true,
                'sensitive_data_redaction' => true,
            ],
            'logged_events' => [
                'authentication_attempts',
                'input_validation_failures',
                'system_exceptions',
                'administrative_actions',
                'file_uploads',
                'tampering_attempts',
            ],
            'check_logs' => 'tail -f ' . storage_path('logs/laravel.log'),
        ]);
    });

    /**
     * Test All Security Features
     * GET /test-security
     */
    Route::get('/test-security', function () {
        $fileValidator = app(FileValidationService::class);
        $securityLogger = app(SecurityLoggingService::class);
        
        return view('test-security', [
            'fileValidator' => $fileValidator,
            'securityLogger' => $securityLogger,
        ]);
    });
});

/**
 * API Routes for Testing
 */
Route::prefix('api')->middleware('api')->group(function () {
    
    /**
     * Test File Upload Validation (Demo)
     * POST /api/test-file-upload
     * 
     * Usage:
     * curl -X POST http://localhost/foodhunter/public/api/test-file-upload \
     *   -F "file=@test_image.jpg"
     */
    Route::post('/test-file-upload', function (\Illuminate\Http\Request $request) {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No file uploaded',
                ], 400);
            }
            
            $fileValidator = app(FileValidationService::class);
            $file = $request->file('file');
            
            $validation = $fileValidator->validateImage($file, 'test_upload');
            
            if ($validation['valid']) {
                return response()->json([
                    'success' => true,
                    'message' => 'File is valid! ✅',
                    'details' => [
                        'filename' => $file->getClientOriginalName(),
                        'mime_type' => $validation['mime_type'],
                        'size' => $file->getSize(),
                        'size_human' => number_format($file->getSize() / 1024, 2) . ' KB',
                        'validation_passed' => [
                            'magic_bytes_check' => true,
                            'mime_type_verification' => true,
                            'size_check' => true,
                            'gd_library_validation' => true,
                        ],
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File validation failed',
                    'error' => $validation['error'],
                    'filename' => $file->getClientOriginalName(),
                    'claimed_mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ], 400);
            }
            
        } catch (\Exception $e) {
            // [107] Generic error message
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the file',
            ], 500);
        }
    });
});
