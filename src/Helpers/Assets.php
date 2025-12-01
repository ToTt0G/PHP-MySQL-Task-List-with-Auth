<?php
namespace App\Helpers;

class Assets {
    public static function versioned($path) {
        // Ensure we are checking the correct file on disk
        // Remove any leading slash from $path for filesystem check
        $relativePath = ltrim($path, '/');
        $absolutePath = __DIR__ . '/../../public/' . $relativePath;
        
        $version = '';
        if (file_exists($absolutePath)) {
            $version = '?v=' . filemtime($absolutePath);
        }
        
        // Always return a root-relative path (starting with /)
        return '/' . $relativePath . $version;
    }
}
