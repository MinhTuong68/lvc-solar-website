<?php
    session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận ID sản phẩm từ Javascript gửi qua
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($product_id > 0) {
        // 1. Nếu giỏ hàng chưa từng tồn tại, tạo một cái túi trống
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // 2. Nếu món hàng này đã có trong túi -> Cộng dồn số lượng
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } 
        // 3. Nếu món hàng này chưa có -> Thêm mới vào túi
        else {
            $_SESSION['cart'][$product_id] = $qty;
        }

        // Đếm xem trong giỏ đang có tổng cộng bao nhiêu món
        $total_items = array_sum($_SESSION['cart']);
        
        // Báo cáo về lại cho Javascript biết là đã xong
        echo json_encode([
            'status' => 'success', 
            'total_items' => $total_items
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi ID sản phẩm']);
    }
}
?>