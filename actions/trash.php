<?php
require_once '../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;
$parent_id = $_GET['parent_id'] ?? null;

if ($id && $type) {
    global $pdo;
    $table = $type === 'folder' ? 'folders' : 'files';

    // Move to trash
    $stmt = $pdo->prepare("UPDATE $table SET is_trash = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

$view = $_GET['view'] ?? 'my-drive';
$folder_id = $_GET['parent_id'] ?: null;
$redirect = "../index.php?view=$view" . ($folder_id ? "&folder=$folder_id" : "");
header("Location: $redirect");
exit();
