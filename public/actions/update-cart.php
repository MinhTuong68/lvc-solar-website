<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : ''; // Nhận diện hành động: 'update' hoặc 'remove'

    if ($product_id > 0) {
        // Thành:
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $cart = $_SESSION['cart'];
        if ($action === 'remove') {
            // Xóa món hàng khỏi mảng
            if (isset($cart[$product_id])) {
                unset($cart[$product_id]);
            }
        } elseif ($action === 'update') {
            // Cập nhật số lượng mới
            $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            if ($qty > 0) {
                $cart[$product_id] = $qty;
            } else {
                unset($cart[$product_id]); // Lỡ số lượng tụt xuống 0 thì xóa luôn
            }
        }

        $_SESSION['cart'] = $cart;

        // 4. Trả kết quả về
        $total_items = array_sum($cart);
        echo json_encode(['status' => 'success', 'total_items' => $total_items]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi ID']);
        exit;
    }
}
?>