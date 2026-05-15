<?php
/**
 * Secure File Upload Helper
 * Validates file extensions and MIME types strictly to prevent malicious uploads (e.g., PHP execution, RCE).
 */

class FileUploadHelper {
    private static $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private static $allowed_image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    private static $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    private static $allowed_video_exts = ['mp4', 'webm', 'ogg'];

    /**
     * Secures and uploads an image or video file
     * 
     * @param array $file $_FILES['input_name']
     * @param string $target_directory Relative directory path e.g., '../../../uploads/avatars/'
     * @param string $prefix Prefix for the filename e.g., 'avatar_1_'
     * @param string $type 'image' or 'video'
     * @return string|false Database-ready path on success, false on failure
     * @throws Exception If validation fails
     */
    public static function secureUpload($file, $target_directory, $prefix, $type = 'image') {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid parameters.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('Exceeded filesize limit.');
            default:
                throw new Exception('Unknown errors.');
        }

        // Limit File Size
        $max_size = ($type === 'video') ? 50 * 1024 * 1024 : 5 * 1024 * 1024; // 50MB for video, 5MB for images
        if ($file['size'] > $max_size) {
            throw new Exception('Exceeded filesize limit.');
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        
        $allowed_mimes = ($type === 'video') ? self::$allowed_video_types : self::$allowed_image_types;
        if (!in_array($mime_type, $allowed_mimes, true)) {
            throw new Exception('Invalid file format. MIME type ' . htmlspecialchars($mime_type) . ' is not allowed.');
        }

        // Validate extension mapping (double verification)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ($type === 'video') ? self::$allowed_video_exts : self::$allowed_image_exts;
        
        if (!in_array($ext, $allowed_exts, true)) {
            throw new Exception('Invalid file extension.');
        }

        // Create target directory if missing
        if (!is_dir($target_directory)) {
            if (!mkdir($target_directory, 0777, true)) {
                throw new Exception('Failed to create destination directory.');
            }
        }

        // Generate cryptographically secure filename
        $safe_filename = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
        $target_path = $target_directory . $safe_filename;

        // Move the file
        if (!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception('Failed to move uploaded file.');
        }

        // Return path relative to the domain root (assuming standard upload structure)
        // Ensure path uses forward slashes (web friendly)
        
        // Find uploads part to unify standard db urls
        $path_parts = explode('uploads/', $target_path);
        if (count($path_parts) > 1) {
            return 'uploads/' . array_pop($path_parts);
        }

        return $target_path;
    }
}
