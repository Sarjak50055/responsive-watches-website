<?php
require_once 'includes/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS file_shares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        file_id INT DEFAULT NULL,
        folder_id INT DEFAULT NULL,
        user_id INT NOT NULL,
        shared_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (file_id),
        INDEX (folder_id),
        INDEX (user_id),
        INDEX (shared_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "Table 'file_shares' created successfully.\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
