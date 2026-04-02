<?php
require_once '../includes/auth.php';

$id = intval($_GET['id'] ?? 0);
if ($id) {
    // If account is not in session yet, load it from the database
    if (!isset($_SESSION['accounts'][$id])) {
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['accounts'][$id] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
            ];
        }
    }
    switchAccount($id);
}

header("Location: ../index.php?view=my-drive");
exit();
