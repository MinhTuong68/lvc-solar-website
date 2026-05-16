<?php
    include('../../config/constants.php');
    require_once '../../classes/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ']);
        exit;
    }

    $spam_guard = new RateLimit('add_cart', 10, 100); 
    $check = $spam_guard->check();

    if (!$check['allowed']) {
        echo json_encode([
            'status' => 'error', 
            'message' => $check['message']
        ]);
        exit;
    }

    // Nhận ID sản phẩm từ Javascript gửi qua
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($product_id > 0) {
        $stmt = $conn->prepare("SELECT stock FROM tbl_products WHERE id = ? AND status = 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result_check = $stmt->get_result();

        // KIỂM TRA: Nếu tìm thấy sản phẩm trong Database
        if ($result_check && $result_check->num_rows > 0) {
            $product = $result_check->fetch_assoc();
            $stock = (int)$product['stock'];
            
            // Tính số lượng dự kiến
            $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
            
            // CHỐT CHẶN: Nếu vượt quá kho -> Báo lỗi và Dừng luôn
            if (($current_qty + $qty) > $stock) {
                echo json_encode(['status' => 'error', 'message' => "Rất tiếc! Kho chỉ còn $stock sản phẩm."]);
                exit; 
            }
            
            // NẾU SỐ LƯỢNG HỢP LỆ -> KHÔNG LÀM GÌ CẢ (Để code trôi xuống dưới và thực hiện thêm vào giỏ)
            
        } else {
            // NẾU KHÔNG TÌM THẤY SẢN PHẨM TRONG DATABASE
            echo json_encode(['status' => 'error', 'message' => "Sản phẩm không tồn tại."]);
            exit;
        }
        

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

        $stmt_detail = $conn->prepare("SELECT id, name, price, image FROM tbl_products WHERE id = ? LIMIT 1");
        $stmt_detail->bind_param("i", $product_id);
        $stmt_detail->execute();
        $p_detail = $stmt_detail->get_result()->fetch_assoc();
        $stmt_detail->close();

        if ($p_detail) {
            if (!isset($_SESSION['cart_details'])) $_SESSION['cart_details'] = [];
            $_SESSION['cart_details'][$product_id] = [
                'id'       => $p_detail['id'],
                'name'     => $p_detail['name'],
                'price'    => $p_detail['price'],
                'image'    => $p_detail['image'],
                'quantity' => $_SESSION['cart'][$product_id], // Số lượng mới nhất
            ];
        }

        // Đếm xem trong giỏ đang có tổng cộng bao nhiêu món
        $total_items = array_sum($_SESSION['cart']);

        if (!isset($_SESSION['new_added_count'])) {
            $_SESSION['new_added_count'] = $qty;
        } else {
            $_SESSION['new_added_count'] += $qty;
        }

        // Báo cáo về lại cho Javascript biết là đã xong
        echo json_encode([
            'status' => 'success', 
            'new_items' => $_SESSION['new_added_count'],
            'total_items' => $total_items,
            'cart_items' => array_values($_SESSION['cart_details'] ?? []),
            'root_url'     => ROOT_URL,  
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi ID sản phẩm']);
    }
}
?>