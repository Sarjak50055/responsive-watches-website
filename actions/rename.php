<?php
require_once '../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type']; // 'file' or 'folder'
    $id = $_POST['id'];
    $new_name = $_POST['new_name'];
    
    if ($type === 'file') {
        $stmt = $pdo->prepare("UPDATE files SET name = ?, original_name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_name, $new_name, $id, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_name, $id, $user_id]);
    }
    
    $folder_id = $_POST['parent_id'] ?: null;
    header("Location: ../index.php" . ($folder_id ? "?folder=$folder_id" : ""));
    exit();
}
?>
