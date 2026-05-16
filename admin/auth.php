<?php
define('IS_SECURE', true);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login-admin.php");
    exit();
}

function requireRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    $currentRole = $_SESSION['admin_role'] ?? '';

    if (!in_array($currentRole, $roles)) {
        http_response_code(403);
        die("Bạn không có quyền truy cập chức năng này.");
    }
}
?>