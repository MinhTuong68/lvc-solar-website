<?php
session_start();

if (isset($_POST['otp']) && isset($_POST['phone'])) {
    $user_otp = trim($_POST['otp']);
    $phone = trim($_POST['phone']);

    // Kiểm tra xem mã nhập vào có khớp với Session không
    if (isset($_SESSION['otp_code']) && $user_otp == $_SESSION['otp_code'] && $phone == $_SESSION['otp_phone']) {
        
        // MÃ ĐÚNG -> Thêm SĐT này vào danh sách được phép xem đơn hàng
        if (!isset($_SESSION['authorized_phones'])) {
            $_SESSION['authorized_phones'] = [];
        }
        
        if (!in_array($phone, $_SESSION['authorized_phones'])) {
            $_SESSION['authorized_phones'][] = $phone;
        }

        // Dùng xong thì xóa OTP đi cho bảo mật
        unset($_SESSION['otp_code']);
        unset($_SESSION['otp_phone']);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>