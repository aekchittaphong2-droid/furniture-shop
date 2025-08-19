<?php
// admin/logout.php
session_start();

// ລຶບ session ທັງໝົດ
$_SESSION = array();

// ລຶບ session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ທຳລາຍ session
session_destroy();

// Redirect ໄປໜ້າ login
header('Location: login.php');
exit();
?>