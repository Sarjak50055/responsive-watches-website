<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $base_folder_id = !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;

    // Normalize $_FILES into a standard list of file objects
    $files_to_process = [];
    if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
        // Single file upload
        $files_to_process[] = [
            'name' => $_FILES['file']['name'],
            'tmp_name' => $_FILES['file']['tmp_name'],
            'size' => $_FILES['file']['size'],
            'type' => $_FILES['file']['type'],
            'full_path' => $_FILES['file']['name'] // Just the name for single files
        ];
    } elseif (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        // Folder/Multiple file upload
        foreach ($_FILES['files']['name'] as $i => $name) {
            $files_to_process[] = [
                'name' => $name,
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'size' => $_FILES['files']['size'][$i],
                'type' => $_FILES['files']['type'][$i],
                'full_path' => isset($_FILES['files']['full_path'][$i]) ? $_FILES['files']['full_path'][$i] : $name
            ];
        }
    }

    if (empty($files_to_process)) {
        header("Location: index.php" . ($base_folder_id ? "?folder=$base_folder_id" : ""));
        exit();
    }

    // Validate total storage limit first
    $total_new_size = array_sum(array_column($files_to_process, 'size'));
    $current_usage = getStorageUsage($pdo, $user_id);
    $limit = getStorageLimit($pdo, $user_id);

    if ($current_usage + $total_new_size > $limit) {
        die("Storage limit exceeded. Remaining storage is not enough for this upload.");
    }

    // Prepare folder cache to avoid redundant creations
    $folder_cache = []; // key: path string, value: folder_id

    foreach ($files_to_process as $file) {
        if ($file['size'] == 0 && empty($file['tmp_name'])) continue;

        $relative_path = $file['full_path'];
        $path_parts = explode('/', $relative_path);

        // The last part is the filename
        array_pop($path_parts);

        $current_parent_id = $base_folder_id;
        $acc_path = "";

        // Reconstruct/Find folder structure
        foreach ($path_parts as $part) {
            $acc_path .= ($acc_path ? "/" : "") . $part;

            if (!isset($folder_cache[$acc_path])) {
                // Check if folder exists in DB under this parent
                $stmt = $pdo->prepare("SELECT id FROM folders WHERE user_id = ? AND name = ? AND (parent_id = ? OR (parent_id IS NULL AND ? IS NULL))");
                $stmt->execute([$user_id, $part, $current_parent_id, $current_parent_id]);
                $found_id = $stmt->fetchColumn();

                if ($found_id) {
                    $folder_cache[$acc_path] = $found_id;
                } else {
                    // Create folder
                    $stmt = $pdo->prepare("INSERT INTO folders (user_id, name, parent_id) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $part, $current_parent_id]);
                    $folder_cache[$acc_path] = $pdo->lastInsertId();
                }
            }
            $current_parent_id = $folder_cache[$acc_path];
        }

        // Process the file upload
        $original_name = $file['name'];
        $file_size = $file['size'];
        $file_type = $file['type'];

        // Generate unique localized name
        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_name = uniqid() . ($ext ? '.' . $ext : '');
        $upload_dir = 'uploads/' . $user_id . '/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                $share_token = bin2hex(random_bytes(16));
                $stmt = $pdo->prepare("INSERT INTO files (user_id, folder_id, name, original_name, file_path, file_type, file_size, share_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $current_parent_id, $original_name, $original_name, $file_path, $file_type, $file_size, $share_token]);
            } catch (PDOException $e) {
                error_log("UPLOAD DB ERROR: " . $e->getMessage());
            }
        }
    }

    header("Location: index.php" . ($base_folder_id ? "?folder=$base_folder_id" : ""));
    exit();
}
