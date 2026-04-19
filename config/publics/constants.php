<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    ob_start();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $host = $_SERVER['HTTP_HOST'];

    define('SITEURL', 'http://' . $host . '/webctylvc/public/'); 
    
    define('BASE_URL', 'http://' . $host . '/webctylvc/'); 
    
    define('ROOT_URL', 'http://' . $host . '/webctylvc/');

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'lvc_solar_db'); 

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        die("<div style='text-align:center; padding:100px; font-family:Arial, sans-serif; color:#333;'>
                <h2 style='color:#f59e0b;'>⚠️ HỆ THỐNG ĐANG BẢO TRÌ</h2>
                <p>Website đang được nâng cấp để phục vụ tốt hơn. Vui lòng quay lại sau ít phút.</p>
             </div>");
    }

    mysqli_set_charset($conn, "utf8mb4");

    if (!function_exists('e')) {
        function e($string) {
            return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
        }
    }
?>