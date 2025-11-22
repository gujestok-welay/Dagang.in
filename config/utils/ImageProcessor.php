<?php
/**
 * ImageProcessor
 * Utility untuk membuat thumbnail seragam untuk gambar produk.
 */
class ImageProcessor
{
    /**
     * Generate thumbnail (square) dengan crop center atau scale fit.
     * @param string $sourcePath Path file sumber.
     * @param string $destPath Path file tujuan thumbnail.
     * @param int $targetSize Ukuran sisi thumbnail (default 400).
     * @return array [success=>bool, message=>string]
     */
    public static function generateSquareThumbnail($sourcePath, $destPath, $targetSize = 400)
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => 'File sumber tidak ditemukan.'];
        }
        $info = getimagesize($sourcePath);
        if ($info === false) {
            return ['success' => false, 'message' => 'File bukan gambar valid.'];
        }
        // Pastikan ekstensi GD tersedia
        if (!extension_loaded('gd')) {
            return ['success' => false, 'message' => 'Ekstensi GD PHP tidak aktif. Aktifkan gd di php.ini.'];
        }

        [$width, $height] = $info;
        $mime = $info['mime'];
        $factoryMap = [
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/gif' => 'imagecreatefromgif',
            'image/webp' => 'imagecreatefromwebp'
        ];
        if (!isset($factoryMap[$mime])) {
            return ['success' => false, 'message' => 'Tipe MIME tidak didukung untuk thumbnail: ' . htmlspecialchars($mime)];
        }
        $factory = $factoryMap[$mime];
        if (!function_exists($factory)) {
            return ['success' => false, 'message' => 'Fungsi GD tidak tersedia: ' . $factory];
        }
        $srcImg = @$factory($sourcePath);
        if (!$srcImg) {
            return ['success' => false, 'message' => 'Gagal memuat gambar sumber (korup atau tipe tidak didukung penuh).'];
        }
        // Buat canvas square
        $thumb = imagecreatetruecolor($targetSize, $targetSize);
        // Transparansi utk PNG/GIF
        if (in_array($mime, ['image/png', 'image/gif'])) {
            imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        // Hitung crop area (center crop jika bukan square)
        $srcAspect = $width / $height;
        $targetAspect = 1; // square
        if ($srcAspect > $targetAspect) {
            // Sumber lebih lebar: crop horizontal
            $newHeight = $height;
            $newWidth = (int) ($height * $targetAspect);
            $srcX = (int) (($width - $newWidth) / 2);
            $srcY = 0;
        } else {
            // Sumber lebih tinggi: crop vertical
            $newWidth = $width;
            $newHeight = (int) ($width / $targetAspect);
            $srcX = 0;
            $srcY = (int) (($height - $newHeight) / 2);
        }
        imagecopyresampled(
            $thumb,
            $srcImg,
            0,
            0,
            $srcX,
            $srcY,
            $targetSize,
            $targetSize,
            $newWidth,
            $newHeight
        );
        // Pastikan folder ada
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                imagedestroy($thumb);
                imagedestroy($srcImg);
                return ['success' => false, 'message' => 'Gagal membuat folder thumbnail.'];
            }
        }
        // Simpan sesuai mime
        $saved = false;
        switch ($mime) {
            case 'image/jpeg':
                $saved = imagejpeg($thumb, $destPath, 85);
                break;
            case 'image/png':
                $saved = imagepng($thumb, $destPath, 6);
                break;
            case 'image/gif':
                $saved = imagegif($thumb, $destPath);
                break;
            case 'image/webp':
                $saved = function_exists('imagewebp') ? imagewebp($thumb, $destPath, 85) : false;
                break;
        }
        imagedestroy($thumb);
        imagedestroy($srcImg);
        if (!$saved) {
            return ['success' => false, 'message' => 'Gagal menyimpan thumbnail.'];
        }
        chmod($destPath, 0644);
        return ['success' => true, 'message' => 'Thumbnail berhasil dibuat.'];
    }
}
?>