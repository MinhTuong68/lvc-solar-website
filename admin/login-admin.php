<?php
define('IS_SECURE', true);
require_once("../config/constants.php");
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$is_locked = false; 

$maxAttempts = 3;
$lockTime    = 15 * 60;

$attempts = $_SESSION['login_attempts'] ?? 0;
$lastFail  = $_SESSION['last_fail_time'] ?? 0;

// Kiểm tra khóa trước
if ($attempts >= $maxAttempts) {
    if (time() - $lastFail < $lockTime) {
        $is_locked = true;
        $wait_time = ceil(($lockTime - (time() - $lastFail)) / 60);
        $error = "⛔ Nhập sai quá nhiều lần. Vui lòng đợi $wait_time phút nữa.";
    } else {
        $_SESSION['login_attempts'] = 0;
        $attempts = 0;
    }
}

// Nếu đã login rồi thì redirect
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$is_locked) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Yêu cầu không hợp lệ (Lỗi CSRF). Vui lòng tải lại trang.";
    }
    else{
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "Vui lòng nhập email và mật khẩu.";
        } else {
            $sql  = "SELECT id, fullname, email, password, role, status FROM tbl_admins WHERE email = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$admin || (int)$admin['status'] !== 1) {
                $_SESSION['login_attempts'] = $attempts + 1;
                $_SESSION['last_fail_time'] = time();
                $error = "Thông tin đăng nhập không đúng.";

            } elseif (!password_verify($password, $admin['password'])) {
                $_SESSION['login_attempts'] = $attempts + 1;
                $_SESSION['last_fail_time'] = time();
                $error = "Thông tin đăng nhập không đúng.";

            } else {
                $_SESSION['login_attempts'] = 0;

                session_regenerate_id(true);
                $_SESSION['admin_id']    = $admin['id'];
                $_SESSION['admin_name']  = $admin['fullname'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role']  = $admin['role'];

                header("Location: index.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập quản trị - LVC Solar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #0b2447, #19376d);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 18px;
            padding: 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo h1 {
            color: #0b2447;
            font-size: 28px;
            margin-bottom: 6px;
        }

        .login-logo p {
            color: #64748b;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 15px;
            outline: none;
        }

        .form-control:focus {
            border-color: #00875a;
            box-shadow: 0 0 0 3px rgba(0,135,90,0.12);
        }

        .btn-login {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 10px;
            background: #00875a;
            color: #fff;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #00a86b;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .login-note {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 18px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="login-logo">
        <h1>LVC Solar</h1>
        <p>Đăng nhập hệ thống quản trị</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label>Email quản trị</label>
            <input type="email" name="email" class="form-control" placeholder="admin@lvcsolar.vn" required <?= $is_locked ? 'disabled' : '' ?>>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required <?= $is_locked ? 'disabled' : '' ?>>
        </div>

        <button type="submit" class="btn-login" <?= $is_locked ? 'disabled' : '' ?> style="<?= $is_locked ? 'background:#94a3b8; cursor:not-allowed;' : '' ?>">Đăng nhập</button>
    </form>

    <div class="login-note">
        Chỉ nhân sự được phân quyền mới được truy cập.
    </div>
</div>

</body>
</html>