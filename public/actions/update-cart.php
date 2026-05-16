<?php
header('Content-Type: application/json');
include('../../config/constants.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }
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
                unset($_SESSION['cart_details'][$product_id]);
            }
        } elseif ($action === 'update') {
            // Cập nhật số lượng mới
            $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            //
            if ($qty > 0) {
                $stmt = $conn->prepare("SELECT stock FROM tbl_products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result_check = $stmt->get_result();
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
                if (isset($_SESSION['cart_details'][$product_id])) {
                    $_SESSION['cart_details'][$product_id]['quantity'] = $qty;
                }
            } else {
                unset($cart[$product_id]); // Lỡ số lượng tụt xuống 0 thì xóa luôn
                unset($_SESSION['cart_details'][$product_id]);
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