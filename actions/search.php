<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['results' => []]);
    exit();
}

$user_id = $_SESSION['user_id'];
$query   = trim($_GET['q'] ?? '');
$type    = $_GET['type'] ?? 'any';
$owner   = $_GET['owner'] ?? 'any';
$date    = $_GET['date'] ?? 'any';

$results = [];

// Base Queries
$file_sql = "SELECT f.id, f.name, f.file_type, f.file_size, f.folder_id, 'file' AS item_type FROM files f";
$folder_sql = "SELECT id, name, parent_id, 'folder' AS item_type FROM folders";

$file_where = ["f.is_trash = 0"];
$folder_where = ["is_trash = 0"];
$params = [];

// Query Filter
if (!empty($query)) {
    $file_where[] = "f.name LIKE ?";
    $folder_where[] = "name LIKE ?";
    $params[] = '%' . $query . '%';
}

// Ownership Filter
if ($owner == 'me') {
    $file_where[] = "f.user_id = ?";
    $folder_where[] = "user_id = ?";
    $params[] = $user_id;
} elseif ($owner == 'shared') {
    $file_where[] = "f.id IN (SELECT file_id FROM file_shares WHERE user_id = ? AND file_id IS NOT NULL)";
    $folder_where[] = "id IN (SELECT folder_id FROM file_shares WHERE user_id = ? AND folder_id IS NOT NULL)";
    $params[] = $user_id;
} else {
    // 'any' - include owned and shared
    $file_where[] = "(f.user_id = ? OR f.id IN (SELECT file_id FROM file_shares WHERE user_id = ?))";
    $folder_where[] = "(user_id = ? OR id IN (SELECT folder_id FROM file_shares WHERE user_id = ?))";
    $params[] = $user_id;
    $params[] = $user_id;
}

// Type Filter
if ($type != 'any') {
    if ($type == 'folder') {
        $file_where[] = "1=0"; // Don't show files if searching for folders
    } else {
        $folder_where[] = "1=0"; // Don't show folders if searching for files
        if ($type == 'pdf') $file_where[] = "f.file_type LIKE '%pdf%'";
        if ($type == 'image') $file_where[] = "f.file_type LIKE 'image/%'";
        if ($type == 'video') $file_where[] = "f.file_type LIKE 'video/%'";
    }
}

// Date Filter
if ($date != 'any') {
    $days = 0;
    if ($date == 'today') $days = 0;
    if ($date == '7days') $days = 7;
    if ($date == '30days') $days = 30;
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-$days days midnight"));
    $file_where[] = "f.uploaded_at >= ?";
    $folder_where[] = "created_at >= ?";
    $params[] = $date_limit;
}

// Final Prepare and Execute
$file_final_where = implode(" AND ", $file_where);
$folder_final_where = implode(" AND ", $folder_where);

// For Files
$stmt = $pdo->prepare("$file_sql WHERE $file_final_where ORDER BY f.name LIMIT 10");
// We need to map params correctly. This is tricky because params list is shared.
// For simplicity, let's rebuild param arrays for each.

function getParamsForQuery($where_array, $all_params, $is_shared_with_me_any = false) {
    // This is getting complex. Let's just use named params or separate arrays.
}

// Re-doing the parameter logic simply
$file_params = [];
$folder_params = [];

// Query
if (!empty($query)) {
    $file_params[] = '%' . $query . '%';
    $folder_params[] = '%' . $query . '%';
}
// Owner
if ($owner == 'me') {
    $file_params[] = $user_id;
    $folder_params[] = $user_id;
} elseif ($owner == 'shared') {
    $file_params[] = $user_id;
    $folder_params[] = $user_id;
} else {
    $file_params[] = $user_id; $file_params[] = $user_id;
    $folder_params[] = $user_id; $folder_params[] = $user_id;
}
// Date
if ($date != 'any') {
    $file_params[] = $date_limit;
    $folder_params[] = $date_limit;
}

$stmt = $pdo->prepare("$file_sql WHERE $file_final_where ORDER BY f.name LIMIT 10");
$stmt->execute($file_params);
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'id'        => $row['id'],
        'name'      => $row['name'],
        'type'      => 'file',
        'file_type' => $row['file_type'],
        'size'      => formatSize($row['file_size']),
        'folder_id' => $row['folder_id'],
    ];
}

$stmt = $pdo->prepare("$folder_sql WHERE $folder_final_where ORDER BY name LIMIT 10");
$stmt->execute($folder_params);
foreach ($stmt->fetchAll() as $row) {
    $results[] = [
        'id'        => $row['id'],
        'name'      => $row['name'],
        'type'      => 'folder',
        'parent_id' => $row['parent_id'],
    ];
}

echo json_encode(['results' => $results]);
