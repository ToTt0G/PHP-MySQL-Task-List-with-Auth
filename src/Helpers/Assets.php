<?php
namespace App\Helpers;

class Assets {
    public static function versioned($path) {
        $absolutePath = __DIR__ . '/../../public/' . ltrim($path, '/');
        if (file_exists($absolutePath)) {
            return $path . '?v=' . filemtime($absolutePath);
        }
        return $path;
    }
}
