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
                // To be safe, should check user_id and then delete recursively
                $pdo->prepare("DELETE FROM folders WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
                // Delete files inside
                // In a production app, you'd delete the actual files from disk too.
            } else {
                $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $user_id]);
                $file = $stmt->fetch();
                if ($file && file_exists("../" . $file['file_path'])) {
                    unlink("../" . $file['file_path']);
                }
                $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?")->execute([$id, $user_id]);
            }
        }
    }

    $view = $_POST['view'] ?? 'my-drive';
    $redirect = "../index.php?view=$view" . ($parent_id ? "&folder=$parent_id" : "");
    header("Location: $redirect");
    exit();
}
