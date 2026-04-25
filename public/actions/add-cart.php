<?php
    session_start();
    include('../../config/publics/constants.php');
// #region agent log
file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
    'sessionId' => '6a4c11',
    'runId' => 'initial',
    'hypothesisId' => 'H1',
    'location' => 'public/actions/add-cart.php:4',
    'message' => 'add-cart endpoint hit',
    'data' => [
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'phpSessionId' => session_id()
    ],
    'timestamp' => round(microtime(true) * 1000)
], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
// #endregion

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận ID sản phẩm từ Javascript gửi qua
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    // #region agent log
    file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
        'sessionId' => '6a4c11',
        'runId' => 'initial',
        'hypothesisId' => 'H2',
        'location' => 'public/actions/add-cart.php:24',
        'message' => 'add-cart payload parsed',
        'data' => [
            'productId' => $product_id,
            'quantity' => $qty
        ],
        'timestamp' => round(microtime(true) * 1000)
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    // #endregion

    if ($product_id > 0) {
        $sql_check = "SELECT stock FROM tbl_products WHERE id = $product_id AND status = 1";
        $result_check = $conn->query($sql_check);
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

        // Đếm xem trong giỏ đang có tổng cộng bao nhiêu món
        $total_items = array_sum($_SESSION['cart']);
        // #region agent log
        file_put_contents(__DIR__ . '/../../debug-6a4c11.log', json_encode([
            'sessionId' => '6a4c11',
            'runId' => 'initial',
            'hypothesisId' => 'H3',
            'location' => 'public/actions/add-cart.php:45',
            'message' => 'cart updated in session',
            'data' => [
                'phpSessionId' => session_id(),
                'sessionCart' => $_SESSION['cart'],
                'totalItems' => $total_items
            ],
            'timestamp' => round(microtime(true) * 1000)
        ], JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
        // #endregion
        
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