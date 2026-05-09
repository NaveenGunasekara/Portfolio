<?php
/**
 * Image upload validation + storage under /uploads.
 * Returns relative web path e.g. uploads/projects/abc.jpg or null on failure.
 */
declare(strict_types=1);

function portfolio_allowed_image_mime(string $mime): bool
{
    $allowed = [
        'image/jpeg' => true,
        'image/png'  => true,
        'image/webp' => true,
        'image/gif'  => true,
    ];
    return isset($allowed[$mime]);
}

function portfolio_upload_image(array $file, string $subdir, int $maxBytes = 5_000_000): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        return null;
    }

    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) {
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    if (!portfolio_allowed_image_mime($mime)) {
        return null;
    }

    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $ext = $extMap[$mime];

    $subdir = preg_replace('/[^a-z0-9_-]/i', '', $subdir) ?: 'misc';
    $base = dirname(__DIR__) . '/uploads/' . $subdir;
    if (!is_dir($base) && !mkdir($base, 0775, true) && !is_dir($base)) {
        return null;
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $destFs = $base . '/' . $name;
    if (!move_uploaded_file($tmp, $destFs)) {
        return null;
    }

    return 'uploads/' . $subdir . '/' . $name;
}

function portfolio_delete_upload(?string $relativePath): void
{
    if (!$relativePath || !str_starts_with($relativePath, 'uploads/')) {
        return;
    }
    $full = dirname(__DIR__) . '/' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
}
