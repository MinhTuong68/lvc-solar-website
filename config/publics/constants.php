<?php
    // 1. Cài đặt múi giờ Việt Nam
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    // 2. Bật đệm dữ liệu (giúp chuyển hướng trang mượt mà không bị lỗi header)
    ob_start();

    // 3. Khởi tạo Session (RẤT QUAN TRỌNG: Dùng để lưu Giỏ hàng và Trạng thái đăng nhập của khách)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $host = $_SERVER['HTTP_HOST'];

    // 4. Định nghĩa đường dẫn gốc
    // Lưu ý: Nếu trang khách của bạn nằm trong thư mục public thì nhớ trỏ cho đúng
    // define('SITEURL', 'http://localhost/webctylvc/public/'); 
    // define('', 'http://localhost/webctylvc/'); 
    // define('ROOT_URL', 'http://192.168.1.19/webctylvc/');

    define('SITEURL', 'http://' . $host . '/webctylvc/public/'); 
    
    // (Ghi chú: Dòng define trống của bạn bị lỗi cú pháp, mình đặt tên nó là BASE_URL cho chuẩn nhé)
    define('BASE_URL', 'http://' . $host . '/webctylvc/'); 
    
    define('ROOT_URL', 'http://' . $host . '/webctylvc/');

    // 5. Cấu hình Database (Giống hệt Admin)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'lvc_solar_db'); 

    // 6. Tạo biến kết nối CSDL ($conn)
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 7. Xử lý lỗi thân thiện với Khách Hàng
    if (!$conn) {
        // Thay vì dùng die() văng lỗi code, ta in ra một giao diện bảo trì lịch sự
        die("<div style='text-align:center; padding:100px; font-family:Arial, sans-serif; color:#333;'>
                <h2 style='color:#f59e0b;'>⚠️ HỆ THỐNG ĐANG BẢO TRÌ</h2>
                <p>Website đang được nâng cấp để phục vụ tốt hơn. Vui lòng quay lại sau ít phút.</p>
             </div>");
    }

    // 8. Đảm bảo hiển thị tiếng Việt có dấu chuẩn 100%
    mysqli_set_charset($conn, "utf8mb4");

    if (!function_exists('e')) {
        function e($string) {
            return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
        }
    }
?>