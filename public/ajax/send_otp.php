<?php
session_start();
require_once '../../config/constants.php'; // Đường dẫn trỏ về file cấu hình DB của anh

if (isset($_POST['phone'])) {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

    // Tìm Email của khách hàng dựa vào SĐT (Lấy đơn mới nhất)
    $sql = "SELECT customer_email FROM tbl_orders WHERE customer_phone = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $email = $res->fetch_assoc()['customer_email'];
        
        // 1. Random mã OTP 6 số
        $otp = rand(100000, 999999);
        
        // 2. Lưu vào Session
        $_SESSION['otp_code'] = $otp;
        $_SESSION['otp_phone'] = $phone;

        // 3. CODE GỬI MAIL (Dùng hàm mail mặc định của PHP hoặc PHPMailer)
        $subject = "Ma xac nhan tra cuu don hang LVC Solar";
        $message = "Xin chao,\n\nMa xac nhan tra cuu don hang cua ban la: " . $otp . "\n\nVui long khong chia se ma nay cho bat ky ai.";
        $headers = "From: no-reply@lvcsolar.vn";
        
        @mail($email, $subject, $message, $headers); // Hàm gửi mail cơ bản

        // Gửi hint về cho JS (ẩn bớt email đi cho bảo mật, vd: nva***@gmail.com)
        $email_parts = explode("@", $email);
        $hint = substr($email_parts[0], 0, 3) . "***@" . $email_parts[1];

        echo json_encode(['status' => 'success', 'email_hint' => $hint]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Số điện thoại này chưa từng đặt hàng!']);
    }
}
?>