<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
requireLogin();

if (isset($_GET['id']) && isset($_GET['type'])) {
    $user_id = $_SESSION['user_id'];
    $id = $_GET['id'];
    $type = $_GET['type'];
    
    if ($type === 'file') {
        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $file = $stmt->fetch();
        
        if ($file) {
            $path = '../' . $file['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    } else {
        // Simple folder delete
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
}

$folder_id = $_GET['parent_id'] ?: null;
$view = $_GET['view'] ?? 'my-drive';
$redirect = "../index.php?view=$view" . ($folder_id ? "&folder=$folder_id" : "");
header("Location: $redirect");
exit();
