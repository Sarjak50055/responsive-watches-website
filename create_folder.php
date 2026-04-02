<?php
require_once 'includes/auth.php';
requireLogin();

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {
//     $user_id = $_SESSION['user_id'];
//     $name = $_POST['folder_name'];
//     $parent_id = $_POST['parent_id'] ?? null;

//     // error_log("CREATE FOLDER: User $user_id, Name $name, Parent " . var_export($parent_id, true));
//     echo "CREATE FOLDER: User $user_id, Name $name, Parent " . $parent_id;
//     try {
//         $stmt = $pdo->prepare("INSERT INTO folders (user_id, name, parent_id) VALUES (?, ?, ?)");
//         $stmt->execute([$user_id, $name, $parent_id]);
//         // echo "Error aave chhe.";
//         error_log("CREATE FOLDER SUCCESS: ID " . $pdo->lastInsertId());
//     } catch (PDOException $e) {
//         error_log("CREATE FOLDER ERROR: " . $e->getMessage());
//         echo "Error aave chhe2222." . $e->getMessage();
//     }

//     // header("Location: index.php" . ($parent_id ? "?folder=$parent_id" : ""));
//     // exit();
// } else {
//     error_log("CREATE FOLDER: Invalid request method or missing folder_name");
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_name'])) {

    $user_id = $_SESSION['user_id'];
    $name = $_POST['folder_name'];

    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    echo "CREATE FOLDER: User $user_id, Name $name, Parent " . var_export($parent_id, true);

    try {
        $stmt = $pdo->prepare("INSERT INTO folders (user_id, name, parent_id) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $parent_id]);

        error_log("CREATE FOLDER SUCCESS: ID " . $pdo->lastInsertId());
    } catch (PDOException $e) {

        error_log("CREATE FOLDER ERROR: " . $e->getMessage());
        // echo "Error aave chhe: " . $e->getMessage();
    }
}
