<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
requireLogin();

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if ($id && $type) {
    if (!isset($pdo)) {
        die("Connection to nebula database lost.");
    }
    $table = $type === 'folder' ? 'folders' : 'files';

    // Restore from trash
    $stmt = $pdo->prepare("UPDATE $table SET is_trash = 0 WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

$view = $_GET['view'] ?? 'trash';
$folder_id = $_GET['parent_id'] ?: null;
$redirect = "../index.php?view=$view" . ($folder_id ? "&folder=$folder_id" : "");
header("Location: $redirect");
exit();
