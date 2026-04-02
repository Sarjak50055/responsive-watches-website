<?php
require_once 'includes/config.php';
$tables = ['users', 'files', 'folders'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
}
