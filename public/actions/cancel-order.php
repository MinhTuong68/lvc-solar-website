<?php
    define('IS_SECURE', true);
    include("../../config/constants.php"); 
    include('../../classes/order.php'); 
    
    $orderObj = new Order($conn);

    // Chặn nghiêm ngặt: Chỉ chấp nhận phương thức POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['toast_message'] = "Yêu cầu không hợp lệ!";
            $_SESSION['toast_type'] = 'error';
            header("Location: " . BASE_URL . "?page=order_history");
            exit();
        }
        $order_id = (int)$_POST['order_id'];
        
        // 1. Bảo mật: Lấy danh sách SĐT hợp lệ
        $authorized_phones = $_SESSION['authorized_phones'] ?? [];

        // 2. Kiểm tra thông tin đơn
        $order_info = $orderObj->getOrderByID($order_id);

        if ($order_info) {
            // Xác thực SĐT
            if (in_array($order_info['customer_phone'], $authorized_phones)) {
                
                // 3. Tiến hành gọi hàm hủy đơn
                $result = $orderObj->clientCancelOrder($order_id);
                
                $_SESSION['toast_message'] = $result['msg'];
                $_SESSION['toast_type'] = $result['status'] ? 'success' : 'error';

            } else {
                $_SESSION['toast_message'] = "Truy cập bị từ chối! Bạn không có quyền hủy đơn hàng này.";
                $_SESSION['toast_type'] = "error";
            }
        } else {
            $_SESSION['toast_message'] = "Đơn hàng không tồn tại!";
            $_SESSION['toast_type'] = "error";
        }
    } else {
        // Trả về lỗi nếu ai đó cố tình gõ link trực tiếp (GET)
        $_SESSION['toast_message'] = "Hành động không hợp lệ!";
        $_SESSION['toast_type'] = "error";
    }

    // 4. Quay về lịch sử
   header("Location: ../index.php?page=order_history");
    exit();
?>