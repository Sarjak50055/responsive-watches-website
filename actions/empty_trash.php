<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    try {
        global $pdo;

        // Delete all files in trash for this user
        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE user_id = ? AND is_trash = 1");
        $stmt->execute([$user_id]);
        $files = $stmt->fetchAll();

        foreach ($files as $file) {
            $path = '../' . $file['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $pdo->prepare("DELETE FROM files WHERE user_id = ? AND is_trash = 1")->execute([$user_id]);
        $pdo->prepare("DELETE FROM folders WHERE user_id = ? AND is_trash = 1")->execute([$user_id]);

        header("Location: ../index.php?view=trash");
        exit();
    } catch (PDOException $e) {
        die("Empty Trash Error: " . $e->getMessage());
    }
}
