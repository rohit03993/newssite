<?php
/**
 * Small JPEG for WhatsApp and Facebook link previews.
 * Full news photos are often large PNG files. Those apps skip them and show the logo instead.
 */
if (PHP_SAPI !== 'cli') {
    nm_send_share_image();
}

function nm_send_share_image()
{
$file = isset($_GET['f']) ? basename((string) $_GET['f']) : '';
if ($file === '' || !preg_match('/\.(jpe?g|png|webp|gif)$/i', $file)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Bad request';
    exit;
}

$path = __DIR__ . '/images/news/' . $file;
$realNews = realpath(__DIR__ . '/images/news');
$realFile = realpath($path);
$newsPrefix = $realNews === false ? '' : rtrim($realNews, '/\\') . DIRECTORY_SEPARATOR;
if ($newsPrefix === '' || $realFile === false || strpos($realFile, $newsPrefix) !== 0 || !is_file($realFile)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found';
    exit;
}

$jpeg = nm_share_jpeg($realFile);
if ($jpeg === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found';
    exit;
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . strlen($jpeg));
header('Cache-Control: public, max-age=86400');
echo $jpeg;
exit;
}

function nm_share_jpeg($path)
{
    if (!function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
        return null;
    }
    $info = @getimagesize($path);
    if (!$info) {
        return null;
    }
    $srcW = (int) $info[0];
    $srcH = (int) $info[1];
    if ($srcW < 1 || $srcH < 1) {
        return null;
    }
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $src = @imagecreatefromjpeg($path);
            break;
        case IMAGETYPE_PNG:
            $src = @imagecreatefrompng($path);
            break;
        case IMAGETYPE_GIF:
            $src = @imagecreatefromgif($path);
            break;
        case IMAGETYPE_WEBP:
            $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
            break;
        default:
            $src = null;
    }
    if (!$src) {
        return null;
    }

    $scale = min(1200 / $srcW, 630 / $srcH, 1);
    $dstW = max(1, (int) round($srcW * $scale));
    $dstH = max(1, (int) round($srcH * $scale));
    $dst = imagecreatetruecolor($dstW, $dstH);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $white);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
    imagedestroy($src);

    $quality = 72;
    $jpeg = '';
    do {
        ob_start();
        imagejpeg($dst, null, $quality);
        $jpeg = ob_get_clean();
        $quality -= 8;
    } while (strlen($jpeg) > 280000 && $quality >= 40);
    imagedestroy($dst);
    if ($jpeg === '' || $jpeg === false) {
        return null;
    }
    return $jpeg;
}
