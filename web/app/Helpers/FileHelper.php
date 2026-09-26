<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Formats file size to human readable format
     */
    public static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        if ($bytes <= 0) {
            return '0 B';
        }

        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    /**
     * Extracts file category from filename
     */
    public static function extractCategory(string $filename): string
    {
        $parts = explode('_', $filename);
        return $parts[0] ?? 'unknown';
    }

    /**
     * Validates file extension
     */
    public static function isValidExtension(string $extension, array $allowed = ['txt', 'enc', 'bin']): bool
    {
        return in_array(strtolower($extension), $allowed);
    }
}