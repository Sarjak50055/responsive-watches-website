<?php
require_once '../includes/auth.php';
requireLogin();

if (isset($_GET['amount'])) {
    $amount_gb = intval($_GET['amount']);
    $user_id = $_SESSION['user_id'];

    // Calculate bytes (using 1024 base consistent with 15GB = 16106127360)
    $extra_bytes = $amount_gb * 1024 * 1024 * 1024;

    try {
        // Add extra storage on top of current limit for THIS user only
        $stmt = $pdo->prepare("UPDATE users SET storage_limit = storage_limit + ? WHERE id = ?");
        $stmt->execute([$extra_bytes, $user_id]);

        // Redirect back with success message (optional)
        header("Location: ../index.php?status=success_upgrade");
        exit();
    } catch (PDOException $e) {
        die("Error updating storage: " . $e->getMessage());
    }
} else {
    header("Location: ../upgrade.php");
    exit();
}
