<?php
require_once 'config.php';

/**
 * Register a new user
 */
function registerUser($pdo, $username, $email, $password)
{
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hashed_password]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Login user
 */
function loginUser($pdo, $email, $password)
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (!isset($_SESSION['accounts'])) {
            $_SESSION['accounts'] = [];
        }

        $_SESSION['accounts'][$user['id']] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ];

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        return true;
    }
    return false;
}

/**
 * Switch active account
 */
function switchAccount($id)
{
    if (isset($_SESSION['accounts'][$id])) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $_SESSION['accounts'][$id]['username'];
        $_SESSION['email'] = $_SESSION['accounts'][$id]['email'];
        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn($pdo = null)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // If PDO is provided, verify the user still exists in DB
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            session_unset();
            session_destroy();
            return false;
        }
    }

    return true;
}

/**
 * Logout user
 */
function logoutUser()
{
    session_unset();
    session_destroy();
}

/**
 * Redirect if not logged in
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        $login_url = defined('SITE_URL') ? SITE_URL . 'login.php' : 'login.php';
        header("Location: $login_url");
        exit();
    }
}
