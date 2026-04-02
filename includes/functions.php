<?php
require_once 'config.php';

/**
 * Format file size into human readable string
 */
function formatSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Get user storage usage
 */
function getStorageUsage($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT SUM(file_size) as total FROM files WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

/**
 * Get user storage limit
 */
function getStorageLimit($pdo, $user_id)
{
    $stmt = $pdo->prepare("SELECT storage_limit FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['storage_limit'] ?? 16106127360; // Default 15GB
}

/**
 * Get folder path for breadcrumbs
 */
function getFolderPath($pdo, $folder_id)
{
    $path = [];
    while ($folder_id) {
        $stmt = $pdo->prepare("SELECT id, name, parent_id FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        $folder = $stmt->fetch();
        if (!$folder) break;
        array_unshift($path, $folder);
        $folder_id = $folder['parent_id'];
    }
    return $path;
}

/**
 * Get icon based on file type
 */
function getFileIcon($file_type)
{
    if (strpos($file_type, 'image/') !== false) return 'image';
    if (strpos($file_type, 'video/') !== false) return 'videocam';
    if (strpos($file_type, 'pdf') !== false) return 'picture_as_pdf';
    if (strpos($file_type, 'zip') !== false || strpos($file_type, 'rar') !== false) return 'archive';
    if (strpos($file_type, 'word') !== false || strpos($file_type, 'text/') !== false) return 'description';
    return 'insert_drive_file';
}
