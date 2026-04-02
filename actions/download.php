<?php
require_once '../includes/auth.php';
requireLogin();

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $file_id = $_GET['id'];
    
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $user_id]);
    $file = $stmt->fetch();
    
    if ($file) {
        $path = '../' . $file['file_path'];
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
    }
}
header("Location: ../index.php");
?>
