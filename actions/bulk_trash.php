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

            if ($type === 'folder') {
                $stmt = $pdo->prepare("UPDATE folders SET is_trash = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                // Recursively trash files in this folder? (Optional, GDrive does this)
                $pdo->prepare("UPDATE files SET is_trash = 1 WHERE folder_id = ? AND user_id = ?")->execute([$id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE files SET is_trash = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
            }
        }
    }

    $view = $_POST['view'] ?? 'my-drive';
    $redirect = "../index.php?view=$view" . ($parent_id ? "&folder=$parent_id" : "");
    header("Location: $redirect");
    exit();
}
