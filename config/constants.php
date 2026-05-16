<?php
    ob_start();
    $envFile = dirname(__DIR__) . '/.env'; // Từ config/ lùi ra thư mục gốc
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    error_reporting(0);
    ini_set('display_errors', 0);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /* ========================================================
       CẤU HÌNH ĐƯỜNG DẪN TÊN MIỀN
       (Khi đưa lên hosting thật diennangluongmattroimientay.com, 
        bạn nhớ XÓA bỏ chữ /webctylvc/ nhé) thêm s
    ======================================================== */
    $host = $_SERVER['HTTP_HOST'];
    define('SITEURL', 'http://' . $host . '/webctylvc/public/'); 
    define('BASE_URL', 'http://' . $host . '/webctylvc/'); 
    define('ROOT_URL', 'http://' . $host . '/webctylvc/');

    /* ========================================================
       CẤU HÌNH KẾT NỐI DATABASE
    ======================================================== */
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'lvc_solar_db'); 

    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        http_response_code(503);
        header('Retry-After: 3600');
        header('X-Content-Type-Options: nosniff');

        $maintenanceFile = realpath(__DIR__ . '/maintenance.html');

        if ($maintenanceFile && is_file($maintenanceFile)) {
            readfile($maintenanceFile);
        }
        else{
            // Thông báo thân thiện khi DB gặp sự cố
            die("<div style='text-align:center; padding:100px; font-family:Arial, sans-serif; color:#333;'>
                    <h2 style='color:#f59e0b;'>⚠️ HỆ THỐNG ĐANG BẢO TRÌ</h2>
                    <p>Website đang được nâng cấp để phục vụ tốt hơn. Vui lòng quay lại sau ít phút.</p>
                </div>");
        }
        exit();
    }

    mysqli_set_charset($conn, "utf8mb4");

    /* ========================================================
       CÁC HÀM TIỆN ÍCH DÙNG CHUNG
    ======================================================== */
    // Hàm bọc htmlspecialchars để chống tấn công XSS
    if (!function_exists('e')) {
        function e($string) {
            return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
        }
    }

    // Tạo CSRF Token nếu chưa có
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
?>