<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AppHelper
{
    /**
     * Generate a unique filename for uploaded files
     *
     * @param string $originalName
     * @param string $extension
     * @return string
     */
    public static function generateUniqueFilename($originalName, $extension = null)
    {
        $extension = $extension ?: pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $sanitized = Str::slug($filename);
        
        return $sanitized . '_' . time() . '_' . Str::random(10) . '.' . $extension;
    }
    
    /**
     * Check if a file is a valid PDF and within size constraints
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $minSize Size in KB
     * @param int $maxSize Size in KB
     * @return bool
     */
    public static function isValidPdf($file, $minSize = 100, $maxSize = 500)
    {
        // Check MIME type
        if ($file->getMimeType() !== 'application/pdf') {
            return false;
        }
        
        // Check file size (convert KB to bytes)
        $fileSize = $file->getSize();
        $minBytes = $minSize * 1024;
        $maxBytes = $maxSize * 1024;
        
        return $fileSize >= $minBytes && $fileSize <= $maxBytes;
    }
    
    /**
     * Format a JSON string for display
     *
     * @param string|array $json
     * @return string
     */
    public static function formatJson($json)
    {
        if (is_array($json)) {
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        
        if (is_string($json)) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT);
            }
        }
        
        return $json;
    }
    
    /**
     * Get file size in human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatFileSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Check if a string is valid JSON
     *
     * @param string $string
     * @return bool
     */
    public static function isValidJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
