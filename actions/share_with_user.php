<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
requireLogin();

if (isset($_GET['id']) && isset($_GET['type']) && isset($_GET['email'])) {
    $item_id = (int)$_GET['id'];
    $item_type = $_GET['type'];
    $email = $_GET['email'];
    $current_user_id = $_SESSION['user_id'];

    // Find the user by email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $recipient = $stmt->fetch();

    if (!$recipient) {
        die("User with email $email not found. Ask them to register first!");
    }

    if ($recipient['id'] == $current_user_id) {
        die("You cannot share a file with yourself!");
    }

    $recipient_id = $recipient['id'];

    // Check if item exists and belongs to current user
    if ($item_type == 'file') {
        $stmt = $pdo->prepare("SELECT id FROM files WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT id FROM folders WHERE id = ? AND user_id = ?");
    }
    $stmt->execute([$item_id, $current_user_id]);
    
    if ($stmt->fetch()) {
        // Create share record
        if ($item_type == 'file') {
            $stmt = $pdo->prepare("INSERT INTO file_shares (file_id, user_id, shared_by) VALUES (?, ?, ?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO file_shares (folder_id, user_id, shared_by) VALUES (?, ?, ?)");
        }
        $stmt->execute([$item_id, $recipient_id, $current_user_id]);
        
        header("Location: ../index.php?msg=Shared successfully with $email");
        exit();
    } else {
        die("Item not found or access denied.");
    }
} else {
    header("Location: ../index.php");
    exit();
}
