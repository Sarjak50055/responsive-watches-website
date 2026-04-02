<?php
require_once '../includes/config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Fetch file by share_token
    $stmt = $pdo->prepare("SELECT * FROM files WHERE share_token = ?");
    $stmt->execute([$token]);
    $file = $stmt->fetch();
    
    if ($file) {
        $path = '../' . $file['file_path'];
        if (file_exists($path)) {
            // Force download
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
http_response_code(404);
echo "File not found.";
?>
