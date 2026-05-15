<?php
/**
 * Image Helper for Landsfy
 */

if (!function_exists('getImageUrl')) {
    function getImageUrl($path) {
        if (empty($path)) return '';
        if (strpos($path, 'http') === 0) return $path;
        
        // Assume path is relative to root
        // If we are in a subdirectory like /agent or /admin, we might need ../
        // But for consistency with JS, we can use a root-relative path if possible
        // For now, let's keep it simple: if it's not absolute, prepend ../
        return '../' . $path;
    }
}
