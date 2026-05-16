<?php
    include('../../config/constants.php'); 
    include('../../classes/order.php');
    require_once '../../classes/rate_limit.php';

    $orderObj = new Order($conn);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        if (
            !isset($_POST['csrf_token']) || 
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            $_SESSION['toast_message'] = "Yêu cầu không hợp lệ, vui lòng thử lại.";
            $_SESSION['toast_type'] = 'error';
            header("Location: " . BASE_URL . "?page=order_history");
            exit();
        }

        $spam_guard = new RateLimit('update_qty', 5, 100); 
        $check = $spam_guard->check();

        if (!$check['allowed']) {
            // Lưu tin nhắn chửi spam vào Session để hiển thị Toast
            $_SESSION['toast_message'] = $check['message'];
            $_SESSION['toast_type'] = 'error';
            
            // Quay lại trang chỉnh sửa đơn hàng ngay lập tức
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $id = (int)$_POST['order_id'];
        $p_id = (int)$_POST['product_id'];

        $order = $orderObj->getOrderByID($id);
        $authorized_phones = $_SESSION['authorized_phones'] ?? [];
        if (!$order || !in_array($order['customer_phone'], $authorized_phones)) {
            $_SESSION['toast_message'] = "Bạn không có quyền chỉnh sửa đơn hàng này!";
            $_SESSION['toast_type'] = 'error';
            header("Location: " . BASE_URL . "?page=order_history");
            exit();
        }

        // 1. NẾU BẤM CẬP NHẬT
        if ($_POST['action'] == 'update_qty') {
            $new_qty = (int)$_POST['quantity'];
            if ($new_qty > 0) {
                $stmt = $conn->prepare("UPDATE tbl_order_details SET quantity = ? WHERE order_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $new_qty, $id, $p_id);
                $stmt->execute();
                $_SESSION['toast_message'] = "Cập nhật số lượng thành công!";
                $_SESSION['toast_type'] = "success";
            }
        }

        // 2. NẾU BẤM XÓA
        if ($_POST['action'] == 'delete_item') {
            $stmt = $conn->prepare("DELETE FROM tbl_order_details WHERE order_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $id, $p_id);
            $stmt->execute();
            $_SESSION['toast_message'] = "Đã xóa sản phẩm khỏi đơn hàng!";
            $_SESSION['toast_type'] = "success";
        }

        // TỰ ĐỘNG TÍNH LẠI TỔNG TIỀN VÀ CẬP NHẬT VÀO HÓA ĐƠN
        $stmt = $conn->prepare("SELECT COUNT(*) AS count_item, SUM(price * quantity) AS total FROM tbl_order_details WHERE order_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if ($row['count_item'] > 0){
             if ($row['total']) {
                $new_total = $row['total'];
            } else {
                $new_total = 0;
            }
            $stmt = $conn->prepare("UPDATE tbl_orders SET total_amount = ? WHERE id = ?");
            $stmt->bind_param("di", $new_total, $id);
            $stmt->execute();

            // Quay lại trang chỉnh sửa
            header("Location: ../?page=edit_order&id=" . $id);
            exit;
        }
        else{
            $stmt = $conn->prepare("DELETE FROM tbl_orders WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['toast_message'] = "Đơn hàng tự động xóa vì không còn sản phẩm!";
            $_SESSION['toast_type'] = "success";
            header("Location: ../?page=order_history");
            exit;
        }
       
    }
?>