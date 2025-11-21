<?php
/**
 * Secure File Upload Validator
 * Validates and processes image uploads with security best practices
 */

class FileUploadValidator
{
    // Allowed MIME types
    private static $allowed_mime_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    // Allowed file extensions
    private static $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Maximum file size (5MB)
    private static $max_file_size = 5 * 1024 * 1024;

    // Maximum image dimensions (10000x10000)
    private static $max_width = 10000;
    private static $max_height = 10000;

    /**
     * Validate uploaded file
     *
     * @param array $file $_FILES array
     * @return array ['valid' => bool, 'message' => string, 'data' => array]
     */
    public static function validate($file)
    {
        // Check if file was uploaded without errors
        if (!isset($file['error'])) {
            return [
                'valid' => false,
                'message' => 'File tidak ditemukan.',
                'data' => []
            ];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'message' => self::getUploadErrorMessage($file['error']),
                'data' => []
            ];
        }

        // Check file size
        if ($file['size'] > self::$max_file_size) {
            return [
                'valid' => false,
                'message' => 'Ukuran file terlalu besar. Maksimal 5MB.',
                'data' => []
            ];
        }

        // Check file size is not zero
        if ($file['size'] <= 0) {
            return [
                'valid' => false,
                'message' => 'File kosong atau tidak valid.',
                'data' => []
            ];
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($extension, self::$allowed_extensions)) {
            return [
                'valid' => false,
                'message' => 'Format file tidak didukung. Gunakan: JPG, PNG, GIF, atau WebP.',
                'data' => []
            ];
        }

        // Validate MIME type using getimagesize
        $image_check = getimagesize($file['tmp_name']);
        if ($image_check === false) {
            return [
                'valid' => false,
                'message' => 'File bukan gambar yang valid.',
                'data' => []
            ];
        }

        // Get actual MIME type
        $actual_mime = $image_check['mime'];

        // Verify MIME type is allowed
        if (!in_array($actual_mime, self::$allowed_mime_types)) {
            return [
                'valid' => false,
                'message' => 'Tipe MIME file tidak diizinkan.',
                'data' => []
            ];
        }

        // Validate image dimensions
        $width = $image_check[0];
        $height = $image_check[1];

        if ($width > self::$max_width || $height > self::$max_height) {
            return [
                'valid' => false,
                'message' => 'Dimensi gambar terlalu besar. Maksimal 10000x10000 pixel.',
                'data' => []
            ];
        }

        // Additional check: verify extension matches MIME type
        if (!self::extensionMatchesMime($extension, $actual_mime)) {
            return [
                'valid' => false,
                'message' => 'Ekstensi file tidak sesuai dengan tipe MIME.',
                'data' => []
            ];
        }

        // Verify file is readable
        if (!is_readable($file['tmp_name'])) {
            return [
                'valid' => false,
                'message' => 'File tidak dapat dibaca.',
                'data' => []
            ];
        }

        // Generate safe filename
        $new_filename = self::generateSafeFilename($extension);

        return [
            'valid' => true,
            'message' => 'File valid dan siap diunggah.',
            'data' => [
                'filename' => $new_filename,
                'extension' => $extension,
                'mime_type' => $actual_mime,
                'width' => $width,
                'height' => $height,
                'size' => $file['size']
            ]
        ];
    }

    /**
     * Generate safe filename with timestamp and random string
     *
     * @param string $extension
     * @return string
     */
    private static function generateSafeFilename($extension)
    {
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        return 'product_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Check if file extension matches MIME type
     *
     * @param string $extension
     * @param string $mime_type
     * @return bool
     */
    private static function extensionMatchesMime($extension, $mime_type)
    {
        $mime_map = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp']
        ];

        if (!isset($mime_map[$extension])) {
            return false;
        }

        return in_array($mime_type, $mime_map[$extension]);
    }

    /**
     * Get human-readable upload error message
     *
     * @param int $error_code
     * @return string
     */
    private static function getUploadErrorMessage($error_code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi ukuran maksimal server).',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi ukuran dalam form).',
            UPLOAD_ERR_PARTIAL => 'File hanya diunggah sebagian.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder upload tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Tidak dapat menulis file ke disk.',
            UPLOAD_ERR_EXTENSION => 'Ekstensi file diblokir oleh server.'
        ];

        return isset($errors[$error_code]) ? $errors[$error_code] : 'Kesalahan upload tidak diketahui.';
    }

    /**
     * Move uploaded file to destination with error handling
     *
     * @param string $tmp_file
     * @param string $destination
     * @return array ['success' => bool, 'message' => string]
     */
    public static function moveUploadedFile($tmp_file, $destination)
    {
        // Ensure destination directory exists and is writable
        $destination_dir = dirname($destination);

        if (!is_dir($destination_dir)) {
            return [
                'success' => false,
                'message' => 'Folder upload tidak ditemukan.'
            ];
        }

        if (!is_writable($destination_dir)) {
            return [
                'success' => false,
                'message' => 'Folder upload tidak dapat ditulis.'
            ];
        }

        // Check if destination file already exists
        if (file_exists($destination)) {
            return [
                'success' => false,
                'message' => 'File sudah ada.'
            ];
        }

        // Move file
        if (!move_uploaded_file($tmp_file, $destination)) {
            return [
                'success' => false,
                'message' => 'Gagal memindahkan file.'
            ];
        }

        // Set restrictive permissions (644)
        chmod($destination, 0644);

        return [
            'success' => true,
            'message' => 'File berhasil diunggah.'
        ];
    }

    /**
     * Delete a file safely
     *
     * @param string $file_path
     * @return bool
     */
    public static function deleteFile($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }

        if (!is_readable($file_path) || !is_writable(dirname($file_path))) {
            return false;
        }

        return unlink($file_path);
    }
}
?>