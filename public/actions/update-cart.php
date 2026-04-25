<?php
session_start();
header('Content-Type: application/json');
include('../../config/publics/constants.php');

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
            //
            if ($qty > 0) {
                $sql_check = "SELECT stock FROM tbl_products WHERE id = $product_id";
                $result_check = $conn->query($sql_check);
                if ($result_check && $result_check->num_rows > 0) {
                    $product = $result_check->fetch_assoc();
                    $stock = (int)$product['stock'];
                    
                    // Nếu khách gõ số 12 mà kho còn 1 -> Chặn lại và Dừng
                    if ($qty > $stock) {
                        echo json_encode(['status' => 'error', 'message' => "Không thể cập nhật! Kho chỉ còn $stock sản phẩm.", 'stock' => $stock]);
                        exit;
                    }
                }
            }
            //
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