<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'])) {
    $items = json_decode($_POST['items'], true);
    $parent_id = $_POST['parent_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (is_array($items)) {
        foreach ($items as $item) {
            $id = $item['id'];
            $type = $item['type'];
            $table = ($type === 'folder' ? 'folders' : 'files');
            
            $stmt = $pdo->prepare("UPDATE $table SET is_trash = 0 WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
        }
    }

    $redirect = "../index.php?view=trash" . ($parent_id ? "&folder=$parent_id" : "");
    header("Location: $redirect");
    exit();
}
