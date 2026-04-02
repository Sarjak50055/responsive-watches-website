<?php
require_once '../includes/auth.php';
requireLogin();

$file_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(403);
    exit('Access denied.');
}

$path = '../' . $file['file_path'];

if (!file_exists($path)) {
    http_response_code(404);
    exit('File not found.');
}

// Send the file inline (browser renders it, not downloads it)
$mime = $file['file_type'] ?: mime_content_type($path);

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . rawurlencode($file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;
