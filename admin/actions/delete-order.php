<?php
    include("../classes/order.php");
    include_once('../admin/auth.php');
    $order_obj = new Order($conn);

    // Kiểm tra xem có ID truyền trên thanh URL không
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Gọi hàm xử lý từ Class
        $result = $order_obj->deleteOrder($id);
        
        // 1. Gắn câu thông báo từ Class trả về
        $_SESSION['toast_message'] = $result['msg'];

        // 2. Kiểm tra trạng thái để hiển thị màu thông báo (Xanh hoặc Đỏ)
        if ($result['status'] == true) {
            $_SESSION['toast_type'] = 'success';
        } else {
            $_SESSION['toast_type'] = 'error';
        }
    } else {
        // Trường hợp ai đó gõ link bậy bạ không có ID
        $_SESSION['toast_message'] = "Không tìm thấy mã đơn hàng cần xóa!";
        $_SESSION['toast_type'] = 'error';
    }

    // Xóa xong thì đá người dùng về lại trang Quản lý đơn hàng
    header("Location: index.php?page=manage-order");
    exit();
?>